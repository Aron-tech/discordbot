<?php

use App\Models\GuildSelector;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {

    use WithPagination;

    public ?int $quantity = 10;

    public ?string $search = null;

    public function selectExam($exam_id)
    {
        $this->dispatch('selectExam', $exam_id);
    }

    public function with(): array
    {
        $guild = GuildSelector::getGuild();
        return [

            'headers' => [
                ['index' => 'id', 'label' => '#'],
                ['index' => 'name', 'label' => 'Vizsga neve'],
                ['index' => 'bestResultForAuthUser.score', 'label' => 'Legmagasabb pontszámod'],
                ['index' => 'user_results_count', 'label' => 'Probálkozások'],
                ['index' => 'action', 'label' => 'Vizsga elindítása'],
            ],

            'rows' => $guild->exams()
                ->withCount(['results as user_results_count' => function ($query) {
                    $query->where('user_discord_id', auth()->id());
                }
                ])
                ->with('bestResultForAuthUser')
                ->when($this->search, function (Builder $query) {
                    return $query->where('name', 'like', "%{$this->search}%");
                })
                ->paginate($this->quantity)
                ->withQueryString()
        ];
    }
}; ?>

<div>
    <x-table :$headers :$rows filter loading>
        @interact('column_action', $row)
            <x-button.circle icon="pencil" wire:click="selectExam({{$row['id']}})"/>
        @endinteract
    </x-table>
</div>
