<?php

use App\Models\Guild;
use App\Models\GuildSelector;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;
use Livewire\Attributes\{Layout, Title, On};
use App\Models\ExamResult;

new
#[Layout('layouts.app')]
#[Title('Vizsga log')]
class extends Component {

    use Interactions;
    use WithPagination;

    public ?Guild $guild = null;

    public ?int $quantity = 10;

    public ?string $search = null;

    public function mount(): void
    {
        $this->guild = GuildSelector::getGuild();
    }

    public function cancelled(string $message): void
    {
        $this->toast()->info($message)->send();
    }

    public function deleteResult($result_id): void
    {
        $this->dialog()
            ->question('Figyelmeztetés!', 'Biztosan törölni szeretnéd a választ?')
            ->confirm('Törlés', 'destroyResult', $result_id)
            ->cancel('Mégse', 'cancelled', 'A válasz törlésre visszavonásra került.')
            ->send();
    }

    public function destroyResult(ExamResult $result): void
    {
        $result->delete();
        $this->toast()->success('Sikeresen törölve az eredmény.')->send();
    }

    public function with(): array
    {
        return [
            'headers' => [
                ['index' => 'exam.name', 'label' => 'Vizsga neve'],
                ['index' => 'user_discord_id', 'label' => 'Discord ID'],
                ['index' => 'user.name', 'label' => 'Discord név'],
                ['index' => 'score', 'label' => 'Eredmény'],
                ['index' => 'action', 'label' => 'Törlés'],
            ],
            'rows' => ExamResult::whereHas('exam', function ($query) {
                $query->where('guild_guild_id', $this->guild->guild_id);
            })
                ->with(['exam', 'user:discord_id,name'])
                ->when($this->search, function (Builder $query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('user_discord_id', 'like', "%{$this->search}%")
                            ->orWhereHas('user', function ($userQuery) {
                                $userQuery->where('name', 'like', "%{$this->search}%");
                            })
                            ->orWhereHas('exam', function ($examQuery) {
                                $examQuery->where('name', 'like', "%{$this->search}%");
                            });
                    });
                })
                ->paginate($this->quantity)
                ->withQueryString()
        ];
    }
}; ?>

<div>
    <x-table :$headers :$rows filter loading>
        @interact('column_action', $row)
            <x-button.circle color="red" icon="trash" wire:click="deleteResult('{{ $row->id }}')"/>
        @endinteract
    </x-table>
</div>
