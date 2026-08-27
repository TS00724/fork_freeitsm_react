<?php

declare(strict_types=1);

final class UiApiSecurityRuntime
{
    private UiApiSessionStore $session;
    private UiApiSecurityProvider $provider;
    private UiApiCsrfGuard $csrf;

    public function __construct(
        UiApiSessionStore $session,
        UiApiSecurityProvider $provider,
        UiApiCsrfGuard $csrf
    ) {
        $this->session = $session;
        $this->provider = $provider;
        $this->csrf = $csrf;
    }

    public function authorize(
        UiApiRouteSecurity $policy,
        UiApiRequest $request,
        UiApiRequestContext $context,
        array $parameters
    ): UiApiRequestContext {
        if (!$policy->usesSession()) return $context;

        $this->session->start();
        $analystId = (int) $this->session->get('analyst_id', 0);
        if ($analystId <= 0) {
            if ($policy->sessionRequired()) throw UiApiException::unauthorized();
            return $context->withSecurityState($this->anonymousState());
        }

        if (!empty($this->session->get('password_expired', false))) {
            throw UiApiException::passwordChangeRequired($this->provider->links()['passwordChange'] ?? null);
        }

        // Fail CSRF before database-backed actor, tenant or RBAC resolution.
        if ($policy->csrfRequired()) $this->csrf->validate($request, $this->session);

        $state = $this->provider->resolve($analystId, $this->session);
        if ($state === null) {
            throw UiApiException::unauthorized('The authenticated Session is no longer valid.');
        }

        $this->enforcePolicy($policy, $state, $parameters);
        if ($policy->issueCsrf()) {
            $state['csrfToken'] = $this->csrf->issue($this->session);
            $state['csrfHeader'] = UiApiCsrfGuard::HEADER_NAME;
        }
        return $context->withSecurityState($state);
    }

    /** @return array<string,mixed> */
    public function sessionPayload(UiApiRequestContext $context): array
    {
        if (!$context->isAuthenticated()) {
            return ['authenticated' => false, 'links' => $this->provider->links()];
        }
        return $this->payloadFromContext($context);
    }

    /** @return array<string,mixed> */
    public function switchTenant(
        UiApiRequest $request,
        UiApiRequestContext $context,
        array $parameters
    ): array {
        unset($parameters);
        $body = $request->jsonObject();
        $tenantId = $body['tenantId'] ?? null;
        if (!is_int($tenantId) && !(is_string($tenantId) && ctype_digit($tenantId))) {
            throw UiApiException::validation('tenantId must be a positive integer.', ['field' => 'tenantId']);
        }
        $tenantId = (int) $tenantId;
        if ($tenantId <= 0) {
            throw UiApiException::validation('tenantId must be a positive integer.', ['field' => 'tenantId']);
        }

        $actor = $context->actor();
        $analystId = (int) ($actor['id'] ?? 0);
        if ($analystId <= 0) throw UiApiException::unauthorized();

        $state = $this->provider->switchTenant($analystId, $tenantId, $this->session);
        $state['csrfToken'] = $this->csrf->rotate($this->session);
        $state['csrfHeader'] = UiApiCsrfGuard::HEADER_NAME;
        return $this->payloadFromState($state);
    }

    private function enforcePolicy(UiApiRouteSecurity $policy, array $state, array $parameters): void
    {
        $capability = $policy->capability();
        if ($capability !== null && !in_array($capability, $state['capabilities'] ?? [], true)) {
            throw UiApiException::forbidden('The authenticated actor lacks the required capability.');
        }

        $module = $policy->module();
        if ($module !== null && !in_array($module, $state['modules'] ?? [], true)) {
            throw UiApiException::forbidden('The authenticated actor cannot access the required module.');
        }

        $tenantParameter = $policy->tenantParameter();
        if ($tenantParameter !== null) {
            $tenantId = (int) ($parameters[$tenantParameter] ?? 0);
            $allowedIds = array_map('intval', $state['accessibleTenantIds'] ?? []);
            if ($tenantId <= 0 || !in_array($tenantId, $allowedIds, true)) {
                throw UiApiException::forbidden('The authenticated actor cannot access that company.');
            }
        }

        $objectScope = $policy->objectScope();
        if ($objectScope !== null && !$objectScope($state, $parameters)) {
            if ($policy->hideObjectDenial()) throw UiApiException::notFound();
            throw UiApiException::forbidden('The authenticated actor cannot access that object.');
        }
    }

    private function anonymousState(): array
    {
        return ['authenticated' => false, 'links' => $this->provider->links()];
    }

    private function payloadFromContext(UiApiRequestContext $context): array
    {
        return [
            'authenticated' => true,
            'actor' => $context->actor(),
            'activeTenant' => $context->tenant(),
            'availableTenants' => $context->availableTenants(),
            'capabilities' => $context->capabilities(),
            'modules' => $context->modules(),
            'locale' => $context->locale(),
            'timezone' => $context->timezone(),
            'csrf' => ['headerName' => $context->csrfHeader(), 'token' => $context->csrfToken()],
            'links' => $context->links(),
        ];
    }

    private function payloadFromState(array $state): array
    {
        return [
            'authenticated' => true,
            'actor' => $state['actor'],
            'activeTenant' => $state['tenant'],
            'availableTenants' => $state['availableTenants'],
            'capabilities' => $state['capabilities'],
            'modules' => $state['modules'],
            'locale' => $state['locale'],
            'timezone' => $state['timezone'],
            'csrf' => ['headerName' => $state['csrfHeader'], 'token' => $state['csrfToken']],
            'links' => $state['links'],
        ];
    }
}
