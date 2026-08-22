/* eslint-disable */
/**
 * GENERATED FILE — DO NOT EDIT.
 * Source: api/ui/v1/openapi.json
 * Generator: frontend/scripts/generate-ui-contract.mjs
 *
 * These are transport DTOs/enums, not React domain or view models.
 */

export interface UiApiMeta {
  readonly apiVersion: "1";
  readonly requestId: string;
  readonly correlationId: string;
  readonly timestamp: string;
}

export type UiApiErrorCode = "invalid_method" | "invalid_path" | "invalid_route_parameter" | "invalid_json" | "unauthenticated" | "forbidden" | "not_found" | "method_not_allowed" | "conflict" | "unsupported_media_type" | "validation_failed" | "rate_limited" | "server_error";

export interface UiApiError {
  readonly code: UiApiErrorCode;
  readonly message: string;
  readonly details?: { readonly [key: string]: unknown; };
}

export interface UiApiErrorEnvelope {
  readonly error: UiApiError;
  readonly meta: UiApiMeta;
}

export interface UiApiSecuritySlots {
  readonly actor: "unresolved";
  readonly tenant: "unresolved";
  readonly capabilities: "unresolved";
  readonly locale: "unresolved";
  readonly timezone: "unresolved";
}

export interface UiApiRootData {
  readonly name: "FreeITSM Browser UI API";
  readonly version: "v1";
  readonly surface: "same-origin-browser-bff";
  readonly authentication: "reserved-for-wp-05";
  readonly routes: string[];
  readonly security: UiApiSecuritySlots;
}

export interface UiApiHealthChecks {
  readonly database: "not_checked";
  readonly session: "not_checked";
}

export interface UiApiHealthData {
  readonly status: "ok";
  readonly scope: "ui-api-process";
  readonly checks: UiApiHealthChecks;
}

export interface UiApiRootResponse {
  readonly data: UiApiRootData;
  readonly meta: UiApiMeta;
}

export interface UiApiHealthResponse {
  readonly data: UiApiHealthData;
  readonly meta: UiApiMeta;
}
