<button
    type="button"
    id="pwa-install-button"
    style="display: none;"
    data-pwa-install
>
    {{ $text }}
</button>

<script>
    (function () {
        var btn = document.getElementById('pwa-install-button');
        var deferredPrompt = null;

        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;
            btn.style.display = 'inline-block';
        });

        btn.addEventListener('click', async function () {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            var { outcome } = await deferredPrompt.userChoice;
            window.dispatchEvent(new CustomEvent('pwa:install-prompt', { detail: { outcome: outcome } }));
            deferredPrompt = null;
            btn.style.display = 'none';
        });

        window.addEventListener('appinstalled', function () {
            window.dispatchEvent(new CustomEvent('pwa:installed'));
            btn.style.display = 'none';
        });
    })();
</script>
