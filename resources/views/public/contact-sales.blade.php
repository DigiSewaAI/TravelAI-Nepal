<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.contact_sales_title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-lg p-8 max-w-lg w-full">
        <h1 class="text-2xl font-bold text-gray-900 mb-3">
            {{ __('messages.contact_sales_title') }}
        </h1>
        <p class="text-gray-600 mb-6">
            {{ __('messages.contact_sales_description') }}
        </p>

        <div class="space-y-3 text-sm">
            <p><strong>{{ __('messages.contact_sales_email_label') }}:</strong>
               <a href="mailto:sales@travelai-nepal.com" class="text-blue-600">sales@travelai-nepal.com</a>
            </p>
            <p><strong>{{ __('messages.contact_sales_phone_label') }}:</strong>
               <a href="tel:+977-1-XXXXXXX" class="text-blue-600">+977-1-XXXXXXX</a>
            </p>
        </div>

        <a href="{{ route('home') }}" class="inline-block mt-6 text-blue-600 hover:underline">
            ← {{ __('messages.contact_sales_back') }}
        </a>
    </div>
</body>
</html>