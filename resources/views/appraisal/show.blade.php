@extends('layouts.app')
@section('title', 'My Appraisal')

@section('nav')
<a href="{{ route('staff.dashboard') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]">
  <i class="ti ti-arrow-left text-lg w-5"></i> Dashboard
</a>
<a href="{{ route('staff.appraisal') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium">
  <i class="ti ti-file-description text-lg w-5"></i> My Appraisal
</a>
@endsection

@section('content')
@php
  $staff = $appraisal->staff;
@endphp

<div class="flex items-start justify-between mb-5">
  <div>
    <div class="text-sm text-gray-400 uppercase tracking-wider font-medium">Staff Performance Appraisal</div>
    <div class="text-2xl font-bold text-gray-900">{{ strtoupper($cycle->name) }}</div>
    <div class="text-sm text-gray-500">This reflects the tasks you've logged this cycle. Log new tasks from the Tasks page to update it.</div>
  </div>
  <div class="flex items-center gap-2">
    <a href="{{ route('appraisal.pdf', $appraisal) }}" class="inline-flex items-center gap-2 bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-800 transition">
      <i class="ti ti-file-download"></i> Export PDF
    </a>
  </div>
</div>

<div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-5 text-sm text-blue-700 flex items-start gap-2">
  <i class="ti ti-info-circle mt-0.5 flex-shrink-0"></i>
  <div>
    This appraisal builds automatically from the tasks you log. To add or update a KRA, task, or idea, go to
    <a href="{{ route('staff.tasks') }}" class="font-semibold underline hover:text-blue-800">Tasks</a> and log it there — it will appear here once saved.
  </div>
</div>

{{-- Vital info --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Vital Information</div>
    <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
      {{ $appraisal->status==='approved' ? 'bg-green-100 text-green-700'
        : ($appraisal->status==='with_hr'  ? 'bg-orange-100 text-orange-700'
        : ($appraisal->status==='submitted' ? 'bg-[#eeedfe] text-[#3C3489]'
        : 'bg-gray-100 text-gray-500')) }}">
      {{ ucfirst(str_replace('_',' ',$appraisal->status)) }}
    </span>
  </div>
  <div class="p-5 grid grid-cols-2 gap-4 text-sm">
    <div><span class="text-gray-400">Staff:</span> <strong>{{ $staff->name }}</strong></div>
    <div><span class="text-gray-400">Supervisor:</span> <strong>{{ $appraisal->supervisor->name }}</strong></div>
    <div><span class="text-gray-400">Department:</span> {{ $staff->department }}</div>
    <div><span class="text-gray-400">Designation:</span> {{ $staff->designation }}</div>
    <div><span class="text-gray-400">Period:</span> {{ $cycle->name }}</div>
    <div><span class="text-gray-400">Deadline:</span> {{ \Carbon\Carbon::parse($cycle->deadline)->format('d M Y') }}</div>
  </div>
</div>

{{-- SECTION 1: KRAs --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 1: Major Targets & KRA</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">35%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-[#f5f0ff]">
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">KRA for the Month</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Target</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Achievement</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-28">% Done</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-20">Self Score</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-20">Supervisor Score</th>
        </tr>
      </thead>
      <tbody>
        @forelse($appraisal->kras as $kra)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400 align-top">{{ $kra->sn }}</td>
          <td class="py-2 px-3 align-top">{{ $kra->kra }}</td>
          <td class="py-2 px-3 align-top">{{ $kra->target }}</td>
          <td class="py-2 px-3 align-top">{{ $kra->achievement }}</td>
          <td class="py-2 px-3 align-top">
            @php $pct = $kra->completion_percentage ?? 0; @endphp
            <div class="flex flex-col items-center gap-1">
              <span class="font-semibold text-sm {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
              <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[70px]">
                <div class="h-1.5 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
              </div>
            </div>
          </td>
          <td class="py-2 px-3 text-center align-top">
            <span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $kra->staff_score ?? '—' }}</span>
          </td>
          <td class="py-2 px-3 text-center align-top">
            <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $kra->supervisor_score ?? '—' }}</span>
          </td>
        </tr>
        @empty
        <tr class="border-t border-[#f0edf8]">
          <td colspan="7" class="py-4 px-3 text-gray-400 italic text-sm">No KRAs logged yet. Log one from the Tasks page.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- SECTION 2: Tasks --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 2: Routine & Other Tasks</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">25%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-[#f5f0ff]">
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Task</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Performance & Achievement</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-28">% Done</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-20">Self Score</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-20">Supervisor Score</th>
        </tr>
      </thead>
      <tbody>
        @forelse($appraisal->tasks as $task)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400 align-top">{{ $task->sn }}</td>
          <td class="py-2 px-3 align-top">{{ $task->task }}</td>
          <td class="py-2 px-3 align-top">{{ $task->performance }}</td>
          <td class="py-2 px-3 align-top">
            @php $pct = $task->completion_percentage ?? 0; @endphp
            <div class="flex flex-col items-center gap-1">
              <span class="font-semibold text-sm {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
              <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[70px]">
                <div class="h-1.5 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
              </div>
            </div>
          </td>
          <td class="py-2 px-3 text-center align-top">
            <span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $task->staff_score ?? '—' }}</span>
          </td>
          <td class="py-2 px-3 text-center align-top">
            <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $task->supervisor_score ?? '—' }}</span>
          </td>
        </tr>
        @empty
        <tr class="border-t border-[#f0edf8]">
          <td colspan="6" class="py-4 px-3 text-gray-400 italic text-sm">No tasks logged yet. Log one from the Tasks page.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- SECTION 3: Innovations --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 3: Ideas, Innovations & Outstanding Contributions</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">20%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-[#f5f0ff]">
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Idea / Contribution</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Impact</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-28">% Done</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-20">Self Score</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-20">Supervisor Score</th>
        </tr>
      </thead>
      <tbody>
        @forelse($appraisal->innovations as $inn)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400 align-top">{{ $inn->sn }}</td>
          <td class="py-2 px-3 align-top">{{ $inn->idea }}</td>
          <td class="py-2 px-3 align-top">{{ $inn->impact }}</td>
          <td class="py-2 px-3 align-top">
            @php $pct = $inn->completion_percentage ?? 0; @endphp
            <div class="flex flex-col items-center gap-1">
              <span class="font-semibold text-sm {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
              <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[70px]">
                <div class="h-1.5 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
              </div>
            </div>
          </td>
          <td class="py-2 px-3 text-center align-top">
            <span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $inn->staff_score ?? '—' }}</span>
          </td>
          <td class="py-2 px-3 text-center align-top">
            <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $inn->supervisor_score ?? '—' }}</span>
          </td>
        </tr>
        @empty
        <tr class="border-t border-[#f0edf8]">
          <td colspan="6" class="py-4 px-3 text-gray-400 italic text-sm">No innovations logged yet. Log one from the Tasks page.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- SECTION 4: Competencies --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 4: Core Competencies</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">15%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-[#f5f0ff]">
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">#</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Competency</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Self Score</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Supervisor Score</th>
        </tr>
      </thead>
      <tbody>
        @foreach($appraisal->competencies as $comp)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400">{{ $comp->sn }}</td>
          <td class="py-2 px-3 font-medium text-gray-700">{{ $comp->competency }}</td>
          <td class="py-2 px-3 text-center">
            <span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $comp->staff_score ?? '—' }}</span>
          </td>
          <td class="py-2 px-3 text-center">
            <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $comp->supervisor_score ?? '—' }}</span>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection
