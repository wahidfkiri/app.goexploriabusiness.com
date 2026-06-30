<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'cms';

    public function up(): void
    {
        Schema::connection('cms')->create('cms_contact_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('etablissement_id')->index();
            $table->unsignedBigInteger('page_id')->nullable()->index();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();

            $table->string('form_name')->nullable();
            $table->string('source')->nullable();
            $table->string('source_url')->nullable();
            $table->string('referrer')->nullable();

            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('preferred_contact_method')->nullable();

            $table->string('subject')->nullable();
            $table->longText('message');
            $table->enum('status', ['new', 'read', 'replied', 'archived', 'spam'])->default('new')->index();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->index();

            $table->boolean('consent')->default(false);
            $table->boolean('newsletter_opt_in')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['etablissement_id', 'status', 'created_at']);
            $table->index(['etablissement_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::connection('cms')->dropIfExists('cms_contact_messages');
    }
};
