#!/bin/bash

# =============================================================================
# Install missing libraries (both git and npm-based)
# =============================================================================

# Load common configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/libraries-common.sh"

cd "$SCRIPT_DIR"

echo -e "${GREEN}🔽 Installing missing libraries in $LIBRARIES_DIR${NC}"

ensure_libraries_dir

# Counters
INSTALLED_COUNT=0
FAILED_COUNT=0
EXISTING_COUNT=0

# Function to clone git repository
install_git_repo() {
    local dir=$1
    local name=$2
    local repo_url=$3

    if [ ! -d "$LIBRARIES_DIR/$dir" ]; then
        echo -e "${PURPLE}🔽 Installing $name from Git${NC}"

        if git clone "$repo_url" "$LIBRARIES_DIR/$dir" 2>/dev/null; then
            echo -e "${GREEN}✅ $name installed successfully${NC}"
            ((INSTALLED_COUNT++))
        else
            echo -e "${YELLOW}⚠️  Could not install $name (repository may not exist or network issue)${NC}"
            ((FAILED_COUNT++))
        fi
    else
        echo -e "${CYAN}ℹ️  $name already exists${NC}"
        ((EXISTING_COUNT++))
    fi
}

# Function to install npm-managed library
install_npm_library() {
    local dir=$1
    local name=$2
    local package=$3

    if [ ! -d "$LIBRARIES_DIR/$dir" ]; then
        echo -e "${PURPLE}🔽 Installing $name via npm${NC}"

        # Create library directory
        mkdir -p "$LIBRARIES_DIR/$dir"

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

            echo -e "${GREEN}✅ $name installed successfully${NC}"
            ((INSTALLED_COUNT++))
        else
            echo -e "${YELLOW}⚠️  Could not install $name via npm (package may not exist)${NC}"
            rm -rf "$SCRIPT_DIR/$LIBRARIES_DIR/$dir"
            ((FAILED_COUNT++))
        fi

        cd "$SCRIPT_DIR"
        rm -rf "$temp_dir"
    else
        echo -e "${CYAN}ℹ️  $name already exists${NC}"
        ((EXISTING_COUNT++))
    fi
}

# Function to download from CDN
install_cdn_library() {
    local dir=$1
    local name=$2
    local url=$3
    local filename=$4

    if [ ! -d "$LIBRARIES_DIR/$dir" ]; then
        echo -e "${PURPLE}🔽 Installing $name from CDN${NC}"

        mkdir -p "$LIBRARIES_DIR/$dir"
        cd "$LIBRARIES_DIR/$dir"

        if curl -sL "$url" -o "$filename"; then
            echo -e "${GREEN}✅ $name installed successfully${NC}"
            ((INSTALLED_COUNT++))
        else
            echo -e "${YELLOW}⚠️  Could not download $name (network issue or URL changed)${NC}"
            cd "$SCRIPT_DIR"
            rm -rf "$LIBRARIES_DIR/$dir"
            ((FAILED_COUNT++))
        fi

        cd "$SCRIPT_DIR"
    else
        echo -e "${CYAN}ℹ️  $name already exists${NC}"
        ((EXISTING_COUNT++))
    fi
}

# Install missing Git-based libraries
echo -e "\n${GREEN}🔗 Installing missing Git-based libraries...${NC}"
for lib_name in "${!GIT_LIBRARIES[@]}"; do
    install_git_repo "$lib_name" "$lib_name" "${GIT_LIBRARIES[$lib_name]}"
done

# Install missing npm libraries
echo -e "\n${GREEN}📦 Installing missing npm-managed libraries...${NC}"
for dir in "${!NPM_LIBRARIES[@]}"; do
    install_npm_library "$dir" "$dir" "${NPM_LIBRARIES[$dir]}"
done

# Install CDN-based libraries
echo -e "\n${GREEN}🌐 Installing CDN-based libraries...${NC}"
install_cdn_library "bootstrap" "Bootstrap JS" "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" "bootstrap.bundle.min.js"
install_cdn_library "bootstrap" "Bootstrap CSS" "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" "bootstrap.min.css"
install_cdn_library "jquery" "jQuery" "https://code.jquery.com/jquery-3.7.1.min.js" "jquery.min.js"
install_cdn_library "font-awesome" "Font Awesome" "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" "all.min.css"

echo -e "\n${GREEN}🎉 Library installation process completed!${NC}"

# Show summary
echo -e "\n${BLUE}📊 Installation Summary:${NC}"
echo -e "  ${GREEN}🔽 Installed: $INSTALLED_COUNT${NC}"
echo -e "  ${CYAN}ℹ️  Already existed: $EXISTING_COUNT${NC}"
echo -e "  ${RED}❌ Failed: $FAILED_COUNT${NC}"

if [ $FAILED_COUNT -gt 0 ]; then
    echo -e "\n${YELLOW}⚠️  Some libraries could not be installed. This may be normal for unavailable packages.${NC}"
    echo -e "   ${CYAN}💡 Check the output above for details on specific failures${NC}"
fi

# Always exit successfully for composer
exit 0
