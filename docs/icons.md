# Icon Generation

The `pwa:generate-icons` command uses [Intervention Image 3.x](https://image.intervention.io/v3)
to generate a complete icon set from a single source image.

---

## Requirements

- Source image: PNG, JPEG, or WebP
- Minimum size: **512 × 512 px**, square (width must equal height)

---

## Quick start

```bash
# Place your source icon at the configured path (default):
cp ~/my-logo.png resources/images/pwa-icon.png

# Generate all sizes
ddev artisan pwa:generate-icons
```

---

## Generated files

| File | Size | Purpose |
|---|---|---|
| `public/icons/icon-72x72.png` | 72×72 | Standard |
| `public/icons/icon-96x96.png` | 96×96 | Standard |
| `public/icons/icon-128x128.png` | 128×128 | Standard |
| `public/icons/icon-144x144.png` | 144×144 | Standard |
| `public/icons/icon-152x152.png` | 152×152 | Standard |
| `public/icons/icon-192x192.png` | 192×192 | Standard |
| `public/icons/icon-384x384.png` | 384×384 | Standard |
| `public/icons/icon-512x512.png` | 512×512 | Standard |
| `public/icons/icon-maskable-192x192.png` | 192×192 | Maskable |
| `public/icons/icon-maskable-512x512.png` | 512×512 | Maskable |
| `public/icons/apple-touch-icon.png` | 180×180 | iOS Safari |
| `public/icons/favicon-16x16.png` | 16×16 | Favicon |
| `public/icons/favicon-32x32.png` | 32×32 | Favicon |

---

## Maskable icons

[Maskable icons](https://web.dev/maskable-icon/) are safe-zone icons with a padded background.
Android uses them for adaptive icons (circle, squircle, etc.).

The padding defaults to 10% on each side (`maskable_padding: 0.1`). The background colour comes
from `pwa.icons.maskable_background` or falls back to `pwa.manifest.data.background_color`.

```php
// config/pwa.php
'icons' => [
    'generate_maskable'   => true,
    'maskable_sizes'      => [192, 512],
    'maskable_padding'    => 0.1,        // 10% safe zone
    'maskable_background' => '#1a1a2e',  // explicit colour; null = use background_color
],
```

---

## Command options

```
pwa:generate-icons [source] [--output=] [--dry-run]

  source     Absolute path to source image. Default: pwa.icons.source from config.
  --output   Absolute output directory. Default: pwa.icons.output_path from config.
  --dry-run  Validate source image without writing any files.
```

Examples:

```bash
# Validate a custom source without writing
ddev artisan pwa:generate-icons /tmp/my-icon.png --dry-run

# Write to a custom directory
ddev artisan pwa:generate-icons --output=/var/www/html/public/pwa-icons
```

---

## Custom icon generator

Replace `IconProcessor` by binding your own implementation:

```php
use SlFomin\PwaLaravel\Contracts\IconGenerator;

$this->app->bind(IconGenerator::class, MyCustomIconGenerator::class);
```

Your class must implement:

```php
interface IconGenerator
{
    public function generate(string $sourcePath, string $outputPath): array;
    public function validateSource(string $sourcePath): void;
}
```

`generate()` must return a list of icon descriptors:

```php
[
    ['src' => '/icons/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
    // ...
]
```

---

## Dynamic manifest icons

If you use the **dynamic** manifest driver, the generated icon list is **not** automatically merged
into the manifest. Pass the icons explicitly in your resolver:

```php
public function resolve(Request $request, ManifestBuilder $default): ManifestBuilder
{
    return $default->icons([
        ['src' => '/icons/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => '/icons/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => '/icons/icon-maskable-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ]);
}
```

For the **static** manifest driver, `vite-plugin-pwa` picks up icons from its `manifest.icons`
option in `vite.config.js` and embeds the hashed paths automatically.
