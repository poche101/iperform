@extends('layouts.app')
@section('title', 'Tasks')

@section('nav')
  @include('supervisor.partials.nav')
@endsection

@section('content')
<div class="flex items-start justify-between flex-wrap gap-3 mb-5">
  <div>
    <div class="text-2xl font-bold text-gray-900 mb-1">Tasks</div>
    <div class="text-sm text-gray-500">Select a staff member to review their tasks, grouped into KRA, Routine and Ideas & Outstanding Innovation.</div>
  </div>

  {{-- Cycle selector: switching the active cycle never hides earlier months --}}
  @if($cycles->count())
  <form method="GET" action="{{ route('supervisor.tasks') }}" class="flex items-center gap-2">
    <label class="text-xs font-medium text-gray-500">Cycle</label>
    <select name="cycle" onchange="this.form.submit()"
            class="px-3 py-2 border border-[#e0daf5] rounded-lg text-sm bg-white focus:outline-none focus:border-[#7F77DD]">
      @foreach($cycles as $c)
        @php $pending = $pendingByCycle[$c->id] ?? 0; @endphp
        <option value="{{ $c->id }}" {{ $cycle && $cycle->id === $c->id ? 'selected' : '' }}>
          {{ $c->name }}{{ $c->is_active ? ' (active)' : '' }}{{ $pending ? " — {$pending} awaiting" : '' }}
        </option>
      @endforeach
    </select>
  </form>
  @endif
</div>

@if($cycle && !$cycle->is_active)
<div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-700">
  <i class="ti ti-history text-lg"></i>
  You are viewing {{ $cycle->name }}, which is not the active cycle. You can still grade its tasks.
</div>
@endif

<div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3 flex items-center justify-between">
  Your team
  <span class="bg-gray-100 text-gray-500 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $team->count() }}</span>
</div>

@forelse($team as $member)
  @php
    $stats    = $statsByStaff[$member->id] ?? null;
    $total    = (int) ($stats->total ?? 0);
    $awaiting = (int) ($stats->awaiting ?? 0);
    $graded   = (int) ($stats->graded ?? 0);
  @endphp
  <a href="{{ route('supervisor.tasks.staff', ['staff' => $member->id, 'cycle' => $cycle?->id]) }}"
     class="flex items-center gap-3 bg-white border border-[#e0daf5] rounded-xl p-4 mb-3 hover:border-[#7F77DD] hover:shadow-sm transition">
    <div class="w-10 h-10 bg-[#eeedfe] rounded-full flex items-center justify-center text-xs font-bold text-[#3C3489] flex-shrink-0">
      {{ strtoupper(substr($member->name,0,1)) }}{{ strtoupper(substr(explode(' ',$member->name)[1]??'',0,1)) }}
    </div>
    <div class="flex-1 min-w-0">
      <div class="font-medium text-gray-900 text-sm truncate">{{ $member->name }}</div>
      <div class="text-xs text-gray-400 truncate">{{ $member->department }}</div>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
      @if($awaiting)
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $awaiting }} awaiting</span>
      @endif
      @if($graded)
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700">{{ $graded }} graded</span>
      @endif
      @if(!$total)
        <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-400">No tasks</span>
      @endif
      <i class="ti ti-chevron-right text-gray-300"></i>
    </div>
  </a>
@empty
  <div class="bg-white border border-[#e0daf5] rounded-xl p-8 text-center">
    <i class="ti ti-users text-4xl text-gray-300 block mb-2"></i>
    <div class="text-sm text-gray-400">No staff are assigned to you yet.</div>
  </div>
@endforelse
@endsection
