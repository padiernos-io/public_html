#!/bin/bash

# =============================================================================
# Check for missing libraries that might be needed by enabled Drupal modules
# =============================================================================

# Load common configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/libraries-common.sh"

cd "$SCRIPT_DIR"

echo -e "${GREEN}🔍 Checking for missing libraries in $LIBRARIES_DIR${NC}"

ensure_libraries_dir

# Define common Drupal module library dependencies
declare -A MODULE_LIBRARIES=(
    ["webform"]="choices,codemirror,signature_pad"
    ["paragraphs"]="slick,masonry"
    ["views_infinite_scroll"]="masonry,imagesloaded"
    ["colorbox"]="colorbox"
    ["lightbox2"]="lightbox2"
    ["fancybox"]="fancybox"
    ["photoswipe"]="photoswipe"
    ["slick"]="slick"
    ["owl"]="owl.carousel"
    ["flexslider"]="flexslider"
    ["bxslider"]="bxslider"
    ["responsive_tables_filter"]="datatables"
    ["select2"]="select2"
    ["chosen"]="chosen"
    ["google_analytics"]="gtag"
    ["google_tag"]="gtag"
    ["chartjs"]="chartjs"
    ["d3"]="d3"
    ["leaflet"]="leaflet"
    ["geofield_map"]="leaflet"
    ["fullcalendar_view"]="fullcalendar"
    ["calendar"]="fullcalendar"
    ["countdown"]="flipdown,count-up.js"
    ["animate_css"]="animate.css"
    ["aos"]="aos"
    ["wow"]="wow.js"
    ["gsap"]="gsap"
    ["lottie"]="lottie"
    ["three_js"]="three"
    ["pdf_api"]="pdf.js"
    ["clipboard"]="clipboard"
    ["qr_code"]="qrcode"
    ["inputmask"]="inputmask"
    ["cleave"]="cleave"
    ["flatpickr"]="flatpickr"
    ["pikaday"]="pikaday"
    ["sweetalert"]="sweetalert2"
    ["toastr"]="toastr"
    ["progress"]="pace,nprogress"
    ["htmx"]="htmx"
    ["alpine"]="alpine"
    ["dropzone"]="dropzone"
    ["cropper"]="cropperjs"
    ["sortable"]="sortable"
    ["velocity"]="velocity"
)

MISSING_LIBRARIES=()
FOUND_MODULES=()

# Check which modules are enabled and what libraries they might need
if command -v drush &> /dev/null; then
    echo -e "${CYAN}📋 Checking enabled modules for library requirements...${NC}"

    # Get list of enabled modules
    ENABLED_MODULES=$(drush pm:list --status=enabled --type=module --no-core --format=list 2>/dev/null | tr '\n' ' ' || echo "")

    for module_pattern in "${!MODULE_LIBRARIES[@]}"; do
        if echo "$ENABLED_MODULES" | grep -q "$module_pattern"; then
            FOUND_MODULES+=("$module_pattern")
            IFS=',' read -ra LIBS <<< "${MODULE_LIBRARIES[$module_pattern]}"
            for lib in "${LIBS[@]}"; do
                if [ ! -d "$LIBRARIES_DIR/$lib" ]; then
                    MISSING_LIBRARIES+=("$lib")
                fi
            done
        fi
    done
else
    echo -e "${YELLOW}⚠️  Drush not available. Cannot check enabled modules automatically.${NC}"
fi

# Also check for libraries that are in our configuration but not installed
echo -e "\n${CYAN}📋 Checking configured libraries...${NC}"

for lib_name in "${!NPM_LIBRARIES[@]}"; do
    if [ ! -d "$LIBRARIES_DIR/$lib_name" ]; then
        MISSING_LIBRARIES+=("$lib_name (npm)")
    fi
done

for lib_name in "${!GIT_LIBRARIES[@]}"; do
    if [ ! -d "$LIBRARIES_DIR/$lib_name" ]; then
        MISSING_LIBRARIES+=("$lib_name (git)")
    fi
done

# Report findings
if [ ${#FOUND_MODULES[@]} -gt 0 ]; then
    echo -e "\n${GREEN}📦 Enabled modules that may need libraries:${NC}"
    for module in "${FOUND_MODULES[@]}"; do
        echo -e "  • ${BLUE}$module${NC} → ${MODULE_LIBRARIES[$module]}"
    done
fi

# Report missing libraries
if [ ${#MISSING_LIBRARIES[@]} -gt 0 ]; then
    echo -e "\n${YELLOW}⚠️  Missing libraries:${NC}"
    for lib in $(printf '%s\n' "${MISSING_LIBRARIES[@]}" | sort -u); do
        echo -e "  ${RED}❌ $lib${NC}"
    done
    echo -e "\n${CYAN}💡 Run 'composer run libraries:install' to install missing libraries${NC}"

    # For composer compatibility, we can exit successfully but still indicate missing libraries
    # If you want this to fail CI/CD pipelines, change exit 0 to exit 1
    echo -e "\n${BLUE}ℹ️  Use exit code to determine if missing libraries should fail automation${NC}"
    exit 0
else
    echo -e "\n${GREEN}✅ No missing libraries detected${NC}"
fi

# Show summary
total_installed=$(ls -1 "$LIBRARIES_DIR" 2>/dev/null | wc -l)
echo -e "\n${BLUE}📊 Summary:${NC}"
echo -e "  Libraries installed: ${GREEN}${total_installed}${NC}"
echo -e "  Missing libraries: ${RED}$(printf '%s\n' "${MISSING_LIBRARIES[@]}" | sort -u | wc -l)${NC}"

echo -e "\n${YELLOW}💡 Tips:${NC}"
echo -e "  • Run 'composer run libraries:install' to install all missing libraries"
echo -e "  • Run 'composer run libraries:list' to see all available libraries"
echo -e "  • Check module documentation for specific library requirements"
