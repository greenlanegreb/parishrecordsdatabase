#!/bin/bash

# Target string to look for and move/insert
TARGET_STRING="'feedback_schema.th_email'"
INSERT_LINE=144

# Loop through all php files in the current directory
for file in *.php; do
    if [ -f "$file" ]; then
        echo "Processing: $file"

        # 1. Remove all existing instances of this line to prevent duplicates
        grep -v "$TARGET_STRING" "$file" > "$file.tmp"

        # 2. Insert the string right at line 421 in the temp file
        sed -i "${INSERT_LINE}i \    ${TARGET_STRING} => 'Email'," "$file.tmp"

        # 3. Replace the original file with the modified temp file
        mv "$file.tmp" "$file"
    fi
done

echo "Done! All language files have been updated with the email key."
