<?php

use App\Models\Duty;
use App\Models\Guild;
use App\Models\GuildSelector;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;


new
#[Layout('layouts.app')]
#[Title('Logs')]
class extends Component {
    use WithPagination;
    use Interactions;

    public ?int $quantity = 20;

    public ?string $search = null;

    public ?Duty $selected_duty = null;

    public ?Guild $guild = null;

    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    public function mount()
    {
        $this->guild = GuildSelector::getGuild();
    }

    public function deleteDuty(int $duty_id)
    {
        $this->selected_duty = Duty::withTrashed()->findOrFail($duty_id);

        $this->dialog()
            ->question('Figyelmeztetés!', 'Biztosan véglegesen törölni szeretnéd?')
            ->confirm('Törlés', method: 'destroyDuty')
            ->cancel('Mégse')
            ->send();
    }

    public function destroyDuty()
    {
        $this->selected_duty->forceDelete();

        $this->selected_duty = null;

        $this->toast()->success('Sikeres művelet', 'Sikeresen töröltél egy szolgálati időt.')->send();
    }

    public function with(): array
    {
        return [
            'headers' => [
                ['index' => 'user_discord_id', 'label' => 'Discord ID'],
                ['index' => 'user.name', 'label' => 'Név'],
                ['index' => 'value', 'label' => 'Mentett idő'],
                ['index' => 'start_time', 'label' => 'Szolgálatba lépés'],
                ['index' => 'end_time', 'label' => 'Szolgálatból kilépés'],
                ['index' => 'action'],
            ],
            'rows' => $this->guild->dutiesWithTrashed()
                ->whereNotNull('value')
                ->with(['user:discord_id,name'])
                ->orderBy(...array_values($this->sort))
                ->when($this->search, function (Builder $query) {
                    $query->where(function ($q) {
                        $q->whereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', "%{$this->search}%");
                        })->orWhere('user_discord_id', 'like', "%{$this->search}%");
                    });
                })
                ->paginate($this->quantity)
                ->withQueryString(),
            'type' => 'data',
        ];
    }

}; ?>

<div>
    <x-table :$headers :$rows filter loading paginate striped :$sort :quantity="[10,20,50]">
        @interact('column_action', $row)
            <x-button.circle color="red" icon="trash" wire:click="deleteDuty('{{ $row->id }}')"/>
        @endinteract
    </x-table>
</div>
