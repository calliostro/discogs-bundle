# Integration Test Setup

## Test Strategy

Integration tests are **separated from the CI pipeline** to prevent:

- 🚫 Rate limiting (429 Too Many Requests)
- 🚫 Flaky builds due to network issues
- 🚫 Dependency on external API availability
- 🚫 Slow build times (2+ minutes vs. 0.4 seconds)

## Running Tests

```bash
# Unit tests only (CI default - fast & reliable)
composer test

# Integration tests only (manual - requires API access)
composer test-integration  

# All tests together (local development)
composer test-all
```

## Test Levels

### 1. Public API Tests (Always Run)

- File: `tests/Integration/PublicApiIntegrationTest.php`
- No credentials required
- Tests public endpoints through Bundle: artists, releases, labels, masters
- Safe for forks and pull requests

### 2. Authentication Levels Test (Conditional)

- File: `tests/Integration/AuthenticatedApiIntegrationTest.php`
- Requires environment variables below
- Tests Bundle authentication configuration:
  - Level 2: Consumer credentials (search)
  - Level 3: Personal token (user data)
  - Level 4: OAuth tokens (direct library usage)

## GitHub Secrets Required

To enable authenticated integration tests in CI/CD, add these secrets to your GitHub repository:

Navigate to: **Repository Settings → Secrets and variables → Actions**

| Secret Name                     | Description                      | Where to get it                                                           |
|---------------------------------|----------------------------------|---------------------------------------------------------------------------|
| `DISCOGS_CONSUMER_KEY`          | Your Discogs app consumer key    | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |
| `DISCOGS_CONSUMER_SECRET`       | Your Discogs app consumer secret | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |
| `DISCOGS_PERSONAL_ACCESS_TOKEN` | Your personal access token       | [Discogs Developer Settings](https://www.discogs.com/settings/developers) |

## Local Development

### Quick Start

```bash
# Run public tests only (no credentials needed)
vendor/bin/phpunit tests/Integration/PublicApiIntegrationTest.php

# Run authentication tests (requires env vars below)
vendor/bin/phpunit tests/Integration/AuthenticatedApiIntegrationTest.php

# Run all integration tests
vendor/bin/phpunit tests/Integration/ --testdox
```

## Safety Notes

- Public tests are safe for any environment
- Authentication tests will be skipped if secrets are missing
- No credentials are logged or exposed in the test output
- Tests use read-only operations only (no data modification)

## Getting Credentials

### Environment Variables Setup

For authenticated tests, set these environment variables:

```bash
# PowerShell
$env:DISCOGS_CONSUMER_KEY="your-consumer-key"
$env:DISCOGS_CONSUMER_SECRET="your-consumer-secret"
$env:DISCOGS_PERSONAL_ACCESS_TOKEN="your-personal-access-token"

# CMD
set DISCOGS_CONSUMER_KEY=your-consumer-key
set DISCOGS_CONSUMER_SECRET=your-consumer-secret
set DISCOGS_PERSONAL_ACCESS_TOKEN=your-personal-access-token
```

### Getting Credentials

1. Go to [Discogs Developer Settings](https://www.discogs.com/settings/developers)
2. Create a new application
3. Note down Consumer Key and Consumer Secret
4. Generate a Personal Access Token

## Example GitHub Actions Workflow

```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v4
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.1
        
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
      
    - name: Run unit tests
      run: composer test-unit
      
    - name: Run public integration tests
      run: composer test-integration-public
      
    - name: Run authenticated integration tests
      run: composer test-integration
      env:
        DISCOGS_CONSUMER_KEY: ${{ secrets.DISCOGS_CONSUMER_KEY }}
        DISCOGS_CONSUMER_SECRET: ${{ secrets.DISCOGS_CONSUMER_SECRET }}
        DISCOGS_PERSONAL_ACCESS_TOKEN: ${{ secrets.DISCOGS_PERSONAL_ACCESS_TOKEN }}
```

## Bundle-Specific Integration Tests

These integration tests focus on:

1. **Bundle Integration**: Test that the Bundle correctly configures the Discogs API client
2. **Symfony Integration**: Verify that services are properly wired
3. **Configuration**: Ensure Bundle configuration is correctly applied
4. **Error Handling**: Test how the Bundle handles API errors
5. **Rate Limiting**: Verify that rate limiting works properly

## Additional Safety Notes

- ⚠️ Rate limiting may occur with many tests (Discogs API limits)
- 🔄 Tests use exponential backoff retry logic
- 📊 Bundle throttling is tested (reactive approach)
