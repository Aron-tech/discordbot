<?php

use App\Enums\Guild\SettingTypeEnum;
use App\Livewire\Traits\DcMessageTrait;
use App\Livewire\Traits\DiscordApiTrait;
use App\Livewire\Traits\FeatureTrait;
use App\Models\Guild;
use App\Models\GuildSelector;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;
use Livewire\Attributes\{Layout, Title, On};
use Livewire\Attributes\Validate;
use App\Models\TicketCategory;

new
#[Layout('layouts.app')]
#[Title('Ticket Manager')]
class extends Component {

    use Interactions;
    use WithPagination;
    use FeatureTrait;
    use DiscordApiTrait;
    use DcMessageTrait;

    public ?Guild $guild = null;

    #[Validate('required|min:3|max:70|string')]
    public string $name = '';
    #[Validate('nullable|max:1000|string')]
    public ?string $description = null;
    #[Validate('required|min:10|max:1000|string')]
    public ?string $initial_message = 'Helló {{USER_ID}}, üdv a ticketedben! Várj türelemmel, míg egy {{ROLE_IDS}} válaszol.';
    #[Validate('required|array|min:1')]
    public array $moderator_roles = [];
    #[Validate('required|integer|min:1|max:255')]
    public int $max_open_tickets = 5;

    public bool $ticket_modal = false;
    public string $modal_title = 'Ticket kategória létrehozás';
    public ?int $editing_category_id = null;

    public array $ticket_categories = [];

    public ?array $roles = [];
    public ?array $channels = [];

    public ?string $panel_room = null;

    public ?int $quantity = 20;
    public ?string $search = null;
    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    public function mount(): void
    {
        $this->guild = GuildSelector::getGuild();
        $this->roles = $this->getRoles();
        $this->channels = $this->getChannels();
        $this->ticket_categories = $this->getTicketCategories();
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

    private function getTicketCategories()
    {
        return $this->guild->ticketCategories()
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($category) {
                return [
                    'label' => $category->name,
                    'value' => $category->id,
                ];
            })
            ->toArray();
    }

    public function selectCategory(?int $category_id = null): void
    {
        $this->editing_category_id = $category_id;
        $this->modal_title = 'Ticket kategória szerkesztés';

        $ticket_category = $this->guild->ticketCategories()->where('id', $category_id)->first();

        if (!$ticket_category) {
            $this->toast()->error('Nem található a ticket kategória!')->send();
            return;
        }

        $this->name = $ticket_category->name;
        $this->description = $ticket_category->description;
        $this->initial_message = $ticket_category->initial_message;
        $this->moderator_roles = $ticket_category->moderator_roles;
        $this->max_open_tickets = $ticket_category->max_tickets;
        $this->editing_category_id = $ticket_category->id;

        $this->ticket_modal = true;
    }

    public function saveCategory(): void
    {
        $validated = $this->validate();

        if ($this->editing_category_id) {

            TicketCategory::find($this->editing_category_id)->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'initial_message' => $validated['initial_message'],
                'moderator_roles' => $validated['moderator_roles'],
                'max_tickets' => $validated['max_open_tickets'],
            ]);

            $this->toast()->success('Sikeresen módosítva a ticket kategória!')->send();
        } else {
            $channel_response = $this->createDiscordCategoryWithTicketPermissions($this->guild->guild_id, $validated['name'], $validated['moderator_roles']);

            $this->guild->ticketCategories()->create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'initial_message' => $validated['initial_message'],
                'moderator_roles' => $validated['moderator_roles'],
                'max_tickets' => $validated['max_open_tickets'],
                'category_id' => $channel_response['id'],
            ]);

            $this->toast()->success('Sikeresen létrehozva a ticket kategória!')->send();
        }

        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'initial_message', 'moderator_roles', 'max_open_tickets']);
        $this->initial_message = 'Helló {{USER_ID}}, üdv a ticketedben! Várj türelemmel, míg egy {{ROLE_IDS}} válaszol.';
        $this->modal_title = 'Ticket kategória létrehozás';
        $this->ticket_modal = false;
        $this->editing_category_id = null;
    }

    public function deleteCategory(): void
    {
        $this->ticket_modal = false;

        $this->dialog()
            ->question('Figyelmeztetés!', 'Biztosan törölni szeretnéd véglegesen a ' . $this->name . ' ticket kategóriát?')
            ->confirm('Törlés', 'destroyCategory')
            ->cancel('Mégse')
            ->send();
    }

    public function destroyCategory(): void
    {
        if (!$this->editing_category_id) {
            $this->toast()->error('Nincs kiválasztva ticket kategória!')->send();
            return;
        }

        $ticket_category = TicketCategory::find($this->editing_category_id);

        if (!$ticket_category) {
            $this->toast()->error('Nem található a ticket kategória!')->send();
            return;
        }

        $ticket_category->delete();

        $this->toast()->success('Sikeresen törölve a ticket kategória!')->send();

        $this->resetForm();
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

    public function sendPanel(): void
    {
        if (!$this->panel_room) {
            $this->toast()->error('Nincs kiválasztva ticket kategória vagy discord csatorna')->send();
            return;
        }

        $response = $this->sendTicketPanel($this->panel_room, $this->guild->guild_id);
        if($response){
            $this->toast()->success('Sikeresen elküldve a ticket panel!')->send();
            $this->panel_room = null;
        }else{
            $this->toast()->error('Hiba történt a ticket panel küldése során!')->send();
        }
    }

    public function with(): array
    {
        return [
            'headers' => [
                ['index' => 'name', 'label' => 'Név'],
                ['index' => 'created_at', 'label' => 'Létrehozva'],
                ['index' => 'action']
            ],
            'rows' => $this->guild->ticketCategories()
                ->when($this->search, function (Builder $query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->paginate($this->quantity)
                ->withQueryString()
        ];
    }

}; ?>

<div>
    <div class="mb-4 flex justify-between">
        <x-button x-on:click="$modalOpen('send-panel')" text="Ticket panel küldése"/>
        <x-button text="Ticket kategória létrehozás" wire:click="$toggle('ticket_modal')" color="green"/>
    </div>
    <x-table :$headers :$rows filter loading paginate striped :$sort :quantity="[10,20,50]">
        @interact('column_action', $row)
        <x-button.circle color="indigo" icon="wrench-screwdriver" wire:click="selectCategory('{{ $row['id'] }}')"/>
        @endinteract
    </x-table>

    <x-modal wire="ticket_modal" :title="$modal_title">
        <div class="space-y-4">
            <x-input label="Kategória neve" wire:model.lazy="name"/>
            <x-textarea label="Kategória leírása" wire:model.lazy="description"/>
            <x-textarea label="Ticket üdvözlő üzenet" wire:model.lazy="initial_message"/>
            <x-select.styled label="Moderátor rangok" wire:model.lazy="moderator_roles" :options="$this->roles" multiple
                             searchable/>
            <x-number label="Maximális ticket szám/felhasználó" wire:model.lazy="max_open_tickets" step="1"/>
        </div>
        <x-slot:footer>
            <div class=" w-full flex justify-between">
                @if($editing_category_id)
                    <x-button wire:click="deleteCategory" icon="trash" text="Törlés" color="red"/>
                @endif
                <div class="flex flex-wrap gap-4">
                    <x-button wire:click="$toggle('ticket_modal')" text="Mégse" color="gray"/>
                    <x-button text="Mentés" wire:click="saveCategory()"/>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>

    <x-modal id="send-panel" title="Ticket panel küldése">
        <div class="space-y-4">
            <x-select.styled label="Panel szoba" wire:model.lazy="panel_room" :options="$this->channels" searchable/>
        </div>
        <x-slot:footer>
            <div class=" w-full flex justify-end gap-4">
                <div class="flex flex-wrap gap-4">
                    <x-button x-on:click="$modalClose('send-panel')" text="Mégse" color="gray"/>
                    <x-button x-on:click="$modalClose('send-panel')" wire:click="sendPanel()" text="Küldés"/>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
