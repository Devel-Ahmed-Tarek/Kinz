<?php
// app/Events/MessageSent.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $sender;
    public $receiver;

    public function __construct($message, $sender, $receiver)
    {
        $this->message = $message;
        $this->receiver = $receiver;
        $this->sender = $sender;

    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.' . $this->receiver . '.' . $this->sender->id),
        ];
    }

    public function broadcastAs()
    {
        return 'chat';
    }

}
