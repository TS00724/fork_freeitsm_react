import { BrowserRouter } from 'react-router-dom';
import type { ReactNode } from 'react';
import { AuthBoundary } from '../../auth/AuthBoundary';
import type { RuntimeConfig } from '../../config/runtimeConfig';
import { RuntimeConfigProvider } from '../../config/RuntimeConfigProvider';
import { ExtensionPointsProvider } from '../../i18n/ExtensionPointsProvider';
import { PermissionBoundary } from '../../permissions/PermissionBoundary';
import { TenantBoundary } from '../../tenants/TenantBoundary';
import { ThemeProvider } from '../../theme/ThemeProvider';

export function AppProviders({ config, children }: { config: RuntimeConfig; children: ReactNode }) {
  return (
    <RuntimeConfigProvider config={config}>
      <ThemeProvider initialPreference={config.colorMode}>
        <ExtensionPointsProvider locale={config.locale} timezone={config.timezone}>
          <AuthBoundary>
            <TenantBoundary>
              <PermissionBoundary>
                <BrowserRouter basename={config.routerBasePath}>
                  {children}
                </BrowserRouter>
              </PermissionBoundary>
            </TenantBoundary>
          </AuthBoundary>
        </ExtensionPointsProvider>
      </ThemeProvider>
    </RuntimeConfigProvider>
  );
}
