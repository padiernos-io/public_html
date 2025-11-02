#!/bin/bash

# =============================================================================
# Drush Site Alias Management Script
# =============================================================================

cd "$(dirname "$0")/.."

echo "🔧 Drush Site Alias Management"
echo "================================"

case "${1:-help}" in
    "list"|"ls")
        echo "📋 Available site aliases:"
        drush site:alias --format=list
        ;;
    "test")
        echo "🧪 Testing site aliases..."
        echo ""

        # Test a few key aliases
        aliases=("@prod.www" "@prod.mike" "@dev.www" "@local.www")

        for alias in "${aliases[@]}"; do
            echo -n "Testing $alias... "
            if drush $alias status --format=list --field=bootstrap 2>/dev/null | grep -q "Successful"; then
                echo "✅"
            else
                echo "❌"
            fi
        done
        ;;
    "validate")
        echo "✅ Validating alias files..."

        for file in drush/sites/*.yml; do
            if [ -f "$file" ]; then
                echo "Checking $(basename "$file")..."
                # Basic YAML syntax check
                if command -v yamllint >/dev/null 2>&1; then
                    yamllint "$file" || echo "⚠️  YAML validation failed for $file"
                else
                    echo "ℹ️  yamllint not available, skipping syntax check"
                fi
            fi
        done
        ;;
    "status")
        echo "📊 Site alias status:"
        echo ""
        echo "Alias files found:"
        ls -la drush/sites/*.yml | while read -r line; do
            file=$(echo "$line" | awk '{print $9}')
            if [ -n "$file" ]; then
                count=$(grep -c "^[a-zA-Z].*:" "$file" 2>/dev/null || echo "0")
                echo "  📄 $(basename "$file"): $count aliases"
            fi
        done

        echo ""
        echo "Total aliases: $(drush site:alias --format=list | wc -l)"
        ;;
    "help"|*)
        echo "Usage: $0 [command]"
        echo ""
        echo "Commands:"
        echo "  list      Show all available aliases"
        echo "  test      Test key aliases for connectivity"
        echo "  validate  Validate alias file syntax"
        echo "  status    Show alias files and counts"
        echo "  help      Show this help message"
        echo ""
        echo "Examples:"
        echo "  $0 list"
        echo "  $0 test"
        echo "  drush @prod.www status"
        echo "  drush @dev.mike cr"
        ;;
esac
