# Drush Site Aliases Configuration

This directory contains comprehensive Drush site aliases for all sites configured in `web/sites/sites.php`.

## Files Created

### `padiernos.site.yml`
Family member site aliases that all use the `padiernos.me` site directory:
- www (www.padiernos.me)
- cecilia (cecilia.padiernos.me)
- danez (danez.padiernos.me)
- gaby (gaby.padiernos.me)
- jason (jason.padiernos.me)
- mia (mia.padiernos.me)
- rach (rach.padiernos.me)

### `mike.site.yml`
Mike's personal site aliases that use the `mike.padiernos.me` site directory:
- mike (production)
- mike.dev (development)
- mike.local (local development)

### `all.site.yml`
Complete configuration with environment variants:
- **Production**: `@prod.www`, `@prod.mike`, `@prod.cecilia`, etc.
- **Development**: `@dev.www`, `@dev.mike`, `@dev.cecilia`, etc.
- **Local**: `@local.www`, `@local.mike`

### `self.site.yml` (Updated)
Quick access aliases for local development:
- dev (localhost development)
- prod (main production site)
- local (local port-based)
- family (legacy compatibility)

## Usage Examples

```bash
# Use specific site aliases
drush @prod.www status
drush @prod.mike cr
drush @dev.cecilia uli

# Family sites (all use same database)
drush @cecilia status
drush @gaby cr
drush @jason uli

# Mike's personal site (separate database)
drush @mike status
drush @mike.dev cr
drush @mike.local uli

# Environment-specific operations
drush @prod.www sql:dump > backup.sql
drush @dev.www sql:cli
drush @local.www browse
```

## Management Commands

```bash
# Via Composer
composer run drush:aliases:list     # List all aliases
composer run drush:aliases:test     # Test key aliases
composer run drush:aliases:status   # Show status and counts

# Direct script usage
./scripts/drush-aliases.sh list
./scripts/drush-aliases.sh test
./scripts/drush-aliases.sh status
```

## Site Configuration Reference

Based on `web/sites/sites.php`:
```php
$sites = [
  'www.padiernos.me'   => 'padiernos.me',     // Uses padiernos.me directory
  'cecilia.padiernos.me' => 'padiernos.me',   // Shares same database/config
  'danez.padiernos.me' => 'padiernos.me',     // Shares same database/config
  'gaby.padiernos.me'  => 'padiernos.me',     // Shares same database/config
  'jason.padiernos.me' => 'padiernos.me',     // Shares same database/config
  'mia.padiernos.me'   => 'padiernos.me',     // Shares same database/config
  'mike.padiernos.me'  => 'padiernos.me',     // Actually uses mike.padiernos.me directory
  'rach.padiernos.me'  => 'padiernos.me'      // Shares same database/config
];
```

## Directory Structure
```
web/sites/
├── default/                 # Default site
├── mike.padiernos.me/      # Mike's personal site (separate)
└── sites.php              # Multi-site configuration
```

## Notes

- **Family sites** (cecilia, danez, gaby, jason, mia, rach, www) all share the same Drupal installation and database
- **Mike's site** has its own separate directory and database
- All aliases point to `localhost` for local development
- Production aliases use HTTPS, development uses HTTP
- Path aliases included for drush script location and dump directory
