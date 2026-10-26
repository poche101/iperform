<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TaskCommented extends Notification
{
    public function __construct(
        public string $taskTitle,
        public string $comment,
        public string $supervisorName,
        public ?int $taskLogId,
        public int $appraisalId,
    ) {}

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    /** Where the staff member lands: their task (highlighted) or, if it has no task log, the appraisal. */
    private function targetUrl(): string
    {
        if ($this->taskLogId) {
            return route('staff.tasks') . '#task-' . $this->taskLogId;
        }

        return route('staff.appraisal.view', $this->appraisalId);
    }

    private function bodyText(): string
    {
        return $this->supervisorName . ' on "' . Str::limit($this->taskTitle, 50) . '": ' . Str::limit($this->comment, 90);
    }

    /** Stored in the notifications table; read by the bell. */
    public function toArray($notifiable): array
    {
        return [
            'title'       => 'New supervisor comment',
            'body'        => $this->bodyText(),
            'url'         => $this->targetUrl(),
            'task_log_id' => $this->taskLogId,
            'appraisal_id'=> $this->appraisalId,
        ];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        // Opening through notifications.open marks the bell item as read at the same time.
        $openUrl = route('notifications.open', $this->id);

        return (new WebPushMessage)
            ->title('New supervisor comment')
            ->icon(asset('icons/icon-192.png')) // match the icon path used in TaskGraded
            ->body($this->bodyText())
            ->action('View comment', 'view')
            ->data(['url' => $openUrl]);
    }
}
