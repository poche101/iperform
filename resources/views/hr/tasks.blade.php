@extends('layouts.app')
@section('title', 'HR — Task Logs')

@section('nav')
@foreach([['hr.dashboard','ti-chart-bar','HR Overview'],['hr.users','ti-users','Users'],['hr.assignments','ti-arrows-exchange','Assignments'],['hr.cycles','ti-calendar','Cycles'],['hr.tasks','ti-clipboard-list','Task Logs']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route, $route.'.*') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')

<div class="flex items-center justify-between gap-3 flex-wrap mb-5">
  <div>
    <div class="text-xl font-bold text-gray-800">Task Logs</div>
    <div class="text-sm text-gray-500">{{ $cycle?->name ?? 'No cycle' }} · Select a supervisor to view their staff and task logs</div>
  </div>

  @if($cycles->count() > 1)
  <form method="GET" action="{{ route('hr.tasks') }}">
    <select name="cycle" onchange="this.form.submit()" class="border border-[#e0daf5] rounded-lg px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:border-[#7F77DD]">
      @foreach($cycles as $c)
      <option value="{{ $c->id }}" @selected($cycle && $cycle->id === $c->id)>{{ $c->name }}{{ $c->is_active ? ' (active)' : '' }}</option>
      @endforeach
    </select>
  </form>
  @endif
</div>

@if(!$cycle)
<div class="bg-white border border-[#e0daf5] rounded-xl p-8 text-center text-gray-400">
  <i class="ti ti-calendar-off text-3xl mb-2 block"></i>
  There is no appraisal cycle, so no task logs are available to display.
</div>
@else

@php
  $allLogs       = $tasks->flatten(1);
  $totalStaff    = $tasks->count();
  $totalLogs     = $allLogs->count();
  $totalAwaiting = $allLogs->where('status', 'awaiting')->count();
  $totalGraded   = $allLogs->where('status', 'graded')->count();

  // Group every staff member by supervisor (0 = none), alphabetical, "none" last
  $groups = $allStaff
      ->groupBy(fn ($s) => $s->supervisor_id ?? 0)
      ->sortBy(fn ($members, $supId) => $supId == 0 ? 'zzzz' : strtolower($members->first()->supervisor?->name ?? ''));
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Staff with logs</div>
    <div class="text-2xl font-bold text-[#3C3489]">{{ $totalStaff }}</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Total logs</div>
    <div class="text-2xl font-bold text-[#3C3489]">{{ $totalLogs }}</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Awaiting review</div>
    <div class="text-2xl font-bold text-amber-600">{{ $totalAwaiting }}</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Graded</div>
    <div class="text-2xl font-bold text-green-600">{{ $totalGraded }}</div>
  </div>
</div>

<div class="bg-white border border-[#e0daf5] rounded-xl p-4 mb-5 flex items-center gap-3">
  <i class="ti ti-search text-gray-400"></i>
  <input id="sup-search" type="text" oninput="filterSupervisors()" placeholder="Search supervisors..."
         class="w-full text-sm text-gray-700 focus:outline-none">
</div>

<div id="sup-list" class="grid grid-cols-1 md:grid-cols-2 gap-4">
@forelse($groups as $supId => $members)
@php
  $supervisor = $supId == 0 ? null : $members->first()->supervisor;
  $supName    = $supervisor?->name ?? 'No supervisor assigned';
  $groupLogs  = $members->flatMap(fn ($m) => $tasks->get($m->id, collect()));
  $initials   = collect(explode(' ', trim($supName)))->filter()->take(2)->map(fn ($p) => strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp
<a href="{{ route('hr.tasks.supervisor', ['supervisor' => $supId == 0 ? 'none' : $supId, 'cycle' => $cycle->id]) }}"
   data-name="{{ strtolower($supName) }}"
   class="sup-card block bg-white border border-[#e0daf5] rounded-xl p-5 hover:border-[#7F77DD] hover:shadow-sm transition">
  <div class="flex items-center justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
      <div class="w-11 h-11 rounded-full {{ $supId == 0 ? 'bg-gray-200 text-gray-500' : 'bg-[#3C3489] text-white' }} flex items-center justify-center text-sm font-semibold shrink-0">
        {{ $supId == 0 ? '?' : $initials }}
      </div>
      <div class="min-w-0">
        <div class="font-semibold text-gray-800 text-sm truncate">{{ $supName }}</div>
        <div class="text-xs text-gray-500 truncate">
          {{ $supervisor ? (($supervisor->department ?? '—') . ' · ' . ($supervisor->designation ?? '—')) : 'Staff without a supervisor' }}
        </div>
      </div>
    </div>
    <i class="ti ti-chevron-right text-gray-300 text-lg"></i>
  </div>
  <div class="flex items-center gap-2 flex-wrap mt-4">
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">{{ $members->count() }} {{ Str::plural('staff', $members->count()) }}</span>
    <span class="text-[11px] bg-[#eeedfe] text-[#3C3489] px-2 py-0.5 rounded-full">{{ $groupLogs->count() }} {{ Str::plural('log', $groupLogs->count()) }}</span>
    @if(($n = $groupLogs->where('status', 'awaiting')->count()) > 0)
    <span class="text-[11px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ $n }} awaiting</span>
    @endif
    @if(($n = $groupLogs->where('status', 'graded')->count()) > 0)
    <span class="text-[11px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full">{{ $n }} graded</span>
    @endif
  </div>
</a>
@empty
<div class="md:col-span-2 bg-white border border-[#e0daf5] rounded-xl p-8 text-center text-gray-400">
  <i class="ti ti-users-off text-3xl mb-2 block"></i>
  No staff found.
</div>
@endforelse
</div>

@endif

@endsection

@section('scripts')
<script>
function filterSupervisors() {
  const q = document.getElementById('sup-search').value.trim().toLowerCase();
  document.querySelectorAll('.sup-card').forEach(card => {
    card.style.display = card.dataset.name.includes(q) ? '' : 'none';
  });
}
</script>
@endsection
