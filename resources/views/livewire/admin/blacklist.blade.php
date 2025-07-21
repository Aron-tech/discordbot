<?php

use App\Enums\PermissionEnum;
use App\Models\BlackList;
use App\Models\Guild;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

    public ?int $quantity = 10;

    public ?string $search = null;

    public ?Guild $guild = null;

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
        }else{
            $blacklist->delete();
            $this->toast()->success('Sikeres művelet', 'Sikeresen törölted a feketelistát.')->send();
        }
    }

    public function destroyBlacklist($blacklist_id): void
    {
        $blacklist = $this->guild->blacklistsWithTrashed()->where('id', $blacklist_id)->forceDelete();

        $this->toast()->success('Sikeres művelet', 'Sikeresen törölted a feketelistát.')->send();
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
            'rows' => $this->guild->blacklistsWithTrashed()->with('user')
                ->paginate($this->quantity)
                ->withQueryString()
        ];
    }

}; ?>

<div>
    <x-table :$headers :$rows filter loading>
        @interact('column_action', $row)
        <x-button.circle color="red" icon="trash" wire:click="deleteBlacklist('{{ $row->id }}')"/>
        @endinteract
    </x-table>
</div>
