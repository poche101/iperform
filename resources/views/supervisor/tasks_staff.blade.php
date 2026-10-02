@extends('layouts.app')
@section('title', $staff->name . ' – Tasks')

@section('nav')
  @include('supervisor.partials.nav')
@endsection

@section('content')
<a href="{{ route('supervisor.tasks', ['cycle' => $cycle?->id]) }}"
   class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-[#3C3489] mb-3">
  <i class="ti ti-arrow-left"></i> All staff
</a>

<div class="flex items-start justify-between flex-wrap gap-3 mb-5">
  <div class="flex items-center gap-3">
    <div class="w-11 h-11 bg-[#eeedfe] rounded-full flex items-center justify-center text-sm font-bold text-[#3C3489]">
      {{ strtoupper(substr($staff->name,0,1)) }}{{ strtoupper(substr(explode(' ',$staff->name)[1]??'',0,1)) }}
    </div>
    <div>
      <div class="text-2xl font-bold text-gray-900">{{ $staff->name }}</div>
      <div class="text-sm text-gray-500">{{ $staff->department }}</div>
    </div>
  </div>

  @if($cycles->count())
  <form method="GET" action="{{ route('supervisor.tasks.staff', $staff) }}" class="flex items-center gap-2">
    @if($activeKey)<input type="hidden" name="category" value="{{ $activeKey }}">@endif
    <label class="text-xs font-medium text-gray-500">Cycle</label>
    <select name="cycle" onchange="this.form.submit()"
            class="px-3 py-2 border border-[#e0daf5] rounded-lg text-sm bg-white focus:outline-none focus:border-[#7F77DD]">
      @foreach($cycles as $c)
        @php $pending = $pendingByCycle[$c->id] ?? 0; @endphp
        <option value="{{ $c->id }}" {{ $cycle && $cycle->id === $c->id ? 'selected' : '' }}>
          {{ $c->name }}{{ $c->is_active ? ' (active)' : '' }}{{ $pending ? " — {$pending} awaiting" : '' }}
        </option>
      @endforeach
    </select>
  </form>
  @endif
</div>

@if($cycle && !$cycle->is_active)
<div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-700">
  <i class="ti ti-history text-lg"></i>
  You are viewing {{ $cycle->name }}, which is not the active cycle. You can still grade its tasks.
</div>
@endif

{{-- Category tiles --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
  @foreach($sections as $key => $section)
    @php $isActive = $activeKey === $key; @endphp
    <a href="{{ route('supervisor.tasks.staff', ['staff' => $staff->id, 'cycle' => $cycle?->id, 'category' => $key]) }}"
       class="block rounded-xl p-4 border transition
              {{ $isActive ? 'bg-[#eeedfe] border-[#3C3489]' : 'bg-white border-[#e0daf5] hover:border-[#7F77DD] hover:shadow-sm' }}">
      <div class="flex items-center justify-between mb-2">
        <div class="w-9 h-9 rounded-lg flex items-center justify-center {{ $isActive ? 'bg-white text-[#3C3489]' : 'bg-[#eeedfe] text-[#3C3489]' }}">
          <i class="ti {{ $section['icon'] }} text-xl"></i>
        </div>
        <span class="text-2xl font-bold text-[#3C3489]">{{ $section['total'] }}</span>
      </div>
      <div class="font-medium text-gray-900 text-sm">{{ $section['label'] }}</div>
      <div class="text-xs text-gray-400 mb-2">{{ $section['hint'] }}</div>
      <div class="flex flex-wrap gap-1.5">
        @if($section['awaiting'])
          <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $section['awaiting'] }} awaiting</span>
        @endif
        @if($section['graded'])
          <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700">{{ $section['graded'] }} graded</span>
        @endif
        @if(!$section['total'])
          <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-400">No tasks</span>
        @endif
      </div>
    </a>
  @endforeach
</div>

{{-- Tasks under the selected category --}}
@if($activeKey)
  <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3 flex items-center justify-between">
    <span class="flex items-center gap-1.5">
      <i class="ti {{ $sections[$activeKey]['icon'] }} text-base"></i> {{ $sections[$activeKey]['label'] }}
    </span>
    <span class="bg-gray-100 text-gray-500 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $activeTasks->count() }}</span>
  </div>

  @forelse($activeTasks as $task)
    @include('supervisor.partials.task-review', ['task' => $task])
  @empty
    <div class="bg-white border border-[#e0daf5] rounded-xl p-8 text-center">
      <i class="ti ti-clipboard-off text-4xl text-gray-300 block mb-2"></i>
      <div class="text-sm text-gray-400">No {{ $sections[$activeKey]['label'] }} tasks logged in this cycle.</div>
    </div>
  @endforelse
@else
  <div class="bg-white border border-dashed border-[#e0daf5] rounded-xl p-8 text-center">
    <div class="text-sm text-gray-400">Select KRA, Routine or Ideas & Outstanding Innovation above to see the tasks.</div>
  </div>
@endif
@endsection
