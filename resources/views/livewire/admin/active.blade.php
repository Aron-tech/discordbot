<?php

use App\Enums\PermissionEnum;
use App\Models\Guild;
use App\Models\GuildSelector;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;


new
#[Layout('layouts.app')]
#[Title('Aktív felhasználók')]
class extends Component {

    use Interactions;
    use WithPagination;

    public ?Guild $guild = null;
    public int $active_duties_count = 0;

    public ?int $quantity = 20;

    public ?string $search = null;


    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    public function mount(): void
    {
        $this->guild = GuildSelector::getGuild();

        $this->active_duties_count = $this->guild->duties()
            ->whereNull('value')
            ->whereNull('end_time')
            ->count();
    }

    public function deleteUserDuty($duty_id): void
    {
        if(auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::EDIT_PERIOD_DUTY])) {
            $this->toast()->error('Nincs jogosultságod a felhasználó kiléptetéséhez.')->send();
            return;
        }

        if($this->guild->duties()->find($duty_id)->exists()) {
            $duty = $this->guild->duties()->find($duty_id);
            $duty->forceDelete();
            $this->toast()->success('A felhasználó sikeresen kiléptetve.')->send();
        } else {
            $this->toast()->error('A megadott duty nem található.')->send();
        }

    }

    public function with(): array
    {

        $users = $this->guild->users()
            ->with(['duties' => function ($query) {
                $query->whereNull('value')
                      ->whereNull('end_time')
                      ->latest('start_time')
                      ->select('id', 'user_discord_id', 'start_time');
            }])
            ->withPivot('ic_name')
            ->whereHas('duties', function ($q) {
                $q->whereNull('value')
                    ->whereNull('end_time');
            })
            ->when($this->search, function (Builder $query) {
                $query->whereHas('duties', function ($q) {
                    $q->whereNull('value')
                        ->whereNull('end_time');
                })->where(function ($q) {
                    $q->where('users.discord_id', 'like', "%{$this->search}%")
                      ->orWhere('users.name', 'like', "%{$this->search}%")
                      ->orWhere('guild_user.ic_name', 'like', "%{$this->search}%");
                });
            })
            ->paginate($this->quantity);

        $users->getCollection()->transform(function ($user) {
            $user->first_duty_id = $user->duties->first()->id ?? null;
            $user->first_duty_start_time = $user->duties->first()->start_time ?? null;
            return $user;
        });

        return [
            'headers' => [
                ['index' => 'discord_id', 'label' => 'Discord ID'],
                ['index' => 'name', 'label' => 'DC név'],
                ['index' => 'pivot.ic_name', 'label' => 'IC név'],
                ['index' => 'first_duty_start_time', 'label' => 'Belépés'],
                ['index' => 'action', 'label' => 'Kiléptetés', 'sortable' => false],
            ],
            'rows' => $users,
        ];
    }
}; ?>

<div>
    <div class="flex items-center w-1/4 mx-auto">
        <x-stats icon="user-group" :number="$active_duties_count" wire:click="resetPage()" title="Szolgálatban lévők száma" footer="Kattints a kártyára az oldal az adatok frissítéséért." animated  />
    </div>
    <x-table :$headers :$rows filter loading paginate striped :$sort :quantity="[10,20,50]">
        @interact('column_action', $row)
            <x-button.circle color="red" icon="arrow-right-end-on-rectangle" wire:click="deleteUserDuty('{{$row['first_duty_id']}}')"/>
        @endinteract
    </x-table>
</div>
