<?php

use App\Models\Guild;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guild_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Guild::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->string('ic_name');
            $table->string('ic_number');
            $table->string('ic_tel')->nullable();
            $table->date('last_warn_time')->nullable();
            $table->date('last_role_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guild_user_role');
    }
};
