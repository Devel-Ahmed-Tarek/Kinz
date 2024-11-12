<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId || $user->isFriendWith($receiverId);
});

// إعداد Pusher
// var pusher = new Pusher('your_app_key', {
//     cluster: 'your_cluster',
//     encrypted: true
// });

// الاشتراك في القناة الخاصة
// var channel = pusher.subscribe('private-chat.' + receiverId);

// الاستماع إلى الحدث المخصص 'my-event'
// channel.bind('my-event', function(data) {
// console.log('Message received:', data.message);
// يمكنك تحديث واجهة المستخدم بالرسالة الجديدة هنا
// });
