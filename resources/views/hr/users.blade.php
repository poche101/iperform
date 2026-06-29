@extends('layouts.app')
@section('title', 'Users')

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
    <div class="text-2xl font-bold text-gray-900">Users</div>
    <div class="text-sm text-gray-500">Create logins for staff, supervisors and HR.</div>
  </div>
  <button onclick="document.getElementById('add-user-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-[#3C3489] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#26215C] transition">
    <i class="ti ti-plus"></i> Add user
  </button>
</div>

<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden">
  <table class="w-full text-sm">
    <thead>
      <tr class="bg-[#f5f0ff]">
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Name</th>
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Username</th>
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Role</th>
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Department</th>
        <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Supervisor</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $u)
      <tr class="border-b border-[#f0edf8] last:border-0">
        <td class="py-3 px-4">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-[#eeedfe] rounded-full flex items-center justify-center text-[11px] font-bold text-[#3C3489]">
              {{ strtoupper(substr($u->name,0,1)) }}{{ strtoupper(substr(explode(' ',$u->name)[1]??'',0,1)) }}
            </div>
            <span class="font-medium text-gray-900">{{ $u->name }}</span>
          </div>
        </td>
        <td class="py-3 px-4 text-[#534AB7]">{{ $u->username }}</td>
        <td class="py-3 px-4">
          <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
            {{ $u->role==='hr' ? 'bg-orange-100 text-orange-700' : ($u->role==='supervisor' ? 'bg-[#eeedfe] text-[#3C3489]' : 'bg-gray-100 text-gray-500') }}">
            {{ strtoupper($u->role) }}
          </span>
        </td>
        <td class="py-3 px-4 text-gray-500">{{ $u->department ?? '—' }}</td>
        <td class="py-3 px-4 text-gray-500">{{ $u->supervisor?->name ?? '—' }}</td>
        <td class="py-3 px-4">
          @if($u->id !== auth()->id())
          <form method="POST" action="{{ route('hr.users.delete', $u) }}" onsubmit="return confirm('Delete {{ $u->name }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition"><i class="ti ti-trash"></i></button>
          </form>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- Add user modal --}}
<div id="add-user-modal" class="hidden fixed inset-0 bg-[#3C3489]/40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md">
    <div class="text-lg font-semibold mb-1">Add user</div>
    <div class="text-sm text-gray-400 mb-5">Create a sign-in for a new staff, supervisor or HR member.</div>
    <form method="POST" action="{{ route('hr.users.store') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Full name</label>
        <input name="name" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Jane Doe" required>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Username</label>
          <input name="username" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="jane" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Password</label>
          <input type="password" name="password" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="••••••" required>
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Department / Title</label>
        <input name="department" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Media Team">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Designation</label>
        <input name="designation" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Web Developer">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Role</label>
          <select name="role" id="role-select" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
            <option value="staff">Staff</option>
            <option value="supervisor">Supervisor</option>
            <option value="hr">HR</option>
          </select>
        </div>
        <div id="sup-field">
          <label class="block text-xs font-medium text-gray-500 mb-1">Supervisor</label>
          <select name="supervisor_id" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
            <option value="">Pick one</option>
            @foreach($supervisors as $sup)
            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="flex gap-3 justify-end pt-2">
        <button type="button" onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="px-4 py-2 text-sm border border-[#e0daf5] rounded-lg text-gray-500 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-[#3C3489] text-white rounded-lg hover:bg-[#26215C]">Create user</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('role-select').addEventListener('change', function() {
  document.getElementById('sup-field').style.display = this.value === 'staff' ? '' : 'none';
});
</script>
@endsection
