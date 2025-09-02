# Ashio OpenProvider PHP SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ashio/openprovider-php-sdk.svg?style=flat-square)](https://packagist.org/packages/ashio/openprovider-php-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/ashio/openprovider-php-sdk.svg?style=flat-square)](https://packagist.org/packages/ashio/openprovider-php-sdk)
[![License](https://img.shields.io/packagist/l/ashio/openprovider-php-sdk.svg?style=flat-square)](./LICENSE)

Production-grade, fully-typed SDK for the OpenProvider API. Featuring consistent DTOs, explicit method signatures, predictable pagination, and robust error handling for an elite developer experience.

## Installation & Authentication

Install via Composer:

```bash
composer require ashio/openprovider-php-sdk
```

Authenticate:

```php
use Ashio\OpenProvider;

$op = new OpenProvider('USERNAME', 'PASSWORD');
```

## Quick Start

1) Check a domain

```php
$checks = $op->domain()->check(['example.com']);
if ($checks[0]->status === 'free') {
    // proceed
}
```

2) Create a customer

```php
use Ashio\Model\Customer;
use Ashio\Model\ContactName;
use Ashio\Model\ContactAddress;

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

3) Register the domain for the customer

```php
$req = new Ashio\Model\DomainCreateRequest(
    ownerHandle: $customer->handle,
    domain: (new Ashio\Model\DomainName())->fromArray(['name' => 'example','extension' => 'com']),
    period: 1
);
$order = $op->domain()->createDomain($req);
```

4) Fetch domain details

```php
$domain = $op->domain()->getDomain($order->id);
echo $domain->status; // e.g. ACTIVE
```

## Working With DTOs

- Every handler returns concrete DTOs (no arrays).
- Request bodies are always expressed via dedicated Request DTOs, eliminating generic arrays and ensuring type safety.
- Nested structures are strongly typed (e.g., names, addresses, price objects).
- Listings return `Ashio\Model\PaginatedResponse<T>` with `$total` and `$results`.

Example: iterate domains

```php
use Ashio\Model\PaginatedResponse;

/** @var PaginatedResponse<Ashio\Model\Domain> $page */
$page = $op->domain()->listDomains(limit: 50);
foreach ($page->results as $d) {
    echo $d->status . PHP_EOL;
}
```

## Advanced Error Handling

```php
use Ashio\Exception\{AuthenticationException, ValidationException, NotFoundException, ApiException};

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

## Per-Module Examples

### Domains

```php
$domains = $op->domain()->listDomains(limit: 10);
$auth = $op->domain()->getAuthCode(123); // AuthCode DTO
```

### DNS

```php
use Ashio\Model\DnsCreateZoneRequest;
use Ashio\Model\DomainName;

$zones = $op->domain()->dns()->listZones(limit: 10); // PaginatedResponse<Zone>

$domainName  = (new DomainName())->fromArray(['name' => 'example', 'extension' => 'com']);
$zoneRequest = new DnsCreateZoneRequest($domainName);
$zoneRequest->type = 'master';
// $zoneRequest->records = [...]; // Optional

$ok = $op->domain()->dns()->createZone($zoneRequest); // DnsActionResponse
```

### SSL

```php
use Ashio\Model\CsrCreateRequest;
use Ashio\Model\CsrCreateResponse;

$csr = $op->ssl()->createCsr(new CsrCreateRequest('example.com')); // CsrCreateResponse
$products = $op->ssl()->listProducts(limit: 5); // PaginatedResponse<SslProduct>
```

### Advanced Usage

```php
// Create SSL Order with a request DTO
$create = new Ashio\Model\SslOrderCreateRequest(
    productId: 1,
    csr: '---CSR---',
    hostNames: ['example.com','www.example.com'],
    period: 12
);
$order = $op->ssl()->createOrder($create);

// List orders with explicit parameters
$orders = $op->ssl()->listOrders(limit: 10, status: 'active');

// Approve domain transfer and handle ActionResponse
use Ashio\Model\DomainApproveTransferRequest;

$approveRequest = new DomainApproveTransferRequest();
$approveRequest->approve = 1; // or 0 to reject

// Assuming the domain ID is 123
$resp = $op->domain()->approveTransfer(123, $approveRequest);
if ($resp->success) {
    // ok
}
```

### Licenses

```php
use Ashio\Model\License as LicenseModel;

$page = $op->license()->listLicenses(limit: 10); // PaginatedResponse<License>
$key  = $op->license()->getPleskKey(1001);       // LicenseKey DTO
$new  = $op->license()->createPleskLicense(new Ashio\Model\CreatePleskLicenseRequest(items: ['PLESK-ITEM'], period: 1));
```

### Email Templates

```php
$emails = $op->emailTemplate()->listEmails(limit: 20); // PaginatedResponse<EmailTemplate>
```

## License

This SDK is licensed under the BSD 2-Clause License. See the [LICENSE](./LICENSE) file for details.

## Acknowledgements

This SDK was developed and is maintained by [Marco Dantas](https://ashio.com.br) of Ashio.
