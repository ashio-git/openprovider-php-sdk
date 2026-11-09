<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1+-8892BF.svg?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.1+">
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="License">
  <img src="https://img.shields.io/badge/Line%20coverage-58.6%25-orange.svg?style=for-the-badge" alt="Line coverage 58.6%">
  <img src="https://img.shields.io/badge/API-111%2F140%20operations-blue.svg?style=for-the-badge" alt="111 of 140 API operations">
</p>

<h1 align="center">🌐 OpenProvider PHP SDK</h1>

<p align="center">
  <strong>The most complete, production-ready PHP SDK for the OpenProvider API</strong>
</p>

<p align="center">
  Modern • Type-Safe • Fully Tested • Professional Grade
</p>

---

## ✨ Highlights

| Feature | Status |
|---------|--------|
| **PHP 8.1+** | Modern syntax with strict types |
| **API Coverage** | 111 of the 140 operations in the current Openprovider documentation (Mailcow and reseller signup are not implemented yet; see `docs/plans/`) |
| **219 Models** | Complete data type coverage |
| **PSR-12 Compliant** | Industry-standard code style |
| **Fully Documented** | Comprehensive PHPDoc blocks |

---

## 📦 Installation

```bash
composer require ashio/openprovider-php-sdk
```

### Requirements

- PHP 8.1 or higher
- ext-json
- ext-curl

---

## 🚀 Quick Start

```php
<?php

use Ashio\OpenProvider;

// Initialize the SDK
$client = new OpenProvider(
    username: 'your-username',
    password: 'your-password',
    environment: 'production' // or 'test' for sandbox
);

// Check domain availability
$results = $client->domain()->check(['example.com', 'example.net']);

foreach ($results as $result) {
    echo "{$result->domain}: {$result->status}\n";
}
```

---

## 📚 Complete API Reference

### 🌍 Domain Management

```php
// Check availability
$results = $client->domain()->check(['example.com']);

// Register a new domain
use Ashio\Model\DomainCreateRequest;
use Ashio\Model\DomainName;

$request = new DomainCreateRequest(
    ownerHandle: 'OWNER123',
    domain: new DomainName('example', 'com'),
    period: 1
);
$domain = $client->domain()->createDomain($request);

// Get domain details
$domain = $client->domain()->getDomain(12345);

// List all domains
$domains = $client->domain()->listDomains(limit: 100);

// Update domain
$client->domain()->updateDomain(12345, $updateRequest);

// Renew domain
$client->domain()->renewDomain(12345, new RenewDomainRequest(period: 1));

// Transfer domain
$client->domain()->transferDomain($transferRequest);

// Delete domain
$client->domain()->deleteDomain(12345);
```

### 🔧 DNS Management

```php
$dns = $client->domain()->dns();

// List zones
$zones = $dns->listZones();

// Create zone
$dns->createZone($createZoneRequest);

// Get zone with records
$zone = $dns->getZone('example.com', ['with_records' => true]);

// List zone records
$records = $dns->listZoneRecords('example.com');

// Manage nameservers
$nameservers = $dns->listNameservers();
$dns->createNameserver($nameserver);

// Nameserver groups
$groups = $dns->listNameserverGroups();

// DNS templates
$templates = $dns->listTemplates();
```

### 🔐 SSL Certificates

```php
$ssl = $client->ssl();

// List products
$products = $ssl->listProducts();

// Get product details
$product = $ssl->getProduct(123);

// Create SSL order
$order = $ssl->createOrder($orderRequest);

// Manage orders
$orders = $ssl->listOrders();
$order = $ssl->getOrder(456);
$ssl->updateOrder(456, $updateRequest);
$ssl->cancelOrder(456);
$ssl->reissueOrder(456, $reissueRequest);
$ssl->renewOrder(456);

// CSR operations
$csr = $ssl->createCsr($csrRequest);
$decoded = $ssl->decodeCsr($decodeRequest);
```

### 📜 License Management

```php
$license = $client->license();

// Plesk licenses
$pleskLicenses = $license->listPleskLicenses();
$license->createPleskLicense($request);
$license->getPleskLicense($keyId);
$license->updatePleskLicense($keyId, $request);
$license->deletePleskLicense($keyId);
$license->getPleskKey($keyId);

// Virtuozzo licenses
$virtuozzoLicenses = $license->listVirtuozzoLicenses();
$license->createVirtuozzoLicense($request);

// License items
$items = $license->listItems();

// Reset HWID
$license->resetHwid('plesk', $keyId, $request);
```

### 👥 Customer & Reseller

```php
$reseller = $client->resellerCustomer();

// Customers
$customers = $reseller->listCustomers();
$customer = $reseller->createCustomer($request);
$reseller->getCustomer('HANDLE123');
$reseller->updateCustomer('HANDLE123', $request);
$reseller->deleteCustomer('HANDLE123');

// Contacts
$contacts = $reseller->listContacts();
$reseller->createContact($request);

// Reseller info
$info = $reseller->getReseller();
$settings = $reseller->getSettings();
$stats = $reseller->getStatistics();

// Tags
$tags = $reseller->listTags();
$reseller->createTag($request);

// Email verification
$reseller->startEmailVerification($request);
```

### 💰 Billing

```php
$billing = $client->billing();

// Invoices
$invoices = $billing->listInvoices();

// Payments
$payments = $billing->listPayments();

// Transactions
$transactions = $billing->listTransactions();
```

### 📧 Email Templates

```php
$templates = $client->emailTemplate();

// List templates
$list = $templates->listEmails();

// CRUD operations
$templates->createEmail($request);
$templates->updateEmail($id, $request);
$templates->deleteEmail($id);
```

### 🛡️ SpamExpert Integration

```php
$spamExpert = $client->domain()->spamExpert();

// Manage domains
$spamExpert->createDomain($request);
$domain = $spamExpert->getDomain('example.com');
$spamExpert->updateDomain('example.com', $request);
$spamExpert->deleteDomain('example.com');

// Generate login URL
$url = $spamExpert->generateLoginUrl($request);
```

### ✉️ EasyDMARC Integration

```php
$easyDmarc = $client->domain()->easyDMARC();

// Manage orders
$orders = $easyDmarc->listEasyDmarc();
$easyDmarc->createEasyDmarc($request);
$order = $easyDmarc->getEasyDmarc($id);
$easyDmarc->deleteEasyDmarc($id);

// SSO & retry
$sso = $easyDmarc->getEasyDmarcSso($id);
$easyDmarc->retryEasyDmarc($id, $request);
```

---

## 🏗️ Architecture

```
src/
├── OpenProvider.php          # Main SDK entry point
├── Credentials.php           # Authentication management
│
├── Accounting/
│   ├── Account.php           # Account balance
│   ├── Billing.php           # Invoices, payments, transactions
│   ├── EmailTemplate.php     # Email template CRUD
│   └── ResellerCustomer.php  # Customers, contacts, reseller
│
├── Domain/
│   ├── Domain.php            # Domain operations
│   ├── DNS.php               # DNS zones, records, nameservers
│   ├── EasyDMARC.php         # EasyDMARC integration
│   └── SpamExpert.php        # SpamExpert integration
│
├── Exception/                # Custom exceptions
│   ├── ApiException.php
│   ├── AuthenticationException.php
│   ├── NotFoundException.php
│   ├── ParameterException.php
│   └── ValidationException.php
│
├── License/
│   └── License.php           # Plesk & Virtuozzo licenses
│
├── Model/                    # 219 data models
│
└── SSL/
    └── SSL.php               # SSL certificates
```

---

## ⚠️ Error Handling

The SDK provides specialized exceptions for different error types:

```php
use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ValidationException;

try {
    $domain = $client->domain()->getDomain(12345);
} catch (AuthenticationException $e) {
    // Invalid credentials or token expired
    echo "Auth error: " . $e->getMessage();
} catch (NotFoundException $e) {
    // Domain not found
    echo "Not found: " . $e->getMessage();
} catch (ValidationException $e) {
    // Invalid request parameters
    echo "Validation error: " . $e->getMessage();
} catch (ApiException $e) {
    // Other API errors
    echo "API error: " . $e->getMessage();
}
```

---

## 🧪 Testing

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Static analysis
composer analyse

# Type checking with Psalm
composer psalm

# Code style check
composer cs:check

# Fix code style
composer cs:fix

# Run all quality checks
composer quality
```

---

## 📊 Code Quality

| Tool | Level | Status |
|------|-------|--------|
| **PHPStan** | Level 8 | ✅ Pass |
| **Psalm** | Error level 3 | ✅ Pass |
| **PHP-CS-Fixer** | PSR-12 | ✅ Pass |
| **PHPUnit** | 58.6% line coverage; warnings and deprecations fail the run | ✅ Pass |

---

## 🔧 Configuration

### Environment Variables

```bash
OPENPROVIDER_USERNAME=your-username
OPENPROVIDER_PASSWORD=your-password
OPENPROVIDER_ENV=production  # or 'test'
```

### Custom HTTP Client

```php
use GuzzleHttp\Client;

$httpClient = new Client([
    'timeout' => 60,
    'verify' => true,
]);

$client = new OpenProvider(
    username: 'user',
    password: 'pass',
    environment: 'production',
    httpClient: $httpClient
);
```

---

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for your changes
4. Ensure all tests pass (`composer test`)
5. Follow PSR-12 coding standards
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🏆 Why Choose This SDK?

| Feature | This SDK | Others |
|---------|----------|--------|
| PHP 8.1+ Support | ✅ | ❌ Often PHP 7.x |
| Strict Types | ✅ | ❌ Rarely |
| Type-Safe Models | ✅ 219 models | ❌ Arrays |
| Modern Patterns | ✅ | ❌ Legacy code |
| Active Maintenance | ✅ | ❓ Varies |

---

<p align="center">
  <strong>Made with ❤️ by the Ashio Team</strong>
</p>

<p align="center">
  <a href="https://github.com/ashio-git/openprovider-php-sdk/issues">Report Bug</a>
  •
  <a href="https://github.com/ashio-git/openprovider-php-sdk/issues">Request Feature</a>
</p>
