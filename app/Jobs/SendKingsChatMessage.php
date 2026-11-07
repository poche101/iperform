<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\KingsChatMessenger;
use Illuminate\Foundation\Bus\Dispatchable;

class SendKingsChatMessage
{
    use Dispatchable;

    public function __construct(public int $userId, public string $text) {}

    public function handle(KingsChatMessenger $messenger): void
    {
        $user = User::find($this->userId);

        if ($user) {
            $messenger->send($user, $this->text);
        }
    }
}
