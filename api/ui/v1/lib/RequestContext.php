<?php

declare(strict_types=1);

/**
 * Normalized request context passed to every future UI API handler.
 * WP-04 resolves transport metadata only; WP-05 must bind authoritative
 * Session, actor, tenant/company, capability, locale and timezone state.
 */
final class UiApiRequestContext
{
    public const API_VERSION = '1';

    private string $requestId;
    private string $correlationId;
    private string $timestamp;
    private $actor;
    private $tenant;
    private $capabilities;
    private ?string $locale;
    private ?string $timezone;

    public function __construct(
        string $requestId,
        string $correlationId,
        ?string $timestamp = null,
        $actor = null,
        $tenant = null,
        $capabilities = null,
        ?string $locale = null,
        ?string $timezone = null
    ) {
        $this->requestId = $requestId;
        $this->correlationId = $correlationId;
        $this->timestamp = $timestamp ?? gmdate('Y-m-d\TH:i:s\Z');
        $this->actor = $actor;
        $this->tenant = $tenant;
        $this->capabilities = $capabilities;
        $this->locale = $locale;
        $this->timezone = $timezone;
    }

    public static function fromRequest(UiApiRequest $request): self
    {
        return self::fromIncomingIds($request->header('x-request-id'), $request->header('x-correlation-id'));
    }

    public static function fromServer(array $server): self
    {
        return self::fromIncomingIds(
            isset($server['HTTP_X_REQUEST_ID']) ? (string)$server['HTTP_X_REQUEST_ID'] : null,
            isset($server['HTTP_X_CORRELATION_ID']) ? (string)$server['HTTP_X_CORRELATION_ID'] : null
        );
    }

    public function requestId(): string { return $this->requestId; }
    public function correlationId(): string { return $this->correlationId; }
    public function timestamp(): string { return $this->timestamp; }
    public function actor() { return $this->actor; }
    public function tenant() { return $this->tenant; }
    public function capabilities() { return $this->capabilities; }
    public function locale(): ?string { return $this->locale; }
    public function timezone(): ?string { return $this->timezone; }

    public function envelopeMeta(): array
    {
        return [
            'apiVersion' => self::API_VERSION,
            'requestId' => $this->requestId,
            'correlationId' => $this->correlationId,
            'timestamp' => $this->timestamp,
        ];
    }

    public function unresolvedSecuritySlots(): array
    {
        return [
            'actor' => $this->actor === null ? 'unresolved' : 'resolved',
            'tenant' => $this->tenant === null ? 'unresolved' : 'resolved',
            'capabilities' => $this->capabilities === null ? 'unresolved' : 'resolved',
            'locale' => $this->locale === null ? 'unresolved' : 'resolved',
            'timezone' => $this->timezone === null ? 'unresolved' : 'resolved',
        ];
    }

    private static function fromIncomingIds(?string $incomingRequestId, ?string $incomingCorrelationId): self
    {
        $requestId = self::validId($incomingRequestId) ? trim((string)$incomingRequestId) : self::generateId();
        $correlationId = self::validId($incomingCorrelationId)
            ? trim((string)$incomingCorrelationId)
            : $requestId;
        return new self($requestId, $correlationId);
    }

    private static function validId(?string $value): bool
    {
        if ($value === null) return false;
        $value = trim($value);
        return $value !== '' && strlen($value) <= 128
            && preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/', $value) === 1;
    }

    private static function generateId(): string
    {
        try {
            $bytes = random_bytes(16);
        } catch (Throwable $e) {
            $bytes = hash('sha256', uniqid('', true) . mt_rand(), true);
        }
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex(substr($bytes, 0, 16));
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
            . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
    }
}
