<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoCambioRecibido implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct() {}

    public function broadcastOn(): array
    {
        return [
            new Channel('panel-alertas'),
        ];
    }
}
