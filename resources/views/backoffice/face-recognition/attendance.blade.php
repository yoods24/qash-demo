<x-backoffice.layout>
       <div class="container text-center">
        <h1 class="mb-4">Absensi Wajah (Simulasi Login)</h1>

        <div class="card mb-4 shadow-sm">
            <div class="card-body p-2 d-flex justify-content-center">
                <video id="video" width="480" height="360" playsinline muted class="rounded border shadow-sm"></video>
                <canvas id="canvas" style="display:none;"></canvas>
            </div>
        </div>

        {{-- 🌟 INPUT USERNAME BARU 🌟 --}}
        <div class="mb-3">
            <label for="usernameInput"  class="form-label fw-bold">Username untuk Absen:</label>
            <input type="text" id="usernameInput" value="{{ $user->first_name .' '. $user->last_name }}" class="form-control" disabled placeholder="{{ $user->first_name .' '. $user->last_name }}">
        </div>
        <div class="d-grid gap-2 mb-3">
            <button id="startCameraBtn" class="btn btn-outline-primary btn-lg">Mulai Kamera</button>
            <button id="absenBtn" class="btn btn-primary btn-lg" disabled>Absen Sekarang</button>
        </div>
        <p id="status-message" class="mt-3 fs-5">Klik "Mulai Kamera" untuk memulai.</p>

        <a href="{{ route('backoffice.face.register', ['tenant' => tenant('id')]) }}" class="btn btn-secondary mt-4">Daftar Wajah</a>
    </div>

</x-backoffice.layout>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Elements#
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const statusMessage = document.getElementById('status-message');
    const absenBtn = document.getElementById('absenBtn');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const usernameInput = document.getElementById('usernameInput');

    // API (dev)
    const API_BASE_URL = 'http://127.0.0.1:5001';
    const API_URL_RECOGNIZE = `${API_BASE_URL}/recognize`;
    const CONFIRM_URL = "{{ route('backoffice.face.confirm', ['tenant' => tenant('id')]) }}";
    const ATTENDANCE_INDEX_URL = "{{ route('backoffice.attendance.index', ['tenant' => tenant('id')]) }}";
    // Tenant context
    const TENANT_ID = @json(tenant('id'));

    let cameraStream = null;
    let lastLat = null;
    let lastLng = null;

    async function setupCamera() {
        if (!video) return;
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = cameraStream;
            try { await video.play(); } catch (e) {}
            if (statusMessage) {
                statusMessage.innerText = 'Kamera siap.';
                statusMessage.className = 'text-muted';
            }
            if (absenBtn) absenBtn.disabled = false;
            if (startCameraBtn) startCameraBtn.disabled = true;
        } catch (err) {
            console.error('Gagal mengakses kamera:', err);
            if (statusMessage) {
                statusMessage.innerText = '❌ Gagal akses kamera. Izinkan di browser.';
                statusMessage.className = 'text-danger';
            }
            if (absenBtn) absenBtn.disabled = true;
        }
    }

    function stopCamera() {
        try {
            if (cameraStream) {
                cameraStream.getTracks().forEach(t => t.stop());
                cameraStream = null;
            }
            if (video) video.srcObject = null;
            if (absenBtn) absenBtn.disabled = true;
            if (startCameraBtn) startCameraBtn.disabled = false;
            if (statusMessage) {
                statusMessage.innerText = 'Kamera dihentikan.';
                statusMessage.className = 'text-muted';
            }
        } catch (_) {}
    }

    function captureImage() {
        if (!video || !canvas) return Promise.reject('Elemen video/canvas tidak ada');
        const context = canvas.getContext('2d');
        if (video.videoWidth === 0 || video.videoHeight === 0) {
            canvas.width = 480; canvas.height = 360;
            context.fillStyle = '#ccc'; context.fillRect(0, 0, canvas.width, canvas.height);
            context.fillStyle = 'black'; context.fillText('Video not ready', 10, 50);
        } else {
            canvas.width = video.videoWidth; canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
        }
        return new Promise((resolve, reject) => {
            canvas.toBlob(blob => {
                if (blob) resolve(blob);
                else reject(new Error('Gagal buat blob'));
            }, 'image/jpeg', 0.9);
        });
    }

    async function sendToRecognize() {
        if (!statusMessage) return;
        statusMessage.innerText = '🔄 Mendeteksi wajah & lokasi...';
        statusMessage.className = 'text-info';
        if (absenBtn) absenBtn.disabled = true;

        let imageBlob;
        try {
            imageBlob = await captureImage();
        } catch (_) {
            statusMessage.innerText = '❌ Gagal ambil gambar.';
            statusMessage.className = 'text-danger';
            if (absenBtn) absenBtn.disabled = false;
            return;
        }

        const formData = new FormData();
        formData.append('image', imageBlob, 'photo.jpg');

        if (!usernameInput) {
            statusMessage.innerText = '❌ Error: Input username tidak ditemukan.';
            statusMessage.className = 'text-danger';
            if (absenBtn) absenBtn.disabled = false;
            return;
        }
        const username = usernameInput.value.trim();
        if (!username) {
            statusMessage.innerText = '❌ Error: Harap masukkan username.';
            statusMessage.className = 'text-danger';
            if (absenBtn) absenBtn.disabled = false;
            return;
        }
        formData.append('username', username);
        // Include tenant in body for tenant-aware API
        formData.append('tenant_id', TENANT_ID ?? '');

        // Geolocation
        try {
            statusMessage.innerText = '🔄 Mendapatkan lokasi...';
            const position = await new Promise((resolve, reject) => {
                const options = { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 };
                navigator.geolocation.getCurrentPosition(resolve, reject, options);
            });
            lastLat = position.coords.latitude;
            lastLng = position.coords.longitude;
            formData.append('latitude', lastLat);
            formData.append('longitude', lastLng);
        } catch (geoError) {
            let geoErrorMessage = 'Gagal mendapatkan lokasi.';
            if (geoError.code === 1) geoErrorMessage = 'Izin lokasi ditolak.';
            else if (geoError.code === 2) geoErrorMessage = 'Lokasi tidak tersedia.';
            else if (geoError.code === 3) geoErrorMessage = 'Waktu habis.';
            statusMessage.innerText = `❌ ${geoErrorMessage} Absensi dibatalkan.`;
            statusMessage.className = 'text-danger';
            if (absenBtn) absenBtn.disabled = false;
            return;
        }

        // Send to Flask
        try {
            statusMessage.innerText = '🔄 Mengirim data ke server...';
            const response = await fetch(API_URL_RECOGNIZE, {
                method: 'POST',
                // Do not set Content-Type manually; browser sets correct boundary
                headers: { 'X-Tenant-Id': TENANT_ID ?? '' },
                body: formData
            });
            if (!response.ok) {
                let errorMsg = `Server error: ${response.status}`;
                try { errorMsg = (await response.json()).message || errorMsg; } catch (_) {}
                throw new Error(errorMsg);
            }
            const result = await response.json();
            if (result.status === 'ok') {
                // Record attendance on Laravel after successful recognition
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const confirmResp = await fetch(CONFIRM_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf ?? '',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ latitude: lastLat, longitude: lastLng })
                    });
                    if (confirmResp.ok) {
                        const confirmJson = await confirmResp.json();
                        statusMessage.innerText = `✅ ${result.message}. ${confirmJson.message}.`;
                        statusMessage.className = 'text-success';
                        setTimeout(() => { window.location.href = ATTENDANCE_INDEX_URL; }, 1200);
                    } else {
                        statusMessage.innerText = `✅ ${result.message}. ⚠️ Failed to record attendance in app.`;
                        statusMessage.className = 'text-warning';
                    }
                } catch (_) {
                    statusMessage.innerText = `✅ ${result.message}. ⚠️ Failed to reach app server.`;
                    statusMessage.className = 'text-warning';
                }
            } else {
                statusMessage.innerText = `❌ ${result.message || 'Error tidak diketahui.'}`;
                statusMessage.className = 'text-danger';
            }
        } catch (error) {
            statusMessage.innerText = '❌ Anda belum melakukan daftar wajah.';
            statusMessage.className = 'text-danger';
        } finally {
            if (absenBtn) absenBtn.disabled = false;
        }
    }

    if (startCameraBtn) {
        startCameraBtn.addEventListener('click', (e) => {
            e.preventDefault();
            setupCamera();
        });
    }

    if (absenBtn) {
        absenBtn.addEventListener('click', (e) => {
            e.preventDefault();
            sendToRecognize();
        });
    }

    window.addEventListener('beforeunload', stopCamera);
});
</script>
