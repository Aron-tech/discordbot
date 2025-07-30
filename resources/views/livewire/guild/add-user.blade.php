<?php

use App\Enums\Guild\RoleTypeEnum;
use App\Http\Requests\UpdateUserPivotLivewireRequest;
use App\Livewire\Traits\DcChecking;
use App\Models\Guild;
use App\Models\GuildSelector;
use App\Models\User;
use Livewire\Volt\Component;
use TallStackUi\Traits\Interactions;

new class extends Component {

    use DcChecking;
    use Interactions;

    public ?Guild $guild = null;

    //The new users variables
    public ?string $new_discord_id = null;

    public ?string $ic_name = null;
    public ?string $ic_number = null;
    public ?string $ic_tel = null;

    public ?array $member_data = null;

    public bool $loading = false;

    public function mount(): void
    {
        $this->guild = GuildSelector::getGuild();
    }

    private function isValidDcId(): bool
    {
        if (empty($this->new_discord_id)) {
            $this->toast()->warning('Sikertelen művelet', 'A Discord ID megadása kötelező!')->send();
            return false;
        }

        $this->member_data = getMemberData($this->guild->guild_id, $this->new_discord_id);

        if ($this->member_data === []) {
            $this->toast()->warning('Sikertelen művelet', 'A Discord ID helytelen vagy nem található a felhasználó a szerveren!')->send();
            return false;
        }

        return true;
    }

    private function resetForm(): void
    {
        $this->new_discord_id = null;
        $this->ic_name = null;
        $this->ic_number = null;
        $this->ic_tel = null;
        $this->member_data = null;
    }

    public function addUserToGuild(): void
    {
        $this->loading = true;

        try {
            $validated = $this->validate((new UpdateUserPivotLivewireRequest())->rules());

            if (!$this->isValidDcId())
                return;

            $user = User::firstOrCreate(['discord_id' => $this->new_discord_id],
                [
                    'name' => $this->member_data['user']['username'],
                    'avatar' => $this->member_data['user']['avatar'],
                ]);

            $this->guild->users()->attach($user->discord_id, [
                'ic_name' => $validated['ic_name'],
                'ic_number' => $validated['ic_number'],
                'ic_tel' => $validated['ic_tel'] ?? null,
            ]);

            $existing_roles = $this->member_data['roles'] ?? [];

            $default_roles = getRoleValue($this->guild, RoleTypeEnum::DEFAULT_ROLES->value);

            $merged_roles = array_unique(array_merge($existing_roles, $default_roles));

            changeMemberData($this->guild->guild_id, $this->new_discord_id, $merged_roles);

                $this->toast()->success('Sikeres művelet', 'A felhasználó sikeresen felvéve a rendszerbe!')->send();
                $this->resetForm();
            } finally {
                $this->loading = false;
        }
    }

}; ?>

<div>
    <x-modal title="Felhasználó felvétele" size="2xl" id="add-user-modal" x-on:open="$focusOn('discord_id')"
             z-index="z-40">
        <x-card>
            <div class="space-y-4">
                <x-input label="Discord ID *" wire:model.lazy="new_discord_id" clearable/>
                <div class="grid lg:grid-cols-3 gap-4">
                    <x-input label="IC Név *" wire:model.lazy="ic_name" clearable/>
                    <x-input label="Jelvényszám *" wire:model.lazy="ic_number" clearable/>
                    <x-input label="Telefonszám" wire:model.lazy="ic_tel" clearable/>
                </div>
            </div>
            <x-slot:footer>
                <x-button x-on:click="$modalClose('add-user-modal')" wire:click="addUserToGuild" class="w-full">
                    Felhasználó hozzáadása
                </x-button>
            </x-slot:footer>
        </x-card>
    </x-modal>

    @if($loading)
        <div class="flex items-center justify-center mt-4">
            <x-loading />
            <span class="ml-2">Felhasználó hozzáadása folyamatban...</span>
        </div>
    @endif
</div>
