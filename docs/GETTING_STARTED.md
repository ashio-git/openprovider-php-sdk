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
use Ashio\Credentials;

// Create credentials
$credentials = new Credentials(
    username: 'your-username',
    password: 'your-password'
);

// Initialize the client
$client = new OpenProvider($credentials);
```

## Your First API Call

Let's check if a domain is available:

```php
// Check domain availability
$result = $client->domain()->checkDomain('example.com');

if ($result->status === 'free') {
    echo "Domain is available! ✅\n";
} else {
    echo "Domain is not available ❌\n";
}
```

## Common Operations

### Register a Domain

```php
$domain = $client->domain()->createDomain([
    'domain' => [
        'name' => 'example',
        'extension' => 'com'
    ],
    'period' => 1,
    'owner_handle' => 'OWNER123',
    'admin_handle' => 'ADMIN123',
    'tech_handle' => 'TECH123',
    'billing_handle' => 'BILLING123',
    'nameservers' => [
        ['name' => 'ns1.example.com'],
        ['name' => 'ns2.example.com']
    ]
]);

echo "Domain registered: {$domain->id}\n";
```

### Manage DNS Records

```php
// Create a DNS zone
$zone = $client->dns()->createZone('example.com');

// Add an A record
$client->dns()->createRecord('example.com', [
    'type' => 'A',
    'name' => 'www',
    'value' => '192.168.1.1',
    'ttl' => 3600
]);

// Add an MX record
$client->dns()->createRecord('example.com', [
    'type' => 'MX',
    'name' => '@',
    'value' => 'mail.example.com',
    'prio' => 10,
    'ttl' => 3600
]);
```

### Order SSL Certificate

```php
// Generate CSR first (or use your own)
$csr = "-----BEGIN CERTIFICATE REQUEST-----\n...";

// Order certificate
$order = $client->ssl()->createOrder([
    'product_id' => 123,
    'csr' => $csr,
    'period' => 1,
    'approver_email' => 'admin@example.com'
]);

echo "SSL order created: {$order->id}\n";
```

## Error Handling

The SDK throws specific exceptions for different error types:

```php
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\ValidationException;
use Ashio\Exception\NotFoundException;

try {
    $domain = $client->domain()->getDomain('example.com');
} catch (AuthenticationException $e) {
    echo "Authentication failed: {$e->getMessage()}\n";
} catch (NotFoundException $e) {
    echo "Domain not found: {$e->getMessage()}\n";
} catch (ValidationException $e) {
    echo "Validation error: {$e->getMessage()}\n";
}
```

## Environment Configuration

### Using Environment Variables

```bash
# .env file
OPENPROVIDER_USERNAME=your-username
OPENPROVIDER_PASSWORD=your-password
OPENPROVIDER_ENVIRONMENT=production
```

```php
$credentials = new Credentials(
    username: getenv('OPENPROVIDER_USERNAME'),
    password: getenv('OPENPROVIDER_PASSWORD'),
    environment: getenv('OPENPROVIDER_ENVIRONMENT')
);
```

### Test Environment

For testing, use the test environment:

```php
$credentials = new Credentials(
    username: 'test-username',
    password: 'test-password',
    environment: 'test'
);
```

## Next Steps

- Read the [full documentation](../README.md)
- Check out [examples](./EXAMPLES.md)
- Learn about [advanced features](./ADVANCED.md)
- See [API reference](./API_REFERENCE.md)

## Need Help?

- 📧 Email: support@example.com
- 🐛 Issues: [GitHub Issues](https://github.com/ashio-git/openprovider-php-sdk/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/ashio-git/openprovider-php-sdk/discussions)

Happy coding! 🚀
