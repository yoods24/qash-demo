<div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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
        <div id="geo-map" wire:ignore style="height: 320px; border-radius: 8px; overflow: hidden;"></div>
        <div class="form-text mt-1">Drag the marker to set coordinates; the blue circle is the allowed radius.</div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button class="btn btn-main" wire:click="saveGeofence">Save Geolocation</button>
        <button class="btn btn-outline-secondary" type="button" id="btn-use-current">Use Current Location</button>
    </div>

    <script>
        (function(){
            const waitForLeaflet = (cb) => {
                if (window.L) return cb();
                const t = setInterval(() => { if (window.L) { clearInterval(t); cb(); } }, 100);
            };

            function initOrGet() {
                const el = document.getElementById('geo-map');
                if (!el) return null;
                if (el._geo) return el._geo; // already initialized

                const cfg = {
                    lat: Number(@json($geo_lat ?? -6.200)),
                    lng: Number(@json($geo_lng ?? 106.816)),
                    radius: Number(@json($geo_radius ?? 200)),
                };

                const map = L.map(el).setView([cfg.lat, cfg.lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                const marker = L.marker([cfg.lat, cfg.lng], { draggable: true }).addTo(map);
                const circle = L.circle([cfg.lat, cfg.lng], { radius: cfg.radius, color: '#0d6efd' }).addTo(map);

                const ilat = () => document.getElementById('geo-lat');
                const ilng = () => document.getElementById('geo-lng');
                const irad = () => document.getElementById('geo-radius');

                const setInputs = (lat, lng) => {
                    const a = ilat(); const b = ilng();
                    if (a) {
                        a.value = Number(lat).toFixed(6);
                        // Inform Livewire that the value changed (wire:model.defer)
                        a.dispatchEvent(new Event('input', { bubbles: true }));
                        a.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (b) {
                        b.value = Number(lng).toFixed(6);
                        b.dispatchEvent(new Event('input', { bubbles: true }));
                        b.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                };

                marker.on('dragend', () => {
                    const p = marker.getLatLng();
                    circle.setLatLng(p);
                    setInputs(p.lat, p.lng);
                });

                function bindInputListeners() {
                    const latInput = ilat();
                    const lngInput = ilng();
                    const radInput = irad();
                    if (latInput && !latInput._geoBound) {
                        latInput.addEventListener('change', () => {
                            const lat = parseFloat(latInput.value);
                            const lng = parseFloat(lngInput?.value ?? 'NaN');
                            if (!isFinite(lat) || !isFinite(lng)) return;
                            marker.setLatLng([lat,lng]);
                            circle.setLatLng([lat,lng]);
                            map.setView([lat,lng]);
                        });
                        latInput._geoBound = true;
                    }
                    if (lngInput && !lngInput._geoBound) {
                        lngInput.addEventListener('change', () => {
                            const lat = parseFloat(latInput?.value ?? 'NaN');
                            const lng = parseFloat(lngInput.value);
                            if (!isFinite(lat) || !isFinite(lng)) return;
                            marker.setLatLng([lat,lng]);
                            circle.setLatLng([lat,lng]);
                            map.setView([lat,lng]);
                        });
                        lngInput._geoBound = true;
                    }
                    if (radInput && !radInput._geoBound) {
                        radInput.addEventListener('input', () => {
                            const r = parseInt(radInput.value||'200', 10);
                            circle.setRadius(r);
                        });
                        radInput._geoBound = true;
                    }
                }

                // Expose for later re-binding
                el._geo = { map, marker, circle, bindInputListeners };
                bindInputListeners();

                document.getElementById('btn-use-current')?.addEventListener('click', function(){
                    if (!('geolocation' in navigator)) return alert('Geolocation not supported');
                    navigator.geolocation.getCurrentPosition(pos => {
                        const { latitude: lat, longitude: lng } = pos.coords;
                        marker.setLatLng([lat,lng]);
                        circle.setLatLng([lat,lng]);
                        map.setView([lat,lng]);
                        setInputs(lat, lng);
                        // Also sync Livewire state directly for reliability
                        try {
                            @this.set('geo_lat', Number(lat));
                            @this.set('geo_lng', Number(lng));
                        } catch (e) {}
                    }, () => alert('Could not get current location'));
                });

                return el._geo;
            }

            waitForLeaflet(() => {
                const inst = initOrGet();
                // After any Livewire DOM update, re-bind input listeners (map container is wire:ignore)
                try {
                    if (window.Livewire && Livewire.hook) {
                        Livewire.hook('message.processed', () => { inst?.bindInputListeners?.(); });
                    } else {
                        document.addEventListener('livewire:load', () => {
                            if (window.Livewire && Livewire.hook) {
                                Livewire.hook('message.processed', () => { inst?.bindInputListeners?.(); });
                            }
                        });
                    }
                } catch (e) { /* no-op */ }
            });
        })();
    </script>
</div>
