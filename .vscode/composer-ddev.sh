#!/bin/bash
# Wrapper to run Composer via DDEV for the Composer Companion extension.
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DDEV_ROOT="$(dirname "$SCRIPT_DIR")"

cd "$DDEV_ROOT"
exec ddev composer "$@"
