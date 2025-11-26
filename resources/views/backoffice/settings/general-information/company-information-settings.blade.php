<x-backoffice.settings-layout>
    @php
        $profile = $tenantProfile ?? new \App\Models\TenantProfile();
        $daysOfWeek = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];
        $socialPlatforms = [
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'whatsapp' => 'WhatsApp',
            'facebook' => 'Facebook',
            'website' => 'Website',
        ];
    @endphp

    <div class="col-md-9">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="mb-1">Company Information</h5>
                        <p class="text-muted mb-0">Update the brand details that your customers will see.</p>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <div class="fw-semibold mb-1">Please fix the following issues:</div>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('backoffice.tenant-profile.update-general-info') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="contact_email">Contact Email</label>
                            <input
                                type="email"
                                class="form-control"
                                id="contact_email"
                                name="contact_email"
                                value="{{ old('contact_email', $profile->contact_email ?? '') }}"
                                placeholder="hello@brand.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="contact_phone">Contact Phone</label>
                            <input
                                type="text"
                                class="form-control"
                                id="contact_phone"
                                name="contact_phone"
                                value="{{ old('contact_phone', $profile->contact_phone ?? '') }}"
                                placeholder="+62 812 1234 5678">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="address">Address</label>
                            <textarea
                                class="form-control"
                                id="address"
                                name="address"
                                rows="3"
                                placeholder="Jl. Example No. 1, Jakarta">{{ old('address', $profile->address ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="latitude">Latitude</label>
                            <input
                                type="number"
                                step="any"
                                class="form-control"
                                id="latitude"
                                name="latitude"
                                value="{{ old('latitude', $profile->latitude) }}"
                                placeholder="-6.2088">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="longitude">Longitude</label>
                            <input
                                type="number"
                                step="any"
                                class="form-control"
                                id="longitude"
                                name="longitude"
                                value="{{ old('longitude', $profile->longitude) }}"
                                placeholder="106.8456">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Opening Hours</label>
                            <div class="row g-2">
                                @foreach ($daysOfWeek as $key => $label)
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text fw-semibold" style="min-width: 130px;">{{ $label }}</span>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="opening_hours[{{ $key }}]"
                                                value="{{ old('opening_hours.' . $key, data_get($profile->opening_hours, $key, '')) }}"
                                                placeholder="08:00 - 21:00">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Leave blank for closed days.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Social Links</label>
                            <div class="row g-2">
                                @foreach ($socialPlatforms as $platformKey => $platformLabel)
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted" for="social_{{ $platformKey }}">{{ $platformLabel }}</label>
                                        <input
                                            type="url"
                                            class="form-control"
                                            id="social_{{ $platformKey }}"
                                            name="social_links[{{ $platformKey }}]"
                                            value="{{ old('social_links.' . $platformKey, data_get($profile->social_links, $platformKey, '')) }}"
                                            placeholder="https://{{ $platformKey }}.com/yourbrand">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="logo_url">Logo URL</label>
                            <input
                                type="url"
                                class="form-control"
                                id="logo_url"
                                name="logo_url"
                                value="{{ old('logo_url', $profile->logo_url ?? '') }}"
                                placeholder="https://cdn.brand.com/logo.png">
                            <small class="text-muted">Use a hosted image link for now.</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-main px-4">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-backoffice.settings-layout>
