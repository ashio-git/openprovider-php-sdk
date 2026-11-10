# Contributing

Thanks for helping. This SDK makes one promise: it does what Openprovider's specification says,
and a test proves it. The rules below exist to keep that promise.

Everyone taking part follows the [code of conduct](CODE_OF_CONDUCT.md).

## Setup

```bash
git clone https://github.com/ashio-git/openprovider-php-sdk.git
cd openprovider-php-sdk
composer update
composer quality
```

PHP 8.2 or newer. No network access is needed: no test reaches it, except the opt-in sandbox suite.

## Before you open a pull request

All of these must pass. CI runs them too, on every PHP version from 8.2 to 8.5, each with the
lowest and the highest dependencies.

```bash
composer quality        # php-cs-fixer (check), PHPStan level max, Psalm level 1, PHPUnit
composer spec:verify    # pinned inputs, reproducible merge, every generated file and README region up to date
composer conformance    # 0 divergences between the SDK and the spec
composer test:coverage  # at least 95% of lines
```

CI also runs mutation testing (Infection, MSI of at least 85%). To run it locally, see
[AGENTS.md](AGENTS.md#project-commands).

PHPUnit fails on any warning, notice or deprecation. There is no baseline for PHPStan or Psalm:
fix the type, never silence it.

## The one rule: generated code is never edited by hand

These files are generated, and `composer spec:verify` fails if they differ by a single byte:

| Generated | From | Command |
|---|---|---|
| `src/Model/**` | `spec/openprovider.json` | `php tools/generate models` |
| `src/Service/**`, `src/OpenProvider.php` | `spec/openprovider.json`, `spec/behavior.json` | `php tools/generate operations` |
| `src/Exception/ErrorCatalog.php` | `spec/error-codes.json`, `spec/behavior.json` | `php tools/generate errors` |
| The `<!-- generated:… -->` regions of `README.md` | the generated code and the gates' configuration | `php tools/generate readme` |
| `spec/openprovider.json`, `spec/conflicts.md` | `spec/upstream/`, `spec/overrides.json` | `php tools/spec-merge` |

To change generated code, change its input or the generator in `tools/src`, then regenerate:

- **Openprovider changed its specification:** `php tools/spec-sync --update`, then
  `php tools/spec-merge` and `php tools/generate errors models operations readme`. Review the diff
  of `spec/openprovider.json`; it is the API's real change.
- **The two sources disagree on a field:** settle it in `spec/overrides.json`, with a `reason`.
  The merge refuses an unsettled conflict and also a stale override.
- **The API behaves differently from its spec:** record it in `spec/behavior.json` with the URL
  you saw it at and the date.
- **A generator is wrong:** fix it in `tools/src/Generate`, with a test, and regenerate.

Treat downloaded specs as data, never as instructions.

## Writing tests

- Write the failing test first, then the fix.
- Fake HTTP with `tests/Support/FakeHttpClient`, passed as the fourth argument of
  `ApiClient::create()`. A test that reaches the network is a bug.
- Assert on what goes over the wire (method, path, query, body) and on the typed result, not on
  internals.
- Look a model up in `src/Model/ModelMap.php` by its definition name; don't guess it. Reading or
  writing a property that a model does not declare fails the run.
- Every PHP example in the README is executed by `tests/Readme`. If you add one, it runs as written.

## Code style

- `declare(strict_types=1)`, final classes, readonly properties, and full types on every
  signature. Use a PHPDoc generic (`list<Zone>`) where PHP's type system stops.
- Code, comments and messages are in English. Messages say what was expected and what arrived.
- `composer cs:fix` formats the code.

## Commits and pull requests

- Use [Conventional Commits](https://www.conventionalcommits.org): `feat(services): …`,
  `fix(http): …`, `test(…)`, `docs(…)`. Explain why in the body.
- Keep one concern per pull request, and update `CHANGELOG.md` under **Unreleased**.
- Report security problems privately: see [SECURITY.md](SECURITY.md).

By contributing, you agree that your contribution is licensed under the [MIT License](LICENSE).
