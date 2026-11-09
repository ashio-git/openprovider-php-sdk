# Spec provenance

Where every input of the 3.0 SDK comes from, pinned by commit and sha256, and the policy that turns them into `openprovider.json` ([merge policy](#merge-policy)).

All upstream files are untrusted data. The tools parse them as JSON and never execute them. They are Openprovider's, not covered by this project's license: see [NOTICE](../NOTICE).

## Merge policy

1. **Operations:** exactly the operations of the developer portal (`developer.openprovider.com`), the current reference. Operations that exist only in a newer `api-documentation` release are left out until the portal documents them, and no SDK method exists outside this set.
2. **Schemas:** from the per-service `swagger/*.swagger.json` files of `openprovider/api-documentation`, at a pinned commit. Each definition is prefixed with its service (`ssl.orderCreateOrderRequest`), so two services can never overwrite each other's schema. What only the portal describes (Mailcow white-label, the reseller signup and their schemas) comes from the portal, prefixed `portal.` and marked `x-experimental`.
3. **A schema in both sources:** the union of their fields, because the live API already returns the fields of the newer release. Two types for one field are a conflict: the merge fails until `overrides.json` settles it, with a reason, and every decision is listed in `conflicts.md`.
4. **Behaviour the Swagger files do not describe** (token lifetime, rate limits, paging maxima, time zone, error classes): in `behavior.json`, each fact with its source URL and access date.
5. **Versioned snapshot:** the inputs live here with `SHA256SUMS` and this file. A weekly job compares them with upstream and opens an issue when anything changed.

## Files

| File | Written by | What it is |
|---|---|---|
| `sources.json` | hand, then `tools/spec-sync --update` | URLs, pinned commit and retrieval metadata |
| `upstream/api-documentation/swagger/*.swagger.json` | `tools/spec-sync --update` | the 11 per-service specs, byte for byte |
| `upstream/developer-portal/swagger.spec.js` | `tools/spec-sync --update` | the portal bundle, byte for byte |
| `SHA256SUMS` | both tools | sha256 of every input and of the merge output; `cd spec && sha256sum -c SHA256SUMS` |
| `overrides.json` | hand | justified decisions the merge refuses to take alone |
| `openprovider.json` | `tools/spec-merge` | the merged Swagger 2.0 spec: 140 operations, `basePath: /v1` |
| `conflicts.md` | `tools/spec-merge` | every decision of the last merge |
| `error-codes.json` | once, from the knowledge base | the 608 API error codes of the knowledge base |
| `behavior.json` | hand | API behaviour the Swagger files do not describe, each fact with sources |

## 1. Per-service specs: `openprovider/api-documentation`

- Repository: <https://github.com/openprovider/api-documentation>, branch `v1beta`. It is the build output behind docs.openprovider.com.
- Pinned commit: `e0bcf22e81c1247a8c6cdccf486c1673ef2aed10`, "New release Tue Aug 25 18:35:00 CEST 2026" (committed 2026-08-25T16:35:41Z, merged by PR #6 on 2026-08-26).
- URL pattern: `https://raw.githubusercontent.com/openprovider/api-documentation/<commit>/swagger/<service>.swagger.json`.
- Retrieved: 2026-11-09. On that day the head of `v1beta` was `e7742139b438525955e21f9cc0957ab2b84402a5` (2026-09-07, banner changes only); its `swagger/` tree has the same blobs as the pinned commit.
- Licence: the repository declares none. The files are vendored only to make the merge reproducible; `/spec` is `export-ignore`, so they never ship in the package.

| File | sha256 | git blob |
|---|---|---|
| `auth.swagger.json` | `8ca40fc38b1037caee01cc53515262404f65c55d850d6637608667d0bbaafb24` | `20ac02bb6d40059f07cc4ac3610910b6ee4b4b29` |
| `billing.swagger.json` | `25044189f8202a681994abbbccc340da4af26fef7dc9e58f6239305345c50b2a` | `9529f0f888bb4f2648f63126d9b9aba9ae86563e` |
| `dns.swagger.json` | `3cfc9e68f78c4806d68afdd88847aa2bf7869d29ad405589e76e1cdeecae3a2e` | `13d62a1f1d39d0fb686a58e0d08276e9a8a56a8e` |
| `domain.swagger.json` | `d59a9a83a7ac8b29fdaabdb107d933aafb5c047af165da606523c8b002180f13` | `96fce19bea745880008a7557e7b7277fdad93977` |
| `easydmarc.swagger.json` | `0472e6ef8b0f92b99b763e6b7eace571d10b48253c21982fddfbdaf647dd9365` | `c2995db4551fb4ef08e8690c021af2d8b9c5cb7c` |
| `email-template.swagger.json` | `ab686451089d1e681379284ea233239b60b330697a16842988c60475752c5413` | `28d31b5f880d58ac2b2d9299f263de34ec38c538` |
| `license.swagger.json` | `b624db38383c2f0d5e84a71870e118cdc89131a90cc75414b86eb39350ae9c28` | `f2c4331fbeb100e99b75d29b3de1c190b523b232` |
| `mailcow.swagger.json` | `e30239d3e235d554811e27af2f69ee4d51fdfd88f35490c38a80ed21420af15c` | `99257b38c1fe1f3e2fb3d03e46a627a35ad26334` |
| `reseller-customer.swagger.json` | `bb55c76de48f1440be88b8d4d412bec354f0e29f42e45bf7dbe14daf293df3a9` | `45ceba2e087427f75ea70c2157a77ecfb103b1ff` |
| `spam-expert.swagger.json` | `abd0f94b1136ebea936f372232f0bdfa8cc3c0473ac75a9f21841c56bee0dca5` | `15c0b698adbd79aa10dcf3150ae3320147c5dacd` |
| `ssl.swagger.json` | `64eedf800a540758eddcbe43045dc178fe5b6eca39bac50895c6f4f45b3eb5a9` | `8b6134db470831e95b34b2d5622059f97e99c1b2` |

Why these files and not the composed `swagger.json` of the same commit: in the composed file Mailcow's `orderCreateOrderRequest` overwrites the SSL one, so `POST /ssl/orders` points at a two-field schema. The per-service `ssl.swagger.json` has the full 15-field schema. The merge prefixes every definition with its service (`ssl.orderCreateOrderRequest`, `mailcow.orderCreateOrderRequest`), so the collision cannot happen again.

## 2. Developer portal: `swagger.spec.js`

- URL: <https://developer.openprovider.com/data/swagger.spec.js>, a single statement `window.OP_API_SPEC = {…};`. `tools/spec-sync` refuses any other shape.
- Retrieved: 2026-11-09. `ETag: "6ad68276-a8934"`, `Last-Modified: Mon, 19 Oct 2026 20:49:58 GMT`. Every portal file carries that timestamp, so it is a site deploy date, not a spec date.
- sha256: `3bfab3f054dff866603f2481e268d437a9d0283171f00aea25949c35960796f1`.
- Its JSON equals <https://developer.openprovider.com/data/swagger.json> (`ETag: "6ad68276-b2297"`).
- Content: the core of api-documentation release `29cd544dee` (2025-10-09) with `/v1` paths, plus the Mailcow white-label operations and `POST /resellers/signup`. It is the current reference, whose operations define the SDK.
- What the merge takes from it: the operation set, the operationIds, tags and tag groups, and whatever only it describes (5 operations, 18 definitions, marked `x-experimental`). Fields it shares with the per-service specs are united with them.

## 3. Error codes: `error-codes.json`

- Source: <https://openprovider.help/books/api-integrations/page/api-error-codes>, revision #9 (updated 2026-08-19), accessed 2026-11-09.
- Parsed from that page's table (609 rows). Normalised here: keyed by code and sorted; code 25007 appears twice with identical text and is kept once, leaving 608 codes.

## 4. Behaviour: `behavior.json`

Token lifetime, the 196 re-login rule, environments, the `/v1beta` sunset, rate limits, paging maxima, timezone, idempotency and the classification of error codes into exception classes. Each fact lists its sources; all were accessed on 2026-11-09.

## Updating

1. The weekly job (`.github/workflows/spec-drift.yml`) runs `tools/spec-sync --check-upstream` and opens or updates an issue when the branch or the portal serves different bytes.
2. `php tools/spec-sync --update --commit=<new sha>` downloads the new inputs and rewrites `SHA256SUMS` and `sources.json`.
3. `php tools/spec-merge` writes `openprovider.json` and `conflicts.md`. New type conflicts or name collisions fail the merge until `overrides.json` settles them with a reason.
4. Update the tables above (a test checks that every sha256 in `SHA256SUMS` appears here), then run `composer quality`.
