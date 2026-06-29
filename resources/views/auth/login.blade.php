<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iPerform — Sign In</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:'#3C3489'}}}}</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body class="bg-[#f5f0ff] min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl p-10 w-full max-w-sm shadow-sm">
  <div class="text-center mb-8">
    <div class="w-16 h-16 bg-[#3C3489] rounded-full flex items-center justify-center mx-auto mb-3">
      <i class="ti ti-arrow-up text-white text-3xl"></i>
    </div>
    <div class="text-2xl font-bold text-gray-900">iperform</div>
    <div class="text-[11px] text-gray-400 uppercase tracking-[3px] mt-1">Staff Appraisals</div>
  </div>

  <form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Username</label>
      <input name="username" value="{{ old('username') }}" class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] focus:ring-2 focus:ring-[#eeedfe]" placeholder="Enter username" autocomplete="username">
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Password</label>
      <input type="password" name="password" class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] focus:ring-2 focus:ring-[#eeedfe]" placeholder="Enter password" autocomplete="current-password">
    </div>

    @if($errors->any())
    <div class="text-red-600 text-sm">{{ $errors->first() }}</div>
    @endif

    <button type="submit" class="w-full bg-[#3C3489] text-white py-2.5 rounded-lg font-medium text-sm hover:bg-[#26215C] transition">
      Sign in
    </button>
  </form>
</div>
</body>
</html>
