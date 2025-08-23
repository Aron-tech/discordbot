<?php

use App\Enums\Guild\ChannelTypeEnum;
use App\Enums\Guild\RoleTypeEnum;
use App\Enums\Guild\SettingTypeEnum;
use App\Models\Guild;
use App\Models\GuildSelector;
use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;
use TallStackUi\Traits\Interactions;


new
#[Layout('layouts.app')]
#[Title('Beállítások')]
class extends Component {

    use Interactions;

    public ?array $roles = [];
    public ?array $channels = [];

    //Default properties
    public ?array $admin_roles = [];
    public ?array $mod_roles = [];
    public ?array $default_roles = [];
    public ?string $log_channel = null;

    //For custom rank command
    public ?array $custom_roles = [];

    //Holiday system properties
    public ?string $freedom_role = null;
    public ?string $holiday_channel = null;
    public bool $holiday_enabled = false;

    //Check system properties
    public ?int $min_rankup_time = 0;
    public ?int $min_rankup_duty = 0;
    public ?int $next_checking_time = 0;
    public bool $check_enabled = false;

    //Warn system properties
    public ?int $min_duty_time = 0;
    public ?int $warn_time = 0;
    public ?string $warn_channel = null;
    public ?array $warn_roles = [];
    public bool $warn_enabled = false;

    //Duty system properties
    public ?string $duty_role = null;
    public ?string $duty_room = null;
    public ?string $duty_log = null;
    public ?string $active_num_channel = null;
    public bool $duty_enabled = false;

    //Rank system properties
    public ?array $ic_roles = [];
    public bool $rank_system_enabled = false;

    //Egyéb funkciók
    public bool $blacklist_enabled = false;
    public bool $exam_enabled = false;
    public bool $statistics_enabled = false;

    public ?Guild $guild;

    private function updateGuildArray(string $field, string $key, mixed $value): void
    {
        $array = $this->guild->$field ?? [];
        $array[$key] = $value;
        $this->guild->$field = $array;
    }

    public function updatedDutyEnabled(): void
    {
        $this->updateGuildArray('roles', RoleTypeEnum::DUTY_ROLE->value, $this->duty_role);
        $this->updateGuildArray('channels', ChannelTypeEnum::DUTY->value, $this->duty_room);
        $this->updateGuildArray('channels', ChannelTypeEnum::ACTIVE_NUM->value, $this->active_num_channel);
        $this->updateGuildArray('channels', ChannelTypeEnum::DUTY_LOG->value, $this->duty_log);

        if($this->duty_enabled){
            if(!$this->duty_role || !$this->duty_room || !$this->active_num_channel || !$this->duty_log) {
                $this->toast()->warning('Hiba', 'Kérlek állítsd be a Duty funkcióhoz szükséges rangokat és szobákat.')->send();
                $this->duty_enabled = false;
                return;
            }

            $this->updateGuildArray('settings', SettingTypeEnum::DUTY_SYSTEM->value, true);
            $this->toast()->success('A Duty rendszer sikeresen bekapcsolva.')->send();
        }else {
            $this->updateGuildArray('settings', SettingTypeEnum::DUTY_SYSTEM->value, false);
            $this->toast()->success('A Duty rendszer sikeresen kikapcsolva.')->send();
            if($this->check_enabled){
                $this->check_enabled = false;
                $this->updateGuildArray('settings', SettingTypeEnum::CHECK_SYSTEM->value, $this->check_enabled);
                $this->toast()->warning('Figyelmeztetés', 'Az Automatikus ellenőrzés rendszer kikapcsolva, mivel a Duty rendszer ki lett kapcsolva.')->send();
            }
            if($this->warn_enabled){
                $this->warn_enabled = false;
                $this->updateGuildArray('settings', SettingTypeEnum::WARN_SYSTEM->value, $this->warn_enabled);
                $this->toast()->warning('Figyelmeztetés', 'A Figyelmeztetés rendszer kikapcsolva, mivel a Duty rendszer ki lett kapcsolva.')->send();
            }
        }

        $this->guild->save();
    }

    public function updatedWarnEnabled(): void
    {
        $this->updateGuildArray('roles', RoleTypeEnum::WARN_ROLES->value, $this->warn_roles);
        $this->updateGuildArray('channels', ChannelTypeEnum::WARN->value, $this->warn_channel);
        $this->updateGuildArray('settings', SettingTypeEnum::WARN_TIME->value, $this->warn_time);

        if($this->warn_enabled){
            if(!$this->warn_roles || !$this->warn_channel || !$this->warn_time) {
                $this->toast()->warning('Hiba', 'Kérlek állítsd be a Figyelmeztetés funkcióhoz szükséges rangokat és szobákat, illetve egyéb beállításokat.')->send();
                $this->warn_enabled = false;
                return;
            }

            if(!$this->duty_enabled){
                $this->toast()->warning('Hiba', 'A Figyelmeztetés rendszer csak akkor működik, ha a Duty rendszer engedélyezve van.')->send();
                $this->warn_enabled = false;
                return;
            }

            $this->updateGuildArray('settings', SettingTypeEnum::WARN_SYSTEM->value, true);
            $this->toast()->success('A Figyelmeztetés rendszer sikeresen bekapcsolva.')->send();
        }else {
            $this->updateGuildArray('settings', SettingTypeEnum::WARN_SYSTEM->value, false);
            $this->toast()->success('A Figyelmeztetés rendszer sikeresen kikapcsolva.')->send();

            if($this->check_enabled){
                $this->check_enabled = false;
                $this->updateGuildArray('settings', SettingTypeEnum::CHECK_SYSTEM->value, $this->check_enabled);
                $this->toast()->warning('Figyelmeztetés', 'Az Automatikus ellenőrzés rendszer kikapcsolva, mivel a Figyelmeztetés rendszer ki lett kapcsolva.')->send();
            }
        }

        $this->guild->save();
    }

    public function updatedRankSystemEnabled(): void
    {
        $this->updateGuildArray('roles', RoleTypeEnum::IC_ROLES->value, $this->ic_roles);

        if($this->rank_system_enabled){
            if(!$this->ic_roles) {
                $this->toast()->warning('Hiba', 'Kérlek állítsd be a Rangrendszer funkcióhoz szükséges rangokat.')->send();
                $this->rank_system_enabled = false;
                return;
            }

            $this->updateGuildArray('settings', SettingTypeEnum::RANK_SYSTEM->value, $this->rank_system_enabled);

            $this->toast()->success('A Rangrendszer sikeresen bekapcsolva.')->send();
        }else {
            $this->toast()->success('A Rangrendszer sikeresen kikapcsolva.')->send();
            $this->updateGuildArray('settings', SettingTypeEnum::RANK_SYSTEM->value, $this->rank_system_enabled);
            if($this->check_enabled){
                $this->check_enabled = false;
                $this->updateGuildArray('settings', SettingTypeEnum::CHECK_SYSTEM->value, $this->check_enabled);
                $this->toast()->warning('Figyelmeztetés', 'Az Automatikus ellenőrzés rendszer kikapcsolva, mivel a Figyelmeztetés rendszer ki lett kapcsolva.')->send();
            }
        }

        $this->guild->save();
    }

    public function updatedHolidayEnabled(): void
    {
        $this->updateGuildArray('roles', RoleTypeEnum::FREEDOM_ROLE->value, $this->freedom_role);
        $this->updateGuildArray('channels', ChannelTypeEnum::HOLIDAY->value, $this->holiday_channel);

        if($this->holiday_enabled){
            if(!$this->freedom_role || !$this->holiday_channel) {
                $this->toast()->warning('Hiba', 'Kérlek állítsd be a Szabadság funkcióhoz szükséges rangokat és szobákat.')->send();
                $this->holiday_enabled = false;
                return;
            }

            $this->updateGuildArray('settings', SettingTypeEnum::HOLIDAY_SYSTEM->value, true);
            $this->toast()->success('A Szabadság rendszer sikeresen bekapcsolva.')->send();
        }else {
            $this->updateGuildArray('settings', SettingTypeEnum::HOLIDAY_SYSTEM->value, false);
            $this->toast()->success('A Szabadság rendszer sikeresen kikapcsolva.')->send();
            if($this->check_enabled){
                $this->check_enabled = false;
                $this->updateGuildArray('settings', SettingTypeEnum::CHECK_SYSTEM->value, $this->check_enabled);
                $this->toast()->warning('Figyelmeztetés', 'Az Automatikus ellenőrzés rendszer kikapcsolva, mivel a Figyelmeztetés rendszer ki lett kapcsolva.')->send();
            }
        }

        $this->guild->save();
    }

    public function updatedCheckEnabled(): void
    {
        $this->updateGuildArray('settings', SettingTypeEnum::MIN_RANK_UP_DUTY->value, $this->min_rankup_duty);
        $this->updateGuildArray('settings', SettingTypeEnum::MIN_RANK_UP_TIME->value, $this->min_rankup_time);
        $this->updateGuildArray('settings', SettingTypeEnum::MIN_DUTY->value, $this->min_duty_time);
        $this->updateGuildArray('settings', SettingTypeEnum::NEXT_CHECKING_TIME->value, $this->next_checking_time);

        if($this->check_enabled){
            if(!$this->min_rankup_duty || !$this->min_rankup_time || !$this->min_duty_time || !$this->next_checking_time) {
                $this->toast()->warning('Hiba', 'Kérlek állítsd be az Automatikus ellenőrzés funkcióhoz szükséges beállításokat.')->send();
                $this->check_enabled = false;
                return;
            }

            if(!$this->duty_enabled) {
                $this->toast()->warning('Hiba', 'Az Automatikus ellenőrzés rendszer csak akkor működik, ha a Duty rendszer engedélyezve van.')->send();
                $this->check_enabled = false;
                return;
            }

            if(!$this->warn_enabled) {
                $this->toast()->warning('Hiba', 'Az Automatikus ellenőrzés rendszer csak akkor működik, ha a Figyelmeztetés rendszer engedélyezve van.')->send();
                $this->check_enabled = false;
                return;
            }

            if(!$this->holiday_enabled) {
                $this->toast()->warning('Hiba', 'Az Automatikus ellenőrzés rendszer csak akkor működik, ha a Szabadság rendszer engedélyezve van.')->send();
                $this->check_enabled = false;
                return;
            }

            if(!$this->rank_system_enabled) {
                $this->toast()->warning('Hiba', 'Az Automatikus ellenőrzés rendszer csak akkor működik, ha a Rangrendszer engedélyezve van.')->send();
                $this->check_enabled = false;
                return;
            }

            $this->updateGuildArray('settings', SettingTypeEnum::CHECK_SYSTEM->value, true);
            $this->toast()->success('Az Automatikus ellenőrzés rendszer sikeresen bekapcsolva.')->send();
        }else {
            $this->updateGuildArray('settings', SettingTypeEnum::CHECK_SYSTEM->value, false);
            $this->toast()->success('Az Automatikus ellenőrzés rendszer sikeresen kikapcsolva.')->send();
        }

        $this->guild->save();
    }

    private function getRoles()
    {
        $roles = cache()->remember($this->guild->guild_id . '_roles', 15, function () {
            return getGuildData($this->guild->guild_id, 'roles');
        });

        return collect($roles)
            ->sortBy('position')
            ->map(function ($role) {
                return [
                    'label' => $role['name'],
                    'value' => $role['id'],
                ];
            })->toArray();
    }

    public function updatedBlacklistEnabled(): void
    {
        $this->updateGuildArray('settings', SettingTypeEnum::BLACKLIST_SYSTEM->value, $this->blacklist_enabled);

        if($this->blacklist_enabled){
            $this->toast()->success('A Feketelista funkció sikeresen bekapcsolva.')->send();
        }else {
            $this->toast()->success('A Feketelista funkció sikeresen kikapcsolva.')->send();
        }

        $this->guild->save();
    }

    public function updatedExamEnabled(): void
    {
        $this->updateGuildArray('settings', SettingTypeEnum::EXAM_SYSTEM->value, $this->exam_enabled);

        if($this->exam_enabled){
            $this->toast()->success('A Vizsgarendszer funkció sikeresen bekapcsolva.')->send();
        }else {
            $this->toast()->success('A Vizsgarendszer funkció sikeresen kikapcsolva.')->send();
        }

        $this->guild->save();
    }

    public function updatedStatisticsEnabled(): void
    {
        $this->updateGuildArray('settings', SettingTypeEnum::STATISTIC_SYSTEM->value, $this->statistics_enabled);

        if($this->statistics_enabled){
            $this->toast()->success('A Statisztika funkció sikeresen bekapcsolva.')->send();
        }else {
            $this->toast()->success('A Statisztika funkció sikeresen kikapcsolva.')->send();
        }

        $this->guild->save();
    }

    private function getChannels()
    {
        $channels = cache()->remember($this->guild->guild_id . '_channels', 15, function () {
            return getGuildData($this->guild->guild_id, 'channels');
        });
        return collect($channels)
            ->sortBy('position')
            ->map(function ($channel) {
            return [
                'label' => $channel['name'],
                'value' => $channel['id'],
            ];
        })->toArray();
    }

    private function loadPropertiesFromDB(): void
    {
        $this->log_channel = getChannelValue($this->guild, ChannelTypeEnum::DEFAULT_LOG->value);
        $this->admin_roles = getRoleValue($this->guild, RoleTypeEnum::ADMIN_ROLES->value);
        $this->mod_roles = getRoleValue($this->guild, RoleTypeEnum::MOD_ROLES->value);
        $this->default_roles = getRoleValue($this->guild, RoleTypeEnum::DEFAULT_ROLES->value);
        $this->duty_role = getRoleValue($this->guild, RoleTypeEnum::DUTY_ROLE->value);
        $this->freedom_role = getRoleValue($this->guild, RoleTypeEnum::FREEDOM_ROLE->value);
        $this->ic_roles = getRoleValue($this->guild, RoleTypeEnum::IC_ROLES->value);
        $this->warn_roles = getRoleValue($this->guild, RoleTypeEnum::WARN_ROLES->value);
        $this->duty_room = getChannelValue($this->guild, ChannelTypeEnum::DUTY->value);
        $this->duty_log = getChannelValue($this->guild, ChannelTypeEnum::DUTY_LOG->value);
        $this->active_num_channel = getChannelValue($this->guild, ChannelTypeEnum::ACTIVE_NUM->value);
        $this->warn_channel = getChannelValue($this->guild, ChannelTypeEnum::WARN->value);
        $this->holiday_channel = getChannelValue($this->guild, ChannelTypeEnum::HOLIDAY->value);
        $this->custom_roles = getRoleValue($this->guild, RoleTypeEnum::CUSTOM_ROLES->value);
        $this->min_rankup_duty = getSettingValue($this->guild, SettingTypeEnum::MIN_RANK_UP_DUTY->value);
        $this->min_rankup_time = getSettingValue($this->guild, SettingTypeEnum::MIN_RANK_UP_TIME->value);
        $this->min_duty_time = getSettingValue($this->guild, SettingTypeEnum::MIN_DUTY->value);
        $this->warn_time = getSettingValue($this->guild, SettingTypeEnum::WARN_TIME->value);
        $this->duty_enabled = getSettingValue($this->guild, SettingTypeEnum::DUTY_SYSTEM->value) ?? false;
        $this->warn_enabled = getSettingValue($this->guild, SettingTypeEnum::WARN_SYSTEM->value) ?? false;
        $this->rank_system_enabled = getSettingValue($this->guild, SettingTypeEnum::RANK_SYSTEM->value) ?? false;
        $this->holiday_enabled = getSettingValue($this->guild, SettingTypeEnum::HOLIDAY_SYSTEM->value) ?? false;
        $this->next_checking_time = getSettingValue($this->guild, SettingTypeEnum::NEXT_CHECKING_TIME->value);
        $this->check_enabled = getSettingValue($this->guild, SettingTypeEnum::CHECK_SYSTEM->value) ?? false;
        $this->blacklist_enabled = getSettingValue($this->guild, SettingTypeEnum::BLACKLIST_SYSTEM->value) ?? false;
        $this->exam_enabled = getSettingValue($this->guild, SettingTypeEnum::EXAM_SYSTEM->value) ?? false;
        $this->statistics_enabled = getSettingValue($this->guild, SettingTypeEnum::STATISTIC_SYSTEM->value) ?? false;
    }

    public function saveDefaultProperties(): void
    {
        if(!$this->admin_roles || !$this->mod_roles || !$this->default_roles) {
            $this->toast()->warning('Hiba', 'Kérlek állítsd be az admin, moderátor és alap rangokat.')->send();
            return;
        }

        if(!$this->log_channel) {
            $this->toast()->warning('Hiba', 'Kérlek állítsd be a log szobát.')->send();
            return;
        }

        $this->updateGuildArray('roles', RoleTypeEnum::ADMIN_ROLES->value, $this->admin_roles);
        $this->updateGuildArray('roles', RoleTypeEnum::MOD_ROLES->value, $this->mod_roles);
        $this->updateGuildArray('roles', RoleTypeEnum::DEFAULT_ROLES->value, $this->default_roles);
        $this->updateGuildArray('channels', ChannelTypeEnum::DEFAULT_LOG->value, $this->log_channel);

        $this->guild->save();

        $this->toast()->success('Alapértelmezett beállítások sikeresen mentve.')->send();
    }

    public function mount(): void
    {
        $this->guild = GuildSelector::getGuild();
        $this->roles = $this->getRoles();
        $this->channels = $this->getChannels();
        $this->loadPropertiesFromDB();
    }
}; ?>

<div>
    <x-side-bar.separator text="Alapértelmezett beállítások" line />
    <div class="grid lg:grid-cols-3 grid-cols-1 gap-4 lg:gap-8">
        <x-card header="Jogosultságok">
            <div class="space-y-4">
                <x-select.styled label="Admin rangok" wire:model.lazy="admin_roles" :options="$this->roles" multiple searchable/>
                <x-select.styled label="Moderátor rangok" wire:model.lazy="mod_roles" :options="$this->roles" multiple searchable/>
                <x-select.styled label="Alap rangok" wire:model.lazy="default_roles" :options="$this->roles" multiple searchable/>
            </div>
        </x-card>
        <div class="space-y-4">
            <x-card>
                <x-slot:header>
                    Default Log szoba
                    <x-tooltip text="Az összes parancsot képes használni."/>
                </x-slot:header>
                <x-select.styled wire:model.lazy="log_channel" :options="$this->channels" searchable/>
            </x-card>
        </div>
        <div class="justify-self-end self-end">
            <x-button wire:click="saveDefaultProperties()">Mentés</x-button>
        </div>
    </div>
    <x-side-bar.separator @class(['my-16']) text="Funkció beállítások" line />
    <div class="grid lg:grid-cols-3 grid-cols-1 gap-4 lg:gap-8">
        <x-card header="Duty funkció">
            <div class="space-y-4">
                <x-select.styled label="Duty rang" wire:model.lazy="duty_role" :options="$this->roles" searchable/>
                <x-select.styled label="Panel szoba" wire:model.lazy="duty_room" :options="$this->channels" searchable/>
                <x-select.styled label="Szolgálatban lévők száma – csatorna" wire:model.lazy="active_num_channel" :options="$this->channels" searchable/>
                <x-select.styled label="Log szoba" wire:model.lazy="duty_log" :options="$this->channels" searchable/>
                <x-toggle wire:model.live="duty_enabled" label="Funkció engedélyezése" />
            </div>
        </x-card>
        <div class="space-y-4">
            <x-card header="Figyelmeztetés funkció">
                <div class="space-y-4">
                    <x-select.styled label="Figyelmeztetés rangok" wire:model.lazy="warn_roles" :options="$this->roles" multiple searchable/>
                    <x-select.styled label="Figyelmeztetés szoba" wire:model.lazy="warn_channel" :options="$this->channels" searchable/>
                    <x-number label="Automatikus lejárata (Nap)" wire:model.lazy="warn_time" step="1"/>
                    <x-toggle wire:model.live="warn_enabled" label="Funkció engedélyezése" />
                </div>
            </x-card>
            <x-card>
                <x-slot:header>
                    Rangok az egyedi rang parancshoz
                    <x-tooltip text="Te döntöd el, hogy a parancs használatakor mely rangokat tegye a felhasználóra. A parancs törli a felhasználó összes rangját és ezeket fogja rátenni ezt csak nem regisztráltokon csinálja meg."/>
                </x-slot:header>
                <x-select.styled wire:model.lazy="custom_roles" :options="$this->roles" multiple searchable/>
            </x-card>
        </div>
        <div class="space-y-4">
            <x-card header="Rangrendszer funkció">
                <div class="space-y-4">
                    <x-select.styled label="Rangok (Sorrendben jelöld ki)" wire:model.lazy="ic_roles" :options="$this->roles" multiple searchable/>
                    <x-toggle wire:model.live="rank_system_enabled" label="Funkció engedélyezése"/>
                </div>
            </x-card>
            <x-card header="Szabadság funkció">
                <div class="space-y-4">
                    <x-select.styled label="Szabadség rang" wire:model.lazy="freedom_role" :options="$this->roles" searchable/>
                    <x-select.styled label="Szabadság szoba" wire:model.lazy="holiday_channel" :options="$this->channels" searchable/>
                    <x-toggle wire:model.live="holiday_enabled" label="Funkció engedélyezése" />
                </div>
            </x-card>
        </div>
        <x-card header="Automatikus ellenőrzés funkciók">
            <div class="space-y-4">
                <x-number label="Ellenőrzés gyakorisága (nap)" wire:model.lazy="next_checking_time" step="1"/>
                <x-number label="Minimum RangUp idő (órában)" wire:model.lazy="min_rankup_duty" step="0.5"/>
                <x-number label="Előző RangUp óta minimum eltelt idő - " wire:model.lazy="min_rankup_time" step="1"/>
                <x-number label="Minimum szolgálati idő (órában)" wire:model.lazy="min_duty_time" step="0.5"/>
                <x-toggle wire:model.live="check_enabled" label="Funkció engedélyezése" />
            </div>
        </x-card>
        <x-card header="Egyéb funkciók">
            <div class="space-y-4">
                <x-toggle wire:model.live="blacklist_enabled" label="Feketelista funkció engedélyezése"/>
                <x-toggle wire:model.live="exam_enabled" label="Vizsgarendszer funkció engedélyezése"/>
                <x-toggle wire:model.live="statistics_enabled" label="Statisztika funkció engedélyezése"/>
            </div>
        </x-card>
    </div>
</div>

