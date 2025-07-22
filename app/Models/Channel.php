<?php

namespace App\Models;

use Illuminate\Broadcasting\Channel as BroadcastingChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Broadcast;

class Channel extends Model
{
    use BroadcastsEvents, HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * The users subscribed to the channel.
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subscriptions')
            ->withTimestamps();
    }

    /**
     * The messages sent to the channel.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Determine whether the given user is subscribed to the channel.
     */
    public function isSubscribed(User $user): bool
    {
        return $this->subscribers->contains($user);
    }

    /**
     * Get the channel's subscribers in random order.
     */
    public function getSubscribers(): Collection
    {
        return $this->subscribers()->inRandomOrder()->get();
    }

    /**
     * Get the channel's messages with user.
     */
    public function getMessages(): Collection
    {
        return $this->messages()->with('user')->get();
    }

    /**
     * Subscribe the given user to the channel.
     */
    public function subscribe(User $user): bool
    {
        return $this->subscribers()->attach($user) === null;
    }

    /**
     * Send a message to the channel.
     */
    public function send(User $user, string $message): void
    {
        if (! $message) {
            return;
        }

        $message = $this->messages()->create([
            'user_id' => $user->id,
            'content' => $message,
            'sent_at' => now(),
        ]);

        Broadcast::private('channels.'.$this->id)
            ->as('App\\Events\\MessageSent')
            ->with(['message' => $message->load('user')])
            ->send();
    }

    /**
     * Get the channels that model events should broadcast on.
     */
    public function broadcastOn(string $event): BroadcastingChannel|array
    {
        return match ($event) {
            'created' => new PresenceChannel('workspace'),
            default => []
        };
    }
}
