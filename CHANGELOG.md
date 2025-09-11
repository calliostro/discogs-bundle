# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.1.4](https://github.com/calliostro/discogs-bundle/releases/tag/v3.1.4) – 2025-09-11

### Added

- Comprehensive @throws documentation for exception handling in source files
- Additional edge case tests for HWIOauthTokenProvider to achieve 100% code coverage

### Fixed

- Deprecated getConfig() usage in functional tests replaced with future-safe implementation
- PHPStan warnings suppressed for defensive null checks in test assertions

### Changed

- Improved exception documentation specificity (XML loading exceptions vs generic exceptions)
- Enhanced test coverage from 96.9% to 100% with comprehensive OAuth token scenarios

---

## [3.1.3](https://github.com/calliostro/discogs-bundle/releases/tag/v3.1.3) – 2025-09-08

### Changed

- Enhanced CI workflow with comprehensive testing matrix (PHP 8.1–8.5, Symfony 6.4–8.0)
- Integrated PHP-CS-Fixer with Symfony coding standards (@Symfony + @Symfony:risky)
- Optimized code quality configuration and applied consistent formatting

### Added

- Initial CHANGELOG.md for better release tracking
- PHPStan static analysis at Level 8 with comprehensive error checking
- Composer scripts for testing, code style checks, and static analysis
- Extended GitHub Actions workflow with a comprehensive PHP / Symfony version matrix
- Support for legacy, feature, hotfix, and release branch patterns
- Manual workflow dispatch option for GitHub Actions
- .markdownlint.json for consistent documentation formatting
- Comprehensive test suite with 97.46% code coverage across all source files

### Fixed

- Fixed the static analysis warning in HWIOauthTokenProvider using type-safe method calls
- Resolved all PHPStan Level 8 issues throughout the codebase
- Applied Symfony coding standards consistently across all source files

---

## [3.1.2](https://github.com/calliostro/discogs-bundle/releases/tag/v3.1.2) – 2024-08-25

### Added

- PHP 8.5 beta compatibility – Early support for the upcoming PHP 8.5 release
- Symfony 8.0 beta testing – Ready for Symfony 8.0 when it arrives
- Enhanced stability – Improved build reliability and faster dependency resolution

### Changed

- Migrated from Travis CI to GitHub Actions for improved reliability
- Streamlined testing workflow for faster releases
- Optimized dependency testing matrix
- Updated CI status badge in README

### Fixed

- Better future-proofing – Early validation with next-generation PHP/Symfony combinations

---

## [3.1.1](https://github.com/calliostro/discogs-bundle/releases/tag/v3.1.1) – 2024-08-25

### Added

- Symfony 8.x support – Now compatible with the upcoming Symfony 8.0 release
- Extended CI testing matrix to include PHP 8.4 and 8.5 (beta)
- Comprehensive testing across Symfony 6.4 (LTS), 7.x, and 8.x versions

### Changed

- Enhanced compatibility testing with different dependency combinations

---

## [3.1.0](https://github.com/calliostro/discogs-bundle/releases/tag/v3.1.0) – 2024-08-18

### Fixed

- Fix Discogs authentication when using only consumer key/secret (OAuth optional)
- Suppress warnings if HWIOAuthBundle is not installed
- Improved error messages for missing or invalid OAuth configuration

### Changed

- All service classes are now `final` for better reliability and performance
- Added clear descriptions for configuration options
- Updated default user agent string
- README and UPGRADE guides rewritten with modern examples and clearer instructions
- Configuration documentation is now easier to understand

### Breaking Changes

- PHP 8.1+ required (previously 7.3+)
- Symfony 6.4+ or 7.x required (previously 5.x+)
- Support for older PHP and Symfony versions has been dropped

---

## [3.0.1](https://github.com/calliostro/discogs-bundle/releases/tag/v3.0.1) – 2022-07-16

### Added

- Symfony 6 support

---

## [3.0.0](https://github.com/calliostro/discogs-bundle/releases/tag/v3.0.0) – 2021-04-22

### Fixed

- Fix Discogs authentication with only consumer key/secret

### Changed

- Make service classes final
- Add description for configuration

---

## [2.0.1](https://github.com/calliostro/discogs-bundle/releases/tag/v2.0.1) – 2021-04-21

### Fixed

- Suppress warning if HWIOAuthBundle is not installed

### Changed

- Replace default user agent
- Update documentation

---

## [2.0.0](https://github.com/calliostro/discogs-bundle/releases/tag/v2.0.0) – 2021-04-17

### Added

- Support for Symfony 5
- First release of this fork

---

*Note: This repository follows semantic versioning starting from v3.1.3.*
*Previous releases were published without a formal changelog.*

## Version History

- **v3.x**: Legacy branch maintained for bug fixes only
- **v4.0.0+**: Active development branch with potential breaking changes
