@foreach($rows as $u)
<tr class="user-row hover:bg-gray-50/50 transition">
  <td class="py-3 px-4">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 bg-[#eeedfe] rounded-full flex items-center justify-center text-[11px] font-bold text-[#3C3489]">
        {{ strtoupper(substr($u->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $u->name)[1] ?? '', 0, 1)) }}
      </div>
      <span class="font-medium text-gray-900 user-name">{{ $u->name }}</span>
    </div>
  </td>
  <td class="py-3 px-4 text-[#534AB7] font-mono text-xs">{{ $u->username }}</td>
  <td class="py-3 px-4">
    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
      {{ $u->role === 'staff_performance' ? 'bg-orange-100 text-orange-700' : ($u->role === 'supervisor' ? 'bg-[#eeedfe] text-[#3C3489]' : 'bg-gray-100 text-gray-600') }}">
      {{ strtoupper(str_replace('_', ' ', $u->role)) }}
    </span>
    @if($u->role === 'supervisor' && $u->is_staff)
      <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700 ml-1">+ STAFF</span>
    @endif
  </td>
  <td class="py-3 px-4 text-gray-500">{{ $u->department ?? '—' }}</td>
  <td class="py-3 px-4 text-gray-500">{{ $u->supervisor?->name ?? '—' }}</td>
  <td class="py-3 px-4">
    <div class="flex items-center justify-end gap-2">
      <button type="button"
        onclick="editUser({{ json_encode($u) }}, '{{ route('hr.users.update', $u) }}')"
        class="text-xs text-[#3C3489] hover:text-[#26215C] p-1.5 rounded-lg hover:bg-[#eeedfe] transition" title="Edit User">
        <i class="ti ti-edit text-base"></i>
      </button>

      @if($u->id !== auth()->id())
      <button type="button"
        onclick="openDeleteModal('{{ $u->name }}', '{{ route('hr.users.delete', $u) }}')"
        class="text-xs text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition" title="Delete User">
        <i class="ti ti-trash text-base"></i>
      </button>
      @endif
    </div>
  </td>
</tr>
@endforeach
