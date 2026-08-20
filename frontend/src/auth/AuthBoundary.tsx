import { createContext, useContext, type ReactNode } from 'react';

export interface AuthBoundaryState {
  status: 'unresolved';
  identity: null;
}

const unresolvedAuth: AuthBoundaryState = { status: 'unresolved', identity: null };
const AuthContext = createContext<AuthBoundaryState | null>(null);

export function AuthBoundary({ children }: { children: ReactNode }) {
  return <AuthContext.Provider value={unresolvedAuth}>{children}</AuthContext.Provider>;
}

export function useAuthBoundary(): AuthBoundaryState {
  const value = useContext(AuthContext);
  if (!value) throw new Error('AuthBoundary is missing');
  return value;
}
