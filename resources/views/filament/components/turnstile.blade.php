@if (filled(config('services.turnstile.site_key')))
    <div wire:ignore>
        <div
            class="cf-turnstile"
            data-sitekey="{{ config('services.turnstile.site_key') }}"
            data-theme="auto"
            data-callback="cfTurnstileOnSuccess"
            data-expired-callback="cfTurnstileOnExpired"
            data-error-callback="cfTurnstileOnError"
        ></div>
    </div>

    <script>
        window.cfTurnstileOnSuccess = function (token) {
            const input = document.querySelector('input[wire\\:model="data.cfTurnstileResponse"]');

            if (! input) {
                return;
            }

            input.value = token;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        };

        window.cfTurnstileOnExpired = window.cfTurnstileOnError = function () {
            const input = document.querySelector('input[wire\\:model="data.cfTurnstileResponse"]');

            if (input) {
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };
    </script>

    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
