import { createContext, useContext, useMemo, type ReactNode } from 'react';

interface ExtensionPointsContextValue {
  locale: string;
  timezone: string;
  translate: (key: string, fallback: string) => string;
  formatDateTime: (value: Date | string | number) => string;
}

const ExtensionPointsContext = createContext<ExtensionPointsContextValue | null>(null);

export function ExtensionPointsProvider({
  locale,
  timezone,
  children
}: {
  locale: string;
  timezone: string;
  children: ReactNode;
}) {
  const value = useMemo<ExtensionPointsContextValue>(() => {
    const formatter = new Intl.DateTimeFormat(locale, {
      dateStyle: 'medium',
      timeStyle: 'short',
      timeZone: timezone
    });
    return {
      locale,
      timezone,
      // WP-02 intentionally does not duplicate the existing PHP translation source.
      translate: (_key, fallback) => fallback,
      formatDateTime: (input) => formatter.format(new Date(input))
    };
  }, [locale, timezone]);

  return (
    <ExtensionPointsContext.Provider value={value}>
      {children}
    </ExtensionPointsContext.Provider>
  );
}

export function useExtensionPoints(): ExtensionPointsContextValue {
  const value = useContext(ExtensionPointsContext);
  if (!value) throw new Error('ExtensionPointsProvider is missing');
  return value;
}
