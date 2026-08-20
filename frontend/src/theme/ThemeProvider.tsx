import { EuiProvider } from '@elastic/eui';
import { EuiThemeBorealis } from '@elastic/eui-theme-borealis';
import { createContext, useContext, useMemo, useState, type ReactNode } from 'react';
import type { ColorModePreference } from '../config/runtimeConfig';

export type ResolvedColorMode = 'light' | 'dark';

interface ThemeContextValue {
  mode: ResolvedColorMode;
  preference: ColorModePreference;
  toggleMode: () => void;
}

const ThemeContext = createContext<ThemeContextValue | null>(null);

function resolveInitialMode(preference: ColorModePreference): ResolvedColorMode {
  if (preference === 'light' || preference === 'dark') return preference;
  return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function ThemeProvider({
  initialPreference,
  children
}: {
  initialPreference: ColorModePreference;
  children: ReactNode;
}) {
  const [mode, setMode] = useState<ResolvedColorMode>(() => resolveInitialMode(initialPreference));
  const value = useMemo<ThemeContextValue>(
    () => ({
      mode,
      preference: initialPreference,
      toggleMode: () => setMode((current) => (current === 'light' ? 'dark' : 'light'))
    }),
    [initialPreference, mode]
  );

  return (
    <ThemeContext.Provider value={value}>
      <EuiProvider theme={EuiThemeBorealis} colorMode={mode}>
        {children}
      </EuiProvider>
    </ThemeContext.Provider>
  );
}

export function useThemeMode(): ThemeContextValue {
  const value = useContext(ThemeContext);
  if (!value) throw new Error('ThemeProvider is missing');
  return value;
}
