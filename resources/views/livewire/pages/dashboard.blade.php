<?php

use App\Models\Guild;
use App\Models\GuildSelector;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};

new
#[Layout('layouts.app')]
#[Title('Információk')]
class extends Component {

    public ?Guild $guild = null;

    public ?User $user = null;

    public string $total_duty = '0:0';
    public string $period_duty = '0:0';

    public int $days_in_role = 0;
    public int $days_in_faction = 0;

    public function mount()
    {
        $this->guild = GuildSelector::getGuild();
        $this->updateData();
    }

    public function updateData()
    {
        if ($this->guild) {
            $this->user = $this->guild->users()->where('discord_id', auth()->id())->first();
            $this->period_duty = dutyTimeFormatter($this->user->periodDutyTime($this->guild));
            $this->total_duty = dutyTimeFormatter($this->user->totalDutyTime($this->guild));
            $this->days_in_role = Carbon::parse($this->user->pivot->last_role_time)->diffInDays(now());
            $this->days_in_faction = Carbon::parse($this->user->pivot->created_at)->diffInDays(now());
        }
    }
}; ?>

<div>
    <div class="flex justify-center gap-4">
        <x-avatar image="{{$user->avatar}}" lg/>
        <x-h4 class="my-auto dark:text-white uppercase">{{$user->name}}</x-h4>
    </div>
    <div class="flex my-10 gap-2">
        <x-stats wire:click="updateData" :number="$period_duty" title="Aktuális szolgálati idő" icon="clock"/>
        <x-stats wire:click="updateData" :number="$total_duty" title="Összes szolgálati idő" icon="clock"/>
        <x-stats wire:click="updateData" :number="$days_in_role" animated title="Rangon eltöltött napok száma" icon="calendar-days"/>
        <x-stats wire:click="updateData" :number="$days_in_faction" animated title="Frakcióban eltöltött napok száma" icon="calendar-date-range"/>
    </div>
    <div class="grid lg:grid-cols-3 grid-cols-1 gap-4 my-10">
        <x-input label="IC név" :value="$user->pivot->ic_name" readonly/>
        <x-input label="Jelvényszám" :value="$user->pivot->ic_number" readonly/>
        <x-input label="Telefonszám" :value="$user->pivot->ic_tel" readonly/>
    </div>
</div>
