export class UiApiHttpError extends Error {
  constructor(
    message: string,
    public readonly status: number,
    public readonly responseBody: unknown
  ) {
    super(message);
    this.name = 'UiApiHttpError';
  }
}

export class UiApiSecurityError extends Error {
  constructor(message: string) {
    super(message);
    this.name = 'UiApiSecurityError';
  }
}

export interface UiApiClientOptions {
  baseUrl: string;
  getCsrfToken?: () => string | null | undefined;
  fetchImplementation?: typeof fetch;
}

export interface UiApiRequestOptions extends RequestInit {
  expect?: 'json' | 'text' | 'response';
}

const SAFE_METHODS = new Set(['GET', 'HEAD', 'OPTIONS']);
const URL_VALIDATION_ORIGIN = 'https://freeitsm-api.invalid';

function directoryPath(pathname: string): string {
  return pathname === '/' || pathname.endsWith('/') ? pathname : `${pathname}/`;
}

function decodePathSegment(segment: string, label: string): string {
  let decoded = segment;
  for (let pass = 0; pass < 3; pass += 1) {
    let next: string;
    try {
      next = decodeURIComponent(decoded);
    } catch {
      throw new UiApiSecurityError(`${label} contains invalid percent encoding`);
    }
    if (next === decoded) break;
    decoded = next;
  }
  return decoded;
}

function containsControlCharacter(value: string): boolean {
  return [...value].some((character) => {
    const codePoint = character.codePointAt(0) ?? 0;
    return codePoint <= 0x1f || codePoint === 0x7f;
  });
}

function assertSafePathInput(value: unknown, label: string, allowQuery: boolean): string {
  if (typeof value !== 'string') throw new UiApiSecurityError(`${label} must be a string`);
  const candidate = value.trim();
  if (candidate.includes('\\') || containsControlCharacter(candidate) || /%5c/i.test(candidate)) {
    throw new UiApiSecurityError(`${label} must not contain backslashes or control characters`);
  }
  if (/^[a-z][a-z\d+.-]*:/i.test(candidate) || candidate.startsWith('//')) {
    throw new UiApiSecurityError(`${label} must remain same-origin`);
  }
  if (candidate.includes('#') || (!allowQuery && candidate.includes('?'))) {
    throw new UiApiSecurityError(`${label} contains a forbidden query or fragment`);
  }

  const pathname = candidate.split('?', 1)[0] ?? '';
  for (const segment of pathname.split('/')) {
    const decoded = decodePathSegment(segment, label);
    if (decoded === '.' || decoded === '..') {
      throw new UiApiSecurityError(`${label} must not contain dot segments`);
    }
    if (/[\\/]/.test(decoded)) {
      throw new UiApiSecurityError(`${label} must not contain encoded path separators`);
    }
  }
  return candidate;
}

function isContainedPath(pathname: string, basePathname: string): boolean {
  const base = directoryPath(basePathname);
  return base === '/' || pathname === base.slice(0, -1) || pathname.startsWith(base);
}

function parseApiBaseUrl(value: string): URL {
  const candidate = assertSafePathInput(value, 'UI API base URL', false);
  if (!candidate) throw new UiApiSecurityError('UI API base URL must not be empty');
  const parsed = new URL(candidate, URL_VALIDATION_ORIGIN);
  if (parsed.origin !== URL_VALIDATION_ORIGIN) {
    throw new UiApiSecurityError('UI API base URL must remain same-origin');
  }
  parsed.pathname = directoryPath(parsed.pathname);
  return parsed;
}

function joinEndpoint(baseUrl: URL, path: string): string {
  const candidate = assertSafePathInput(path, 'UI API endpoint', true);
  const endpoint = new URL(candidate, baseUrl);
  if (endpoint.origin !== URL_VALIDATION_ORIGIN) {
    throw new UiApiSecurityError('UI API endpoint must remain same-origin');
  }
  if (!isContainedPath(endpoint.pathname, baseUrl.pathname)) {
    throw new UiApiSecurityError('UI API endpoint must remain within the configured API base URL');
  }
  return `${endpoint.pathname}${endpoint.search}`;
}

export function createUiApiClient(options: UiApiClientOptions) {
  const fetcher = options.fetchImplementation ?? fetch;
  const apiBaseUrl = parseApiBaseUrl(options.baseUrl);

  async function request<T = unknown>(
    path: string,
    init: UiApiRequestOptions = {}
  ): Promise<T> {
    const { expect: expectedResponse, ...requestInit } = init;
    const method = (requestInit.method ?? 'GET').trim().toUpperCase();
    if (!method) throw new UiApiSecurityError('HTTP method must not be empty');
    const headers = new Headers(requestInit.headers);

    if (!SAFE_METHODS.has(method)) {
      const csrfToken = options.getCsrfToken?.();
      if (typeof csrfToken !== 'string' || !csrfToken.trim()) {
        throw new UiApiSecurityError(`A CSRF token is required for ${method} requests`);
      }
      headers.set('X-CSRF-Token', csrfToken.trim());
    }
    if (requestInit.body && !headers.has('Content-Type') && !(requestInit.body instanceof FormData)) {
      headers.set('Content-Type', 'application/json');
    }

    const response = await fetcher(joinEndpoint(apiBaseUrl, path), {
      ...requestInit,
      method,
      headers,
      credentials: 'same-origin'
    });
    if (expectedResponse === 'response') return response as T;

    const contentType = response.headers.get('Content-Type') ?? '';
    const body: unknown = response.status === 204
      ? undefined
      : contentType.includes('application/json')
        ? await response.json()
        : await response.text();

    if (!response.ok) {
      throw new UiApiHttpError(`UI API request failed with HTTP ${response.status}`, response.status, body);
    }
    return body as T;
  }

  return { request };
}
