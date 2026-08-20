import { Route, Routes } from 'react-router-dom';
import { AppShell } from '../layouts/AppShell';
import { ArchitectureReviewPage } from '../pages/ArchitectureReviewPage';
import { ErrorStatePage } from '../pages/ErrorStatePage';
import { ForbiddenPage } from '../pages/ForbiddenPage';
import { HomePage } from '../pages/HomePage';
import { NotFoundPage } from '../pages/NotFoundPage';

export function AppRouter() {
  return (
    <Routes>
      <Route element={<AppShell />}>
        <Route index element={<HomePage />} />
        <Route path="architecture" element={<ArchitectureReviewPage />} />
        <Route path="forbidden" element={<ForbiddenPage />} />
        <Route path="error" element={<ErrorStatePage />} />
        <Route path="*" element={<NotFoundPage />} />
      </Route>
    </Routes>
  );
}
