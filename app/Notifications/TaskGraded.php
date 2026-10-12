<?php

namespace App\Notifications;

use App\Models\TaskLog;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TaskGraded extends Notification
{
    public function __construct(public TaskLog $taskLog) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $reviewer = $this->taskLog->reviewer?->name ?? 'Your supervisor';

        return (new WebPushMessage)
            ->title('Your task has been graded')
            ->icon('/icons/icon-192.png')
            ->badge('/icons/icon-72.png')
            ->body("{$reviewer} scored \"{$this->taskLog->title}\": {$this->taskLog->supervisor_score}/10")
            ->data(['url' => route('staff.tasks')])
            ->options(['TTL' => 86400]);
    }
}
