# Padiernos: Multisite Platform

A production-ready, fully-featured multisite platform powering the Padiernos.me family of websites with enterprise-grade tools, modern development workflows, and comprehensive feature set.

**Project**: `padiernos/drupal` • **Version**: 1.1.0 • **License**: GPL-2.0-or-later

## Project Information

- **Name**: padiernos/drupal
- **Description**: A ready-to-use multisite platform offering smart defaults to get started quickly and enterprise-grade tools for marketers, designers, and content creators.
- **License**: GPL-2.0-or-later
- **Version**: 1.1.0
- **Repository**: [padiernos-me/public_html](https://github.com/padiernos-me/public_html)
- **Type**: Project (Composer)

---

## Overview

A comprehensive multisite platform serving multiple personal and professional websites under the padiernos.me domain. Built with modern web development practices, atomic design principles, and automated deployment workflows.

### Key Highlights

- **Drupal 11.3+ Framework** - Built on the latest Drupal core with performance optimizations
- **Multisite Architecture** supporting multiple domains from a single codebase
- **200+ Dependencies** with 140+ contributed modules in the ecosystem
- **Advanced Performance Optimization** with Lightning CSS, caching, and modern build tools
- **Automated Development Workflow** with optimized toolchain and quality assurance
- **Developer Tools** including PHPStan, Rector, debugging, and development utilities

### Live Sites

The platform currently powers these active websites:

- **[www.padiernos.me](https://www.padiernos.me)** - Main website
- **[cecie.padiernos.me](https://cecie.padiernos.me)** - Cecie's personal site
- **[danez.padiernos.me](https://danez.padiernos.me)** - Danez's personal site
- **[gaby.padiernos.me](https://gaby.padiernos.me)** - Gaby's personal site
- **[jason.padiernos.me](https://jason.padiernos.me)** - Jason's personal site
- **[mia.padiernos.me](https://mia.padiernos.me)** - Mia's personal site
- **[mike.padiernos.me](https://mike.padiernos.me)** - Mike's personal site
- **[rach.padiernos.me](https://rach.padiernos.me)** - Rach's personal site

All sites share the same codebase but maintain separate:
- Configuration (`sites/[domain]/config/`)
- Files (`sites/[domain]/files/`)
- Database settings
- Site-specific modules and themes

## Tech Stack

### Core Platform
- **Drupal 11.3+** - Web content management framework
- **PHP 8.4+** - Server-side language with OPcache
- **MySQL 8.0+/MariaDB 10.3+** - Database
- **Composer 2.x** - PHP dependency management with optimizations

### Frontend Development
- **Minim Theme** - Custom atomic design theme with modern CSS framework
- **Lightning CSS** - Ultra-fast CSS processing (100x faster than traditional tools)
- **PostCSS Pipeline** - Modern CSS with nesting, custom properties, and brand colors
- **Gulp 5** - Build automation and task runner
- **TypeScript** - Type checking for JavaScript modules
- **Stylelint** - CSS linting with logical property enforcement

### Performance & Optimization
- **APCu Autoloader** - Optimized class loading
- **Classmap Authoritative** - Production autoloader optimization
- **Redis/Memcache** - Advanced caching layers
- **BigPipe** - Progressive page loading
- **Image Optimization** - WebP conversion, responsive images, lazy loading

### DevOps & Deployment
- **Git** - Version control
- **PHPStan** - Static analysis
- **Rector** - Automated code upgrades

## Architecture

### Project Structure

```
├── web/                          # Web root
│   ├── modules/                  # Contributed and custom modules
│   ├── themes/                   # Themes directory
│   │   └── custom/
│   │       └── minim/            # Custom atomic design theme
│   │       └── stone/            # Alternative theme
│   ├── sites/
│   │   ├── default/              # Default site configuration
│   │   ├── mike.padiernos.me/    # Site-specific configuration
│   │   └── sites.php             # Multisite routing configuration
│   └── libraries/                # External libraries
├── sites/                        # External site configurations
│   ├── dev/                      # Development environment
│   └── mike/                     # Mike's site configuration
├── recipes/                      # Recipes for deployments
├── vendor/                       # Composer dependencies
├── scripts/                      # Utility scripts
│   ├── libraries-*.sh            # Library management
│   └── drush-aliases.sh          # Drush alias setup
├── composer.json                 # Project dependencies
├── phpstan.neon                  # Static analysis config
└── rector.php                    # Code upgrade config
```

### Theme Structure (Atomic Design)

```
web/themes/custom/minim/
├── source/
│   ├── 00-core/                  # Core styles & Minim CSS framework
│   ├── 01-atoms/                 # Basic elements (buttons, inputs)
│   ├── 02-molecules/             # Component groups
│   ├── 03-organisms/             # Complex components
│   ├── 04-symbiosis/             # Layout components
│   └── 05-synergy/               # Page templates
├── assets/                       # Compiled assets (CSS, JS)
├── gulpfile.js                   # Build configuration with Lightning CSS
├── package.json                  # Node dependencies
├── install.sh                    # Automated setup script
└── README.md                     # Theme documentation
```

## Key Features

### Content Management
- **Multi-content types** - Blog, Events, Case Studies, News, Projects, Pages
- **Media management** - Advanced image/video handling with thumbnails
- **Form builder** - Webform integration with popup support
- **User management** - Social auth, 2FA support, role management
- **SEO tools** - Automated meta tags, sitemaps, analytics integration
- **Layout system** - Flexible page building

### Performance & Optimization
- **Caching layers** - Redis/Memcache support, page caching, dynamic cache
- **Image optimization** - WebP conversion, responsive images, lazy loading
- **Code optimization** - Lightning CSS minification (100x faster), CSS/JS aggregation
- **Database optimization** - Query optimization, maintenance tools, APCu caching
- **Composer optimizations** - Classmap authoritative, APCu autoloader

### User Experience
- **Responsive Design** - Mobile-first approach with atomic design
- **Accessibility Tools** - Inclusive design features
- **Cookie Compliance** - GDPR support
- **Social Integration** - Multi-provider authentication
- **Enhanced Filters** - Advanced search and filtering UX

### Developer Experience
- **Component-based development** - Atomic design system
- **Modern CSS workflow** - Lightning CSS + PostCSS with nesting, custom properties
- **Type safety** - TypeScript declarations
- **Development tools** - Debugging utilities, code quality tools
- **Static analysis** - PHPStan and automated code upgrades
- **Library management** - Automated scripts for external dependencies

## Installation & Setup

### Prerequisites
- PHP 8.4 or higher
- MySQL 8.0+ or MariaDB 10.3+
- Composer 2.x
- Node.js 18+ (for theme development)
- Web server (Apache/Nginx)

### Quick Start

1. **Clone repository:**
   ```bash
   git clone <repository-url> padiernos
   cd padiernos
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up web server** to point to the `web/` directory

4. **Configure multisite** (if needed):
   ```bash
   # Edit sites.php for new domains
   vim web/sites/sites.php

   # Create site-specific directory
   mkdir web/sites/new-domain.com
   mkdir web/sites/new-domain.com/files
   chmod 755 web/sites/new-domain.com
   chmod 777 web/sites/new-domain.com/files
   ```

### Theme Development

```bash
cd web/themes/custom/minim

# Automated setup (installs dependencies and builds)
bash install.sh

# Manual setup
npm install

# One-time build with Lightning CSS
npm run run

# Development build with type checking
npm run build

# Watch for changes during development
gulp watch

# Type check TypeScript
npm run type-check

# Lint CSS
npm run lint:css
```

## Available Commands

### Composer Scripts
```bash
# Performance & Optimization
composer optimize                 # Optimize autoloader and performance
composer deploy:prod              # Deploy to production (optimized)

# Security & Updates
composer security:check           # Check for security vulnerabilities
composer security:check:full      # Full security audit including dev
composer update:check             # Check for outdated packages

# Library Management
composer libraries:update         # Update all libraries in web/libraries
composer libraries:status         # Check library status and versions
composer libraries:list           # List all installed libraries

# Theme Management
composer theme:minim:install      # Install/reinstall Minim theme
composer theme:minim:update       # Update Minim theme from repository

# Quality Assurance
composer qa                       # Run all quality checks
composer fix                      # Fix all issues automatically
composer rector:dry               # Run Rector dry-run for preview
composer rector:fix               # Apply Rector fixes

# Maintenance
composer nuke                     # Clean install (removes vendor and core)
composer nuke:full                # Complete reset with files cleanup
composer project:nuke             # Nuclear option - removes all project files
```

### Theme Development Scripts
```bash
cd web/themes/custom/minim

# Build & Development
npm run run                       # Build assets with Lightning CSS
npm run build                     # Type check + build
gulp watch                        # Development mode with hot reload

# Quality Checks
npm run type-check                # TypeScript validation
npm run lint:css                  # Lint CSS with Stylelint
npm run lint:css:fix              # Fix CSS lint issues
```

### PHPStan & Static Analysis
```bash
# Run PHPStan static analysis
vendor/bin/phpstan analyse

# Run with specific level
vendor/bin/phpstan analyse --level=5

# Generate baseline
vendor/bin/phpstan analyse --generate-baseline
```

## Multisite Management

### Project Configuration

This project uses Composer Drupal Lenient plugin to allow installation of modules with non-standard dependencies:

**Allowed modules with flexible constraints:**
- `drupal/better_module_dependencies`
- `drupal/better_parent`
- `drupal/better_search`
- `drupal/book_access_code`
- `drupal/book_tree_menu`
- `drupal/gin_lb`
- `drupal/social_post_facebook`
- `drupal/social_post_linkedin`
- `drupal/themable_forms`
- `drupal/image_url_formatter`

### Adding New Sites

```bash
# Add new site to sites.php
echo "'new-domain.com' => 'new-domain.com'," >> web/sites/sites.php

# Create site directory
mkdir web/sites/new-domain.com
mkdir web/sites/new-domain.com/files
chmod 755 web/sites/new-domain.com
chmod 777 web/sites/new-domain.com/files
```

### Site-Specific Configuration

Each site maintains its own:
- Configuration files
- File storage
- Database settings
- Site-specific customizations

## Deployment

### Production Deployment Workflow

1. **Optimize dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   # Or use the automated script
   composer deploy:prod
   ```

2. **Build optimized assets:**
   ```bash
   cd web/themes/custom/minim
   npm run build
   ```

3. **Deploy and verify:**
   ```bash
   # Verify deployment
   composer security:check
   ```

### Production Checklist

✅ **Pre-Deployment**
- [ ] Run all quality checks: `composer qa`
- [ ] Test in staging environment
- [ ] Backup database and files

✅ **Deployment**
- [ ] Pull latest code from repository
- [ ] Run `composer deploy:prod`
- [ ] Build theme assets

✅ **Post-Deployment**
- [ ] Verify site functionality
- [ ] Check error logs
- [ ] Monitor performance

### Performance Optimization

- **Caching**: Redis/Memcache for sessions, Varnish for pages
- **Assets**: CDN delivery, WebP images, Lightning CSS minification
- **Code**: APCu autoloader, classmap authoritative, database query optimization

## Performance & Toolchain Optimizations

### Modern CSS Pipeline

This project features a highly optimized CSS development pipeline:

#### Lightning CSS Integration
- **Native Performance**: Lightning CSS provides 100x faster CSS processing than traditional tools
- **Modern CSS Support**: Native support for nesting, custom properties, and modern selectors
- **Built-in Autoprefixer**: Automatic vendor prefixing with intelligent browser targeting
- **Advanced Minification**: Superior compression with better performance than other minifiers

#### PostCSS Pipeline Configuration
```javascript
// Optimized processor array in gulpfile.js
const processors = [
  require('postcss-nesting'),            // CSS nesting support
  require('postcss-fontsize'),           // Font size utilities
  require('postcss-brand-colors'),       // Brand color management
  require('postcss-lightningcss')({      // Lightning CSS processor
    browsers: '>= 0.25%',                // Modern browser targeting
    lightningcssOptions: {
      minify: true,                      // Advanced minification
      drafts: { nesting: true },         // Modern CSS features
      targets: { chrome: 80 << 16 }      // Optimal browser support
    }
  })
];
```

#### Stylelint Configuration
- **Lightning CSS Compatible**: Rules optimized for Lightning CSS processing
- **Logical CSS Properties**: Modern logical property enforcement
- **Performance Focused**: Streamlined rule set for faster linting

### Code Quality Tools
- **PHPStan**: Advanced static analysis
- **Rector**: Automated code upgrades
- **Automated Composer Optimization**: Performance tuning with classmap-authoritative and APCu
- **EditorConfig Standards**: Comprehensive formatting rules for consistent code style
- **Git Security**: Enhanced .gitignore with security-focused exclusions

### Library Management
Automated scripts for maintaining the `web/libraries` folder:

```bash
# Update all libraries
composer libraries:update

# Check library status and versions
composer libraries:status

# List all installed libraries
composer libraries:list

# Check for missing libraries
./scripts/libraries-missing.sh

# Clean unused libraries
./scripts/libraries-clean.sh
```

Scripts support both git repositories and npm packages with automatic version tracking.

## Testing & Quality Assurance

### Tools
- **PHPStan Level 5**: Static analysis
- **Rector**: Automated code upgrades and modernization
- **Stylelint**: Lightning CSS compatible linting with logical properties
- **Security Scanner**: Composer vulnerability checking

### Quick Commands
```bash
composer qa                    # Run all quality checks
composer fix                   # Auto-fix issues
composer security:check        # Security scan
vendor/bin/phpstan analyse     # Static analysis
```

## Project Statistics

- **200+ Total Dependencies** - Carefully curated ecosystem with core and contributed packages
- **140+ Contributed Modules** - Extensive module ecosystem
- **8 Active Multisites** - Centralized management across multiple domains
- **15+ PostCSS plugins** - Modern CSS processing with Lightning CSS
- **Atomic design system** - 5-level component hierarchy
- **TypeScript integration** - Type-safe development
- **Lightning CSS** - 100x faster CSS processing
- **PHPStan Level 5** - Advanced static analysis
- **Drupal 11.3+** - Latest stable framework with performance optimizations

## Theming & Components

### Available Themes

**Minim Theme** - Custom atomic design theme with Lightning CSS optimization
- **5-Level Hierarchy**: Core → Atoms → Molecules → Organisms → Symbiosis → Synergy
- **Single Directory Components** (SDC) with TypeScript support
- **Modern Tooling**: Lightning CSS, PostCSS, Gulp 5, hot reload
- **Framework**: [Minim CSS](https://github.com/michaelpadiernos/minim.css) with utilities and grid system

**Stone Theme** - Alternative theme implementation

**Quick Start**: `cd web/themes/custom/minim && bash install.sh && gulp watch`

## Security Features

- **Authentication**: 2FA, Social Auth, password policies
- **Best Practices**: HTTPS enforcement, regular updates, security scanning, permission audits

## Content Types & Media

### Content Types
**Blog Posts** • **Events** • **Case Studies** • **News Articles** • **Pages** • **Projects**

### Media Management
- **Types**: Images (responsive, WebP), Videos, PDFs, Audio, SVG
- **Features**: Bulk upload, optimization, lazy loading

## Recent Updates (v1.1.0)

**Performance**: Lightning CSS (100x faster), APCu autoloader, optimized Composer
**Developer Tools**: PHPStan Level 5, Rector automated upgrades, Stylelint configuration
**Automation**: Library management scripts, QA pipeline, security scanning
**Core**: 140+ modules, streamlined recipe system with 17 curated recipes

**Roadmap**: Advanced caching (Varnish/Redis), CDN integration, APM monitoring, CI/CD pipeline, automated testing

## Contributing

1. Create feature branch from `main`
2. Follow standards: PHP, Atomic Design + BEM (CSS), TypeScript (JS)
3. Run `composer qa && npm run type-check` before committing
4. Use conventional commits with issue references
5. Submit PR with detailed description

## Resources

**Docs**: [Minim CSS](https://github.com/michaelpadiernos/minim.css) • [Lightning CSS](https://lightningcss.dev/) • [Atomic Design](https://atomicdesign.bradfrost.com/)
**Internal**: [Theme Guide](web/themes/custom/minim/README.md) • [Multisite](web/sites/README.txt)

## License

**GPL-2.0-or-later** • **ISC** (Minim theme) • See individual component licenses for details.

---

**Maintainer**: M. Padiernos • **Version**: 1.2.0 • **Updated**: February 12, 2026
**Repository**: padiernos-me/public_html • [www.padiernos.me](https://www.padiernos.me)

---

Built with ❤️ inspired by Atomic Design and modern web technologies.
