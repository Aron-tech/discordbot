<?php

use App\Models\Exam;
use App\Models\GuildSelector;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {

    use WithPagination;

    public ?int $quantity = 10;

    public ?string $search = null;

    public function selectExam($id)
    {
        $this->dispatch('selectExam', id: $id);
    }

    #[On('resetPage')]
    public function reloadPage(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $guild = GuildSelector::getGuild();

        return [
            'headers' => [
                ['index' => 'id', 'label' => '#'],
                ['index' => 'name', 'label' => 'Név'],
                ['index' => 'attempt_count', 'label' => 'M. Probákozások száma'],
                ['index' => 'min_pass_score', 'label' => 'Minimum pontszám'],
                ['index' => 'action'],
            ],
            'rows' => $guild->exams()
                ->when($this->search, function (Builder $query) {
                    return $query->where('name', 'like', "%{$this->search}%");
                })
                ->paginate($this->quantity)
                ->withQueryString()
        ];
    }
}; ?>

<div>
    <x-table :$headers :$rows filter loading striped>
        @interact('column_action', $row)
            <div class="flex space-x-4 items-center">
                <x-toggle label="Láthatóság" position="left" :checked="$row['visible']"/>
                <x-button.circle color="indigo" icon="wrench-screwdriver" wire:click="selectExam('{{ $row['id'] }}')"/>
            </div>
        @endinteract
    </x-table>
</div>
