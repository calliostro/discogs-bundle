# Upgrade Guide

## Upgrading from 3.0.x to 3.1.0

### 🔧 System Requirements

**Before upgrading, ensure your system meets the new requirements:**

- **PHP 8.1+** (previously 7.3+)
- **Symfony 6.4+ or 7.x** (previously 5.x+)

### 📦 Installation

```bash
composer require calliostro/discogs-bundle:^3.1
```

### ✅ What's Included

- **Zero Code Changes Required** – All existing configurations and code continue to work
- **Better Error Messages** – Clearer validation messages when OAuth is misconfigured
- **Improved Performance** – Optimized internal service handling
- **Enhanced Documentation** – Updated examples and configuration guides

### 🚨 Potential Issues

#### If you're using PHP < 8.1

```bash
# Update your PHP version first
php --version  # Should show 8.1 or higher
```

#### If you're using Symfony < 6.4

```bash
# Check your Symfony version
composer show symfony/framework-bundle | grep versions

# Upgrade Symfony first
composer require symfony/framework-bundle:^6.4
```

### 🔍 Testing Your Upgrade

After upgrading, test your Discogs integration:

```php
// Basic test - should work without changes
$artist = $discogs->getArtist(['id' => 8760]);
echo $artist['name']; // Should display artist name
```

### 💡 Configuration Improvements

Your existing configuration continues to work, but you can now benefit from:

#### Clearer Error Messages

```yaml
# config/packages/calliostro_discogs.yaml
calliostro_discogs:
  oauth:
    enabled: true
    # If consumer_key is missing, you'll get a clearer error message
```

#### Better Documentation

Check the updated README.md for modern examples and best practices.

---

**Need Help?**

- [Create an issue](https://github.com/calliostro/discogs-bundle/issues) if you encounter problems
- [Check the documentation](https://github.com/calliostro/discogs-bundle#readme) for updated examples
