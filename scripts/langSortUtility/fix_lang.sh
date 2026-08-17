#!/bin/bash

# Anchor to the project's root lang/ directory relative to this script's location
LANG_DIR="$(dirname "$0")/../../lang"

if [ ! -d "$LANG_DIR" ]; then
    echo "❌ Error: lang directory not found at $LANG_DIR"
    exit 1
fi

cd "$LANG_DIR" || exit

# Target string to look for and move/insert
TARGET_STRING="'feedback_schema.th_email'"
INSERT_LINE=144

# Loop through all php files in the lang directory
for file in *.php; do
    if [ -f "$file" ]; then
        echo "Processing: $file"

        # 1. Remove all existing instances of this line to prevent duplicates
        grep -v "$TARGET_STRING" "$file" > "$file.tmp"

        # 2. Insert the string right at the specified line in the temp file
        sed -i "${INSERT_LINE}i \    ${TARGET_STRING} => 'Email'," "$file.tmp"

        # 3. Replace the original file with the modified temp file
        mv "$file.tmp" "$file"
    fi
done

echo "Done! All language files have been updated with the email key."
