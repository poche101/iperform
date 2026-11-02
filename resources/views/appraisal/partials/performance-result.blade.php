{{-- Final Staff Performance result, shown to the staff member and the supervisor once HR approves.
     Expects $appraisal. Used by appraisal/show.blade.php and appraisal/supervisor.blade.php. --}}
@if($appraisal->status === 'approved')
<div class="bg-white border-2 border-[#534AB7] rounded-xl overflow-hidden mb-4">
  <div class="bg-[#3C3489] px-5 py-3 flex items-center justify-between gap-3 flex-wrap">
    <div>
      <div class="font-semibold text-white text-sm">Staff Performance Result</div>
      <div class="text-xs text-white/60">Finalised by Staff Performance{{ $appraisal->approved_at ? ' · ' . $appraisal->approved_at->format('d M Y') : '' }}</div>
    </div>
    <span class="text-[11px] bg-green-100 text-green-700 font-medium px-2 py-0.5 rounded-full">Approved</span>
  </div>
  <div class="p-5">
    <div class="flex items-center justify-between gap-6 flex-wrap mb-5">
      <div>
        <div class="text-xs text-gray-400">Overall Performance Score</div>
        <div class="text-4xl font-bold text-[#3C3489]">{{ $appraisal->staff_performance_overall ?? '—' }}<span class="text-base font-medium text-gray-400"> / 100</span></div>
      </div>
      <div class="text-right">
        <div class="text-xs text-gray-400">Appraisal Grade</div>
        <div class="text-5xl font-bold text-[#3C3489]">{{ $appraisal->staff_performance_grade ?? '—' }}</div>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
      @foreach([
        ['staff_performance_s1_weighted', 'Section 1 · KRA', 35],
        ['staff_performance_s2_weighted', 'Section 2 · Routines', 25],
        ['staff_performance_s3_weighted', 'Section 3 · Innovations', 20],
        ['staff_performance_s4_weighted', 'Section 4 · Compliance', 20],
      ] as [$field, $label, $max])
      <div class="bg-[#f5f0ff] border border-[#e0daf5] rounded-lg px-3 py-2.5">
        <div class="text-[11px] text-gray-500">{{ $label }}</div>
        <div class="text-lg font-bold text-[#3C3489]">{{ $appraisal->$field ?? '—' }}<span class="text-xs font-normal text-gray-400"> / {{ $max }}</span></div>
      </div>
      @endforeach
    </div>

    @if($appraisal->staff_performance_comments)
    <div>
      <div class="text-xs text-gray-400 mb-1">Staff Performance comments</div>
      <div class="text-sm text-gray-700 leading-relaxed bg-[#faf8ff] border border-[#e0daf5] border-l-4 border-l-[#7F77DD] rounded-lg px-4 py-3">{{ $appraisal->staff_performance_comments }}</div>
    </div>
    @endif
  </div>
</div>
@elseif($appraisal->status === 'with_staff_performance')
<div class="bg-orange-50 border border-orange-200 rounded-lg px-4 py-3 mb-4 text-sm text-orange-700 flex items-start gap-2">
  <i class="ti ti-hourglass-high mt-0.5 flex-shrink-0"></i>
  <div>This appraisal is with Staff Performance for final review. The final score and grade will appear here once it is approved.</div>
</div>
@endif
