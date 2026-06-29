@extends('layouts.app')
@section('title', 'Staff Performance Overview')

@section('nav')
@foreach([['hr.dashboard','ti-chart-bar','Staff Performance Overview'],['hr.users','ti-users','Users'],['hr.assignments','ti-arrows-exchange','Assignments'],['hr.cycles','ti-calendar','Cycles'],['hr.tasks','ti-clipboard-list','Task Logs']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route) ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')
<div class="text-2xl font-bold text-gray-900 mb-1">Staff Performance overview</div>
<div class="text-sm text-gray-500 mb-5">Org-wide insights for the {{ $cycle?->name ?? '—' }} cycle.</div>

<div class="grid grid-cols-4 gap-3 mb-5">
 @foreach([['ti-users','Total staff',$stats['total'] ?? 0],['ti-trending-up','Avg. score',round($stats['avg_score'] ?? 0).'%'],['ti-award','Approved',$stats['approved'] ?? 0],['ti-alert-triangle','With Staff Performance',$stats['with_staff_performance'] ?? 0]] as [$icon,$label,$val])
  <div class="bg-white border border-[#e0daf5] rounded-xl p-4">
    <div class="w-9 h-9 bg-[#eeedfe] rounded-lg flex items-center justify-center mb-2"><i class="ti {{ $icon }} text-[#3C3489] text-lg"></i></div>
    <div class="text-2xl font-bold text-gray-900">{{ $val }}</div>
    <div class="text-xs text-gray-400 mt-0.5">{{ $label }}</div>
  </div>
  @endforeach
</div>

<div class="bg-white border border-[#e0daf5] rounded-xl p-5">
  <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Per-staff insights</div>
  <table class="w-full text-sm">
    <thead>
      <tr class="bg-[#f5f0ff]">
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Staff</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Department</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Status</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Score</th>
        <th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Grade</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($allStaff as $s)
      @php $a = $appraisals[$s->id] ?? null; @endphp
      <tr class="border-b border-[#f0edf8] hover:bg-[#faf8ff]">
        <td class="py-3 px-3">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-[#eeedfe] rounded-full flex items-center justify-center text-[11px] font-bold text-[#3C3489]">
              {{ strtoupper(substr($s->name,0,1)) }}{{ strtoupper(substr(explode(' ',$s->name)[1]??'',0,1)) }}
            </div>
            <span class="font-medium text-gray-900">{{ $s->name }}</span>
          </div>
        </td>
        <td class="py-3 px-3 text-gray-500">{{ $s->department }}</td>
        <td class="py-3 px-3">
          @php $status = $a?->status ?? 'not started'; @endphp
          <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
            {{ $status==='approved' ? 'bg-green-100 text-green-700' : ($status==='with_staff_performance' ? 'bg-orange-100 text-orange-700' : ($status==='submitted' ? 'bg-[#eeedfe] text-[#3C3489]' : 'bg-gray-100 text-gray-500')) }}">
            {{ ucfirst(str_replace('_',' ',$status)) }}
          </span>
        </td>
        <td class="py-3 px-3 font-bold text-[#3C3489]">{{ $a?->staff_performance_overall ? $a->staff_performance_overall.'%' : '—' }}</td>
        <td class="py-3 px-3 font-bold text-[#3C3489] text-lg">{{ $a?->staff_performance_grade ?? '—' }}</td>
        <td class="py-3 px-3">
          @if($a)
          <a href="{{ route('hr.appraisal.show', $a) }}" class="inline-flex items-center gap-1 text-xs border border-[#7F77DD] text-[#3C3489] px-3 py-1 rounded-lg hover:bg-[#eeedfe] transition">
            <i class="ti ti-eye text-sm"></i> View
          </a>
          @else
          <span class="text-xs text-gray-300">No appraisal</span>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
