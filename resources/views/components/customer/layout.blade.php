<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Playfair+Display:wght@500;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Page flip -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@stpageflip/pageflip/dist/css/page-flip.min.css" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide/dist/css/splide.min.css">

    <link rel="icon" href="{{ global_asset('storage/logos/Qash_single_logogram.png') }}" type="image/png">
    <link rel="stylesheet" href="https://unpkg.com/lenis@1.3.15/dist/lenis.css">

    <title>Qash</title>
    @stack('meta')

    @vite(['resources/css/customer.css', 'resources/js/customer.js', 'resources/css/app.css', ])
    @livewireStyles
</head>

<body class="scroll-container">

    <x-customer.navbar></x-customer.navbar>

    <main>
        @include('components.toast-delete')
        {{ $slot }}
    </main>

    <x-customer.footer></x-customer.footer>

    @livewireScripts
    @if (tenant())
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({ options }) => {
                    options.headers = options.headers || {};
                    options.headers['X-Tenant'] = '{{ tenant()->id }}';
                });
            });
        </script>
    @endif

    <!-- JS LIBRARIES -->
    <script src="https://cdn.jsdelivr.net/npm/page-flip/dist/js/page-flip.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide/dist/js/splide.min.js"></script>
    <script src="https://unpkg.com/lenis@1.3.15/dist/lenis.min.js"></script> 

    <!-- Lottie animations -->
    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.6.2/dist/dotlottie-wc.js"></script>

</body>
</html>
