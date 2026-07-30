@extends('layouts.app')
@section('title', 'Grade Appraisal')

@section('nav')
@foreach([['supervisor.dashboard','ti-home','Home'],['supervisor.pipeline','ti-list','Pipeline']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route) ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')
@php
  $canEdit = $appraisal->status === 'submitted' && auth()->id() === $appraisal->supervisor_id;
  $canEditTraining = auth()->id() === $appraisal->supervisor_id; // Sections 6, 7 & Work Confirmation always editable for the assigned supervisor
@endphp

<div class="flex items-start justify-between mb-5">
  <div>
    <div class="text-sm text-gray-400 uppercase tracking-wider font-medium">Supervisor Review</div>
    <div class="text-2xl font-bold text-gray-900">{{ $appraisal->staff->name }}</div>
    <div class="text-sm text-gray-500">{{ $appraisal->staff->department }} · {{ $appraisal->cycle->name }}</div>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('appraisal.pdf', $appraisal) }}" class="inline-flex items-center gap-2 bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-800 transition">
      <i class="ti ti-file-download"></i> PDF
    </a>
    <a href="{{ route('supervisor.dashboard') }}" class="inline-flex items-center gap-2 border border-[#e0daf5] text-gray-500 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
      <i class="ti ti-arrow-left"></i> Back
    </a>
  </div>
</div>

@if($canEdit)
<div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-5 text-sm text-amber-700 flex items-start gap-2">
  <i class="ti ti-info-circle mt-0.5"></i>
  <div>Score each section (0–10). After reviewing and grading all sections, click <strong>Forward to HR</strong> to submit. You will not be able to edit after forwarding.</div>
</div>
@endif

<form method="POST" action="{{ ($canEdit || $canEditTraining) ? route('supervisor.appraisal.save', $appraisal) : '#' }}" id="sup-form">
@csrf

{{-- Vital info --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3"><div class="font-semibold text-[#3C3489] text-sm">Vital Information</div></div>
  <div class="p-5 grid grid-cols-2 gap-3 text-sm">
    <div><span class="text-gray-400">Staff:</span> <strong>{{ $appraisal->staff->name }}</strong></div>
    <div><span class="text-gray-400">Department:</span> {{ $appraisal->staff->department }}</div>
    <div><span class="text-gray-400">Designation:</span> {{ $appraisal->staff->designation }}</div>
    <div><span class="text-gray-400">Period:</span> {{ $appraisal->cycle->name }}</div>
    <div><span class="text-gray-400">Submitted:</span> {{ $appraisal->submitted_at?->format('d M Y H:i') ?? '—' }}</div>
    <div><span class="text-gray-400">Status:</span>
      <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#eeedfe] text-[#3C3489]">{{ ucfirst(str_replace('_',' ',$appraisal->status)) }}</span>
    </div>
  </div>
</div>

{{-- Section 1: KRAs --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 1: Major Targets & KRA</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">35%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-[#f5f0ff]">
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">KRA</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Target</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Achievement</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-24">% Done</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Staff</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Sup. Score</th>
      </tr></thead>
      <tbody>
        @forelse($appraisal->kras as $kra)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400">{{ $kra->sn }}</td>
          <td class="py-2 px-3 text-gray-700">{{ $kra->kra }}</td>
          <td class="py-2 px-3 text-gray-600">{{ $kra->target }}</td>
          <td class="py-2 px-3 text-gray-600">{{ $kra->achievement }}</td>
          <td class="py-2 px-3 text-center">
            @php $pct = $kra->completion_percentage ?? 0; @endphp
            <div class="flex flex-col items-center gap-1">
              <span class="font-semibold text-sm {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
              <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[60px]">
                <div class="h-1.5 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
              </div>
            </div>
          </td>
          <td class="py-2 px-3 text-center"><span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $kra->staff_score ?? '—' }}</span></td>
          <td class="py-2 px-3 text-center">
            @if($canEdit)
            <input type="number" name="kra_scores[{{ $kra->id }}]" min="0" max="10" value="{{ $kra->supervisor_score }}" class="w-16 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">
            @else
            <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $kra->supervisor_score ?? '—' }}</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="py-3 px-3 text-gray-400 italic text-sm">No KRAs recorded.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Section 2: Tasks --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 2: Routine & Other Tasks</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">25%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-[#f5f0ff]">
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Task</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Performance</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-24">% Done</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Staff</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Sup. Score</th>
      </tr></thead>
      <tbody>
        @forelse($appraisal->tasks as $task)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400" rowspan="{{ ($task->challenge_identified || $task->challenge_impact) ? '2' : '1' }}">{{ $task->sn }}</td>
          <td class="py-2 px-3 text-gray-700 font-medium">
            {{ $task->task }}
            <div class="text-[11px] text-gray-400 mt-0.5 font-normal">{{ $task->date?->format('d M Y') }}</div>
          </td>
          <td class="py-2 px-3 text-gray-600">
            @if($task->target)
              <div class="text-[11px] text-amber-700 mb-0.5"><strong>Target:</strong> {{ $task->target }}</div>
            @endif
            <div>{{ $task->performance }}</div>
          </td>
          <td class="py-2 px-3 text-center">
            @php $pct = $task->completion_percentage ?? 0; @endphp
            <div class="flex flex-col items-center gap-1">
              <span class="font-semibold text-sm {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
              <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[60px]">
                <div class="h-1.5 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
              </div>
            </div>
          </td>
          <td class="py-2 px-3 text-center"><span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $task->staff_score ?? '—' }}</span></td>
          <td class="py-2 px-3 text-center">
            @if($canEdit)<input type="number" name="task_scores[{{ $task->id }}]" min="0" max="10" value="{{ $task->supervisor_score }}" class="w-16 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">
            @else<span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $task->supervisor_score ?? '—' }}</span>@endif
          </td>
        </tr>

        {{-- Embedded Sub-Row for Challenges & Risk Assessment Assessment if recorded --}}
        @if($task->challenge_identified || $task->challenge_impact)
        <tr class="bg-gray-50/60 border-b border-[#f0edf8]">
          <td colspan="5" class="px-3 pb-2.5 pt-0 text-xs">
            <div class="bg-white rounded-lg p-2.5 border border-gray-100 flex flex-col gap-1 max-w-3xl">
              <div class="text-[10px] uppercase font-semibold text-[#534AB7] tracking-wider mb-0.5 flex items-center gap-1">
                <i class="ti ti-alert-triangle text-xs"></i> Logged Performance Challenges & Assessment
              </div>
              @if($task->challenge_identified)
                <div class="text-gray-700"><span class="font-medium text-red-600">Challenge:</span> {{ $task->challenge_identified }}</div>
              @endif
              @if($task->challenge_impact)
                <div class="text-gray-600"><span class="font-medium text-gray-700">Impact:</span> {{ $task->challenge_impact }}</div>
              @endif
            </div>
          </td>
        </tr>
        @endif

        @empty
        <tr><td colspan="6" class="py-3 px-3 text-gray-400 italic text-sm">No tasks recorded.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Section 3: Innovations --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 3: Ideas, Innovations & Contributions</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">20%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-[#f5f0ff]">
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Idea / Contribution</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Impact</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-24">% Done</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Staff</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Sup. Score</th>
      </tr></thead>
      <tbody>
        @forelse($appraisal->innovations as $inn)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400">{{ $inn->sn }}</td>
          <td class="py-2 px-3 text-gray-700">{{ $inn->idea }}</td>
          <td class="py-2 px-3 text-gray-600">{{ $inn->impact }}</td>
          <td class="py-2 px-3 text-center">
            @php $pct = $inn->completion_percentage ?? 0; @endphp
            <div class="flex flex-col items-center gap-1">
              <span class="font-semibold text-sm {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
              <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[60px]">
                <div class="h-1.5 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
              </div>
            </div>
          </td>
          <td class="py-2 px-3 text-center"><span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $inn->staff_score ?? '—' }}</span></td>
          <td class="py-2 px-3 text-center">
            @if($canEdit)<input type="number" name="innovation_scores[{{ $inn->id }}]" min="0" max="10" value="{{ $inn->supervisor_score }}" class="w-16 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">
            @else<span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $inn->supervisor_score ?? '—' }}</span>@endif
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="py-3 px-3 text-gray-400 italic text-sm">None recorded.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Section 4: Competencies --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 4: Core Competencies</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">15%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-[#f5f0ff]">
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Competency</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Staff Score</th>
        <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Sup. Score</th>
      </tr></thead>
      <tbody>
        @foreach($appraisal->competencies as $comp)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400">{{ $comp->sn }}</td>
          <td class="py-2 px-3 font-medium text-gray-700">{{ $comp->competency }}</td>
          <td class="py-2 px-3 text-center"><span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $comp->staff_score ?? '—' }}</span></td>
          <td class="py-2 px-3 text-center">
            @if($canEdit)<input type="number" name="competency_scores[{{ $comp->id }}]" min="0" max="10" value="{{ $comp->supervisor_score }}" class="w-16 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">
            @else<span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $comp->supervisor_score ?? '—' }}</span>@endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- Section 5: Challenges (read-only, staff filled) --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 5: Performance Challenges / Constraints</div>
    <span class="text-[10px] text-[#534AB7]">Staff filled</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-[#f5f0ff]"><th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th><th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Challenge</th><th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Impact</th></tr></thead>
      <tbody>
        @forelse((is_array($appraisal->section5) ? $appraisal->section5 : (json_decode($appraisal->getRawOriginal('section5'), true) ?? [])) as $i => $row)
        <tr class="border-t border-[#f0edf8]"><td class="py-2 px-3 text-gray-400">{{ $row['sn'] ?? $i + 1 }}</td><td class="py-2 px-3 text-gray-700">{{ $row['challenge'] ?? '—' }}</td><td class="py-2 px-3 text-gray-600">{{ $row['impact'] ?? '—' }}</td></tr>
        @empty
        <tr><td colspan="3" class="py-3 px-3 text-gray-400 italic text-sm">None reported.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Section 6: Training (supervisor fills Area, Training & Recommendation — always editable for assigned supervisor) --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 6: Capacity Development & Training Needs</div>
    <span class="text-[10px] text-[#534AB7]">Supervisor editable</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-[#f5f0ff]"><th class="w-8 py-2 px-3 text-[11px] text-[#534AB7] font-medium">#</th><th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Area</th><th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Training</th><th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Supervisor Recommendation</th></tr></thead>
      <tbody>
        @forelse((is_array($appraisal->section6) ? $appraisal->section6 : (json_decode($appraisal->getRawOriginal('section6'), true) ?? [])) as $i => $row)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400">{{ $row['sn'] ?? $i + 1 }}</td>
          <td class="py-2 px-3 text-gray-700">
            @if($canEditTraining)
              <input type="text" name="section6[{{ $i }}][area]" value="{{ $row['area'] ?? '' }}" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Area...">
            @else
              {{ $row['area'] ?? '—' }}
            @endif
          </td>
          <td class="py-2 px-3 text-gray-600">
            @if($canEditTraining)
              <input type="text" name="section6[{{ $i }}][training]" value="{{ $row['training'] ?? '' }}" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Training...">
            @else
              {{ $row['training'] ?? '—' }}
            @endif
          </td>
          <td class="py-2 px-3">
            @if($canEditTraining)
              <input type="text" name="section6[{{ $i }}][recommendation]" value="{{ $row['recommendation'] ?? '' }}" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Your recommendation...">
            @else
              {{ $row['recommendation'] ?? '—' }}
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="py-3 px-3 text-gray-400 italic text-sm">None identified.</td></tr>
        @endforelse
      </tbody>
    </table>
    @if($canEditTraining)
    <button type="button" onclick="addSection6Row()" class="mt-3 inline-flex items-center gap-1.5 text-xs text-[#3C3489] border border-[#e0daf5] px-3 py-1.5 rounded-lg hover:bg-[#f5f0ff] transition">
      <i class="ti ti-plus"></i> Add row
    </button>
    @endif
  </div>
</div>

{{-- Section 7: Compliance to Administrative Policy (mirrors the printed appraisal form — supervisor editable) --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 7: Compliance to Administrative Policy</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">20%</span>
  </div>
  <div class="p-5">
    <p class="text-xs text-gray-400 mb-3">Rating – Points deducted will be reflected in the first quarter appraisal scores.</p>
    <div class="overflow-x-auto">
      <table class="w-full text-sm border border-[#e0daf5]">
        <thead>
          <tr class="bg-[#f5f0ff]">
            <th class="w-8 py-2 px-3 text-[11px] text-[#534AB7] font-medium text-left border border-[#e0daf5]">#</th>
            <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium border border-[#e0daf5]">Policy / Areas for Compliance</th>
            <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center border border-[#e0daf5] w-28">Score (0–10)</th>
            <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium border border-[#e0daf5]">Comments</th>
          </tr>
        </thead>
        <tbody>
          @php
            // Hardcoded policy list — matches the printed appraisal form exactly.
            // 'key' is a stable slug used to look up/save this row's score & comment,
            // independent of any stored order or DB content.
            $section7Policies = [
              ['key' => 'attendance',      'sn' => 1, 'label' => 'Attendance at work'],
              ['key' => 'chapel',          'sn' => 2, 'label' => 'Chapel Attendance'],
              ['key' => 'punctuality',     'sn' => 3, 'label' => 'Punctuality to work'],
              ['key' => 'reports',         'sn' => 4, 'label' => 'Prompt and consistent submission of weekly and monthly reports'],
              ['key' => 'participation',   'sn' => 5, 'label' => 'Participation in', 'sub' => [
                  'Dept./Group Staff Meetings',
                  'Dept./Group Prayer Meetings',
                  'Blue Elite Book Club Study',
                  'All other meetings',
              ]],
            ];
            $section7Saved = is_array($appraisal->section7_items) ? $appraisal->section7_items : (json_decode($appraisal->getRawOriginal('section7_items'), true) ?? []);
          @endphp
          @foreach($section7Policies as $policy)
          @php
            $saved = $section7Saved[$policy['key']] ?? [];
          @endphp
          <tr class="border-t border-[#f0edf8]">
            <td class="py-2 px-3 text-gray-400 align-top border border-[#e0daf5]">{{ $policy['sn'] }}</td>
            <td class="py-2 px-3 text-gray-700 align-top border border-[#e0daf5]">
              {{ $policy['label'] }}
              @if(!empty($policy['sub']))
                <ul class="mt-1 pl-4 list-disc text-gray-600 text-[13px]">
                  @foreach($policy['sub'] as $subItem)
                  <li>{{ $subItem }}</li>
                  @endforeach
                </ul>
              @endif
            </td>
            <td class="py-2 px-3 text-center align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <input type="number" name="section7[{{ $policy['key'] }}][score]" min="0" max="10" value="{{ $saved['score'] ?? 0 }}" class="sec7-score w-16 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" oninput="updateSection7Total()">
              @else
                <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $saved['score'] ?? '—' }}</span>
              @endif
            </td>
            <td class="py-2 px-3 align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <input type="text" name="section7[{{ $policy['key'] }}][comment]" value="{{ $saved['comment'] ?? '' }}" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Comment...">
              @else
                <span class="text-gray-500 text-xs">{{ $saved['comment'] ?? '—' }}</span>
              @endif
            </td>
          </tr>
          @endforeach

          {{-- Misconduct banner row --}}
          <tr class="bg-[#dce6f5]">
            <td colspan="4" class="py-1.5 px-3 font-semibold text-[#3C3489] text-xs border border-[#e0daf5]">Indicate records of misconduct in the month</td>
          </tr>

          {{-- Misconduct item --}}
          @php
            $misconduct = is_array($appraisal->section7_misconduct) ? $appraisal->section7_misconduct : (json_decode($appraisal->getRawOriginal('section7_misconduct'), true) ?? ['score' => 0, 'comment' => '']);
          @endphp
          <tr class="border-t border-[#f0edf8]">
            <td class="py-2 px-3 text-gray-400 align-top border border-[#e0daf5]">6</td>
            <td class="py-2 px-3 text-gray-700 align-top border border-[#e0daf5]">Number of official warnings and other disciplinary actions for negligence or misconduct</td>
            <td class="py-2 px-3 text-center align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <input type="number" name="section7_misconduct[score]" min="0" max="10" value="{{ $misconduct['score'] ?? 0 }}" class="sec7-score w-16 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" oninput="updateSection7Total()">
              @else
                <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $misconduct['score'] ?? '—' }}</span>
              @endif
            </td>
            <td class="py-2 px-3 align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <input type="text" name="section7_misconduct[comment]" value="{{ $misconduct['comment'] ?? '' }}" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Comment...">
              @else
                <span class="text-gray-500 text-xs">{{ $misconduct['comment'] ?? '—' }}</span>
              @endif
            </td>
          </tr>

          @php
            $sec7ScoreSum = 0;
            foreach ($section7Policies as $policy) {
              $sec7ScoreSum += $section7Saved[$policy['key']]['score'] ?? 0;
            }
            $sec7ScoreSum += $misconduct['score'] ?? 0;
            $sec7ComplianceMax = (count($section7Policies) + 1) * 10;

            $sec7SummarySum = ($appraisal->overall_contribution_score ?? 0)
              + ($appraisal->key_strengths_score ?? 0)
              + ($appraisal->areas_for_improvement_score ?? 0);
            $sec7SummaryMax = 30;
          @endphp

          {{-- Supervisor Performance Summary banner --}}
          <tr class="bg-[#eeedfe]">
            <td colspan="4" class="py-1.5 px-3 font-semibold text-[#3C3489] text-xs border border-[#e0daf5]">SUPERVISOR PERFORMANCE SUMMARY</td>
          </tr>
          <tr class="border-t border-[#f0edf8]">
            <td colspan="2" class="py-2 px-3 text-gray-700 align-top border border-[#e0daf5]">Overall Contribution to Department Goals:</td>
            <td class="py-2 px-3 text-center align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <input type="number" name="overall_contribution_score" min="0" max="10" value="{{ $appraisal->overall_contribution_score ?? 0 }}" class="sec7-summary-score w-16 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" oninput="updateSection7Total()">
              @else
                <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $appraisal->overall_contribution_score ?? '—' }}</span>
              @endif
            </td>
            <td class="py-2 px-3 align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <textarea name="overall_contribution" rows="1" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">{{ $appraisal->overall_contribution }}</textarea>
              @else
                <span class="text-gray-600">{{ $appraisal->overall_contribution ?: '—' }}</span>
              @endif
            </td>
          </tr>

          {{-- Key Strengths --}}
          <tr class="border-t border-[#f0edf8]">
            <td colspan="2" class="py-2 px-3 text-gray-700 align-top border border-[#e0daf5]">Key Strengths:</td>
            <td class="py-2 px-3 text-center align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <input type="number" name="key_strengths_score" min="0" max="10" value="{{ $appraisal->key_strengths_score ?? 0 }}" class="sec7-summary-score w-16 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" oninput="updateSection7Total()">
              @else
                <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $appraisal->key_strengths_score ?? '—' }}</span>
              @endif
            </td>
            <td class="py-2 px-3 align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <textarea name="key_strengths" rows="1" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">{{ $appraisal->key_strengths }}</textarea>
              @else
                <span class="text-gray-600">{{ $appraisal->key_strengths ?: '—' }}</span>
              @endif
            </td>
          </tr>

          {{-- Areas for Improvement --}}
          <tr class="border-t border-[#f0edf8]">
            <td colspan="2" class="py-2 px-3 text-gray-700 align-top border border-[#e0daf5]">Areas for Improvement:</td>
            <td class="py-2 px-3 text-center align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <input type="number" name="areas_for_improvement_score" min="0" max="10" value="{{ $appraisal->areas_for_improvement_score ?? 0 }}" class="sec7-summary-score w-16 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" oninput="updateSection7Total()">
              @else
                <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $appraisal->areas_for_improvement_score ?? '—' }}</span>
              @endif
            </td>
            <td class="py-2 px-3 align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <textarea name="areas_for_improvement" rows="1" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">{{ $appraisal->areas_for_improvement }}</textarea>
              @else
                <span class="text-gray-600">{{ $appraisal->areas_for_improvement ?: '—' }}</span>
              @endif
            </td>
          </tr>

          {{-- Total Score (auto-computed: Compliance subtotal + Performance Summary subtotal) --}}
          <tr class="bg-[#f5f0ff] font-semibold">
            <td colspan="2" class="py-2 px-3 text-red-600 align-top border border-[#e0daf5]">TOTAL SCORE <span class="font-normal text-gray-500 text-xs">(auto-calculated: Compliance + Performance Summary)</span></td>
            <td class="py-2 px-3 text-center align-top border border-[#e0daf5]">
              <span class="bg-[#3C3489] text-white font-semibold px-2 py-0.5 rounded-full text-xs" id="sec7-grand-total-badge">{{ $sec7ScoreSum + $sec7SummarySum }}</span>
              @if($canEditTraining)
                <input type="hidden" name="total_score" id="sec7-grand-total-input" value="{{ $sec7ScoreSum + $sec7SummarySum }}">
              @endif
            </td>
            <td class="py-2 px-3 align-top border border-[#e0daf5]">
              @if($canEditTraining)
                <input type="text" name="total_score_comment" value="{{ $appraisal->total_score_comment }}" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Comment...">
              @else
                <span class="text-gray-500 text-xs">{{ $appraisal->total_score_comment ?: '—' }}</span>
              @endif
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="mt-3 grid grid-cols-2 gap-3">
      <div class="flex items-center justify-between bg-[#f5f0ff] border border-[#e0daf5] rounded-lg px-4 py-2.5">
        <span class="text-sm font-medium text-[#3C3489]">Policy / Compliance Total</span>
        <span class="text-sm font-semibold text-[#3C3489]"><span id="sec7-compliance-total">{{ $sec7ScoreSum }}</span> / {{ $sec7ComplianceMax }}</span>
      </div>
      <div class="flex items-center justify-between bg-[#f5f0ff] border border-[#e0daf5] rounded-lg px-4 py-2.5">
        <span class="text-sm font-medium text-[#3C3489]">Performance Summary Total</span>
        <span class="text-sm font-semibold text-[#3C3489]"><span id="sec7-summary-total">{{ $sec7SummarySum }}</span> / {{ $sec7SummaryMax }}</span>
      </div>
    </div>
    <div class="mt-2 flex items-center justify-between bg-[#3C3489] rounded-lg px-4 py-2.5">
      <span class="text-sm font-medium text-white">Section 7 Grand Total</span>
      <span class="text-sm font-semibold text-white"><span id="sec7-grand-total">{{ $sec7ScoreSum + $sec7SummarySum }}</span> / {{ $sec7ComplianceMax + $sec7SummaryMax }}</span>
    </div>
  </div>
</div>

{{-- Work Confirmation (always editable for assigned supervisor) --}}
<div class="bg-white border border-[#e0daf5] rounded-xl p-5 mb-4">
  <div class="font-semibold text-gray-800 text-sm mb-2">Work Confirmation</div>
  <p class="text-xs text-gray-500 mb-3">I hereby confirm that the above-mentioned staff member was actively engaged in assigned duties during the period under review and is eligible for payment.</p>
  <div class="flex items-center gap-3">
    <label class="text-sm text-gray-600 whitespace-nowrap">Percentage of Salary to be Paid:</label>
    @if($canEditTraining)
    <input type="number" name="salary_percent" min="0" max="100" value="{{ $appraisal->salary_percent ?? 100 }}" class="w-20 border border-[#e0daf5] rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:border-[#7F77DD]">
    <span class="text-sm text-gray-400">%</span>
    @else
    <strong class="text-gray-900">{{ $appraisal->salary_percent ?? '—' }}%</strong>
    @endif
  </div>
  @if($canEditTraining)
  <div class="mt-3">
    <label class="block text-xs text-gray-400 mb-1">Supervisor Comments</label>
    <textarea name="supervisor_comments" rows="2" class="w-full border border-[#e0daf5] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Any additional comments...">{{ $appraisal->supervisor_comments }}</textarea>
  </div>
  @elseif($appraisal->supervisor_comments)
  <div class="mt-2 text-sm text-gray-600">{{ $appraisal->supervisor_comments }}</div>
  @endif
</div>

@if($canEdit || $canEditTraining)
<div class="flex gap-3 mt-5">
  <button type="submit" formaction="{{ route('supervisor.appraisal.save', $appraisal) }}" class="inline-flex items-center gap-2 border border-[#7F77DD] text-[#3C3489] px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-[#eeedfe] transition">
    <i class="ti ti-device-floppy"></i> Save grades
  </button>
  @if($canEdit)
  <button type="submit" formaction="{{ route('supervisor.appraisal.forward', $appraisal) }}" onclick="return confirm('Forward this appraisal to HR? You cannot edit it after forwarding.')" class="inline-flex items-center gap-2 bg-[#3C3489] text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-[#26215C] transition">
    <i class="ti ti-arrow-right"></i> Forward to Staff Performance
  </button>
  @endif
</div>
@endif

</form>
@endsection

@section('scripts')
<script>
function updateSection7Total() {
  let complianceTotal = 0;
  document.querySelectorAll('.sec7-score').forEach(input => {
    complianceTotal += parseInt(input.value) || 0;
  });

  let summaryTotal = 0;
  document.querySelectorAll('.sec7-summary-score').forEach(input => {
    summaryTotal += parseInt(input.value) || 0;
  });

  const grandTotal = complianceTotal + summaryTotal;

  const complianceEl = document.getElementById('sec7-compliance-total');
  const summaryEl = document.getElementById('sec7-summary-total');
  const grandEl = document.getElementById('sec7-grand-total');
  const grandBadgeEl = document.getElementById('sec7-grand-total-badge');
  const grandInputEl = document.getElementById('sec7-grand-total-input');

  if (complianceEl) complianceEl.textContent = complianceTotal;
  if (summaryEl) summaryEl.textContent = summaryTotal;
  if (grandEl) grandEl.textContent = grandTotal;
  if (grandBadgeEl) grandBadgeEl.textContent = grandTotal;
  if (grandInputEl) grandInputEl.value = grandTotal;
}

document.addEventListener('DOMContentLoaded', updateSection7Total);

function addSection6Row() {
  const tbody = document.querySelector('table tbody');
  // Section 6 table is the first tbody after the "Capacity Development" heading;
  // grabbing it more precisely to avoid touching other tables.
  const section6Table = Array.from(document.querySelectorAll('table')).find(t =>
    t.closest('div')?.previousElementSibling?.textContent?.includes('Capacity Development') ||
    t.innerHTML.includes('section6[')
  );
  if (!section6Table) return;
  const body = section6Table.querySelector('tbody');
  const rowCount = body.querySelectorAll('tr').length;
  const newIndex = rowCount;
  const tr = document.createElement('tr');
  tr.className = 'border-t border-[#f0edf8]';
  tr.innerHTML = `
    <td class="py-2 px-3 text-gray-400">${newIndex + 1}</td>
    <td class="py-2 px-3"><input type="text" name="section6[${newIndex}][area]" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Area..."></td>
    <td class="py-2 px-3"><input type="text" name="section6[${newIndex}][training]" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Training..."></td>
    <td class="py-2 px-3"><input type="text" name="section6[${newIndex}][recommendation]" class="w-full border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Your recommendation..."></td>
  `;
  body.appendChild(tr);
}
</script>
@endsection
