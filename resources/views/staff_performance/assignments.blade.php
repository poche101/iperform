{{-- resources/views/staff_performance/assignments.blade.php --}}
@extends('layouts.app')
@section('title', 'Staff Assignments')

@section('nav')
@foreach([['hr.dashboard','ti-chart-bar','Staff Performance Overview'],['hr.users','ti-users','Users'],['hr.assignments','ti-arrows-exchange','Assignments'],['hr.cycles','ti-calendar','Cycles'],['hr.tasks','ti-clipboard-list','Task Logs']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route) ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')
<div class="text-2xl font-bold text-gray-900 mb-1">Staff Assignments</div>
<div class="text-sm text-gray-500 mb-5">Reassign staff to supervisors. Changes affect the supervisor's review queue immediately.</div>

@php $unassigned = $allStaff->whereNull('supervisor_id'); @endphp
@if($unassigned->count())
<div class="mb-4 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-700 flex items-center gap-2">
  <i class="ti ti-alert-circle"></i>
  <strong>{{ $unassigned->count() }} staff member(s) unassigned.</strong> Please assign a supervisor.
</div>
@endif

<form method="GET" action="{{ route('hr.assignments') }}" class="mb-4 flex items-center gap-2">
  <div class="relative flex-1 max-w-sm">
    <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
    <input
      type="text"
      name="search"
      value="{{ request('search') }}"
      placeholder="Search by name or department..."
      class="w-full border border-[#e0daf5] rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-[#7F77DD]"
    >
  </div>
  <button type="submit" class="text-xs bg-[#3C3489] text-white px-3 py-2 rounded-lg hover:bg-[#26215C] transition">Search</button>
  @if(request('search'))
  <a href="{{ route('hr.assignments') }}" class="text-xs text-gray-500 px-3 py-2 rounded-lg border border-[#e0daf5] hover:bg-[#f5f0ff] transition">Clear</a>
  @endif
</form>

<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden">
  <table class="w-full text-sm">
    <thead>
      <tr class="bg-[#f5f0ff]">
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Staff Member</th>
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Department</th>
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Supervisor</th>
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($allStaff as $s)
      <tr class="border-b border-[#f0edf8] last:border-0">
        <td class="py-3 px-4">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-[#eeedfe] rounded-full flex items-center justify-center text-[11px] font-bold text-[#3C3489]">
              {{ strtoupper(substr($s->name,0,1)) }}{{ strtoupper(substr(explode(' ',$s->name)[1]??'',0,1)) }}
            </div>
            <div>
              <div class="font-medium text-gray-900">{{ $s->name }}</div>
              <div class="text-xs text-gray-400">{{ $s->designation }}</div>
            </div>
          </div>
        </td>
        <td class="py-3 px-4 text-gray-500">{{ $s->department }}</td>
        <td class="py-3 px-4">
          <form method="POST" action="{{ route('hr.assignments.update', $s) }}" class="flex items-center gap-2">
            @csrf
            <select name="supervisor_id" class="border border-[#e0daf5] rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:border-[#7F77DD]">
              @foreach($supervisors as $sup)
              <option value="{{ $sup->id }}" {{ $s->supervisor_id == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
              @endforeach
            </select>
            <button type="submit" class="text-xs bg-[#3C3489] text-white px-3 py-1.5 rounded-lg hover:bg-[#26215C] transition">Update</button>
          </form>
        </td>
        <td class="py-3 px-4">
          @if($s->supervisor)
          <span class="text-xs text-green-600 flex items-center gap-1"><i class="ti ti-check"></i> Assigned</span>
          @else
          <span class="text-xs text-amber-600 flex items-center gap-1"><i class="ti ti-alert-circle"></i> Unassigned</span>
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="4" class="py-6 px-4 text-center text-gray-400 text-sm">No staff members found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($allStaff->hasPages())
<div class="mt-4">
  {{ $allStaff->appends(request()->query())->links() }}
</div>
@endif
@endsection
