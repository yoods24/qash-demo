<x-backoffice.layout>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="mb-3">Settings</h6>
                    <ul class="nav nav-pills flex-column gap-2">
                        <li class="nav-item"><a class="nav-link" href="#">General Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Website Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">App Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">System Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Financial Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Other Settings</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('backoffice.settings.index') }}">Attendance Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('backoffice.settings.index') }}#geo-pane">Geolocation Settings</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Attendance Settings</h5>
                    <livewire:backoffice.settings.attendance-settings />
                </div>
            </div>
        </div>
    </div>
</x-backoffice.layout>

