<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalCycle;
use App\Models\AppraisalKra;
use App\Models\AppraisalTask;
use App\Models\AppraisalInnovation;
use App\Models\AppraisalCompetency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AppraisalController extends Controller
{
    private function activeCycle()
    {
        return AppraisalCycle::where('is_active', true)->firstOrFail();
    }

    /** Staff: view own appraisal for the current active cycle (creates a draft if none exists yet) */
    public function staffShow()
    {
        $user = Auth::user();
        $cycle = $this->activeCycle();
        $appraisal = Appraisal::with(['kras','tasks','innovations','competencies'])
            ->where('staff_id', $user->id)
            ->where('cycle_id', $cycle->id)
            ->first();

        if (!$appraisal) {
            $appraisal = Appraisal::create([
                'cycle_id' => $cycle->id,
                'staff_id' => $user->id,
                'supervisor_id' => $user->supervisor_id,
                'status' => 'drafting',
                'section5' => [],
                'section6' => [],
                'section7_items' => (new Appraisal)->getDefaultSection7(),
            ]);
            // Seed default competencies
            $comps = ['Communication Skills','Teamwork and Collaboration','Problem Solving Ability','Initiative and Proactiveness','Professional Conduct and Work Ethics'];
            foreach ($comps as $i => $c) {
                AppraisalCompetency::create(['appraisal_id'=>$appraisal->id,'sn'=>$i+1,'competency'=>$c]);
            }
            $appraisal->load(['kras','tasks','innovations','competencies']);
        }

        return view('appraisal.show', compact('appraisal', 'cycle'));
    }

    /** Staff: list all of my appraisals across every cycle, past and present */
    public function staffHistory(Request $request)
    {
        $query = Appraisal::with('cycle')
            ->where('staff_id', Auth::id());

        if ($search = $request->input('search')) {
            $query->whereHas('cycle', fn($c) => $c->where('name', 'like', "%{$search}%"));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->input('from')) {
            $query->whereHas('cycle', fn($c) => $c->whereDate('deadline', '>=', $from));
        }

        if ($to = $request->input('to')) {
            $query->whereHas('cycle', fn($c) => $c->whereDate('deadline', '<=', $to));
        }

        $appraisals = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('appraisal.staff-history', compact('appraisals'));
    }

    /** Staff: view any of my own appraisals by id, regardless of cycle or status (read-only once not drafting) */
    public function staffShowAny(Appraisal $appraisal)
    {
        abort_unless($appraisal->staff_id === Auth::id(), 403);
        $appraisal->load(['kras','tasks','innovations','competencies','cycle']);

        return view('appraisal.show', [
            'appraisal' => $appraisal,
            'cycle' => $appraisal->cycle,
            'readOnly' => $appraisal->status !== 'drafting',
        ]);
    }

    /** Staff: save draft */
    public function staffSave(Request $request, Appraisal $appraisal)
    {
        $this->authorizeStaff($appraisal);

        // KRAs
        $appraisal->kras()->delete();
        foreach ($request->input('kras', []) as $i => $row) {
            if (empty($row['kra'])) continue;
            AppraisalKra::create([
                'appraisal_id'         => $appraisal->id,
                'sn'                   => $i + 1,
                'kra'                  => $row['kra'],
                'target'               => $row['target'] ?? '',
                'achievement'          => $row['achievement'] ?? '',
                'completion_percentage'=> max(0, min(100, (int)($row['completion_percentage'] ?? 0))),
                'staff_score'          => $row['staff_score'] ?? null,
            ]);
        }

        // Tasks
        $appraisal->tasks()->delete();
        foreach ($request->input('tasks', []) as $i => $row) {
            if (empty($row['task'])) continue;
            AppraisalTask::create([
                'appraisal_id'         => $appraisal->id,
                'sn'                   => $i + 1,
                'task'                 => $row['task'],
                'performance'          => $row['performance'] ?? '',
                'completion_percentage'=> max(0, min(100, (int)($row['completion_percentage'] ?? 0))),
                'staff_score'          => $row['staff_score'] ?? null,
            ]);
        }

        // Innovations
        $appraisal->innovations()->delete();
        foreach ($request->input('innovations', []) as $i => $row) {
            if (empty($row['idea'])) continue;
            AppraisalInnovation::create([
                'appraisal_id'         => $appraisal->id,
                'sn'                   => $i + 1,
                'idea'                 => $row['idea'],
                'impact'               => $row['impact'] ?? '',
                'completion_percentage'=> max(0, min(100, (int)($row['completion_percentage'] ?? 0))),
                'staff_score'          => $row['staff_score'] ?? null,
            ]);
        }

        // Competencies
        foreach ($request->input('competencies', []) as $id => $score) {
            AppraisalCompetency::where('id', $id)->where('appraisal_id', $appraisal->id)->update(['staff_score'=>$score]);
        }

        // Sections 5 & 6
        $appraisal->update([
            'section5' => $request->input('section5', []),
            'section6' => $request->input('section6', []),
        ]);

        return back()->with('success', 'Draft saved successfully.');
    }

    /** Staff: submit to supervisor */
    public function staffSubmit(Request $request, Appraisal $appraisal)
    {
        $this->authorizeStaff($appraisal);
        $this->staffSave($request, $appraisal);
        $appraisal->update(['status'=>'submitted','submitted_at'=>now()]);
        return redirect()->route('staff.dashboard')->with('success', 'Appraisal submitted to your supervisor!');
    }

    /** Supervisor: show appraisal for grading */
    public function supervisorShow(Appraisal $appraisal)
    {
        $this->authorizeSupervisor($appraisal);
        $appraisal->load(['kras','tasks','innovations','competencies','staff','cycle']);
        return view('appraisal.supervisor', compact('appraisal'));
    }

    /** Supervisor: list all appraisals I supervise, across every cycle, past and present */
    public function supervisorHistory(Request $request)
    {
        $query = Appraisal::with(['staff','cycle'])
            ->where('supervisor_id', Auth::id());

        if ($search = $request->input('search')) {
            $query->whereHas('staff', fn($s) => $s->where('name', 'like', "%{$search}%"));
        }

        if ($department = $request->input('department')) {
            $query->whereHas('staff', fn($s) => $s->where('department', $department));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->input('from')) {
            $query->whereHas('cycle', fn($c) => $c->whereDate('deadline', '>=', $from));
        }

        if ($to = $request->input('to')) {
            $query->whereHas('cycle', fn($c) => $c->whereDate('deadline', '<=', $to));
        }

        $appraisals = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $departments = User::where('supervisor_id', Auth::id())
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('appraisal.supervisor-history', compact('appraisals', 'departments'));
    }

    /** Supervisor: save grades */
    public function supervisorSave(Request $request, Appraisal $appraisal)
    {
        $this->authorizeSupervisor($appraisal);

        // Update supervisor scores on KRAs
        foreach ($request->input('kra_scores', []) as $id => $score) {
            AppraisalKra::where('id', $id)->where('appraisal_id', $appraisal->id)->update(['supervisor_score'=>$score]);
        }
        foreach ($request->input('task_scores', []) as $id => $score) {
            AppraisalTask::where('id', $id)->where('appraisal_id', $appraisal->id)->update(['supervisor_score'=>$score]);
        }
        foreach ($request->input('innovation_scores', []) as $id => $score) {
            AppraisalInnovation::where('id', $id)->where('appraisal_id', $appraisal->id)->update(['supervisor_score'=>$score]);
        }
        foreach ($request->input('competency_scores', []) as $id => $score) {
            AppraisalCompetency::where('id', $id)->where('appraisal_id', $appraisal->id)->update(['supervisor_score'=>$score]);
        }

        $appraisal->update([
            'section6' => $request->input('section6', $appraisal->section6),
            'section7_items' => $request->input('section7', $appraisal->section7_items),
            'overall_contribution' => $request->input('overall_contribution'),
            'key_strengths' => $request->input('key_strengths'),
            'areas_for_improvement' => $request->input('areas_for_improvement'),
            'salary_percent' => $request->input('salary_percent'),
            'supervisor_comments' => $request->input('supervisor_comments'),
        ]);

        return back()->with('success', 'Grades saved.');
    }

    /** Supervisor: forward to Staff Performance */
    public function supervisorForward(Request $request, Appraisal $appraisal)
    {
        $this->authorizeSupervisor($appraisal);
        $this->supervisorSave($request, $appraisal);
        $appraisal->update(['status'=>'with_staff_performance','supervisor_confirmed'=>true,'forwarded_at'=>now()]);
        return redirect()->route('supervisor.dashboard')->with('success', 'Appraisal forwarded to Staff Performance!');
    }

    /** HR (Staff Performance): show appraisal */
    public function hrShow(Appraisal $appraisal)
    {
        abort_unless(Auth::user()->isStaffPerformance(), 403);
        $appraisal->load(['kras','tasks','innovations','competencies','staff','supervisor','cycle']);
        return view('appraisal.hr', compact('appraisal'));
    }

    /** HR (Staff Performance): list all appraisals across every cycle, past and present */
    public function hrHistory(Request $request)
    {
        abort_unless(Auth::user()->isStaffPerformance(), 403);

        $query = Appraisal::with(['staff','supervisor','cycle']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('staff', fn($s) => $s->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('supervisor', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($department = $request->input('department')) {
            $query->whereHas('staff', fn($s) => $s->where('department', $department));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->input('from')) {
            $query->whereHas('cycle', fn($c) => $c->whereDate('deadline', '>=', $from));
        }

        if ($to = $request->input('to')) {
            $query->whereHas('cycle', fn($c) => $c->whereDate('deadline', '<=', $to));
        }

        $appraisals = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        $departments = User::whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('appraisal.hr-history', compact('appraisals', 'departments'));
    }

    /** HR (Staff Performance): auto-calculate totals */
    public function hrAutoCalculate(Appraisal $appraisal)
    {
        abort_unless(Auth::user()->isStaffPerformance(), 403);
        $appraisal->load(['kras','tasks','innovations']);
        $appraisal->autoCalculate();
        return response()->json([
            's1' => $appraisal->staff_performance_s1_weighted,
            's2' => $appraisal->staff_performance_s2_weighted,
            's3' => $appraisal->staff_performance_s3_weighted,
            's4' => $appraisal->staff_performance_s4_weighted,
            'overall' => $appraisal->staff_performance_overall,
            'grade' => $appraisal->staff_performance_grade,
        ]);
    }

    /** HR (Staff Performance): save and approve */
    public function hrApprove(Request $request, Appraisal $appraisal)
    {
        abort_unless(Auth::user()->isStaffPerformance(), 403);
        $appraisal->update([
            'staff_performance_s1_weighted' => $request->input('staff_performance_s1_weighted'),
            'staff_performance_s2_weighted' => $request->input('staff_performance_s2_weighted'),
            'staff_performance_s3_weighted' => $request->input('staff_performance_s3_weighted'),
            'staff_performance_s4_weighted' => $request->input('staff_performance_s4_weighted'),
            'staff_performance_overall'     => $request->input('staff_performance_overall'),
            'staff_performance_grade'       => $request->input('staff_performance_grade'),
            'staff_performance_comments'    => $request->input('staff_performance_comments'),
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        return redirect()->route('hr.dashboard')->with('success', 'Appraisal approved!');
    }

    /** AI: generate Staff Performance comment */
    public function aiComment(Request $request, Appraisal $appraisal)
    {
        abort_unless(Auth::user()->isStaffPerformance(), 403);
        $staff = $appraisal->staff;
        $prompt = "You are a staff performance manager at a church organisation. Write a concise, professional 2-3 sentence staff performance comment for {$staff->name} ({$staff->designation}, {$staff->department}) for the {$appraisal->cycle->name} appraisal. Overall score: {$appraisal->staff_performance_overall}/100 (Grade: {$appraisal->staff_performance_grade}). Key strengths: {$appraisal->key_strengths}. Areas for improvement: {$appraisal->areas_for_improvement}. Write in third person, professional and encouraging tone.";

        $response = Http::withHeaders(['x-api-key' => config('services.anthropic.key'), 'anthropic-version' => '2023-06-01', 'Content-Type' => 'application/json'])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 500,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);

        return response()->json(['comment' => $response->json('content.0.text', '')]);
    }

    /** PDF export */
    public function exportPdf(Appraisal $appraisal)
    {
        $user = Auth::user();
        abort_unless(
            $user->isStaffPerformance() || $appraisal->supervisor_id === $user->id || $appraisal->staff_id === $user->id,
            403
        );
        $appraisal->load(['kras','tasks','innovations','competencies','staff','supervisor','cycle']);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('appraisal.pdf', compact('appraisal'));
        $pdf->setPaper('a4', 'portrait');
        $filename = 'iPerform_' . str_replace(' ', '_', $appraisal->staff->name) . '_' . str_replace(' ', '_', $appraisal->cycle->name) . '.pdf';
        return $pdf->download($filename);
    }

    private function authorizeStaff(Appraisal $appraisal)
    {
        // Only gates writes (save/submit). Viewing a past or non-drafting appraisal
        // is always allowed via staffShowAny() regardless of cycle or status.
        abort_unless($appraisal->staff_id === Auth::id() && $appraisal->status === 'drafting', 403);
    }

    private function authorizeSupervisor(Appraisal $appraisal)
    {
        abort_unless(Auth::user()->isSupervisor() && $appraisal->supervisor_id === Auth::id(), 403);
    }
}
