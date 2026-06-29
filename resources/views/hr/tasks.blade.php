@extends('layouts.app')
@section('title', 'All Task Logs')

@section('nav')
@foreach([['hr.dashboard','ti-chart-bar','HR Overview'],['hr.users','ti-users','Users'],['hr.assignments','ti-arrows-exchange','Assignments'],['hr.cycles','ti-calendar','Cycles'],['hr.tasks','ti-clipboard-list','Task Logs']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route) ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')
<div class="text-2xl font-bold text-gray-900 mb-1">Task logs</div>
<div class="text-sm text-gray-500 mb-5">All staff task logs for the {{ $cycle?->name ?? '—' }} cycle.</div>

@forelse($allStaff as $s)
@php $staffTasks = $tasks->get($s->id, collect()); @endphp
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#f5f0ff] px-5 py-3 flex items-center gap-3">
    <div class="w-8 h-8 bg-[#3C3489] rounded-full flex items-center justify-center text-white text-[11px] font-bold">
      {{ strtoupper(substr($s->name,0,1)) }}{{ strtoupper(substr(explode(' ',$s->name)[1]??'',0,1)) }}
    </div>
    <div>
      <div class="font-semibold text-[#3C3489] text-sm">{{ $s->name }}</div>
      <div class="text-xs text-gray-400">{{ $s->department }} · Supervisor: {{ $s->supervisor?->name ?? '—' }}</div>
    </div>
    <div class="ml-auto flex gap-3 text-xs text-gray-500">
      <span><strong>{{ $staffTasks->count() }}</strong> tasks</span>
      <span><strong>{{ $staffTasks->where('status','graded')->count() }}</strong> graded</span>
      @if($staffTasks->count())
      <span><strong>{{ round($staffTasks->avg('completion_percentage')) }}%</strong> avg completion</span>
      @endif
    </div>
  </div>
  @if($staffTasks->count())
  <table class="w-full text-sm">
    <thead><tr class="border-b border-[#f0edf8]">
      <th class="text-left py-2 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Task</th>
      <th class="text-left py-2 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Category</th>
      <th class="text-left py-2 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Date</th>
      <th class="text-center py-2 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">% Done</th>
      <th class="text-center py-2 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Self</th>
      <th class="text-center py-2 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Sup.</th>
      <th class="text-left py-2 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Status</th>
    </tr></thead>
    <tbody>
      @foreach($staffTasks as $task)
      <tr class="border-b border-[#f0edf8] last:border-0 hover:bg-[#faf8ff]">
        <td class="py-2.5 px-4">
          <div class="font-medium text-gray-800 text-sm">{{ $task->title }}</div>
          @if($task->details)
          <div class="text-xs text-gray-400 truncate max-w-xs">{{ $task->details }}</div>
          @endif
        </td>
        <td class="py-2.5 px-4">
          <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
            {{ $task->category==='KRA'?'bg-[#eeedfe] text-[#3C3489]':($task->category==='Innovation'?'bg-amber-100 text-amber-700':'bg-green-100 text-green-700') }}">
            {{ $task->category }}
          </span>
        </td>
        <td class="py-2.5 px-4 text-xs text-gray-500">{{ $task->date->format('d M Y') }}</td>
        <td class="py-2.5 px-4 text-center">
          @php $pct = $task->completion_percentage; @endphp
          <div class="flex flex-col items-center gap-0.5">
            <span class="text-xs font-bold {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
            <div class="w-12 bg-gray-100 rounded-full h-1">
              <div class="h-1 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
            </div>
          </div>
        </td>
        <td class="py-2.5 px-4 text-center">
          <span class="text-xs font-semibold text-[#3C3489]">{{ $task->self_score ?? '—' }}</span>
        </td>
        <td class="py-2.5 px-4 text-center">
          <span class="text-xs font-semibold text-green-700">{{ $task->supervisor_score ?? '—' }}</span>
        </td>
        <td class="py-2.5 px-4">
          <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
            {{ $task->status==='graded'?'bg-green-100 text-green-700':($task->status==='awaiting'?'bg-amber-100 text-amber-700':'bg-gray-100 text-gray-500') }}">
            {{ $task->getStatusLabel() }}
          </span>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div class="py-5 text-center text-sm text-gray-400 italic">No tasks logged yet.</div>
  @endif
</div>
@empty
<div class="bg-white border border-[#e0daf5] rounded-xl p-8 text-center">
  <div class="text-sm text-gray-400">No staff found.</div>
</div>
@endforelse
@endsection
