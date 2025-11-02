<x-backoffice.layout>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="mb-3">Settings</h6>

                    <div class="settings-nav" id="settingsNav">
                        <!-- General Settings -->
                        <button class="settings-toggle d-flex align-items-center w-100 collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#settings-general" aria-expanded="false">
                            <i class="bi bi-gear me-2"></i>
                            <span>General Settings</span>
                            <i class="bi bi-chevron-down ms-auto settings-caret"></i>
                        </button>
                        <div id="settings-general" class="collapse settings-subwrap">
                            <a href="#" class="settings-sublink">Company Profile</a>
                            <a href="#" class="settings-sublink">Branding</a>
                            <a href="#" class="settings-sublink">Localization</a>
                        </div>

                        <!-- Website Settings -->
                        <button class="settings-toggle d-flex align-items-center w-100 collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#settings-website" aria-expanded="false">
                            <i class="bi bi-globe2 me-2"></i>
                            <span>Website Settings</span>
                            <i class="bi bi-chevron-down ms-auto settings-caret"></i>
                        </button>
                        <div id="settings-website" class="collapse settings-subwrap">
                            <a href="#" class="settings-sublink">Landing Page</a>
                            <a href="#" class="settings-sublink">SEO</a>
                            <a href="#" class="settings-sublink">Custom Domains</a>
                        </div>

                        <!-- App Settings (includes Attendance + Geolocation) -->
                        <button class="settings-toggle d-flex align-items-center w-100" type="button"
                                data-bs-toggle="collapse" data-bs-target="#settings-app" aria-expanded="true">
                            <i class="bi bi-phone me-2"></i>
                            <span>App Settings</span>
                            <i class="bi bi-chevron-down ms-auto settings-caret"></i>
                        </button>
                        <div id="settings-app" class="collapse show settings-subwrap">
                            <a href="#" class="settings-sublink">Invoice Settings</a>
                            <a href="#" class="settings-sublink">Invoice Templates</a>
                            <a href="#" class="settings-sublink">Printer</a>
                            <a href="#" class="settings-sublink">POS</a>
                            <a href="#" class="settings-sublink">Signatures</a>
                            <a href="#" class="settings-sublink">Custom Fields</a>
                            <div class="settings-divider"></div>
                            <a href="{{ route('backoffice.settings.attendance-settings') }}"
                               class="settings-sublink {{ request()->routeIs('backoffice.settings.attendance-settings') ? 'active' : '' }}">Attendance Settings</a>
                            <a href="{{ route('backoffice.settings.geolocation-settings') }}"
                               class="settings-sublink {{ request()->routeIs('backoffice.settings.geolocation-settings') ? 'active' : '' }}">Geolocation Settings</a>
                        </div>

                        <!-- System Settings -->
                        <button class="settings-toggle d-flex align-items-center w-100 collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#settings-system" aria-expanded="false">
                            <i class="bi bi-pc-display me-2"></i>
                            <span>System Settings</span>
                            <i class="bi bi-chevron-down ms-auto settings-caret"></i>
                        </button>
                        <div id="settings-system" class="collapse settings-subwrap">
                            <a href="#" class="settings-sublink">Backups</a>
                            <a href="#" class="settings-sublink">Email</a>
                            <a href="#" class="settings-sublink">Integrations</a>
                        </div>

                        <!-- Financial Settings -->
                        <button class="settings-toggle d-flex align-items-center w-100 collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#settings-financial" aria-expanded="false">
                            <i class="bi bi-cash-coin me-2"></i>
                            <span>Financial Settings</span>
                            <i class="bi bi-chevron-down ms-auto settings-caret"></i>
                        </button>
                        <div id="settings-financial" class="collapse settings-subwrap">
                            <a href="#" class="settings-sublink">Taxes</a>
                            <a href="#" class="settings-sublink">Payment Methods</a>
                            <a href="#" class="settings-sublink">Currencies</a>
                        </div>

                        <!-- Other Settings -->
                        <button class="settings-toggle d-flex align-items-center w-100 collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#settings-other" aria-expanded="false">
                            <i class="bi bi-sliders2 me-2"></i>
                            <span>Other Settings</span>
                            <i class="bi bi-chevron-down ms-auto settings-caret"></i>
                        </button>
                        <div id="settings-other" class="collapse settings-subwrap">
                            <a href="#" class="settings-sublink">Feature Flags</a>
                            <a href="#" class="settings-sublink">Audit Logs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{ $slot }}
    </div>
</x-backoffice.layout>
