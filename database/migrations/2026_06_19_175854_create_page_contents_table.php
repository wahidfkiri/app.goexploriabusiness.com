<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            
            // Type de contenu
            $table->string('type')->default('about');
            
            // Champs de base
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            
            // Champs vidéo
            $table->integer('duration')->nullable();
            
            // Champs événement
            $table->timestamp('event_start_date')->nullable();
            $table->timestamp('event_end_date')->nullable();
            $table->string('event_location')->nullable();
            $table->string('event_address')->nullable();
            $table->integer('event_capacity')->nullable();
            $table->decimal('event_price', 10, 2)->nullable();
            $table->boolean('event_is_free')->default(false);
            
            // Champs blog
            $table->string('blog_author')->nullable();
            $table->string('blog_category')->nullable();
            $table->text('blog_excerpt')->nullable();
            
            // Champs FAQ
            $table->string('faq_question')->nullable();
            
            // Champs témoignage
            $table->string('testimonial_name')->nullable();
            $table->string('testimonial_role')->nullable();
            $table->integer('testimonial_rating')->nullable();
            $table->text('testimonial_content')->nullable();
            
            // Champs contact
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_address')->nullable();
            $table->string('contact_hours')->nullable();
            $table->text('contact_message')->nullable();
            
            // Champs about
            $table->string('about_subtitle')->nullable();
            $table->text('about_values')->nullable();
            
            // Données supplémentaires
            $table->json('extra_data')->nullable();
            
            // Champs SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // Publication
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            
            // Soft delete et timestamps
            $table->softDeletes();
            $table->timestamps();
            
            // Index
            $table->index(['activity_id', 'type']);
            $table->index('is_active');
            $table->index('event_start_date');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_contents');
    }
};