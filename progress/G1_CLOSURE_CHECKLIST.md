# G1 closure checklist — user + GPT Pro

- Source under review: `46c901597557abe7f319a880c9a3539105307196`
- Status: **Pending**
- Rule: do not start WP-04/WP-05 until all required rows have a recorded result.

## 1. Browser automation

Run on a machine that can download Playwright's pinned browsers:

```bash
cd frontend
npm ci
npx playwright install
npm run test:e2e
npm run test:a11y
```

| Field | Value |
|---|---|
| Date / operator | Pending |
| Node / npm | Pending |
| Chromium | Pending |
| Firefox | Pending |
| WebKit | Pending |
| axe serious/critical | Pending |
| Evidence path or pasted summary | Pending |

Do not convert a download or launch failure into a test pass. Record the exact
failed command and environment if the binaries remain unavailable.

## 2. User Human QoS

Start the reviewed built artifact:

```bash
cd frontend
npm run build
npm run preview:test
```

Open `http://127.0.0.1:4173/ui/` and review:

- `/ui/` shell clarity and initial Light theme;
- theme toggle;
- direct `/ui/forbidden` and unknown-route behavior;
- browser back/forward and refresh;
- keyboard navigation and visible focus;
- desktop/narrow viewport readability;
- error wording and absence of console errors;
- perceived first-load performance, noting the 510.78 kB gzip main-chunk risk;
- code readability in `runtimeConfig.ts`, `api/client.ts`, providers, router,
  AppShell, tests, and ADRs.

| Field | Value |
|---|---|
| Reviewer / date | Pending |
| Result (`Pass`, `Needs tuning`, `Block`) | Pending |
| Requested adjustments | Pending |

## 3. EUI/Elastic license decision

Read `docs/react-migration/THIRD_PARTY_REVIEW.md` and
`frontend/THIRD_PARTY_NOTICES.md`.

| Field | Value |
|---|---|
| Owner/legal reviewer / date | Pending |
| Intended deployment (`self-hosted`, `distributed`, `managed service`, or combination) | Pending |
| Result (`Accepted`, `Rejected`, `Needs legal review`) | Pending |
| Conditions / notice requirements | Pending |

## 4. Final release decision

When sections 1–3 are complete, update:

1. `progress/VERIFICATION_REPORT.md`;
2. `progress/WORK_PROGRESS.md`;
3. `handoffs/WP-02.md` and `handoffs/WP-03.md`; and
4. this file's Status.

Only then may the next prompt authorize WP-04/WP-05. Apache `/ui/*` fallback
must remain separately labelled unimplemented until its own narrow server change
and legacy-route regression checks exist. Do not create GitHub Actions or a pull
request, and do not begin Go/go-zero, clustering, or any feature pilot.
