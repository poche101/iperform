@extends('layouts.app')
@section('title', 'Appraisal Cycles')

@section('nav')
@foreach([['hr.dashboard','ti-chart-bar','HR Overview'],['hr.users','ti-users','Users'],['hr.assignments','ti-arrows-exchange','Assignments'],['hr.cycles','ti-calendar','Cycles'],['hr.tasks','ti-clipboard-list','Task Logs']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route) ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')
<div class="flex items-start justify-between mb-5">
  <div>
    <div class="text-2xl font-bold text-gray-900">Appraisal Cycles</div>
    <div class="text-sm text-gray-500">Create and manage monthly appraisal periods.</div>
  </div>
  <button onclick="document.getElementById('add-cycle-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-[#3C3489] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#26215C] transition">
    <i class="ti ti-plus"></i> New cycle
  </button>
</div>

<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden">
  <table class="w-full text-sm">
    <thead>
      <tr class="bg-[#f5f0ff]">
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Cycle Name</th>
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Period</th>
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Deadline</th>
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse($cycles as $cycle)
      <tr class="border-b border-[#f0edf8] last:border-0">
        <td class="py-3 px-4 font-medium text-gray-900">{{ $cycle->name }}</td>
        <td class="py-3 px-4 text-gray-500">{{ $cycle->start_date->format('d M Y') }} – {{ $cycle->end_date->format('d M Y') }}</td>
        <td class="py-3 px-4 text-gray-500">{{ $cycle->deadline->format('d M Y') }}</td>
        <td class="py-3 px-4">
          @if($cycle->is_active)
          <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Active
          </span>
          @else
          <span class="text-xs text-gray-400 px-2.5 py-1 bg-gray-100 rounded-full">Closed</span>
          @endif
        </td>
      </tr>
      @empty
      <tr><td colspan="4" class="py-8 text-center text-gray-400 italic">No cycles created yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Add cycle modal --}}
<div id="add-cycle-modal" class="hidden fixed inset-0 bg-[#3C3489]/40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md">
    <div class="text-lg font-semibold mb-1">New appraisal cycle</div>
    <div class="text-sm text-gray-400 mb-5">Set the period, deadline and activate when ready.</div>
    <form method="POST" action="{{ route('hr.cycles.store') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Cycle Name</label>
        <input name="name" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="e.g. July 2026" required>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
          <input type="date" name="start_date" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
          <input type="date" name="end_date" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" required>
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Submission Deadline</label>
        <input type="date" name="deadline" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" required>
      </div>
      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded border-[#e0daf5]">
        <label for="is_active" class="text-sm text-gray-600">Set as active cycle (deactivates current active cycle)</label>
      </div>
      <div class="flex gap-3 justify-end pt-2">
        <button type="button" onclick="document.getElementById('add-cycle-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-[#e0daf5] rounded-lg text-gray-500 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-[#3C3489] text-white rounded-lg hover:bg-[#26215C]">Create cycle</button>
      </div>
    </form>
  </div>
</div>
@endsection
