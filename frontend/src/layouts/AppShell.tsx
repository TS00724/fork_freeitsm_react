import {
  EuiButtonIcon,
  EuiHeader,
  EuiHeaderSection,
  EuiHeaderSectionItem,
  EuiPageTemplate,
  EuiText,
  EuiTitle,
  EuiToolTip
} from '@elastic/eui';
import { NavLink, Outlet } from 'react-router-dom';
import { useRuntimeConfig } from '../config/RuntimeConfigProvider';
import { useThemeMode } from '../theme/ThemeProvider';

const navigation = [
  { to: '/', label: 'Foundation' },
  { to: '/architecture', label: 'Architecture review' },
  { to: '/forbidden', label: '403 skeleton' },
  { to: '/error', label: 'Error skeleton' },
  { to: '/missing-route', label: '404 skeleton' }
];

export function AppShell() {
  const config = useRuntimeConfig();
  const { mode, toggleMode } = useThemeMode();

  return (
    <>
      <EuiHeader role="banner">
        <EuiHeaderSection grow={false}>
          <EuiHeaderSectionItem>
            <EuiTitle size="xs"><h1>FreeITSM React/EUI</h1></EuiTitle>
          </EuiHeaderSectionItem>
        </EuiHeaderSection>
        <EuiHeaderSection>
          <EuiHeaderSectionItem>
            <nav className="foundationNav" aria-label="Foundation review routes">
              {navigation.map((item) => (
                <NavLink
                  key={item.to}
                  to={item.to}
                  end={item.to === '/'}
                  className={({ isActive }) =>
                    isActive ? 'foundationNav__link isActive' : 'foundationNav__link'
                  }
                >
                  {item.label}
                </NavLink>
              ))}
            </nav>
          </EuiHeaderSectionItem>
        </EuiHeaderSection>
        <EuiHeaderSection side="right" grow={false}>
          <EuiHeaderSectionItem>
            <EuiToolTip content={`Switch to ${mode === 'light' ? 'dark' : 'light'} mode`}>
              <EuiButtonIcon
                aria-label={`Switch to ${mode === 'light' ? 'dark' : 'light'} mode`}
                iconType={mode === 'light' ? 'moon' : 'sun'}
                onClick={toggleMode}
              />
            </EuiToolTip>
          </EuiHeaderSectionItem>
        </EuiHeaderSection>
      </EuiHeader>
      <EuiPageTemplate restrictWidth={1200} paddingSize="l">
        <EuiPageTemplate.Section>
          <EuiText size="s" color="subdued">
            Runtime basename: <code>{config.routerBasePath}</code>
          </EuiText>
          <Outlet />
        </EuiPageTemplate.Section>
      </EuiPageTemplate>
    </>
  );
}
