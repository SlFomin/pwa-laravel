# Artisan Commands

---

## `pwa:install`

Interactive installer. Run once after requiring the package.

```bash
ddev artisan pwa:install
```

Steps performed:

1. Publishes `config/pwa.php`.
2. Publishes assets (icon stubs).
3. Prints next-step instructions.
4. Optionally asks you to star the GitHub repository.

---

## `pwa:generate-icons`

Generates a full icon set from a single source image.

```
pwa:generate-icons [source] [--output=] [--dry-run]
```

| Argument / Option | Description |
|---|---|
| `source` | Path to source PNG/JPEG/WebP (≥512×512, square). Defaults to `pwa.icons.source` |
| `--output` | Output directory. Defaults to `pwa.icons.output_path` |
| `--dry-run` | Validate source without writing any files |

**Output** (default sizes, all in `public/icons/`):

- `icon-{size}x{size}.png` for each configured size (72, 96, 128, 144, 152, 192, 384, 512)
- `icon-maskable-{size}x{size}.png` for maskable sizes (192, 512)
- `apple-touch-icon.png` at 180×180
- `favicon-16x16.png`, `favicon-32x32.png`

Examples:

```bash
# Generate with default config
ddev artisan pwa:generate-icons

# Validate a different source file
ddev artisan pwa:generate-icons resources/images/logo.png --dry-run

# Write to a custom directory
ddev artisan pwa:generate-icons --output=public/pwa-icons
```

---

## `pwa:publish-manifest`

Generates `manifest.webmanifest` from `config/pwa.php` without requiring a Vite build step.
Useful for projects that serve only the PHP layer, or for generating a static manifest for
review / debugging.

```
pwa:publish-manifest [--path=] [--pretty]
```

| Option | Description |
|---|---|
| `--path` | Output file path. Defaults to `pwa.manifest.static_path` |
| `--pretty` | Pretty-print the JSON output |

Examples:

```bash
# Generate to the default location
ddev artisan pwa:publish-manifest

# Human-readable JSON for debugging
ddev artisan pwa:publish-manifest --pretty

# Write to a custom location
ddev artisan pwa:publish-manifest --path=public/my-manifest.webmanifest
```

> **Note:** The generated file contains no hashed asset filenames — those are inserted by
> `vite-plugin-pwa` during `npm run build`. This command is for projects that manage assets
> outside Vite or need a quick static manifest.

---

## CI / tooling commands (via `composer.json`)

```bash
ddev composer test          # Run Pest test suite
ddev composer test-coverage # Pest with coverage (requires Xdebug or PCOV)
ddev composer analyse       # PHPStan level 8
ddev composer format        # Laravel Pint (fix)
ddev composer format-test   # Laravel Pint (check only)
ddev composer rector        # Rector (automatic code upgrades)
ddev composer ci            # format-test + analyse + test (full CI suite)
```
