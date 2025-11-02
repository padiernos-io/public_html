#!/bin/bash

# =============================================================================
# List all libraries in the web/libraries directory
# =============================================================================

# Load common configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/libraries-common.sh"

cd "$SCRIPT_DIR"

echo -e "${BLUE}📋 Listing libraries in $LIBRARIES_DIR${NC}"

ensure_libraries_dir

if [ ! "$(ls -A "$LIBRARIES_DIR" 2>/dev/null)" ]; then
    echo -e "${YELLOW}📭 No libraries found in $LIBRARIES_DIR${NC}"
    exit 0
fi

echo -e "\n${GREEN}📚 Available Libraries:${NC}"

# List libraries with details
for lib_dir in "$LIBRARIES_DIR"/*; do
    if [ -d "$lib_dir" ]; then
        lib_name=$(basename "$lib_dir")

        # Determine library type and version
        if [ -f "$lib_dir/package.json" ]; then
            version=$(grep '"version"' "$lib_dir/package.json" 2>/dev/null | sed 's/.*"version": *"\([^"]*\)".*/\1/' || echo "unknown")
            type="📦 npm"
        elif [ -f "$lib_dir/bower.json" ]; then
            version=$(grep '"version"' "$lib_dir/bower.json" 2>/dev/null | sed 's/.*"version": *"\([^"]*\)".*/\1/' || echo "unknown")
            type="📦 bower"
        elif [ -d "$lib_dir/.git" ]; then
            cd "$lib_dir"
            version=$(git describe --tags --abbrev=0 2>/dev/null || git rev-parse --short HEAD 2>/dev/null || echo "unknown")
            type="🔗 git"
            cd - > /dev/null
        else
            version="unknown"
            type="📁 manual"
        fi

        # Skip indicator
        if should_skip_library "$lib_name"; then
            skip_indicator=" ${YELLOW}(managed by Drupal)${NC}"
        else
            skip_indicator=""
        fi

        echo -e "  ${type} ${CYAN}${lib_name}${NC}: v${version}${skip_indicator}"
    fi
done | sort

# Summary
total_count=$(ls -1 "$LIBRARIES_DIR" 2>/dev/null | wc -l)
git_count=$(find "$LIBRARIES_DIR" -name ".git" -type d 2>/dev/null | wc -l)

echo -e "\n${BLUE}📊 Summary:${NC}"
echo -e "  Total libraries: ${GREEN}${total_count}${NC}"
echo -e "  Git repositories: ${GREEN}${git_count}${NC}"
echo -e "  Other libraries: ${GREEN}$((total_count - git_count))${NC}"
