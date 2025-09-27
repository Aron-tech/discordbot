<?php

use App\Actions\CheckingDutyAction;
use App\Actions\CheckingUserDutyAction;
use App\Enums\DutyTypeEnum;
use App\Enums\PermissionEnum;
use App\Livewire\Traits\FormatsDuty;
use App\Models\Guild;
use App\Models\GuildSelector;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;
use App\Http\Requests\UpdateUserPivotLivewireRequest;
use App\Enums\Guild\RoleTypeEnum;
use App\Livewire\Traits\DcMessageTrait;
use App\Enums\Guild\ChannelTypeEnum;
use App\Enums\Guild\SettingTypeEnum;
use App\Livewire\Traits\FeatureTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

new
#[Layout('layouts.app')]
#[Title('Automatikus szolgálati idő kiosztás')]
class extends Component {

    use WithPagination;
    use FormatsDuty;
    use DcMessageTrait;
    use Interactions;

    public array $selected_users = [];

    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    public ?int $quantity = 20;
    public ?string $search = null;
    public array $visible_columns = ['id', 'name', 'ic_name', 'duties_sum_value', 'in_role_days', 'action_type'];

    public ?Guild $guild = null;

    public $next_checking_time = null;
    public $min_rank_up_duty = null;
    public $min_rank_up_time = null;
    public $min_duty = null;

    public function mount(): void
    {
        $this->guild = GuildSelector::getGuild();

        $this->next_checking_time = getSettingValue($this->guild, SettingTypeEnum::NEXT_CHECKING_TIME->value);
        $this->min_rank_up_duty = getSettingValue($this->guild, SettingTypeEnum::MIN_RANK_UP_DUTY->value);
        $this->min_rank_up_time = getSettingValue($this->guild, SettingTypeEnum::MIN_RANK_UP_TIME->value);
        $this->min_duty = getSettingValue($this->guild, SettingTypeEnum::MIN_DUTY->value);
    }

    public function runAutoDutyReport(): void
    {
        if(empty($this->selected_users)){
            $this->toast()->warning('Sikertelen művelet!', 'Nincs kijelölve felhasználó.')->send();
            return;
        }

        foreach ($this->selected_users as $user_discord_id) {
            CheckingUserDutyAction::run(
                $this->guild,
                $user_discord_id,
                array(
                    'next_checking_time' => $this->next_checking_time,
                    'min_rank_up_duty' => $this->min_rank_up_duty,
                    'min_rank_up_time' => $this->min_rank_up_time,
                    'min_duty' => $this->min_duty,
                )
            );
        }

        $this->selected_users = [];

        to_route('admin.panel')->with('success', 'Sikeresen művelet!');
    }

    public function with(): array
    {
        $allHeaders = [
            ['index' => 'id', 'label' => 'Discord ID'],
            ['index' => 'name', 'label' => 'DC Név'],
            ['index' => 'ic_name', 'label' => 'IC név'],
            ['index' => 'duties_sum_value', 'label' => 'Szolgálati idő'],
            ['index' => 'duties_with_trashed_sum_value', 'label' => 'Összes sz. idő'],
            ['index' => 'in_role_days', 'label' => 'Rangon'],
            ['index' => 'in_guild_days', 'label' => 'Frakcióban'],
            ['index' => 'duties_with_trashed_max_start_time', 'label' => 'Utolsó szolgálatba lépés ideje'],
            ['index' => 'action_type', 'label' => 'Akció', 'sortable' => false],
        ];

        $headers = array_filter($allHeaders, fn($header) => in_array($header['index'], $this->visible_columns));
        $headers = collect($allHeaders)
            ->whereIn('index', $this->visible_columns)
            ->when(!in_array('id', $this->visible_columns), fn($c) => $c->prepend(['index' => 'id', 'label' => 'Discord ID']))
            ->when(!in_array('action_type', $this->visible_columns), fn($c) => $c->push(['index' => 'action_type', 'label' => 'Akció', 'sortable' => false]))
            ->values()
            ->toArray();

        $rows = $this->guild->users()
            ->select('users.discord_id as id')
            ->when($this->search, function ($query) {
                $search = "%{$this->search}%";
                $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', $search)
                        ->orWhere('users.discord_id', 'like', $search)
                        ->orWhere('guild_user.ic_name', 'like', $search);
                });
            })
            ->withSum(['duties' => function ($query) {
                $query->where('guild_guild_id', $this->guild->guild_id);
            }], 'value')
            ->withSum(['dutiesWithTrashed' => function ($query) {
                $query->where('guild_guild_id', $this->guild->guild_id);
            }], 'value')
            ->withMax(['dutiesWithTrashed' => function ($query) {
                $query->where('guild_guild_id', $this->guild->guild_id);
            }], 'start_time')
            ->selectRaw('DATEDIFF(NOW(), COALESCE(guild_user.last_role_time, guild_user.created_at)) as in_role_days')
            ->selectRaw('DATEDIFF(NOW(), guild_user.created_at) as in_guild_days')
            ->orderBy(...array_values($this->sort))
            ->paginate($this->quantity)
            ->through(function ($user) use ($headers) {
                $row = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'ic_name' => $user->pivot->ic_name,
                    'duties_sum_value' => $this->formatMinutesToHHMM($user->duties_sum_value),
                    'duties_with_trashed_sum_value' => $this->formatMinutesToHHMM($user->duties_with_trashed_sum_value),
                    'in_role_days' => $user->in_role_days . ' napja',
                    'in_guild_days' => $user->in_guild_days . ' napja',
                    'duties_with_trashed_max_start_time' => $user->duties_with_trashed_max_start_time ? Carbon::parse($user->duties_with_trashed_max_start_time)->diffForHumans() : 'Nincs adat',
                    'action_type' => (function () use ($user) {
                        $user_rank_up = ($user->duties_sum_value >= ($this->min_rank_up_duty * 60))
                            && Carbon::parse($user->pivot->last_role_time)->addDays($this->min_rank_up_time)->isPast()
                            && (is_null($user->pivot->last_warn_time) || Carbon::parse($user->pivot->last_warn_time)->addDays($this->next_checking_time)->isPast());

                        $user_warn = ($user->duties_sum_value < ($this->min_duty * 60))
                            && (is_null($user->pivot->freedom_expiring) || Carbon::parse($user->pivot->freedom_expiring)->lt(Carbon::now()->subDays($this->next_checking_time)))
                            && ($user->pivot->created_at->addDays($this->next_checking_time)->isPast());

                        if ($user_warn) {
                            return 'Figyelmeztetés';
                        }

                        if ($user_rank_up) {
                            dd($user);
                            return 'Felfokozás';
                        }

                        return null;
                    })(),
                ];

                return collect($row)->only(array_column($headers, 'index'))->toArray();
            });

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }
}; ?>

<div>
    <div class="my-4 flex justify-between">
        <div class="lg:w-1/3">
            <x-button icon="shield-check" wire:click="runAutoDutyReport">Végrehajtás</x-button>
        </div>
        <div class="lg:w-1/3">
            <x-select.styled
                label="Látható oszlopok"
                :options="collect(config('columns.auto-duty-panel'))->map(fn($label, $key) => ['label' => $label, 'value' => $key])->values()"
                wire:model.live="visible_columns"
                multiple
            />
        </div>
    </div>
    <x-table :$headers :$rows striped :$sort filter loading :quantity="[10,20,50]" filter paginate selectable wire:model.live="selected_users"/>
</div>
