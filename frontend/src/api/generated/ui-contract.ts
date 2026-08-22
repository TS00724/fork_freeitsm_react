/* eslint-disable */
/**
 * GENERATED FILE — DO NOT EDIT.
 * Source: api/ui/v1/openapi.json
 * Run: npm run contracts:generate
 */

export const UI_API_CONTRACT_VERSION = "1.0.0" as const;
export const UI_API_OPERATIONS = [
  {
    "route": "/health",
    "method": "GET",
    "operationId": "uiApiHealth"
  },
  {
    "route": "/",
    "method": "GET",
    "operationId": "uiApiIndex"
  }
] as const;
export type UiApiOperationId = typeof UI_API_OPERATIONS[number]["operationId"];

export interface UiApiError {
  readonly "code": UiApiErrorCode;
  readonly "message": string;
  readonly "details"?: Record<string, unknown>;
}

export type UiApiErrorCode = "bad_request" | "invalid_json" | "invalid_request_id" | "invalid_route_parameter" | "payload_too_large" | "unauthenticated" | "forbidden" | "not_found" | "method_not_allowed" | "conflict" | "unsupported_media_type" | "validation_failed" | "rate_limited" | "server_error";

export interface UiApiErrorResponse {
  readonly "error": UiApiError;
  readonly "meta": UiApiResponseMeta;
}

export interface UiApiHealthData {
  readonly "status": "ok";
  readonly "service": "freeitsm-ui-api";
  readonly "version": "1";
}

export interface UiApiHealthResponse {
  readonly "data": UiApiHealthData;
  readonly "meta": UiApiResponseMeta;
}

export interface UiApiIndexData {
  readonly "name": "FreeITSM UI API";
  readonly "version": "1";
  readonly "stage": string;
  readonly "openapi": string;
  readonly "routes": ReadonlyArray<string>;
  readonly "securityBoundary": string;
  readonly "machineApiBoundary": string;
}

export interface UiApiIndexResponse {
  readonly "data": UiApiIndexData;
  readonly "meta": UiApiResponseMeta;
}

export interface UiApiResponseMeta {
  readonly "apiVersion": "1";
  readonly "requestId": string;
  readonly "correlationId": string;
  readonly "timestamp": string;
}
