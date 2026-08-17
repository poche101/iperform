<?php

namespace App\Notifications;

use App\Models\Appraisal;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class AppraisalSubmitted extends Notification
{
    public function __construct(public Appraisal $appraisal) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Appraisal Submitted')
            ->icon('/icons/icon-192.png')
            ->body("{$this->appraisal->staff->name} just submitted their appraisal.")
            ->data(['url' => route('supervisor.pipeline')])
            ->options(['TTL' => 1000]);
    }
}
