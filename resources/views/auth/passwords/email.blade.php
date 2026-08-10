<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <span>{{ $message }}</span> @enderror
            </div>
            <button type="submit">Send Password Reset Link</button>
        </form>
    </div>
</body>
</html>