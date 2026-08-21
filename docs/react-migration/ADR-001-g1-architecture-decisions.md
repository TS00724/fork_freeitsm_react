# ADR-001 — G1 frontend architecture decisions

Date: 2026-08-21  
Status: Accepted at G1; implementation remains incremental

## Context

The React/EUI foundation was created under `frontend/` while the legacy PHP UI remains authoritative. G1 exists so the user can approve the frontend boundary before any PHP UI-BFF or business-module migration starts.

## Decisions

1. **React mount prefix:** use `${BASE_URL}ui/`. The legacy PHP UI remains at its existing routes. New React routes do not take over legacy URLs until the relevant module has passed its later gate.
2. **Migration strategy:** use strangler migration. PHP and React run in parallel; unmigrated modules continue to link to legacy PHP pages.
3. **Current web server:** Apache remains the implementation target because the repository already depends on root `.htaccess` compatibility rules and callback URLs. Nginx compatibility is a future deployment target and may become preferable when a future Go/go-zero backend is introduced, but Go/go-zero is not implemented here.
4. **AppShell direction:** preserve the information architecture and user familiarity of the PHP shell, but map each responsibility to an EUI component/pattern instead of copying legacy HTML/CSS.
5. **Theme:** initial browser default is Light. Dark remains available as an explicit user choice; system preference must not silently change the first-run mode.
6. **Identity hierarchy:** future SOC identity is the level-1 identity source. FreeITSM identity/tenant/role/capability state is level-2 and must be consumed through an adapter/context boundary so SOC values can later supply or supersede local values without rewriting business features.
7. **Locales:** minimum React locale set is English (`en`), Simplified Chinese (`zh-CN`) and Traditional Chinese (`zh-TW`). Existing FreeITSM language sources remain authoritative where reusable; do not create an unnecessary duplicate translation source.
8. **Timezone:** timezone is independently configurable and must not be inferred from locale. API/storage time remains UTC-oriented; presentation uses the selected user timezone.
9. **API types:** use a hybrid contract model. Generate transport DTOs/enums from OpenAPI/JSON Schema where available, then map them to handwritten frontend domain/view models.
10. **Pilot order:** Calendar is the recommended first post-G1 vertical slice because it exercises read/write, form/modal, permission, tenant, i18n and timezone behavior with lower business complexity than Tickets. Watchtower follows as an aggregation/dashboard pilot; Tickets follows only after those platform patterns are stable.
11. **Quality gates:** combine static checks, unit/component coverage, accessibility automation, Playwright E2E/security-negative tests and explicit user Human QoS acceptance at important gates.

## Consequences

- The old PHP UI is not deleted or redirected by this ADR.
- No `/api/ui/v1` server handler is implemented by this ADR.
- No Session, CSRF, RBAC, tenant backend behavior or business feature is implemented by this ADR.
- Apache fallback for `/ui/*` must eventually be added narrowly, preserving `/api/*`, auth callbacks, setup, cron, downloads, CSAT and `/a/<token>` compatibility.
- Future Nginx/Go-zero deployment remains possible because the browser boundary is same-origin and route-prefix based rather than PHP-template based.
