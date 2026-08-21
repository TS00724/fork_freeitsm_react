# Third-party review — G1 verification closure

This is an engineering inventory, not legal advice. It records the dependency
graph resolved by `frontend/package-lock.json`; it does not approve production
distribution or hosted use of the listed software.

## Resolved compatibility set

| Package | Resolved version | Compatibility evidence |
|---|---:|---|
| React / React DOM | 18.3.1 | React DOM requires React `^18.3.1`; EUI permits React and React DOM 17 or 18 |
| `@elastic/eui` | 119.0.0 | Its React, React DOM, TypeScript 5, Emotion 11, datemath, moment and Borealis peers are all satisfied by the lockfile |
| `@elastic/eui-theme-borealis` | 8.0.0 | Exact version required by EUI 119.0.0 |
| `@elastic/datemath` / moment | 5.0.3 / 2.30.1 | Satisfy EUI's `^5.0.2` and `^2.13.0` peer ranges |
| TypeScript | 5.8.3 | Satisfies EUI's TypeScript 5 peer range; strict mode remains enabled |
| Vite / React plugin | 7.3.6 / 5.2.0 | Plugin accepts Vite 7; the project Node range matches the relevant Vite/jsdom toolchain ranges |
| `@testing-library/jest-dom` | 6.9.1 | Selected instead of 7.x so the declared Node 20 LTS path remains valid |

The application pins direct dependencies exactly. The npm 11.9.0 lockfile is
lockfile version 3 and supplies the transitive versions and integrity hashes.
No React 19 compatibility is claimed.

## Node and package-manager baseline

`frontend/package.json` declares:

```text
Node: ^20.19.0 || ^22.13.0 || >=24.0.0
npm:  11.9.0
```

This range intentionally excludes Node 21, Node 23 and early Node 22 releases
that are admitted by a broad `>=20.19.0` declaration but are not supported by
all resolved Vite/jsdom dependencies. The lockfile must be regenerated only by
an intentional dependency update, not merely by using a different npm release.

## PrismJS security override

EUI 119.0.0 depends on `refractor@3.6.0`, whose manifest normally resolves
`prismjs` within `~1.27.0`. PrismJS versions below 1.30.0 are affected by the
DOM-clobbering issue `GHSA-x7hr-w5r2-h6wg` / `CVE-2024-53382`.

The application therefore has an explicit npm override:

```json
{
  "overrides": {
    "prismjs": "1.30.0"
  }
}
```

The current lockfile resolves PrismJS 1.30.0. On 2026-08-21, both of these
commands were actually run against that lockfile and returned zero reported
vulnerabilities:

```bash
npm audit --package-lock-only --omit=dev --json
npm audit --package-lock-only --json
```

That result is a registry snapshot, not a permanent security guarantee. The
override moves PrismJS outside refractor 3.6.0's declared `~1.27.0` range, so
`EuiCodeBlock` must remain covered by unit/build/browser tests. Do not run
`npm audit fix` blindly: npm previously proposed an incompatible downgrade to
EUI 32.3.0 instead of preserving the approved EUI 119 architecture.

## License and notice review

The FreeITSM repository is MIT licensed, but that does not relicense third-party
dependencies or their compiled copies.

| Resolved production package | Manifest / package terms | Engineering consequence |
|---|---|---|
| `@elastic/eui@119.0.0` | `SEE LICENSE IN LICENSE.txt`; package license states that the repository default is SSPL v1 / Elastic License 2.0 dual licensing unless a file header selects other stated terms | Not MIT and not an OSI-approved default; preserve the package license and EUI `NOTICE.txt` with distributed copies |
| `@elastic/eui-theme-borealis@8.0.0` | `SEE LICENSE IN LICENSE.txt`; same stated default | Preserve its package license with distributed copies |
| `@elastic/eui-theme-common@10.0.0` | `SEE LICENSE IN LICENSE.txt`; same stated default | Preserve its package license with distributed copies |
| `@elastic/esql-definitions@4.21.0` | Elastic License 2.0 | Preserve its license/notice material and include it in deployment review |

Elastic License 2.0 includes restrictions relevant to offering substantial
software functionality as a hosted or managed service. Because the target is a
FreeITSM subsystem inside a future SOC environment, the repository owner and,
where applicable, legal/compliance reviewers must explicitly decide whether the
intended self-hosted, distributed and/or managed-service deployment is accepted
under these terms.

Status: **Pending owner/legal acceptance.** Dependency installation and local
G1 verification may continue, but this document does not approve production
distribution, relicensing, or hosted/managed-service use. See
`frontend/THIRD_PARTY_NOTICES.md` for the release notice checklist.

## Reproducibility and verification boundary

Before accepting a dependency update:

1. run `npm ci` from a clean `frontend/` install and confirm the lockfile does
   not change;
2. check for peer-resolution and `EBADENGINE` warnings;
3. run the repository's actual local verification commands and record their
   exit codes in `progress/VERIFICATION_REPORT.md`;
4. run both production-only and full lockfile audits;
5. review additions to third-party license and notice material; and
6. exercise `EuiCodeBlock` whenever the PrismJS override or EUI changes.

This review does not claim that tests not recorded in the verification report
have passed. Dependency verification is local-only: do not create or depend on
GitHub Actions, and do not create a pull request as part of this closure.
