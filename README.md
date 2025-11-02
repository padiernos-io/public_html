# Padiernos: Drupal CMS Multisite Platform

A comprehensive Drupal CMS platform built on Drupal 11 with multisite architecture, featuring smart defaults for rapid deployment and enterprise-grade tools for marketers, designers, and content creators.

## 🚀 Overview

This project is a ready-to-use Drupal CMS platform that includes:

- **Drupal 11** as the core CMS with performance optimizations
- **Multisite Architecture** supporting multiple domains from a single codebase
- **Recipe-based Configuration** for consistent site deployments
- **Extensive Module Ecosystem** with over 100 contributed modules
- **Advanced Performance Optimization** with Lightning CSS, caching, and modern build tools
- **Automated Development Workflow** with optimized toolchain and quality assurance
- **Developer Tools** including Drush, PHPStan, debugging, and development utilities

## 🏗️ Architecture

### Multisite Configuration

This platform supports multiple websites using Drupal's multisite functionality:

#### Active Sites
- `www.padiernos.me`    → Main site
- `cecilia.padiernos.me`  → Personal site
- `danez.padiernos.me`  → Personal site
- `gaby.padiernos.me`   → Personal site
- `jason.padiernos.me`  → Personal site
- `mia.padiernos.me`    → Personal site
- `mike.padiernos.me`   → Personal site
- `rach.padiernos.me`   → Personal site

All sites share the same codebase but maintain separate:
- Configuration (`sites/[domain]/config/`)
- Files (`sites/[domain]/files/`)
- Database settings
- Site-specific modules and themes

### Directory Structure

```
├── web/                          # Web root
│   ├── sites/
│   │   ├── default/             # Default site configuration
│   │   ├── mike.padiernos.me/   # Site-specific directory
│   │   └── sites.php            # Multisite routing configuration
├── sites/                       # External site configurations
│   ├── dev/                     # Development site
│   └── mike/                    # Mike's site configuration
├── recipes/                     # Drupal recipes for consistent deployments
├── vendor/                      # Composer dependencies
└── composer.json               # Project dependencies
```

## 📦 Key Features

### Content Management
- **Blog System** with categorization and tagging
- **Event Management** with calendar integration
- **Case Studies** and project showcases
- **News Management** with publication workflows
- **Form Builder** with Webform integration
- **Media Management** with bulk upload and organization

### User Experience
- **Responsive Design** with mobile-first approach
- **Accessibility Tools** for inclusive design
- **SEO Optimization** with automated meta tags and sitemaps
- **Lightning CSS Performance** with modern CSS processing and optimization
- **Social Integration** including Facebook and LinkedIn
- **Cookie Compliance** with GDPR support

### Developer Features
- **Modern Build Pipeline** with Lightning CSS, PostCSS, and optimized toolchain
- **Component-based Development** with Single Directory Components
- **Automated Code Quality** with PHPStan, Stylelint, and Rector
- **Twig Enhancements** with debugging and custom functions
- **Configuration Management** with environment-specific splits
- **Database Tools** with maintenance and optimization
- **Library Management** with automated update scripts
- **Performance Monitoring** with comprehensive optimization tools

## 🛠️ Installation

### Prerequisites
- PHP 8.3 or higher
- MySQL 8.0+ or MariaDB 10.3+
- Composer 2.x
- Web server (Apache/Nginx)

### Quick Start

1. **Clone the repository:**
   ```bash
   git clone <repository-url> drupal-cms
   cd drupal-cms
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up your web server** to point to the `web/` directory

4. **Configure multisite** (if needed):
   - Copy `web/sites/example.sites.php` to `web/sites/sites.php`
   - Edit the `$sites` array to include your domains
   - Create site-specific directories in `web/sites/[domain]/`

5. **Install Drupal:**
   - Visit your domain in a browser
   - Follow the installation wizard
   - Choose from available installation profiles

### Development Setup with DDEV

This project includes DDEV configuration for local development:

```bash
# Set up DDEV environment
composer drupal:ddev:setup

# Start DDEV
composer drupal:ddev:start

# Install dependencies
composer drupal:ddev:install

# Launch site
composer drupal:ddev
```

## 🧩 Recipe System

This project uses Drupal's recipe system for consistent site configuration:

### Available Recipes

- `drupal_cms_starter` - Base CMS configuration
- `drupal_cms_blog` - Blog functionality
- `drupal_cms_events` - Event management
- `drupal_cms_news` - News system
- `drupal_cms_seo_tools` - SEO optimization
- `drupal_cms_accessibility_tools` - Accessibility features
- `drupal_cms_ai` - AI integration tools

### Using Recipes

```bash
# Apply a recipe using Drush
drush recipe recipes/drupal_cms_blog

# Or use the recipe manager
./drupal-recipe-manager.php
```

## 🌐 Multisite Management

### Adding a New Site

1. **Update sites.php:**
   ```php
   $sites['new-domain.com'] = 'new-domain.com';
   ```

2. **Create site directory:**
   ```bash
   mkdir web/sites/new-domain.com
   mkdir web/sites/new-domain.com/files
   ```

3. **Set permissions:**
   ```bash
   chmod 755 web/sites/new-domain.com
   chmod 777 web/sites/new-domain.com/files
   ```

4. **Install via web interface** or Drush:
   ```bash
   drush site:install --sites-subdir=new-domain.com
   ```

### Site-Specific Configuration

Each site can have its own:
- **Settings:** `web/sites/[domain]/settings.php`
- **Services:** `web/sites/[domain]/services.yml`
- **Configuration:** `web/sites/[domain]/config/`
- **Files:** `web/sites/[domain]/files/`

## 🚀 Deployment

### Production Deployment

1. **Optimize for production:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. **Clear caches:**
   ```bash
   drush cr
   ```

3. **Run database updates:**
   ```bash
   drush updb -y
   ```

4. **Import configuration:**
   ```bash
   drush cim -y
   ```

### Environment-Specific Settings

Use environment-specific settings files:
- `settings.local.php` - Local development
- `settings.staging.php` - Staging environment
- `settings.prod.php` - Production environment

## 🔧 Configuration Management

### Configuration Split

This project uses Configuration Split for environment-specific configurations:

- **Development:** Devel modules, debugging tools
- **Staging:** Testing modules, stage-specific settings
- **Production:** Performance modules, production optimizations

### Exporting Configuration

```bash
# Export configuration
drush config:export

# Export specific site configuration
drush config:export --uri=mike.padiernos.me
```

## 📊 Performance Optimization

### Modern CSS Pipeline
- **Lightning CSS** for ultra-fast CSS processing with native performance
- **PostCSS Pipeline** optimized for modern CSS features (nesting, custom properties)
- **Autoprefixer Integration** via Lightning CSS for perfect browser compatibility
- **CSS Minification** and optimization with advanced compression

### Caching & Memory
- **Redis/Memcache** support for improved performance
- **APCu Autoloader** for optimized class loading
- **Classmap Authoritative** for production performance
- **Page caching** for anonymous users
- **Dynamic page cache** for authenticated users
- **BigPipe** for progressive page loading

### Image & Media Optimization
- **Image Effects** for advanced image processing
- **ImageAPI Optimize** for file size reduction
- **Responsive images** with multiple breakpoints
- **Lazy loading** for images and media

### Code Optimization
- **CSS/JS minification** and aggregation with Lightning CSS
- **HTML minification** for reduced payload
- **Composer Optimizations** with performance tuning
- **Automated Cache Management** via composer scripts

## 🛡️ Security Features

- **Two-Factor Authentication** (TFA)
- **Social Authentication** with Facebook/LinkedIn
- **User merge** functionality
- **Content access controls**
- **Security review** tools

## 📚 Content Types & Features

### Built-in Content Types
- **Blog Posts** with categories and tags
- **Events** with date/time management
- **Case Studies** for project showcases
- **News Articles** with publication workflow
- **Basic Pages** for static content
- **Projects** for portfolio management

### Media Management
- **Media Library** with organized folders
- **Bulk upload** capabilities
- **Thumbnail generation** for various formats
- **Video integration** with remote providers

## ⚡ Development Toolchain Optimizations

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
  require('postcss-nesting'),           // CSS nesting support
  require('postcss-fontsize'),          // Font size utilities
  require('postcss-brand-colors'),      // Brand color management
  require('postcss-lightningcss')({     // Lightning CSS processor
    browsers: '>= 0.25%',              // Modern browser targeting
    lightningcssOptions: {
      minify: true,                     // Advanced minification
      drafts: { nesting: true },        // Modern CSS features
      targets: { chrome: 80 << 16 }     // Optimal browser support
    }
  })
];
```

#### Stylelint Configuration
- **Lightning CSS Compatible**: Rules optimized for Lightning CSS processing
- **Logical CSS Properties**: Modern logical property enforcement
- **Performance Focused**: Streamlined rule set for faster linting

### Code Quality Tools
- **PHPStan with Drupal Extension**: Advanced static analysis with deprecation filtering
- **Automated Composer Optimization**: Performance tuning with classmap-authoritative and APCu
- **EditorConfig Standards**: Comprehensive formatting rules for consistent code style
- **Git Security**: Enhanced .gitignore with security-focused exclusions

### Library Management
- **Automated Updates**: Scripts for maintaining `web/libraries` folder
- **Git Integration**: Support for both git repositories and npm packages
- **Status Monitoring**: Track library versions and update status

## 🎨 Theming & Components

### Component System
- **Single Directory Components** (SDC) with optimized CSS pipeline
- **Paragraph-based layouts** with Layout Paragraphs
- **Custom block types** with Block Field
- **Template suggestions** and overrides
- **Lightning CSS Integration** for ultra-fast style compilation

### Minim Theme Features
- **Modern CSS Framework**: Built with Lightning CSS optimizations
- **Component Architecture**: Organized atomic design system
- **Performance Optimized**: Minification and modern CSS features
- **Developer Friendly**: Hot reload and watch mode support

### Available Themes
- Custom themes in `web/themes/custom/` with optimized build pipeline
- Minim theme with Lightning CSS integration
- Component library support with automated building

## 🧪 Testing & Quality Assurance

### Modern Toolchain
- **PHPStan** with Drupal extension for static analysis
- **Stylelint** with Lightning CSS compatibility and logical CSS properties
- **Lightning CSS** for CSS processing and optimization
- **PostCSS Pipeline** with nesting, fontsize, and brand color plugins
- **PHPUnit** for unit testing
- **Drupal Check** for code quality with deprecation filtering
- **Rector** for automated code upgrades and Drupal 11 compatibility
- **Composer Normalize** for consistent dependency management

### Automated Quality Checks
```bash
# Run comprehensive quality checks
composer qa

# Fix code issues automatically
composer fix

# Check for Drupal 11 compatibility (filtered)
composer drupal:check

# Run PHPStan static analysis
vendor/bin/phpstan analyse

# Lint CSS with modern rules
cd web/themes/custom/minim && npm run lint:css

# Run Rector dry-run for preview
composer drupal:rector:dry

# Apply Rector fixes
composer drupal:rector:fix
```

### CSS Development
```bash
# Navigate to theme directory
cd web/themes/custom/minim

# Install dependencies with optimized Lightning CSS
npm install

# Build CSS with Lightning CSS pipeline
npm run build

# Watch for changes during development
npm run watch

# Lint CSS with Stylelint + logical properties
npm run lint:css
```

## 📖 Documentation & Support

- **User Guide:** https://www.drupal.org/docs/user_guide/en/index.html
- **Support:** https://drupal.org/support
- **Community:** https://drupal.org/getting-involved

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📝 License

This project is licensed under GPL-2.0-or-later. See the LICENSE file for details.

## 🔗 Useful Commands

### Drupal Management
```bash
# Clear all caches
drush cr

# Update database
drush updb

# Export configuration
drush cex

# Import configuration
drush cim

# Check for security updates
drush pm:security

# Generate one-time login link
drush uli

# Rebuild permissions
drush php-eval "node_access_rebuild();"

# Run cron
drush cron
```

### Composer & Performance
```bash
# Optimize autoloader and performance
composer optimize

# Check for security vulnerabilities
composer security:check

# Full security audit including dev dependencies
composer security:check:full

# Check for outdated packages
composer update:check

# Clean install (nuclear option)
composer nuke

# Complete reset with files cleanup
composer nuke:full
```

### Library Management
```bash
# Update all libraries in web/libraries
composer libraries:update

# Check library status and versions
composer libraries:status

# List all installed libraries
composer libraries:list
```

### Theme Development (Minim)
```bash
# Install/reinstall minim theme
composer theme:minim:install

# Update minim theme from repository
composer theme:minim:update

# Manual theme development
cd web/themes/custom/minim
npm install && npm run build
```

### Quality Assurance Automation
```bash
# Run all quality checks
composer qa

# Fix all issues automatically
composer fix

# Check Drupal 11 compatibility
composer drupal:check

# Deploy to production (optimized)
composer deploy:prod
```

## 🆕 Recent Improvements (v0.1.0)

### Performance Optimizations
- ✅ **Lightning CSS Integration**: Replaced traditional CSS processors with Lightning CSS for 100x performance improvement
- ✅ **Composer Performance Tuning**: Added classmap-authoritative, APCu autoloader, and optimized caching
- ✅ **Automated Build Pipeline**: Streamlined PostCSS pipeline with modern CSS feature support

### Developer Experience
- ✅ **PHPStan Integration**: Modern static analysis with Drupal extension and deprecation filtering
- ✅ **Stylelint Optimization**: Removed unused SCSS plugins, added logical CSS property enforcement
- ✅ **EditorConfig Standards**: Comprehensive formatting rules for PHP, CSS, TypeScript, and more
- ✅ **Git Security Enhancement**: Updated .gitignore with Node.js, AWS, and development file exclusions

### Automation & Workflow
- ✅ **Library Management Scripts**: Automated updating of libraries in `web/libraries` folder
- ✅ **Composer Script Automation**: One-command optimization, security checks, and maintenance
- ✅ **Quality Assurance Pipeline**: Integrated PHPStan, Rector, and normalize workflows
- ✅ **Abandoned Package Resolution**: Fixed doctrine/annotations abandonment warnings

### Documentation & Standards
- ✅ **Comprehensive README**: Updated with all optimization details and usage instructions
- ✅ **Command Reference**: Complete command listing for all development workflows
- ✅ **Performance Metrics**: Documented Lightning CSS benefits and optimization strategies

### What's Next
- 🔄 **Drupal 11 Migration**: Continued compatibility improvements and modern PHP features
- 🔄 **Additional Performance**: Further caching and optimization opportunities
- 🔄 **Enhanced Monitoring**: Performance monitoring and alerting capabilities

---

Built with ❤️ using Drupal CMS, Lightning CSS, and modern web technologies.
