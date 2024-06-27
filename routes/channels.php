<?php

use App\Models\Channel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('channels.{channel}', function ($user, Channel $channel) {
    return $channel->isSubscribed($user);
});
