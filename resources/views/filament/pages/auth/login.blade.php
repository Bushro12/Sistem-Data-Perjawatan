<x-filament-panels::page.simple :has-topbar="false">
    <div class="esdap-login-wrapper">
        <div class="esdap-login-card">
            <div class="esdap-login-left">
                <div class="esdap-login-header">
                    <img src="{{ asset('images/mystaff-logo-clean.png') }}" alt="MySTAFF" class="esdap-logo-mark" />
                    <span>MySTAFF</span>
                </div>

                <p class="esdap-login-tagline">SISTEM DATA PERJAWATAN</p>
                <h1 class="esdap-login-title">Selamat Datang<span class="esdap-dot">.</span></h1>
                <p class="esdap-login-sub">Log masuk untuk akses sistem pengurusan perjawatan JKN Kedah.</p>

                <div class="esdap-form-area">
                    {{ $this->content }}
                </div>
            </div>

            <div class="esdap-login-right">
                <div class="esdap-right-glow esdap-right-glow-1"></div>
                <div class="esdap-right-glow esdap-right-glow-2"></div>
                <div class="esdap-right-content">
                    <div class="esdap-right-logo-ring">
                        <img src="{{ asset('images/mystaff-logo-clean.png') }}" alt="MySTAFF" class="esdap-right-logo" />
                    </div>
                    <p class="esdap-right-tagline">Kedah Healthcare Staffing Data System</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
