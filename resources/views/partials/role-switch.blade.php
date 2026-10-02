{{-- resources/views/partials/role-switch.blade.php
     Shown only to supervisors who also have a supervisor of their own (see User::isAlsoStaff()).
     Lets them move between the supervisor side and the staff side without logging out. --}}
@if(auth()->user()->isAlsoStaff())
  @php $onStaffSide = request()->routeIs('staff.*'); @endphp

  <div class="mx-5 my-3 border-t border-[#e0daf5]"></div>

  @if($onStaffSide)
    <div class="px-5 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Supervisor</div>
    <a href="{{ route('supervisor.dashboard') }}"
       class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] bg-[#faf8ff] text-[#3C3489] border-[#AFA9EC] font-medium hover:bg-[#eeedfe]">
      <i class="ti ti-arrow-back-up text-lg w-5"></i> Back to supervisor view
    </a>
    <a href="{{ route('supervisor.tasks') }}"
       class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]">
      <i class="ti ti-clipboard-check text-lg w-5"></i> Review team tasks
    </a>
  @else
    <div class="px-5 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">My own work</div>
    <a href="{{ route('staff.dashboard') }}"
       class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]">
      <i class="ti ti-layout-dashboard text-lg w-5"></i> My dashboard
    </a>
    <a href="{{ route('staff.tasks') }}"
       class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]">
      <i class="ti ti-checklist text-lg w-5"></i> My tasks
    </a>
    <a href="{{ route('staff.appraisal') }}"
       class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]">
      <i class="ti ti-file-text text-lg w-5"></i> My appraisal
    </a>
  @endif
@endif
