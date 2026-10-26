@auth
@php
  $nbUnread = auth()->user()->unreadNotifications()
      ->where('type', \App\Notifications\TaskCommented::class)
      ->count();
@endphp

<div id="nb-root" class="relative flex-shrink-0"
     data-feed="{{ route('notifications.feed') }}"
     data-read-all="{{ route('notifications.readAll') }}">

  <button id="nb-btn" type="button" aria-label="Notifications" aria-haspopup="true" aria-expanded="false"
          class="relative w-10 h-10 rounded-full bg-white border border-[#e0daf5] flex items-center justify-center text-[#3C3489] hover:bg-[#eeedfe] transition">
    <i class="ti ti-bell text-xl"></i>
    <span id="nb-badge"
          class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#EF9F27] text-amber-900 text-[10px] font-bold flex items-center justify-center border-2 border-white {{ $nbUnread ? '' : 'hidden' }}">{{ $nbUnread > 9 ? '9+' : $nbUnread }}</span>
  </button>

  <div id="nb-panel"
       class="hidden absolute right-0 mt-2 w-[22rem] max-w-[calc(100vw-2rem)] bg-white border border-[#e0daf5] rounded-2xl shadow-xl overflow-hidden z-50">
    <div class="flex items-center justify-between px-4 py-3 bg-[#f5f0ff] border-b border-[#e0daf5]">
      <div class="text-sm font-semibold text-[#3C3489]">Notifications</div>
      <button id="nb-read-all" type="button" class="text-[11px] text-[#534AB7] hover:underline">Mark all read</button>
    </div>
    <div id="nb-list" class="max-h-96 overflow-y-auto divide-y divide-[#f0edf8]"></div>
    <div id="nb-empty" class="hidden px-4 py-8 text-center">
      <i class="ti ti-bell-off text-3xl text-[#AFA9EC] block mb-1"></i>
      <div class="text-xs text-gray-400">No comments yet</div>
    </div>
  </div>
</div>

<script>
(function () {
  const root = document.getElementById('nb-root');
  if (!root) return;

  const btn       = document.getElementById('nb-btn');
  const badge     = document.getElementById('nb-badge');
  const panel     = document.getElementById('nb-panel');
  const list      = document.getElementById('nb-list');
  const empty     = document.getElementById('nb-empty');
  const readAll   = document.getElementById('nb-read-all');
  const feedUrl   = root.dataset.feed;
  const readUrl   = root.dataset.readAll;
  const csrf      = '{{ csrf_token() }}';

  function setBadge(n) {
    badge.textContent = n > 9 ? '9+' : n;
    badge.classList.toggle('hidden', n < 1);
  }

  function render(items) {
    list.replaceChildren();
    empty.classList.toggle('hidden', items.length > 0);

    items.forEach(function (item) {
      const a = document.createElement('a');
      a.href = item.url;
      a.className = 'flex gap-3 px-4 py-3 hover:bg-[#faf8ff] transition ' + (item.read ? '' : 'bg-[#faf8ff]');

      const icon = document.createElement('span');
      icon.className = 'w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center ' +
                       (item.read ? 'bg-gray-100 text-gray-400' : 'bg-[#3C3489] text-white');
      icon.innerHTML = '<i class="ti ti-message-circle-2 text-base"></i>';

      const text = document.createElement('div');
      text.className = 'min-w-0 flex-1';

      const title = document.createElement('div');
      title.className = 'text-xs font-semibold text-gray-800';
      title.textContent = item.title;

      const body = document.createElement('div');
      body.className = 'text-xs text-gray-500 mt-0.5 break-words';
      body.textContent = item.body;

      const time = document.createElement('div');
      time.className = 'text-[10px] text-gray-400 mt-1';
      time.textContent = item.time || '';

      text.append(title, body, time);
      a.append(icon, text);

      if (!item.read) {
        const dot = document.createElement('span');
        dot.className = 'w-2 h-2 rounded-full bg-[#EF9F27] flex-shrink-0 mt-1.5';
        a.append(dot);
      }

      list.append(a);
    });
  }

  function load() {
    return fetch(feedUrl, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) { if (d) { setBadge(d.unread); render(d.items); } })
      .catch(function () { /* offline or session expired: keep what is shown */ });
  }

  function toggle(show) {
    const open = typeof show === 'boolean' ? show : panel.classList.contains('hidden');
    panel.classList.toggle('hidden', !open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) load();
  }

  btn.addEventListener('click', function (e) { e.stopPropagation(); toggle(); });
  document.addEventListener('click', function (e) { if (!root.contains(e.target)) toggle(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') toggle(false); });

  readAll.addEventListener('click', function () {
    fetch(readUrl, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    }).then(load);
  });

  // Keep the badge fresh: poll, and refresh when the tab comes back into view
  load();
  setInterval(load, 45000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) load(); });
})();
</script>
@endauth
