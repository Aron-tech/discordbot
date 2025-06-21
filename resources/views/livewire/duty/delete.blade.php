<?php

use App\Models\Duty;
use Livewire\Volt\Component;

new class extends Component {
    public function delete($discord_id): void
    {
        Duty::findOrFail($discord_id)->delete();
        session()->flash('success', 'Sikeres törlés.');
        $this->resetPage();
    }

}; ?>

<div>
    <form wire:submit.prevent="delete({{ $discord_id }})">
        <button type="submit" class="text-red-600 hover:underline">
            Törlés
        </button>
    </form>
</div>
