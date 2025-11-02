#!/bin/bash

# =============================================================================
# Show status of all libraries with version information
# =============================================================================

# Load common configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/libraries-common.sh"

cd "$SCRIPT_DIR"

echo -e "${BLUE}📊 Library Status Report${NC}"

ensure_libraries_dir

if [ ! "$(ls -A "$LIBRARIES_DIR" 2>/dev/null)" ]; then
    echo -e "${YELLOW}📭 No libraries found in $LIBRARIES_DIR${NC}"
    exit 0
fi

echo -e "\n${GREEN}📋 Library Status:${NC}"

# Check status of git repositories
git_repos_found=false
for lib_dir in "$LIBRARIES_DIR"/*; do
    if [ -d "$lib_dir" ] && [ -d "$lib_dir/.git" ]; then
        if [ "$git_repos_found" = false ]; then
            echo -e "\n${PURPLE}🔗 Git Repositories:${NC}"
            git_repos_found=true
        fi

        lib_name=$(basename "$lib_dir")

        if should_skip_library "$lib_name"; then
            echo -e "  ${YELLOW}⏭️  ${lib_name} (managed by Drupal)${NC}"
            continue
        fi

        cd "$lib_dir"

        # Check git status
        if git diff-index --quiet HEAD -- 2>/dev/null; then
            status_icon="${GREEN}✅${NC}"
            status_text="clean"
        else
            status_icon="${YELLOW}⚠️${NC}"
            status_text="modified"
        fi

        # Get commit info
        commit_info=$(git log -1 --format='%h %s' 2>/dev/null || echo "no commits")
        branch=$(git branch --show-current 2>/dev/null || echo "unknown")

        echo -e "  ${status_icon} ${CYAN}${lib_name}${NC} (${branch}): ${commit_info} - ${status_text}"

        cd - > /dev/null
    fi
done

# Check npm/other libraries
other_libs_found=false
for lib_dir in "$LIBRARIES_DIR"/*; do
    if [ -d "$lib_dir" ] && [ ! -d "$lib_dir/.git" ]; then
        if [ "$other_libs_found" = false ]; then
            echo -e "\n${BLUE}📦 Other Libraries:${NC}"
            other_libs_found=true
        fi

        lib_name=$(basename "$lib_dir")

        if should_skip_library "$lib_name"; then
            echo -e "  ${YELLOW}⏭️  ${lib_name} (managed by Drupal)${NC}"
            continue
        fi

        # Determine version and type
        if [ -f "$lib_dir/package.json" ]; then
            version=$(grep '"version"' "$lib_dir/package.json" 2>/dev/null | sed 's/.*"version": *"\([^"]*\)".*/\1/' || echo "unknown")
            type="npm"
        elif [ -f "$lib_dir/bower.json" ]; then
            version=$(grep '"version"' "$lib_dir/bower.json" 2>/dev/null | sed 's/.*"version": *"\([^"]*\)".*/\1/' || echo "unknown")
            type="bower"
        else
            version="unknown"
            type="manual"
        fi

        echo -e "  ${GREEN}📁${NC} ${CYAN}${lib_name}${NC} (${type}): v${version}"
    fi
done

# Summary statistics
total_count=$(ls -1 "$LIBRARIES_DIR" 2>/dev/null | wc -l)
git_count=$(find "$LIBRARIES_DIR" -name ".git" -type d 2>/dev/null | wc -l)

echo -e "\n${BLUE}📊 Summary:${NC}"
echo -e "  Libraries directory: ${LIBRARIES_DIR}"
echo -e "  Total libraries: ${GREEN}${total_count}${NC}"
echo -e "  Git repositories: ${GREEN}${git_count}${NC}"
echo -e "  Other libraries: ${GREEN}$((total_count - git_count))${NC}"

echo -e "\n${YELLOW}💡 Tips:${NC}"
echo -e "  • Use 'composer run libraries:update' to update all libraries"
echo -e "  • Use 'composer run libraries:clean' to clean git repositories"
echo -e "  • Use 'drush libraries-list' to see Drupal's library requirements"
