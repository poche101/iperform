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
    {{-- Search Bar (searches the database, so it covers rows not yet loaded) --}}
    <form method="GET" action="{{ route('hr.users') }}" class="relative min-w-[240px]">
      <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base"></i>
      <input
        type="text"
        id="user-search-input"
        name="search"
        value="{{ request('search') }}"
        placeholder="Search users..."
        autocomplete="off"
        class="w-full pl-9 pr-3 py-2 bg-white border border-[#e0daf5] rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#7F77DD] transition"
      >
    </form>

    {{-- Add User Button --}}
    <button onclick="openAddModal()" class="inline-flex items-center gap-2 bg-[#3C3489] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#26215C] transition shrink-0">
      <i class="ti ti-plus"></i> Add user
    </button>
  </div>
</div>

@php
  $sections = [
    [
      'key'      => 'supervisors',
      'title'    => 'Supervisors',
      'subtitle' => 'People who review staff task logs and appraisals',
      'icon'     => 'ti-user-check',
      'page'     => $supervisorUsers,
      'supLabel' => 'Reports to',
      'empty'    => 'No supervisors found.',
    ],
    [
      'key'      => 'staff',
      'title'    => 'Staff',
      'subtitle' => 'Staff and Staff Performance accounts',
      'icon'     => 'ti-users',
      'page'     => $staffUsers,
      'supLabel' => 'Supervisor',
      'empty'    => 'No staff found.',
    ],
  ];
@endphp

@foreach($sections as $s)
@php
  $page      = $s['page'];
  $remaining = max(0, $page->total() - $page->currentPage() * $page->perPage());
@endphp
<div class="mb-6">
  <div class="flex items-center justify-between mb-2">
    <div class="flex items-center gap-2">
      <i class="ti {{ $s['icon'] }} text-lg text-[#3C3489]"></i>
      <h2 class="text-base font-semibold text-gray-900">{{ $s['title'] }}</h2>
      <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">{{ $page->total() }}</span>
    </div>
    <div class="text-xs text-gray-400 hidden sm:block">{{ $s['subtitle'] }}</div>
  </div>

  <div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-sm" id="{{ $s['key'] }}-table">
        <thead>
          <tr class="bg-[#f5f0ff]">
            <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Name</th>
            <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Username</th>
            <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Role</th>
            <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">Department</th>
            <th class="text-left py-2.5 px-4 text-[11px] text-[#534AB7] font-medium uppercase tracking-wide">{{ $s['supLabel'] }}</th>
            <th class="py-2.5 px-4"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#f0edf8]">
          @forelse($page as $u)
            @include('staff_performance._user_rows', ['rows' => [$u]])
          @empty
          <tr>
            <td colspan="6" class="py-8 text-center text-gray-400">{{ $s['empty'] }}</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Load more (a real link, so it still works without JS) --}}
    <div class="load-more-wrap px-4 py-3 border-t border-[#f0edf8] text-center {{ $page->hasMorePages() ? '' : 'hidden' }}">
      <a href="{{ $page->nextPageUrl() }}" data-section="{{ $s['key'] }}"
         class="load-more-link inline-flex items-center gap-1 text-sm font-medium text-[#3C3489] hover:underline">
        <i class="ti ti-chevron-down"></i> Load more <span class="remaining text-gray-400 font-normal">({{ $remaining }} more)</span>
      </a>
    </div>
  </div>
</div>
@endforeach

{{-- Add User Modal --}}
<div id="add-user-modal" class="hidden fixed inset-0 bg-[#3C3489]/40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">
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
          <select name="role" id="add-role-select" onchange="toggleRoleFields('add')" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
            <option value="staff">Staff</option>
            <option value="supervisor">Supervisor</option>
            <option value="staff_performance">Staff Performance</option>
          </select>
        </div>
        <div id="add-sup-field">
          <label class="block text-xs font-medium text-gray-500 mb-1">Supervisor</label>
          <select id="add-supervisor-id" name="supervisor_id" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
            <option value="">— None —</option>
            @foreach($supervisors as $sup)
            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Supervisors only: they also have their own tasks and appraisal --}}
      <div id="add-staff-field" class="hidden bg-[#faf8ff] border border-[#e0daf5] rounded-lg px-3 py-2.5">
        <input type="hidden" name="is_staff" value="0">
        <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
          <input type="checkbox" id="add-is-staff" name="is_staff" value="1" class="mt-0.5 accent-[#3C3489]">
          <span>
            Also does staff work
            <span class="block text-xs text-gray-400">They log their own tasks and appraisal, reviewed by the supervisor chosen above.</span>
          </span>
        </label>
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
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">
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
          <select id="edit-role-select" name="role" onchange="toggleRoleFields('edit')" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
            <option value="staff">Staff</option>
            <option value="supervisor">Supervisor</option>
            <option value="staff_performance">Staff Performance</option>
          </select>
        </div>
        <div id="edit-sup-field">
          <label class="block text-xs font-medium text-gray-500 mb-1">Supervisor</label>
          <select id="edit-supervisor-id" name="supervisor_id" class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD]">
            <option value="">— None —</option>
            @foreach($supervisors as $sup)
            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Supervisors only: they also have their own tasks and appraisal --}}
      <div id="edit-staff-field" class="hidden bg-[#faf8ff] border border-[#e0daf5] rounded-lg px-3 py-2.5">
        <input type="hidden" name="is_staff" value="0">
        <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
          <input type="checkbox" id="edit-is-staff" name="is_staff" value="1" class="mt-0.5 accent-[#3C3489]">
          <span>
            Also does staff work
            <span class="block text-xs text-gray-400">They log their own tasks and appraisal, reviewed by the supervisor chosen above.</span>
          </span>
        </label>
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

/**
 * Role drives which fields show (prefix is 'add' or 'edit'):
 *  - Staff:             supervisor dropdown
 *  - Supervisor:        supervisor dropdown (who they report to) + "also does staff work" checkbox
 *  - Staff Performance: neither
 * Hidden fields are cleared so a stale value is never submitted.
 */
function toggleRoleFields(prefix) {
  const role     = document.getElementById(prefix + '-role-select').value;
  const supField = document.getElementById(prefix + '-sup-field');
  const supSel   = document.getElementById(prefix + '-supervisor-id');
  const stfField = document.getElementById(prefix + '-staff-field');
  const stfBox   = document.getElementById(prefix + '-is-staff');

  const showSupervisor = role === 'staff' || role === 'supervisor';
  supField.style.display = showSupervisor ? 'block' : 'none';
  if (!showSupervisor) supSel.value = '';

  const isSupervisor = role === 'supervisor';
  stfField.classList.toggle('hidden', !isSupervisor);
  if (!isSupervisor) stfBox.checked = false;
}

function openAddModal() {
  toggleRoleFields('add');
  openModal('add-user-modal');
}

function editUser(user, routeUrl) {
  document.getElementById('edit-user-form').action = routeUrl;
  document.getElementById('edit-name').value = user.name || '';
  document.getElementById('edit-username').value = user.username || '';
  document.getElementById('edit-department').value = user.department || '';
  document.getElementById('edit-designation').value = user.designation || '';

  document.getElementById('edit-role-select').value = user.role;

  const supSelect = document.getElementById('edit-supervisor-id');
  supSelect.value = user.supervisor_id || '';

  // A person cannot be their own supervisor
  Array.from(supSelect.options).forEach(o => {
    o.disabled = o.hidden = String(o.value) === String(user.id);
  });

  document.getElementById('edit-is-staff').checked = !!user.is_staff;

  toggleRoleFields('edit');
  openModal('edit-user-modal');
}

function openDeleteModal(userName, deleteUrl) {
  document.getElementById('delete-user-name').textContent = userName;
  document.getElementById('delete-user-form').action = deleteUrl;
  openModal('delete-user-modal');
}

// Search: runs on the server (so it covers every user, not just loaded rows).
// Submits shortly after typing stops, and Enter also submits.
(function () {
  const input = document.getElementById('user-search-input');
  let timer;
  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => input.form.submit(), 500);
  });
  if (input.value) {
    input.focus();
    input.setSelectionRange(input.value.length, input.value.length);
  }
})();

// "Load more": fetch the next 10 rows for that section and append them
document.querySelectorAll('.load-more-link').forEach(link => {
  link.addEventListener('click', async function (e) {
    e.preventDefault();
    const section = link.dataset.section;
    const url = new URL(link.href, window.location.origin);
    url.searchParams.set('section', section);

    link.classList.add('opacity-50', 'pointer-events-none');
    try {
      const res = await fetch(url.pathname + url.search, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error('Request failed');
      const data = await res.json();

      document.querySelector('#' + section + '-table tbody').insertAdjacentHTML('beforeend', data.html);

      if (data.next) {
        link.href = data.next;
        link.querySelector('.remaining').textContent = '(' + data.remaining + ' more)';
      } else {
        link.closest('.load-more-wrap').classList.add('hidden');
      }
    } catch (err) {
      window.location.href = link.href; // fall back to a normal page load
    } finally {
      link.classList.remove('opacity-50', 'pointer-events-none');
    }
  });
});
</script>
@endsection
