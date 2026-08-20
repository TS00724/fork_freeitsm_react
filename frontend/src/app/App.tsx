import type { RuntimeConfig } from '../config/runtimeConfig';
import { ErrorBoundary } from './ErrorBoundary';
import { AppProviders } from './providers/AppProviders';
import { AppRouter } from './router';

export function App({ config }: { config: RuntimeConfig }) {
  return (
    <ErrorBoundary>
      <AppProviders config={config}>
        <AppRouter />
      </AppProviders>
    </ErrorBoundary>
  );
}
