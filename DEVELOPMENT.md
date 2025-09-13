# Development Guide

This guide is for contributors and developers working on the discogs-bundle itself.

## 🧪 Testing

### Quick Commands

```bash
# Unit tests (fast, CI-compatible, no external dependencies)
composer test

# Integration tests (requires Discogs API credentials)
composer test-integration

# All tests together (unit + integration)
composer test-all

# Code coverage (HTML + XML reports)
composer test-coverage
```

### Static Analysis & Code Quality

```bash
# Static analysis (PHPStan Level 8) - Default for Symfony 7.4+
composer analyse

# Static analysis without baseline (Symfony < 7.4)
composer analyse-legacy

# Code style check (Symfony standards)
composer cs

# Auto-fix code style
composer cs-fix
```

## 🔗 Integration Tests

Integration tests are **separated from the CI pipeline** to prevent:

- 🚫 Rate limiting (429 Too Many Requests)
- 🚫 Flaky builds due to network issues
- 🚫 Dependency on external API availability
- 🚫 Slow build times (2+ minutes vs. 0.4 seconds)

### Test Strategy

- **Unit Tests**: Fast, reliable, no external dependencies → **CI default**
- **Integration Tests**: Real API calls, rate-limited → **Manual execution**
- **Bundle Focus**: Test Symfony integration, service wiring, configuration

### Test Levels

#### 1. Public API Tests (Always Run)

- File: `tests/Integration/PublicApiIntegrationTest.php`
- No credentials required
- Tests public endpoints through Bundle: artists, releases, labels, masters
- Safe for forks and pull requests

#### 2. Authentication Levels Test (Conditional)

- File: `tests/Integration/AuthenticatedApiIntegrationTest.php`
- Requires environment variables below
- Tests Bundle authentication configuration:
  - Level 2: Consumer credentials (search)
  - Level 3: Personal token (user data)
  - Level 4: OAuth tokens (direct library usage)

### GitHub Secrets Required

To enable authenticated integration tests in CI/CD, add these secrets to your GitHub repository:

#### Repository Settings → Secrets and variables → Actions

| Secret Name                     | Description                      | Where to get it                                                           |
|---------------------------------|----------------------------------|---------------------------------------------------------------------------|
| `DISCOGS_CONSUMER_KEY`          | Your Discogs app consumer key    | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |
| `DISCOGS_CONSUMER_SECRET`       | Your Discogs app consumer secret | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |
| `DISCOGS_PERSONAL_ACCESS_TOKEN` | Your personal access token       | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |

### Local Development

```bash
# Set environment variables
export DISCOGS_CONSUMER_KEY="your-consumer-key"
export DISCOGS_CONSUMER_SECRET="your-consumer-secret" 
export DISCOGS_PERSONAL_ACCESS_TOKEN="your-personal-access-token"

# Run public tests only
vendor/bin/phpunit tests/Integration/PublicApiIntegrationTest.php

# Run authentication tests (requires env vars)
vendor/bin/phpunit tests/Integration/AuthenticatedApiIntegrationTest.php

# Run all integration tests
vendor/bin/phpunit tests/Integration/ --testdox
```

### Safety Notes

- Public tests are safe for any environment
- Authentication tests will be skipped if secrets are missing
- No credentials are logged or exposed in the test output
- Tests use read-only operations only (no data modification)

## 🛠️ Development Workflow

1. Fork the repository
2. Create feature branch (`git checkout -b feature/name`)
3. Make changes with tests
4. Run test suite (`composer test-all`)
5. Check code quality (`composer analyse && composer cs` or `composer analyse-legacy && composer cs` for Symfony < 7.3)
6. Commit changes (`git commit -m 'Add feature'`)
7. Push to branch (`git push origin feature/name`)
8. Open Pull Request

## 📋 Code Standards

- **PHP Version**: ^8.1
- **Code Style**: Symfony Coding Standards (@Symfony + @Symfony:risky)
- **Static Analysis**: PHPStan Level 8
- **Test Coverage**: Comprehensive unit and integration tests
- **Symfony Compatibility**: 6.4+ | 7.x | 8.x
- **Bundle Focus**: Minimal footprint, clean integration

## 🏗️ Bundle Architecture

The Symfony bundle provides:

1. **Service Integration**: Seamless DiscogsClient autowiring
2. **Configuration Management**: YAML-based bundle configuration
3. **Authentication Setup**: Personal tokens, consumer credentials, OAuth
4. **Rate Limiting**: Optional throttling for API calls
5. **Symfony Integration**: Compatible with Symfony 6.4+ | 7.x | 8.x

### Bundle-Specific Testing Focus

Integration tests ensure:

- **Bundle Integration**: Bundle correctly configures the Discogs API client
- **Symfony Integration**: Services are properly wired and injectable  
- **Configuration**: Bundle configuration is correctly applied
- **Error Handling**: Bundle handles API errors gracefully
- **Rate Limiting**: Throttling works as configured

## 🔍 Getting Credentials

1. Go to [Discogs Developer Settings](https://www.discogs.com/settings/developers)
2. Create a new application
3. Note down Consumer Key and Consumer Secret
4. Generate a Personal Access Token
