<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>iPerform — {{ $appraisal->staff->name }} — {{ $appraisal->cycle->name }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1a1a2e; line-height: 1.4; }
.page { padding: 30px 35px; }
.header { border-bottom: 2px solid #3C3489; padding-bottom: 12px; margin-bottom: 16px; text-align: center; }
.org-name { font-size: 10px; color: #534AB7; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 4px; }
.doc-title { font-size: 18px; font-weight: bold; color: #1a1a2e; margin-bottom: 3px; }
.cycle-name { font-size: 13px; font-weight: bold; color: #3C3489; }
.info-grid { display: table; width: 100%; margin-bottom: 14px; }
.info-row { display: table-row; }
.info-cell { display: table-cell; width: 50%; padding: 4px 8px; background: #f5f0ff; font-size: 10px; border: 1px solid #e0daf5; }
.info-label { color: #888780; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; display: block; }
.info-value { font-weight: bold; color: #1a1a2e; }
.section { margin-bottom: 14px; }
.section-header { background: #3C3489; color: white; padding: 6px 10px; font-weight: bold; font-size: 11px; display: flex; justify-content: space-between; }
.section-header-purple { background: #eeedfe; color: #3C3489; padding: 6px 10px; font-weight: bold; font-size: 11px; }
.section-weight { background: #dddafe; color: #534AB7; padding: 1px 7px; border-radius: 10px; font-size: 9px; font-weight: normal; }
.section-body { border: 1px solid #e0daf5; border-top: none; }
table { width: 100%; border-collapse: collapse; font-size: 10px; }
th { background: #f5f0ff; color: #534AB7; padding: 5px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; border: 0.5px solid #e0daf5; }
td { padding: 5px 8px; border: 0.5px solid #e0daf5; vertical-align: top; }
.score-badge { background: #eeedfe; color: #3C3489; font-weight: bold; padding: 1px 6px; border-radius: 8px; font-size: 9px; }
.score-badge-green { background: #e1f5ee; color: #0F6E56; font-weight: bold; padding: 1px 6px; border-radius: 8px; font-size: 9px; }
.total-row td { background: #3C3489; color: white; font-weight: bold; }
.italic-empty { color: #888780; font-style: italic; }
.hr-box { border: 1px solid #534AB7; margin-top: 14px; }
.hr-box-header { background: #3C3489; color: white; padding: 8px 12px; }
.hr-box-header-title { font-size: 12px; font-weight: bold; }
.hr-box-header-sub { font-size: 9px; opacity: 0.6; margin-top: 2px; }
.hr-box-body { padding: 12px; }
.formula-box { background: #faeeda; border: 1px solid #EF9F27; padding: 6px 10px; font-size: 9px; color: #854F0B; margin-bottom: 10px; border-radius: 4px; }
.score-summary { margin-bottom: 10px; }
.score-row { display: table; width: 100%; border-bottom: 0.5px solid #e0daf5; padding: 4px 0; }
.score-row-left { display: table-cell; color: #888780; font-size: 10px; }
.score-row-right { display: table-cell; text-align: right; font-weight: bold; color: #1a1a2e; }
.overall-row { border-top: 2px solid #3C3489; padding-top: 8px; margin-top: 6px; }
.overall-score { font-size: 32px; font-weight: bold; color: #3C3489; }
.grade-badge { font-size: 40px; font-weight: bold; color: #3C3489; text-align: right; }
.grade-row { display: table; width: 100%; margin: 8px 0; }
.grade-left { display: table-cell; vertical-align: middle; }
.grade-right { display: table-cell; vertical-align: middle; text-align: right; }
.hr-comments { margin-top: 10px; }
.hr-comments-label { font-size: 9px; color: #888780; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.hr-comments-text { font-size: 11px; color: #1a1a2e; line-height: 1.6; }
.confirmation-box { background: #f5f0ff; border: 1px solid #AFA9EC; border-radius: 4px; padding: 10px 12px; margin-bottom: 14px; font-size: 10px; }
.sig-section { margin-top: 24px; }
.sig-grid { display: table; width: 100%; }
.sig-cell { display: table-cell; width: 33.33%; padding: 0 10px; }
.sig-cell:first-child { padding-left: 0; }
.sig-cell:last-child { padding-right: 0; }
.sig-line { border-top: 1px solid #1a1a2e; padding-top: 4px; text-align: center; font-size: 9px; color: #5F5E5A; }
.sig-name { font-weight: bold; font-size: 10px; color: #1a1a2e; }
.footer { margin-top: 20px; border-top: 1px solid #e0daf5; padding-top: 8px; text-align: center; font-size: 9px; color: #888780; }
.field-group { display: table; width: 100%; margin-top: 8px; }
.field-cell { display: table-cell; width: 33.33%; padding: 0 8px 0 0; vertical-align: top; }
.field-label { font-size: 9px; color: #888780; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
.field-value { font-size: 10px; color: #1a1a2e; }
.policy-table td { padding: 4px 8px; }
.page-break { page-break-before: always; }
</style>
</head>
<body>
<div class="page">

  {{-- Header --}}
  <div class="header">
    <div class="org-name">iPerform · Staff Appraisals</div>
    <div class="doc-title">Staff Performance Appraisal Form</div>
    <div class="cycle-name">{{ strtoupper($appraisal->cycle->name) }}</div>
  </div>

  {{-- Vital Information --}}
  <table style="margin-bottom:14px; font-size:10px;">
    <tr>
      <th style="width:20%">Staff Name</th>
      <td style="width:30%"><strong>{{ $appraisal->staff->name }}</strong></td>
      <th style="width:20%">Supervisor</th>
      <td style="width:30%"><strong>{{ $appraisal->supervisor->name }}</strong></td>
    </tr>
    <tr>
      <th>Department / Unit</th>
      <td>{{ $appraisal->staff->department }}</td>
      <th>Designation</th>
      <td>{{ $appraisal->staff->designation }}</td>
    </tr>
    <tr>
      <th>Period Appraised</th>
      <td>{{ $appraisal->cycle->name }}</td>
      <th>Status</th>
      <td>{{ ucfirst(str_replace('_',' ',$appraisal->status)) }}</td>
    </tr>
  </table>

  {{-- Section 1 --}}
  <div class="section">
    <div class="section-header-purple">
      Section 1: Major Targets & KRA <span class="section-weight">35%</span>
    </div>
    <table>
      <thead>
        <tr><th style="width:4%">#</th><th style="width:22%">KRA for the Month</th><th style="width:18%">Target</th><th style="width:26%">Achievement</th><th style="width:10%; text-align:center">% Done</th><th style="width:10%; text-align:center">Staff</th><th style="width:10%; text-align:center">Supervisor</th></tr>
      </thead>
      <tbody>
        @forelse($appraisal->kras as $row)
        <tr>
          <td>{{ $row->sn }}</td>
          <td>{{ $row->kra }}</td>
          <td>{{ $row->target }}</td>
          <td>{{ $row->achievement }}</td>
          <td style="text-align:center">
            @php $pct = $row->completion_percentage ?? 0; @endphp
            <div style="font-weight:bold;color:{{ $pct>=80?'#0F6E56':($pct>=50?'#854F0B':'#A32D2D') }};font-size:10px;">{{ $pct }}%</div>
            <div style="background:#e0daf5;height:4px;border-radius:2px;margin-top:2px;">
              <div style="background:{{ $pct>=80?'#0F6E56':($pct>=50?'#EF9F27':'#c0392b') }};height:4px;border-radius:2px;width:{{ $pct }}%;"></div>
            </div>
          </td>
          <td style="text-align:center"><span class="score-badge">{{ $row->staff_score ?? '—' }}</span></td>
          <td style="text-align:center"><span class="score-badge-green">{{ $row->supervisor_score ?? '—' }}</span></td>
        </tr>
        @empty
        <tr><td colspan="7" class="italic-empty">None recorded</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Section 2 --}}
  <div class="section">
    <div class="section-header-purple">
      Section 2: Routine & Other Tasks Assigned <span class="section-weight">25%</span>
    </div>
    <table>
      <thead>
        <tr><th style="width:4%">#</th><th style="width:28%">Task</th><th style="width:36%">Performance & Achievement</th><th style="width:10%; text-align:center">% Done</th><th style="width:11%; text-align:center">Staff</th><th style="width:11%; text-align:center">Supervisor</th></tr>
      </thead>
      <tbody>
        @forelse($appraisal->tasks as $row)
        <tr>
          <td>{{ $row->sn }}</td>
          <td>{{ $row->task }}</td>
          <td>{{ $row->performance }}</td>
          <td style="text-align:center">
            @php $pct = $row->completion_percentage ?? 0; @endphp
            <div style="font-weight:bold;color:{{ $pct>=80?'#0F6E56':($pct>=50?'#854F0B':'#A32D2D') }};font-size:10px;">{{ $pct }}%</div>
            <div style="background:#e0daf5;height:4px;border-radius:2px;margin-top:2px;">
              <div style="background:{{ $pct>=80?'#0F6E56':($pct>=50?'#EF9F27':'#c0392b') }};height:4px;border-radius:2px;width:{{ $pct }}%;"></div>
            </div>
          </td>
          <td style="text-align:center"><span class="score-badge">{{ $row->staff_score ?? '—' }}</span></td>
          <td style="text-align:center"><span class="score-badge-green">{{ $row->supervisor_score ?? '—' }}</span></td>
        </tr>
        @empty
        <tr><td colspan="6" class="italic-empty">None recorded</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Section 3 --}}
  <div class="section">
    <div class="section-header-purple">
      Section 3: Ideas, Innovations & Outstanding Contributions <span class="section-weight">20%</span>
    </div>
    <table>
      <thead>
        <tr><th style="width:4%">#</th><th style="width:32%">Idea / Contribution</th><th style="width:32%">Impact</th><th style="width:10%; text-align:center">% Done</th><th style="width:11%; text-align:center">Staff</th><th style="width:11%; text-align:center">Supervisor</th></tr>
      </thead>
      <tbody>
        @forelse($appraisal->innovations as $row)
        <tr>
          <td>{{ $row->sn }}</td>
          <td>{{ $row->idea }}</td>
          <td>{{ $row->impact }}</td>
          <td style="text-align:center">
            @php $pct = $row->completion_percentage ?? 0; @endphp
            <div style="font-weight:bold;color:{{ $pct>=80?'#0F6E56':($pct>=50?'#854F0B':'#A32D2D') }};font-size:10px;">{{ $pct }}%</div>
            <div style="background:#e0daf5;height:4px;border-radius:2px;margin-top:2px;">
              <div style="background:{{ $pct>=80?'#0F6E56':($pct>=50?'#EF9F27':'#c0392b') }};height:4px;border-radius:2px;width:{{ $pct }}%;"></div>
            </div>
          </td>
          <td style="text-align:center"><span class="score-badge">{{ $row->staff_score ?? '—' }}</span></td>
          <td style="text-align:center"><span class="score-badge-green">{{ $row->supervisor_score ?? '—' }}</span></td>
        </tr>
        @empty
        <tr><td colspan="6" class="italic-empty">None recorded</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Section 4 --}}
  <div class="section">
    <div class="section-header-purple">
      Section 4: Core Competencies <span class="section-weight">15%</span>
    </div>
    <table>
      <thead>
        <tr><th style="width:5%">#</th><th style="width:60%">Competency</th><th style="width:17%; text-align:center">Staff Score</th><th style="width:18%; text-align:center">Supervisor Score</th></tr>
      </thead>
      <tbody>
        @foreach($appraisal->competencies as $row)
        <tr>
          <td>{{ $row->sn }}</td>
          <td>{{ $row->competency }}</td>
          <td style="text-align:center"><span class="score-badge">{{ $row->staff_score ?? '—' }}</span></td>
          <td style="text-align:center"><span class="score-badge-green">{{ $row->supervisor_score ?? '—' }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Section 5 --}}
  <div class="section">
    <div class="section-header-purple">Section 5: Performance Challenges / Constraints</div>
    <table>
      <thead><tr><th style="width:5%">#</th><th style="width:47%">Challenge Identified</th><th style="width:48%">Impact on Performance</th></tr></thead>
      <tbody>
        @forelse((is_array($appraisal->section5) ? $appraisal->section5 : (json_decode($appraisal->getRawOriginal('section5'), true) ?? [])) as $row)
        <tr><td>{{ $row['sn'] }}</td><td>{{ $row['challenge'] }}</td><td>{{ $row['impact'] }}</td></tr>
        @empty
        <tr><td colspan="3" class="italic-empty">None reported</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Section 6 --}}
  <div class="section">
    <div class="section-header-purple">Section 6: Capacity Development & Training Needs</div>
    <table>
      <thead><tr><th style="width:5%">#</th><th style="width:28%">Area for Improvement</th><th style="width:30%">Recommended Training</th><th style="width:37%">Supervisor Recommendation</th></tr></thead>
      <tbody>
        @forelse((is_array($appraisal->section6) ? $appraisal->section6 : (json_decode($appraisal->getRawOriginal('section6'), true) ?? [])) as $row)
        <tr><td>{{ $row['sn'] }}</td><td>{{ $row['area'] }}</td><td>{{ $row['training'] }}</td><td>{{ $row['recommendation'] ?? '—' }}</td></tr>
        @empty
        <tr><td colspan="4" class="italic-empty">None identified</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Section 7 --}}
  <div class="section">
    <div class="section-header-purple">
      Section 7: Compliance to Administrative Policy <span class="section-weight">20%</span>
    </div>
    <table class="policy-table">
      <thead><tr><th style="width:5%">#</th><th style="width:50%">Policy / Area</th><th style="width:15%; text-align:center">Score (0–10)</th><th style="width:30%">Comments</th></tr></thead>
      <tbody>
        @foreach(is_array($appraisal->section7_items) ? $appraisal->section7_items : (json_decode($appraisal->getRawOriginal('section7_items'), true) ?? $appraisal->getDefaultSection7()) as $item)
        <tr>
          <td>{{ $item['sn'] }}</td>
          <td>{{ $item['policy'] }}</td>
          <td style="text-align:center"><span class="score-badge-green">{{ $item['score'] ?? '—' }}</span></td>
          <td>{{ $item['comment'] ?? '—' }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
          <td colspan="2">Total Section 7 Score</td>
          <td style="text-align:center">{{ collect($appraisal->section7_items)->sum('score') }} / 60</td>
          <td></td>
        </tr>
      </tbody>
    </table>
    @if($appraisal->overall_contribution || $appraisal->key_strengths || $appraisal->areas_for_improvement)
    <div class="field-group" style="margin-top:8px; padding:0 4px;">
      <div class="field-cell">
        <div class="field-label">Overall Contribution</div>
        <div class="field-value">{{ $appraisal->overall_contribution ?: '—' }}</div>
      </div>
      <div class="field-cell">
        <div class="field-label">Key Strengths</div>
        <div class="field-value">{{ $appraisal->key_strengths ?: '—' }}</div>
      </div>
      <div class="field-cell" style="padding-right:0">
        <div class="field-label">Areas for Improvement</div>
        <div class="field-value">{{ $appraisal->areas_for_improvement ?: '—' }}</div>
      </div>
    </div>
    @endif
  </div>

  {{-- Work Confirmation --}}
  <div class="confirmation-box">
    <strong>Work Confirmation (Supervisor):</strong> I hereby confirm that the above-mentioned staff member was actively engaged in assigned duties during the period under review and is eligible for payment.<br>
    <strong>Percentage of Salary to be Paid:</strong> {{ $appraisal->salary_percent ?? '—' }}%
    @if($appraisal->supervisor_comments)
    <br><strong>Supervisor Comments:</strong> {{ $appraisal->supervisor_comments }}
    @endif
  </div>

  {{-- HR Summary --}}
  <div class="hr-box">
    <div class="hr-box-header">
      <div class="hr-box-header-title">HR Summary — Performance Management / HR Only</div>
      <div class="hr-box-header-sub">SUPERVISORS ARE NOT TO GO BEYOND THIS POINT</div>
    </div>
    <div class="hr-box-body">
      <div class="formula-box">
        <strong>Formula:</strong> S1 = (Avg Supervisor Score / 10) × 35 &nbsp;·&nbsp;
        S2 = (Avg / 10) × 25 &nbsp;·&nbsp;
        S3 = (Avg / 10) × 20 &nbsp;·&nbsp;
        S4 = (Sec7 Total / 60) × 20 &nbsp;·&nbsp;
        Overall = S1 + S2 + S3 + S4
      </div>
      <table style="margin-bottom:10px;">
        <tr>
          <td style="width:70%; color:#888780">Section 1 — Major Targets & KRA (35 marks)</td>
          <td style="width:30%; font-weight:bold; color:#3C3489; text-align:right">{{ $appraisal->hr_s1_weighted ?? '—' }}</td>
        </tr>
        <tr>
          <td style="color:#888780">Section 2 — Routines, Other Tasks (25 marks)</td>
          <td style="font-weight:bold; color:#3C3489; text-align:right">{{ $appraisal->hr_s2_weighted ?? '—' }}</td>
        </tr>
        <tr>
          <td style="color:#888780">Section 3 — Ideas, Innovations & Outstanding Contributions (20 marks)</td>
          <td style="font-weight:bold; color:#3C3489; text-align:right">{{ $appraisal->hr_s3_weighted ?? '—' }}</td>
        </tr>
        <tr>
          <td style="color:#888780">Section 4 — Compliance to Administrative Policy (20 marks)</td>
          <td style="font-weight:bold; color:#3C3489; text-align:right">{{ $appraisal->hr_s4_weighted ?? '—' }}</td>
        </tr>
        <tr style="border-top:2px solid #3C3489;">
          <td style="font-weight:bold; font-size:12px; padding-top:6px">Overall Performance Score</td>
          <td style="text-align:right; padding-top:6px">
            <span style="font-size:28px; font-weight:bold; color:#3C3489;">{{ $appraisal->hr_overall ?? '—' }}</span>
          </td>
        </tr>
      </table>

      {{-- Grade scale --}}
      <div style="display:table; width:100%; margin-bottom:10px;">
        <div style="display:table-cell; width:70%; vertical-align:middle; font-size:9px; color:#888780; padding-right:16px;">
          <strong style="color:#1a1a2e; font-size:10px;">April Appraisal Grade Scale:</strong><br>
          100 = A+ &nbsp;|&nbsp; 90–99 = A &nbsp;|&nbsp; 80–89 = B+ &nbsp;|&nbsp; 70–79 = B &nbsp;|&nbsp; 60–69 = C+ &nbsp;|&nbsp; 50–59 = C &nbsp;|&nbsp; 40–49 = D &nbsp;|&nbsp; 30–39 = E &nbsp;|&nbsp; 1–29 = F
        </div>
        <div style="display:table-cell; width:30%; text-align:center; vertical-align:middle;">
          <div style="font-size:9px; color:#888780; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Appraisal Grade</div>
          <div style="font-size:48px; font-weight:bold; color:#3C3489; line-height:1;">{{ $appraisal->hr_grade ?? '—' }}</div>
        </div>
      </div>

      @if($appraisal->hr_comments)
      <div class="hr-comments">
        <div class="hr-comments-label">Performance Management / HR Comments</div>
        <div class="hr-comments-text">{{ $appraisal->hr_comments }}</div>
      </div>
      @endif
    </div>
  </div>

  {{-- Signature Section --}}
  <div class="sig-section">
    <div class="sig-grid">
      <div class="sig-cell">
        <div class="sig-line">
          <div class="sig-name">{{ $appraisal->staff->name }}</div>
          <div>Staff Member</div>
          <div style="margin-top:3px; color:#888780">Date: _______________</div>
        </div>
      </div>
      <div class="sig-cell">
        <div class="sig-line">
          <div class="sig-name">{{ $appraisal->supervisor->name }}</div>
          <div>Supervisor</div>
          <div style="margin-top:3px; color:#888780">Date: _______________</div>
        </div>
      </div>
      <div class="sig-cell">
        <div class="sig-line">
          <div class="sig-name">HR / Performance Management</div>
          <div>{{ $appraisal->staff->department }}</div>
          <div style="margin-top:3px; color:#888780">Date: _______________</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Footer --}}
  <div class="footer">
    iPerform · Staff Appraisal Platform · Generated {{ now()->format('d F Y, H:i') }} · Confidential
  </div>

</div>
</body>
</html>
