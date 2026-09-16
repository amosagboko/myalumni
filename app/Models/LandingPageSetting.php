<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LandingPageSetting extends Model
{
    protected $fillable = [
        'page_title',
        'brand_name',
        'login_text',
        'navbar_logo_path',
        'hero_heading',
        'hero_subtitle',
        'hero_background_path',
        'onboarding_heading',
        'onboarding_open_message',
        'onboarding_closed_message',
        'matric_label',
        'matric_help',
        'continue_button',
        'highlights_title',
        'highlights_icon',
        'highlights_empty',
        'news_title',
        'news_icon',
        'news_empty',
        'events_title',
        'events_icon',
        'events_empty',
        'footer_heading',
        'footer_tagline',
        'footer_copyright',
    ];

    public static function defaults(): array
    {
        return [
            'page_title' => 'FuLafia Alumni Portal',
            'brand_name' => 'FuLafia Alumni Portal',
            'login_text' => 'Login',
            'hero_heading' => 'Welcome to FuLafia Alumni Portal',
            'hero_subtitle' => 'Connect with fellow alumni, stay updated with university news, and access exclusive alumni benefits.',
            'onboarding_heading' => 'Begin Your Onboarding',
            'onboarding_open_message' => 'Enter your matriculation number below to begin your onboarding journey.',
            'onboarding_closed_message' => 'Onboarding is currently closed. Please try again later.',
            'matric_label' => 'Matriculation Number',
            'matric_help' => 'Enter your matriculation number to begin onboarding',
            'continue_button' => 'Continue',
            'highlights_title' => 'Highlights',
            'highlights_icon' => 'bi-stars',
            'highlights_empty' => 'Discover highlights and featured stories from the alumni community.',
            'news_title' => 'News',
            'news_icon' => 'bi-calendar-event',
            'news_empty' => 'Stay updated with the latest news and updates from the alumni community.',
            'events_title' => 'Events',
            'events_icon' => 'bi-briefcase',
            'events_empty' => 'Stay updated with our latest events and happenings as they unfold.',
            'footer_heading' => 'FuLafia Alumni Portal',
            'footer_tagline' => 'Stay connected with your alma mater and fellow alumni.',
            'footer_copyright' => '© :year Federal University of Lafia. All rights reserved.',
        ];
    }

    public static function current(): self
    {
        $row = static::query()->orderBy('id')->first();

        if ($row) {
            return $row;
        }

        return static::query()->create(static::defaults());
    }

    public function bootstrapIcon(string $icon, string $fallback = 'bi-stars'): string
    {
        $icon = trim($icon) ?: $fallback;

        if (str_starts_with($icon, 'bi ')) {
            return $icon;
        }

        if (str_starts_with($icon, 'bi-')) {
            return 'bi '.$icon;
        }

        return 'bi bi-'.$icon;
    }

    public function navbarLogoUrl(): string
    {
        if ($this->navbar_logo_path && Storage::disk('public')->exists($this->navbar_logo_path)) {
            return Storage::url($this->navbar_logo_path);
        }

        return asset('images/alumni-logo1.jpg');
    }

    public function heroBackgroundUrl(): string
    {
        if ($this->hero_background_path && Storage::disk('public')->exists($this->hero_background_path)) {
            return Storage::url($this->hero_background_path);
        }

        return asset('images/fulafia-campus.jpg');
    }

    public function copyrightText(): string
    {
        return str_replace(':year', (string) date('Y'), $this->footer_copyright ?: '');
    }
}
