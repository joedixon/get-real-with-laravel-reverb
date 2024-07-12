<?php

use App\Models\Channel;
use App\Models\User;

use function Livewire\Volt\mount;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

state(['channel', 'channels', 'users', 'subscribers', 'name']);

mount(function (Channel $channel) {
    $this->channels = Channel::all()->toArray();
    $this->users = User::orderBy('name')->get();
    $this->subscribers = $this->channel->getSubscribers();
});

title(fn () => "#{$this->channel->name}");

$createChannel = fn (string $name) => Channel::create(['name' => $name]);

?>

<div
    class="flex w-full rounded-lg bg-fuchsia-800"
    style="height: calc(100vh - 40px)"
>
    <!-- Channels -->
    <div class="shrink-0 overflow-y-scroll rounded-l-lg bg-sidebar">
        <div class="flex h-14 items-center gap-x-32 border-b p-4">
            <h1 class="text-xl font-bold">Laravel</h1>

            <x-icons.pencil class="h-6 w-6 text-gray-500" />
        </div>

        <div class="flex flex-col gap-y-6 p-2 text-lg text-gray-700">
            <ul class="flex flex-col gap-y-3">
                <li class="flex items-center gap-x-2 rounded-md px-4 py-1">
                    <x-icons.message class="h-6 w-6 text-gray-700" />

                    Threads
                </li>

                <li class="flex items-center gap-x-2 rounded-md px-4 py-1">
                    <x-icons.airplane class="h-6 w-6 text-gray-700" />

                    Drafts & sent
                </li>
            </ul>

            <ul
                class="flex flex-col gap-y-2"
                x-data="channels"
            >
                <li class="flex flex-col">
                    <div class="flex">
                        <button
                            @click="open = !open"
                            class="flex items-center gap-x-2 rounded-md px-4 py-1 hover:bg-white/30"
                        >
                            <x-icons.chevron-down class="h-3 w-3 text-gray-700" />
                        </button>

                        <button class="flex items-center justify-between w-full">
                            Channels

                            <x-icons.plus
                                x-show="!openChannelForm"
                                class="h-5 w-5 text-gray-700"
                                @click="openChannelForm = !openChannelForm" 
                            />

                            <x-icons.minus
                                x-show="openChannelForm"
                                class="h-5 w-5 text-gray-700"
                                @click="openChannelForm = !openChannelForm" 
                            />
                        </button>
                    </div>

                    <input
                        x-show="openChannelForm"
                        wire:model="name"
                        type="text"
                        class="w-full rounded-md border border-gray-300 px-3 py-1"
                        placeholder="general"
                        @keyup.enter="openChannelForm = false; $wire.createChannel($event.target.value)"
                    />
                </li>   

                <li>
                    <ul x-cloak x-show="open">
                        <template x-for="channel in $wire.channels">
                            <li>
                                <a
                                    :href="`/${channel.name}`"
                                    class="flex items-center gap-x-2 rounded-md px-4 py-1 hover:bg-fuchsia-900 hover:text-white"
                                    :class="{ 'bg-fuchsia-900 text-white': channel.name === '{{ $channel->name }}' }"
                                >
                                    <x-icons.hashtag
                                        class="text:inherit h-4 w-4"
                                    />

                                    <span x-text="channel.name"></span>
                                </a>
                            </li>
                        </template>
                    </ul>
                </li>
            </ul>

            <ul
                class="flex flex-col gap-y-2"
                x-data="{ open: true }"
            >
                <li>
                    <button
                        @click="open = !open"
                        class="flex w-full items-center gap-x-2 rounded-md px-4 py-1 hover:bg-white/30"
                    >
                        <x-icons.chevron-down class="h-3 w-3 text-gray-700" />

                        Direct messages
                    </button>
                </li>

                <li>
                    @include('components.users')
                </li>
            </ul>
        </div>
    </div>

    <div class="flex w-full flex-col rounded-r-lg bg-white">
        <div class="flex h-14 items-center justify-between border-b p-3">
            <h1 class="text-xl font-bold"># {{ $channel->name }}</h1>

            <div class="flex rounded border p-1" wire:ignore>
                <div class="flex items-center -space-x-1">
                    @foreach ($subscribers->take(3) as $subscriber)
                        <img
                            src="{{ $subscriber->avatar }}"
                            alt="{{ $subscriber->name }}"
                            class="h-5 w-5 rounded-md border border-white"
                        />
                    @endforeach
                </div>

                <span class="px-2">{{ $subscribers->count() }}</span>
            </div>
        </div>

        <!-- Channel -->
        <livewire:channel :channel="$channel" />
    </div>
</div>

@script
<script>
Alpine.data('channels', () => {
    return {
        open: true,

        openChannelForm: false,

        init() {
            Echo.join('workspace')
                .listen('.ChannelCreated', (event) => {
                    this.$wire.channels.push(event.model)
                })
        }
    }
});
</script>
@endscript
