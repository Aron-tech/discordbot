<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketCategory extends Model
{
    protected $table = 'ticket_categories';

    protected $primaryKey = 'id';

    protected $fillable = [
        'guild_guild_id',
        'name',
        'description',
        'moderator_roles',
        'initial_message',
        'max_tickets',
        'category_id',
    ];

    protected $casts = [
        'moderator_roles' => 'json',
        'max_tickets' => 'integer',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class, 'guild_guild_id', 'guild_id');
    }
}
