# Ashio OpenProvider PHP SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ashio/openprovider-php-sdk.svg?style=flat-square)](https://packagist.org/packages/ashio/openprovider-php-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/ashio/openprovider-php-sdk.svg?style=flat-square)](https://packagist.org/packages/ashio/openprovider-php-sdk)
[![License](https://img.shields.io/packagist/l/ashio/openprovider-php-sdk.svg?style=flat-square)](./LICENSE)

A production-grade, fully-typed, and meticulously audited PHP SDK for the OpenProvider API. Engineered for an elite developer experience with 100% API compliance, consistent DTOs, explicit method signatures, and robust error handling.

---

## Project Philosophy

This SDK was built with a clear set of design principles aimed at providing a superior developer experience:

*   **100% API Compliance:** Every endpoint, method, and data model defined in the official `swagger.json` is implemented. If the API can do it, so can this SDK.
*   **Type-Safety First:** All interactions are strongly typed. Say goodbye to generic arrays and "magic strings." All requests and responses use dedicated Data Transfer Objects (DTOs), enabling static analysis and autocompletion in your IDE.
*   **Predictable & Consistent:** The learning curve is minimized. All list methods return a `PaginatedResponse` object. All API operations return a consistent DTO. You learn the pattern once and can apply it everywhere.
*   **Robust Error Handling:** API errors are not just exceptions; they are specific, typed exceptions. This allows for clean and precise error handling with `try...catch` blocks (e.g., `NotFoundException`, `ValidationException`).

This library is for developers who value predictability, reliability, and code that is easy to read, maintain, and debug.

## Table of Contents

*   [Installation & Authentication](#installation--authentication)
*   [Quick Start: Registering a Domain](#quick-start-registering-a-domain)
*   [Core Concepts](#core-concepts)
    *   [Working With DTOs (Data Transfer Objects)](#working-with-dtos)
    *   [Handling Paginated Responses](#handling-paginated-responses)
    *   [Advanced Error Handling](#advanced-error-handling)
*   [Feature Index](#feature-index)
*   [Per-Module Examples](#per-module-examples)
    *   [Domains](#domains)
    *   [DNS](#dns)
    *   [SSL](#ssl)
    *   [Licenses](#licenses)
    *   [Email Templates](#email-templates)
*   [License](#license)
*   [Acknowledgements](#acknowledgements)

## Installation & Authentication

Install the SDK via Composer:

```bash
composer require ashio/openprovider-php-sdk
```

Authenticate with your OpenProvider credentials. The API token is fetched automatically.

```php
use Ashio\OpenProvider;

$op = new OpenProvider('YOUR_USERNAME', 'YOUR_PASSWORD');
```

## Quick Start: Registering a Domain

This example demonstrates a complete, real-world workflow from checking a domain to registering it for a new customer.

### Step 1: Check Domain Availability

```php
// Check if 'example.com' is available for registration.
$checks = $op->domain()->check(['example.com']);

if ($checks->status !== 'free') {
    die('Domain is not available.');
}

echo "Domain example.com is available!\n";
```

### Step 2: Create a New Customer

```php
use Ashio\Model\Customer;
use Ashio\Model\ContactName;
use Ashio\Model\ContactAddress;

// DTOs ensure all required fields are considered and correctly formatted.
$customerData = (new Customer())
    ->fromArray([
        'email' => 'john.doe@example.com',
        'name' => (new ContactName())->fromArray([
            'firstName' => 'John',
            'lastName' => 'Doe'
        ])->toArray(),
        'address' => (new ContactAddress())->fromArray([
            'street' => 'Main Street',
            'number' => '123',
            'city' => 'Anytown',
            'zipcode' => '12345',
            'country' => 'US'
        ])->toArray(),
    ]);

// The API returns a complete Customer DTO, including the newly created handle.
$customer = $op->resellerCustomer()->createCustomer($customerData);
echo "Created customer with handle: " . $customer->handle . "\n";
```

### Step 3: Register the Domain for the Customer

```php
use Ashio\Model\DomainCreateRequest;
use Ashio\Model\DomainName;

// Use a dedicated Request DTO to build the registration payload.
$registrationRequest = new DomainCreateRequest(
    ownerHandle: $customer->handle,
    domain: (new DomainName())->fromArray(['name' => 'example', 'extension' => 'com']),
    period: 1
);

// The createDomain method returns a complete Domain DTO of the newly registered domain.
$registeredDomain = $op->domain()->createDomain($registrationRequest);
echo "Successfully registered domain with ID: " . $registeredDomain->id . "\n";
echo "Status: " . $registeredDomain->status . "\n"; // e.g., 'ACT' (Active)
```

## Core Concepts

### Working With DTOs

Every API response is mapped to a concrete Data Transfer Object (DTO). This means you get predictable objects with documented properties and full IDE autocompletion, instead of generic arrays. Request bodies are also built using DTOs, ensuring all parameters are correctly named and typed before the request is even sent.

### Handling Paginated Responses

All methods that return a list of items (e.g., `listDomains`, `listOrders`) return a `Ashio\Model\PaginatedResponse` object. This provides a consistent way to handle paginated data across the entire SDK.

```php
use Ashio\Model\PaginatedResponse;
use Ashio\Model\Domain;

/** @var PaginatedResponse<Domain> $page */
$page = $op->domain()->listDomains(limit: 50);

echo "Total domains found: " . $page->total . "\n";

foreach ($page->results as $domain) {
    // $domain is a fully-typed Ashio\Model\Domain object
    echo "Domain: {$domain->domain->name}.{$domain->domain->extension}, Status: {$domain->status}\n";
}
```

### Advanced Error Handling

The SDK uses specific, typed exceptions for different error scenarios. This allows for clean, precise `try...catch` blocks to handle API responses gracefully.

```php
use Ashio\Exception\{AuthenticationException, ValidationException, NotFoundException, ApiException};

try {
    // Attempt to get a domain that does not exist.
    $op->domain()->getDomain(9999999);
} catch (AuthenticationException $e) {
    // Handle invalid credentials or IP restrictions.
    echo "Error: Authentication failed. " . $e->getMessage();
} catch (NotFoundException $e) {
    // Handle 404 Not Found errors.
    echo "Error: The requested resource was not found. " . $e->getMessage();
} catch (ValidationException $e) {
    // Handle 422 Unprocessable Entity errors (e.g., invalid input).
    echo "Error: Validation failed. " . $e->getMessage();
} catch (ApiException $e) {
    // Catch-all for other 4xx or 5xx API errors.
    echo "An unexpected API error occurred: " . $e->getMessage();
}
```

## Feature Index

This SDK provides 100% coverage of the OpenProvider API. Key features include:

- **Domains:** Full lifecycle management including checking, listing, registration, transfers, renewals, auth code management, and name suggestions.
- **DNS:** Complete management of zones, records, templates, and nameserver groups.
- **SSL:** Full lifecycle management including product listing, CSR creation/decoding, and order management (create, reissue, renew, cancel).
- **Licenses:** Full CRUD operations for Plesk & Virtuozzo licenses, including keys and items.
- **Accounting:** Access to invoices, payments, transactions, and email templates.
- **Reseller & Customer:** Full CRUD for contacts and customers, plus management of reseller settings.

## Per-Module Examples

### Domains

```php
// Retrieve a paginated list of domains.
$domains = $op->domain()->listDomains(limit: 10); // PaginatedResponse<Domain>

// Fetch the authorization code for a specific domain.
$auth = $op->domain()->getAuthCode(123); // AuthCode DTO
```

### DNS

```php
use Ashio\Model\DnsCreateZoneRequest;
use Ashio\Model\DomainName;

// List existing DNS zones.
$zones = $op->domain()->dns()->listZones(limit: 10); // PaginatedResponse<Zone>

// Build a request to create a new zone.
$domainName = (new DomainName())->fromArray(['name' => 'example', 'extension' => 'com']);
$zoneRequest = new DnsCreateZoneRequest($domainName);
$zoneRequest->type = 'master';
// $zoneRequest->records = [...]; // Optional DNS records.

// Submit the request; returns a DnsActionResponse DTO.
$response = $op->domain()->dns()->createZone($zoneRequest);
```

### SSL

```php
use Ashio\Model\CsrCreateRequest;
use Ashio\Model\CsrCreateResponse;

// Generate a CSR using a dedicated DTO.
$csrRequest = new CsrCreateRequest('example.com');
$csrRequest->organization = 'My Company LLC';
$csrRequest->country = 'US';
// ... set other properties as needed.

/** @var CsrCreateResponse $csrResponse */
$csrResponse = $op->ssl()->createCsr($csrRequest);
echo "Generated CSR:\n" . $csrResponse->csr;

// List available SSL products.
$products = $op->ssl()->listProducts(limit: 5); // PaginatedResponse<SslProduct>
```

### Licenses

```php
use Ashio\Model\CreatePleskLicenseRequest;
use Ashio\Model\License as LicenseModel;

// Retrieve existing licenses.
$page = $op->license()->listLicenses(limit: 10); // PaginatedResponse<LicenseModel>

// Fetch a Plesk license key.
$key = $op->license()->getPleskKey(1001); // LicenseKey DTO

// Create a new Plesk license.
$newLicense = $op->license()->createPleskLicense(
    new CreatePleskLicenseRequest(items: ['PLESK-ITEM'], period: 1)
);
```

### Email Templates

```php
// List available email templates.
$emails = $op->emailTemplate()->listEmails(limit: 20); // PaginatedResponse<EmailTemplate>
```

## License

This SDK is licensed under the BSD 2-Clause "Simplified" License. See the [LICENSE](./LICENSE) file for details.

## Acknowledgements

This SDK was proudly developed and is maintained by Marco Dantas of Ashio.  
<https://ashio.com.br>

