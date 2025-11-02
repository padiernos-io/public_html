#!/bin/bash

# =============================================================================
# Common Library Management Functions for Drupal Web Libraries
# =============================================================================

# Base configuration
LIBRARIES_DIR="web/libraries"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Common npm libraries configuration
declare -A NPM_LIBRARIES=(
    ["choices"]="choices.js"
    ["codemirror"]="codemirror"
    ["count-up.js"]="countup.js"
    ["glightbox"]="glightbox"
    ["nouislider"]="nouislider"
    ["plyr"]="plyr"
    ["slick"]="slick-carousel"
    ["tiny-slider"]="tiny-slider"
    ["gsap"]="gsap"
    ["swiper"]="swiper"
    ["animate.css"]="animate.css"
    ["prism"]="prismjs"
    ["highlight.js"]="highlight.js"
    ["quill"]="quill"
    ["sortable"]="sortablejs"
    ["cropperjs"]="cropperjs"
    ["velocity"]="velocity-animate"
    ["owl.carousel"]="owl.carousel"
    ["fancybox"]="@fancyapps/ui"
    ["sweetalert2"]="sweetalert2"
    ["toastr"]="toastr"
    ["pace"]="pace-js"
    ["nprogress"]="nprogress"
    ["aos"]="aos"
    ["wow.js"]="wowjs"
    ["lottie"]="lottie-web"
    ["three"]="three"
    ["d3"]="d3"
    ["chartjs"]="chart.js"
    ["apexcharts"]="apexcharts"
    ["fullcalendar"]="@fullcalendar/core"
    ["flatpickr"]="flatpickr"
    ["pikaday"]="pikaday"
    ["inputmask"]="inputmask"
    ["cleave"]="cleave.js"
    ["clipboard"]="clipboard"
    ["qrcode"]="qrcode"
    ["pdf.js"]="pdfjs-dist"
    ["jspdf"]="jspdf"
    ["xlsx"]="xlsx"
    ["papaparse"]="papaparse"
    ["lodash"]="lodash"
    ["axios"]="axios"
    ["alpine"]="alpinejs"
    ["htmx"]="htmx.org"
)

# Git-based libraries configuration
declare -A GIT_LIBRARIES=(
    ["dropzone"]="https://github.com/dropzone/dropzone.git"
    ["masonry"]="https://github.com/desandro/masonry.git"
    ["imagesloaded"]="https://github.com/desandro/imagesloaded.git"
    ["isotope"]="https://github.com/metafizzy/isotope.git"
    ["moment"]="https://github.com/moment/moment.git"
    ["chart.js"]="https://github.com/chartjs/Chart.js.git"
    ["fullcalendar"]="https://github.com/fullcalendar/fullcalendar.git"
    ["select2"]="https://github.com/select2/select2.git"
    ["datatables"]="https://github.com/DataTables/DataTables.git"
    ["leaflet"]="https://github.com/Leaflet/Leaflet.git"
    ["aos"]="https://github.com/michalsnik/aos.git"
)

# Ensure libraries directory exists
ensure_libraries_dir() {
    if [ ! -d "$LIBRARIES_DIR" ]; then
        echo -e "${YELLOW}📁 Creating libraries directory: $LIBRARIES_DIR${NC}"
        mkdir -p "$LIBRARIES_DIR"
    fi
}

# Check if library should be skipped
should_skip_library() {
    local lib_name=$1
    case "$lib_name" in
        "ckeditor5"|"ckeditor5-anchor-drupal")
            return 0 # Skip
            ;;
        *)
            return 1 # Don't skip
            ;;
    esac
}
