<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>{{ __('messages.reset_password_title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
        .container { max-width: 400px; margin: 80px auto; background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h2 { font-size: 24px; font-weight: 700; margin-bottom: 20px; }
        .alert { padding: 12px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 16px; }
        label { display: block; font-weight: 600; margin-bottom: 6px; }
        input { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; }
        button { background: #2563eb; color: white; font-weight: 600; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; width: 100%; }
        button:hover { background: #1d4ed8; }
        .error { color: #dc2626; font-size: 14px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>{{ __('messages.reset_password_heading') }}</h2>
        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div>
                <label>{{ __('messages.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>
            <button type="submit" style="margin-top:16px;">{{ __('messages.send_reset_link') }}</button>
        </form>
    </div>
</body>
</html>