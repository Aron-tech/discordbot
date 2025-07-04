<?php

use App\Models\Guild;
use App\Models\GuildSelector;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};

new
#[Layout('layouts.app')]
#[Title('Toplista')]
class extends Component {

    public ?Guild $guild = null;

    public ?Collection $period_top_users = null;
    public ?Collection $total_top_users = null;

    public function mount()
    {
        $this->guild = GuildSelector::getGuild();

        $this->period_top_users = $this->guild->users()
            ->withSum(['duties' => function ($query) {
                $query->where('guild_guild_id', $this->guild->guild_id);
            }], 'value')
            ->orderBy('duties_sum_value', 'desc')
            ->take(10)
            ->get();

        $this->total_top_users = $this->guild->users()
            ->withSum(['dutiesWithTrashed' => function ($query) {
                $query->where('guild_guild_id', $this->guild->guild_id);
            }], 'value')
            ->orderBy('duties_with_trashed_sum_value', 'desc')
            ->take(10)
            ->get();
    }


}; ?>

<div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="flex flex-col gap-4">
            <x-side-bar.separator text="Aktuális szolgálati idő toplista" line />
            @foreach($period_top_users as $user)
                <x-toplist-user
                    :avatar="$user->avatar"
                    :name="$user->name"
                    :value="$user->duties_sum_value"
                    label="Aktuális szolgálati idő" />
            @endforeach
        </div>
        <div class="flex flex-col gap-4">
            <x-side-bar.separator text="Összes szolgálati idő Toplista" line />
            @foreach($total_top_users as $user)
                <div class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <x-avatar image="{{$user->avatar}}" lg/>
                    <div>
                        <x-h4 class="dark:text-white uppercase">{{$user->name}}</x-h4>
                        <p class="text-gray-500 dark:text-gray-400">Összes szolgálati
                            idő: {{dutyTimeFormatter($user->duties_with_trashed_sum_value ?? 0)}}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
