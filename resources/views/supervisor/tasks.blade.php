@extends('layouts.app')
@section('title', 'Review Tasks')

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
<div class="text-2xl font-bold text-gray-900 mb-1">Review tasks</div>
<div class="text-sm text-gray-500 mb-5">Grade submitted work and leave feedback your team will see immediately.</div>

{{-- Awaiting review --}}
<div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3 flex items-center justify-between">
  Awaiting review
  <span class="bg-amber-100 text-amber-700 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $awaiting->count() }}</span>
</div>

@forelse($awaiting as $task)
<div class="bg-white border border-[#e0daf5] rounded-xl p-4 mb-3">
  <div class="flex items-start gap-3 mb-3">
    <div class="w-9 h-9 bg-[#eeedfe] rounded-full flex items-center justify-center text-[11px] font-bold text-[#3C3489] flex-shrink-0">
      {{ strtoupper(substr($task->staff->name,0,1)) }}{{ strtoupper(substr(explode(' ',$task->staff->name)[1]??'',0,1)) }}
    </div>
    <div class="flex-1 min-w-0">
      <div class="font-medium text-gray-900 text-sm">{{ $task->title }}</div>
      <div class="text-xs text-gray-400">{{ $task->staff->name }} · {{ $task->staff->department }}</div>
      <div class="flex flex-wrap gap-2 mt-1.5">
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Awaiting review</span>
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
          {{ $task->category==='KRA'?'bg-[#eeedfe] text-[#3C3489]':($task->category==='Innovation'?'bg-amber-100 text-amber-700':'bg-green-100 text-green-700') }}">
          {{ $task->category }}
        </span>
        @if($task->self_score !== null)
        <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Staff self-score: {{ $task->self_score }}/10</span>
        @endif
        @if($task->completion_percentage > 0)
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
          {{ $task->completion_percentage>=80?'bg-green-100 text-green-700':($task->completion_percentage>=50?'bg-amber-100 text-amber-700':'bg-red-100 text-red-600') }}">
          {{ $task->completion_percentage }}% done
        </span>
        @endif
      </div>
    </div>
    <div class="text-[11px] text-gray-400 whitespace-nowrap">{{ $task->date->format('d M Y') }}</div>
  </div>

  @if($task->details)
  <div class="text-xs text-gray-600 mb-3 pl-12">{{ $task->details }}</div>
  @endif

  @if($task->target)
  <div class="pl-12 mb-3 text-xs">
    <span class="font-medium text-amber-700">Target:</span>
    <span class="text-gray-600">{{ $task->target }}</span>
  </div>
  @endif

  {{-- Performance Challenges Segment --}}
  @if($task->challenge_identified || $task->challenge_impact)
  <div class="pl-12 mb-4">
    <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 text-xs max-w-2xl flex flex-col gap-1.5">
      <div class="text-[10px] uppercase font-bold text-[#534AB7] tracking-wider flex items-center gap-1">
        <i class="ti ti-alert-triangle"></i> Logged Performance Constraints
      </div>
      @if($task->challenge_identified)
        <div class="text-gray-700"><span class="font-medium text-red-600">Challenge:</span> {{ $task->challenge_identified }}</div>
      @endif
      @if($task->challenge_impact)
        <div class="text-gray-600"><span class="font-medium text-gray-700">Impact Assessment:</span> {{ $task->challenge_impact }}</div>
      @endif
    </div>
  </div>
  @endif

  {{-- Completion bar --}}
  @if($task->completion_percentage > 0)
  <div class="pl-12 mb-3">
    <div class="flex items-center gap-2">
      <div class="flex-1 bg-gray-100 rounded-full h-1.5">
        <div class="h-1.5 rounded-full {{ $task->completion_percentage>=80?'bg-green-500':($task->completion_percentage>=50?'bg-amber-400':'bg-red-400') }}"
          style="width:{{ $task->completion_percentage }}%"></div>
      </div>
      <span class="text-xs font-medium text-gray-500">{{ $task->completion_percentage }}%</span>
    </div>
  </div>
  @endif

  {{-- Grade form --}}
  <form method="POST" action="{{ route('supervisor.tasks.grade', $task) }}" class="pl-12 border-t border-[#f0edf8] pt-3">
    @csrf
    <div class="mb-2">
      <label class="block text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">
        Grade · <span class="score-display-{{ $task->id }}">{{ old('supervisor_score', 8) }}</span>/10
      </label>
      <input type="range" name="supervisor_score" min="0" max="10" step="1" value="{{ old('supervisor_score', 8) }}"
        oninput="document.querySelector('.score-display-{{ $task->id }}').textContent=this.value"
        class="w-full h-1.5 bg-[#e0daf5] rounded-full appearance-none cursor-pointer accent-[#3C3489]">
      <div class="flex justify-between text-[10px] text-gray-300 mt-0.5">
        @for($i=0;$i<=10;$i++)<span>{{ $i }}</span>@endfor
      </div>
    </div>
    <div class="mb-3">
      <label class="block text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Comment</label>
      <textarea name="supervisor_comment" rows="2"
        class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] resize-none"
        placeholder="What worked well? What's the next step?">{{ old('supervisor_comment') }}</textarea>
    </div>
    <button type="submit"
      class="inline-flex items-center gap-2 bg-[#3C3489] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#26215C] transition">
      <i class="ti ti-send"></i> Submit feedback
    </button>
  </form>
</div>
@empty
<div class="bg-white border border-[#e0daf5] rounded-xl p-8 text-center mb-5">
  <i class="ti ti-circle-check text-4xl text-green-300 block mb-2"></i>
  <div class="text-sm text-gray-400">All caught up — no tasks awaiting review.</div>
</div>
@endforelse

{{-- Recently graded --}}
@if($recentlyGraded->count())
<div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3 mt-5">Recently graded</div>
@foreach($recentlyGraded as $task)
<div class="bg-white border border-[#e0daf5] rounded-xl p-4 mb-3">
  <div class="flex items-start gap-3">
    <div class="w-9 h-9 bg-[#eeedfe] rounded-full flex items-center justify-center text-[11px] font-bold text-[#3C3489] flex-shrink-0">
      {{ strtoupper(substr($task->staff->name,0,1)) }}{{ strtoupper(substr(explode(' ',$task->staff->name)[1]??'',0,1)) }}
    </div>
    <div class="flex-1 min-w-0">
      <div class="font-medium text-gray-900 text-sm truncate">{{ $task->title }}</div>
      <div class="text-xs text-gray-400">{{ $task->staff->name }} · {{ $task->date->format('d M Y') }}</div>

      {{-- Historical Challenges Render inside graded history layout --}}
      @if($task->challenge_identified || $task->challenge_impact)
      <div class="mt-2 bg-gray-50/50 border border-gray-100 rounded-lg p-2.5 text-xs flex flex-col gap-0.5">
        @if($task->challenge_identified)
          <div class="text-gray-700"><span class="font-medium text-red-600">Challenge:</span> {{ $task->challenge_identified }}</div>
        @endif
        @if($task->challenge_impact)
          <div class="text-gray-600"><span class="font-medium text-gray-700">Impact:</span> {{ $task->challenge_impact }}</div>
        @endif
      </div>
      @endif

      <div class="mt-2 bg-[#faf8ff] border border-[#AFA9EC] rounded-lg p-2.5">
        <div class="flex items-center gap-2 mb-1">
          <span class="text-[10px] font-medium text-[#534AB7] uppercase tracking-wider">Your feedback</span>
          <span class="ml-auto text-xs font-bold text-[#3C3489]">{{ $task->supervisor_score }}/10</span>
        </div>
        @if($task->supervisor_comment)
        <div class="text-xs text-gray-600">{{ $task->supervisor_comment }}</div>
        @endif
        <div class="text-[10px] text-gray-400 mt-1">{{ $task->reviewed_at?->format('d/m/Y, H:i') }}</div>
      </div>
    </div>
  </div>
</div>
@endforeach
@endif
@endsection
