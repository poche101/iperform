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
  $canEdit = $appraisal->status === 'drafting' && auth()->id() === $appraisal->staff_id;
  $staff   = $appraisal->staff;
@endphp

<div class="flex items-start justify-between mb-5">
  <div>
    <div class="text-sm text-gray-400 uppercase tracking-wider font-medium">Staff Performance Appraisal</div>
    <div class="text-2xl font-bold text-gray-900">{{ strtoupper($cycle->name) }}</div>
    <div class="text-sm text-gray-500">Rating scale 0–10. Staff completes sections 1–4 plus % completion per item.</div>
  </div>
  <div class="flex items-center gap-2">
    <a href="{{ route('appraisal.pdf', $appraisal) }}" class="inline-flex items-center gap-2 bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-800 transition">
      <i class="ti ti-file-download"></i> Export PDF
    </a>
  </div>
</div>

@if($canEdit)
<div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-5 text-sm text-blue-700 flex items-start gap-2">
  <i class="ti ti-info-circle mt-0.5 flex-shrink-0"></i>
  <div>For each KRA, task, or idea — fill in what you did, enter your <strong>% completion</strong> (how much of the target was achieved), then give yourself a self-score out of 10.</div>
</div>
@endif

<form method="POST" action="{{ $canEdit ? route('staff.appraisal.save', $appraisal) : '#' }}" id="appraisal-form">
@csrf

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
    <table class="w-full text-sm" id="kra-table">
      <thead>
        <tr class="bg-[#f5f0ff]">
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">KRA for the Month</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Target</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Achievement</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-28">% Done</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-20">Self Score</th>
        </tr>
      </thead>
      <tbody>
        @forelse($appraisal->kras as $i => $kra)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400 align-top">{{ $kra->sn }}</td>
          <td class="py-2 px-3 align-top">
            @if($canEdit)
              <textarea name="kras[{{ $i }}][kra]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2">{{ $kra->kra }}</textarea>
            @else
              {{ $kra->kra }}
            @endif
          </td>
          <td class="py-2 px-3 align-top">
            @if($canEdit)
              <textarea name="kras[{{ $i }}][target]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[110px] resize-none" rows="2">{{ $kra->target }}</textarea>
            @else
              {{ $kra->target }}
            @endif
          </td>
          <td class="py-2 px-3 align-top">
            @if($canEdit)
              <textarea name="kras[{{ $i }}][achievement]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2">{{ $kra->achievement }}</textarea>
            @else
              {{ $kra->achievement }}
            @endif
          </td>
          <td class="py-2 px-3 align-top">
            @if($canEdit)
              <div class="flex flex-col items-center gap-1">
                <input type="number" name="kras[{{ $i }}][completion_percentage]" min="0" max="100"
                  value="{{ $kra->completion_percentage ?? 0 }}"
                  class="w-20 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]"
                  oninput="updateBar(this)">
                <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[70px]">
                  <div class="bg-[#3C3489] h-1.5 rounded-full transition-all" style="width:{{ $kra->completion_percentage ?? 0 }}%"></div>
                </div>
              </div>
            @else
              @php $pct = $kra->completion_percentage ?? 0; @endphp
              <div class="flex flex-col items-center gap-1">
                <span class="font-semibold text-sm {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
                <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[70px]">
                  <div class="h-1.5 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
                </div>
              </div>
            @endif
          </td>
          <td class="py-2 px-3 text-center align-top">
            @if($canEdit)
              <input type="number" name="kras[{{ $i }}][staff_score]" min="0" max="10" value="{{ $kra->staff_score }}"
                class="w-14 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">
            @else
              <span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $kra->staff_score ?? '—' }}</span>
            @endif
          </td>
        </tr>
        @empty
        <tr class="border-t border-[#f0edf8] empty-row">
          <td colspan="6" class="py-4 px-3 text-gray-400 italic text-sm">No KRAs logged yet.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    @if($canEdit)
    <button type="button" onclick="addKRARow()"
      class="mt-3 inline-flex items-center gap-1 text-xs border border-[#7F77DD] text-[#3C3489] px-3 py-1.5 rounded-lg hover:bg-[#eeedfe] transition">
      <i class="ti ti-plus"></i> Add KRA
    </button>
    @endif
  </div>
</div>

{{-- SECTION 2: Tasks --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 2: Routine & Other Tasks</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">25%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm" id="task-table">
      <thead>
        <tr class="bg-[#f5f0ff]">
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Task</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Performance & Achievement</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-28">% Done</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-20">Self Score</th>
        </tr>
      </thead>
      <tbody>
        @forelse($appraisal->tasks as $i => $task)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400 align-top">{{ $task->sn }}</td>
          <td class="py-2 px-3 align-top">
            @if($canEdit)
              <textarea name="tasks[{{ $i }}][task]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2">{{ $task->task }}</textarea>
            @else {{ $task->task }} @endif
          </td>
          <td class="py-2 px-3 align-top">
            @if($canEdit)
              <textarea name="tasks[{{ $i }}][performance]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2">{{ $task->performance }}</textarea>
            @else {{ $task->performance }} @endif
          </td>
          <td class="py-2 px-3 align-top">
            @if($canEdit)
              <div class="flex flex-col items-center gap-1">
                <input type="number" name="tasks[{{ $i }}][completion_percentage]" min="0" max="100"
                  value="{{ $task->completion_percentage ?? 0 }}"
                  class="w-20 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]"
                  oninput="updateBar(this)">
                <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[70px]">
                  <div class="bg-[#3C3489] h-1.5 rounded-full transition-all" style="width:{{ $task->completion_percentage ?? 0 }}%"></div>
                </div>
              </div>
            @else
              @php $pct = $task->completion_percentage ?? 0; @endphp
              <div class="flex flex-col items-center gap-1">
                <span class="font-semibold text-sm {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
                <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[70px]">
                  <div class="h-1.5 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
                </div>
              </div>
            @endif
          </td>
          <td class="py-2 px-3 text-center align-top">
            @if($canEdit)
              <input type="number" name="tasks[{{ $i }}][staff_score]" min="0" max="10" value="{{ $task->staff_score }}"
                class="w-14 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">
            @else
              <span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $task->staff_score ?? '—' }}</span>
            @endif
          </td>
        </tr>
        @empty
        <tr class="border-t border-[#f0edf8] empty-row">
          <td colspan="5" class="py-4 px-3 text-gray-400 italic text-sm">No tasks logged yet.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    @if($canEdit)
    <button type="button" onclick="addTaskRow()"
      class="mt-3 inline-flex items-center gap-1 text-xs border border-[#7F77DD] text-[#3C3489] px-3 py-1.5 rounded-lg hover:bg-[#eeedfe] transition">
      <i class="ti ti-plus"></i> Add Task
    </button>
    @endif
  </div>
</div>

{{-- SECTION 3: Innovations --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 3: Ideas, Innovations & Outstanding Contributions</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">20%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm" id="innovation-table">
      <thead>
        <tr class="bg-[#f5f0ff]">
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium w-8">#</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Idea / Contribution</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Impact</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-28">% Done</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center w-20">Self Score</th>
        </tr>
      </thead>
      <tbody>
        @forelse($appraisal->innovations as $i => $inn)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400 align-top">{{ $inn->sn }}</td>
          <td class="py-2 px-3 align-top">
            @if($canEdit)
              <textarea name="innovations[{{ $i }}][idea]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2">{{ $inn->idea }}</textarea>
            @else {{ $inn->idea }} @endif
          </td>
          <td class="py-2 px-3 align-top">
            @if($canEdit)
              <textarea name="innovations[{{ $i }}][impact]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2">{{ $inn->impact }}</textarea>
            @else {{ $inn->impact }} @endif
          </td>
          <td class="py-2 px-3 align-top">
            @if($canEdit)
              <div class="flex flex-col items-center gap-1">
                <input type="number" name="innovations[{{ $i }}][completion_percentage]" min="0" max="100"
                  value="{{ $inn->completion_percentage ?? 0 }}"
                  class="w-20 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]"
                  oninput="updateBar(this)">
                <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[70px]">
                  <div class="bg-[#3C3489] h-1.5 rounded-full transition-all" style="width:{{ $inn->completion_percentage ?? 0 }}%"></div>
                </div>
              </div>
            @else
              @php $pct = $inn->completion_percentage ?? 0; @endphp
              <div class="flex flex-col items-center gap-1">
                <span class="font-semibold text-sm {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
                <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[70px]">
                  <div class="h-1.5 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
                </div>
              </div>
            @endif
          </td>
          <td class="py-2 px-3 text-center align-top">
            @if($canEdit)
              <input type="number" name="innovations[{{ $i }}][staff_score]" min="0" max="10" value="{{ $inn->staff_score }}"
                class="w-14 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">
            @else
              <span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $inn->staff_score ?? '—' }}</span>
            @endif
          </td>
        </tr>
        @empty
        <tr class="border-t border-[#f0edf8] empty-row">
          <td colspan="5" class="py-4 px-3 text-gray-400 italic text-sm">No innovations logged yet.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    @if($canEdit)
    <button type="button" onclick="addInnovationRow()"
      class="mt-3 inline-flex items-center gap-1 text-xs border border-[#7F77DD] text-[#3C3489] px-3 py-1.5 rounded-lg hover:bg-[#eeedfe] transition">
      <i class="ti ti-plus"></i> Add Idea
    </button>
    @endif
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
        </tr>
      </thead>
      <tbody>
        @foreach($appraisal->competencies as $comp)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400">{{ $comp->sn }}</td>
          <td class="py-2 px-3 font-medium text-gray-700">{{ $comp->competency }}</td>
          <td class="py-2 px-3 text-center">
            @if($canEdit)
              <input type="number" name="competencies[{{ $comp->id }}]" min="0" max="10" value="{{ $comp->staff_score }}"
                class="w-14 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]">
            @else
              <span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $comp->staff_score ?? '—' }}</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@if($canEdit)
<div class="flex gap-3 mt-5">
  <button type="submit" formaction="{{ route('staff.appraisal.save', $appraisal) }}"
    class="inline-flex items-center gap-2 border border-[#7F77DD] text-[#3C3489] px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-[#eeedfe] transition">
    <i class="ti ti-device-floppy"></i> Save draft
  </button>
  <button type="button" onclick="openConfirmationModal()"
    class="inline-flex items-center gap-2 bg-[#3C3489] text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-[#26215C] transition cursor-pointer">
    <i class="ti ti-send"></i> Submit to supervisor
  </button>
</div>
@endif

</form>

{{-- Custom Submit Confirmation Modal --}}
@if($canEdit)
<div id="submit-confirm-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
  {{-- Backdrop transition overlay --}}
  <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>

  {{-- Modal positioning wrap --}}
  <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-[#e0daf5]">
      
      {{-- Modal content body --}}
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#eeedfe] sm:mx-0 sm:h-10 sm:w-10">
            <i class="ti ti-alert-triangle text-xl text-[#3C3489]"></i>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Submit Appraisal?</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">Are you sure you want to forward this appraisal to your supervisor? This action **cannot be undone** and your input options will be locked.</p>
            </div>
          </div>
        </div>
      </div>

      {{-- Action Button Bar --}}
      <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
        <button type="button" onclick="confirmAndSubmitForm()"
          class="inline-flex w-full justify-center rounded-lg bg-[#3C3489] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#26215C] sm:w-auto transition">
          Yes, submit appraise
        </button>
        <button type="button" onclick="closeConfirmationModal()"
          class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition">
          Cancel
        </button>
      </div>

    </div>
  </div>
</div>
@endif

@endsection

@section('scripts')
<script>
let kraCount  = {{ $appraisal->kras->count() }};
let taskCount = {{ $appraisal->tasks->count() }};
let innCount  = {{ $appraisal->innovations->count() }};

// Modal Control Functions
function openConfirmationModal() {
  const modal = document.getElementById('submit-confirm-modal');
  if (modal) {
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
}

function closeConfirmationModal() {
  const modal = document.getElementById('submit-confirm-modal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }
}

function confirmAndSubmitForm() {
  const form = document.getElementById('appraisal-form');
  if (form) {
    // Explicitly update action endpoint to follow the defined submission route
    form.action = "{{ route('staff.appraisal.submit', $appraisal) }}";
    form.submit();
  }
}

// Live-update the mini progress bar under a percentage input
function updateBar(input) {
  const val = Math.min(100, Math.max(0, parseInt(input.value) || 0));
  const bar = input.closest('div').querySelector('.bg-\\[\\#3C3489\\]');
  if (bar) bar.style.width = val + '%';
}

const pctField = (name) => `
  <div class="flex flex-col items-center gap-1">
    <input type="number" name="${name}" min="0" max="100" value="0"
      class="w-20 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]"
      oninput="updateBar(this)">
    <div class="w-full bg-gray-200 rounded-full h-1.5 min-w-[70px]">
      <div class="bg-[#3C3489] h-1.5 rounded-full transition-all" style="width:0%"></div>
    </div>
  </div>`;

const scoreField = (name) =>
  `<input type="number" name="${name}" min="0" max="10"
    class="w-14 text-center border border-[#e0daf5] rounded px-2 py-1 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="0-10">`;

function addKRARow() {
  const tbody = document.querySelector('#kra-table tbody');
  const emptyRow = tbody.querySelector('.empty-row');
  if (emptyRow) emptyRow.remove();

  const i = kraCount++;
  const sn = tbody.querySelectorAll('tr').length + 1;
  tbody.insertAdjacentHTML('beforeend', `
    <tr class="border-t border-[#f0edf8]">
      <td class="py-2 px-3 text-gray-400 align-top">${sn}</td>
      <td class="py-2 px-3 align-top"><textarea name="kras[${i}][kra]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2" placeholder="KRA description"></textarea></td>
      <td class="py-2 px-3 align-top"><textarea name="kras[${i}][target]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[110px] resize-none" rows="2" placeholder="Target"></textarea></td>
      <td class="py-2 px-3 align-top"><textarea name="kras[${i}][achievement]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2" placeholder="Achievement"></textarea></td>
      <td class="py-2 px-3 align-top">${pctField(`kras[${i}][completion_percentage]`)}</td>
      <td class="py-2 px-3 text-center align-top">${scoreField(`kras[${i}][staff_score]`)}</td>
    </tr>`);
}

function addTaskRow() {
  const tbody = document.querySelector('#task-table tbody');
  const emptyRow = tbody.querySelector('.empty-row');
  if (emptyRow) emptyRow.remove();

  const i = taskCount++;
  const sn = tbody.querySelectorAll('tr').length + 1;
  tbody.insertAdjacentHTML('beforeend', `
    <tr class="border-t border-[#f0edf8]">
      <td class="py-2 px-3 text-gray-400 align-top">${sn}</td>
      <td class="py-2 px-3 align-top"><textarea name="tasks[${i}][task]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2" placeholder="Task description"></textarea></td>
      <td class="py-2 px-3 align-top"><textarea name="tasks[${i}][performance]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2" placeholder="Performance & achievement"></textarea></td>
      <td class="py-2 px-3 align-top">${pctField(`tasks[${i}][completion_percentage]`)}</td>
      <td class="py-2 px-3 text-center align-top">${scoreField(`tasks[${i}][staff_score]`)}</td>
    </tr>`);
}

function addInnovationRow() {
  const tbody = document.querySelector('#innovation-table tbody');
  const emptyRow = tbody.querySelector('.empty-row');
  if (emptyRow) emptyRow.remove();

  const i = innCount++;
  const sn = tbody.querySelectorAll('tr').length + 1;
  tbody.insertAdjacentHTML('beforeend', `
    <tr class="border-t border-[#f0edf8]">
      <td class="py-2 px-3 text-gray-400 align-top">${sn}</td>
      <td class="py-2 px-3 align-top"><textarea name="innovations[${i}][idea]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2" placeholder="Idea or contribution"></textarea></td>
      <td class="py-2 px-3 align-top"><textarea name="innovations[${i}][impact]" class="w-full text-sm border border-[#e0daf5] rounded px-2 py-1 min-w-[140px] resize-none" rows="2" placeholder="Impact"></textarea></td>
      <td class="py-2 px-3 align-top">${pctField(`innovations[${i}][completion_percentage]`)}</td>
      <td class="py-2 px-3 text-center align-top">${scoreField(`innovations[${i}][staff_score]`)}</td>
    </tr>`);
}
</script>
@endsection