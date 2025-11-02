<div>
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Good {{ now()->format('A') === 'AM' ? 'Morning' : 'Evening' }}, {{ auth()->user()->firstName }} {{ auth()->user()->lastName }}</h3>
        <div class="text-muted">{{ now()->format('d M Y') }}</div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-4">
                        <div class="display-6" id="live-time">{{ now()->format('H:i:s') }}</div>
                        <div class="text-muted small">Current Time</div>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        @if(!$this->today || !$this->today->clock_in_at)
                            <button wire:click="clockIn" class="btn btn-success"
                                @if(($requiresGeo ?? false) && (!($geoOk ?? false) || !($geofenceConfigured ?? false))) disabled @endif>
                                Clock In
                            </button>
                        @endif
                        @if($this->today && $this->today->clock_in_at && !$this->today->clock_out_at)
                            <button wire:click="clockOut" class="btn btn-warning">Clock Out</button>
                            <button wire:click="toggleBreak" class="btn btn-outline-primary">
                                {{ $onBreak ? 'Resume Work' : 'Break' }}
                            </button>
                        @endif
                    </div>
                </div>
                <div class="px-3 pb-2 d-flex gap-4 text-muted small">
                    <div>
                        <span class="fw-semibold text-dark">On Shift:</span>
                        <span id="on-shift-timer">00h 00m</span>
                    </div>
                    <div>
                        <span class="fw-semibold text-dark">On Break:</span>
                        <span id="on-break-timer">00h 00m</span>
                    </div>
                </div>
                @if(($requiresGeo ?? false))
                    <div class="px-3 pb-2 small">
                        @if(!($geofenceConfigured ?? false))
                            <span class="text-warning">Geofence is not configured. Please contact admin.</span>
                        @elseif(is_null($geoOk))
                            <span class="text-muted">Detecting location… allow location access.</span>
                        @elseif(!$geoOk)
                            <span class="text-danger">Outside geofence ({{ $geoDistance }} m). Clock in disabled.</span>
                        @else
                            <span class="text-success">Within geofence ({{ $geoDistance }} m). You can clock in.</span>
                        @endif
                    </div>
                @endif
                @if(($requiresGeo ?? false) && !is_null($geoOk))
                <div class="px-3 pb-2 small">
                    @if($geoOk)
                        <span class="text-success">Within geofence ({{ $geoDistance }} m)</span>
                    @else
                        <span class="text-danger">Outside geofence ({{ $geoDistance }} m)</span>
                    @endif
                    @if(!empty($geofence))
                        <span class="ms-2 text-muted">You: {{ number_format($lat ?? 0, 5) }}, {{ number_format($lng ?? 0, 5) }} | Site: {{ number_format($geofence['lat'] ?? 0, 5) }}, {{ number_format($geofence['lng'] ?? 0, 5) }} (r={{ (int)($geofence['radius'] ?? 0) }}m)</span>
                    @endif
                </div>
                @endif

                @if(($requiresFace ?? false))
                    <div class="px-3 pb-2 small">
                        <span class="fw-semibold text-dark">Facial recognition:</span>
                        <span class="text-success ms-1">On</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="mb-3">Days Overview This Month</h6>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="text-center">
                            <div class="h4 mb-0">{{ $overview['totalWorkingDays'] }}</div>
                            <div class="text-muted small">Total Working Days</div>
                        </div>
                        <div class="text-center">
                            <div class="h4 mb-0 text-danger">{{ $overview['absentDays'] }}</div>
                            <div class="text-muted small">Absent Days</div>
                        </div>
                        <div class="text-center">
                            <div class="h4 mb-0 text-success">{{ $overview['presentDays'] }}</div>
                            <div class="text-muted small">Present Days</div>
                        </div>
                        <div class="text-center">
                            <div class="h4 mb-0 text-warning">{{ $overview['halfDays'] }}</div>
                            <div class="text-muted small">Half Days</div>
                        </div>
                        <div class="text-center">
                            <div class="h4 mb-0 text-primary">{{ $overview['lateDays'] }}</div>
                            <div class="text-muted small">Late Days</div>
                        </div>
                        <div class="text-center">
                            <div class="h4 mb-0 text-info">{{ $overview['holidays'] }}</div>
                            <div class="text-muted small">Holidays</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0">Attendance</h6>
            </div>
            <div id="fi-app">
                {{ $this->table }}
            </div>
        </div>
    </div>

    <script>
        // Lightweight ticking clock
        (function(){
            const el = document.getElementById('live-time');
            if (!el) return;
            const tick = () => {
                const d = new Date();
                const pad = (n) => (n<10? '0'+n : n);
                el.textContent = `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
            };
            tick();
            setInterval(tick, 1000);
        })();

        // Live timers for on-shift and on-break
        (function(){
            const onShift = document.getElementById('on-shift-timer');
            const onBreak = document.getElementById('on-break-timer');
            if (!onShift || !onBreak) return;

            const clockInAt = @json(optional($today?->clock_in_at)->toIso8601String());
            const breakSeconds = @json((int) ($today?->break_seconds ?? 0));
            const runningBreakStart = @json($runningBreakStart);

            function fmt(sec){
                sec = Math.max(0, Math.floor(sec||0));
                const h = Math.floor(sec/3600);
                const m = Math.floor((sec%3600)/60);
                return `${String(h).padStart(2,'0')}h ${String(m).padStart(2,'0')}m`;
            }

            function update(){
                const now = Date.now()/1000;
                let onBreakSec = 0;
                if (runningBreakStart){
                    onBreakSec = Math.max(0, now - (Date.parse(runningBreakStart)/1000));
                }
                onBreak.textContent = fmt(onBreakSec);

                if (clockInAt){
                    const started = Date.parse(clockInAt)/1000;
                    let shiftSec = Math.max(0, now - started) - (breakSeconds + onBreakSec);
                    onShift.textContent = fmt(shiftSec);
                } else {
                    onShift.textContent = fmt(0);
                }
            }
            update();
            setInterval(update, 1000);
        })();

        // Geolocation watcher: updates Livewire lat/lng and validates geofence
        (function(){
            if (!('geolocation' in navigator)) return;

            const G = @json($geofence ?? null);
            const geofenceRadius = parseInt((G && G.radius) ? G.radius : 200, 10);
            const accuracyThreshold = Math.min(500, Math.max(50, Math.round(geofenceRadius / 2))); // 50–500m

            let last = { lat: null, lng: null, acc: Infinity, ts: 0 };

            const setGeo = (lat, lng, acc) => {
                try {
                    @this.set('lat', parseFloat(lat));
                    @this.set('lng', parseFloat(lng));
                    @this.call('validateGeofence');
                    last = { lat, lng, acc: acc ?? last.acc, ts: Date.now() };
                } catch(e) { /* swallow */ }
            };

            const distanceMeters = (lat1, lon1, lat2, lon2) => {
                const R = 6371000;
                const dLat = (lat2-lat1) * Math.PI/180;
                const dLon = (lon2-lon1) * Math.PI/180;
                const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLon/2)**2;
                return Math.round(R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a))));
            };

            const acceptPosition = (coords) => {
                const { latitude: lat, longitude: lng, accuracy: acc } = coords;
                // Filter out very coarse or obviously wrong positions
                if (!isFinite(acc)) return false;
                // Accept if within threshold, or accuracy improves significantly, or we've never set a value
                if (last.lat === null || acc <= accuracyThreshold || acc < (last.acc * 0.7)) {
                    return true;
                }
                // If huge jump (> 2km) with poor accuracy, ignore
                if (last.lat !== null && distanceMeters(last.lat, last.lng, lat, lng) > 2000 && acc > accuracyThreshold) {
                    return false;
                }
                return false;
            };

            // Initial fix
            navigator.geolocation.getCurrentPosition(
                pos => {
                    if (acceptPosition(pos.coords)) {
                        setGeo(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                    }
                },
                err => console.debug('geo error', err),
                { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 }
            );

            // Continuous updates
            const watchId = navigator.geolocation.watchPosition(
                pos => {
                    if (acceptPosition(pos.coords)) {
                        setGeo(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                    }
                },
                err => {},
                { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 }
            );

            window.addEventListener('beforeunload', () => {
                try { navigator.geolocation.clearWatch(watchId); } catch(e) {}
            });
        })();

        // Optional: check if face dataset exists for current user (via Flask API)
        (function(){
            const el = document.getElementById('face-data-status');
            if (!el) return;
            const API_BASE_URL = 'http://127.0.0.1:5001';
            const TENANT_ID = @json(tenant('id'));
            const name = @json(auth()->user()->firstName . ' ' . auth()->user()->lastName);
            const url = `${API_BASE_URL}/has_face`;
            const fd = new FormData();
            fd.append('tenant_id', TENANT_ID ?? '');
            fd.append('name', name);
            fetch(url, { method: 'POST', headers: { 'X-Tenant-Id': TENANT_ID ?? '' }, body: fd })
              .then(r => r.ok ? r.json() : Promise.reject())
              .then(j => {
                  if (j && (j.registered === true || j.status === 'ok')) {
                      el.textContent = 'registered';
                      el.className = 'text-success';
                  } else if (j && (j.registered === false)) {
                      el.textContent = 'not registered';
                      el.className = 'text-danger';
                  } else {
                      el.textContent = 'unknown';
                      el.className = 'text-muted';
                  }
              })
              .catch(() => {
                  el.textContent = 'unknown';
                  el.className = 'text-muted';
              });
        })();
    </script>
</div>
