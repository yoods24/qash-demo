<x-backoffice.layout>
<div class="container my-2">
    <h2 class="mb-4 text-dark">Edit Profile</h2>

    <ul class="nav nav-tabs mb-3" id="orderTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="basicinfo-tab" data-bs-toggle="tab" data-bs-target="#basicinfo" type="button" role="tab">Basic Info</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="changepassword-tab" data-bs-toggle="tab" data-bs-target="#changepassword" type="button" role="tab">Change Password</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="notificationsettings-tab" data-bs-toggle="tab" data-bs-target="#notificationsettings" type="button" role="tab">Notification Settings</button>
      </li>
    </ul>

    <div class="tab-content" id="orderTabsContent">
        {{-- BASIC INFO TAB --}}
        <div class="tab-pane fade show active" id="basicinfo" role="tabpanel" aria-labelledby="basicinfo-tab">
            <form action="{{ route('backoffice.profile.update', Auth::user()) }}" method="POST">
                @csrf
                @method('POST')
                <div class="row g-2">
                    <div class="col-6">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" value="{{ $user->firstName }}" required>
                    </div>
                    <div class="col-6">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" value="{{ $user->lastName }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                </div>
                
                <div class="mb-3">
                    <label for="about" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="about" name="about" value="{{ $user->phone }}" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/backoffice" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primer text-white">Update Profile</button>
                </div>
            </form>
        </div>

        {{-- CHANGE PASSWORD TAB --}}
        <div class="tab-pane fade" id="changepassword" role="tabpanel" aria-labelledby="changepassword-tab">
            <form method="POST" action="{{ route('backoffice.profile.password.update', Auth::user()) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/backoffice" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-warning text-white">Change Password</button>
                </div>
            </form>
        </div>

        {{-- NOTIFICATION SETTINGS TAB --}}
        <div class="tab-pane fade" id="notificationsettings" role="tabpanel" aria-labelledby="notificationsettings-tab">
            <form method="POST" action="{{ route('backoffice.profile.notification.update', Auth::user()) }}">
                @csrf

                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" {{ $user->email_notifications ? 'checked' : '' }}>
                    <label class="form-check-label" for="email_notifications">
                        Receive Email Notifications
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="sms_notifications" id="sms_notifications" {{ $user->sms_notifications ? 'checked' : '' }}>
                    <label class="form-check-label" for="sms_notifications">
                        Receive SMS Notifications
                    </label>
                </div>

                <button type="submit" class="btn btn-success mt-2">Save Settings</button>
            </form>
        </div>
    </div>

</div>
</x-backoffice.layout>
