# Extending the Package

All public extension points are defined as interfaces in `SlFomin\PwaLaravel\Contracts`.

---

## Contracts overview

| Interface | Default implementation | Purpose |
|---|---|---|
| `ManifestDriver` | `StaticManifestDriver` | Controls how the manifest is resolved and served |
| `ManifestResolver` | `DefaultManifestResolver` | Builds context-aware manifests (tenant, locale…) |
| `IconGenerator` | `IconProcessor` | Generates icon files from a source image |
| `ServiceWorkerStrategy` | `GenerateSWStrategy` | Provides Vite plugin options for the SW strategy |

---

## Custom ManifestDriver

A custom driver controls the entire manifest pipeline — resolution, URL generation, and link
attributes. Bind it in `AppServiceProvider::register()`:

```php
use SlFomin\PwaLaravel\Contracts\ManifestDriver;

$this->app->bind(ManifestDriver::class, MyManifestDriver::class);
```

```php
use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Contracts\ManifestDriver;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

final class MyManifestDriver implements ManifestDriver
{
    public function resolve(Request $request): ManifestBuilder
    {
        return ManifestBuilder::make([
            'name'       => 'My App',
            'short_name' => 'App',
            'start_url'  => '/',
            'display'    => 'standalone',
        ]);
    }

    public function url(Request $request): string
    {
        return '/manifest.webmanifest';
    }

    public function linkAttributes(Request $request): array
    {
        return ['rel' => 'manifest', 'href' => $this->url($request)];
    }
}
```

---

## Custom ManifestResolver

A resolver is simpler than a driver: it receives the default `ManifestBuilder` and returns a
modified one. Use this when you only need to override fields, not the whole pipeline.

```php
use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Contracts\ManifestResolver;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

final class RoleManifestResolver implements ManifestResolver
{
    public function resolve(Request $request, ManifestBuilder $default): ManifestBuilder
    {
        if ($request->user()?->hasRole('admin')) {
            return $default
                ->name('App Admin')
                ->themeColor('#b91c1c')
                ->startUrl('/admin');
        }

        return $default;
    }

    public function cacheKey(Request $request): ?string
    {
        $role = $request->user()?->role ?? 'guest';
        return "role.{$role}";
    }
}
```

Bind it and switch the driver to `dynamic`:

```php
// AppServiceProvider
$this->app->bind(ManifestResolver::class, RoleManifestResolver::class);
```

```env
PWA_MANIFEST_DRIVER=dynamic
```

---

## Custom IconGenerator

Replace `intervention/image` with another library, or add WebP output:

```php
use SlFomin\PwaLaravel\Contracts\IconGenerator;
use SlFomin\PwaLaravel\Exceptions\IconGenerationException;

final class ImagickIconGenerator implements IconGenerator
{
    public function generate(string $sourcePath, string $outputPath): array
    {
        $this->validateSource($sourcePath);
        // ... your implementation
        return [
            ['src' => '/icons/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ];
    }

    public function validateSource(string $sourcePath): void
    {
        if (! file_exists($sourcePath)) {
            throw new IconGenerationException("Source not found: {$sourcePath}");
        }
        // ...
    }
}
```

```php
$this->app->bind(IconGenerator::class, ImagickIconGenerator::class);
```

---

## Facade

`Pwa` is a facade over `PwaManager`, exposing the most commonly used methods:

```php
use SlFomin\PwaLaravel\Facades\Pwa;

Pwa::manifest();            // ManifestBuilder for the current request
Pwa::manifestUrl();         // string
Pwa::serviceWorkerUrl();    // string
Pwa::worker();              // WorkerManager
Pwa::driver();              // ManifestDriver
```

---

## Events

No custom events are fired by the package currently. The service worker registration script
dispatches browser `CustomEvent`s (`pwa:registered`, `pwa:error`, `pwa:install-prompt`,
`pwa:installed`) that you can listen to in your JavaScript.

---

## Architecture notes

- `ManifestManager` and `WorkerManager` are singletons bound in the container.
- `ManifestDriver` and `ManifestResolver` are non-singleton (resolved fresh on each call via
  `app->bind`). This ensures the resolver gets a fresh `Request` in long-running processes like
  Octane.
- All PHP files use `declare(strict_types=1)`. PHPStan level 8 is enforced in CI.
- Inertia code is loaded only when `InertiaDetector::installed()` returns `true`. The namespace
  `SlFomin\PwaLaravel\Inertia` has no hard dependency on `inertiajs/inertia-laravel`.
