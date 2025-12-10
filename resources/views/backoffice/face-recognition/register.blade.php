<x-backoffice.layout>
    <div class="container text-center">
        <h1 class="mb-4">📸 Pendaftaran Wajah Karyawan</h1>

        <div class="card mb-4 shadow-sm">
            <div class="card-body p-2 d-flex justify-content-center">
                <video id="video" width="480" height="360" playsinline muted class="rounded border shadow-sm"></video>
                <canvas id="canvas" style="display:none;"></canvas>
            </div>
        </div>

        <div class="mb-3 text-start">
            <label for="nameInput" class="form-label fw-bold">Nama untuk Pendaftaran:</label>
            <input type="text" value="{{ $user->first_name .' '. $user->last_name }}" id="nameInput" disabled class="form-control" placeholder="{{ $user->first_name .' '. $user->last_name }}">
        </div>

        <div class="d-grid gap-2 mb-3">
            <button id="startCameraBtn" class="btn btn-outline-primary btn-lg">Mulai Kamera</button>
            <button id="registerBtn" class="btn btn-primary btn-lg" disabled>Mulai Pendaftaran</button>
            <button id="captureBtn" class="btn btn-warning btn-lg" style="display:none;">📷 Ambil Foto Pose Saat Ini</button>
        </div>

        <p id="status-message" class="mt-3 fs-5">Klik "Mulai Kamera" untuk memulai.</p>
        <div id="poseInstruction" class="fs-5 fw-bold mb-3 text-warning"></div>

        <div id="register-controls"></div>

        <a href="{{ route('backoffice.face.attendance', ['tenant' => tenant('id')]) }}" class="btn btn-secondary mt-4">Kembali ke Presensi</a>
    </div>

</x-backoffice.layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const API_URL_REGISTER = 'http://127.0.0.1:5001/register';
    const API_URL_CANCEL = 'http://127.0.0.1:5001/cancel_register';
    const ATTENDANCE_URL = "{{ route('backoffice.face.attendance', ['tenant' => tenant('id')]) }}";
    const TENANT_ID = @json(tenant('id'));

    let isRegistering = false;
    let totalFrames = 5;
    let currentFrame = 0;
    let currentPoseIndex = 0;
    let isSending = false; // Mencegah klik ganda

    const POSE_INSTRUCTIONS = [
        "📸 Posisi Wajah LURUS ke depan",
        "👉 Miringkan wajah ke KIRI",
        "👈 Miringkan wajah ke KANAN",
        "👆 Tengadahkan wajah ke ATAS",
        "👇 Tundukkan wajah ke BAWAH"
    ];

    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const registerBtn = document.getElementById('registerBtn');
    const captureBtn = document.getElementById('captureBtn');
    const statusMsg = document.getElementById('status-message');
    const nameInput = document.getElementById('nameInput');
    const controlsContainer = document.getElementById('register-controls');
    const poseInstruction = document.getElementById('poseInstruction');

    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            try { await video.play(); } catch (e) {}
            if (statusMsg) {
                statusMsg.innerText = 'Kamera siap.';
                statusMsg.className = 'text-muted';
            }
            if (registerBtn) registerBtn.disabled = false;
            if (startCameraBtn) startCameraBtn.disabled = true;
        } catch (err) {
            if (statusMsg) {
                statusMsg.innerText = '❌ Gagal akses kamera. Izinkan di browser.';
                statusMsg.className = 'text-danger';
            }
        }
    }

    function setupGoBackToAttendanceButton(message, isError = false) {
        captureBtn.style.display = "none";
        registerBtn.style.display = "block";
        registerBtn.className = isError ? "btn btn-danger btn-lg w-100 mb-3" : "btn btn-secondary btn-lg w-100 mb-3";
        registerBtn.textContent = isError ? "❌ Kembali ke Halaman Presensi" : "✅ Kembali ke Halaman Presensi";
        registerBtn.onclick = () => { window.location.href = ATTENDANCE_URL; };
        statusMsg.innerText = message;
        statusMsg.className = isError ? 'text-danger' : 'text-success';
        poseInstruction.textContent = "";
    }

    async function cancelRegistration(name) {
        if (!name) return;
        isRegistering = false;
        statusMsg.innerText = "🚫 Membatalkan dan menghapus data...";
        statusMsg.className = "text-warning";
        const formData = new FormData();
        formData.append('name', name);
        try {
            // Add tenant for cancel endpoint as well
            formData.append('tenant_id', TENANT_ID ?? '');
            await fetch(API_URL_CANCEL, {
                method: "POST",
                headers: { 'X-Tenant-Id': TENANT_ID ?? '' },
                body: formData
            });
            window.location.href = ATTENDANCE_URL;
        } catch (error) {
            setupGoBackToAttendanceButton("🚫 Pembatalan Gagal terhubung ke server.", true);
        }
    }

    async function sendFrameAndGetResult(name, frameIndex, poseIndex) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.9));
        const formData = new FormData();
        formData.append('name', name);
        formData.append('image', blob, `${name}_${frameIndex}.jpg`);
        formData.append('frame_index', frameIndex);
        formData.append('total_frames', totalFrames);
        formData.append('required_pose', poseIndex);
        // Include tenant id in both header and body for compatibility
        formData.append('tenant_id', TENANT_ID ?? '');
        try {
            const response = await fetch(API_URL_REGISTER, {
                method: "POST",
                headers: { 'X-Tenant-Id': TENANT_ID ?? '' },
                body: formData
            });
            return await response.json();
        } catch (error) {
            return { status: 'error', message: '❌ Terjadi kesalahan saat terhubung ke server Flask.', fatalError: true };
        }
    }

    captureBtn.addEventListener('click', async () => {
        if (isSending || !isRegistering) return;

        isSending = true;
        captureBtn.disabled = true;
        poseInstruction.textContent = "Processing...";
        statusMsg.innerText = "Memvalidasi gambar...";
        statusMsg.className = "text-info";

        const name = nameInput.value.trim();
        const result = await sendFrameAndGetResult(name, currentFrame, currentPoseIndex);

        if (result.status === 'error' && (result.done || result.fatalError)) {
            isRegistering = false;
            controlsContainer.innerHTML = '';
            setupGoBackToAttendanceButton(result.message, true);
            return;
        }

        if (result.status === 'skip') {
            statusMsg.innerText = result.message;
            statusMsg.className = "text-danger";
        }

        if (result.status === 'success' || result.done) {
            statusMsg.innerText = result.message;
            statusMsg.className = "text-success";

            const progressBar = controlsContainer.querySelector('.progress-bar');
            const progressPercent = ((currentFrame + 1) / totalFrames) * 100;
            if (progressBar) {
                progressBar.style.width = `${progressPercent}%`;
                progressBar.textContent = `${Math.round(progressPercent)}%`;
            }

            if (result.done) {
                isRegistering = false;
                setupGoBackToAttendanceButton(result.message, false);
                return;
            }

            currentFrame++;
            currentPoseIndex = (currentFrame % totalFrames);
        }

        poseInstruction.textContent = POSE_INSTRUCTIONS[currentPoseIndex];
        poseInstruction.style.color = 'blue';

        isSending = false;
        captureBtn.disabled = false;
    });

    async function handleInitialRegisterClick() {
        const name = nameInput.value.trim();
        if (!name) {
            statusMsg.innerText = "⚠️ Nama wajib diisi!";
            statusMsg.className = "text-danger";
            return;
        }

        registerBtn.style.display = "none";
        captureBtn.style.display = "block";
        controlsContainer.innerHTML = `
            <div class="progress mb-3">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
            </div>
            <button id=\"cancelBtn\" class=\"btn btn-danger btn-lg w-100 mb-3\">❌ Batalkan Pendaftaran</button>
        `;
        document.getElementById('cancelBtn').onclick = () => cancelRegistration(name);

        isRegistering = true;
        currentFrame = 0;
        currentPoseIndex = 0;

        statusMsg.innerText = `Aksi: Ambil foto untuk pose ke 1/${totalFrames}.`;
        poseInstruction.textContent = POSE_INSTRUCTIONS[currentPoseIndex];
        poseInstruction.style.color = 'blue';
    }

    if (startCameraBtn) {
        startCameraBtn.addEventListener('click', (e) => {
            e.preventDefault();
            startCamera();
        });
    }

    registerBtn.addEventListener('click', handleInitialRegisterClick);
});
</script>
