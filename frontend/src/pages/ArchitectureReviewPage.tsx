import { EuiCallOut, EuiDescriptionList, EuiSpacer, EuiTitle } from '@elastic/eui';
import { useExtensionPoints } from '../i18n/ExtensionPointsProvider';

export function ArchitectureReviewPage() {
  const extensions = useExtensionPoints();
  const items = [
    { title: 'Frontend source', description: 'frontend/src only' },
    { title: 'Build output', description: 'frontend/dist only' },
    { title: 'Router', description: 'BrowserRouter with runtime basename' },
    { title: 'API layer', description: 'Transport placeholder; zero endpoint contracts' },
    { title: 'Locale extension', description: extensions.locale },
    { title: 'Timezone extension', description: extensions.timezone }
  ];

  return (
    <section>
      <EuiSpacer size="l" />
      <EuiTitle><h2>Architecture decisions for G1</h2></EuiTitle>
      <EuiSpacer size="m" />
      <EuiDescriptionList listItems={items} type="column" />
      <EuiSpacer size="l" />
      <EuiCallOut title="User approval required" color="warning" iconType="warning">
        <p>Review routes, provider order, runtime config, EUI licensing, and build output before proceeding.</p>
      </EuiCallOut>
    </section>
  );
}
