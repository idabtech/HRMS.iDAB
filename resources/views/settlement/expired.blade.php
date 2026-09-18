<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Link Expired') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card text-center p-5 shadow-sm" style="max-width: 500px;">
        <h2 class="text-danger fw-bold mb-3">{{ __('Link Expired') }}</h2>
        <p class="text-muted mb-4">{{ __('This Full & Final Settlement review link has expired. Please contact your HR department to request an updated sharing link.') }}</p>
        <div><strong>{{ $settlement->settlement_number }}</strong></div>
    </div>
</body>
</html>