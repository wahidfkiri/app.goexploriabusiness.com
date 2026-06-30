<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('event_type', 40)->default('holiday');
            $table->text('description')->nullable();
            $table->string('recurrence_type', 40)->default('fixed_date');
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedTinyInteger('day')->nullable();
            $table->unsignedTinyInteger('weekday')->nullable();
            $table->unsignedTinyInteger('nth_occurrence')->nullable();
            $table->smallInteger('offset_days')->nullable();
            $table->unsignedSmallInteger('duration_days')->default(1);
            $table->date('specific_start_date')->nullable();
            $table->date('specific_end_date')->nullable();
            $table->boolean('is_all_day')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('color', 20)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['country_id', 'slug']);
            $table->index(['country_id', 'is_active']);
            $table->index(['country_id', 'recurrence_type']);
            $table->index(['country_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_calendar_events');
    }
};
