<?php

use App\Enums\PermissionEnum;
use App\Livewire\Traits\DcMessageTrait;
use App\Models\BlackList;
use App\Models\Guild;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title, On};
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Session;
use App\Models\GuildSelector;

new
#[Layout('layouts.app')]
#[Title('Feketelist - Admin')]
class extends Component {

    use withPagination;
    use Interactions;
    use DcMessageTrait;

    public ?int $quantity = 10;

    public ?string $search = null;

    public ?Guild $guild = null;

    public ?string $blacklist_reason = null;
    public ?string $blacklist_discord_id = null;

    public function mount(): void
    {
        $this->guild = GuildSelector::getGuild();
    }

    public function deleteBlacklist($blacklist_id): void
    {
        $blacklist = $this->guild->blacklistsWithTrashed()->find($blacklist_id);

        if (!isset($blacklist)) {
            $this->toast()->warning('Figyelmeztetés!', 'A feketelista nem található.')->send();
            return;
        }

        if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::DELETE_BLACKLIST])) {
            $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod a feketelistán szereplő értéket törölni.')->send();
            return;
        }

        $blacklist_id = $blacklist->id;

        if ($blacklist->deleted_at) {
            $this->dialog()
                ->question('Figyelmeztetés!', 'Biztosan véglegesen törölni szeretnéd?')
                ->confirm('Törlés', method: 'destroyBlacklist', params: [$blacklist_id])
                ->cancel('Mégsem')
                ->send();
        } else {
            $blacklisted_user_id = $blacklist->user_discord_id;
            $blacklist->delete();
            $this->toast()->success('Sikeres művelet', 'Sikeresen törölted a feketelistát.')->send();
            $channel_id = $this->getDutyLogChannelId($this->guild);
            $this->sendDefaultLog($channel_id, [
                'message' => "A felhasználó eltávolította <@{$blacklisted_user_id}> felhasználót a feketelistáról.",
                'user' => auth()->id(),
            ]);
        }
    }

    public function destroyBlacklist($blacklist_id): void
    {
        $blacklist = $this->guild->blacklistsWithTrashed()->where('id', $blacklist_id)->first();
        $blacklisted_user_id = $blacklist->user_discord_id;
        $blacklist->forceDelete();

        $this->toast()->success('Sikeres művelet', 'Sikeresen törölted a feketelistát.')->send();

        $channel_id = $this->getDutyLogChannelId($this->guild);
        $this->sendDefaultLog($channel_id, [
            'message' => "A felhasználó véglegesen törölte <@{$blacklisted_user_id}> felhasználót a feketelistáról.",
            'user' => auth()->id(),
        ]);
    }

    public function addBlacklist(): void
    {
        if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::ADD_BLACKLIST])) {
            $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod a feketelistához hozzáadni.')->send();
            return;
        }
        $validated = $this->validate([
            'blacklist_discord_id' => [
                'required',
                'string',
                'min:5',
                Rule::exists('users', 'discord_id'),
            ],
            'blacklist_reason' => [
                'required',
                'string',
                'min:3',
            ],
        ]);

        if (!$validated['blacklist_discord_id'] || !$validated['blacklist_reason']) {
            $this->toast()->warning('Hiányzó adatok', 'Kérlek, add meg a Discord ID-t és az indokot.')->send();
            return;
        }

        BlackList::create([
            'user_discord_id' => $validated['blacklist_discord_id'],
            'reason' => $validated['blacklist_reason'],
            'guild_guild_id' => $this->guild->guild_id,
        ]);

        $this->toast()->success('Sikeres művelet', 'Sikeresen hozzáadtad a feketelistához.')->send();
        $channel_id = $this->getDutyLogChannelId($this->guild);
        $this->sendDefaultLog($channel_id, [
            'command' => 'blacklistadd',
            'message' => "A felhasználó hozzáadta <@{$this->blacklist_discord_id}> felhasználót a feketelistához. Indok: {$this->blacklist_reason}",
            'user' => auth()->id(),
        ]);

        $this->blacklist_discord_id = null;
        $this->blacklist_reason = null;
    }


    public function with(): array
    {
        return [
            'headers' => [
                ['index' => 'user_discord_id', 'label' => 'Discord ID'],
                ['index' => 'user.name', 'label' => 'Felhasználó'],
                ['index' => 'reason', 'label' => 'Indok'],
                ['index' => 'created_at', 'label' => 'Létrehozva'],
                ['index' => 'deleted_at', 'label' => 'Törölve', 'type' => 'boolean'],
                ['index' => 'action']
            ],
            'rows' => $this->guild->blacklistsWithTrashed()
                ->with('user')
                ->when($this->search, function (Builder $query) {
                    $query->where(function ($q) {
                        $q->whereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', "%{$this->search}%");
                        })->orWhere('user_discord_id', 'like', "%{$this->search}%");
                    });
                })
                ->paginate($this->quantity)
                ->withQueryString()
        ];
    }

}; ?>

<div class="flex flex-col gap-4">
    <x-card header="Feketelistához adás" minimize="mount">
        <div class="flex flex-col gap-4">
            <x-input label="Discord ID" wire:model.lazy="blacklist_discord_id" clearable/>
            <x-textarea label="Indok" wire:model.lazy="blacklist_reason">

            </x-textarea>
            <x-button color="black" icon="flag" wire:click="addBlacklist">Hozzáadás</x-button>
        </div>
    </x-card>
    <x-table :$headers :$rows filter loading>
        @interact('column_action', $row)
        <x-button.circle color="red" icon="trash" wire:click="deleteBlacklist('{{ $row->id }}')"/>
        @endinteract
    </x-table>
</div>
