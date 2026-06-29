@extends('layouts.app')
@section('title', 'Appraisal Pipeline')

@section('nav')
@foreach([['supervisor.dashboard','ti-home','Home'],['supervisor.pipeline','ti-list','Pipeline']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route) ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')
<div class="text-2xl font-bold text-gray-900 mb-1">Appraisal pipeline</div>
<div class="text-sm text-gray-500 mb-5">Where every staff member is in the {{ $cycle?->name ?? '—' }} cycle.</div>

@php
$grouped = ['drafting'=>[], 'submitted'=>[], 'with_hr'=>[], 'approved'=>[]];
foreach($staff as $s) {
  $a = $appraisals[$s->id] ?? null;
  $status = $a?->status ?? 'drafting';
  if(isset($grouped[$status])) $grouped[$status][] = ['staff'=>$s,'appraisal'=>$a];
}
@endphp

<div class="grid grid-cols-2 gap-4">
  @foreach(['drafting'=>['Drafting','gray'],'submitted'=>['Awaiting Review','amber'],'with_hr'=>['With HR','purple'],'approved'=>['Approved','green']] as $status => [$label, $color])
  <div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-[#f0edf8] flex items-center justify-between
      {{ $color==='amber' ? 'bg-amber-50' : ($color==='green' ? 'bg-green-50' : ($color==='purple' ? 'bg-[#eeedfe]' : 'bg-gray-50')) }}">
      <div class="text-sm font-semibold
        {{ $color==='amber' ? 'text-amber-800' : ($color==='green' ? 'text-green-700' : ($color==='purple' ? 'text-[#3C3489]' : 'text-gray-600')) }}">
        {{ $label }}
      </div>
      <span class="text-xs font-bold px-2 py-0.5 rounded-full
        {{ $color==='amber' ? 'bg-amber-200 text-amber-800' : ($color==='green' ? 'bg-green-200 text-green-800' : ($color==='purple' ? 'bg-[#dddafe] text-[#3C3489]' : 'bg-gray-200 text-gray-600')) }}">
        {{ count($grouped[$status]) }}
      </span>
    </div>
    <div class="p-3 min-h-[100px]">
      @forelse($grouped[$status] as $item)
      @php $apr = $item['appraisal']; $s = $item['staff']; @endphp
      <a href="{{ $apr ? route('supervisor.appraisal.show', $apr) : '#' }}"
         class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-[#f5f0ff] transition mb-1.5 last:mb-0 border border-transparent hover:border-[#e0daf5]">
        <div class="w-8 h-8 bg-[#eeedfe] rounded-full flex items-center justify-center text-[11px] font-bold text-[#3C3489] flex-shrink-0">
          {{ strtoupper(substr($s->name,0,1)) }}{{ strtoupper(substr(explode(' ',$s->name)[1]??'',0,1)) }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-sm font-medium text-gray-900 truncate">{{ $s->name }}</div>
          <div class="text-xs text-gray-400 truncate">{{ $s->department }}</div>
        </div>
        @if($apr)
        <div class="text-xs text-gray-400">{{ $apr->kras->count() + $apr->tasks->count() }} tasks</div>
        @endif
        <i class="ti ti-chevron-right text-gray-300 text-sm flex-shrink-0"></i>
      </a>
      @empty
      <div class="flex items-center justify-center h-16 text-xs text-gray-300 italic">None</div>
      @endforelse
    </div>
  </div>
  @endforeach
</div>
@endsection
