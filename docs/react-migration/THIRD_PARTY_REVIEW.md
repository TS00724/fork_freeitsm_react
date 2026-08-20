# Third-party review — WP-02

This is an engineering inventory, not a legal opinion.

## Selected compatibility set

| Package | Pin | Evidence/notes |
|---|---:|---|
| React / React DOM | 18.3.1 | React 18 required by the migration plan |
| `@elastic/eui` | 119.0.0 | Official manifest permits React 17 or 18, TypeScript 5, and Node 20+ |
| `@elastic/eui-theme-borealis` | 8.0.0 | EUI peer dependency |
| Vite | 7.3.6 | Requires modern Node; frontend declares Node >=20.19 |
| TypeScript | 5.8.3 | Strict mode enabled |

Registry access was unavailable, so the exact resolved graph and notices are not yet captured in a project lockfile. Pins must be rechecked by `npm ci` after the lockfile is generated and reviewed.

## License observation

EUI's package does not declare MIT. The official EUI license text states that source may be dual licensed under SSPL v1 and Elastic License 2.0, licensed under an Apache-2.0-compatible license, or solely under Elastic License 2.0 depending on file headers. The FreeITSM root license is not changed by WP-02.

Before production distribution, the project owner must review the resolved EUI LICENSE/NOTICE files, transitive package licenses, required release notices, and organizational acceptance of the selected terms.

Status: **open G1 decision; no compatibility or legal conclusion claimed**.
