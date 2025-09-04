<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'سامانه تیکت' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="text-center mb-6">
                <h1 class="text-xl font-bold text-gray-900">سامانه تیکت</h1>
            </div>
            {{ $slot }}
        </div>
    </div>
</body>
</html>