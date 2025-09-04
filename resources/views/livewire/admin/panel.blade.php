<?php

use App\Actions\CheckingDutyAction;
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

new
#[Layout('layouts.app')]
#[Title('Szolgálati idő kezelése')]
class extends Component {

    use Interactions;
    use FormatsDuty;
    use WithPagination;
    use DcMessageTrait;
    use FeatureTrait;

    public ?Guild $guild = null;

    public bool $modal = false;
    public ?string $period_duty_time = null;
    public ?int $add_period_duty_time = 0;
    public ?string $total_duty_time = null;
    public ?int $add_total_duty_time = 0;
    public ?string $blacklist_reason = null;

    public ?string $selected_user_role = null;
    public array $ic_roles = [];

    public ?string $ic_name = null;
    public ?string $ic_number = null;
    public ?string $ic_tel = null;

    public ?User $selected_user = null;
    public ?array $selected_user_member_data = null;

    public ?int $quantity = 20;

    public ?string $search = null;


    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    public function mount(): void
    {
        $this->guild = GuildSelector::getGuild();

        $roles = getGuildData($this->guild->guild_id, 'roles');

        $ic_role_ids = getRoleValue($this->guild, 'ic_roles');

        $this->ic_roles = collect($roles)
            ->sortBy('position')
            ->filter(function ($role) use ($ic_role_ids) {
                return in_array($role['id'], $ic_role_ids);
            })
            ->map(function ($role) {
                return [
                    'label' => $role['name'],
                    'value' => $role['id'],
                ];
            })
            ->values()
            ->toArray();
    }

    public function updatedAddPeriodDutyTime(): void
    {
        $addMinutes = $this->safeCastToInt($this->add_period_duty_time);
        $this->period_duty_time = dutyTimeFormatter(
            $this->selected_user->periodDutyTime($this->guild) + $addMinutes
        );
        $this->updatedAddTotalDutyTime();
    }

    public function updatedAddTotalDutyTime()
    {
        $addPeriod = $this->safeCastToInt($this->add_period_duty_time);
        $addTotal = $this->safeCastToInt($this->add_total_duty_time);

        $this->total_duty_time = $this->formatMinutesToHHMM(
            $this->selected_user->totalDutyTime($this->guild) + $addTotal + $addPeriod
        );
    }

    private function safeCastToInt($value): int
    {
        if (empty($value) || $value === null || $value === '') {
            return 0;
        }

        $value = (string) $value;

        $cleaned = preg_replace('/[^0-9\-]/', '', $value);

        if (empty($cleaned) || $cleaned === '-') {
            return 0;
        }

        return (int) $cleaned;
    }

    public function addDuty(DutyTypeEnum $type)
    {
        if (!$this->ensureFeatureEnabled($this->guild, SettingTypeEnum::DUTY_SYSTEM)) {
            return;
        }

        $channel_id = $this->getDutyLogChannelId($this->guild);

        if (DutyTypeEnum::PERIOD === $type) {

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::EDIT_PERIOD_DUTY])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
                return;
            }

            $this->selected_user->duties()->create([
                'guild_guild_id' => $this->guild->guild_id,
                'value' => $this->add_period_duty_time,
                'start_time' => now(),
                'end_time' => now(),
            ]);

            $this->toast()->success('Sikeres művelet', 'Sikeresen hozzáadtál a felhasználó szolgálati idejéhez.')->send();
            $this->sendDefaultLog($channel_id, [
                'command' => 'dutymanager',
                'message' => "A felhasználó hozzáadott <@{$this->selected_user->discord_id}> felhasználónak az aktuális összes szolgálati idejéhez {$this->add_period_duty_time} percet.",
                'user' => auth()->id(),
            ]);
            $this->add_period_duty_time = 0;

        } else if (DutyTypeEnum::TOTAL === $type) {

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::EDIT_TOTAL_DUTY])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az összes szolgálati idő szerkesztéséhez.')->send();
                return;
            }

            $duty = $this->selected_user->duties()->create([
                'guild_guild_id' => $this->guild->guild_id,
                'value' => $this->add_total_duty_time,
                'start_time' => now(),
                'end_time' => now(),
            ]);

            $duty->delete();

            $this->toast()->success('Sikeres művelet', 'Sikeresen hozzáadtál a felhasználó összes szolgálati idejéhez.')->send();
            $this->sendDefaultLog($channel_id, [
                'message' => "A felhasználó hozzáadott <@{$this->selected_user->discord_id}> felhasználónak az összes szolgálati idejéhez {$this->add_total_duty_time} percet.",
                'user' => auth()->id(),
            ]);
            $this->add_total_duty_time = 0;
        }
    }

    public function deleteUserDuties(DutyTypeEnum $type): void
    {
        if (!$this->ensureFeatureEnabled($this->guild, SettingTypeEnum::DUTY_SYSTEM)) {
            return;
        }

        $this->dialog()
            ->question('Figyelmeztetés!', 'Biztosan törölni szeretnéd a felhasználó szolgálati idejét?')
            ->confirm('Törlés', method: 'destroyUserDuties', params: $type->value)
            ->cancel('Mégse')
            ->send();
    }

    public function destroyUserDuties($type): void
    {
        $channel_id = $this->getDutyLogChannelId($this->guild);
        if (DutyTypeEnum::PERIOD->value === $type) {

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::DELETE_USER_PERIOD_DUTY])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
                return;
            }

            $this->selected_user->duties()->where('guild_guild_id', $this->guild->guild_id)->delete();
            $this->toast()->success('Sikeres művelet', 'Sikeresen törölted a szolgálati idejét a felhasználónak.')->send();
            $this->sendDefaultLog($channel_id, [
                'message' => "A felhasználó törölte az aktuális összes szolgálati időjét <@{$this->selected_user->discord_id}> felhasználónak.",
                'user' => auth()->id(),
            ]);
        } else if (DutyTypeEnum::TOTAL->value === $type) {

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::DELETE_USER_TOTAL_DUTY])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
                return;
            }

            $this->selected_user->dutiesWithTrashed()->where('guild_guild_id', $this->guild->guild_id)->forceDelete();
            $this->toast()->success('Sikeres művelet', 'Sikeresen törölted az összes szolgálati idejét a felhasználónak.')->send();
            $this->sendDefaultLog($channel_id, [
                'message' => "A felhasználó törölte az összes szolgálati időjét <@{$this->selected_user->discord_id}> felhasználónak.",
                'user' => auth()->id(),
            ]);
        }
    }

    public function deleteDuties(DutyTypeEnum $type): void
    {
        if (!$this->ensureFeatureEnabled($this->guild, SettingTypeEnum::DUTY_SYSTEM)) {
            return;
        }

        $this->dialog()
            ->question('Figyelmeztetés!', 'Biztosan törölni szeretnéd az összes szolgálati időt?')
            ->confirm('Törlés', method: 'destroyDuties', params: $type->value)
            ->cancel('Mégse')
            ->send();
    }

    public function destroyDuties($type)
    {
        $channel_id = $this->getDutyLogChannelId($this->guild);
        if (DutyTypeEnum::PERIOD->value === $type) {

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::DELETE_PERIOD_DUTY])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
                return;
            }

            $this->guild->duties()->delete();
            $this->toast()->success('Sikeres művelet', 'Sikeresen törölted a szolgálati időt.')->send();
            $this->sendDefaultLog($channel_id, [
                'command' => 'dutyreset',
                'message' => "A felhasználó törölte az aktuális összes szolgálati időt.",
                'user' => auth()->id(),
            ]);
        } else if (DutyTypeEnum::TOTAL->value === $type) {

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::DELETE_TOTAL_DUTY])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
                return;
            }

            $this->guild->dutiesWithTrashed()->forceDelete();
            $this->toast()->success('Sikeres művelet', 'Sikeresen törölted az összes szolgálati időt.')->send();
            $this->sendDefaultLog($channel_id, [
                'command' => 'dutyclear',
                'message' => "A felhasználó törölte az összes szolgálati időt.",
                'user' => auth()->id(),
            ]);
        }
    }

    public function updateUserRole(): void
    {
        try {
            if (!$this->ensureFeatureEnabled($this->guild, SettingTypeEnum::RANK_SYSTEM)) {
                return;
            }

            if (!$this->selected_user || !$this->selected_user_role) {
                $this->toast()->warning('Hiányzó adatok', 'Válaszd ki a felhasználót és a rangot!')->send();
                return;
            }

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::EDIT_USER_IC_ROLES])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
                return;
            }

            $previous_roles = array_intersect($this->selected_user_member_data['roles'], getRoleValue($this->guild, 'ic_roles'));
            $previous_role = reset($previous_roles) ?: null;

            if ($previous_role === $this->selected_user_role) {
                $this->toast()->info('Nincs változás', 'A felhasználó már ezen a rangon van.')->send();
                return;
            }

            $new_roles = array_diff($this->selected_user_member_data['roles'], getRoleValue($this->guild, 'ic_roles'));
            $new_roles[] = $this->selected_user_role;

            $response = changeMemberData($this->guild->guild_id, $this->selected_user->discord_id, $new_roles);

            if ($response->successful()) {
                $this->guild->users()->updateExistingPivot($this->selected_user->discord_id, [
                    'last_role_time' => now(),
                ]);

                $this->toast()->success('Sikeres rangmódosítás', 'A felhasználó rangja frissült a Discord szerveren is.')->send();

                $channel_id = $this->getDefaultLogChannelId($this->guild);
                $this->sendDefaultLog($channel_id, [
                    'command' => 'rolemanager',
                    'message' => "A felhasználó módosította <@{$this->selected_user->discord_id}> felhasználó rangját. Új rangja: <@&{$this->selected_user_role}>",
                    'user' => auth()->id(),
                ]);
            } else {
                throw new \Exception("Discord API hiba: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->toast()->error('Hiba történt', $e->getMessage())->send();
            logger()->error('Rang módosítási hiba: ' . $e->getMessage());
        }
    }

    public function promoteUserRole(): void
    {
        try {

            if (!$this->ensureFeatureEnabled($this->guild, SettingTypeEnum::RANK_SYSTEM)) {
                return;
            }

            if (!$this->selected_user || !$this->selected_user_role) {
                $this->toast()->warning('Hiányzó adatok', 'Válaszd ki a felhasználót és a rangot!')->send();
                return;
            }

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::EDIT_USER_IC_ROLES])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
                return;
            }

            $ic_roles = getRoleValue($this->guild, 'ic_roles');

            $current_role_index = array_search($this->selected_user_role, $ic_roles);

            if ($current_role_index === false || $current_role_index === count($ic_roles) - 1) {
                $this->toast()->info('Max rang', 'A felhasználó már a legmagasabb IC rangon van.')->send();
                return;
            }

            $next_role = $ic_roles[$current_role_index + 1];

            $new_roles = array_diff($this->selected_user_member_data['roles'], $ic_roles);
            $new_roles[] = $next_role;

            $role_name = $this->ic_roles[array_search($next_role, array_column($this->ic_roles, 'value'))]['label'] ?? 'ismeretlen';

            $this->selected_user_role = $next_role;

            $response = changeMemberData($this->guild->guild_id, $this->selected_user->discord_id, array_values($new_roles));

            if ($response->successful()) {
                $this->guild->users()->updateExistingPivot($this->selected_user->discord_id, [
                    'last_role_time' => now(),
                ]);

                $this->toast()->success('Sikeres rangemelés',
                    "A felhasználó most a(z) {$role_name} rangot viseli!"
                )->send();

                $channel_id = $this->getDefaultLogChannelId($this->guild);
                $this->sendDefaultLog($channel_id, [
                        'command' => 'rolemanager',
                        'message' => "A felhasználó által <@{$this->selected_user->discord_id}> rangja emelve lett {$role_name} rangra.",
                        'user' => auth()->id(),
                ]);
            } else {
                throw new \Exception("Discord API hiba: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->toast()->error('Hiba történt', $e->getMessage())->send();
            logger()->error('Rang emelési hiba: ' . $e->getMessage());
        }
    }

    public function demoteUserRole(): void
    {
        try {
            if (!$this->ensureFeatureEnabled($this->guild, SettingTypeEnum::RANK_SYSTEM)) {
                return;
            }

            if (!$this->selected_user || !$this->selected_user_role) {
                $this->toast()->warning('Hiányzó adatok', 'Válaszd ki a felhasználót és a rangot!')->send();
                return;
            }

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::EDIT_USER_IC_ROLES])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
                return;
            }

            $ic_roles = getRoleValue($this->guild, 'ic_roles');

            $current_role_index = array_search($this->selected_user_role, $ic_roles);

            if ($current_role_index === false || $current_role_index === 0) {
                $this->toast()->info('Mimimum rang', 'A felhasználó már a legalacsonyabb IC rangon van.')->send();
                return;
            }

            $next_role = $ic_roles[$current_role_index - 1];

            $new_roles = array_diff($this->selected_user_member_data['roles'], $ic_roles);
            $new_roles[] = $next_role;

            $new_roles = array_diff($this->selected_user_member_data['roles'], $ic_roles);
            $new_roles[] = $next_role;

            $role_name = $this->ic_roles[array_search($next_role, array_column($this->ic_roles, 'value'))]['label'] ?? 'ismeretlen';

            $this->selected_user_role = $next_role;

            $response = changeMemberData($this->guild->guild_id, $this->selected_user->discord_id, array_values($new_roles));

            if ($response->successful()) {
                $this->guild->users()->updateExistingPivot($this->selected_user->discord_id, [
                    'last_role_time' => now(),
                ]);

                $this->toast()->success('Sikeres rangcsökkentés',
                    "A felhasználó most a(z) {$role_name} rangot viseli!"
                )->send();

                $channel_id = $this->getDefaultLogChannelId($this->guild);
                $this->sendDefaultLog($channel_id, [
                    'command' => 'rolemanager',
                    'message' => "A felhasználó által <@{$this->selected_user->discord_id}> rangja csökkentve lett {$role_name} rangra.",
                    'user' => auth()->id(),
                ]);
            } else {
                throw new \Exception("Discord API hiba: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->toast()->error('Hiba történt', $e->getMessage())->send();
            logger()->error('Rang csökkentési hiba: ' . $e->getMessage());
        }
    }

    public function warnUser(): void
    {
        try {
            if (!$this->ensureFeatureEnabled($this->guild, SettingTypeEnum::WARN_SYSTEM)) {
                return;
            }

            if (!$this->selected_user) {
                $this->toast()->warning('Hiányzó adatok', 'Válaszd ki a figyelmeztetendő felhasználót!')->send();
                return;
            }

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::ADD_WARN_TO_USER])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
                return;
            }

            // Warn rangok lekérése a konfigból
            $warn_roles = getRoleValue($this->guild, 'warn_roles');
            if (empty($warn_roles)) {
                throw new \Exception("Nincsenek warn rangok konfigurálva");
            }

            $current_warns = array_intersect($this->selected_user_member_data['roles'], $warn_roles);

            $current_max_level = 0;
            foreach ($current_warns as $role_id) {
                $level = array_search($role_id, $warn_roles) + 1;
                if ($level > $current_max_level) {
                    $current_max_level = $level;
                }
            }

            $next_level = $current_max_level + 1;

            if ($next_level > count($warn_roles)) {
                $this->toast()->warning('Max figyelmeztetés', 'A felhasználó már a legmagasabb figyelmeztetési szinten van!')->send();
                return;
            }

            $new_roles = array_diff($this->selected_user_member_data['roles'], $warn_roles);
            $new_roles[] = $warn_roles[$next_level - 1];

            $response = changeMemberData($this->guild->guild_id, $this->selected_user->discord_id, array_values($new_roles));

            if ($response->successful()) {
                $this->guild->users()->updateExistingPivot($this->selected_user->discord_id, [
                    'last_warn_time' => now(),
                ]);

                $this->toast()->success('Sikeres figyelmeztetés',
                    "A felhasználó most a(z) {$next_level}. szintű figyelmeztetést kapta!"
                )->send();

                $channel_id = $this->getDefaultLogChannelId($this->guild);
                $this->sendDefaultLog($channel_id, [
                    'command' => 'addwarn',
                    'message' => "A felhasználó figyelmeztette <@{$this->selected_user->discord_id}> felhasználót.",
                    'user' => auth()->id(),
                ]);
            } else {
                throw new \Exception("Discord API hiba: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->toast()->error('Hiba történt', $e->getMessage())->send();
            logger()->error('Figyelmeztetési hiba: ' . $e->getMessage());
        }
    }

    public function deleteUserWarn(): void
    {
        try {
            if (!$this->ensureFeatureEnabled($this->guild, SettingTypeEnum::WARN_SYSTEM)) {
                return;
            }

            if (!$this->selected_user) {
                $this->toast()->warning('Hiányzó adatok', 'Válaszd ki a felhasználót!')->send();
                return;
            }

            if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::DELETE_WARN_FROM_USER])) {
                $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
                return;
            }

            $warn_roles = getRoleValue($this->guild, 'warn_roles');
            if (empty($warn_roles)) {
                throw new \Exception("Nincsenek warn rangok konfigurálva");
            }

            $new_roles = array_diff($this->selected_user_member_data['roles'], $warn_roles);

            $response = changeMemberData($this->guild->guild_id, $this->selected_user->discord_id, array_values($new_roles));

            if ($response->successful()) {
                $this->guild->users()->updateExistingPivot($this->selected_user->discord_id, [
                    'last_warn_time' => null,
                ]);

                $this->toast()->success('Sikeres művelet', 'Az összes figyelmeztető rang eltávolítva!')->send();

                $channel_id = $this->getDefaultLogChannelId($this->guild);
                $this->sendDefaultLog($channel_id, [
                    'command' => 'removewarn',
                    'message' => "A felhasználó levette <@{$this->selected_user->discord_id}> felhasználóról az összes figyelmeztetést.",
                    'user' => auth()->id(),
                ]);
            } else {
                throw new \Exception("Discord API hiba: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->toast()->error('Hiba történt', $e->getMessage())->send();
            logger()->error('Figyelmeztetés törlési hiba: ' . $e->getMessage());
        }
    }

    public function deleteUser(): void
    {
        $this->dialog()
            ->question('Figyelmeztetés!', 'Biztosan véglegesen törölni szeretnéd?')
            ->confirm('Törlés', method: 'destroyUser')
            ->cancel('Mégse')
            ->send();
    }

    public function destroyUser()
    {
        if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::DELETE_USER])) {
            $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
            return;
        }

        $member_data = getMemberData($this->guild->guild_id, $this->selected_user->discord_id);

        if ($member_data !== []) {

            if (empty($member_data['roles'])) {
                $this->toast()->warning('Nincs rang', 'A felhasználónak nincs rangja a Discord szerveren.')->send();
                return;
            }
            $new_roles = array_values($member_data['roles']);

            foreach (RoleTypeEnum::cases() as $role_type) {
                $role_value = getRoleValue($this->guild, $role_type->value);

                if (is_array($role_value)) {
                    $new_roles = array_diff($new_roles, $role_value);
                } elseif (is_string($role_value)) {
                    $new_roles = array_filter($new_roles, function($item) use ($role_value) {
                        return $item !== $role_value;
                    });
                }
            }


            $response = changeMemberData($this->guild->guild_id, $this->selected_user->discord_id, $new_roles);

            if (!$response->successful()) {
                $this->toast()->error('Hiba történt', 'Nem sikerült eltávolítani a felhasználót a Discord szerverről.')->send();
                return;
            }
        }

        $this->guild->users()->detach($this->selected_user->discord_id);

        $this->guild->duties()->where('user_discord_id', $this->selected_user->discord_id)->delete();

        $this->toast()->success('Sikeres művelet', 'Sikeresen töröltél egy felhasználót.')->send();

        $channel_id = $this->getDefaultLogChannelId($this->guild);
        $this->sendDefaultLog($channel_id, [
            'command' => 'deleteuser',
            'message' => "A felhasználó törölte <@{$this->selected_user->discord_id}> felhasználót.",
            'user' => auth()->id(),
        ]);

        $this->modal = false;
    }

    public function updateUserIcData(): void
    {
        if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::EDIT_USER_IC_DATA])) {
            $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
            return;
        }

        $validated = $this->validate((new UpdateUserPivotLivewireRequest())->rules());

        $this->guild->users()->updateExistingPivot($this->selected_user->discord_id, [
            'ic_name' => $validated['ic_name'],
            'ic_number' => $validated['ic_number'],
            'ic_tel' => $validated['ic_tel'],
        ]);

        $this->toast()->success('Sikeres művelet', 'Sikeresen módosítottad egy felhasználó IC adatait.')->send();

        $channel_id = $this->getDefaultLogChannelId($this->guild);
        $this->sendDefaultLog($channel_id, [
            'message' => "A felhasználó módosította <@{$this->selected_user->discord_id}> felhasználó IC adatait.",
            'user' => auth()->id(),
        ]);
    }

    public function autoReportDuty(): void
    {
        if (!$this->ensureFeatureEnabled($this->guild, SettingTypeEnum::CHECK_SYSTEM)) {
            return;
        }

        if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::USE_AUTO_REPORT])) {
            $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod az aktuális szolgálati idők szerkesztéséhez.')->send();
            return;
        }

        CheckingDutyAction::run($this->guild, true);

        $this->toast()->success('Sikeres művelet', 'A szolgálati idők ellenőrzése sikeresen megtörtént.')->send();

        $channel_id = $this->getDefaultLogChannelId($this->guild);
        $this->sendDefaultLog($channel_id, [
            'message' => "A felhasználó automatikus szolgálati idő ellenőrzést hajtott végre.",
            'user' => auth()->id(),
        ]);
    }

    public function openModal($user_discord_id): void
    {
        $this->selected_user = $this->guild->users()->where('user_discord_id', $user_discord_id)->first();

        $this->selected_user_member_data = getMemberData($this->guild->guild_id, $this->selected_user->discord_id);

        if(isset($this->selected_user_member_data['roles'])){
            $matched = array_intersect($this->selected_user_member_data['roles'], getRoleValue($this->guild, 'ic_roles'));
            $this->selected_user_role = reset($matched) ?: null;
        }

        $this->ic_name = $this->selected_user->pivot->ic_name;
        $this->ic_number = $this->selected_user->pivot->ic_number;
        $this->ic_tel = $this->selected_user->pivot->ic_tel;

        $this->modal = true;

        $this->updatedAddTotalDutyTime();

        $this->updatedAddPeriodDutyTime();
    }

    public function confirmBlackList()
    {
        if (!$this->ensureFeatureEnabled($this->guild, SettingTypeEnum::BLACKLIST_SYSTEM)) {
            return;
        }

        if (auth()->user()->cannot('hasPermission', [$this->guild, PermissionEnum::ADD_BLACKLIST])) {
            $this->toast()->error('Hozzáférés megtagadva', 'Nincs jogosultságod a felhasználó feketelistára tételéhez.')->send();
            return;
        }

        $this->dialog()
            ->question('Figyelmeztetés!', 'Biztosan feketelistára szeretnéd tenni a felhasználót? (A felhasználó automatikusan törlésre kerül a rendszerből.)')
            ->confirm('Feketelistára tétel', method: 'addBlackList')
            ->cancel('Mégse')
            ->send();
    }

    public function addBlackList()
    {
        $validated = $this->validate([
            'blacklist_reason' => 'required|string|max:255|min:3',
        ]);

        $this->selected_user->blacklists()->create([
            'reason' => $validated['blacklist_reason'],
            'guild_guild_id' => $this->guild->guild_id,
        ]);

        $this->destroyUser();

        $this->toast()->success('Sikeres művelet', 'A felhasználó sikeresen feketelistára került.')->send();

        $channel_id = $this->getDefaultLogChannelId($this->guild);
        $this->sendDefaultLog($channel_id, [
            'command' => 'blacklistadd',
            'message' => "A felhasználó feketelistára tette <@{$this->selected_user->discord_id}> felhasználót. Indok: {$validated['blacklist_reason']}",
            'user' => auth()->id(),
        ]);
    }

    public function with(): array
    {
        return [
            'headers' => [
                ['index' => 'discord_id', 'label' => 'Discord ID'],
                ['index' => 'name', 'label' => 'DC Név'],
                ['index' => 'ic_name', 'label' => 'IC név'],
                ['index' => 'duties_sum_value', 'label' => 'Szolgálati idő'],
                ['index' => 'duties_with_trashed_sum_value', 'label' => 'Összes sz. idő'],
                ['index' => 'in_role_days', 'label' => 'Rangon'],
                ['index' => 'in_guild_days', 'label' => 'Frakcióban'],
                ['index' => 'status', 'label' => 'Státusz', 'sortable' => false],
                ['index' => 'action'],
            ],

            'rows' => $this->guild->users()
                ->withSum(['duties' => function ($query) {
                    $query->where('guild_guild_id', $this->guild->guild_id);
                }], 'value')
                ->withSum(['dutiesWithTrashed' => function ($query) {
                    $query->where('guild_guild_id', $this->guild->guild_id);
                }], 'value')
                ->selectRaw('DATEDIFF(NOW(), COALESCE(guild_user.last_role_time, guild_user.created_at)) as in_role_days')
                ->selectRaw('DATEDIFF(NOW(), guild_user.created_at) as in_guild_days')
                ->orderBy(...array_values($this->sort))
                ->when($this->search, function (Builder $query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('discord_id', 'like', "%{$this->search}%")
                            ->orWhere('users.name', 'like', "%{$this->search}%")
                            ->orWhere('guild_user.ic_name', 'like', "%{$this->search}%")
                            ->orWhere('guild_user.ic_number', 'like', "%{$this->search}%")
                            ->orWhere('guild_user.ic_tel', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                    });
                })
                ->paginate($this->quantity)
                ->through(function ($user) {
                    return [
                        'discord_id' => $user->discord_id,
                        'name' => $user->name,
                        'ic_name' => $user->pivot->ic_name,
                        'duties_sum_value' => $this->formatMinutesToHHMM($user->duties_sum_value),
                        'duties_with_trashed_sum_value' => $this->formatMinutesToHHMM($user->duties_with_trashed_sum_value),
                        'in_role_days' => $user->in_role_days . ' napja',
                        'in_guild_days' => $user->in_guild_days . ' napja',
                        'status' => collect([
                            $user->pivot->freedom_expiring && Carbon::parse($user->pivot->freedom_expiring)->isFuture() ? 'Szab.' : null,
                            $user->pivot->last_warn_time && Carbon::parse($user->pivot->last_warn_time)->diffInDays(now()) < 7 ? 'Figy.' : null,
                        ])->filter()->join(' / '),
                    ];
                })
                ->withQueryString(),

            'type' => 'data',
        ];
    }

}; ?>

<div>
    <div class="flex justify-center sm:w-1/2 lg:w-1/4 mx-auto my-4">
        <x-stats icon="user-group" :number="$this->guild->users()->count()" wire:click="resetPage()"
                 title="Összes felhasználó" footer="Kattints a kártyára az oldal az adatok frissítéséért."
                 animated/>
    </div>
    <x-card header="Összes tagra vonatkozó kezelések" minimize="mount">
        <div class="flex flex-col lg:flex-row gap-4">
            <x-button text="Szolgálati idők ellenőrzése" wire:click="autoReportDuty" icon="shield-check"/>
            <x-button text="Szolgálati idők törlése" wire:click="deleteDuties('{{DutyTypeEnum::PERIOD}}')" color="red"
                      icon="trash"/>
            <x-button text="Összes szolgálati idők törlése" wire:click="deleteDuties('{{DutyTypeEnum::TOTAL}}')"
                      color="red" icon="trash"/>
        </div>
    </x-card>

    <x-table :$headers :$rows filter loading paginate striped :$sort :quantity="[10,20,50]">
        @interact('column_action', $row)
            <x-button.circle color="indigo" icon="wrench-screwdriver" wire:click="openModal('{{ $row['discord_id'] }}')"/>
        @endinteract
    </x-table>

    <x-modal title="Felhasználó kezelése" size="2xl" wire="modal" z-index="z-40">
        <div class="flex flex-col gap-4">
            <x-card header="Szolgálati idő szerkesztése" minimize>
                <div class="flex flex-col lg:flex-row gap-2">
                    <x-number wire:model.live.debounce.500ms="add_period_duty_time" step="5"/>
                    <x-input wire:model.live.debounce.500ms="period_duty_time" readonly/>
                    <x-button icon="plus" wire:click="addDuty('{{DutyTypeEnum::PERIOD}}')"/>
                    <x-button icon="trash" color="red" wire:click="deleteUserDuties('{{DutyTypeEnum::PERIOD}}')"/>
                </div>
            </x-card>
            <x-card header="Összes szolgálati idő szerkesztése" minimize="mount">
                <div class="flex flex-col lg:flex-row gap-2">
                    <x-number wire:model.live.debounce.500ms="add_total_duty_time" step="5"/>
                    <x-input wire:model.live.debounce.500ms="total_duty_time" readonly/>
                    <x-button icon="plus" wire:click="addDuty('{{DutyTypeEnum::TOTAL}}')"/>
                    <x-button icon="trash" color="red" wire:click="deleteUserDuties('{{DutyTypeEnum::TOTAL}}')"/>
                </div>
            </x-card>
            @if($selected_user_role)
                <x-card header="Rang szerkesztése" minimize>
                    <x-select.styled wire:change="updateUserRole" :options="$this->ic_roles" wire:model="selected_user_role"
                                     searchable/>
                    <div class="flex gap-4 justify-end">
                        <x-button icon="bars-arrow-up" color="green" wire:click="promoteUserRole" class="mt-2"/>
                        <x-button icon="bars-arrow-down" color="red" wire:click="demoteUserRole" class="mt-2"/>
                    </div>
                </x-card>
            @endif
            <x-card header="IC adatok szerkesztése" minimize="mount">
                <div class="flex flex-col lg:flex-row gap-2">
                    <x-input wire:model.lazy="ic_name" label="Név"/>
                    <x-input wire:model.lazy="ic_number" label="Jelvényszám"/>
                    <x-input wire:model.lazy="ic_tel" label="Telefonszám"/>
                </div>
                <div class="flex justify-end mt-2 lg:mt-4">
                    <x-button icon="bookmark" wire:click="updateUserIcData()"/>
                </div>
            </x-card>
            <x-card header="Feketelistára tétel" minimize="mount">
                <x-textarea label="Indok" wire:model="blacklist_reason">
                </x-textarea>
                <div class="flex justify-end mt-2 lg:mt-4">
                    <x-button icon="flag" wire:click="confirmBlackList" text="Feketelistára tétel" color="red"/>
                </div>
            </x-card>
            <div class="flex flex-col lg:flex-row gap-2 justify-center">
                @if($selected_user_role)
                    <x-button wire:click="deleteUserWarn" text="Warn eltávolítása" icon="exclamation-triangle"/>
                    <x-button wire:click="warnUser" text="Figyelmeztetés" color="orange" icon="exclamation-triangle"/>
                @endif
                <x-button wire:click="deleteUser" text="Felhasználó törlése" color="red" icon="trash"/>
            </div>
        </div>
    </x-modal>

    <div class="fixed bottom-5 right-5 z-10">
        <x-button.circle x-on:click="$modalOpen('add-user-modal')" icon="plus" lg/>
    </div>

    @livewire('guild.add-user')
</div>
