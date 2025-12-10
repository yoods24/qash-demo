<x-backoffice.login-layout>
    <style>
    /* Container style */
    .custom-tabs {
        border-radius: 8px;
        padding: 5px;
        display: inline-flex;
        margin: 0 auto;
        max-width: max-content;
    }
    
    /* Default tab style */
    .custom-tabs .nav-link {
        border-radius: 6px;
        color: #6c757d; /* gray text */
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
        background: #f8f9fa !important; /* light gray background like your image */
    }

    /* Active tab */
    .custom-tabs .nav-link.active {
        color: #f25c05; /* orange text */
        font-weight: 600;
        box-shadow: 0 0 5px rgba(0,0,0,0.05);
        border-bottom: 1px solid #dee2e6; /* restore default Bootstrap line */
        padding: 0.5rem 5rem;
        border-color: #f25c05;
    }

    /* Hover effect */
    .custom-tabs .nav-link:hover {
        color: #f25c05; /* orange on hover */
        padding: 0.5rem 3rem;
    }
    .custom-tabs .nav-link.active:hover {
        padding: 0.5rem 5rem;
    }
    .nav-tabs {
        justify-content: space-around;
        background: #f8f9fa !important;
        width: auto;
        padding: 10px;
        border-radius: 9px;
        border: none;
        gap: 5px
    }
    .nav-tabs li {
    }
    .login-img {
        width: 250px;
        height: 50%;
        object-fit: cover;
        position: sticky;
    }
    .form-group input:active {
        border-color: #f25c05 !important;
    }
    </style>
    <section class="secondary-white-bg">
        <div class="custom-tabs my-5 text-black d-flex flex-column justify-content-center text-center">
            <div class="d-flex justify-content-center">
                <img class="login-img" 
                src="{{ global_asset('storage/logos/Logotype-Orange.png') }}" 
                alt="qash-logo">
            </div>
            <div class="login-detail border rounded my-5 p-5 d-flex flex-column gap-5">
                <h3>Masuk ke Qash</h3>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="simple-tab-0" data-bs-toggle="tab" href="#email-tab" role="tab" aria-controls="simple-tabpanel-0" aria-selected="true">Email</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="simple-tab-1" data-bs-toggle="tab" href="#telephone-tab" role="tab" aria-controls="simple-tabpanel-1" aria-selected="false">Telepon</a>
                    </li>                    
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="simple-tab-2" data-bs-toggle="tab" href="#qr-tab" role="tab" aria-controls="simple-tabpanel-1" aria-selected="false">QR</a>
                    </li>
                </ul>
                <div class="tab-content" id="tab-content">
                    <div class="tab-pane active" id="email-tab" role="tabpanel" aria-labelledby="simple-tab-0">
                        <form method="POST" action="{{ route('auth.store') }}">
                        @csrf
                            <div class="form-group text-start mb-4">
                                <label for="email">Email address</label>
                                <input 
                                    type="email" 
                                    class="my-3 form-control @error('email') is-invalid @enderror" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email') }}" 
                                    placeholder="email"
                                >
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group text-start mb-4">
                                <label for="company_code">Kode Perusahaan</label>
                                <input 
                                    type="text" 
                                    class="my-3 form-control text-uppercase @error('company_code') is-invalid @enderror" 
                                    id="company_code" 
                                    name="company_code" 
                                    value="{{ old('company_code') }}" 
                                    placeholder="ACME-OPS-01"
                                >
                                @error('company_code')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group text-start">
                                <label for="password">Password</label>
                                <input 
                                    type="password" 
                                    class="my-3 form-control @error('password') is-invalid @enderror" 
                                    id="password" 
                                    name="password" 
                                    placeholder="password"
                                >
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <a class="primer text-decoration-none bold" href="">Lupa Kata sandi?</a>
                            </div>
                            <button type="submit" class="btn btn-primer my-3 w-100">Submit</button>
                            <p class="text-muted">Belum punya akun <span class="primer">Qash?</span></p>
                        </form>
                    </div>
                    <div class="tab-pane" id="telephone-tab" role="tabpanel" aria-labelledby="telephone-tab">Tab 2 selected</div>
                    <div class="tab-pane" id="qr-tab" role="tabpanel" aria-labelledby="qr-tab">Tab 2 selected</div>
                </div>
            </div>
        </div>
    </section>
</x-backoffice.login-layout>
