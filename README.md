# OpenProvider PHP SDK

[![Latest Version](https://img.shields.io/packagist/v/vexura/openprovider-sdk-client?label=version)](https://packagist.org/packages/vexura/openprovider-sdk-client/)
[![Composer Downloads](https://img.shields.io/packagist/dm/vexura/openprovider-sdk-client.svg?label=Composer%20Downloads)](https://packagist.org/packages/vexura/openprovider-sdk-client)

Production‑grade, fully‑typed SDK for OpenProvider. Consistent DTOs, explicit method signatures, predictable pagination, and robust error handling for an elite developer experience.

## Installation & Auth

Install via Composer:

```bash
composer require vexura/openprovider-sdk-client
```

Authenticate:

```php
use Vexura\OpenProvider;

$op = new OpenProvider('USERNAME', 'PASSWORD');
```

## Quick Start (End‑to‑End)

1) Check a domain

```php
$checks = $op->domain()->check(['example.com']);
if ($checks[0]->status === 'free') {
    // proceed
}
```

2) Create a customer (DTO)

```php
use Vexura\Model\Customer;
use Vexura\Model\ContactName;
use Vexura\Model\ContactAddress;

$customer = (new Customer())
    ->fromArray([
        'email' => 'john.doe@example.com',
        'name' => (new ContactName())->fromArray(['first_name' => 'John','last_name' => 'Doe'])->toArray(),
        'address' => (new ContactAddress())->fromArray([
            'street' => 'Main', 'number' => '123', 'city' => 'Anytown', 'country' => 'US'
        ])->toArray(),
    ]);

$customer = $op->resellerCustomer()->createCustomer($customer);
```

3) Register the domain for the customer (DTO request)

```php
$req = new Vexura\Model\DomainCreateRequest(
    ownerHandle: $customer->handle,
    domain: (new Vexura\Model\DomainName())->fromArray(['name' => 'example','extension' => 'com']),
    period: 1
);
$order = $op->domain()->createDomain($req);
```

4) Fetch domain details (DTO) and read properties

```php
$domain = $op->domain()->getDomain($order->id);
echo $domain->status; // e.g. ACTIVE
```

## Working With DTOs

- Every handler returns concrete DTOs (no arrays).
- Nested structures are strongly typed (e.g., names, addresses, price objects).
- Lists return `Vexura\Model\PaginatedResponse<T>` with `$total` and `$results`.

Example: iterate domains

```php
use Vexura\Model\PaginatedResponse;

/** @var PaginatedResponse<Vexura\Model\Domain> $page */
$page = $op->domain()->listDomains(limit: 50);
foreach ($page->results as $d) {
    echo $d->status . PHP_EOL;
}
```

## Advanced Error Handling

```php
use Vexura\Exception\{AuthenticationException, ValidationException, NotFoundException, ApiException};

try {
    $op->domain()->getDomain(999999);
} catch (AuthenticationException $e) {
    // Invalid credentials or forbidden
} catch (NotFoundException $e) {
    // Resource not found
} catch (ValidationException $e) {
    // Invalid input (422)
} catch (ApiException $e) {
    // Other API errors
}
```

## Feature Index

- Domains: check, list, get, register, transfer, renew, auth codes, suggestions
- DNS: zones, records, templates, nameserver groups
- SSL: products, orders, CSR create/decode, order actions
- Licenses: Plesk & Virtuozzo listing/CRUD, license keys, items
- Accounting: invoices, payments, transactions, email templates
- Reseller/Customer: contacts, customers, settings

## Per‑Module Examples

Domains

```php
$domains = $op->domain()->listDomains(limit: 10);
$auth = $op->domain()->getAuthCode(123); // AuthCode DTO
```

DNS

```php
$zones = $op->domain()->dns()->listZones(limit: 10); // PaginatedResponse<Zone>
$ok = $op->domain()->dns()->createZone([
    'domain' => ['name' => 'example','extension' => 'com'],
    'type' => 'master',
    'records' => []
]); // DnsActionResponse (success)
```

SSL

```php
use Vexura\Model\CsrCreateResponse;

$csr = $op->ssl()->createCsr(['common_name' => 'example.com']); // CsrCreateResponse
$products = $op->ssl()->listProducts(limit: 5); // PaginatedResponse<SslProduct>
```

Advanced Usage

```php
// Create SSL Order with a request DTO
$create = new Vexura\Model\SslOrderCreateRequest(
    productId: 1,
    csr: '---CSR---',
    hostNames: ['example.com','www.example.com'],
    period: 12
);
$order = $op->ssl()->createOrder($create);

// List orders with explicit parameters
$orders = $op->ssl()->listOrders(limit: 10, status: 'active');

// Approve domain transfer and handle ActionResponse
$resp = $op->domain()->approveTransfer(123, ['confirm' => true]);
if ($resp->success) {
    // ok
}
```

Licenses

```php
use Vexura\Model\License as LicenseModel;

$page = $op->license()->listLicenses(limit: 10); // PaginatedResponse<License>
$key  = $op->license()->getPleskKey(1001);       // LicenseKey DTO
 $new  = $op->license()->createPleskLicense(new Vexura\Model\CreatePleskLicenseRequest(items: ['PLESK-ITEM'], period: 1));
```

Email Templates

```php
$emails = $op->emailTemplate()->listEmails(limit: 20); // PaginatedResponse<EmailTemplate>
```

## Summary

This SDK is designed to be predictable, explicit, and safe by default. Strong DTOs, explicit method signatures, and uniform pagination enable a frictionless developer experience suitable for high‑scale production systems.
