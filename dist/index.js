import { VitePWA } from 'vite-plugin-pwa';

// resources/js/plugin.ts
var LARAVEL_DEFAULTS = {
  registerType: "autoUpdate",
  manifestFilename: "manifest.webmanifest",
  // Регистрацию SW берёт на себя blade-директива @pwaRegisterSW
  injectRegister: null,
  workbox: {
    cleanupOutdatedCaches: true,
    clientsClaim: true,
    skipWaiting: false
  },
  devOptions: {
    enabled: false
  }
};
var DEFAULT_INERTIA_EXCLUDES = [
  /^\/api\//,
  /^\/sanctum\//,
  /^\/broadcasting\//,
  /^\/livewire\//,
  /^\/horizon\//,
  /^\/telescope\//,
  /^\/pulse\//
];
function laravelPwa(options = {}) {
  const {
    inertia = false,
    excludeFromSW = [],
    navigateFallback,
    ...vitePwaOptions
  } = options;
  const denylist = [
    ...inertia ? DEFAULT_INERTIA_EXCLUDES : [],
    ...excludeFromSW.map((p) => p instanceof RegExp ? p : new RegExp(p)),
    ...vitePwaOptions.workbox?.navigateFallbackDenylist ?? []
  ];
  const resolvedFallback = navigateFallback !== void 0 ? navigateFallback : inertia ? "/" : vitePwaOptions.workbox?.navigateFallback ?? null;
  const merged = {
    ...LARAVEL_DEFAULTS,
    ...vitePwaOptions,
    workbox: {
      ...LARAVEL_DEFAULTS.workbox,
      ...vitePwaOptions.workbox,
      ...resolvedFallback !== null ? { navigateFallback: resolvedFallback } : {},
      ...denylist.length > 0 ? { navigateFallbackDenylist: denylist } : {}
    }
  };
  return VitePWA(merged);
}

// resources/js/register.ts
async function setupPwa(options = {}) {
  const { registerSW } = await import('virtual:pwa-register');
  const swOptions = {
    immediate: options.immediate ?? true,
    onRegisteredSW(_url, registration) {
      options.onRegistered?.(registration);
    }
  };
  if (options.onNeedRefresh) {
    swOptions.onNeedRefresh = options.onNeedRefresh;
  }
  if (options.onOfflineReady) {
    swOptions.onOfflineReady = options.onOfflineReady;
  }
  if (options.onRegisterError) {
    swOptions.onRegisterError = options.onRegisterError;
  }
  const updateSW = registerSW(swOptions);
  return { updateSW };
}

export { laravelPwa, setupPwa as registerSW, setupPwa };
//# sourceMappingURL=index.js.map
//# sourceMappingURL=index.js.map