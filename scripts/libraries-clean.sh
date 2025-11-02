#!/bin/bash

# =============================================================================
# Clean git repositories (reset --hard and clean -fd)
# =============================================================================

# Load common configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/libraries-common.sh"

cd "$SCRIPT_DIR"

echo -e "${GREEN}🧹 Cleaning git repositories in $LIBRARIES_DIR${NC}"

ensure_libraries_dir

# Counters
CLEANED_COUNT=0
SKIPPED_COUNT=0

# Clean git repositories
for lib_dir in "$LIBRARIES_DIR"/*; do
    if [ -d "$lib_dir" ] && [ -d "$lib_dir/.git" ]; then
        lib_name=$(basename "$lib_dir")

        if should_skip_library "$lib_name"; then
            echo -e "${YELLOW}⏭️  Skipping $lib_name (managed by Drupal)${NC}"
            ((SKIPPED_COUNT++))
            continue
        fi

        echo -e "${BLUE}🧹 Cleaning $lib_name${NC}"
        cd "$lib_dir"

        # Reset to HEAD and clean untracked files
        git reset --hard HEAD 2>/dev/null
        git clean -fd 2>/dev/null

        echo -e "${GREEN}✅ $lib_name cleaned successfully${NC}"
        ((CLEANED_COUNT++))

        cd - > /dev/null
    fi
done

# Count non-git libraries for summary
non_git_count=0
for lib_dir in "$LIBRARIES_DIR"/*; do
    if [ -d "$lib_dir" ] && [ ! -d "$lib_dir/.git" ]; then
        ((non_git_count++))
    fi
done

echo -e "\n${GREEN}🎉 Repository cleaning completed!${NC}"

# Show summary
echo -e "\n${BLUE}📊 Cleaning Summary:${NC}"
echo -e "  ${GREEN}🧹 Cleaned: $CLEANED_COUNT${NC}"
echo -e "  ${YELLOW}⏭️  Skipped: $SKIPPED_COUNT${NC}"
echo -e "  ${CYAN}📁 Non-git libraries: $non_git_count${NC}"

echo -e "\n${YELLOW}💡 Note: Only git repositories were cleaned. Non-git libraries remain unchanged.${NC}"
