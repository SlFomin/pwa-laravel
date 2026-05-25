
# [0.1.0](https://github.com/slfomin/pwa-laravel/compare/v0.0.1...v0.1.0) (2026-05-23)


### Bug Fixes

* **cmd:** Добавить --force флаг в pwa:publish-manifest ([4d4435a](https://github.com/slfomin/pwa-laravel/commit/4d4435aac91d7ff04d50d22b50cc5a56d9425c45))
* **config:** Добавить env-переменные для путей, чувствительных к config:cache ([7c2ffd0](https://github.com/slfomin/pwa-laravel/commit/7c2ffd0a9c0d43d9f843ee0b3423ec873135c1a8))
* **icons:** Улучшить обработку ошибок в IconProcessor ([873a617](https://github.com/slfomin/pwa-laravel/commit/873a617dcff7fb16abc34cda4faca15a4bc44a95))
* **inertia:** Использовать app('request') вместо request() в InertiaAdapter ([ca084ce](https://github.com/slfomin/pwa-laravel/commit/ca084ce0a3be9a55654e1a7f5a3d8ebcc0868dcb))
* **js:** Убрать unsafe cast в plugin.ts ([7b7faf1](https://github.com/slfomin/pwa-laravel/commit/7b7faf1063809ad925bcc713b8f284b9be71fdd5))
* **manifest:** Логировать warning при невалидном JSON в StaticManifestDriver ([0fc3174](https://github.com/slfomin/pwa-laravel/commit/0fc31746834283236f934b6d9c2fb07294d95e79))
* **manifest:** Обновлена валидация цвета в ManifestBuilder ([018d354](https://github.com/slfomin/pwa-laravel/commit/018d35448f0b9c2a1b8b158ab09f9ade59fad82a)), closes [#fffff](https://github.com/slfomin/pwa-laravel/issues/fffff)
* **routes:** Убрать web middleware с manifest-роута ([3b0ed4f](https://github.com/slfomin/pwa-laravel/commit/3b0ed4f560ff93aae266d7ba6aedb03e28414d4d))
* **sw:** Экранировать url и scope через json_encode ([33da972](https://github.com/slfomin/pwa-laravel/commit/33da9729b3b144fb41a9dab4bd47eb12aa6a3086))
* **tsconfig:** Исключить __tests__ из основной конфигурации TypeScript ([5713c98](https://github.com/slfomin/pwa-laravel/commit/5713c981fe0d595deba55aacd0c2a07609cd993a))

## [0.0.1](https://github.com/slfomin/pwa-laravel/compare/87c3b11f58339cff95b51ab17ef20812b5b7030d...v0.0.1) (2026-05-23)


### Bug Fixes

* **ci:** Исправлена версия actions/checkout с v6 на v4 ([9d72058](https://github.com/slfomin/pwa-laravel/commit/9d72058c9919b4aee24760507709758d8104b79b))
* **events:** Исправлены ошибки PHPStan в PwaEvents ([d875ca5](https://github.com/slfomin/pwa-laravel/commit/d875ca54db8b14d13d37d6cebd8739d7a3a89ea3))
* **style:** Исправлен порядок импортов ([87c3b11](https://github.com/slfomin/pwa-laravel/commit/87c3b11f58339cff95b51ab17ef20812b5b7030d))


### Features

* **console:** Добавить PublishManifestCommand — генерация статичного манифеста из конфига ([6d32bf6](https://github.com/slfomin/pwa-laravel/commit/6d32bf66972e79142fbcb7ebda7dceefefb60d20))
* **dist:** Поставлять pre-built dist/ вместе с composer-пакетом ([2841eb9](https://github.com/slfomin/pwa-laravel/commit/2841eb972a275fe54e1126c2b725a8b7137f4bda))
* **events:** Добавить события жизненного цикла PWA ([957c897](https://github.com/slfomin/pwa-laravel/commit/957c897ace5346f4da84976e8016b3cb00f1dd1f))
* **icons:** Добавлена команда pwa:generate-icons ([98343b6](https://github.com/slfomin/pwa-laravel/commit/98343b6db4be38f74a6b947d694c545c0d5ca23f))
* **icons:** Реализован IconProcessor (intervention/image 3.x) ([2fd1fad](https://github.com/slfomin/pwa-laravel/commit/2fd1fadd5d5720c5021e84386c34b4d37ff7c3ca))
* **inertia:** Добавить InertiaAdapter — share PWA-пропсов через Inertia::share() ([55baa38](https://github.com/slfomin/pwa-laravel/commit/55baa38dc22ec83266c04cb18373a827efd15f6a))
* **inertia:** Добавить InertiaDetector — определение наличия пакета и типа запроса ([b6b41e3](https://github.com/slfomin/pwa-laravel/commit/b6b41e378a11c9c7cae419662e63635581fc9460))
* **inertia:** Добавить InertiaPwaMiddleware — Vary и no-store для Inertia partial responses ([dcf3272](https://github.com/slfomin/pwa-laravel/commit/dcf3272d3057cb8d2323a107efb67e3ccc54bff7))
* **js:** Добавить Inertia v3 хуки usePwa для Vue 3, React 19, Svelte 5 ([33bb027](https://github.com/slfomin/pwa-laravel/commit/33bb027e1da9a1eee2e329879a91c7ae55a5cf42))
* **js:** Добавить plugin.ts — обёртка над VitePWA с Laravel-дефолтами ([d477b06](https://github.com/slfomin/pwa-laravel/commit/d477b069084a48202c4220f4a70aea906a6338c0))
* **js:** Добавить register.ts — программная регистрация SW через virtual:pwa-register ([21dc293](https://github.com/slfomin/pwa-laravel/commit/21dc29310bb2ce2e5ffdb8f11d2787579ee832a7))
* **js:** Добавить types.ts и index.ts — публичный API и интерфейсы ([3c176e8](https://github.com/slfomin/pwa-laravel/commit/3c176e841f874ae0067bef5df9a2c200f9627323))
* **js:** Добавить конфигурацию npm-пакета @slfomin/pwa-laravel ([9a025c0](https://github.com/slfomin/pwa-laravel/commit/9a025c0ce7bd2e94f8e0a11dba06289cc1562ce2))
* **provider:** bind ServiceWorkerStrategy by configured strategy key ([53db736](https://github.com/slfomin/pwa-laravel/commit/53db736c30e6d3fbca5a7272f39130e30c8fe1b0))
* **provider:** Зарегистрированы IconGenerator и GenerateIconsCommand ([3f5c760](https://github.com/slfomin/pwa-laravel/commit/3f5c760fbb4e3acff23b2185a323908fe133dc59))
* **provider:** Подключить Inertia-адаптер и middleware alias в ServiceProvider ([05f1aac](https://github.com/slfomin/pwa-laravel/commit/05f1aac7e4c529f76395f8878b2c1fc995152421))
* **stubs:** Добавить vite.config.stub и sw-custom.js.stub ([a95e1fe](https://github.com/slfomin/pwa-laravel/commit/a95e1fecaa1e6b517becb1483e3c6b2bb9c9a49a))
* **sw:** Добавлен ServiceWorkerController ([eabba18](https://github.com/slfomin/pwa-laravel/commit/eabba187b31babf74d4ebc2235c33d45a50f328c))
* **sw:** Добавлены GenerateSWStrategy и InjectManifestStrategy ([01e256c](https://github.com/slfomin/pwa-laravel/commit/01e256cd19453b7f5b01f74cfe4c4396380c2c6c))
* **sw:** Зарегистрирован роут pwa.sw для ServiceWorkerController ([4bac73e](https://github.com/slfomin/pwa-laravel/commit/4bac73e057b60e6de538c75f34fd2cd6598154d5))
* Реализован скелет пакета и статичный режим манифеста ([9c27c86](https://github.com/slfomin/pwa-laravel/commit/9c27c86e0ce9fcc8604cbc94e9af4dc2666baeed))
