# Changelog

All notable changes to the OpenProvider PHP SDK will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `Pagination\Listing`: the `data` of every paged listing (and of ListTags) implements it, with `items()` (never null) and `total()`, typed by item for PHPStan and Psalm.
- `Paginator::all(..., of: Item::class)`: the items come typed as `Item` for static analysis and IDEs, and each one is checked against it.
- `Serialization\Dates`: `parse()` reads the API's date strings (`Y-m-d H:i:s`, `Y-m-d`, ISO 8601) as `DateTimeImmutable` in `Europe/Amsterdam` and refuses dates that do not exist; `format()` writes any date as the API expects it.
- `composer smoke:production`: a read-only smoke suite against a production account. Its transport refuses every write before it is sent, it never repeats a refused login, and it reports the fields the models could not type.

### Changed
- `Psr16TokenStore` keys are now 64 characters (the prefix and a hash), the longest PSR-16 guarantees every cache accepts; they were 83. Tokens cached under the old keys are not read: each process logs in once more.

### Fixed
- `Paginator` no longer ends a listing at a page shorter than the limit while `total` says more items remain, so an endpoint that caps its pages cannot cut a listing short.
- A JSON list where the specification declares an object is now an `UnexpectedResponseException`. It was read as an empty model, and `Paginator` then ended the listing at once.
- A path parameter of `.` or `..` is refused. The HTTP client resolved it as a dot segment, which sent the request to another endpoint (`deleteCustomer('..')` became `DELETE /v1/`).
- Credentials that are not valid UTF-8 are refused when they are built, with an `InvalidArgumentException`. They used to reach the login as a raw `JsonException` whose trace held the password.
- A response body that is not JSON no longer appears in exception traces: `ApiResponse::fromHttp()` marks it `#[\SensitiveParameter]` and no longer chains the `JsonException`. A truncated login answer could otherwise show the token when `zend.exception_ignore_args` is off.
- The `RequestEncoder` docblock now says that an empty list is left out of a query, where it cannot be expressed; a body still carries it.

## [3.0.0] - 2026-11-09

3.0 is a rewrite for Openprovider's `/v1` API; `/v1beta` closes on 2027-06-30. It shares no code with 2.x and has no compatibility layer. The namespace is `Ashio\OpenProvider\`.

### Added
- Every operation of the current API reference, Mailcow and the reseller signup included. They are generated from a pinned and merged copy of Openprovider's specification (`spec/`): one service per tag group, one method per operation, each with `#[Operation]`.
- Immutable models with `fromArray()`, `toArray()` and `jsonSerialize()`. Unknown fields and mistyped values are kept in `$extra`, and fixed-value lists come as constants.
- `ApiClient`:
  - PSR-18/PSR-17 transport, with Guzzle as the default;
  - `ClientConfig` with an `Environment` enum;
  - lazy login with a `TokenStore` (in memory, or PSR-16 shared between processes);
  - on code 196, a new login and one repeat, writes included;
  - retries for reads only, and only when you opt in.
- `ApiResponse` with `code`, `desc`, `data`, `warnings`, `maintenance` and `httpStatus`. Errors are classified by the API's code, never by the HTTP status, into `AuthenticationException`, `RateLimitedException`, `MaintenanceException`, `NotFoundException`, `ValidationException` and `ApiException`. The other failures are `TransportException`, `UnexpectedResponseException` and `InvalidArgumentException`. `ErrorCatalog` describes every documented code.
- `Paginator::all()`, which pages each listing at its documented maximum page size.
- Requests encoded as the specification declares: query or body per parameter, dotted names, repeated keys for lists, booleans as words, and only `null` dropped.
- Operations documented only by the developer portal are marked `@experimental`.
- Tooling: `tools/spec-sync`, `tools/spec-merge`, `tools/generate`, `tools/conformance` and `tools/coverage`; a weekly spec drift check; a sandbox smoke suite.
- A README whose numbers and operation reference are generated, and whose examples are executed by the tests.

### Changed
- PHP 8.2 or newer is required.
- The base path is `/v1`. The sandbox is `https://api.sandbox.openprovider.nl`, because the old host on port 8480 no longer answers.
- The User-Agent is `ashio-openprovider-php/<version>`.
- MIT license, copyright Marco Dantas.

### Removed
- The whole 2.x API: the `Ashio\` namespace and its hand-written services and models.
- `Account::getBalance()`, which called `GET /accounting/balance`, an endpoint no specification documents. The balance is `$openProvider->resellerCustomer()->getReseller()->data->balance`.

### Fixed
- Compared with 2.x: the 15 major and 53 minor divergences from the specification found by the 2.x audit (data never sent or never read). They are fixed by construction, and the conformance gate keeps them fixed.
- A failed login (HTTP 500 with code 196 in production) is an `AuthenticationException`, not a server error.

### Security
- Passwords and tokens are held as `Secret`s. They are masked in strings, dumps, JSON and exception traces, cannot be serialized, and the parameters that take them are marked `#[\SensitiveParameter]`.
- An unknown environment name throws instead of falling back to production.

## [2.1.1] - 2026-11-09

The last 2.x release: the 2.x baseline made green, before the 3.0 rewrite. It still talks to `/v1beta`, which Openprovider closes on 2027-06-30.

### Fixed
- `EasyDmarcOrder::fromArray()` (used by `EasyDMARC::getEasyDmarc()`) threw a fatal `Error` whenever the response contained `domain`: it called the instance method `Domain::fromArray()` statically. `domain` is now a `DomainName` (`name`, `extension`), matching the spec's `orderDomain`.
- `RenewDomainRequest::toArray()` (used by `Domain::renewDomain()`) threw a fatal `Error` when `domain` was set, because `Model\Domain` has no `toArray()`. The `domain` field of every request DTO is now a `DomainName`, matching the spec's `domainDomain`/`zoneDomain`: `ApproveTransferRequest`, `CreateDomainRequest`, `CreateZoneRequest`, `RenewDomainRequest`, `ResetAuthCodeRequest`, `SendFoa1Request`, `TryAgainLastOperationRequest`, `UpdateDomainRequest`, `UpdateZoneRequest`.
- `CreateDomainRequest::fromArray()` and `UpdateDomainRequest::fromArray()` threw on `name_servers` (static call to the instance method `Nameserver::fromArray()`).
- `Prices` and `PriceGroup` now hold `ProductPrice` (`currency`, `price`) and `LevelPrice::$price` holds `Prices`, as the spec defines; the previous types made them throw.
- Models referenced classes that did not exist: `RecentEasyDmarcAction` (now `EasyDmarcRecentAction`), `NameServersSet` (now `NameserverSet`), and `UINDocumentType` / `UpdateAdditionalData` (added from the spec).
- Paginated list methods no longer read `data.total` from a response that is not an array.
- `DnsRecordUpdates::toArray()` no longer passes a private method to `array_map()` as a callable.
- `SslOrder::fromArray()` (used by `SSL::getOrder()` and `SSL::listOrders()`) threw a `TypeError` on any payload containing `options` or `domain_validation_statuses`: a generic loop assigned their raw arrays to object-typed properties before the explicit mapping ran.
- `toArray()` dropped legitimate `0`, `0.0` and `false` values in `SettingsPaymentMethodLimit` and six `Statistics*` models (`array_filter()` without a callback); only `null` is dropped now.
- `StatisticsSpamExpertsStatistics::toArray()` returned camelCase keys instead of the API keys `fromArray()` reads.
- README no longer claims 100% test coverage or 100% API coverage: measured line coverage is 58.6% and 111 of the 140 operations in the current documentation are implemented.

### Tests
- 57 constructor calls passed the HTTP client as positional argument 3, which became `$environment` in the sandbox release; they now use the named argument `httpClient:`.
- Rewrote the model tests that read or wrote properties the models do not have (99 accesses in 17 files) or mutated readonly DTOs. They now build models from API-shaped payloads and assert real fields and round trips.
- Added regression tests for the serialization bugs above.

### Added
- `toArray()` on `ProductPrice` and `EasyDmarcRecentAction`.

### Changed
- PHPStan level 8 and Psalm now pass with no errors. Psalm is configured for a library (unused-code detection off, since public API is unused by design).
- Merged 100+ duplicated docblocks (only the last one was ever read) and added array value types to `fromArray()` / `toArray()`.
- PHPUnit configuration migrated to the PHPUnit 10.5/11.5 schema; warnings, notices and deprecations now fail the run. PHPUnit 9 dropped from `require-dev`.
- CI: one workflow testing PHP 8.1–8.5 plus lowest dependencies on 8.1, a code-quality job and a coverage job. Removed the PHP 7.2–8.0 jobs (the package requires PHP 8.1) and the Codecov uploads (no token was configured and no `coverage.xml` was produced).


## [2.1.0] - 2026-01-05

### Added
- `listLicensesWithPriceVersion()` method for license queries with pricing info
- Extracted `CsrPublicKey` to its own file for proper PSR-4 compliance

### Changed
- Reorganized repository structure for cleanliness
- Moved development/audit files to `.archive/` (gitignored)
- Updated README with comprehensive API documentation
- Updated model count: 219 actively-used models

### Fixed
- Restored 110 critical model classes that were accidentally removed
- Restored all service files to their working state
- Fixed class organization (no more multiple classes per file)

### Removed
- Removed 245 orphan model files (unused Swagger-generated files)
- Cleaned up temporary analysis scripts
- Removed redundant audit files from repository root

### Quality
- **219 models** - all actively used (0 orphans)
- **237 source files** - all passing syntax checks
- **51 test files** - comprehensive coverage
- **111 API endpoints** - 100% coverage
- **6 exception classes** - complete error handling

---

## [2.0.0] - 2025-09-02

### Changed
- **BREAKING:** The entire project namespace has been refactored to `Ashio` for branding consistency
- **BREAKING:** Complete refactoring of method signatures for type-safe Request DTOs
- **BREAKING:** Standardized all listing methods to return `PaginatedResponse<T>`

### Added
- Complete OpenProvider API implementation
- Full support for Domain management operations
- DNS zone and record management
- SSL certificate operations
- License management (Plesk and Virtuozzo)
- Customer and Reseller management
- Account and Billing operations
- Email template management
- EasyDMARC integration
- SpamExpert integration
- Comprehensive test suite with 445 tests
- 100% test coverage with 959 assertions
- PSR-12 compliant code
- PHPStan level 8 analysis
- Full type safety with PHP 8.1+
- Detailed documentation and examples

### Features
- ✅ All 172 source files tested
- ✅ 154 model classes with full coverage
- ✅ 12 service classes fully implemented
- ✅ 6 custom exception types
- ✅ Type-safe API with strict declarations
- ✅ Professional error handling
- ✅ Comprehensive PHPDoc documentation

### Quality
- Zero technical debt
- Production-ready code
- Battle-tested reliability
- Professional code standards
- Maintainable architecture

## Archive

Entries recorded before 2.1.0 was released, kept as they were written. Some of their claims were corrected in 2.1.1.

### [Unreleased]

### Quality Improvements - MAJOR UPDATE 🎉
- **ACHIEVED: PHPStan Level 8 with ZERO errors! (187 → 0, 100% fixed)** 🎉🎉🎉
- **ACHIEVED: 100% API Coverage (111/111 endpoints)** 🎉🎉🎉
- **ACHIEVED: 191 Tests Passing (+147 tests, +334% increase)** 🎉🎉🎉
- **ACHIEVED: ~85-90% Test Coverage (from <40%)** 🎉🎉🎉
- **ACHIEVED: 374 Assertions (from 127, +194% increase)** 🎉🎉🎉
- Fixed all property type declarations in OpenProvider class (9 properties)
- Added return type declarations (void) to setup methods
- Fixed generic type annotations in PaginatedResponse (SSL, ResellerCustomer)
- Added proper type hints to 40+ public methods
- Fixed array parameter types across all modules (Domain, DNS, SSL, License, EasyDMARC, Accounting)
- **Added complete PHPDoc to 162 models:**
  - Constructor parameters with array types (18 models)
  - fromArray() methods with parameter types (144 models)
  - toArray() methods with return types (144 models)
- Improved PHPDoc documentation across entire codebase
- All 142 tests passing ✅

### Session 3 Fixes (Final 25 PHPStan Errors)
- Fixed nullable parameter in OpenProvider constructor (PHP 8.4 compatibility)
- Removed redundant type checks in OpenProvider request method
- Fixed PHPDoc parameter names in License class (2 methods)
- Fixed strict comparison in DomainTokenRequest
- Added type guards for API response handling (18 locations):
  - ResellerCustomer.php: 4 offset access fixes
  - DNS.php: 6 offset access fixes
  - Domain.php: 8 offset access fixes
- Removed unused PHPStan ignore pattern
- Production-ready code quality achieved

### Session 4 - Test Coverage Expansion
- **Created 98 new tests (+223% increase from 44 to 142)**
- **Added 128 new assertions (+101% increase from 127 to 255)**
- Created comprehensive test suite for all modules:
  - OpenProvider core: 18 tests (authentication, HTTP methods, error handling)
  - Credentials: 3 tests (constructor, getters)
  - ResellerCustomer: +18 tests (contacts, customers, tags, email verification)
  - Domain: +13 tests (lifecycle, transfer, trade, restore, pricing)
  - DNS: +17 tests (zones, nameservers, groups, templates, tokens)
  - SSL: +10 tests (orders, products, certificates, approver emails)
  - License: +13 tests (Plesk & Virtuozzo operations, HWID reset)
  - EasyDMARC: +5 tests (orders, SSO, retry operations)
  - SpamExpert: +5 tests (domains, login URL generation)
- All tests use proper mocking patterns
- Comprehensive error scenario testing (401, 404, 422, 500)
- Fast test execution (<100ms for 142 tests)
- Estimated test coverage: ~70%

### Session 5 - Near-Complete Test Coverage
- **Created 49 additional tests (+34.5% increase from 142 to 191)**
- **Added 119 new assertions (+46.7% increase from 255 to 374)**
- **Achieved ~85-90% test coverage (near complete!)**
- Created comprehensive model and exception tests:
  - Exception tests: 8 tests (all exception classes, inheritance, error codes)
  - PaginatedResponse: 7 tests (pagination logic, empty/large datasets)
  - DomainName: 8 tests (fromArray/toArray, validation, international domains)
  - Contact: 8 tests (complete/minimal data, nested objects, all fields)
  - ActionResponse: 7 tests (success/failure, boolean coercion)
  - Nameserver: 11 tests (IPv4/IPv6, validation, null handling)
- All 191 tests passing with zero failures
- Fast test execution (<100ms for 191 tests)
- Production-ready test coverage achieved

### Added
- PHPStan static analysis configuration (Level 8)
- Psalm type checking configuration
- PHP-CS-Fixer code style configuration (PSR-12)
- GitHub Actions CI/CD pipeline
- Comprehensive quality check scripts in composer.json
- ResellerSettings model for reseller configuration
- ResellerStatistics model for reseller usage statistics
- DomainToken model for DNS token management
- DomainTokenRequest model for token creation
- Complete API coverage documentation (API_COVERAGE_REPORT.md)
- Contribution guidelines (CONTRIBUTING.md)
- Developer quick start guide (QUICK_START_DEV.md)
- 16-week improvement plan (PLANO_MELHORIA_SDK.md)

### Changed
- All 184 source files formatted to PSR-12 standard
- Updated composer.json with dev dependencies (PHPStan, Psalm, PHP-CS-Fixer)
- Improved type hints in Credentials class
- Enhanced imports in ResellerCustomer class
- Updated DNS class with DomainToken support

### Fixed
- Code style violations across entire codebase
- Missing type hints in multiple classes
- Import statements organization

### Infrastructure
- Multi-version PHP testing (7.2, 7.3, 7.4, 8.0, 8.1, 8.2, 8.3)
- Automated code quality checks on CI/CD
- Code coverage reporting setup

### [2.0.0] - 2025-09-02

### Changed
- **BREAKING:** The entire project namespace has been refactored to `Ashio` for branding consistency. All `use` statements must be updated.
- **BREAKING:** Complete refactoring of method signatures for all `POST` and `PUT` operations. Methods that previously accepted generic `array`s now require specific, strongly-typed Request DTOs (e.g., `DomainTransferRequest`, `DnsCreateZoneRequest`), ensuring type-safety and predictability.
- **BREAKING:** Standardized all listing methods to consistently return an `Ashio\Model\PaginatedResponse<T>` object. Access to results is now universally through the `$results` property.

### Added
- New Request DTOs to cover 100% of the API endpoints, including:
    - `DomainTransferRequest`, `DomainTradeRequest`, `DomainRestoreRequest`
    - `DnsCreateZoneRequest`, `DnsUpdateZoneRequest`, `DnsCreateTemplateRequest`
    - `SpamExpertCreateDomainRequest`, `SpamExpertUpdateDomainRequest`
    - And many others, ensuring complete coverage.

### Fixed
- Completed all Response DTOs to mirror 100% of the API specification. Critical models such as `SslOrder` and `Tld` now include all previously missing properties (`vendorOrderId`, `quarantinePeriod`, `levelPrices`, etc.).
- Resolved inconsistencies in the return types of `listNameserverGroups`, `listTags`, and `listDomainEmailVerifications`, which now use `PaginatedResponse`.

[Unreleased]: https://github.com/ashio-git/openprovider-php-sdk/compare/v3.0.0...HEAD
[3.0.0]: https://github.com/ashio-git/openprovider-php-sdk/compare/v2.1.1...v3.0.0
[2.1.1]: https://github.com/ashio-git/openprovider-php-sdk/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/ashio-git/openprovider-php-sdk/releases/tag/v2.1.0
[2.0.0]: https://github.com/ashio-git/openprovider-php-sdk/tree/877f5cbfa4dab079e978b2c88d7ffa6168c7a29c
