#!/bin/bash
# WebP Image Converter Script
# Usage: ./convert-webp.sh [directory]
# Converts JPG/PNG images to WebP format for better performance

DIR="${1:-public/uploads}"
QUALITY=85
COUNT=0

echo "🔄 WebP Donusturme Basladi"
echo "📁 Hedef Klasor: $DIR"
echo "📊 Kalite: $QUALITY%"
echo ""

# Find all JPG/JPEG/PNG files
for img in $(find "$DIR" -type f \( -iname "*.jpg" -o -iname "*.jpeg" -o -iname "*.png" \) 2>/dev/null); do
    # Get filename without extension
    filename="${img%.*}"
    
    # Skip if WebP already exists and is newer
    if [ -f "${filename}.webp" ] && [ "$(find "$img" -newer "${filename}.webp")" = "" ]; then
        continue
    fi
    
    # Convert to WebP
    if command -v cwebp &> /dev/null; then
        cwebp -q "$QUALITY" "$img" -o "${filename}.webp" 2>/dev/null
        if [ $? -eq 0 ]; then
            echo "✓ ${img} → ${filename}.webp"
            ((COUNT++))
        fi
    elif command -v convert &> /dev/null; then
        # Use ImageMagick if cwebp not available
        convert "$img" -quality "$QUALITY%" "${filename}.webp" 2>/dev/null
        if [ $? -eq 0 ]; then
            echo "✓ ${img} → ${filename}.webp"
            ((COUNT++))
        fi
    else
        echo "⚠ cwebp veya ImageMagick gerekli. Kurulum: apt install webp"
        break
    fi
done

echo ""
echo "✅ Tamamlandi! $COUNT gorsel donusturuldu."
