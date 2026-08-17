<?php

namespace App\Notifications;

use App\Models\TaskLog;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TaskSubmitted extends Notification
{
    public function __construct(public TaskLog $taskLog) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('New task submitted')
            ->icon('/icons/icon-192.png')
            ->badge('/icons/icon-72.png')
            ->body("{$this->taskLog->staff->name}: {$this->taskLog->title}")
            ->data(['url' => route('supervisor.tasks')])
            ->options(['TTL' => 1000]);
    }
}
