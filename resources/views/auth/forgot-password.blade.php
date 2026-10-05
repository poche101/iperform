<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iPerform — Forgot Password</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:'#3C3489'}}}}</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body class="bg-[#f5f0ff] min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl p-10 w-full max-w-sm shadow-sm">
  <div class="text-center mb-8">
    <div class="w-16 h-16 bg-[#3C3489] rounded-full flex items-center justify-center mx-auto mb-3">
      <i class="ti ti-lock-question text-white text-3xl"></i>
    </div>
    <div class="text-2xl font-bold text-gray-900">Forgot password?</div>
    <div class="text-sm text-gray-500 mt-2">Enter your username and we'll email you a link to create a new password.</div>
  </div>

  @if(session('status'))
  <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('status') }}</div>
  @endif

  <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Username</label>
      <input name="username" value="{{ old('username') }}" required class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] focus:ring-2 focus:ring-[#eeedfe]" placeholder="Enter username" autocomplete="username">
    </div>

    @if($errors->any())
    <div class="text-red-600 text-sm">{{ $errors->first() }}</div>
    @endif

    <button type="submit" class="w-full bg-[#3C3489] text-white py-2.5 rounded-lg font-medium text-sm hover:bg-[#26215C] transition">
      Send reset link
    </button>
  </form>

  <div class="text-center mt-5">
    <a href="{{ route('login') }}" class="text-sm text-[#3C3489] hover:underline">&larr; Back to sign in</a>
  </div>
</div>
</body>
</html>
