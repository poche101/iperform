@extends('layouts.app')
@section('title', 'My Dashboard')

@section('nav')
<a href="{{ route('staff.dashboard') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('staff.dashboard') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-home text-lg w-5"></i> Home
</a>
<a href="{{ route('staff.tasks') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('staff.tasks') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-checkbox text-lg w-5"></i> Tasks
</a>
<a href="{{ route('staff.appraisal') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('staff.appraisal') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-file-description text-lg w-5"></i> Appraisal
</a>
@endsection

@section('content')
<div class="mb-1 text-2xl font-bold text-gray-900">Performance, with grace.</div>
<div class="text-sm text-gray-500 mb-5">Submit monthly appraisals on time — eliminate salary delays caused by late submissions.</div>

{{-- Hero card --}}
<div class="bg-[#3C3489] text-white rounded-xl p-5 mb-5">
  <div class="text-[10px] opacity-60 uppercase tracking-wider mb-1">
    {{ $cycle?->name ?? 'No active cycle' }} cycle is live
  </div>
  <div class="text-xl font-bold mb-0.5">Log daily. Submit on time.</div>
  <div class="text-sm opacity-80 mb-4">
    A warm, modern appraisal tool for church staff — log tasks, submit appraisals on time and get paid promptly.
  </div>
  <div class="flex gap-3 flex-wrap">
    <a href="{{ route('staff.tasks') }}"
      class="inline-flex items-center gap-2 bg-[#EF9F27] text-amber-900 px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-400 transition">
      <i class="ti ti-plus"></i> Log a task
    </a>
    <a href="{{ route('staff.appraisal') }}"
      class="inline-flex items-center gap-2 bg-white/10 text-white border border-white/20 px-4 py-2 rounded-lg text-sm font-medium hover:bg-white/20 transition">
      <i class="ti ti-file-description"></i> View my appraisal
    </a>
  </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-3 mb-5">
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="w-9 h-9 bg-[#eeedfe] rounded-lg flex items-center justify-center mb-2">
      <i class="ti ti-file-description text-[#3C3489] text-lg"></i>
    </div>
    <div class="text-sm font-bold text-gray-900 leading-tight">
      {{ $appraisal ? ucfirst(str_replace('_',' ',$appraisal->status)) : 'Not started' }}
    </div>
    <div class="text-xs text-gray-400 mt-0.5">Appraisal status</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="w-9 h-9 bg-[#eeedfe] rounded-lg flex items-center justify-center mb-2">
      <i class="ti ti-clock text-[#3C3489] text-lg"></i>
    </div>
    <div class="text-2xl font-bold text-gray-900">
      {{ $cycle ? max(0, now()->diffInDays(\Carbon\Carbon::parse($cycle->deadline), false)) : '—' }}d
    </div>
    <div class="text-xs text-gray-400 mt-0.5">Until deadline</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="w-9 h-9 bg-[#eeedfe] rounded-lg flex items-center justify-center mb-2">
      <i class="ti ti-award text-[#3C3489] text-lg"></i>
    </div>
    <div class="text-2xl font-bold text-gray-900">{{ $appraisal?->hr_grade ?? '—' }}</div>
    <div class="text-xs text-gray-400 mt-0.5">Current grade</div>
  </div>
</div>

{{-- How it works --}}
<div class="bg-white border border-[#e0daf5] rounded-xl p-5">
  <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">How it works</div>
  @foreach([
    ['1','STAFF','Log your tasks','Tasks logged on/before the 25th land in this month\'s bucket. Anything after carries forward automatically.'],
    ['2','STAFF','AI drafts your appraisal','One tap: AI reads only this month\'s bucket and writes a first-person summary you can edit.'],
    ['3','SUPERVISOR','Review & forward','Live pipeline view — see who\'s logging, drafting, submitted, with you, or with HR.'],
    ['4','HR','Analyse the whole team','AI totals scores, predicts promotion readiness, flags training needs and gives per-staff coaching insights.'],
  ] as [$n,$role,$title,$desc])
  <div class="flex gap-4 mb-4 last:mb-0">
    <div class="w-9 h-9 bg-[#3C3489] text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0">{{ $n }}</div>
    <div>
      <div class="text-[10px] text-[#534AB7] uppercase tracking-wider font-medium">{{ $role }}</div>
      <div class="font-semibold text-gray-800 text-sm">{{ $title }}</div>
      <div class="text-xs text-gray-500 mt-0.5">{{ $desc }}</div>
    </div>
  </div>
  @endforeach
  <div class="mt-4 pt-4 border-t border-[#f0edf8] text-xs text-gray-400 italic text-center">
    "Whatever you do, work at it with all your heart, as working for the Lord." — Colossians 3:23
  </div>
</div>
@endsection
