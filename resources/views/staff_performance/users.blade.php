@extends('layouts.app')
@section('title', 'Users')

@section('nav')
@foreach([
  ['hr.dashboard', 'ti-chart-bar', 'Staff Performance Overview'],
  ['hr.users', 'ti-users', 'Users'],
  ['hr.assignments', 'ti-arrows-exchange', 'Assignments'],
  ['hr.cycles', 'ti-calendar', 'Cycles'],
  ['hr.tasks', 'ti-clipboard-list', 'Task Logs']
] as [$route, $icon, $label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route) ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')
{{-- Header --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Users</h1>
    <p class="text-sm text-gray-500">Create logins for staff, supervisors, and Staff Performance.</p>
  </div>

  <div class="flex items-center gap-3">
    {{-- Search Bar --}}
    <form method="GET" action="{{ route('hr.users') }}" class="relative min-w-[240px]">
      <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base"></i>
      <input
        type="text"
        id="user-search-input"
        name="search"
        value="{{ request('search') }}"
        placeholder="Search users..."
        onkeyup="filterTable()"
        class="w-full pl-9 pr-3 py-2 bg-white border border-[#e0daf5] rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#7F77DD] transition"
      >
    </form>

    {{-- Add User Button --}}
    <button onclick="openModal('add-user-modal')" class="inline-flex items-center gap-2 bg-[#3C3489] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#26215C] transition shrink-0">
      <i class="ti ti-plus"></i> Add user
    </button>
  </div>
</div>

{{-- Users Table --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden shadow-sm">
  <div class="overflow-x-auto">
    <table class="w-full text-sm" id="users-table">
      <thead>
        <tr class="bg-[#f5f0ff]">
          <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Name</th>
          <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Username</th>
          <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Role</th>
          <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Department</th>
          <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Supervisor</th>
          <th class="py-2.5 px-4"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#f0edf8]">
        @forelse($users as $u)
        <tr class="user-row hover:bg-gray-50/50 transition">
          <td class="py-3 px-4">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 bg-[#eeedfe] rounded-full flex items-center justify-center text-[11px] font-bold text-[#3C3489]">
                {{ strtoupper(substr($u->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $u->name)[1] ?? '', 0, 1)) }}
              </div>
              <span class="font-medium text-gray-900 user-name">{{ $u->name }}</span>
            </div>
          </td>
          <td class="py-3 px-4 text-[#534AB7] font-mono text-xs user-username">{{ $u->username }}</td>
          <td class="py-3 px-4">
            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full user-role
              {{ $u->role === 'staff_performance' ? 'bg-orange-100 text-orange-700' : ($u->role === 'supervisor' ? 'bg-[#eeedfe] text-[#3C3489]' : 'bg-gray-100 text-gray-600') }}">
              {{ strtoupper(str_replace('_', ' ', $u->role)) }}
            </span>
          </td>
          <td class="py-3 px-4 text-gray-500 user-dept">{{ $u->department ?? '—' }}</td>
          <td class="py-3 px-4 text-gray-500">{{ $u->supervisor?->name ?? '—' }}</td>
          <td class="py-3 px-4">
            <div class="flex items-center justify-end gap-2">
              <button
                type="button"
                onclick="editUser({{ json_encode($u) }}, '{{ route('hr.users.update', $u) }}')"
                class="text-xs text-[#3C3489] hover:text-[#26215C] p-1.5 rounded-lg hover:bg-[#eeedfe] transition"
                title="Edit User"
              >
                <i class="ti ti-edit text-base"></i>
              </button>

              @if($u->id !== auth()->id())
              <button
                type="button"
                onclick="openDeleteModal('{{ $u->name }}', '{{ route('hr.users.delete', $u) }}')"
                class="text-xs text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition"
                title="Delete User"
              >
                <i class="ti ti-trash text-base"></i>
              </button>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr id="no-records-row">
          <td colspan="6" class="py-8 text-center text-gray-400">No users found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if(method_exists($users, 'hasPages') && $users->hasPages())
  <div class="px-4 py-3 bg-white border-t border-[#f0edf8] flex items-center justify-between text-xs text-gray-500">
    <div>
      Showing <span class="font-medium text-gray-700">{{ $users->firstItem() }}</span> to <span class="font-medium text-gray-700">{{ $users->lastItem() }}</span> of <span class="font-medium text-gray-700">{{ $users->total() }}</span> results
    </div>
    <div>
      {{ $users->links() }}
    </div>
  </div>
  @endif
</div>

{{-- Add User Modal --}}
<div id="add-user-modal" class="hidden fixed inset-0 bg-[#3C3489]/40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
    <div class="text-lg font-semibold mb-1">Add user</div>
    <div class="text-sm text-gray-400 mb-5">Create a sign-in for a new staff, supervisor, or Staff Performance member.</div>
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
          <select name="role" id="add-role-select" onchange="toggleSupervisorField('add-role-select', 'add-sup-field')" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
            <option value="staff">Staff</option>
            <option value="supervisor">Supervisor</option>
            <option value="staff_performance">Staff Performance</option>
          </select>
        </div>
        <div id="add-sup-field">
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
        <button type="button" onclick="closeModal('add-user-modal')" class="px-4 py-2 text-sm border border-[#e0daf5] rounded-lg text-gray-500 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-[#3C3489] text-white rounded-lg hover:bg-[#26215C]">Create user</button>
      </div>
    </form>
  </div>
</div>

{{-- Single Shared Edit User Modal --}}
<div id="edit-user-modal" class="hidden fixed inset-0 bg-[#3C3489]/40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
    <div class="text-lg font-semibold mb-1">Edit user</div>
    <div class="text-sm text-gray-400 mb-5">Update user account details.</div>
    <form id="edit-user-form" method="POST" action="" class="space-y-4">
      @csrf
      @method('PUT')
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Full name</label>
        <input id="edit-name" name="name" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" required>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Username</label>
          <input id="edit-username" name="username" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Password</label>
          <input type="password" name="password" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Leave blank to keep current">
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Department / Title</label>
        <input id="edit-department" name="department" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Designation</label>
        <input id="edit-designation" name="designation" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Role</label>
          <select id="edit-role-select" name="role" onchange="toggleSupervisorField('edit-role-select', 'edit-sup-field')" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
            <option value="staff">Staff</option>
            <option value="supervisor">Supervisor</option>
            <option value="staff_performance">Staff Performance</option>
          </select>
        </div>
        <div id="edit-sup-field">
          <label class="block text-xs font-medium text-gray-500 mb-1">Supervisor</label>
          <select id="edit-supervisor-id" name="supervisor_id" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
            <option value="">Pick one</option>
            @foreach($supervisors as $sup)
            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="flex gap-3 justify-end pt-2">
        <button type="button" onclick="closeModal('edit-user-modal')" class="px-4 py-2 text-sm border border-[#e0daf5] rounded-lg text-gray-500 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm bg-[#3C3489] text-white rounded-lg hover:bg-[#26215C]">Save changes</button>
      </div>
    </form>
  </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="delete-user-modal" class="hidden fixed inset-0 bg-[#3C3489]/40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl text-center">
    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-xl">
      <i class="ti ti-trash"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Delete User</h3>
    <p class="text-sm text-gray-500 mb-6">Are you sure you want to delete <span id="delete-user-name" class="font-medium text-gray-900"></span>? This action cannot be undone.</p>

    <form id="delete-user-form" method="POST" action="">
      @csrf
      @method('DELETE')
      <div class="flex gap-3 justify-center">
        <button type="button" onclick="closeModal('delete-user-modal')" class="w-full px-4 py-2 text-sm border border-[#e0daf5] rounded-lg text-gray-500 hover:bg-gray-50 transition">Cancel</button>
        <button type="submit" class="w-full px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Delete</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) {
  document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
  document.getElementById(id).classList.add('hidden');
}

function toggleSupervisorField(selectId, fieldId) {
  const roleSelect = document.getElementById(selectId);
  const supField = document.getElementById(fieldId);
  supField.style.display = roleSelect.value === 'staff' ? 'block' : 'none';
}

function editUser(user, routeUrl) {
  document.getElementById('edit-user-form').action = routeUrl;
  document.getElementById('edit-name').value = user.name || '';
  document.getElementById('edit-username').value = user.username || '';
  document.getElementById('edit-department').value = user.department || '';
  document.getElementById('edit-designation').value = user.designation || '';

  const roleSelect = document.getElementById('edit-role-select');
  roleSelect.value = user.role;

  document.getElementById('edit-supervisor-id').value = user.supervisor_id || '';

  toggleSupervisorField('edit-role-select', 'edit-sup-field');
  openModal('edit-user-modal');
}

function openDeleteModal(userName, deleteUrl) {
  document.getElementById('delete-user-name').textContent = userName;
  document.getElementById('delete-user-form').action = deleteUrl;
  openModal('delete-user-modal');
}

function filterTable() {
  const query = document.getElementById('user-search-input').value.toLowerCase();
  const rows = document.querySelectorAll('.user-row');

  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(query) ? '' : 'none';
  });
}
</script>
@endsection
