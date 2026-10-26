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
@endphp

<div class="flex items-center justify-between gap-3 flex-wrap mb-4">
  <div>
    <div class="text-xl font-bold text-gray-800">{{ $supervisorUser?->name ?? 'No supervisor assigned' }}</div>
    <div class="text-sm text-gray-500">
      {{ $cycle?->name ?? 'No cycle' }} · {{ $team->count() }} {{ Str::plural('staff', $team->count()) }} · Select a staff member to open their appraisal
    </div>
  </div>
  <a href="{{ route('hr.tasks', ['cycle' => $cycle?->id]) }}" class="inline-flex items-center gap-2 border border-[#e0daf5] text-gray-500 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
    <i class="ti ti-arrow-left"></i> Back
  </a>
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
