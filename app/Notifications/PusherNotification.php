<?php

namespace App\Notifications;

use App\Events\PusherNotification as EventsPusherNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PusherNotification extends Notification
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // تحديد القنوات التي سيُرسل الإشعار إليها: قاعدة البيانات والبث المباشر (Pusher)
    public function via($notifiable)
    {
        return ['database'];
    }

    // البيانات التي سيتم تخزينها في قاعدة البيانات
    public function toDatabase($notifiable)
    {
        event(new EventsPusherNotification($this->data, $notifiable));
        return $this->data;
    }

    // البيانات التي سيتم إرسالها إلى Pusher (البث المباشر)
    // public function toBroadcast($notifiable)
    // {
    //     return new BroadcastMessage([
    //         'title' => $this->title,
    //         'message' => $this->message,
    //     ]);
    // }

    // public function broadcastOn()
    // {
    //     return new Channel('notification.' . $notifiable->id);
    // }

    // public function broadcastAs()
    // {
    //     return 'message';
    // }
}
