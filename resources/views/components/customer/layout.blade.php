<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    {{-- Page flip --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@stpageflip/pageflip/dist/css/page-flip.min.css" />
    {{-- Bootstrap Icon --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <title>Qash</title>
    @vite(['resources/css/customer.css', 'resources/css/app.css','resources/js/app.js', 'resources/js/customer.js'])
</head>
<body class="scroll-container text-white">
    <x-customer.navbar></x-customer.navbar>
    <main>
        @include('components.toast-delete')
        {{$slot}}
    </main>
    <x-customer.footer></x-customer.footer>
</body>
{{-- page flip --}}
<script src="{{ asset('js/page-flip.browser.min.js') }}"></script>
</html>