<div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <ul class="nav nav-tabs" id="attendanceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="att-tab" data-bs-toggle="tab" data-bs-target="#att-pane" type="button" role="tab">Attendance</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="geo-tab" data-bs-toggle="tab" data-bs-target="#geo-pane" type="button" role="tab">Geolocation</button>
        </li>
    </ul>
    <div class="tab-content border-start border-end border-bottom p-3">
        <div class="tab-pane fade show active" id="att-pane" role="tabpanel">
            <div class="mb-3">
                <label class="form-label fw-semibold">Attendance Mode</label>
                <div class="d-flex gap-4 flex-wrap">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="mode-default-combined" value="default_combined" wire:model="mode">
                        <label class="form-check-label" for="mode-default-combined">Default (Face + Geo)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="mode-geo" value="geo" wire:model="mode">
                        <label class="form-check-label" for="mode-geo">Geolocation Only</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="mode-face" value="face" wire:model="mode">
                        <label class="form-check-label" for="mode-face">Facial Recognition Only</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="mode-manual" value="manual" wire:model="mode">
                        <label class="form-check-label" for="mode-manual">Manual (Admin sets attendance)</label>
                    </div>
                </div>
                <div class="form-text">Default combines face verification + geofence. Manual disables self clock-in/out for staff.</div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Enable Facial Recognition</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="face-enabled" wire:model="face_recognition_enabled">
                        <label class="form-check-label" for="face-enabled">{{ $face_recognition_enabled ? 'Enabled' : 'Disabled' }}</label>
                    </div>
                    <div class="form-text">Required for Default or Facial modes.</div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Apply Facial Recognition To</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="face-all" value="all" wire:model="apply_face_to">
                            <label class="form-check-label" for="face-all">All Users</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="face-per-user" value="per_user" wire:model="apply_face_to">
                            <label class="form-check-label" for="face-per-user">Per User Setting</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary" wire:click="saveAttendance">Save Settings</button>
            </div>
        </div>

        <div class="tab-pane fade" id="geo-pane" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Latitude</label>
                    <input id="geo-lat" type="number" step="any" class="form-control" wire:model.defer="geo_lat" placeholder="e.g. -6.200">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Longitude</label>
                    <input id="geo-lng" type="number" step="any" class="form-control" wire:model.defer="geo_lng" placeholder="e.g. 106.816">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Radius (meters)</label>
                    <input id="geo-radius" type="number" class="form-control" wire:model.defer="geo_radius" min="50" max="5000">
                </div>
            </div>

            <div class="mt-3">
                <div id="geo-map" style="height: 320px; border-radius: 8px; overflow: hidden;"></div>
                <div class="form-text mt-1">Drag the marker to set coordinates; the blue circle is the allowed radius.</div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary" wire:click="saveGeofence">Save Geolocation</button>
                <button class="btn btn-outline-secondary" type="button" id="btn-use-current">Use Current Location</button>
            </div>
            <script>
                (function(){
                    const el = document.getElementById('geo-map');
                    if (!el) return;
                    function waitForLeaflet(cb){
                        if (window.L) return cb();
                        const t = setInterval(() => { if (window.L) { clearInterval(t); cb(); } }, 100);
                    }

                    waitForLeaflet(() => {
                        const cfg = {
                            lat: Number(@json($geo_lat ?? -6.200)),
                            lng: Number(@json($geo_lng ?? 106.816)),
                            radius: Number(@json($geo_radius ?? 200)),
                        };
                        const setInputs = (lat, lng) => {
                            try { @this.set('geo_lat', lat); @this.set('geo_lng', lng); } catch(e) {}
                            const ilat = document.getElementById('geo-lat');
                            const ilng = document.getElementById('geo-lng');
                            if (ilat) ilat.value = Number(lat).toFixed(6);
                            if (ilng) ilng.value = Number(lng).toFixed(6);
                        };

                        const map = L.map('geo-map').setView([cfg.lat, cfg.lng], 16);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(map);

                        const marker = L.marker([cfg.lat, cfg.lng], { draggable: true }).addTo(map);
                        let circle = L.circle([cfg.lat, cfg.lng], { radius: cfg.radius, color: '#0d6efd' }).addTo(map);

                        marker.on('dragend', () => {
                            const p = marker.getLatLng();
                            circle.setLatLng(p);
                            setInputs(p.lat, p.lng);
                        });

                        const radInput = document.getElementById('geo-radius');
                        radInput?.addEventListener('input', () => {
                            const r = parseInt(radInput.value||200);
                            circle.setRadius(r);
                            try { @this.set('geo_radius', r); } catch(e) {}
                        });

                        const latInput = document.getElementById('geo-lat');
                        const lngInput = document.getElementById('geo-lng');
                        const syncFromInputs = () => {
                            const lat = parseFloat(latInput.value);
                            const lng = parseFloat(lngInput.value);
                            if (!isFinite(lat) || !isFinite(lng)) return;
                            marker.setLatLng([lat,lng]);
                            circle.setLatLng([lat,lng]);
                            map.setView([lat,lng]);
                            try { @this.set('geo_lat', lat); @this.set('geo_lng', lng); } catch(e) {}
                        };
                        latInput?.addEventListener('change', syncFromInputs);
                        lngInput?.addEventListener('change', syncFromInputs);

                        document.getElementById('btn-use-current')?.addEventListener('click', function(){
                            if (!('geolocation' in navigator)) return alert('Geolocation not supported');
                            navigator.geolocation.getCurrentPosition(pos => {
                                const { latitude: lat, longitude: lng } = pos.coords;
                                marker.setLatLng([lat,lng]);
                                circle.setLatLng([lat,lng]);
                                map.setView([lat,lng]);
                                setInputs(lat, lng);
                            }, err => alert('Could not get current location'));
                        });
                    });
                })();
            </script>
        </div>
    </div>
</div>
