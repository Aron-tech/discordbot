<?php

use App\Enums\PermissionEnum;
use App\Models\Guild;
use App\Models\GuildSelector;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use TallStackUi\Traits\Interactions;
use Livewire\Attributes\{Layout, Title};


new
#[Layout('layouts.app')]
#[Title('Select server')]
class extends Component {

    use Interactions;

    public array $bot_guilds = [];

    public array $user_owner_guilds = [];

    public array $guilds = [];

    public function mount()
    {
        $this->loadGuilds();

        if (GuildSelector::hasGuild())
            GuildSelector::clearGuild();
    }

    public function loadGuilds()
    {
        $this->guilds = $user_guilds = Http::withHeaders([
            'Authorization' => 'Bearer ' . auth()->user()->d_token,
        ])->get('https://discord.com/api/users/@me/guilds')->json();

        $bot_guild_ids = Guild::pluck('guild_id')->toArray();

        $this->bot_guilds = collect($user_guilds)->filter(function ($guild) use ($bot_guild_ids) {
            return in_array($guild['id'], $bot_guild_ids);
        })->values()->toArray();

        $this->user_owner_guilds = collect($user_guilds)->filter(function ($guild) use ($bot_guild_ids) {
            $has_manage_guild = ($guild['permissions'] & 0x20) === 0x20;

            return ($guild['owner'] === true || $has_manage_guild) && !in_array($guild['id'], $bot_guild_ids);
        })->values()->toArray();
    }

    public function sendInfo()
    {
        $this->dialog()
            ->success('Sikeres mentés!', 'A felhasználó sikeresen el lett mentve.')
            ->send();
    }

    public function select($guild_id)
    {
        $guild = Guild::where('guild_id', $guild_id)->first();

        if (!$guild) {
            $external_url = 'https://discord.com/oauth2/authorize?client_id=1377217826104016928&permissions=8&integration_type=0&scope=bot';
            return $this->dispatch('open-external', url: $external_url);
        }

        if (!$guild->installed && auth()->user()->cannot('hasPermission', [$guild, PermissionEnum::EDIT_SETTINGS])) {
            $this->toast()
                ->error('Hiba!', 'A bot nincs telepítve a kiválasztott szerveren.')
                ->send();
            return;
        }

        GuildSelector::setGuild($guild);
    }
}; ?>

<div>
    <x-tab selected="{{__('Csatlakozott')}}">
        <x-tab.items tab="{{__('Csatlakozott')}}">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                @foreach($bot_guilds as $guild)
                    <x-card image="https://cdn.discordapp.com/icons/{{$guild['id']}}/{{$guild['icon']}}.jpg">
                        <div class="flex justify-between">
                            <x-h4>{{$guild['name']}}</x-h4>
                            <x-button.circle
                                wire:click="select({{ $guild['id'] }})"
                                icon="arrow-long-right"
                                loading
                            />
                        </div>
                    </x-card>
                @endforeach
            </div>
        </x-tab.items>
        <x-tab.items tab="{{__('Csatlakozásra vár')}}" wire:click='sendInfo()'>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                @foreach($user_owner_guilds as $guild)
                    <x-card image="https://cdn.discordapp.com/icons/{{$guild['id']}}/{{$guild['icon']}}.jpg">
                        <div class="flex justify-between">
                            <x-h4>{{$guild['name']}}</x-h4>
                            <x-button.circle
                                wire:click="select({{ $guild['id'] }})"
                                icon="arrow-long-right"
                                loading
                            />
                        </div>
                    </x-card>
                @endforeach
            </div>
        </x-tab.items>
    </x-tab>
    <div
        x-data
        x-init="$wire.on('open-external', e => window.open(e.url, '_blank'))"
    ></div>
</div>

