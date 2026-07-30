@extends('layouts.app')
@section('title', 'HR — Task Logs')

@section('nav')
@foreach([['hr.dashboard','ti-chart-bar','HR Overview'],['hr.users','ti-users','Users'],['hr.assignments','ti-arrows-exchange','Assignments'],['hr.cycles','ti-calendar','Cycles'],['hr.tasks','ti-clipboard-list','Task Logs']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route) ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')

<div class="flex items-center justify-between mb-5">
  <div>
    <div class="text-xl font-bold text-gray-800">Task Logs</div>
    <div class="text-sm text-gray-500">
      {{ $cycle?->name ?? 'No active cycle' }} · All staff activity
    </div>
  </div>
</div>

@if(!$cycle)
<div class="bg-white border border-[#e0daf5] rounded-xl p-8 text-center text-gray-400">
  <i class="ti ti-calendar-off text-3xl mb-2 block"></i>
  There is no active appraisal cycle, so no task logs are available to display.
</div>
@else

@php
  $totalStaffLogged = $tasks->count();
  $totalLogs        = $tasks->flatten(1)->count();
  $totalAwaiting     = $tasks->flatten(1)->where('status', 'awaiting')->count();
  $totalGraded       = $tasks->flatten(1)->where('status', 'graded')->count();
@endphp

<div class="grid grid-cols-4 gap-4 mb-5">
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Staff with logs</div>
    <div class="text-2xl font-bold text-[#3C3489]">{{ $totalStaffLogged }}</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Total logs</div>
    <div class="text-2xl font-bold text-[#3C3489]">{{ $totalLogs }}</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Awaiting review</div>
    <div class="text-2xl font-bold text-amber-600">{{ $totalAwaiting }}</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Graded</div>
    <div class="text-2xl font-bold text-green-600">{{ $totalGraded }}</div>
  </div>
</div>

{{-- Filter bar --}}
<div class="bg-white border border-[#e0daf5] rounded-xl p-4 mb-5">
  <div class="flex items-center gap-3">
    <i class="ti ti-filter text-gray-400"></i>
    <select id="staff-filter" onchange="filterByStaff()" class="border border-[#e0daf5] rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-[#7F77DD]">
      <option value="">All staff ({{ $allStaff->count() }})</option>
      @foreach($allStaff as $staffMember)
      <option value="staff-group-{{ $staffMember->id }}">{{ $staffMember->name }}{{ $staffMember->supervisor ? ' — reports to ' . $staffMember->supervisor->name : '' }}</option>
      @endforeach
    </select>
  </div>
</div>

@forelse($tasks as $staffId => $staffTasks)
@php $staffMember = $staffTasks->first()->staff; @endphp
<div id="staff-group-{{ $staffId }}" class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div>
      <div class="font-semibold text-[#3C3489] text-sm">{{ $staffMember->name ?? 'Unknown staff' }}</div>
      <div class="text-xs text-gray-500">{{ $staffMember->department ?? '—' }} · {{ $staffMember->designation ?? '—' }}</div>
    </div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">{{ $staffTasks->count() }} {{ Str::plural('log', $staffTasks->count()) }}</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-[#f5f0ff]">
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Date</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Category</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Title</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">% Done</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Staff</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Supervisor</th>
          <th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Status</th>
          <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Reviewed by</th>
        </tr>
      </thead>
      <tbody>
        @foreach($staffTasks->sortByDesc('date') as $log)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-500 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($log->date)->format('d M Y') }}</td>
          <td class="py-2 px-3">
            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
              {{ $log->category === 'KRA' ? 'bg-[#eeedfe] text-[#3C3489]' : ($log->category === 'Innovation' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
              {{ $log->category }}
            </span>
          </td>
          <td class="py-2 px-3 font-medium text-gray-700">
            {{ $log->title }}
            @if($log->details)
            <div class="text-xs text-gray-400 mt-0.5">{{ Str::limit($log->details, 90) }}</div>
            @endif
          </td>
          <td class="py-2 px-3 text-center">
            @php $pct = $log->completion_percentage ?? 0; @endphp
            <span class="font-semibold text-xs {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
          </td>
          <td class="py-2 px-3 text-center"><span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $log->self_score ?? '—' }}</span></td>
          <td class="py-2 px-3 text-center"><span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $log->supervisor_score ?? '—' }}</span></td>
          <td class="py-2 px-3 text-center">
            @if($log->status === 'graded')
            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700">Graded</span>
            @else
            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Awaiting</span>
            @endif
          </td>
          <td class="py-2 px-3 text-gray-500 text-xs">
            {{ $log->reviewer->name ?? '—' }}
            @if($log->reviewed_at)
            <div class="text-gray-400">{{ \Illuminate\Support\Carbon::parse($log->reviewed_at)->format('d M Y') }}</div>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@empty
<div class="bg-white border border-[#e0daf5] rounded-xl p-8 text-center text-gray-400">
  <i class="ti ti-clipboard-off text-3xl mb-2 block"></i>
  No task logs have been recorded for the active cycle yet.
</div>
@endforelse

@endif

@endsection

@section('scripts')
<script>
function filterByStaff() {
  const selected = document.getElementById('staff-filter').value;
  const groups = document.querySelectorAll('[id^="staff-group-"]');
  groups.forEach(group => {
    if (!selected || group.id === selected) {
      group.style.display = '';
    } else {
      group.style.display = 'none';
    }
  });
}
</script>
@endsection
