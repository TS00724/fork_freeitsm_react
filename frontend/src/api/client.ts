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

export interface UiApiClientOptions {
  baseUrl: string;
  getCsrfToken?: () => string | null | undefined;
  fetchImplementation?: typeof fetch;
}

export interface UiApiRequestOptions extends RequestInit {
  expect?: 'json' | 'text' | 'response';
}

const SAFE_METHODS = new Set(['GET', 'HEAD', 'OPTIONS']);

function joinEndpoint(baseUrl: string, path: string): string {
  const base = baseUrl.endsWith('/') ? baseUrl : `${baseUrl}/`;
  return `${base}${path.replace(/^\/+/, '')}`;
}

export function createUiApiClient(options: UiApiClientOptions) {
  const fetcher = options.fetchImplementation ?? fetch;

  async function request<T = unknown>(
    path: string,
    init: UiApiRequestOptions = {}
  ): Promise<T> {
    const method = (init.method ?? 'GET').toUpperCase();
    const headers = new Headers(init.headers);
    const csrfToken = options.getCsrfToken?.();

    if (!SAFE_METHODS.has(method) && csrfToken) headers.set('X-CSRF-Token', csrfToken);
    if (init.body && !headers.has('Content-Type') && !(init.body instanceof FormData)) {
      headers.set('Content-Type', 'application/json');
    }

    const response = await fetcher(joinEndpoint(options.baseUrl, path), {
      ...init,
      method,
      headers,
      credentials: 'same-origin'
    });
    if (init.expect === 'response') return response as T;

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
