import { describe, expect, it, vi } from 'vitest';
import { createUiApiClient } from '../src/api/client';

describe('UI API transport placeholder', () => {
  it('uses same-origin credentials and injects a supplied CSRF token for writes', async () => {
    const fetchImplementation = vi.fn<typeof fetch>().mockResolvedValue(
      new Response(JSON.stringify({ arbitrary: true }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' }
      })
    );
    const client = createUiApiClient({
      baseUrl: '/freeitsm-app/api/ui/v1/',
      getCsrfToken: () => '  review-token  ',
      fetchImplementation
    });

    await client.request('placeholder', { method: 'POST', body: '{}', expect: 'json' });
    const [url, init] = fetchImplementation.mock.calls[0] ?? [];
    expect(url).toBe('/freeitsm-app/api/ui/v1/placeholder');
    expect(init?.credentials).toBe('same-origin');
    expect(new Headers(init?.headers).get('X-CSRF-Token')).toBe('review-token');
    expect(init).not.toHaveProperty('expect');
  });

  it('preserves an unknown error body without assuming an envelope', async () => {
    const client = createUiApiClient({
      baseUrl: '/api/ui/v1/',
      fetchImplementation: vi.fn<typeof fetch>().mockResolvedValue(
        new Response('denied', { status: 403, headers: { 'Content-Type': 'text/plain' } })
      )
    });

    await expect(client.request('placeholder')).rejects.toMatchObject({
      status: 403,
      responseBody: 'denied'
    });
  });

  it('allows a safe request without a CSRF token', async () => {
    const fetchImplementation = vi.fn<typeof fetch>().mockResolvedValue(
      new Response(JSON.stringify({ ok: true }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' }
      })
    );
    const client = createUiApiClient({
      baseUrl: '/api/ui/v1/',
      fetchImplementation
    });

    await expect(client.request('health?detail=1')).resolves.toEqual({ ok: true });
    expect(fetchImplementation).toHaveBeenCalledWith(
      '/api/ui/v1/health?detail=1',
      expect.objectContaining({ credentials: 'same-origin', method: 'GET' })
    );
  });

  it.each([undefined, null, '', '   '])(
    'fails closed before an unsafe request when the CSRF token is %s',
    async (csrfToken) => {
      const fetchImplementation = vi.fn<typeof fetch>();
      const client = createUiApiClient({
        baseUrl: '/api/ui/v1/',
        getCsrfToken: () => csrfToken,
        fetchImplementation
      });

      await expect(client.request('tickets', { method: 'DELETE' })).rejects.toThrow('CSRF token');
      expect(fetchImplementation).not.toHaveBeenCalled();
    }
  );

  it.each([
    '',
    'https://example.invalid/api/ui/v1/',
    '//example.invalid/api/ui/v1/',
    '/\\example.invalid/api/ui/v1/',
    '/api/ui/%2e%2e/v1/'
  ])('rejects an unsafe API base URL: %s', (baseUrl) => {
    expect(() => createUiApiClient({ baseUrl })).toThrow();
  });

  it.each([
    '../auth',
    '%2e%2e/auth',
    '%252e%252e/auth',
    'https://example.invalid/collect',
    '//example.invalid/collect',
    '\\example.invalid/collect'
  ])('rejects an endpoint that could escape the API base: %s', async (path) => {
    const fetchImplementation = vi.fn<typeof fetch>();
    const client = createUiApiClient({
      baseUrl: '/api/ui/v1/',
      fetchImplementation
    });

    await expect(client.request(path)).rejects.toThrow();
    expect(fetchImplementation).not.toHaveBeenCalled();
  });
});
