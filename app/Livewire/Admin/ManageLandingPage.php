<?php

namespace App\Livewire\Admin;

use App\Models\LandingPageSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageLandingPage extends Component
{
    use WithFileUploads;

    public string $page_title = '';

    public string $brand_name = '';

    public string $login_text = '';

    public $navbar_logo;

    public ?string $existingNavbarLogo = null;

    public string $hero_heading = '';

    public string $hero_subtitle = '';

    public $hero_background;

    public ?string $existingHeroBackground = null;

    public string $onboarding_heading = '';

    public string $onboarding_open_message = '';

    public string $onboarding_closed_message = '';

    public string $matric_label = '';

    public string $matric_help = '';

    public string $continue_button = '';

    public string $highlights_title = '';

    public string $highlights_icon = '';

    public string $highlights_empty = '';

    public string $news_title = '';

    public string $news_icon = '';

    public string $news_empty = '';

    public string $events_title = '';

    public string $events_icon = '';

    public string $events_empty = '';

    public string $footer_heading = '';

    public string $footer_tagline = '';

    public string $footer_copyright = '';

    public function mount(): void
    {
        $this->fillFromSettings(LandingPageSetting::current());
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules());
        unset($validated['navbar_logo'], $validated['hero_background']);

        $settings = LandingPageSetting::current();

        if ($this->navbar_logo) {
            if ($settings->navbar_logo_path && Storage::disk('public')->exists($settings->navbar_logo_path)) {
                Storage::disk('public')->delete($settings->navbar_logo_path);
            }
            $validated['navbar_logo_path'] = $this->navbar_logo->store('landing', 'public');
        }

        if ($this->hero_background) {
            if ($settings->hero_background_path && Storage::disk('public')->exists($settings->hero_background_path)) {
                Storage::disk('public')->delete($settings->hero_background_path);
            }
            $validated['hero_background_path'] = $this->hero_background->store('landing', 'public');
        }

        $settings->update($validated);

        $this->navbar_logo = null;
        $this->hero_background = null;
        $this->fillFromSettings($settings->fresh());
        $this->resetValidation();

        session()->flash('message', 'Landing page updated.');
        toastr()->success('Landing page updated.');
    }

    public function removeNavbarLogo(): void
    {
        $settings = LandingPageSetting::current();
        if ($settings->navbar_logo_path && Storage::disk('public')->exists($settings->navbar_logo_path)) {
            Storage::disk('public')->delete($settings->navbar_logo_path);
        }
        $settings->update(['navbar_logo_path' => null]);
        $this->existingNavbarLogo = null;
        toastr()->success('Navbar logo reset to default.');
    }

    public function removeHeroBackground(): void
    {
        $settings = LandingPageSetting::current();
        if ($settings->hero_background_path && Storage::disk('public')->exists($settings->hero_background_path)) {
            Storage::disk('public')->delete($settings->hero_background_path);
        }
        $settings->update(['hero_background_path' => null]);
        $this->existingHeroBackground = null;
        toastr()->success('Hero background reset to default.');
    }

    public function render()
    {
        return view('livewire.admin.manage-landing-page');
    }

    protected function fillFromSettings(LandingPageSetting $settings): void
    {
        $defaults = LandingPageSetting::defaults();

        foreach ($defaults as $key => $value) {
            $this->{$key} = (string) ($settings->{$key} ?? $value ?? '');
        }

        $this->existingNavbarLogo = $settings->navbar_logo_path;
        $this->existingHeroBackground = $settings->hero_background_path;
    }

    protected function rules(): array
    {
        return [
            'page_title' => 'required|string|max:255',
            'brand_name' => 'required|string|max:255',
            'login_text' => 'required|string|max:50',
            'navbar_logo' => 'nullable|image|max:2048',
            'hero_heading' => 'required|string|max:255',
            'hero_subtitle' => 'nullable|string|max:1000',
            'hero_background' => 'nullable|image|max:4096',
            'onboarding_heading' => 'required|string|max:255',
            'onboarding_open_message' => 'nullable|string|max:1000',
            'onboarding_closed_message' => 'nullable|string|max:1000',
            'matric_label' => 'required|string|max:255',
            'matric_help' => 'nullable|string|max:255',
            'continue_button' => 'required|string|max:50',
            'highlights_title' => 'required|string|max:100',
            'highlights_icon' => 'required|string|max:50',
            'highlights_empty' => 'nullable|string|max:500',
            'news_title' => 'required|string|max:100',
            'news_icon' => 'required|string|max:50',
            'news_empty' => 'nullable|string|max:500',
            'events_title' => 'required|string|max:100',
            'events_icon' => 'required|string|max:50',
            'events_empty' => 'nullable|string|max:500',
            'footer_heading' => 'required|string|max:255',
            'footer_tagline' => 'nullable|string|max:500',
            'footer_copyright' => 'required|string|max:255',
        ];
    }
}
