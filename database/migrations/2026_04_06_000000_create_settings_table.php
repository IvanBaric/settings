<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use IvanBaric\Settings\Support\SettingsModels;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(SettingsModels::table(), function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('page');
            $table->string('key');
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['page', 'key']);
            $table->index('page');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(SettingsModels::table());
    }
};
