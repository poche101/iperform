<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iPerform — Offline</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#3C3489">
</head>
<body class="bg-[#f5f0ff] min-h-screen flex items-center justify-center p-6">
  <div class="text-center max-w-sm">
    <div class="w-20 h-20 bg-[#3C3489] rounded-full flex items-center justify-center mx-auto mb-6">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728M15.536 8.464a5 5 0 010 7.072M12 12h.01M6.343 17.657a9 9 0 010-12.728M8.464 15.536a5 5 0 010-7.072" />
      </svg>
    </div>
    <div class="text-2xl font-bold text-gray-900 mb-2">You're offline</div>
    <div class="text-sm text-gray-500 mb-6">
      iPerform needs an internet connection to sync your data. Previously visited pages are still available below.
    </div>
    <button onclick="window.location.reload()"
      class="inline-flex items-center gap-2 bg-[#3C3489] text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-[#26215C] transition mb-4">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      Try again
    </button>
    <div class="text-xs text-gray-400 italic mt-4">
      "Whatever you do, work at it with all your heart." — Colossians 3:23
    </div>
  </div>
</body>
</html>
