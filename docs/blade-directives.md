# Blade Directives

Three directives are registered by the package. All of them are rendered via dedicated Blade views,
so you can publish and override them.

---

## `@pwaMeta`

Place inside `<head>`. Renders:

- `<link rel="manifest" href="...">` (with `crossorigin="use-credentials"` in dynamic mode)
- `<meta name="theme-color" ...>`
- `<meta name="background-color" ...>`
- iOS Safari meta tags (`apple-mobile-web-app-capable`, `apple-touch-icon`, ...)
- Favicon `<link>` tags for configured sizes
- `<meta name="mobile-web-app-capable" ...>`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @pwaMeta
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```

The manifest URL and link attributes come from the active `ManifestDriver`, so they are correct in
both static and dynamic mode.

---

## `@pwaRegisterSW`

Place before `</body>`. Renders an inline `<script>` that registers the service worker on page
load.

```blade
    @pwaRegisterSW
</body>
```

The script is **not** rendered in these cases:

- `pwa.service_worker.auto_register` is `false`.
- The application environment is `local` and `pwa.service_worker.dev_enabled` is `false` (the default).

The `autoUpdate` register type (the default) posts `SKIP_WAITING` to a newly installed worker
automatically. Set `register_type` to `prompt` and handle the `pwa:registered` window event
yourself for a custom update UI.

### Window events emitted by the script

| Event | `detail` | Fired when |
|---|---|---|
| `pwa:registered` | `{ registration: ServiceWorkerRegistration }` | SW successfully registered |
| `pwa:error` | `{ error: Error }` | SW registration failed |

---

## `@pwaInstallButton('text')`

Renders a hidden `<button>` that becomes visible when the browser fires the
`beforeinstallprompt` event.

```blade
@pwaInstallButton('Add to Home Screen')
```

The button has `id="pwa-install-button"` and `data-pwa-install` for easy targeting via CSS or JS.

### Window events emitted by the button script

| Event | `detail` | Fired when |
|---|---|---|
| `pwa:install-prompt` | `{ outcome: 'accepted' \| 'dismissed' }` | User responded to the install prompt |
| `pwa:installed` | — | `appinstalled` fired (app was added to home screen) |

### Styling example

```css
#pwa-install-button {
    display: none; /* hidden by default; shown via JS when available */
    background: #1a1a2e;
    color: #fff;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
}
```

---

## Publishing views

```bash
ddev artisan vendor:publish --tag=pwa-views
```

Published to `resources/views/vendor/pwa/directives/`:

| File | Used by |
|---|---|
| `meta.blade.php` | `@pwaMeta` |
| `sw-register.blade.php` | `@pwaRegisterSW` |
| `install-button.blade.php` | `@pwaInstallButton` |
