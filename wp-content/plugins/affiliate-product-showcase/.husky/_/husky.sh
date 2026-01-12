#!/bin/sh
# husky.sh

# Hook directory
hook_dir="$(dirname "$0")"

# Add hook directory to PATH
export PATH="$hook_dir:$PATH"

# Execute the hook script if it exists
hook_name=$(basename "$0")
if [ -f "$hook_dir/$hook_name" ]; then
    . "$hook_dir/$hook_name"
fi
