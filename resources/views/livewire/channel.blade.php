<?php

use function Livewire\Volt\mount;
use function Livewire\Volt\state;

state(['channel', 'messages', 'subscribed']);

mount(function ($channel) {
    $this->messages = $this->channel->getMessages()->toArray();
    $this->subscribed = $this->channel->isSubscribed(auth()->user());
});

$join = fn () => ($this->subscribed = $this->channel->subscribe(auth()->user()));

$send = fn (string $message) => $this->channel->send(auth()->user(), $message);

?>

<div
    x-data="channel"
    class="flex h-full w-full flex-col justify-between p-4 pb-2"
    style="height: calc(100vh - 100px)"
>
    <div
        class="messages mb-4 flex h-full grow flex-col overflow-y-scroll"
        x-ref="messages"
    >
        @if ($subscribed)
            <span
                class="mt-auto w-full py-4 text-center text-lg"
                :class="{ 'mb-4 border-b': $wire.messages.length > 0 }"
            >
                This is the very beginning of the
                <strong>{{ $channel->name }}</strong>
                channel.
            </span>

            <template x-for="message in $wire.messages">
                <div class="flex gap-x-2">
                    <img
                        :src="message.user.avatar"
                        :alt="message.user.name"
                        class="h-10 w-10 rounded-md"
                    />

                    <div>
                        <div class="flex items-center gap-x-2">
                            <span
                                class="text-lg font-bold"
                                x-text="message.user.name"
                            ></span>

                            <time
                                class="text-sm text-gray-600"
                                x-text="message.sent_at"
                            ></time>
                        </div>

                        <div x-html="message.content" class="text-lg"></div>
                    </div>
                </div>
            </template>
        @endif
    </div>

    <div
        class="flex w-full"
        @submitted.stop="send($event.detail.message)"
    >
        @if ($subscribed)
            <div class="flex w-full flex-col gap-y-1">
                <x-editor channel="{{ $channel->name }}" />

                <!-- Typing Indicator -->
                <span class="block shrink-0 text-xs text-gray-500 after:content-['\200b']"></span>
            </div>
        @else
            <div class="flex flex flex-grow flex-col items-center justify-center gap-y-4 rounded-md border bg-gray-100 p-6">
                <span class="text-lg font-bold">
                    #{{ $channel->name }}
                </span>

                <button
                    type="submit"
                    class="rounded-md bg-green-800 px-4 py-2 text-base text-white"
                    wire:click="join"
                >
                    Join channel
                </button>
            </div>
        @endif
    </div>
</div>

@script
<script>
Alpine.data('channel', () => {
    return {
        isTyping: false,

        usersTyping: [],

        channel: null,

        init() {
            this.scrollPosition()
            this.channel = Echo.channel('channels.{{ $channel->id }}')
            this.channel.listen('MessageSent', (event) => {
                this.$wire.messages.push(event.message)
            })
        },

        send(message) {
            this.$wire.send(message)
        },

        scrollPosition() {
            this.$watch('$wire.messages', () => {
                this.$refs.messages.scrollTop =
                    this.$refs.messages.scrollHeight;
            });
        },
    }
})
</script>
@endscript
