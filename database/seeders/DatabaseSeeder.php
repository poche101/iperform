<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AppraisalCycle;
use App\Models\Appraisal;
use App\Models\AppraisalKra;
use App\Models\AppraisalTask;
use App\Models\AppraisalInnovation;
use App\Models\AppraisalCompetency;
use App\Models\TaskLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // HR
        $hr = User::create([
            'name' => 'Pst. Adaobi Hart',
            'username' => 'hr',
            'email' => 'hr@iperform.app',
            'password' => Hash::make('password'),
            'role' => 'hr',
        ]);

        // Supervisor
        $sup = User::create([
            'name' => 'Pst. Charis Onuoha',
            'username' => 'sup',
            'email' => 'supervisor@iperform.app',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
            'title' => 'Lead Supervisor',
        ]);

        // Staff
        $staff = [
            ['name'=>'Paul Orife','username'=>'paul','email'=>'paul@iperform.app','department'=>'Media Team','designation'=>'Web Developer'],
            ['name'=>'Grace Eze','username'=>'grace','email'=>'grace@iperform.app','department'=>'Worship Team','designation'=>'Choir Director'],
            ['name'=>'Daniel Okafor','username'=>'daniel','email'=>'daniel@iperform.app','department'=>'Tech Team','designation'=>'IT Support'],
            ['name'=>'Mary Adesina','username'=>'mary','email'=>'mary@iperform.app','department'=>"Children's Ministry",'designation'=>'Department Head'],
        ];

        $staffUsers = [];
        foreach ($staff as $s) {
            $staffUsers[] = User::create(array_merge($s, [
                'password' => Hash::make('password'),
                'role' => 'staff',
                'supervisor_id' => $sup->id,
            ]));
        }

        // Active Cycle
        $cycle = AppraisalCycle::create([
            'name' => 'June 2026',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'deadline' => '2026-06-25',
            'is_active' => true,
        ]);

        // Seed Paul's appraisal (submitted)
        $paul = $staffUsers[0];
        $appraisal = Appraisal::create([
            'cycle_id' => $cycle->id,
            'staff_id' => $paul->id,
            'supervisor_id' => $sup->id,
            'status' => 'submitted',
            'submitted_at' => now(),
            'section5' => json_encode([
                ['sn'=>1,'challenge'=>'Limited cloud hosting budget','impact'=>'Slowed deployment of some features'],
            ]),
            'section6' => json_encode([
                ['sn'=>1,'area'=>'Cloud architecture','training'=>'AWS certification course','recommendation'=>''],
            ]),
            'section7_items' => json_encode([
                ['sn'=>1,'policy'=>'Attendance at work','score'=>null,'comment'=>''],
                ['sn'=>2,'policy'=>'Chapel Attendance','score'=>null,'comment'=>''],
                ['sn'=>3,'policy'=>'Punctuality to work','score'=>null,'comment'=>''],
                ['sn'=>4,'policy'=>'Prompt submission of weekly/monthly reports','score'=>null,'comment'=>''],
                ['sn'=>5,'policy'=>'Participation in meetings & prayer','score'=>null,'comment'=>''],
                ['sn'=>6,'policy'=>'Official warnings/disciplinary actions','score'=>null,'comment'=>''],
            ]),
        ]);

        foreach ([
            [1,'Develop meeting transcription application','Automated voice-to-text solution','Implemented transcription process that enhanced accuracy and speed.',100,10,null],
            [2,'Deploy new convert tracking app for 5 churches','Multi-church platform','Delivered application adopted by five churches.',80,10,null],
        ] as [$sn,$kra,$target,$achievement,$pct,$ss,$supscore]) {
            AppraisalKra::create(['appraisal_id'=>$appraisal->id,'sn'=>$sn,'kra'=>$kra,'target'=>$target,'achievement'=>$achievement,'completion_percentage'=>$pct,'staff_score'=>$ss,'supervisor_score'=>$supscore]);
        }
        foreach ([
            [1,'Server maintenance and security patches','Reduced server response time by 20%. Maintained secure environment.',100,10,null],
            [2,'Assisted media team with jib camera operations','Operated jib camera during live Sunday productions.',75,10,null],
        ] as [$sn,$task,$perf,$pct,$ss,$supscore]) {
            AppraisalTask::create(['appraisal_id'=>$appraisal->id,'sn'=>$sn,'task'=>$task,'performance'=>$perf,'completion_percentage'=>$pct,'staff_score'=>$ss,'supervisor_score'=>$supscore]);
        }
        AppraisalInnovation::create(['appraisal_id'=>$appraisal->id,'sn'=>1,'idea'=>'Automated attendance QR code system','impact'=>'Replaced manual tracking, reduced errors significantly.','completion_percentage'=>60,'staff_score'=>8,'supervisor_score'=>null]);
        foreach ([
            [1,'Communication Skills',8,null],[2,'Teamwork and Collaboration',9,null],
            [3,'Problem Solving Ability',9,null],[4,'Initiative and Proactiveness',10,null],
            [5,'Professional Conduct and Work Ethics',9,null],
        ] as [$sn,$comp,$ss,$supscore]) {
            AppraisalCompetency::create(['appraisal_id'=>$appraisal->id,'sn'=>$sn,'competency'=>$comp,'staff_score'=>$ss,'supervisor_score'=>$supscore]);
        }

        // Seed Mary's appraisal (approved with HR scores)
        $mary = $staffUsers[3];
        $maryAppraisal = Appraisal::create([
            'cycle_id' => $cycle->id,
            'staff_id' => $mary->id,
            'supervisor_id' => $sup->id,
            'status' => 'approved',
            'submitted_at' => now()->subDays(5),
            'forwarded_at' => now()->subDays(3),
            'approved_at' => now()->subDays(1),
            'section5' => json_encode([]),
            'section6' => json_encode([['sn'=>1,'area'=>'Leadership development','training'=>'Advanced leadership workshop','recommendation'=>'Recommended for senior role']]),
            'section7_items' => json_encode([
                ['sn'=>1,'policy'=>'Attendance at work','score'=>10,'comment'=>''],
                ['sn'=>2,'policy'=>'Chapel Attendance','score'=>10,'comment'=>''],
                ['sn'=>3,'policy'=>'Punctuality to work','score'=>10,'comment'=>''],
                ['sn'=>4,'policy'=>'Prompt submission of weekly/monthly reports','score'=>10,'comment'=>''],
                ['sn'=>5,'policy'=>'Participation in meetings & prayer','score'=>10,'comment'=>''],
                ['sn'=>6,'policy'=>'Official warnings/disciplinary actions','score'=>10,'comment'=>''],
            ]),
            'overall_contribution' => 'Exceptional leader',
            'key_strengths' => 'Leadership, creativity, dedication',
            'areas_for_improvement' => 'Delegation skills',
            'salary_percent' => 100,
            'supervisor_comments' => 'Top performer, recommend for promotion.',
            'supervisor_confirmed' => true,
            'hr_s1_weighted' => 35,
            'hr_s2_weighted' => 22.5,
            'hr_s3_weighted' => 18,
            'hr_s4_weighted' => 20,
            'hr_overall' => 95.5,
            'hr_grade' => 'A+',
            'hr_comments' => 'Mary demonstrates exceptional performance across all areas.',
        ]);
        AppraisalKra::create(['appraisal_id'=>$maryAppraisal->id,'sn'=>1,'kra'=>'Children curriculum development','target'=>'12-week program','achievement'=>'Completed full 12-week curriculum with 95% attendance.','completion_percentage'=>100,'staff_score'=>10,'supervisor_score'=>10]);
        AppraisalTask::create(['appraisal_id'=>$maryAppraisal->id,'sn'=>1,'task'=>'Weekly children service coordination','performance'=>'Coordinated 4 services per month with avg 120 children.','completion_percentage'=>100,'staff_score'=>9,'supervisor_score'=>9]);
        AppraisalInnovation::create(['appraisal_id'=>$maryAppraisal->id,'sn'=>1,'idea'=>'Parent communication app integration','impact'=>'Improved parent-church engagement by 60%.','completion_percentage'=>90,'staff_score'=>9,'supervisor_score'=>9]);
        foreach ([
            [1,'Communication Skills',10,10],[2,'Teamwork and Collaboration',10,9],
            [3,'Problem Solving Ability',9,9],[4,'Initiative and Proactiveness',10,10],
            [5,'Professional Conduct and Work Ethics',10,10],
        ] as [$sn,$comp,$ss,$supscore]) {
            AppraisalCompetency::create(['appraisal_id'=>$maryAppraisal->id,'sn'=>$sn,'competency'=>$comp,'staff_score'=>$ss,'supervisor_score'=>$supscore]);
        }

        // Daniel: with_hr
        $daniel = $staffUsers[2];
        $danielAppraisal = Appraisal::create([
            'cycle_id' => $cycle->id,
            'staff_id' => $daniel->id,
            'supervisor_id' => $sup->id,
            'status' => 'with_hr',
            'submitted_at' => now()->subDays(4),
            'forwarded_at' => now()->subDays(2),
            'section5' => json_encode([['sn'=>1,'challenge'=>'Outdated equipment','impact'=>'Some tasks took longer than planned']]),
            'section6' => json_encode([['sn'=>1,'area'=>'Network security','training'=>'CCNA Certification','recommendation'=>'Strongly recommended']]),
            'section7_items' => json_encode([
                ['sn'=>1,'policy'=>'Attendance at work','score'=>10,'comment'=>'Exemplary'],
                ['sn'=>2,'policy'=>'Chapel Attendance','score'=>9,'comment'=>'Consistent'],
                ['sn'=>3,'policy'=>'Punctuality to work','score'=>10,'comment'=>''],
                ['sn'=>4,'policy'=>'Prompt submission of weekly/monthly reports','score'=>9,'comment'=>''],
                ['sn'=>5,'policy'=>'Participation in meetings & prayer','score'=>10,'comment'=>''],
                ['sn'=>6,'policy'=>'Official warnings/disciplinary actions','score'=>10,'comment'=>'No incidents'],
            ]),
            'overall_contribution' => 'Excellent team player',
            'key_strengths' => 'Technical expertise, reliability',
            'areas_for_improvement' => 'Communication with non-technical staff',
            'salary_percent' => 100,
            'supervisor_comments' => 'Outstanding performance this cycle.',
            'supervisor_confirmed' => true,
        ]);
        AppraisalKra::create(['appraisal_id'=>$danielAppraisal->id,'sn'=>1,'kra'=>'Network infrastructure upgrade','target'=>'Zero downtime migration','achievement'=>'Completed migration with 99.9% uptime maintained.','completion_percentage'=>100,'staff_score'=>9,'supervisor_score'=>9]);
        AppraisalTask::create(['appraisal_id'=>$danielAppraisal->id,'sn'=>1,'task'=>'IT helpdesk support','performance'=>'Resolved 47 tickets, avg resolution 2hrs.','completion_percentage'=>95,'staff_score'=>8,'supervisor_score'=>8]);
        AppraisalInnovation::create(['appraisal_id'=>$danielAppraisal->id,'sn'=>1,'idea'=>'Implemented ticketing system','impact'=>'Reduced resolution time by 40%.','completion_percentage'=>85,'staff_score'=>8,'supervisor_score'=>7]);
        foreach ([
            [1,'Communication Skills',8,8],[2,'Teamwork and Collaboration',9,9],
            [3,'Problem Solving Ability',9,9],[4,'Initiative and Proactiveness',8,8],
            [5,'Professional Conduct and Work Ethics',9,9],
        ] as [$sn,$comp,$ss,$supscore]) {
            AppraisalCompetency::create(['appraisal_id'=>$danielAppraisal->id,'sn'=>$sn,'competency'=>$comp,'staff_score'=>$ss,'supervisor_score'=>$supscore]);
        }

        // Grace: drafting
        $grace = $staffUsers[1];
        Appraisal::create([
            'cycle_id' => $cycle->id,
            'staff_id' => $grace->id,
            'supervisor_id' => $sup->id,
            'status' => 'drafting',
            'section7_items' => json_encode([
                ['sn'=>1,'policy'=>'Attendance at work','score'=>null,'comment'=>''],
                ['sn'=>2,'policy'=>'Chapel Attendance','score'=>null,'comment'=>''],
                ['sn'=>3,'policy'=>'Punctuality to work','score'=>null,'comment'=>''],
                ['sn'=>4,'policy'=>'Prompt submission of weekly/monthly reports','score'=>null,'comment'=>''],
                ['sn'=>5,'policy'=>'Participation in meetings & prayer','score'=>null,'comment'=>''],
                ['sn'=>6,'policy'=>'Official warnings/disciplinary actions','score'=>null,'comment'=>''],
            ]),
        ]);

        // -----------------------------------------------
        // Task logs for Paul (staff1) — demo data
        // -----------------------------------------------
        $taskData = [
            ['Live-streamed midweek service','2026-06-10','Ran OBS, monitored chat, archived the full recording.','KRA',7,80,'awaiting',null,null,null],
            ['Edited Sunday service highlights reel','2026-06-08','Cut 90s recap from 4-cam footage and pushed to YouTube + socials.','Routine',8,100,'graded',$sup->id,'Strong pacing. Next time include sermon pull-quote on screen.',now()->subDays(2)],
            ['Two-hour rehearsal coordination','2026-06-05','Coordinated two-hour rehearsal with the full team for Sunday set.','KRA',9,100,'graded',$sup->id,'Excellent organisation and leadership.',now()->subDays(4)],
            ['Updated church website banner','2026-06-03','Designed and deployed new banner for upcoming conference.','Routine',8,100,'graded',$sup->id,'Clean design, well done.',now()->subDays(6)],
            ['Automated attendance QR system','2026-06-01','Built QR code check-in system to replace manual attendance.','Innovation',8,60,'awaiting',null,null,null],
        ];

        foreach ($taskData as [$title,$date,$details,$category,$selfScore,$pct,$status,$reviewedBy,$comment,$reviewedAt]) {
            TaskLog::create([
                'staff_id'              => $paul->id,
                'cycle_id'              => $cycle->id,
                'title'                 => $title,
                'date'                  => $date,
                'details'               => $details,
                'category'              => $category,
                'self_score'            => $selfScore,
                'completion_percentage' => $pct,
                'status'                => $status,
                'reviewed_by'           => $reviewedBy,
                'supervisor_score'      => $reviewedBy ? 8 : null,
                'supervisor_comment'    => $comment,
                'reviewed_at'           => $reviewedAt,
            ]);
        }

    }
}
