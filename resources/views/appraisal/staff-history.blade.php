@extends('layouts.app')
@section('title', 'My Appraisal History')

@section('nav')
<a href="{{ route('staff.dashboard') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('staff.dashboard') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-home text-lg w-5"></i> Home
</a>
<a href="{{ route('staff.tasks') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('staff.tasks') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-checkbox text-lg w-5"></i> Tasks
</a>
<a href="{{ route('staff.appraisal') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('staff.appraisal') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-file-description text-lg w-5"></i> Appraisal
</a>
<a href="{{ route('staff.appraisal.history') }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs('staff.appraisal.history') || request()->routeIs('staff.appraisal.view') ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti ti-history text-lg w-5"></i> History
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">

  {{-- Header --}}
  <div class="flex items-start justify-between flex-wrap gap-3 mb-6">
    <div>
      <div class="text-2xl font-bold text-gray-900">My Appraisal History</div>
      <div class="text-sm text-gray-500 mt-0.5">Every appraisal you've submitted, always available to view.</div>
    </div>
    <div class="flex items-center gap-2 bg-white border border-[#e0daf5] rounded-xl px-4 py-2.5 shadow-sm">
      <i class="ti ti-clipboard-list text-[#3C3489] text-lg"></i>
      <span class="text-sm font-semibold text-gray-800">{{ $appraisals->total() }}</span>
      <span class="text-xs text-gray-400">record{{ $appraisals->total() === 1 ? '' : 's' }}</span>
    </div>
  </div>

  {{-- Filter bar --}}
  <form method="GET" action="{{ route('staff.appraisal.history') }}" class="bg-white border border-[#e0daf5] rounded-2xl p-4 sm:p-5 mb-6 shadow-sm">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

      {{-- Search --}}
      <div class="lg:col-span-2 relative">
        <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
        <div class="relative">
          <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
          <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search by cycle name..."
            class="w-full border border-[#e0daf5] rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#7F77DD]/30 focus:border-[#7F77DD] transition"
          >
        </div>
      </div>

      {{-- Status --}}
      <div>
        <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
        <select name="status" class="w-full border border-[#e0daf5] rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#7F77DD]/30 focus:border-[#7F77DD] transition">
          <option value="">Any status</option>
          @foreach(['drafting'=>'Drafting','submitted'=>'Submitted','with_staff_performance'=>'With Staff Performance','approved'=>'Approved'] as $val => $label)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      {{-- Date range --}}
      <div class="sm:col-span-2 lg:col-span-1">
        <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Date range</label>
        <div class="flex items-center gap-1.5">
          <input type="date" name="from" value="{{ request('from') }}"
            class="w-full border border-[#e0daf5] rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#7F77DD]/30 focus:border-[#7F77DD] transition">
          <span class="text-gray-300 text-xs">–</span>
          <input type="date" name="to" value="{{ request('to') }}"
            class="w-full border border-[#e0daf5] rounded-lg px-2.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#7F77DD]/30 focus:border-[#7F77DD] transition">
        </div>
      </div>
    </div>

    <div class="flex items-center gap-2 mt-4">
      <button type="submit" class="inline-flex items-center gap-1.5 bg-[#3C3489] text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-[#2c2668] transition">
        <i class="ti ti-filter text-sm"></i> Apply filters
      </button>
      @if(request()->anyFilled(['search','status','from','to']))
      <a href="{{ route('staff.appraisal.history') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 px-4 py-2 rounded-lg border border-[#e0daf5] hover:bg-[#f5f0ff] transition">
        <i class="ti ti-x text-sm"></i> Clear all
      </a>
      @endif
    </div>
  </form>

  {{-- Results --}}
  @if($appraisals->isEmpty())
    <div class="bg-white border border-[#e0daf5] rounded-2xl p-12 text-center shadow-sm">
      <div class="w-14 h-14 bg-[#eeedfe] rounded-full flex items-center justify-center mx-auto mb-3">
        <i class="ti ti-mood-empty text-[#3C3489] text-2xl"></i>
      </div>
      <div class="text-gray-700 font-medium">No appraisals match your filters</div>
      <div class="text-sm text-gray-400 mt-1">Try adjusting or clearing your search criteria.</div>
    </div>
  @else
    <div class="bg-white border border-[#e0daf5] rounded-2xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-[#f5f0ff] border-b border-[#e0daf5]">
              <th class="text-left py-3 px-4 text-[11px] text-[#534AB7] font-semibold uppercase tracking-wide">Month</th>
              <th class="text-left py-3 px-4 text-[11px] text-[#534AB7] font-semibold uppercase tracking-wide">Cycle</th>
              <th class="text-left py-3 px-4 text-[11px] text-[#534AB7] font-semibold uppercase tracking-wide">Status</th>
              <th class="text-left py-3 px-4 text-[11px] text-[#534AB7] font-semibold uppercase tracking-wide">Submitted</th>
              <th class="text-right py-3 px-4 text-[11px] text-[#534AB7] font-semibold uppercase tracking-wide">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#f0edf8]">
            @foreach($appraisals as $appraisal)
            <tr class="hover:bg-[#faf8ff] transition">
              <td class="py-3.5 px-4 font-medium text-gray-900">
                {{ $appraisal->cycle?->deadline ? \Carbon\Carbon::parse($appraisal->cycle->deadline)->format('F Y') : '—' }}
              </td>
              <td class="py-3.5 px-4 text-gray-500">{{ $appraisal->cycle->name ?? '—' }}</td>
              <td class="py-3.5 px-4">
                <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-medium
                  @class([
                      'bg-gray-100 text-gray-500' => $appraisal->status === 'drafting',
                      'bg-[#eeedfe] text-[#3C3489]' => $appraisal->status === 'submitted',
                      'bg-amber-100 text-amber-700' => $appraisal->status === 'with_staff_performance',
                      'bg-green-100 text-green-700' => $appraisal->status === 'approved',
                  ])">
                  {{ ucfirst(str_replace('_', ' ', $appraisal->status)) }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-gray-500">{{ $appraisal->submitted_at?->format('d M Y') ?? '—' }}</td>
              <td class="py-3.5 px-4 text-right">
                <a href="{{ route('staff.appraisal.view', $appraisal) }}" class="inline-flex items-center gap-1 text-xs border border-[#7F77DD] text-[#3C3489] px-3 py-1.5 rounded-lg hover:bg-[#eeedfe] transition">
                  <i class="ti ti-eye text-sm"></i> View
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($appraisals->hasPages())
      <div class="px-4 py-3 border-t border-[#f0edf8] bg-[#faf8ff]">
        {{ $appraisals->links() }}
      </div>
      @endif
    </div>
  @endif
</div>
@endsection
