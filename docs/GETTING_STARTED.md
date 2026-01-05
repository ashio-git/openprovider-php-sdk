# Getting Started with OpenProvider PHP SDK

Welcome to the OpenProvider PHP SDK! This guide will help you get up and running quickly.

## Installation

Install the SDK using Composer:

```bash
composer require ashio/openprovider-php-sdk
```

## Basic Setup

```php
<?php

require 'vendor/autoload.php';

use Ashio\OpenProvider;

// Initialize the client - authentication happens automatically
$client = new OpenProvider(
    username: 'your-username',
    password: 'your-password',
    environment: 'production' // or 'test' for sandbox
);
```

## Your First API Call

Let's check if a domain is available:

```php
// Check domain availability
$results = $client->domain()->check(['example.com', 'example.net']);

foreach ($results as $result) {
    if ($result->status === 'free') {
        echo "{$result->domain}: Available! ✅\n";
    } else {
        echo "{$result->domain}: Not available ❌\n";
    }
}
```

## Common Operations

### Register a Domain

```php
use Ashio\Model\DomainCreateRequest;
use Ashio\Model\DomainName;

$request = new DomainCreateRequest(
    ownerHandle: 'OWNER123',
    domain: new DomainName('example', 'com'),
    period: 1
);
$request->ns_group = 'dns-openprovider';

$domain = $client->domain()->createDomain($request);
echo "Domain registered: ID {$domain->id}\n";
```

### Manage DNS Records

```php
use Ashio\Model\DnsCreateZoneRequest;

$dns = $client->domain()->dns();

// Create a DNS zone
$dns->createZone(new DnsCreateZoneRequest(name: 'example.com'));

// Get zone with records
$zone = $dns->getZone('example.com', ['with_records' => true]);

// List zones
$zones = $dns->listZones(limit: 100);
foreach ($zones->results as $zone) {
    echo "Zone: {$zone->name}\n";
}
```

### Order SSL Certificate

```php
use Ashio\Model\SslOrderCreateRequest;

$ssl = $client->ssl();

// List available products
$products = $ssl->listProducts();

// Create an order
$order = $ssl->createOrder(new SslOrderCreateRequest(
    product_id: 123,
    period: 1,
    csr: '-----BEGIN CERTIFICATE REQUEST-----...'
));

echo "SSL order created: ID {$order->id}\n";
```

### Manage Licenses

```php
use Ashio\Model\CreatePleskLicenseRequest;

$license = $client->license();

// List Plesk licenses
$licenses = $license->listPleskLicenses();

// Create new license
$newLicense = $license->createPleskLicense(new CreatePleskLicenseRequest(
    title: 'My Plesk License',
    binding: '192.168.1.1'
));
```

## Error Handling

The SDK throws specific exceptions for different error types:

```php
use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\ValidationException;
use Ashio\Exception\NotFoundException;

try {
    $domain = $client->domain()->getDomain(12345);
} catch (AuthenticationException $e) {
    echo "Authentication failed: {$e->getMessage()}\n";
} catch (NotFoundException $e) {
    echo "Domain not found: {$e->getMessage()}\n";
} catch (ValidationException $e) {
    echo "Validation error: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "General API error: {$e->getMessage()}\n";
}
```

## Environment Configuration

### Using Environment Variables

```bash
# .env file
OPENPROVIDER_USERNAME=your-username
OPENPROVIDER_PASSWORD=your-password  
OPENPROVIDER_ENV=production
```

```php
$client = new OpenProvider(
    username: getenv('OPENPROVIDER_USERNAME'),
    password: getenv('OPENPROVIDER_PASSWORD'),
    environment: getenv('OPENPROVIDER_ENV') ?: 'production'
);
```

### Test Environment

For testing, use the sandbox environment:

```php
$client = new OpenProvider(
    username: 'test-username',
    password: 'test-password',
    environment: 'test'
);
```

## Service Access Pattern

All services are accessed through the main client:

```php
$client->domain()              // Domain operations
$client->domain()->dns()       // DNS management
$client->domain()->spamExpert() // SpamExpert integration
$client->domain()->easyDMARC() // EasyDMARC integration
$client->ssl()                 // SSL certificates
$client->license()             // Plesk/Virtuozzo licenses
$client->billing()             // Invoices, payments, transactions
$client->resellerCustomer()    // Customers, contacts, reseller
$client->emailTemplate()       // Email template management
```

## Next Steps

- Read the [full documentation](../README.md)
- Check the [API Coverage Report](../API_COVERAGE_REPORT.md)
- See [Contributing Guide](../CONTRIBUTING.md)

## Need Help?

- 🐛 Issues: [GitHub Issues](https://github.com/ashio-git/openprovider-php-sdk/issues)
- 📧 Questions: Open a GitHub issue

Happy coding! 🚀
