<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iPerform — Reset Password</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:'#3C3489'}}}}</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body class="bg-[#f5f0ff] min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl p-10 w-full max-w-sm shadow-sm">
  <div class="text-center mb-8">
    <div class="w-16 h-16 bg-[#3C3489] rounded-full flex items-center justify-center mx-auto mb-3">
      <i class="ti ti-key text-white text-3xl"></i>
    </div>
    <div class="text-2xl font-bold text-gray-900">Create new password</div>
    <div class="text-sm text-gray-500 mt-2">Choose a password of at least 8 characters.</div>
  </div>

  <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ old('email', $email) }}">

    <div>
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">New password</label>
      <input type="password" name="password" required minlength="8" class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] focus:ring-2 focus:ring-[#eeedfe]" placeholder="New password" autocomplete="new-password">
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Confirm password</label>
      <input type="password" name="password_confirmation" required minlength="8" class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] focus:ring-2 focus:ring-[#eeedfe]" placeholder="Repeat new password" autocomplete="new-password">
    </div>

    @if($errors->any())
    <div class="text-red-600 text-sm">{{ $errors->first() }}</div>
    @endif

    <button type="submit" class="w-full bg-[#3C3489] text-white py-2.5 rounded-lg font-medium text-sm hover:bg-[#26215C] transition">
      Reset password
    </button>
  </form>

  <div class="text-center mt-5">
    <a href="{{ route('login') }}" class="text-sm text-[#3C3489] hover:underline">&larr; Back to sign in</a>
  </div>
</div>
</body>
</html>
