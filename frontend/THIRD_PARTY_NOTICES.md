# Third-party notices checklist

FreeITSM's root MIT license does not replace the terms for dependencies bundled
into the React frontend. This file is a release checklist and package index; it
does not reproduce the full license texts and is not legal advice.

## Elastic production packages requiring explicit review

| Package | Resolved version | License/notice source after `npm ci` |
|---|---:|---|
| `@elastic/eui` | 119.0.0 | `node_modules/@elastic/eui/LICENSE.txt`, `node_modules/@elastic/eui/NOTICE.txt`, and its `licenses/` directory |
| `@elastic/eui-theme-borealis` | 8.0.0 | `node_modules/@elastic/eui-theme-borealis/LICENSE.txt` and its `licenses/` directory |
| `@elastic/eui-theme-common` | 10.0.0 | `node_modules/@elastic/eui-theme-common/LICENSE.txt` and its `licenses/` directory |
| `@elastic/esql-definitions` | 4.21.0 | `node_modules/@elastic/esql-definitions/LICENSE.txt` and `node_modules/@elastic/esql-definitions/NOTICE.txt` |

The EUI and theme package license files state a default dual license under
SSPL v1 and Elastic License 2.0 unless an individual file header selects other
stated terms. `@elastic/esql-definitions` declares Elastic License 2.0. These
terms are not MIT and are not an OSI-approved default for the EUI packages.

## Release requirements

Before distributing a build or offering it through the planned SOC system:

1. obtain explicit repository-owner and, where applicable, legal/compliance
   acceptance for the actual deployment model;
2. determine which dependency files are present in the shipped bundle;
3. ship the applicable complete third-party license terms and notices without
   removing or obscuring them;
4. keep the package names and resolved versions synchronized with
   `package-lock.json`; and
5. repeat the review whenever EUI, its themes, or Elastic transitive packages
   change.

Status: **Pending owner/legal acceptance.** This checklist is not approval for
production distribution or hosted/managed-service use.
