<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.offline_page_title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-2xl shadow-lg text-center max-w-md">
            <i class="fas fa-wifi text-6xl text-gray-400 mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.offline_title') }}</h1>
            <p class="text-gray-600 mt-2">{{ __('messages.offline_message') }}</p>
            <a href="/" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                {{ __('messages.offline_retry') }}
            </a>
        </div>
    </div>
</body>
</html>