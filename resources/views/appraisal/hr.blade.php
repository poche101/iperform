@extends('layouts.app')
@section('title', 'HR — Appraisal Review')

@section('nav')
@foreach([['hr.dashboard','ti-chart-bar','HR Overview'],['hr.users','ti-users','Users'],['hr.assignments','ti-arrows-exchange','Assignments'],['hr.cycles','ti-calendar','Cycles'],['hr.tasks','ti-clipboard-list','Task Logs']] as [$route,$icon,$label])
<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] {{ request()->routeIs($route) ? 'bg-[#eeedfe] text-[#3C3489] border-[#3C3489] font-medium' : 'text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]' }}">
  <i class="ti {{ $icon }} text-lg w-5"></i> {{ $label }}
</a>
@endforeach
@endsection

@section('content')
@php $canEdit = $appraisal->status === 'with_hr'; @endphp

<div class="flex items-start justify-between mb-5">
  <div>
    <div class="text-sm text-gray-400 uppercase tracking-wider font-medium">HR Appraisal Review</div>
    <div class="text-2xl font-bold text-gray-900">{{ $appraisal->staff->name }}</div>
    <div class="text-sm text-gray-500">{{ $appraisal->staff->department }} · {{ $appraisal->cycle->name }}</div>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('appraisal.pdf', $appraisal) }}" class="inline-flex items-center gap-2 bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-800 transition">
      <i class="ti ti-file-download"></i> Export PDF
    </a>
    <a href="{{ route('hr.dashboard') }}" class="inline-flex items-center gap-2 border border-[#e0daf5] text-gray-500 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
      <i class="ti ti-arrow-left"></i> Back
    </a>
  </div>
</div>

{{-- Read-only sections 1-4 --}}
@foreach([
  ['kras','Section 1: Major Targets & KRA','35%',['#','KRA','Target','Achievement','% Done','Staff','Supervisor']],
  ['tasks','Section 2: Routine & Other Tasks','25%',['#','Task','Performance','% Done','Staff','Supervisor']],
  ['innovations','Section 3: Ideas & Innovations','20%',['#','Idea','Impact','% Done','Staff','Supervisor']],
] as [$rel,$title,$weight,$cols])
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">{{ $title }}</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">{{ $weight }}</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-[#f5f0ff]">
        @foreach($cols as $col)<th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">{{ $col }}</th>@endforeach
      </tr></thead>
      <tbody>
        @forelse($appraisal->$rel as $row)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400">{{ $row->sn }}</td>
          @if($rel==='kras')
          <td class="py-2 px-3">{{ $row->kra }}</td><td class="py-2 px-3">{{ $row->target }}</td><td class="py-2 px-3">{{ $row->achievement }}</td>
          @elseif($rel==='tasks')
          <td class="py-2 px-3">{{ $row->task }}</td><td class="py-2 px-3">{{ $row->performance }}</td>
          @else
          <td class="py-2 px-3">{{ $row->idea }}</td><td class="py-2 px-3">{{ $row->impact }}</td>
          @endif
          <td class="py-2 px-3 text-center">
            @php $pct = $row->completion_percentage ?? 0; @endphp
            <div class="flex flex-col items-center gap-1">
              <span class="font-semibold text-xs {{ $pct>=80?'text-green-600':($pct>=50?'text-amber-600':'text-red-500') }}">{{ $pct }}%</span>
              <div class="w-14 bg-gray-200 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $pct>=80?'bg-green-500':($pct>=50?'bg-amber-400':'bg-red-400') }}" style="width:{{ $pct }}%"></div>
              </div>
            </div>
          </td>
          <td class="py-2 px-3 text-center"><span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $row->staff_score ?? '—' }}</span></td>
          <td class="py-2 px-3 text-center"><span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $row->supervisor_score ?? '—' }}</span></td>
        </tr>
        @empty
        <tr><td colspan="{{ count($cols) }}" class="py-3 px-3 text-gray-400 italic text-sm">None recorded</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endforeach

{{-- Section 4: Competencies --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 4: Core Competencies</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">15%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-[#f5f0ff]"><th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">#</th><th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Competency</th><th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Staff</th><th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Supervisor</th></tr></thead>
      <tbody>
        @foreach($appraisal->competencies as $c)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400">{{ $c->sn }}</td><td class="py-2 px-3 font-medium text-gray-700">{{ $c->competency }}</td>
          <td class="py-2 px-3 text-center"><span class="bg-[#eeedfe] text-[#3C3489] font-semibold px-2 py-0.5 rounded-full text-xs">{{ $c->staff_score ?? '—' }}</span></td>
          <td class="py-2 px-3 text-center"><span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $c->supervisor_score ?? '—' }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- Section 7 --}}
<div class="bg-white border border-[#e0daf5] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#eeedfe] px-5 py-3 flex items-center justify-between">
    <div class="font-semibold text-[#3C3489] text-sm">Section 7: Compliance to Administrative Policy</div>
    <span class="text-[11px] bg-[#dddafe] text-[#534AB7] px-2 py-0.5 rounded-full">20%</span>
  </div>
  <div class="p-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-[#f5f0ff]"><th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium">#</th><th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Policy</th><th class="py-2 px-3 text-[11px] text-[#534AB7] font-medium text-center">Score</th><th class="text-left py-2 px-3 text-[11px] text-[#534AB7] font-medium">Comments</th></tr></thead>
      <tbody>
        @foreach(is_array($appraisal->section7_items) ? $appraisal->section7_items : (json_decode($appraisal->getRawOriginal('section7_items'), true) ?? $appraisal->getDefaultSection7()) as $item)
        <tr class="border-t border-[#f0edf8]">
          <td class="py-2 px-3 text-gray-400">{{ $item['sn'] }}</td>
          <td class="py-2 px-3 text-gray-700">{{ $item['policy'] }}</td>
          <td class="py-2 px-3 text-center"><span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full text-xs">{{ $item['score'] ?? '—' }}</span></td>
          <td class="py-2 px-3 text-gray-500 text-xs">{{ $item['comment'] ?? '—' }}</td>
        </tr>
        @endforeach
        <tr class="bg-[#f5f0ff] font-semibold text-[#3C3489]">
          <td colspan="2" class="py-2 px-3">Total Section 7</td>
          <td class="py-2 px-3 text-center">{{ collect($appraisal->section7_items)->sum('score') }} / 60</td>
          <td></td>
        </tr>
      </tbody>
    </table>
    <div class="grid grid-cols-3 gap-4 mt-4 text-sm">
      <div><div class="text-xs text-gray-400 mb-1">Overall Contribution</div><div class="text-gray-700">{{ $appraisal->overall_contribution ?: '—' }}</div></div>
      <div><div class="text-xs text-gray-400 mb-1">Key Strengths</div><div class="text-gray-700">{{ $appraisal->key_strengths ?: '—' }}</div></div>
      <div><div class="text-xs text-gray-400 mb-1">Areas for Improvement</div><div class="text-gray-700">{{ $appraisal->areas_for_improvement ?: '—' }}</div></div>
    </div>
  </div>
</div>

{{-- Supervisor confirmation --}}
<div class="bg-white border border-[#e0daf5] rounded-xl p-5 mb-4 text-sm">
  <div class="font-semibold text-gray-800 mb-1">Work Confirmation</div>
  <div class="text-gray-500 mb-2">Supervisor confirmed staff was actively engaged and eligible for payment.</div>
  <div><span class="text-gray-400">Salary to be paid:</span> <strong>{{ $appraisal->salary_percent ?? '—' }}%</strong></div>
  @if($appraisal->supervisor_comments)
  <div class="mt-2"><span class="text-gray-400">Supervisor comments:</span> {{ $appraisal->supervisor_comments }}</div>
  @endif
</div>

{{-- HR Summary Section --}}
<form method="POST" action="{{ route('hr.appraisal.approve', $appraisal) }}">
@csrf
<div class="bg-white border-2 border-[#534AB7] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#3C3489] px-5 py-3">
    <div class="font-semibold text-white text-sm">HR Summary — Performance Management Only</div>
    <div class="text-xs text-white/60">SUPERVISORS ARE NOT TO GO BEYOND THIS POINT</div>
  </div>
  <div class="p-5">
    <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 text-xs text-amber-700 mb-4">
      <strong>Formula:</strong> S1=(avg sup score/10)×35 · S2=(avg/10)×25 · S3=(avg/10)×20 · S4=(Sec7total/60)×20 · Overall=S1+S2+S3+S4
    </div>
    <div class="mb-4">
      <button type="button" id="auto-calc-btn" onclick="autoCalculate()" class="inline-flex items-center gap-2 bg-[#534AB7] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#3C3489] transition">
        <i class="ti ti-calculator"></i> Auto-calculate totals
      </button>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-4">
      @foreach([['hr_s1_weighted','Section 1 — KRA (35 marks)'],['hr_s2_weighted','Section 2 — Routines (25 marks)'],['hr_s3_weighted','Section 3 — Innovations (20 marks)'],['hr_s4_weighted','Section 4 — Compliance (20 marks)']] as [$field,$label])
      <div>
        <label class="block text-xs text-gray-400 mb-1">{{ $label }}</label>
        @if($canEdit)
        <input type="number" step="0.01" name="{{ $field }}" id="{{ $field }}" value="{{ $appraisal->$field }}" class="w-full border border-[#e0daf5] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#7F77DD]">
        @else
        <div class="text-lg font-bold text-[#3C3489]">{{ $appraisal->$field ?? '—' }}</div>
        @endif
      </div>
      @endforeach
    </div>
    <div class="border-t border-[#e0daf5] pt-4 mb-4 flex items-center justify-between">
      <div>
        <div class="text-xs text-gray-400">Overall Performance Score</div>
        <div class="text-4xl font-bold text-[#3C3489]" id="overall-display">{{ $appraisal->hr_overall ?? '—' }}</div>
        <input type="hidden" name="hr_overall" id="hr_overall" value="{{ $appraisal->hr_overall }}">
      </div>
      <div class="text-right">
        <div class="text-xs text-gray-400">Appraisal Grade</div>
        <div class="text-5xl font-bold text-[#3C3489]" id="grade-display">{{ $appraisal->hr_grade ?? '—' }}</div>
        <input type="hidden" name="hr_grade" id="hr_grade" value="{{ $appraisal->hr_grade }}">
      </div>
    </div>
    <div>
      <div class="flex items-center justify-between mb-2">
        <label class="text-xs text-gray-500 font-medium">Performance Management / HR Comments</label>
        <button type="button" onclick="aiComment()" class="inline-flex items-center gap-1.5 bg-[#534AB7] text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-[#3C3489] transition">
          <i class="ti ti-sparkles"></i> AI Assist
        </button>
      </div>
      @if($canEdit)
      <textarea name="hr_comments" id="hr_comments" rows="4" class="w-full border border-[#e0daf5] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#7F77DD]" placeholder="Enter HR performance comments or use AI Assist...">{{ $appraisal->hr_comments }}</textarea>
      @else
      <div class="text-sm text-gray-700 leading-relaxed">{{ $appraisal->hr_comments ?: '—' }}</div>
      @endif
    </div>
  </div>
</div>

@if($canEdit)
<div class="flex gap-3">
  <a href="{{ route('hr.dashboard') }}" class="inline-flex items-center gap-2 border border-[#e0daf5] text-gray-500 px-5 py-2.5 rounded-lg text-sm hover:bg-gray-50 transition">Cancel</a>
  <button type="submit" class="inline-flex items-center gap-2 bg-[#3C3489] text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-[#26215C] transition">
    <i class="ti ti-check"></i> Approve appraisal
  </button>
</div>
@endif
</form>
@endsection

@section('scripts')
<script>
async function autoCalculate() {
  const btn = document.getElementById('auto-calc-btn');
  btn.innerHTML = '<span class="animate-spin inline-block">⟳</span> Calculating...';
  btn.disabled = true;
  const res = await fetch('{{ route("hr.appraisal.calculate", $appraisal) }}', {
    method:'POST', headers:{'X-CSRF-TOKEN':window.csrfToken,'Accept':'application/json'}
  });
  const d = await res.json();
  ['s1','s2','s3','s4'].forEach((k,i) => {
    const el = document.getElementById(['hr_s1_weighted','hr_s2_weighted','hr_s3_weighted','hr_s4_weighted'][i]);
    if(el) el.value = d[k] ?? '';
  });
  document.getElementById('overall-display').textContent = d.overall ?? '—';
  document.getElementById('hr_overall').value = d.overall ?? '';
  document.getElementById('grade-display').textContent = d.grade ?? '—';
  document.getElementById('hr_grade').value = d.grade ?? '';
  btn.innerHTML = '<i class="ti ti-calculator"></i> Auto-calculate totals';
  btn.disabled = false;
}

async function aiComment() {
  const btn = event.target.closest('button');
  btn.innerHTML = '<span class="animate-spin inline-block">⟳</span> Generating...';
  btn.disabled = true;
  const res = await fetch('{{ route("hr.appraisal.ai-comment", $appraisal) }}', {
    method:'POST', headers:{'X-CSRF-TOKEN':window.csrfToken,'Accept':'application/json','Content-Type':'application/json'}
  });
  const d = await res.json();
  document.getElementById('hr_comments').value = d.comment;
  btn.innerHTML = '<i class="ti ti-sparkles"></i> AI Assist';
  btn.disabled = false;
}
</script>
@endsection
