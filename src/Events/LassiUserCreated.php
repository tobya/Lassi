<?php

namespace Lassi\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LassiUserCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $lassiuserdata;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($lassiuserdata, $user)
    {
        $this->user = $user;
        $this->lassiuserdata = $lassiuserdata;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
