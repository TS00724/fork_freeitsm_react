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
      getCsrfToken: () => 'review-token',
      fetchImplementation
    });

    await client.request('placeholder', { method: 'POST', body: '{}' });
    const [url, init] = fetchImplementation.mock.calls[0] ?? [];
    expect(url).toBe('/freeitsm-app/api/ui/v1/placeholder');
    expect(init?.credentials).toBe('same-origin');
    expect(new Headers(init?.headers).get('X-CSRF-Token')).toBe('review-token');
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
});
