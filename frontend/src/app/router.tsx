import { lazy, Suspense, type ReactNode } from 'react';
import { Route, Routes } from 'react-router-dom';
import { AppShell } from '../layouts/AppShell';

const HomePage = lazy(() =>
  import('../pages/HomePage').then((module) => ({ default: module.HomePage }))
);
const ArchitectureReviewPage = lazy(() =>
  import('../pages/ArchitectureReviewPage').then((module) => ({
    default: module.ArchitectureReviewPage
  }))
);
const ForbiddenPage = lazy(() =>
  import('../pages/ForbiddenPage').then((module) => ({ default: module.ForbiddenPage }))
);
const ErrorStatePage = lazy(() =>
  import('../pages/ErrorStatePage').then((module) => ({ default: module.ErrorStatePage }))
);
const NotFoundPage = lazy(() =>
  import('../pages/NotFoundPage').then((module) => ({ default: module.NotFoundPage }))
);

function LazyRoute({ children }: { children: ReactNode }) {
  return (
    <Suspense fallback={<p role="status">Loading page…</p>}>
      {children}
    </Suspense>
  );
}

export function AppRouter() {
  return (
    <Routes>
      <Route element={<AppShell />}>
        <Route index element={<LazyRoute><HomePage /></LazyRoute>} />
        <Route path="architecture" element={<LazyRoute><ArchitectureReviewPage /></LazyRoute>} />
        <Route path="forbidden" element={<LazyRoute><ForbiddenPage /></LazyRoute>} />
        <Route path="error" element={<LazyRoute><ErrorStatePage /></LazyRoute>} />
        <Route path="*" element={<LazyRoute><NotFoundPage /></LazyRoute>} />
      </Route>
    </Routes>
  );
}
