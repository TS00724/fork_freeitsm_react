import { cleanup, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, it } from 'vitest';
import { App } from '../src/app/App';
import { loadRuntimeConfig } from '../src/config/runtimeConfig';

const config = loadRuntimeConfig({ baseUrl: '/', appPath: 'ui/', colorMode: 'light' });

afterEach(() => cleanup());

function renderPath(path: string) {
  window.history.pushState({}, '', path);
  return render(<App config={config} />);
}

describe('foundation routes', () => {
  it('renders the AppShell and lazy foundation page at the configured basename', async () => {
    renderPath('/ui/');
    screen.getByRole('banner');
    await screen.findByRole('heading', { name: /WP-02 architecture review shell/i });
  });

  it('makes the lazy 403 skeleton directly reachable', async () => {
    renderPath('/ui/forbidden');
    await screen.findByRole('heading', { name: /403/i });
  });

  it('routes an unknown deep link to the lazy 404 skeleton', async () => {
    renderPath('/ui/nested/not-real');
    await screen.findByRole('heading', { name: /404/i });
  });

  it('toggles the EUI color mode control while the route chunk loads', async () => {
    const user = userEvent.setup();
    renderPath('/ui/');
    await user.click(screen.getByRole('button', { name: /Switch to dark mode/i }));
    screen.getByRole('button', { name: /Switch to light mode/i });
  });
});
