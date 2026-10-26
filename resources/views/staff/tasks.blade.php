@extends('layouts.app')
@section('title', 'My Tasks')

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
@php
  $newLogType = old('log_type', 'daily');
  $typeCounts = $typeCounts ?? ['all' => $tasks->count(), 'daily' => 0, 'weekly' => 0];
  $type       = $type ?? null;

  // Defaults for the new-task form: today, and this week's Monday to Sunday
  $newDate      = old('date', now()->format('Y-m-d'));
  $newWeekStart = old('week_start', now()->startOfWeek()->format('Y-m-d'));
  $newWeekEnd   = old('week_end', now()->endOfWeek()->format('Y-m-d'));
  $newIsWeekly  = $newLogType === 'weekly';
@endphp

<style>
  /* Pulse shown on a task opened from a notification */
  @keyframes task-focus-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(127,119,221,.55); }
    70%  { box-shadow: 0 0 0 14px rgba(127,119,221,0); }
    100% { box-shadow: 0 0 0 0 rgba(127,119,221,0); }
  }
  .task-focus { border-color: #7F77DD !important; animation: task-focus-pulse 1.4s ease-out 3; }
</style>

<div class="flex items-start justify-between gap-3 mb-5">
  <div>
    <div class="text-2xl font-bold text-gray-900 mb-1">My tasks</div>
    <div class="text-sm text-gray-500">Log what you did daily or weekly, submit it for review, and read your supervisor's feedback.</div>
  </div>
  @include('partials.notification-bell')
</div>

{{-- Log a task form --}}
<div class="bg-white border border-[#e0daf5] rounded-xl p-4 sm:p-5 mb-5">
  <div class="font-semibold text-gray-800 mb-4">Log a task</div>

  @if($errors->any())
  <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
    <ul class="list-disc pl-4 space-y-0.5">
      @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
  @endif

  <form method="POST" action="{{ route('staff.tasks.store') }}">
    @csrf

    {{-- Daily / Weekly --}}
    <div class="mb-4">
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Log type</label>
      <div class="grid grid-cols-2 gap-2">
        @foreach(['daily' => ['Daily', 'ti-sun'], 'weekly' => ['Weekly', 'ti-calendar-week']] as $val => [$lbl, $ico])
        <label class="block w-full">
          <input type="radio" name="log_type" value="{{ $val }}" {{ $newLogType === $val ? 'checked' : '' }}
            onchange="updateLogType('new', this.value)" class="sr-only peer">
          <div class="flex items-center justify-center gap-1.5 py-2 px-2.5 border border-[#e0daf5] rounded-lg text-sm cursor-pointer peer-checked:bg-[#3C3489] peer-checked:text-white peer-checked:border-[#3C3489] hover:bg-[#eeedfe] transition">
            <i class="ti {{ $ico }}"></i> {{ $lbl }}
          </div>
        </label>
        @endforeach
      </div>
    </div>

    <div class="mb-3">
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Title</label>
      <input name="title" value="{{ old('title') }}" class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="e.g. Outreach with my team" required>
    </div>

    {{-- Daily: single date --}}
    <div id="daily-fields-new" class="mb-3 {{ $newIsWeekly ? 'hidden' : '' }}">
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Date</label>
      <input type="date" name="date" value="{{ $newDate }}"
        {{ $newIsWeekly ? 'disabled' : 'required' }}
        class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
    </div>

    {{-- Weekly: From / To --}}
    <div id="weekly-fields-new" class="mb-3 {{ $newIsWeekly ? '' : 'hidden' }}">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">From</label>
          <input type="date" name="week_start" id="week-start-new" value="{{ $newWeekStart }}"
            onchange="syncWeekRange('new')"
            {{ $newIsWeekly ? 'required' : 'disabled' }}
            class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">To</label>
          <input type="date" name="week_end" id="week-end-new" value="{{ $newWeekEnd }}" min="{{ $newWeekStart }}"
            {{ $newIsWeekly ? 'required' : 'disabled' }}
            class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
        </div>
      </div>
      <div class="text-[11px] text-gray-400 mt-1">Choose the first and last day this weekly report covers.</div>
    </div>

    {{-- Target Field --}}
    <div class="mb-3">
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
        Target/Task <span class="text-gray-400 font-normal">(What was the expected goal?)</span>
      </label>
      <textarea name="target" rows="2" class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] resize-none" placeholder="e.g. Reach 500 members, Complete 10 outreaches this month">{{ old('target') }}</textarea>
    </div>

    <div class="mb-3">
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Details</label>
      <textarea name="details" rows="3" class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] resize-none" placeholder="What did you accomplish? Add context your supervisor needs.">{{ old('details') }}</textarea>
    </div>

    {{-- OPTIONAL SECTION: Challenges & Impact --}}
    <div class="bg-[#fcfbfe] border border-[#e8e3f8] rounded-lg p-3 sm:p-4 mb-4">
      <div class="text-xs font-semibold text-[#534AB7] uppercase tracking-wider mb-2 flex items-center gap-1.5">
        <i class="ti ti-alert-triangle text-sm"></i> Performance Challenges & Risk Assessment <span class="text-gray-400 font-normal lowercase">(optional)</span>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
        <div>
          <label class="block text-[11px] font-medium text-gray-500 uppercase tracking-wider mb-1">
            Challenge Identified <span class="text-gray-400 font-normal capitalize">(Optional)</span>
          </label>
          <textarea name="challenge_identified" rows="2" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] resize-none bg-white" placeholder="Any obstacles encountered during this period? (Leave blank if none)">{{ old('challenge_identified') }}</textarea>
        </div>
        <div>
          <label class="block text-[11px] font-medium text-gray-500 uppercase tracking-wider mb-1">
            Impact on Performance <span class="text-gray-400 font-normal capitalize">(Optional)</span>
          </label>
          <textarea name="challenge_impact" rows="2" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] resize-none bg-white" placeholder="How did this affect outcomes or timelines? (Leave blank if none)">{{ old('challenge_impact') }}</textarea>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Category</label>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
        @foreach(['KRA','Routine','Ideas, Innovation & Outstanding Contribution'] as $cat)
        <label class="block w-full">
          <input type="radio" name="category" value="{{ $cat }}" {{ old('category','Routine')===$cat?'checked':'' }} class="sr-only peer">
          <div class="text-center py-2 px-2.5 border border-[#e0daf5] rounded-lg text-xs sm:text-sm cursor-pointer peer-checked:bg-[#3C3489] peer-checked:text-white peer-checked:border-[#3C3489] hover:bg-[#eeedfe] transition truncate">
            {{ $cat }}
          </div>
        </label>
        @endforeach
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
      <div>
        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
          % Completion · <span id="pct-label">{{ old('completion_percentage', 0) }}</span>%
        </label>
        <input type="range" name="completion_percentage" min="0" max="100" step="5"
          value="{{ old('completion_percentage', 0) }}"
          oninput="document.getElementById('pct-label').textContent=this.value"
          class="w-full h-1.5 bg-[#e0daf5] rounded-full appearance-none cursor-pointer accent-[#3C3489]">
        <div class="flex justify-between text-[10px] text-gray-400 mt-0.5"><span>0%</span><span>50%</span><span>100%</span></div>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
          Self score · <span id="score-label">{{ old('self_score', 8) }}</span>/10
        </label>
        <input type="range" name="self_score" min="0" max="10" step="1"
          value="{{ old('self_score', 8) }}"
          oninput="document.getElementById('score-label').textContent=this.value"
          class="w-full h-1.5 bg-[#e0daf5] rounded-full appearance-none cursor-pointer accent-[#3C3489]">
        <div class="flex justify-between text-[10px] text-gray-400 mt-0.5"><span>0</span><span>5</span><span>10</span></div>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3">
      <button type="submit" name="action" value="submit"
        class="inline-flex items-center justify-center gap-2 bg-[#3C3489] text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-[#26215C] transition order-1 sm:order-none">
        <i class="ti ti-send"></i> Log & submit
      </button>
      <button type="submit" name="action" value="draft"
        class="inline-flex items-center justify-center gap-2 border border-[#e0daf5] text-gray-500 px-5 py-2.5 rounded-lg text-sm hover:bg-gray-50 transition order-2 sm:order-none">
        <i class="ti ti-device-floppy"></i> Save as draft
      </button>
    </div>
  </form>
</div>

{{-- Recent tasks header + Daily / Weekly filter --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
  <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest">My recent tasks</div>
  <div class="inline-flex bg-white border border-[#e0daf5] rounded-lg p-0.5 text-xs self-start">
    @foreach([null => 'All', 'daily' => 'Daily', 'weekly' => 'Weekly'] as $key => $lbl)
    @php $countKey = $key ?? 'all'; @endphp
    <a href="{{ route('staff.tasks', $key ? ['type' => $key] : []) }}"
       class="px-3 py-1.5 rounded-md transition whitespace-nowrap {{ $type === $key ? 'bg-[#3C3489] text-white font-medium' : 'text-gray-500 hover:bg-[#eeedfe] hover:text-[#3C3489]' }}">
      {{ $lbl }} <span class="{{ $type === $key ? 'text-white/70' : 'text-gray-400' }}">({{ $typeCounts[$countKey] ?? 0 }})</span>
    </a>
    @endforeach
  </div>
</div>

@forelse($tasks as $task)
@php
  // Values for this task's edit form
  $editIsWeekly  = $task->isWeekly();
  $editDate      = $task->date->format('Y-m-d');
  $editWeekStart = $task->week_start?->format('Y-m-d') ?? $editDate;
  $editWeekEnd   = $task->week_end?->format('Y-m-d') ?? $task->date->copy()->addDays(6)->format('Y-m-d');
@endphp
<div id="task-{{ $task->id }}" class="bg-white border border-[#e0daf5] rounded-xl p-4 mb-3 scroll-mt-24">

  {{-- DISPLAY MODE --}}
  <div id="display-{{ $task->id }}" class="flex items-start justify-between gap-3">
    <div class="flex-1 min-w-0">
      <div class="font-medium text-gray-900 text-sm mb-1.5 break-words">{{ $task->title }}</div>
      <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-2">
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full whitespace-nowrap {{ $task->getLogTypeColorClass() }}">
          {{ $task->getLogTypeLabel() }}
        </span>
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full whitespace-nowrap {{ $task->getStatusColorClass() }}">
          {{ $task->getStatusLabel() }}
        </span>
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full whitespace-nowrap {{ $task->getCategoryColorClass() }}">
          {{ $task->category }}
        </span>
        @if($task->self_score !== null)
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full whitespace-nowrap bg-gray-100 text-gray-500">
          Self {{ $task->self_score }}/10
        </span>
        @endif
        @if($task->completion_percentage > 0)
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full whitespace-nowrap
          {{ $task->completion_percentage >= 80 ? 'bg-green-100 text-green-700' : ($task->completion_percentage >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-600') }}">
          {{ $task->completion_percentage }}% done
        </span>
        @endif
      </div>
      <div class="text-[11px] text-gray-400 mb-2">
        @if($task->isWeekly())
          <i class="ti ti-calendar-week"></i> {{ $task->getPeriodLabel() }}
        @else
          {{ $task->getPeriodLabel() }}
        @endif
      </div>

      @if($task->target)
      <div class="text-xs text-amber-700 mb-1.5 break-words">
        <strong>Target:</strong> {{ $task->target }}
      </div>
      @endif

      @if($task->details)
      <div class="text-xs text-gray-500 mb-2.5 break-words">{{ $task->details }}</div>
      @endif

      {{-- Displaying logged challenges if they exist --}}
      @if($task->challenge_identified || $task->challenge_impact)
      <div class="text-xs bg-gray-50 rounded-lg p-2.5 border border-gray-100 mb-2.5 space-y-1 break-words">
        @if($task->challenge_identified)
          <div class="text-gray-700"><span class="font-medium text-red-600">Challenge:</span> {{ $task->challenge_identified }}</div>
        @endif
        @if($task->challenge_impact)
          <div class="text-gray-600"><span class="font-medium text-gray-700">Impact:</span> {{ $task->challenge_impact }}</div>
        @endif
      </div>
      @endif

      {{-- Progress bar --}}
      @if($task->completion_percentage > 0)
      <div class="mt-2 mb-1">
        <div class="w-full bg-gray-100 rounded-full h-1.5">
          <div class="h-1.5 rounded-full {{ $task->completion_percentage >= 80 ? 'bg-green-500' : ($task->completion_percentage >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
            style="width: {{ $task->completion_percentage }}%"></div>
        </div>
      </div>
      @endif

      {{-- Supervisor comment (left from the appraisal page; works before and after grading) --}}
      @if($task->supervisor_note)
      <div class="mt-3 bg-amber-50 border border-amber-200 rounded-lg p-3 break-words">
        <div class="flex items-center justify-between gap-2 mb-1">
          <div class="flex items-center gap-1.5 min-w-0">
            <i class="ti ti-message-circle-2 text-amber-600 text-xs flex-shrink-0"></i>
            <span class="text-[10px] font-medium text-amber-700 uppercase tracking-wider truncate">Supervisor comment</span>
          </div>
          @if($task->supervisor_note_at)
          <span class="text-[10px] text-gray-400 flex-shrink-0">{{ \Illuminate\Support\Carbon::parse($task->supervisor_note_at)->format('d/m/Y, H:i') }}</span>
          @endif
        </div>
        <div class="text-xs text-gray-700 whitespace-pre-line">{{ $task->supervisor_note }}</div>
      </div>
      @endif

      {{-- Supervisor feedback --}}
      @if($task->status === 'graded')
      <div class="mt-3 bg-[#faf8ff] border border-[#AFA9EC] rounded-lg p-3 break-words">
        <div class="flex items-center justify-between gap-2 mb-1">
          <div class="flex items-center gap-1.5 min-w-0">
            <i class="ti ti-message-circle text-[#534AB7] text-xs flex-shrink-0"></i>
            <span class="text-[10px] font-medium text-[#534AB7] uppercase tracking-wider truncate">Supervisor feedback</span>
          </div>
          <span class="text-xs font-bold text-[#3C3489] flex-shrink-0">{{ $task->supervisor_score }}/10</span>
        </div>
        @if($task->supervisor_comment)
        <div class="text-xs text-gray-700 mb-1">{{ $task->supervisor_comment }}</div>
        @endif
        <div class="text-[10px] text-gray-400">
          {{ $task->reviewer?->name }} · {{ $task->reviewed_at?->format('d/m/Y, H:i') }}
        </div>
      </div>
      @endif
    </div>

    @if($task->status !== 'graded')
    <div class="flex-shrink-0 flex flex-col items-center gap-2">
      <button type="button" onclick="toggleEditTask({{ $task->id }})" class="text-gray-300 hover:text-[#3C3489] transition p-1" aria-label="Edit task">
        <i class="ti ti-pencil text-base"></i>
      </button>
      <form method="POST" action="{{ route('staff.tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
        @csrf @method('DELETE')
        <button type="submit" class="text-gray-300 hover:text-red-500 transition p-1" aria-label="Delete task">
          <i class="ti ti-trash text-base"></i>
        </button>
      </form>
    </div>
    @endif
  </div>

  {{-- EDIT MODE (hidden by default, only exists for non-graded tasks) --}}
  @if($task->status !== 'graded')
  <div id="edit-form-{{ $task->id }}" class="hidden mt-1">
    <form method="POST" action="{{ route('staff.tasks.update', $task) }}">
      @csrf
      @method('PUT')

      {{-- Daily / Weekly --}}
      <div class="mb-4">
        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Log type</label>
        <div class="grid grid-cols-2 gap-2">
          @foreach(['daily' => ['Daily', 'ti-sun'], 'weekly' => ['Weekly', 'ti-calendar-week']] as $val => [$lbl, $ico])
          <label class="block w-full">
            <input type="radio" name="log_type" value="{{ $val }}" {{ $task->log_type === $val ? 'checked' : '' }}
              onchange="updateLogType('{{ $task->id }}', this.value)" class="sr-only peer">
            <div class="flex items-center justify-center gap-1.5 py-2 px-2.5 border border-[#e0daf5] rounded-lg text-sm cursor-pointer peer-checked:bg-[#3C3489] peer-checked:text-white peer-checked:border-[#3C3489] hover:bg-[#eeedfe] transition">
              <i class="ti {{ $ico }}"></i> {{ $lbl }}
            </div>
          </label>
          @endforeach
        </div>
      </div>

      <div class="mb-3">
        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Title</label>
        <input name="title" value="{{ $task->title }}" class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" required>
      </div>

      {{-- Daily: single date --}}
      <div id="daily-fields-{{ $task->id }}" class="mb-3 {{ $editIsWeekly ? 'hidden' : '' }}">
        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Date</label>
        <input type="date" name="date" value="{{ $editDate }}"
          {{ $editIsWeekly ? 'disabled' : 'required' }}
          class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
      </div>

      {{-- Weekly: From / To --}}
      <div id="weekly-fields-{{ $task->id }}" class="mb-3 {{ $editIsWeekly ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">From</label>
            <input type="date" name="week_start" id="week-start-{{ $task->id }}" value="{{ $editWeekStart }}"
              onchange="syncWeekRange('{{ $task->id }}')"
              {{ $editIsWeekly ? 'required' : 'disabled' }}
              class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">To</label>
            <input type="date" name="week_end" id="week-end-{{ $task->id }}" value="{{ $editWeekEnd }}" min="{{ $editWeekStart }}"
              {{ $editIsWeekly ? 'required' : 'disabled' }}
              class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
          </div>
        </div>
        <div class="text-[11px] text-gray-400 mt-1">Choose the first and last day this weekly report covers.</div>
      </div>

      <div class="mb-3">
        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
          Target/Task <span class="text-gray-400 font-normal">(What was the expected goal?)</span>
        </label>
        <textarea name="target" rows="2" class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] resize-none">{{ $task->target }}</textarea>
      </div>

      <div class="mb-3">
        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Details</label>
        <textarea name="details" rows="3" class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] resize-none">{{ $task->details }}</textarea>
      </div>

      <div class="bg-[#fcfbfe] border border-[#e8e3f8] rounded-lg p-3 sm:p-4 mb-4">
        <div class="text-xs font-semibold text-[#534AB7] uppercase tracking-wider mb-2 flex items-center gap-1.5">
          <i class="ti ti-alert-triangle text-sm"></i> Performance Challenges & Risk Assessment <span class="text-gray-400 font-normal lowercase">(optional)</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
          <div>
            <label class="block text-[11px] font-medium text-gray-500 uppercase tracking-wider mb-1">Challenge Identified</label>
            <textarea name="challenge_identified" rows="2" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] resize-none bg-white">{{ $task->challenge_identified }}</textarea>
          </div>
          <div>
            <label class="block text-[11px] font-medium text-gray-500 uppercase tracking-wider mb-1">Impact on Performance</label>
            <textarea name="challenge_impact" rows="2" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] resize-none bg-white">{{ $task->challenge_impact }}</textarea>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Category</label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          @foreach(['KRA','Routine','Ideas, Innovation & Outstanding Contribution'] as $cat)
          <label class="block w-full">
            <input type="radio" name="category" value="{{ $cat }}" {{ $task->category===$cat?'checked':'' }} class="sr-only peer">
            <div class="text-center py-2 px-2.5 border border-[#e0daf5] rounded-lg text-xs sm:text-sm cursor-pointer peer-checked:bg-[#3C3489] peer-checked:text-white peer-checked:border-[#3C3489] hover:bg-[#eeedfe] transition truncate">
              {{ $cat }}
            </div>
          </label>
          @endforeach
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        <div>
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
            % Completion · <span id="pct-label-{{ $task->id }}">{{ $task->completion_percentage }}</span>%
          </label>
          <input type="range" name="completion_percentage" min="0" max="100" step="5"
            value="{{ $task->completion_percentage }}"
            oninput="document.getElementById('pct-label-{{ $task->id }}').textContent=this.value"
            class="w-full h-1.5 bg-[#e0daf5] rounded-full appearance-none cursor-pointer accent-[#3C3489]">
          <div class="flex justify-between text-[10px] text-gray-400 mt-0.5"><span>0%</span><span>50%</span><span>100%</span></div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
            Self score · <span id="score-label-{{ $task->id }}">{{ $task->self_score }}</span>/10
          </label>
          <input type="range" name="self_score" min="0" max="10" step="1"
            value="{{ $task->self_score }}"
            oninput="document.getElementById('score-label-{{ $task->id }}').textContent=this.value"
            class="w-full h-1.5 bg-[#e0daf5] rounded-full appearance-none cursor-pointer accent-[#3C3489]">
          <div class="flex justify-between text-[10px] text-gray-400 mt-0.5"><span>0</span><span>5</span><span>10</span></div>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3">
        <button type="submit"
          class="inline-flex items-center justify-center gap-2 bg-[#3C3489] text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-[#26215C] transition">
          <i class="ti ti-check"></i> Save changes
        </button>
        <button type="button" onclick="toggleEditTask({{ $task->id }})"
          class="inline-flex items-center justify-center gap-2 border border-[#e0daf5] text-gray-500 px-5 py-2.5 rounded-lg text-sm hover:bg-gray-50 transition">
          <i class="ti ti-x"></i> Cancel
        </button>
      </div>
    </form>
  </div>
  @endif
</div>
@empty
<div class="bg-white border border-[#e0daf5] rounded-xl p-6 sm:p-8 text-center">
  <i class="ti ti-checkbox text-4xl text-[#AFA9EC] block mb-2"></i>
  <div class="text-sm text-gray-400">
    @if($type)
      No {{ $type }} tasks logged yet this cycle.
    @else
      No tasks logged yet. Use the form above to log your first task.
    @endif
  </div>
</div>
@endforelse

<script>
function toggleEditTask(id) {
  const display = document.getElementById('display-' + id);
  const form = document.getElementById('edit-form-' + id);
  const editing = !form.classList.contains('hidden');
  form.classList.toggle('hidden', editing);
  display.classList.toggle('hidden', !editing);
}

// Swap between the single "Date" field (daily) and the "From" / "To" fields (weekly).
// The hidden group is disabled so it is not submitted and not validated.
function updateLogType(scope, type) {
  const weekly = type === 'weekly';
  const groups = [
    [document.getElementById('daily-fields-' + scope), !weekly],
    [document.getElementById('weekly-fields-' + scope), weekly],
  ];

  groups.forEach(([el, active]) => {
    if (!el) return;
    el.classList.toggle('hidden', !active);
    el.querySelectorAll('input').forEach(input => {
      input.disabled = !active;
      input.required = active;
    });
  });

  if (weekly) syncWeekRange(scope);
}

// Keep "To" from being earlier than "From"
function syncWeekRange(scope) {
  const start = document.getElementById('week-start-' + scope);
  const end = document.getElementById('week-end-' + scope);
  if (!start || !end) return;

  end.min = start.value;
  if (start.value && end.value && end.value < start.value) {
    end.value = start.value;
  }
}

// Arriving from a notification (#task-123): scroll to that task and pulse it
document.addEventListener('DOMContentLoaded', function () {
  if (!location.hash.startsWith('#task-')) return;
  const el = document.getElementById(location.hash.slice(1));
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  el.classList.add('task-focus');
  setTimeout(function () { el.classList.remove('task-focus'); }, 5000);
});
</script>
@endsection
