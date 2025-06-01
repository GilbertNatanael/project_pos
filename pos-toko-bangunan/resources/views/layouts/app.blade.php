<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    
    <!-- Base Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Jika favicon.png -->
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <!-- Page Specific Styles -->
    @yield('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-RXf+QSDCUQs6zKbUz8zZAw7M9s6Q9Jv4QkOW6D2qG0t/IlzQynKM2CJFPFfxf8kXf2L6W4j0aOykh8a1pT9UFg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        @include('partials.sidebar')
        
        <!-- Main Content -->
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    
    <!-- Base Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    <!-- Page Specific Scripts -->
    @yield('scripts')
</body>
</html>


