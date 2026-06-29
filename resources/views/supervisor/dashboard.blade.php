@extends('layouts.app')
@section('title', 'Supervisor Dashboard')

@section('nav')
<a href="{{ route('supervisor.dashboard') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('supervisor.dashboard') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-home text-lg w-5"></i> Home
</a>
<a href="{{ route('supervisor.pipeline') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('supervisor.pipeline') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-list text-lg w-5"></i> Pipeline
</a>
<a href="{{ route('supervisor.supervisors') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('supervisor.supervisors') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-users text-lg w-5"></i> Supervisors
</a>
<a href="{{ route('supervisor.tasks') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('supervisor.tasks') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-clipboard-check text-lg w-5"></i> Tasks
</a>
@endsection

@section('content')
<div class="text-2xl font-bold text-gray-900 mb-1">Supervisor dashboard</div>
<div class="text-sm text-gray-500 mb-5">Live pipeline for the {{ $cycle?->name ?? '—' }} cycle.</div>

<div class="grid grid-cols-3 gap-3 mb-5">
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="w-9 h-9 bg-[#eeedfe] rounded-lg flex items-center justify-center mb-2"><i class="ti ti-users text-[#3C3489] text-lg"></i></div>
    <div class="text-2xl font-bold">{{ $staff->count() }}</div>
    <div class="text-xs text-gray-400">My team</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="w-9 h-9 bg-[#eeedfe] rounded-lg flex items-center justify-center mb-2"><i class="ti ti-clock text-[#3C3489] text-lg"></i></div>
    <div class="text-2xl font-bold">{{ $grouped['submitted']->count() }}</div>
    <div class="text-xs text-gray-400">Awaiting review</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="w-9 h-9 bg-[#eeedfe] rounded-lg flex items-center justify-center mb-2"><i class="ti ti-check text-[#3C3489] text-lg"></i></div>
    <div class="text-2xl font-bold">{{ $grouped['with_hr']->count() + $grouped['approved']->count() }}</div>
    <div class="text-xs text-gray-400">Forwarded / done</div>
  </div>
</div>

<div class="bg-white border border-[#e0daf5] rounded-xl p-5">
  <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Appraisal pipeline</div>
  @foreach(['submitted'=>'Awaiting review','with_hr'=>'With HR','approved'=>'Approved','drafting'=>'Drafting'] as $status => $label)
  <div class="mb-4">
    <div class="flex items-center justify-between text-[11px] text-gray-400 font-medium uppercase tracking-wider mb-2">
      {{ $label }}
      <span class="bg-[#eeedfe] text-[#3C3489] text-[11px] font-semibold px-2 py-0.5 rounded-full">{{ $grouped[$status]->count() }}</span>
    </div>
    @forelse($grouped[$status] as $s)
    @php $apr = $appraisals[$s->id] ?? null; @endphp
    <a href="{{ $apr ? route('supervisor.appraisal.show', $apr) : '#' }}"
      class="flex items-center gap-3 bg-white border border-[#e0daf5] rounded-lg px-3 py-2.5 mb-1.5 hover:border-[#7F77DD] hover:bg-[#faf8ff] transition">
      <div class="w-9 h-9 bg-[#eeedfe] rounded-full flex items-center justify-center text-xs font-bold text-[#3C3489]">
        {{ strtoupper(substr($s->name,0,1)) }}{{ strtoupper(substr(explode(' ',$s->name)[1]??'',0,1)) }}
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-sm font-medium text-gray-900 truncate">{{ $s->name }}</div>
        <div class="text-xs text-gray-400">{{ $s->department }}</div>
      </div>
      <i class="ti ti-chevron-right text-gray-400"></i>
    </a>
    @empty
    <div class="text-xs text-gray-400 italic py-1">None</div>
    @endforelse
  </div>
  @endforeach
</div>
@endsection
