@php $graded = $task->status === 'graded'; @endphp
<div class="bg-white border border-[#e0daf5] rounded-xl p-4 mb-3">
  <div class="flex items-start gap-3 {{ $graded ? '' : 'mb-3' }}">
    <div class="flex-1 min-w-0">
      <div class="font-medium text-gray-900 text-sm">{{ $task->title }}</div>
      <div class="flex flex-wrap gap-2 mt-1.5">
        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $graded ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
          {{ $graded ? 'Graded' : 'Awaiting review' }}
        </span>
        @if($task->self_score !== null)
          <span class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Staff self-score: {{ $task->self_score }}/10</span>
        @endif
        @if($task->completion_percentage > 0)
          <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
            {{ $task->completion_percentage>=80?'bg-green-100 text-green-700':($task->completion_percentage>=50?'bg-amber-100 text-amber-700':'bg-red-100 text-red-600') }}">
            {{ $task->completion_percentage }}% done
          </span>
        @endif
      </div>
    </div>
    <div class="text-[11px] text-gray-400 whitespace-nowrap">{{ $task->date->format('d M Y') }}</div>
  </div>

  @if($task->details)
    <div class="text-xs text-gray-600 mb-3">{{ $task->details }}</div>
  @endif

  @if($task->target)
    <div class="mb-3 text-xs">
      <span class="font-medium text-amber-700">Target:</span>
      <span class="text-gray-600">{{ $task->target }}</span>
    </div>
  @endif

  @if($task->challenge_identified || $task->challenge_impact)
    <div class="mb-3 bg-gray-50 border border-gray-100 rounded-lg p-3 text-xs max-w-2xl flex flex-col gap-1.5">
      <div class="text-[10px] uppercase font-bold text-[#534AB7] tracking-wider flex items-center gap-1">
        <i class="ti ti-alert-triangle"></i> Logged Performance Constraints
      </div>
      @if($task->challenge_identified)
        <div class="text-gray-700"><span class="font-medium text-red-600">Challenge:</span> {{ $task->challenge_identified }}</div>
      @endif
      @if($task->challenge_impact)
        <div class="text-gray-600"><span class="font-medium text-gray-700">Impact Assessment:</span> {{ $task->challenge_impact }}</div>
      @endif
    </div>
  @endif

  @if($task->completion_percentage > 0)
    <div class="mb-3 flex items-center gap-2">
      <div class="flex-1 bg-gray-100 rounded-full h-1.5">
        <div class="h-1.5 rounded-full {{ $task->completion_percentage>=80?'bg-green-500':($task->completion_percentage>=50?'bg-amber-400':'bg-red-400') }}"
             style="width:{{ $task->completion_percentage }}%"></div>
      </div>
      <span class="text-xs font-medium text-gray-500">{{ $task->completion_percentage }}%</span>
    </div>
  @endif

  @if($graded)
    <div class="mt-2 bg-[#faf8ff] border border-[#AFA9EC] rounded-lg p-2.5">
      <div class="flex items-center gap-2 mb-1">
        <span class="text-[10px] font-medium text-[#534AB7] uppercase tracking-wider">Your feedback</span>
        <span class="ml-auto text-xs font-bold text-[#3C3489]">{{ $task->supervisor_score }}/10</span>
      </div>
      @if($task->supervisor_comment)
        <div class="text-xs text-gray-600">{{ $task->supervisor_comment }}</div>
      @endif
      <div class="text-[10px] text-gray-400 mt-1">{{ $task->reviewed_at?->format('d/m/Y, H:i') }}</div>
    </div>
  @else
    <form method="POST" action="{{ route('supervisor.tasks.grade', $task) }}" class="border-t border-[#f0edf8] pt-3">
      @csrf
      <div class="mb-2">
        <label class="block text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">
          Grade · <span class="score-display-{{ $task->id }}">8</span>/10
        </label>
        <input type="range" name="supervisor_score" min="0" max="10" step="1" value="8"
          oninput="document.querySelector('.score-display-{{ $task->id }}').textContent=this.value"
          class="w-full h-1.5 bg-[#e0daf5] rounded-full appearance-none cursor-pointer accent-[#3C3489]">
        <div class="flex justify-between text-[10px] text-gray-300 mt-0.5">
          @for($i=0;$i<=10;$i++)<span>{{ $i }}</span>@endfor
        </div>
      </div>
      <div class="mb-3">
        <label class="block text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Comment</label>
        <textarea name="supervisor_comment" rows="2"
          class="w-full px-3 py-2 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] resize-none"
          placeholder="What worked well? What's the next step?"></textarea>
      </div>
      <button type="submit" class="inline-flex items-center gap-2 bg-[#3C3489] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#26215C] transition">
        <i class="ti ti-send"></i> Submit feedback
      </button>
    </form>
  @endif
</div>
