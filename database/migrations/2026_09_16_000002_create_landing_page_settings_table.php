<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_title')->default('FuLafia Alumni Portal');
            $table->string('brand_name')->default('FuLafia Alumni Portal');
            $table->string('login_text')->default('Login');
            $table->string('navbar_logo_path')->nullable();

            $table->string('hero_heading')->default('Welcome to FuLafia Alumni Portal');
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_background_path')->nullable();

            $table->string('onboarding_heading')->default('Begin Your Onboarding');
            $table->text('onboarding_open_message')->nullable();
            $table->text('onboarding_closed_message')->nullable();
            $table->string('matric_label')->default('Matriculation Number');
            $table->string('matric_help')->nullable();
            $table->string('continue_button')->default('Continue');

            $table->string('highlights_title')->default('Highlights');
            $table->string('highlights_icon')->default('bi-stars');
            $table->text('highlights_empty')->nullable();

            $table->string('news_title')->default('News');
            $table->string('news_icon')->default('bi-calendar-event');
            $table->text('news_empty')->nullable();

            $table->string('events_title')->default('Events');
            $table->string('events_icon')->default('bi-briefcase');
            $table->text('events_empty')->nullable();

            $table->string('footer_heading')->default('FuLafia Alumni Portal');
            $table->text('footer_tagline')->nullable();
            $table->string('footer_copyright')->default('© :year Federal University of Lafia. All rights reserved.');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_settings');
    }
};
