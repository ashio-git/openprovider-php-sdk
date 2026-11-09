---
name: Bug report
about: Something the SDK does differently from the API or from its documentation
title: '[Bug] '
labels: bug
assignees: ''
---

<!-- Never paste a password, a token or a full response with personal data. Mask them first. -->
<!-- Found a security problem? Do not open an issue: see SECURITY.md. -->

## What happened

What you called, what you expected and what you got instead.

## Code

```php
$response = $openProvider->dns()->getZone('example.com');
```

## Error or response

The exception class, `apiCode`, `httpStatus` and `desc` of an `ApiException`, or the message of
any other exception, with the relevant part of the trace.

## Environment

- SDK version (`Ashio\OpenProvider\Sdk::VERSION`):
- PHP version:
- Environment: sandbox / production
- Base path: `/v1` / `/v1beta`
- HTTP client, if not the default Guzzle:
