@if (filled(config('services.turnstile.site_key')))
    <div wire:ignore wire:ignore.self>
        <div class="cf-turnstile-wrapper">
            <div
                id="cf-turnstile-widget"
                data-sitekey="{{ config('services.turnstile.site_key') }}"
                data-theme="auto"
            ></div>
        </div>
        <p id="cf-turnstile-error" class="cf-turnstile-error">Pengesahan gagal — sila tunggu dan cuba semula.</p>
    </div>

    <style>
        .cf-turnstile-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 14px 0 4px;
            min-height: 65px;
            max-width: 100%;
            overflow: hidden;
        }
        .cf-turnstile-wrapper iframe {
            max-width: 100% !important;
        }
        .cf-turnstile-error {
            display: none;
            color: #dc2626;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            margin: 4px 0 0;
        }
        @media (max-width: 400px) {
            .cf-turnstile-wrapper {
                transform: scale(0.92);
                transform-origin: center;
            }
        }
        @media (max-width: 340px) {
            .cf-turnstile-wrapper {
                transform: scale(0.82);
            }
        }
    </style>

    <script>
        (function () {
            let widgetId = null;
            let initTries = 0;

            function getInput() {
                return document.querySelector('input[wire\\:model="data.cfTurnstileResponse"]')
                    || document.querySelector('input[wire\\:model\\.live="data.cfTurnstileResponse"]')
                    || document.querySelector('input[name="data[cfTurnstileResponse]"]');
            }

            function setInput(value) {
                const el = getInput();
                if (! el) return;
                el.value = value;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }

            window.cfTurnstileOnSuccess = function (token) {
                setInput(token);
                const err = document.getElementById('cf-turnstile-error');
                if (err) err.style.display = 'none';
                initTries = 0;
            };

            window.cfTurnstileOnExpired = function () {
                setInput('');
                const err = document.getElementById('cf-turnstile-error');
                if (err) err.style.display = 'block';
                if (window.turnstile) {
                    try {
                        if (widgetId !== null) window.turnstile.reset(widgetId);
                        else window.turnstile.reset();
                    } catch (e) {}
                }
            };

            window.cfTurnstileOnError = function () {
                setInput('');
                const err = document.getElementById('cf-turnstile-error');
                if (err) err.style.display = 'block';
                setTimeout(function () {
                    if (window.turnstile) {
                        try {
                            if (widgetId !== null) window.turnstile.reset(widgetId);
                            else window.turnstile.reset();
                        } catch (e) {
                            renderTurnstile();
                        }
                    }
                }, 1200);
            };

            function renderTurnstile() {
                const el = document.getElementById('cf-turnstile-widget');
                if (! el) return;
                if (! window.turnstile || typeof window.turnstile.render !== 'function') {
                    if (initTries++ < 30) setTimeout(renderTurnstile, 300);
                    return;
                }
                // Prevent double-render
                if (el.dataset.rendered === '1' && widgetId !== null) return;
                try {
                    if (widgetId !== null) {
                        try { window.turnstile.remove(widgetId); } catch (e) {}
                        widgetId = null;
                    }
                    el.innerHTML = '';
                    widgetId = window.turnstile.render(el, {
                        sitekey: el.dataset.sitekey,
                        theme: el.dataset.theme || 'auto',
                        callback: window.cfTurnstileOnSuccess,
                        'expired-callback': window.cfTurnstileOnExpired,
                        'error-callback': window.cfTurnstileOnError,
                        retry: 'auto',
                        'retry-interval': 800,
                        size: 'normal',
                    });
                    el.dataset.rendered = '1';
                } catch (e) {
                    el.dataset.rendered = '0';
                    if (initTries++ < 10) setTimeout(renderTurnstile, 800);
                }
            }

            function resetTurnstile() {
                setInput('');
                const err = document.getElementById('cf-turnstile-error');
                if (err) err.style.display = 'none';
                if (window.turnstile && widgetId !== null) {
                    try { window.turnstile.reset(widgetId); return; } catch (e) {}
                }
                if (window.turnstile) {
                    try { window.turnstile.reset(); return; } catch (e) {}
                }
                renderTurnstile();
            }

            // Initial render
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', renderTurnstile);
            } else {
                renderTurnstile();
            }

            // Fallback re-check after script loads
            setTimeout(renderTurnstile, 800);
            setTimeout(renderTurnstile, 2000);

            document.addEventListener('livewire:init', () => {
                if (window.Livewire) {
                    try { Livewire.on('cf-turnstile-reset', resetTurnstile); } catch (e) {}
                    if (Livewire.hook) {
                        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                            succeed(({ snapshot, effect }) => {
                                const hasErrors = effect && effect.errors && Object.keys(effect.errors).length > 0;
                                // Also check for Filament notification errors (turnstile failure sends notification)
                                if (hasErrors) setTimeout(resetTurnstile, 350);
                            });
                            fail(() => setTimeout(resetTurnstile, 350));
                        });
                    }
                }
            });

            window.addEventListener('cf-turnstile-reset', resetTurnstile);
            document.addEventListener('livewire:navigated', () => setTimeout(renderTurnstile, 250));
            window.addEventListener('pageshow', () => {
                const input = getInput();
                if (! input || ! input.value) setTimeout(resetTurnstile, 400);
            });
            // Expose for debugging
            window.cfTurnstileReset = resetTurnstile;
            window.cfTurnstileRender = renderTurnstile;
        })();
    </script>

    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
@endif
