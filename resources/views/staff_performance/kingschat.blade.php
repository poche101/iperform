<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>iPerform — KingsChat sender</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/kingschat-web-sdk/dist/stylesheets/style.min.css">
</head>
<body class="bg-[#f5f0ff] min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-sm space-y-4">
  <h1 class="text-xl font-bold text-gray-900">KingsChat notifications</h1>
  <p class="text-sm text-gray-500">
    Notifications are sent from the KingsChat account you connect here. Sign in with a dedicated
    iPerform account, not a personal one.
  </p>
  <p class="text-sm {{ $connected ? 'text-emerald-600' : 'text-amber-600' }}" id="status">
    {{ $connected ? 'A sender account is connected. Connect again to replace it.' : 'No sender account connected yet.' }}
  </p>
  <button id="connect" class="w-full bg-[#3C3489] text-white py-2.5 rounded-lg font-medium text-sm hover:bg-[#26215C]">
    Connect sender account
  </button>
  <a href="{{ route('hr.dashboard') }}" class="block text-center text-sm text-gray-400 hover:text-[#3C3489]">Back to dashboard</a>
</div>

<script type="module">
  import kingsChatWebSdk from 'https://cdn.jsdelivr.net/npm/kingschat-web-sdk/+esm';
  const sdk = kingsChatWebSdk.login ? kingsChatWebSdk : kingsChatWebSdk.default;

  const btn = document.getElementById('connect');
  const status = document.getElementById('status');

  btn.addEventListener('click', async () => {
    btn.disabled = true;
    try {
      const t = await sdk.login({
        clientId: "{{ config('services.kingschat.client_id') }}",
        scopes: ['send_chat_message']
      });

      const res = await fetch("{{ route('hr.kingschat.store') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({
          access_token: t.accessToken,
          refresh_token: t.refreshToken,
          expires_in_millis: t.expiresInMillis
        })
      });
      if (!res.ok) throw new Error('Could not save the token.');
      status.textContent = 'Sender account connected.';
      status.className = 'text-sm text-emerald-600';
    } catch (e) {
      console.error(e);
      status.textContent = e.message || 'Connection failed.';
      status.className = 'text-sm text-red-600';
    } finally {
      btn.disabled = false;
    }
  });
</script>
</body>
</html>
