<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- PWA Meta Tags --}}
<meta name="theme-color" content="#3C3489">
<meta name="application-name" content="iPerform">
<meta name="description" content="A modern staff performance appraisal platform for church organisations.">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="iPerform">
<meta name="msapplication-TileColor" content="#3C3489">
<meta name="msapplication-TileImage" content="/icons/icon-144.png">

{{-- PWA Manifest --}}
<link rel="manifest" href="/manifest.json">

{{-- Apple Touch Icons --}}
<link rel="apple-touch-icon" href="/icons/icon-152.png">
<link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192.png">

{{-- Favicon --}}
<link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-72.png">

<title>iPerform — @yield('title', 'Staff Appraisals')</title>

<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#3C3489',
                'primary-light': '#534AB7',
                'primary-pale': '#eeedfe',
                'primary-border': '#AFA9EC',
                gold: '#EF9F27',
                'gold-pale': '#faeeda',
            }
        }
    }
}
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
body { font-family: system-ui, -apple-system, sans-serif; padding-top: env(safe-area-inset-top); }
.score-input { width: 70px; text-align: center; }
/* PWA install banner */
#pwa-banner { display: none; }
#pwa-banner.visible { display: flex; }
/* Safe area structures */
.topbar {
    padding-left: max(1rem, env(safe-area-inset-left));
    padding-right: max(1rem, env(safe-area-inset-right));
}
.mobile-nav-active { overflow: hidden; }
</style>
</head>
<body class="bg-[#f5f0ff] min-h-screen flex flex-col antialiased">

{{-- PWA Install Banner --}}
<div id="pwa-banner" class="bg-[#3C3489] text-white px-4 py-3 flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-sm z-50 sticky top-0 border-b border-white/10 topbar">
    <div class="flex items-center gap-2.5">
        <img src="/icons/icon-72.png" class="w-7 h-7 rounded-lg flex-shrink-0" alt="iPerform">
        <span>Install <strong>iPerform</strong> for offline access</span>
    </div>
    <div class="flex items-center gap-2 ml-9 sm:ml-0 flex-shrink-0">
        <button id="pwa-install-btn"
            class="bg-[#EF9F27] text-amber-900 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-400 transition">
            Install
        </button>
        <button id="pwa-dismiss-btn"
            class="text-white/60 hover:text-white text-xs px-2 py-1.5 transition">
            Later
        </button>
    </div>
</div>

{{-- Topbar --}}
<header class="topbar bg-[#3C3489] text-white h-14 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-40 shadow-md">
    <div class="flex items-center gap-3 min-w-0">
        {{-- Mobile Hamburger Menu Trigger --}}
        <button id="mobile-menu-toggle" class="lg:hidden text-white p-1 rounded-lg hover:bg-white/10 transition flex-shrink-0" aria-label="Toggle Navigation">
            <i class="ti ti-menu-2 text-xl block"></i>
        </button>

        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 bg-[#7F77DD] rounded-full flex items-center justify-center flex-shrink-0">
                <i class="ti ti-arrow-up text-base"></i>
            </div>
            <div class="truncate">
                <div class="text-base font-bold tracking-tight leading-none">iperform</div>
                <div class="text-[9px] opacity-60 uppercase tracking-widest mt-0.5 truncate hidden sm:block">Staff Appraisals</div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2.5 sm:gap-3 flex-shrink-0">
        {{-- PWA install button (topbar) --}}
        <button id="pwa-topbar-btn"
            class="hidden items-center gap-1.5 bg-white/10 border border-white/20 text-white text-xs px-2.5 py-1.5 rounded-lg hover:bg-white/20 transition">
            <i class="ti ti-download text-sm"></i> <span class="hidden sm:inline">Install app</span>
        </button>
        <div class="w-8 h-8 bg-[#7F77DD] rounded-full flex items-center justify-center text-xs font-bold cursor-default flex-shrink-0">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1)) }}
        </div>
        <form method="POST" action="{{ route('logout') }}" class="inline-flex">
            @csrf
            <button type="submit" class="text-xs bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg px-2.5 py-1.5 sm:border-transparent sm:bg-transparent sm:px-0 sm:py-0 opacity-80 sm:opacity-70 sm:hover:opacity-100 transition">
                <span class="hidden sm:inline">Sign out</span>
                <i class="ti ti-logout text-sm sm:hidden block"></i>
            </button>
        </form>
    </div>
</header>

<div class="flex flex-1 relative">
    {{-- Desktop Sidebar --}}
    <aside id="desktop-sidebar" class="hidden lg:flex w-56 bg-white border-r border-[#e0daf5] flex-col sticky top-14 h-[calc(100vh-56px)] flex-shrink-0">
        <nav class="flex-1 py-3">
            @yield('nav')
        </nav>
        <div class="p-4 border-t border-[#e0daf5] bg-gray-50/50">
            @if(isset($cycle) && $cycle)
            <div class="bg-[#faeeda] border border-amber-100 rounded-xl p-3 mb-3">
                <div class="text-[10px] text-amber-700 font-medium uppercase tracking-wider flex items-center gap-1 mb-0.5">
                    <i class="ti ti-clock text-xs"></i> Deadline
                </div>
                <div class="text-xl font-bold text-[#3C3489]">
                    {{ max(0, now()->diffInDays(\Carbon\Carbon::parse($cycle->deadline), false)) }}d
                </div>
                <div class="text-[11px] text-gray-500">until {{ $cycle->name }} lock</div>
            </div>
            @endif
            <div class="text-xs text-gray-500 min-w-0">
                <div class="font-semibold text-gray-800 truncate">{{ auth()->user()->name }}</div>
                <div class="uppercase tracking-wider text-[10px] mt-0.5 opacity-70">{{ auth()->user()->role }}</div>
            </div>
        </div>
    </aside>

    {{-- Mobile Navigation Drawer Backdrop --}}
    <div id="mobile-sidebar-backdrop" class="fixed inset-0 bg-gray-900/40 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

    {{-- Mobile Navigation Drawer Container --}}
    <aside id="mobile-sidebar" class="fixed inset-y-0 left-0 w-64 bg-white z-50 flex flex-col transform -translate-x-full transition-transform duration-300 ease-in-out lg:hidden shadow-2xl">
        <div class="h-14 bg-[#3C3489] text-white flex items-center justify-between px-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-[#7F77DD] rounded-full flex items-center justify-center">
                    <i class="ti ti-arrow-up text-sm"></i>
                </div>
                <span class="font-bold text-sm tracking-tight">iperform menu</span>
            </div>
            <button id="mobile-menu-close" class="text-white/80 hover:text-white p-1" aria-label="Close Navigation">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>
        <nav class="flex-1 py-3 overflow-y-auto" onclick="toggleMobileMenu(false)">
            @yield('nav')
        </nav>
        <div class="p-4 border-t border-[#e0daf5] bg-gray-50/50 pb-safe">
            @if(isset($cycle) && $cycle)
            <div class="bg-[#faeeda] border border-amber-100 rounded-xl p-3 mb-3">
                <div class="text-[10px] text-amber-700 font-medium uppercase tracking-wider flex items-center gap-1 mb-0.5">
                    <i class="ti ti-clock text-xs"></i> Deadline
                </div>
                <div class="text-lg font-bold text-[#3C3489]">
                    {{ max(0, now()->diffInDays(\Carbon\Carbon::parse($cycle->deadline), false)) }}d
                </div>
                <div class="text-[11px] text-gray-500">until {{ $cycle->name }} lock</div>
            </div>
            @endif
            <div class="text-xs text-gray-500">
                <div class="font-semibold text-gray-700 truncate">{{ auth()->user()->name }}</div>
                <div class="uppercase tracking-wider text-[10px] mt-0.5 opacity-70">{{ auth()->user()->role }}</div>
            </div>
        </div>
    </aside>

    {{-- Main content space --}}
    <main class="flex-1 p-4 sm:p-6 overflow-x-hidden min-w-0 w-full">
        @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2.5 text-sm shadow-sm max-w-4xl mx-auto">
            <i class="ti ti-circle-check text-lg flex-shrink-0"></i>
            <span class="break-words">{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm shadow-sm max-w-4xl mx-auto space-y-1">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-1.5 break-words">
                    <i class="ti ti-alert-circle text-base flex-shrink-0"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
        @endif

        @yield('content')
    </main>
</div>

{{-- PWA Service Worker + Install Logic + Responsiveness Listeners --}}
<script>
window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Mobile Menu Navigation Interactions
const menuToggle = document.getElementById('mobile-menu-toggle');
const menuClose = document.getElementById('mobile-menu-close');
const mobileSidebar = document.getElementById('mobile-sidebar');
const sidebarBackdrop = document.getElementById('mobile-sidebar-backdrop');

function toggleMobileMenu(open) {
    if (open) {
        document.body.classList.add('mobile-nav-active');
        sidebarBackdrop.classList.remove('hidden');
        setTimeout(() => {
            sidebarBackdrop.classList.remove('opacity-0');
            sidebarBackdrop.classList.add('opacity-100');
            mobileSidebar.classList.remove('-translate-x-full');
        }, 10);
    } else {
        document.body.classList.remove('mobile-nav-active');
        mobileSidebar.classList.add('-translate-x-full');
        sidebarBackdrop.classList.remove('opacity-100');
        sidebarBackdrop.classList.add('opacity-0');
        setTimeout(() => {
            sidebarBackdrop.classList.add('hidden');
        }, 300);
    }
}

menuToggle?.addEventListener('click', () => toggleMobileMenu(true));
menuClose?.addEventListener('click', () => toggleMobileMenu(false));
sidebarBackdrop?.addEventListener('click', () => toggleMobileMenu(false));

(function() {
    // Register service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('[iPerform] SW registered:', reg.scope))
                .catch(err => console.log('[iPerform] SW failed:', err));
        });
    }

    // PWA Install prompt
    let deferredPrompt = null;
    const banner = document.getElementById('pwa-banner');
    const topbarBtn = document.getElementById('pwa-topbar-btn');
    const installBtn = document.getElementById('pwa-install-btn');
    const dismissBtn = document.getElementById('pwa-dismiss-btn');

    // Show install UI when browser fires beforeinstallprompt
    window.addEventListener('beforeinstallprompt', e => {
        e.preventDefault();
        deferredPrompt = e;

        // Show banner if not dismissed this session
        if (!sessionStorage.getItem('pwa-dismissed')) {
            banner.classList.add('visible');
        }

        // Always show topbar button
        topbarBtn.classList.remove('hidden');
        topbarBtn.classList.add('inline-flex');
    });

    async function triggerInstall() {
        if (!deferredPrompt) return;
        banner.classList.remove('visible');
        topbarBtn.classList.add('hidden');
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log('[iPerform] PWA install outcome:', outcome);
        deferredPrompt = null;
    }

    installBtn?.addEventListener('click', triggerInstall);
    topbarBtn?.addEventListener('click', triggerInstall);

    dismissBtn?.addEventListener('click', () => {
        banner.classList.remove('visible');
        sessionStorage.setItem('pwa-dismissed', '1');
    });

    // Hide install UI once installed
    window.addEventListener('appinstalled', () => {
        banner.classList.remove('visible');
        topbarBtn.classList.add('hidden');
        deferredPrompt = null;
        console.log('[iPerform] App installed!');
    });
})();
</script>

@yield('scripts')
</body>
</html>
