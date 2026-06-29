@extends('layouts.app')
@section('title', 'Supervisors')

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
<div class="text-2xl font-bold text-gray-900 mb-1">Supervisors</div>
<div class="text-sm text-gray-500 mb-5">Team reviewers and the staff assigned to them.</div>

<div class="bg-white border border-[#e0daf5] rounded-xl p-5">
  <div class="flex items-center gap-3 mb-4 pb-4 border-b border-[#f0edf8]">
    <div class="w-10 h-10 bg-[#3C3489] rounded-full flex items-center justify-center text-white text-sm font-bold">
      {{ strtoupper(substr($user->name,0,1)) }}{{ strtoupper(substr(explode(' ',$user->name)[1]??'',0,1)) }}
    </div>
    <div>
      <div class="font-semibold text-gray-900">{{ $user->name }}</div>
      <div class="text-xs text-gray-400">{{ $user->title ?? 'Supervisor' }}</div>
    </div>
    <span class="ml-auto text-xs bg-[#eeedfe] text-[#3C3489] font-medium px-2.5 py-1 rounded-full">
      {{ $staff->count() }} on team
    </span>
  </div>

  @forelse($staff as $s)
  @php $a = $appraisals[$s->id] ?? null; @endphp
  <a href="{{ $a ? route('supervisor.appraisal.show', $a) : '#' }}"
     class="flex items-center gap-3 py-2.5 border-b border-[#f0edf8] last:border-0 hover:bg-[#f5f0ff] -mx-2 px-2 rounded-lg transition">
    <div class="w-8 h-8 bg-[#eeedfe] rounded-full flex items-center justify-center text-[11px] font-bold text-[#3C3489]">
      {{ strtoupper(substr($s->name,0,1)) }}{{ strtoupper(substr(explode(' ',$s->name)[1]??'',0,1)) }}
    </div>
    <div class="flex-1 min-w-0">
      <div class="text-sm font-medium text-gray-900">{{ $s->name }}</div>
      <div class="text-xs text-gray-400">{{ $s->department }}</div>
    </div>
    <span class="text-xs font-medium px-2 py-0.5 rounded-full
      {{ ($a?->status ?? 'drafting')==='approved' ? 'bg-green-100 text-green-700'
        : (($a?->status ?? '')==='with_hr' ? 'bg-orange-100 text-orange-700'
        : (($a?->status ?? '')==='submitted' ? 'bg-[#eeedfe] text-[#3C3489]'
        : 'bg-gray-100 text-gray-500')) }}">
      {{ ucfirst(str_replace('_',' ', $a?->status ?? 'drafting')) }}
    </span>
  </a>
  @empty
  <div class="text-sm text-gray-400 italic text-center py-4">No staff assigned to you yet.</div>
  @endforelse
</div>
@endsection
