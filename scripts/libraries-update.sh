#!/bin/bash

# =============================================================================
# Update all existing libraries (git pull for git repos, npm update for npm packages)
# =============================================================================

# Load common configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/libraries-common.sh"

cd "$SCRIPT_DIR"

echo -e "${GREEN}🔄 Updating existing libraries in $LIBRARIES_DIR${NC}"

ensure_libraries_dir

# Counters
UPDATED_COUNT=0
FAILED_COUNT=0
SKIPPED_COUNT=0

# Function to update git repository
update_git_repo() {
    local dir=$1
    local name=$2

    if [ -d "$LIBRARIES_DIR/$dir/.git" ]; then
        echo -e "${BLUE}📦 Updating $name${NC}"
        cd "$LIBRARIES_DIR/$dir"

        # Check if there are uncommitted changes
        if ! git diff-index --quiet HEAD --; then
            echo -e "${YELLOW}⚠️  Warning: $name has uncommitted changes${NC}"
            git status --porcelain
        fi

        # Pull latest changes
        if git pull origin main 2>/dev/null || git pull origin master 2>/dev/null; then
            echo -e "${GREEN}✅ $name updated successfully${NC}"
            ((UPDATED_COUNT++))
        else
            echo -e "${YELLOW}⚠️  Could not update $name (possibly no remote or network issue)${NC}"
            ((FAILED_COUNT++))
        fi

        cd - > /dev/null
    else
        echo -e "${YELLOW}⏭️  Skipping $name (not a git repository)${NC}"
        ((SKIPPED_COUNT++))
    fi
}

# Function to update npm-managed library
update_npm_library() {
    local dir=$1
    local name=$2
    local package=$3

    echo -e "${BLUE}📦 Updating $name via npm${NC}"

    # Create temporary directory for npm operations
    local temp_dir=$(mktemp -d)
    cd "$temp_dir"

    # Install latest version
    if npm install "$package" --no-save 2>/dev/null; then
        # Copy files to libraries directory
        if [ -d "node_modules/$package/dist" ]; then
            cp -r "node_modules/$package/dist/"* "$SCRIPT_DIR/$LIBRARIES_DIR/$dir/" 2>/dev/null || true
        elif [ -d "node_modules/$package/build" ]; then
            cp -r "node_modules/$package/build/"* "$SCRIPT_DIR/$LIBRARIES_DIR/$dir/" 2>/dev/null || true
        else
            cp -r "node_modules/$package/"* "$SCRIPT_DIR/$LIBRARIES_DIR/$dir/" 2>/dev/null || true
        fi

        echo -e "${GREEN}✅ $name updated successfully${NC}"
        ((UPDATED_COUNT++))
    else
        echo -e "${YELLOW}⚠️  Could not update $name via npm (package may not exist)${NC}"
        ((FAILED_COUNT++))
    fi

    cd "$SCRIPT_DIR"
    rm -rf "$temp_dir"
}

# Update Git-based libraries
echo -e "\n${GREEN}🔗 Updating Git-based libraries...${NC}"
for lib_dir in "$LIBRARIES_DIR"/*; do
    if [ -d "$lib_dir" ]; then
        lib_name=$(basename "$lib_dir")

        if should_skip_library "$lib_name"; then
            echo -e "${YELLOW}⏭️  Skipping $lib_name (managed by Drupal)${NC}"
            ((SKIPPED_COUNT++))
        elif [ -d "$lib_dir/.git" ]; then
            update_git_repo "$lib_name" "$lib_name"
        fi
    fi
done

# Update npm-managed libraries
echo -e "\n${GREEN}📦 Updating npm-managed libraries...${NC}"
for dir in "${!NPM_LIBRARIES[@]}"; do
    if [ -d "$LIBRARIES_DIR/$dir" ] && [ ! -d "$LIBRARIES_DIR/$dir/.git" ]; then
        update_npm_library "$dir" "$dir" "${NPM_LIBRARIES[$dir]}"
    fi
done

echo -e "\n${GREEN}🎉 Library update process completed!${NC}"

# Show summary
echo -e "\n${BLUE}📊 Update Summary:${NC}"
echo -e "  ${GREEN}✅ Updated: $UPDATED_COUNT${NC}"
echo -e "  ${RED}❌ Failed: $FAILED_COUNT${NC}"
echo -e "  ${YELLOW}⏭️  Skipped: $SKIPPED_COUNT${NC}"

if [ $FAILED_COUNT -gt 0 ]; then
    echo -e "\n${YELLOW}⚠️  Some libraries could not be updated. This is normal if libraries don't exist yet.${NC}"
    echo -e "   ${CYAN}💡 Run 'composer run libraries:install' to install missing libraries${NC}"
fi

# Always exit successfully for composer
exit 0
