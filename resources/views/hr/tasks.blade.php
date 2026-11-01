@extends('layouts.app')
@section('title', 'HR — Supervisor Staff')

@section('nav')
@foreach([['hr.dashboard','ti-chart-bar','HR Overview'],['hr.users','ti-users','Users'],['hr.assignments','ti-arrows-exchange','Assignments'],['hr.cycles','ti-calendar','Cycles'],['hr.tasks','ti-clipboard-list','Task Logs']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route, $route.'.*') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')
@php
  $supParam       = $supervisorUser?->id ?? 'none';
  $pendingByCycle = $pendingByCycle ?? collect();

  // $cycles is newest first, so "previous month" is the next item in the list
  $cycleList = $cycles->values();
  $idx       = $cycle ? $cycleList->search(fn ($c) => $c->id === $cycle->id) : false;
  $newer     = ($idx !== false && $idx > 0) ? $cycleList[$idx - 1] : null;
  $older     = ($idx !== false && $idx < $cycleList->count() - 1) ? $cycleList[$idx + 1] : null;

  // ---- Notification bell: staff who have submitted appraisals ----
  // Preferred: controller passes $submittedAppraisals (all submitted appraisals, newest first,
  //            with ->staff and ->cycle loaded, ideally ->submitted_at).
  // Fallback:  uses the appraisals already loaded for this supervisor's team.
  if (isset($submittedAppraisals)) {
      $notifSource = collect($submittedAppraisals)->map(fn ($a) => [
          'appraisal' => $a,
          'staff'     => $a->staff ?? null,
          'cycleName' => $a->cycle->name ?? null,
      ]);
  } else {
      $notifSource = $appraisalsByStaff
          ->filter(fn ($a) => strtolower((string) $a->status) === 'submitted')
          ->map(fn ($a, $staffId) => [
              'appraisal' => $a,
              'staff'     => $team->firstWhere('id', $staffId),
              'cycleName' => $cycle?->name,
          ])->values();
  }

  $notifications = $notifSource->map(function ($row) {
      $a  = $row['appraisal'];
      $st = $row['staff'];
      $at = $a->submitted_at ?? $a->updated_at ?? null;
      return [
          'id'    => $a->id,
          'name'  => $st->name ?? 'Unknown staff',
          'meta'  => trim(($st->department ?? '') . (($st->department ?? null) && ($st->designation ?? null) ? ' · ' : '') . ($st->designation ?? '')),
          'cycle' => $row['cycleName'],
          'ago'   => $at ? \Illuminate\Support\Carbon::parse($at)->diffForHumans() : '',
          'ts'    => $at ? \Illuminate\Support\Carbon::parse($at)->timestamp : 0,
          'url'   => route('hr.appraisal.show', $a),
      ];
  })->sortByDesc('ts')->values();
@endphp

<div class="flex items-center justify-between gap-3 flex-wrap mb-4">
  <div>
    <div class="text-xl font-bold text-gray-800">{{ $supervisorUser?->name ?? 'No supervisor assigned' }}</div>
    <div class="text-sm text-gray-500">
      {{ $cycle?->name ?? 'No cycle' }} · {{ $team->count() }} {{ Str::plural('staff', $team->count()) }} · Select a staff member to open their appraisal
    </div>
  </div>

  <div class="flex items-center gap-2">
    {{-- Notification bell --}}
    <div class="relative"
         x-data="{
           open: false,
           items: @js($notifications),
           lastSeen: 0,
           init() { try { this.lastSeen = parseInt(localStorage.getItem('hr_appraisal_seen') || '0', 10); } catch (e) {} },
           get unread() { return this.items.filter(i => i.ts > this.lastSeen).length },
           isNew(i) { return i.ts > this.lastSeen },
           toggle() { this.open = !this.open },
           markAllRead() {
             const max = this.items.reduce((m, i) => Math.max(m, i.ts), 0);
             this.lastSeen = max || Math.floor(Date.now() / 1000);
             try { localStorage.setItem('hr_appraisal_seen', String(this.lastSeen)); } catch (e) {}
           }
         }"
         @click.outside="open = false"
         @keydown.escape.window="open = false">
      <button type="button" @click="toggle()" aria-label="Appraisal notifications" :aria-expanded="open"
              class="relative w-10 h-10 flex items-center justify-center rounded-lg border border-[#e0daf5] bg-white text-gray-500 hover:border-[#7F77DD] hover:text-[#3C3489] transition">
        <i class="ti ti-bell text-xl"></i>
        <span x-show="unread > 0" x-cloak x-text="unread > 9 ? '9+' : unread"
              class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold flex items-center justify-center"></span>
      </button>

      <div x-show="open" x-cloak x-transition.origin.top.right
           class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white border border-[#e0daf5] rounded-xl shadow-lg z-50 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-[#e0daf5]">
          <div class="text-sm font-semibold text-gray-800">
            Submitted appraisals
            <span class="ml-1 text-[11px] font-normal text-gray-400" x-text="'(' + items.length + ')'"></span>
          </div>
          <button type="button" x-show="unread > 0" @click="markAllRead()" class="text-xs text-[#3C3489] hover:underline">Mark all read</button>
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-[#f1eefb]">
          <template x-for="i in items" :key="i.id">
            <a :href="i.url" class="flex items-start gap-3 px-4 py-3 hover:bg-[#f5f0ff] transition" :class="isNew(i) ? 'bg-[#faf8ff]' : ''">
              <div class="w-9 h-9 rounded-full bg-[#eeedfe] text-[#3C3489] flex items-center justify-center shrink-0">
                <i class="ti ti-file-check text-lg"></i>
              </div>
              <div class="min-w-0 flex-1">
                <div class="text-sm text-gray-800 truncate">
                  <span class="font-semibold" x-text="i.name"></span> submitted an appraisal
                </div>
                <div class="text-xs text-gray-500 truncate" x-show="i.meta" x-text="i.meta"></div>
                <div class="text-[11px] text-gray-400 mt-0.5">
                  <span x-show="i.cycle" x-text="i.cycle"></span>
                  <span x-show="i.cycle && i.ago"> · </span>
                  <span x-text="i.ago"></span>
                </div>
              </div>
              <span x-show="isNew(i)" class="mt-1.5 w-2 h-2 rounded-full bg-[#7F77DD] shrink-0"></span>
            </a>
          </template>

          <div x-show="items.length === 0" class="px-4 py-8 text-center text-sm text-gray-400">
            <i class="ti ti-bell-off text-2xl block mb-1"></i>
            No submitted appraisals yet.
          </div>
        </div>
      </div>
    </div>

    <a href="{{ route('hr.tasks', ['cycle' => $cycle?->id]) }}" class="inline-flex items-center gap-2 border border-[#e0daf5] text-gray-500 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
      <i class="ti ti-arrow-left"></i> Back
    </a>
  </div>
</div>

{{-- Month switcher --}}
@if($cycleList->count() > 0)
<div class="flex items-center gap-2 mb-5">
  @if($older)
  <a href="{{ route('hr.tasks.supervisor', [$supParam, 'cycle' => $older->id]) }}" title="Previous: {{ $older->name }}"
     class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border border-[#e0daf5] bg-white text-gray-500 hover:border-[#7F77DD] hover:text-[#3C3489] transition">
    <i class="ti ti-chevron-left"></i>
  </a>
  @else
  <span class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border border-[#e0daf5] bg-white text-gray-300"><i class="ti ti-chevron-left"></i></span>
  @endif

  <div class="flex items-center gap-2 overflow-x-auto py-1 flex-1">
    @foreach($cycleList as $c)
    @php
      $isCurrent = $cycle && $cycle->id === $c->id;
      $pending   = $pendingByCycle->get($c->id, 0);
    @endphp
    <a href="{{ route('hr.tasks.supervisor', [$supParam, 'cycle' => $c->id]) }}"
       @if($isCurrent) aria-current="page" @endif
       class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm border transition {{ $isCurrent ? 'bg-[#3C3489] text-white border-[#3C3489] font-medium' : 'bg-white text-gray-600 border-[#e0daf5] hover:border-[#7F77DD] hover:text-[#3C3489]' }}">
      {{ $c->name }}
      @if($c->is_active)<span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $isCurrent ? 'bg-white/20 text-white' : 'bg-green-100 text-green-700' }}">active</span>@endif
      @if($pending > 0)<span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $isCurrent ? 'bg-amber-300 text-amber-900' : 'bg-amber-100 text-amber-700' }}" title="{{ $pending }} awaiting review">{{ $pending }}</span>@endif
    </a>
    @endforeach
  </div>

  @if($newer)
  <a href="{{ route('hr.tasks.supervisor', [$supParam, 'cycle' => $newer->id]) }}" title="Next: {{ $newer->name }}"
     class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border border-[#e0daf5] bg-white text-gray-500 hover:border-[#7F77DD] hover:text-[#3C3489] transition">
    <i class="ti ti-chevron-right"></i>
  </a>
  @else
  <span class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border border-[#e0daf5] bg-white text-gray-300"><i class="ti ti-chevron-right"></i></span>
  @endif
</div>
@endif

@if(!$cycle)
<div class="bg-white border border-[#e0daf5] rounded-xl p-8 text-center text-gray-400">There is no appraisal cycle yet.</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
@forelse($team as $s)
@php
  $appraisal = $appraisalsByStaff->get($s->id);
  $logs      = $tasks->get($s->id, collect());
  $initials  = collect(explode(' ', trim($s->name)))->filter()->take(2)->map(fn ($p) => strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp
<a href="{{ $appraisal ? route('hr.appraisal.show', $appraisal) : '#' }}"
   @if(!$appraisal) aria-disabled="true" @endif
   class="block bg-white border border-[#e0daf5] rounded-xl p-5 transition {{ $appraisal ? 'hover:border-[#7F77DD] hover:shadow-sm' : 'opacity-70 pointer-events-none' }}">
  <div class="flex items-center justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
      <div class="w-11 h-11 rounded-full bg-[#3C3489] text-white flex items-center justify-center text-sm font-semibold shrink-0">{{ $initials }}</div>
      <div class="min-w-0">
        <div class="font-semibold text-gray-800 text-sm truncate">{{ $s->name }}</div>
        <div class="text-xs text-gray-500 truncate">{{ $s->department ?? '—' }} · {{ $s->designation ?? '—' }}</div>
      </div>
    </div>
    @if($appraisal)<i class="ti ti-chevron-right text-gray-300 text-lg"></i>@endif
  </div>
  <div class="flex items-center gap-2 flex-wrap mt-4">
    @if($appraisal)
      <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">{{ ucfirst($appraisal->status) }}</span>
      <span class="text-[11px] bg-[#eeedfe] text-[#3C3489] px-2 py-0.5 rounded-full">{{ $logs->count() }} {{ Str::plural('log', $logs->count()) }}</span>
      @if(($n = $logs->where('status', 'awaiting')->count()) > 0)
        <span class="text-[11px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ $n }} awaiting</span>
      @endif
      @if($appraisal->staff_performance_overall !== null)
        <span class="text-[11px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Score {{ $appraisal->staff_performance_overall }} · {{ $appraisal->staff_performance_grade }}</span>
      @endif
    @else
      <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">No appraisal this cycle</span>
    @endif
  </div>
</a>
@empty
<div class="md:col-span-2 bg-white border border-[#e0daf5] rounded-xl p-8 text-center text-gray-400">No staff under this supervisor.</div>
@endforelse
</div>
@endif
@endsection
