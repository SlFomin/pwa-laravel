# Events

The package fires Laravel events at key points in the PWA lifecycle. Use them for logging,
metrics, cache invalidation, CDN upload — or to mutate the manifest just before it is sent to
the browser.

All event classes live in `SlFomin\PwaLaravel\Events`. Listeners can be registered with
the standard `Event::listen()` API or via the `PwaEvents` fluent helper.

---

## Event reference

| Event | When it fires | Payload |
|---|---|---|
| `ManifestResolving` | Just before the active driver builds the manifest | `Request $request` |
| `ManifestResolved` | After the manifest is built, **before** serialization | `Request $request`, `ManifestBuilder $manifest` |
| `ServiceWorkerRequested` | When the SW file is served via `ServiceWorkerController` | `Request $request`, `string $path`, `string $url` |
| `IconsGenerated` | After `pwa:generate-icons` writes the icon set | `string $sourcePath`, `string $outputPath`, `array $icons` |
| `ManifestPublished` | After `pwa:publish-manifest` writes the file | `string $path`, `ManifestBuilder $manifest`, `int $bytes` |

> `ManifestResolving` and `ManifestResolved` are dispatched by `PwaManager::manifest()`. The
> built-in `ManifestController` and the `Pwa` facade both go through `PwaManager`, so listeners
> fire for both HTTP requests and programmatic calls.

---

## Registering listeners

### `PwaEvents` fluent helper

```php
use SlFomin\PwaLaravel\Events\PwaEvents;
use SlFomin\PwaLaravel\Events\ManifestResolved;
use SlFomin\PwaLaravel\Events\ServiceWorkerRequested;

// In a service provider's boot() method:
PwaEvents::manifestResolved(function (ManifestResolved $event): void {
    if ($event->request->user()?->isPremium()) {
        $event->manifest->name('My App — Pro')->themeColor('#7c3aed');
    }
});

PwaEvents::serviceWorkerRequested(function (ServiceWorkerRequested $event): void {
    logger()->info('SW served', ['url' => $event->url, 'ua' => $event->request->userAgent()]);
});
```

### Standard `Event::listen()`

The helper is sugar — you can register listeners the regular way too:

```php
use Illuminate\Support\Facades\Event;
use SlFomin\PwaLaravel\Events\ManifestResolved;

Event::listen(ManifestResolved::class, MyManifestListener::class);
```

---

## Last-chance manifest modification

`ManifestResolved` is the recommended hook for cross-cutting manifest tweaks — preferred over
implementing a custom `ManifestResolver` when the change is request-specific and doesn't fit
neatly into a single resolver class.

`$event->manifest` is the live `ManifestBuilder` instance — mutations apply to the JSON returned
to the browser.

```php
PwaEvents::manifestResolved(function (ManifestResolved $event): void {
    if (app()->environment('local')) {
        $event->manifest->name($event->manifest->get('name').' (dev)');
    }
});
```

> The manifest is validated on serialization (`toJson()`). If your listener leaves a required
> field empty or sets an invalid `display` value, an `InvalidManifestException` is thrown.

---

## Post-build automation

Hook into the artisan commands to chain follow-up work:

```php
use SlFomin\PwaLaravel\Events\IconsGenerated;
use SlFomin\PwaLaravel\Events\ManifestPublished;

PwaEvents::iconsGenerated(function (IconsGenerated $event): void {
    // e.g. upload to S3 / invalidate CDN
    foreach ($event->icons as $icon) {
        // $icon['src'], $icon['sizes'], $icon['purpose'] ...
    }
});

PwaEvents::manifestPublished(function (ManifestPublished $event): void {
    logger()->info('Manifest written', [
        'path' => $event->path,
        'bytes' => $event->bytes,
    ]);
});
```

---

## Caching note

Listeners that mutate `ManifestResolved` run **after** the dynamic driver's cache lookup. If you
use the dynamic driver with caching enabled, the cached `ManifestBuilder` is what the listener
sees — meaning per-request mutations still apply, but anything that depends on the resolver's
upstream computation will hit the cache. To bypass caching entirely, set
`pwa.manifest.dynamic.cache` to `false` or return `null` from `ManifestResolver::cacheKey()`.

---

## JavaScript-side events

Browser-side `CustomEvent`s dispatched by the `@pwaRegisterSW` and `@pwaInstallButton`
directives are documented in [blade-directives.md](blade-directives.md). They are independent of
the PHP events listed above.
