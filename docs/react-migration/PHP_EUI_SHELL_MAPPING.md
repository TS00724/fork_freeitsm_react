# PHP shell → EUI mapping for G1

Date: 2026-08-21

Source reference: `includes/header.php` is the current reusable analyst header and contains the familiar top-level navigation for Inbox, Reports, Users, Assets, Settings, Logs, Knowledge, Calendar, user greeting and Logout.

WP-03 does **not** migrate those modules. This document only maps the existing information architecture to future EUI responsibilities so the React shell can feel familiar while the PHP UI remains available in parallel.

| Legacy responsibility | Existing PHP concept | EUI candidate/pattern | WP-03 action |
|---|---|---|---|
| Global product header | `.header` | `EuiHeader` | Keep current review shell; use as final shell direction |
| Header groups | `.header-nav`, `.header-right` | `EuiHeaderSection` | Preserve left/product and right/user grouping |
| Header item | `.nav-btn` | `EuiHeaderSectionItem` + EUI button/link | Map incrementally; do not copy SVG/CSS blindly |
| Inbox/Tickets | legacy `tickets/` | EUI header/nav link; later feature route | **Legacy link only until Tickets migration** |
| Reports | legacy `reporting/` | EUI header/nav link | Legacy link only |
| Users | legacy `tickets/users.php` | EUI header/nav link | Legacy link only |
| Assets | legacy `asset-management/` | EUI header/nav link | Legacy link only |
| Settings | legacy settings route | EUI header/nav link / popover | Legacy link only |
| Logs | legacy reporting logs | EUI header/nav link | Legacy link only |
| Knowledge | legacy `knowledge/` | EUI header/nav link | Legacy link only |
| Calendar | legacy Calendar entry | EUI nav link; later first pilot | Candidate first React vertical slice after BFF/security approval |
| User greeting | PHP session analyst name | `EuiAvatar`/text + `EuiPopover` | Future bootstrap/session integration; no identity fields invented here |
| Logout | PHP redirect/confirm | `EuiPopover`/button + future auth route | Remains legacy until auth contract exists |
| Main page structure | module-specific body | `EuiPageTemplate`, `EuiPageHeader`, `EuiPageTemplate.Section` | Use EUI for new React pages |
| Empty/error states | ad-hoc PHP markup | `EuiEmptyPrompt`, callouts | Shared React pattern |
| Data-heavy screens | HTML tables/custom JS | `EuiBasicTable` / `EuiDataGrid` depending interaction | Decide per migrated feature |

## Parallel-navigation rule

During strangler migration, the React shell may show a module in the same conceptual location as PHP while still linking to the legacy URL. A module becomes an internal React route only after its own vertical slice and gate are complete.

Example status model:

```text
Tickets      → Legacy PHP
Assets       → Legacy PHP
Calendar     → Legacy PHP now; first recommended pilot later
Architecture → React review route
```

This preserves user muscle memory without pretending that an unmigrated module is already React.
