# Security policy

## Supported versions

| Version | Supported |
|---|---|
| 3.x | Yes |
| 2.x | No. It talks to `/v1beta`, which Openprovider closes on 2027-06-30. Move to 3.x. |

## Reporting a vulnerability

Please do not open a public issue, pull request or discussion for a security problem.

Report it privately through a
[GitHub security advisory](https://github.com/ashio-git/openprovider-php-sdk/security/advisories/new),
addressed to the maintainer, Marco Dantas. Include:

- what an attacker could do, and under which conditions;
- the SDK version, the PHP version and the steps or code that show the problem;
- whether credentials, tokens or account data could leak, and where to.

Never include real credentials, tokens or customer data. Mask them, or use the sandbox.

You will get an acknowledgement, and the fix will be coordinated with you before anything is
disclosed. A fixed release is announced in the [changelog](CHANGELOG.md) under **Security**.

## What the SDK does to protect secrets

- Passwords and tokens are `Secret` objects. They print as `********` in strings, `var_dump`,
  `print_r`, `var_export`, JSON and exception traces, and cannot be serialized. Parameters that
  take them are marked `#[\SensitiveParameter]`. A test checks each of these.
- `ClientConfig` holds no credentials, so it is safe to log.
- Every request uses HTTPS. The environment is an enum, so an unknown name throws instead of
  falling back to production.
- Writes are never retried, so a network failure cannot send the same write twice.
- The test suite never reaches the network. Only the sandbox smoke suite does, and only when you
  run it with sandbox credentials.
