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
  it('renders the AppShell and foundation page at the configured basename', () => {
    renderPath('/ui/');
    screen.getByRole('banner');
    screen.getByRole('heading', { name: /WP-02 architecture review shell/i });
  });

  it('makes the 403 skeleton directly reachable', () => {
    renderPath('/ui/forbidden');
    screen.getByRole('heading', { name: /403/i });
  });

  it('routes an unknown deep link to the 404 skeleton', () => {
    renderPath('/ui/nested/not-real');
    screen.getByRole('heading', { name: /404/i });
  });

  it('toggles the EUI color mode control', async () => {
    const user = userEvent.setup();
    renderPath('/ui/');
    await user.click(screen.getByRole('button', { name: /Switch to dark mode/i }));
    screen.getByRole('button', { name: /Switch to light mode/i });
  });
});
