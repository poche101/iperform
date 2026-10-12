@extends('layouts.app')
@section('title', 'HR — ' . ($supervisorUser?->name ?? 'No supervisor') . ' · Task Logs')

@section('nav')
@foreach([['hr.dashboard','ti-chart-bar','HR Overview'],['hr.users','ti-users','Users'],['hr.assignments','ti-arrows-exchange','Assignments'],['hr.cycles','ti-calendar','Cycles'],['hr.tasks','ti-clipboard-list','Task Logs']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route, $route.'.*') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')

@php
  $supName   = $supervisorUser?->name ?? 'No supervisor assigned';
  $supParam  = $supervisorUser?->id ?? 'none';
  $allLogs   = $tasks->flatten(1);
@endphp

<a href="{{ route('hr.tasks', ['cycle' => $cycle?->id]) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-[#3C3489] mb-3">
  <i class="ti ti-arrow-left"></i> All supervisors
</a>

<div class="flex items-center justify-between gap-3 flex-wrap mb-5">
  <div>
    <div class="text-xl font-bold text-gray-800">{{ $supName }}</div>
    <div class="text-sm text-gray-500">
      @if($supervisorUser)
        {{ $supervisorUser->department ?? '—' }} · {{ $supervisorUser->designation ?? '—' }} ·
      @endif
      {{ $cycle?->name ?? 'No cycle' }}
    </div>
  </div>

  @if($cycles->count() > 1)
  <form method="GET" action="{{ route('hr.tasks.supervisor', $supParam) }}">
    <select name="cycle" onchange="this.form.submit()" class="border border-[#e0daf5] rounded-lg px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:border-[#7F77DD]">
      @foreach($cycles as $c)
      <option value="{{ $c->id }}" @selected($cycle && $cycle->id === $c->id)>{{ $c->name }}{{ $c->is_active ? ' (active)' : '' }}</option>
      @endforeach
    </select>
  </form>
  @endif
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Staff</div>
    <div class="text-2xl font-bold text-[#3C3489]">{{ $team->count() }}</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Total logs</div>
    <div class="text-2xl font-bold text-[#3C3489]">{{ $allLogs->count() }}</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Awaiting review</div>
    <div class="text-2xl font-bold text-amber-600">{{ $allLogs->where('status', 'awaiting')->count() }}</div>
  </div>
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="text-xs text-gray-400 mb-1">Graded</div>
    <div class="text-2xl font-bold text-green-600">{{ $allLogs->where('status', 'graded')->count() }}</div>
  </div>
</div>

@forelse($team as $staffMember)
@php $staffTasks = $tasks->get($staffMember->id, collect()); @endphp
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div>
      <div class="font-semibold text-[#3C3489] text-sm">{{ $staffMember->name }}</div>
      <div class="text-xs text-gray-500">{{ $staffMember->department ?? '—' }} · {{ $staffMember->designation ?? '—' }}</div>
    </div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">{{ $staffTasks->count() }} {{ Str::plural('log', $staffTasks->count()) }}</span>
  </div>

  @if($staffTasks->isEmpty())
  <div class="p-5 text-sm text-gray-400">No task logs for this cycle.</div>
  @else
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
        @foreach($staffTasks as $log)
        @php
          $isIdeas = $log->category === 'Ideas, Innovation & Outstanding Contribution';
          $catCls  = $log->category === 'KRA'
              ? 'bg-[#eeedfe] text-[#3C3489]'
              : ($isIdeas ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700');
          $catLbl  = $isIdeas ? 'Ideas & Innovation' : $log->category;
          $pct     = $log->completion_percentage ?? 0;
        @endphp
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-500 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($log->date)->format('d M Y') }}</td>
          <td class="py-2 px-3"><span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $catCls }}">{{ $catLbl }}</span></td>
          <td class="py-2 px-3 font-medium text-gray-700">
            {{ $log->title }}
            @if($log->details)
            <div class="text-xs text-gray-400 mt-0.5">{{ Str::limit($log->details, 90) }}</div>
            @endif
          </td>
          <td class="py-2 px-3 text-center">
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
  @endif
</div>
@empty
<div class="bg-white border border-[#e0daf5] rounded-xl p-8 text-center text-gray-400">
  <i class="ti ti-users-off text-3xl mb-2 block"></i>
  No staff are assigned to this supervisor.
</div>
@endforelse

@endsection
