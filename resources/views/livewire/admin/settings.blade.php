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

    public ?array $admin_roles = [];
    public ?array $mod_roles = [];
    public ?array $default_roles = [];

    public ?array $ic_roles = [];
    public ?array $warn_roles = [];

    public bool $checking_duty_status = false;
    public ?string $fivem_server_id = null;

    public ?array $custom_roles = [];

    public ?string $duty_role = null;
    public ?string $freedom_role = null;

    public ?string $duty_room = null;
    public ?string $log_channel = null;
    public ?string $duty_log = null;
    public ?string $active_num_channel = null;
    public ?string $warn_channel = null;
    public ?string $holiday_channel = null;


    public ?int $min_rankup_time = 0;
    public ?int $min_rankup_duty = 0;
    public ?int $min_duty_time = 0;
    public ?int $warn_time = 0;

    public ?Guild $guild;

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

    public function saveChannels()
    {
        if (!$this->guild)
            return $this->dialog()->error('Sikertelen művelet', 'A rendszer nem találja a szervert.')->send();

        $this->guild->channels = [
            ChannelTypeEnum::DEFAULT_LOG->value => $this->log_channel,
            ChannelTypeEnum::DUTY->value => $this->duty_room,
            ChannelTypeEnum::ACTIVE_NUM->value => $this->active_num_channel,
            ChannelTypeEnum::DUTY_LOG->value => $this->duty_log,
            ChannelTypeEnum::WARN->value => $this->warn_channel,
            ChannelTypeEnum::HOLIDAY->value => $this->holiday_channel,
        ];
        $this->guild->save();

        $this->toast()->success('Sikeres művelet', 'Sikeresen módosítottad a szobák beállításait.')->send();
    }

    public function saveRoles()
    {
        if (!$this->guild)
            return $this->dialog()->error('Sikertelen művelet', 'A rendszer nem találja a szervert.')->send();

        $this->guild->roles = [
            RoleTypeEnum::ADMIN_ROLES->value => $this->admin_roles,
            RoleTypeEnum::MOD_ROLES->value => $this->mod_roles,
            RoleTypeEnum::DEFAULT_ROLES->value => $this->default_roles,
            RoleTypeEnum::IC_ROLES->value => $this->ic_roles,
            RoleTypeEnum::WARN_ROLES->value => $this->warn_roles,
            RoleTypeEnum::DUTY_ROLE->value => $this->duty_role,
            RoleTypeEnum::FREEDOM_ROLE->value => $this->freedom_role,
            RoleTypeEnum::CUSTOM_ROLES->value => $this->custom_roles,
        ];
        $this->guild->save();

        $this->toast()->success('Sikeres művelet', 'Sikeresen módosítottad a rangok jogosultságait.')->send();
    }

    public function saveSettings()
    {
        if (!$this->guild) {
            return $this->dialog()->error('Sikertelen művelet', 'A rendszer nem találja a szervert.')->send();
        }

        $validated = $this->validate([
            'min_rankup_duty' => 'nullable|numeric|min:0',
            'min_rankup_time' => 'nullable|integer|min:0',
            'min_duty_time' => 'nullable|numeric|min:0',
            'warn_time' => 'nullable|integer|min:0',
            'checking_duty_status' => 'boolean',
            'fivem_server_id' => $this->checking_duty_status ? 'required|string|max:255' : 'nullable|string|max:255',
        ]);

        $this->guild->settings = [
            SettingTypeEnum::MIN_RANK_UP_DUTY->value => $validated['min_rankup_duty'],
            SettingTypeEnum::MIN_RANK_UP_TIME->value => $validated['min_rankup_time'],
            SettingTypeEnum::MIN_DUTY->value => $validated['min_duty_time'],
            SettingTypeEnum::WARN_TIME->value => $validated['warn_time'],
            SettingTypeEnum::CHECKING_DUTY_STATUS->value => $validated['checking_duty_status'],
            SettingTypeEnum::FIVEM_SERVER_ID->value => $validated['fivem_server_id'],
        ];

        try {
            $this->guild->save();
            $this->toast()->success('Sikeres mentés', 'A beállítások sikeresen frissítve lettek.')->send();
        } catch (\Exception $e) {
            $this->dialog()->error('Hiba történt', 'Nem sikerült menteni a beállításokat. Próbáld újra később.')->send();
            logger()->error('Settings save error: ' . $e->getMessage());
        }
    }

    public function mount()
    {
        $this->guild = GuildSelector::getGuild();
        $this->roles = $this->getRoles();
        $this->channels = $this->getChannels();

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

        $this->checking_duty_status = getSettingValue($this->guild, SettingTypeEnum::CHECKING_DUTY_STATUS->value, false);
        $this->fivem_server_id = getSettingValue($this->guild, SettingTypeEnum::FIVEM_SERVER_ID->value, null);
    }
}; ?>

<div>
    <x-side-bar.separator text="Rangok" line />
    <div class="grid lg:grid-cols-3 grid-cols-1 gap-4 lg:gap-8">
        <x-card>
            <x-slot:header>
                Admin rangok
                <x-tooltip text="Az összes parancsot képes használni."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="admin_roles" :options="$this->roles" multiple searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                Moderátor rangok
                <x-tooltip text="Dokumentációban tekintsd meg melyik parancsokat képes használni."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="mod_roles" :options="$this->roles" multiple searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                Alap rangok
                <x-tooltip text="Dokumentációban tekintsd meg melyik parancsokat képes használni."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="default_roles" :options="$this->roles" multiple searchable/>
        </x-card>
        <x-card header="Szolgálati rang">
            <x-select.styled wire:model.lazy="duty_role" :options="$this->roles" searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                IC rangok
                <x-tooltip
                    text="Figyelj a sorrendre. A kijelölés sorrendjének meg kell egyeznie a rang up sorrenddel."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="ic_roles" :options="$this->roles" multiple searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                Figyelmeztetés rangok
                <x-tooltip
                    text="Figyelj a sorrendre. A kijelölés sorrendjének meg kell egyeznie a warn szintek sorrenddel."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="warn_roles" :options="$this->roles" multiple searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                Szabadság rang
                <x-tooltip
                    text="Ezt a rangot fogja a bot rátenni arra a felhasználóra, aki szabadságot vesz ki. (Ameddig ez a rang van az illetőn nem tudja az alap parancsokat használni)"/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="freedom_role" :options="$this->roles" searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                Rangok az egyedi rang parancshoz
                <x-tooltip
                    text="Te döntöd el, hogy a parancs használatakor mely rangokat tegye a felhasználóra. A parancs törli a felhasználó összes rangját és ezeket fogja rátenni ezt csak nem regisztráltokon csinálja meg."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="custom_roles" :options="$this->roles" multiple searchable/>
        </x-card>
    </div>

    <div class="flex justify-end mt-4 lg:mt-8">
        <x-button wire:click="saveRoles()" text="Mentés"/>
    </div>
    <x-side-bar.separator text="Discord szobák" line />
    <div class="grid lg:grid-cols-4 grid-cols-1 gap-4 lg:gap-8">
        <x-card>
            <x-slot:header>
                Duty szoba
                <x-tooltip
                    text="A bot ide küldi be a kezelőpanelt és itt fogja frissíteni a szolgálatban lévők listáját."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="duty_room" :options="$this->channels" searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                Default Log szoba
                <x-tooltip text="Az összes parancsot képes használni."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="log_channel" :options="$this->channels" searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                Duty Log szoba
                <x-tooltip text="Duty log szoba."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="duty_log" :options="$this->channels" searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                Aktív duty létszám szoba
                <x-tooltip text="A csatorna neve a szolgálatban lévők létszáma lesz."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="active_num_channel" :options="$this->channels" searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                Figyelmeztetés szoba
                <x-tooltip text="Ebbe a csatornába küldi be a figyelemztetéseket."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="warn_channel" :options="$this->channels" searchable/>
        </x-card>
        <x-card>
            <x-slot:header>
                Szabadság szoba
                <x-tooltip text="Ebbe a csatornába küldi be a szabadságokat."/>
            </x-slot:header>
            <x-select.styled wire:model.lazy="holiday_channel" :options="$this->channels" searchable/>
        </x-card>
    </div>
    <div class="flex justify-end mt-4 lg:mt-8">
        <x-button wire:click="saveChannels()" text="Mentés"/>
    </div>
    <x-side-bar.separator @class(['my-16']) text="Autmatizációs funkció beállításai" line />
    <div class="grid lg:grid-cols-4 grid-cols-1 gap-4 lg:gap-8">
        <x-card>
            <x-slot:header>
                Minimum RangUp idő
                <x-tooltip
                    text="Add meg órában, hogy mennyi legyen a minimum szolgálati idő amitől automatikusan magasabb rendfokozatot adjon (Ha nincs megadva a funkció nem elérhető)."/>
            </x-slot:header>
            <x-number wire:model.lazy="min_rankup_duty" step="0.5"/>
        </x-card>
        <x-card>
            <x-slot:header>
                Minimum Duty idő
                <x-tooltip
                    text="Add meg órában, hogy mennyi legyen a minimmum szolgálati idő ami alatt automatikusan figyelmeztetést adjon (Ha nincs megadva a funkció nem elérhető)."/>
            </x-slot:header>
            <x-number wire:model.lazy="min_duty_time" step="0.5"/>
        </x-card>
        <x-card>
            <x-slot:header>
                Minimum RangUp idő (Nap)
                <x-tooltip
                    text="Add meg napban minimum mennyi idő múlva lehet magasabb rendfokozatot adni (Ha nincs megadva akkor a funkció csak a szolgálati időt nézi)."/>
            </x-slot:header>
            <x-number wire:model.lazy="min_rankup_time" step="1"/>
        </x-card>
        <x-card>
            <x-slot:header>
                Figyelmeztetés idő (Nap)
                <x-tooltip
                    text="Add meg napban mennyi idő múlva járjon le a figyelmeztetés (Ha nincs megadva akkor a funkció nem működik)."/>
            </x-slot:header>
            <x-number wire:model.lazy="warn_time" step="1"/>
        </x-card>
        <x-card>
            <x-slot:header>
                Automatikus kiléptető (béta)
                <x-tooltip
                    text="A felhasználókat automatikusan kilépteti a rendszer, ha szolgálatban maradtak (Nem menti el a szolgálati idejüket)."/>
            </x-slot:header>
            <div class="space-y-4">
                <x-input label="FiveM szerver ID"
                         wire:model.lazy="fivem_server_id"
                         :disabled="!$checking_duty_status"/>
                <x-toggle label="Funkció engedélyezése"
                          wire:model.lazy="checking_duty_status"/>
            </div>
        </x-card>
    </div>
    <div class="flex justify-end mt-4 lg:mt-8">
        <x-button wire:click="saveSettings" text="Mentés"/>
    </div>

</div>

