<?php

use App\Enums\PermissionEnum;
use App\Livewire\Traits\FormatsDuty;
use App\Models\Guild;
use App\Models\GuildSelector;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use TallStackUi\Traits\Interactions;
use Livewire\Attributes\Computed;

new
#[Layout('layouts.app')]
#[Title('Információk')]
class extends Component {

    use Interactions;
    use FormatsDuty;

    public ?Guild $guild = null;

    public ?string $freedom_date = null;

    public function mount()
    {
        $this->guild = GuildSelector::getGuild();

        $user = $this->guild->users()->where('discord_id', auth()->id())->first() ?? null;

        if (!isset($user) && auth()->user()->can('hasPermission', [$this->guild, PermissionEnum::VIEW_SETTINGS]))
            return to_route('admin.settings');
        elseif(!isset($user))
            return to_route('guild.selector');

        $pivot = $user?->pivot ?? null;


        $this->freedom_date = $pivot->freedom_expiring
            ? Carbon::parse($pivot->freedom_expiring)->format('Y-m-d')
            : null;

        $this->freedom_date = $this->freedom_date < now()->format('Y-m-d') ? null : $this->freedom_date;
    }

    /*#[Computed]
    public function user(): ?User
    {
        return $this->guild
            ? $this->guild->users()
                ->where('discord_id', auth()->id())
                ->first()
            : null;
    }*/

    /*public function updateData(): void
    {
        if ($this->guild) {
            $this->period_duty = $this->formatMinutesToHHMM($this->user()->periodDutyTime($this->guild));
            $this->total_duty = $this->formatMinutesToHHMM($this->user()->totalDutyTime($this->guild));
            $this->days_in_role = Carbon::parse($this->user()->pivot->last_role_time)->diffInDays(now());
            $this->days_in_faction = Carbon::parse($this->user()->pivot->created_at)->diffInDays(now());
            $this->freedom_date = $this->user()->pivot->freedom_expiring
                ? Carbon::parse($this->user()->pivot->freedom_expiring)->format('Y-m-d')
                : null;
        }
    }*/

    public function with(): array
    {
        $user = $this->guild->users()
            ->where('discord_id', auth()->id())
            ->withSum('duties as period_duty', 'value')
            ->first();

        $user->total_duty = $user->totalDutyTime($this->guild);

        $days_in_role = (int)Carbon::parse($user->pivot->last_role_time)->diffInDays(now());
        $days_in_faction = (int)Carbon::parse($user->pivot->created_at)->diffInDays(now());

        $member_data = getMemberData($this->guild->guild_id, $user->discord_id);

        $ic_role = null;

        if ($member_data && is_array($member_data['roles']) && getRoleValue($this->guild, 'ic_roles')) {
            $ic_role = array_intersect($member_data['roles'], getRoleValue($this->guild, 'ic_roles'));
        }

        $guild_roles = cache()->remember($this->guild->guild_id. 'guild_roles', now()->addHours(12), function () {
            return getGuildData($this->guild->guild_id, 'roles');
        });

        $role_name = null;

        $first_role_id = !empty($ic_role) ? array_values($ic_role)[0] : null;

        if ($first_role_id && isset($guild_roles)) {
            $role = collect($guild_roles)->firstWhere('id', $first_role_id);
            $role_name = $role['name'] ?? null;
        }

        return [
            'user' => $user,
            'days_in_role' => $days_in_role,
            'days_in_faction' => $days_in_faction,
            'ic_role' => $role_name ?? null,
        ];
    }

    public function saveFreedomDate(): void
    {
        if ($this->freedom_date) {
            DB::table('guild_user')->where('guild_guild_id', $this->guild->guild_id)
                ->where('user_discord_id', auth()->id())
                ->update(['freedom_expiring' => $this->freedom_date]);
            $this->toast()->success('Szabadság dátuma frissítve!')->send();
        } else {
            $this->toast()->error('Kérlek adj meg egy érvényes dátumot!')->send();
        }
    }
}; ?>

<div>
    <div class="flex justify-center items-center gap-4">
        <x-avatar image="{{$user->avatar}}" lg/>
        <x-h4 class="my-auto dark:text-white uppercase">{{$user->name}}</x-h4>
        <x-badge text="{{$ic_role}}" sm round />
    </div>
    <div class="flex flex-col lg:flex-row my-10 gap-2">
        <x-stats :number="dutyTimeFormatter((int) $user->period_duty)" title="Aktuális szolgálati idő" icon="clock"/>
        <x-stats :number="dutyTimeFormatter($user->total_duty)" title="Összes szolgálati idő" icon="clock"/>
        <x-stats :number="$days_in_role" animated title="Rangon eltöltött napok száma"
                 icon="calendar-days"/>
        <x-stats :number="$days_in_faction" animated title="Frakcióban eltöltött napok száma"
                 icon="calendar-date-range"/>
    </div>
    @if($user->pivot)
        <div class="grid lg:grid-cols-4 grid-cols-1 gap-4 my-10">
            <x-input label="IC név" :value="$user->pivot->ic_name" readonly/>
            <x-input label="Jelvényszám" :value="$user->pivot->ic_number" readonly/>
            <x-input label="Telefonszám" :value="$user->pivot->ic_tel" readonly/>
            <x-date wire:model.live="freedom_date" label="Szabadságon eddig" :min-date="now()"
                    :max-date="now()->addYear()"
                    wire:change="saveFreedomDate"/>
        </div>
    @endif
</div>
