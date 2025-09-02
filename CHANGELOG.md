# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-09-02

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
