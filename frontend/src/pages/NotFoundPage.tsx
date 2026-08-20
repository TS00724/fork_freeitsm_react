import { EuiButton, EuiEmptyPrompt, EuiSpacer } from '@elastic/eui';
import { useNavigate } from 'react-router-dom';

export function NotFoundPage() {
  const navigate = useNavigate();

  return (
    <>
      <EuiSpacer size="xl" />
      <EuiEmptyPrompt
        iconType="search"
        title={<h2>404 — Route not found</h2>}
        body={<p>The foundation router reached its explicit wildcard route.</p>}
        actions={<EuiButton onClick={() => navigate('/')}>Return to foundation</EuiButton>}
      />
    </>
  );
}
