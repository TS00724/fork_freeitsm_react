/* eslint-disable */
/**
 * GENERATED FILE — DO NOT EDIT.
 * Source: api/ui/v1/openapi-v1.json
 * Generator: frontend/scripts/generate-ui-v1-contract.mjs
 *
 * These are transport DTOs/enums, not React domain or view models.
 */

export interface UiApiMeta {
  readonly apiVersion: "1";
  readonly requestId: string;
  readonly correlationId: string;
  readonly timestamp: string;
}

export type UiApiErrorCode = "invalid_method" | "invalid_path" | "invalid_host" | "invalid_json" | "invalid_route_parameter" | "unauthenticated" | "forbidden" | "csrf_failed" | "csrf_origin_failed" | "password_change_required" | "not_found" | "method_not_allowed" | "conflict" | "unsupported_media_type" | "validation_failed" | "rate_limited" | "server_error";

export interface UiApiError {
  readonly code: UiApiErrorCode;
  readonly message: string;
  readonly details?: { readonly [key: string]: unknown; };
}

export interface UiApiErrorEnvelope {
  readonly error: UiApiError;
  readonly meta: UiApiMeta;
}

export interface UiApiActor {
  readonly id: number;
  readonly username: string;
  readonly displayName: string;
  readonly email: string;
  readonly isAdmin: boolean;
  readonly authSource: "local" | "ldap" | "oidc" | "external";
}

export interface UiApiTenant {
  readonly id: number;
  readonly name: string;
  readonly slug: string;
}

export interface UiApiCsrf {
  readonly headerName: "X-CSRF-Token";
  readonly token: string;
}

export interface UiApiLinks {
  readonly login: string;
  readonly logout: string;
  readonly passwordChange: string;
}

export interface UiApiAnonymousSession {
  readonly authenticated: false;
  readonly links: UiApiLinks;
}

export interface UiApiAuthenticatedSession {
  readonly authenticated: true;
  readonly actor: UiApiActor;
  readonly activeTenant: UiApiTenant;
  readonly availableTenants: UiApiTenant[];
  readonly capabilities: string[];
  readonly modules: string[];
  readonly locale: string;
  readonly timezone: string;
  readonly csrf: UiApiCsrf;
  readonly links: UiApiLinks;
}

export type UiApiSessionData = UiApiAnonymousSession | UiApiAuthenticatedSession;

export interface UiApiSessionEnvelope {
  readonly data: UiApiSessionData;
  readonly meta: UiApiMeta;
}

export interface UiApiAuthenticatedSessionEnvelope {
  readonly data: UiApiAuthenticatedSession;
  readonly meta: UiApiMeta;
}

export interface UiApiTenantSwitchCommand {
  readonly tenantId: number;
}

export interface UiApiFoundationData {
  readonly name: string;
  readonly version: "v1";
  readonly surface: "same-origin-browser-bff";
  readonly authentication: "php-session";
  readonly routes: string[];
  readonly security: { readonly [key: string]: string; };
  readonly [key: string]: unknown;
}

export interface UiApiFoundationEnvelope {
  readonly data: UiApiFoundationData;
  readonly meta: UiApiMeta;
}

export interface UiApiHealthData {
  readonly status: "ok";
  readonly scope: "ui-api-process";
  readonly checks: { readonly database: "not_checked"; readonly session: "not_checked"; };
}

export interface UiApiHealthEnvelope {
  readonly data: UiApiHealthData;
  readonly meta: UiApiMeta;
}
