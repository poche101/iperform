@extends('layouts.app')
@section('title', 'Tasks')

@section('nav')
  @include('supervisor.partials.nav')
@endsection

@section('content')
<div class="max-w-6xl mx-auto">

  {{-- Header --}}
  <div class="flex items-start justify-between flex-wrap gap-3 mb-6">
    <div>
      <div class="text-2xl font-bold text-gray-900">Tasks</div>
      <div class="text-sm text-gray-500 mt-0.5">Select a staff member to open their appraisal for the chosen cycle.</div>
    </div>

    {{-- Cycle selector: switching the active cycle never hides earlier months --}}
    @if($cycles->count())
    <form method="GET" action="{{ route('supervisor.tasks') }}" class="flex items-center gap-2">
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

  <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3 flex items-center justify-between">
    Your team
    <span class="bg-gray-100 text-gray-500 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $team->count() }}</span>
  </div>

  @if($team->isEmpty())
    <div class="bg-white border border-[#e0daf5] rounded-2xl p-12 text-center shadow-sm">
      <i class="ti ti-users text-4xl text-gray-300 block mb-2"></i>
      <div class="text-sm text-gray-400">No staff are assigned to you yet.</div>
    </div>
  @else
    <div class="bg-white border border-[#e0daf5] rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-[#f5f0ff] border-b border-[#e0daf5]">
              <th class="text-left py-3 px-4 text-[11px] text-[#534AB7] font-semibold uppercase tracking-wide">Staff</th>
              <th class="text-left py-3 px-4 text-[11px] text-[#534AB7] font-semibold uppercase tracking-wide">Department</th>
              <th class="text-left py-3 px-4 text-[11px] text-[#534AB7] font-semibold uppercase tracking-wide">Tasks</th>
              <th class="text-left py-3 px-4 text-[11px] text-[#534AB7] font-semibold uppercase tracking-wide">Appraisal</th>
              <th class="text-right py-3 px-4 text-[11px] text-[#534AB7] font-semibold uppercase tracking-wide">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#f0edf8]">
            @foreach($team as $member)
            @php
              $stats     = $statsByStaff[$member->id] ?? null;
              $total     = (int) ($stats->total ?? 0);
              $awaiting  = (int) ($stats->awaiting ?? 0);
              $graded    = (int) ($stats->graded ?? 0);
              $appraisal = $appraisalsByStaff[$member->id] ?? null;
            @endphp
            <tr class="hover:bg-[#faf8ff] transition">
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-9 h-9 bg-[#eeedfe] rounded-full flex items-center justify-center text-[11px] font-bold text-[#3C3489] flex-shrink-0">
                    {{ strtoupper(substr($member->name,0,1)) }}{{ strtoupper(substr(explode(' ',$member->name)[1]??'',0,1)) }}
                  </div>
                  <span class="font-medium text-gray-900">{{ $member->name }}</span>
                </div>
              </td>

              <td class="py-3.5 px-4 text-gray-500">{{ $member->department ?? '—' }}</td>

              <td class="py-3.5 px-4">
                <div class="flex flex-wrap items-center gap-1.5">
                  @if($awaiting)
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $awaiting }} awaiting</span>
                  @endif
                  @if($graded)
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700">{{ $graded }} graded</span>
                  @endif
                  @if(!$total)
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-400">No tasks</span>
                  @endif
                </div>
              </td>

              <td class="py-3.5 px-4">
                @if($appraisal)
                  <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-medium
                    @class([
                        'bg-gray-100 text-gray-500'   => $appraisal->status === 'drafting',
                        'bg-[#eeedfe] text-[#3C3489]' => $appraisal->status === 'submitted',
                        'bg-amber-100 text-amber-700' => $appraisal->status === 'with_staff_performance',
                        'bg-green-100 text-green-700' => $appraisal->status === 'approved',
                    ])">
                    {{ ucfirst(str_replace('_', ' ', $appraisal->status)) }}
                  </span>
                @else
                  <span class="text-[11px] text-gray-400">No appraisal yet</span>
                @endif
              </td>

              <td class="py-3.5 px-4 text-right">
                @if($appraisal)
                <a href="{{ route('supervisor.appraisal.show', $appraisal) }}"
                   class="inline-flex items-center gap-1 text-xs border border-[#7F77DD] text-[#3C3489] px-3 py-1.5 rounded-lg hover:bg-[#eeedfe] transition">
                  <i class="ti ti-eye text-sm"></i> View appraisal
                </a>
                @else
                <span class="inline-flex items-center gap-1 text-xs border border-[#f0edf8] text-gray-300 px-3 py-1.5 rounded-lg cursor-not-allowed">
                  <i class="ti ti-eye-off text-sm"></i> View appraisal
                </span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
@endsection
