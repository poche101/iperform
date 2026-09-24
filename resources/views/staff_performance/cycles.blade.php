@extends('layouts.app')
@section('title', 'Appraisal Cycles')

@section('nav')
@foreach([['hr.dashboard','ti-chart-bar','Staff Performance Overview'],['hr.users','ti-users','Users'],['hr.assignments','ti-arrows-exchange','Assignments'],['hr.cycles','ti-calendar','Cycles'],['hr.tasks','ti-clipboard-list','Task Logs']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route) ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')
{{-- Success, error + validation messages (remove any block your layout already renders) --}}
@if(session('success'))
<div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">
  <i class="ti ti-circle-check text-lg"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
  <i class="ti ti-alert-circle text-lg"></i> {{ session('error') }}
</div>
@endif
@if($errors->any())
<div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
  <i class="ti ti-alert-circle text-lg"></i> {{ $errors->first() }}
</div>
@endif

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
        <th class="text-right py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Actions</th>
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
        <td class="py-3 px-4">
          <div class="flex items-center justify-end gap-2">
            {{-- Extend deadline (min = later of: day after current deadline, today) --}}
            <button type="button"
                    onclick="openExtendModal(this)"
                    data-action="{{ route('hr.cycles.extend', $cycle) }}"
                    data-name="{{ $cycle->name }}"
                    data-current="{{ $cycle->deadline->format('d M Y') }}"
                    data-min="{{ $cycle->deadline->copy()->addDay()->max(now()->startOfDay())->toDateString() }}"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium border border-[#e0daf5] rounded-lg text-[#3C3489] hover:bg-[#f5f0ff] transition">
              <i class="ti ti-calendar-plus"></i> Extend
            </button>

            {{-- Delete cycle --}}
            @if($cycle->is_active)
            <button type="button" disabled
                    title="Activate another cycle before deleting this one"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg text-gray-300 cursor-not-allowed">
              <i class="ti ti-trash"></i> Delete
            </button>
            @else
            <button type="button"
                    onclick="openDeleteModal(this)"
                    data-action="{{ route('hr.cycles.destroy', $cycle) }}"
                    data-name="{{ $cycle->name }}"
                    data-count="{{ $cycle->appraisals_count }}"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium border border-red-200 rounded-lg text-red-600 hover:bg-red-50 transition">
              <i class="ti ti-trash"></i> Delete
            </button>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="5" class="py-8 text-center text-gray-400 italic">No cycles created yet.</td></tr>
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

{{-- Extend deadline modal --}}
<div id="extend-cycle-modal" class="hidden fixed inset-0 bg-[#3C3489]/40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md">
    <div class="text-lg font-semibold mb-1">Extend deadline</div>
    <div class="text-sm text-gray-400 mb-3">
      <span id="extend-cycle-name" class="font-medium text-gray-600"></span> is currently due on
      <span id="extend-cycle-current" class="font-medium text-gray-600"></span>.
    </div>

    <div class="mb-4 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-700">
      Extending makes this the <strong>active cycle</strong>. Any other active cycle will be closed, and staff will see this month's timer and tasks.
    </div>

    <form id="extend-cycle-form" method="POST" action="" class="space-y-4">
      @csrf
      @method('PATCH')
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">New Submission Deadline</label>
        <input type="date" name="deadline" id="extend-cycle-date" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" required>
      </div>
      <div class="flex gap-3 justify-end pt-2">
        <button type="button" onclick="closeModal('extend-cycle-modal')" class="px-4 py-2 text-sm border border-[#e0daf5] rounded-lg text-gray-500 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-[#3C3489] text-white rounded-lg hover:bg-[#26215C]">Extend deadline</button>
      </div>
    </form>
  </div>
</div>

{{-- Delete cycle modal --}}
<div id="delete-cycle-modal" class="hidden fixed inset-0 bg-[#3C3489]/40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md">
    <div class="flex items-center gap-2 text-lg font-semibold mb-1 text-red-600">
      <i class="ti ti-alert-triangle"></i> Delete cycle
    </div>
    <div class="text-sm text-gray-600 mb-2">
      Are you sure you want to delete <span id="delete-cycle-name" class="font-semibold"></span>?
    </div>
    <div id="delete-cycle-warning" class="hidden mb-2 px-3 py-2 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
      This will also permanently delete <span id="delete-cycle-count" class="font-semibold"></span> appraisal(s) and all task logs under this cycle, including any approved ones.
    </div>
    <div class="text-sm text-gray-400 mb-5">This cannot be undone.</div>
    <form id="delete-cycle-form" method="POST" action="">
      @csrf
      @method('DELETE')
      <div class="flex gap-3 justify-end">
        <button type="button" onclick="closeModal('delete-cycle-modal')" class="px-4 py-2 text-sm border border-[#e0daf5] rounded-lg text-gray-500 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">Delete cycle</button>
      </div>
    </form>
  </div>
</div>

<script>
  function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
  }

  function openExtendModal(btn) {
    document.getElementById('extend-cycle-form').action = btn.dataset.action;
    document.getElementById('extend-cycle-name').textContent = btn.dataset.name;
    document.getElementById('extend-cycle-current').textContent = btn.dataset.current;

    const dateInput = document.getElementById('extend-cycle-date');
    dateInput.min = btn.dataset.min;
    dateInput.value = btn.dataset.min;

    document.getElementById('extend-cycle-modal').classList.remove('hidden');
  }

  function openDeleteModal(btn) {
    document.getElementById('delete-cycle-form').action = btn.dataset.action;
    document.getElementById('delete-cycle-name').textContent = btn.dataset.name;

    const count = parseInt(btn.dataset.count, 10) || 0;
    document.getElementById('delete-cycle-count').textContent = count;
    document.getElementById('delete-cycle-warning').classList.toggle('hidden', count === 0);

    document.getElementById('delete-cycle-modal').classList.remove('hidden');
  }
</script>
@endsection
