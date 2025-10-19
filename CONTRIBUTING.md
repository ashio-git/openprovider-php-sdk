# Contributing to OpenProvider PHP SDK

First off, thank you for considering contributing to the OpenProvider PHP SDK! It's people like you that make this SDK the best in the world.

## 🎯 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## 🚀 How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When you create a bug report, include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples**
- **Describe the behavior you observed and what you expected**
- **Include PHP version and SDK version**

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion:

- **Use a clear and descriptive title**
- **Provide a detailed description of the suggested enhancement**
- **Explain why this enhancement would be useful**
- **List any similar features in other SDKs**

### Pull Requests

1. Fork the repo and create your branch from `main`
2. If you've added code, add tests
3. Ensure the test suite passes
4. Make sure your code follows PSR-12
5. Write a clear commit message

## 💻 Development Process

### Setup Development Environment

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/openprovider-php-sdk.git
cd openprovider-php-sdk

# Install dependencies
composer install

# Run tests
composer test
```

### Coding Standards

We follow PSR-12 coding standards. Before submitting:

```bash
# Check code style
composer cs:check

# Fix code style automatically
composer cs:fix

# Run static analysis
composer analyse
```

### Writing Tests

All new features must include tests:

```php
<?php

namespace Ashio\Tests\YourFeature;

use PHPUnit\Framework\TestCase;

class YourFeatureTest extends TestCase
{
    public function testYourFeature(): void
    {
        // Arrange
        $input = 'test';
        
        // Act
        $result = yourFunction($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
./vendor/bin/phpunit tests/YourTest.php

# Run with coverage
composer test:coverage
```

## 📝 Commit Messages

Write clear, concise commit messages:

```
feat: add support for new API endpoint
fix: resolve issue with domain transfer
docs: update README with new examples
test: add tests for SSL certificate renewal
refactor: improve error handling in DNS service
```

### Commit Message Format

```
<type>: <subject>

<body>

<footer>
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `test`: Adding or updating tests
- `refactor`: Code refactoring
- `style`: Code style changes
- `chore`: Maintenance tasks

## 🔍 Code Review Process

1. All submissions require review
2. Reviewers will check:
   - Code quality and style
   - Test coverage
   - Documentation
   - Performance implications
3. Address review comments
4. Once approved, maintainers will merge

## 📚 Documentation

Update documentation when:
- Adding new features
- Changing existing functionality
- Fixing bugs that affect usage

Documentation includes:
- README.md
- Code comments
- PHPDoc blocks
- CHANGELOG.md

## ✅ Checklist Before Submitting

- [ ] Code follows PSR-12 standards
- [ ] All tests pass
- [ ] New tests added for new features
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] No merge conflicts
- [ ] Commit messages are clear

## 🎓 Resources

- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [OpenProvider API Documentation](https://docs.openprovider.com/)

## 💬 Questions?

Feel free to open an issue for questions or join our discussions!

Thank you for contributing! 🙏
