<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageLandingPage;
use App\Models\LandingPageSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LandingPageSettingsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_landing_page_renders_configured_copy(): void
    {
        LandingPageSetting::current()->update([
            'page_title' => 'Custom Tab Title',
            'brand_name' => 'Custom Alumni Brand',
            'login_text' => 'Sign in',
            'hero_heading' => 'Custom Portal Heading',
            'hero_subtitle' => 'Custom hero subtitle for alumni.',
            'onboarding_heading' => 'Start Here',
            'matric_label' => 'Your Matric No',
            'continue_button' => 'Proceed',
            'highlights_title' => 'Spotlights',
            'news_title' => 'Bulletins',
            'events_title' => 'Gatherings',
            'footer_heading' => 'Footer Brand',
            'footer_tagline' => 'Custom footer tagline.',
            'footer_copyright' => '© :year Custom Copyright',
        ]);

        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee('Custom Tab Title', false);
        $response->assertSee('Custom Alumni Brand');
        $response->assertSee('Sign in');
        $response->assertSee('Custom Portal Heading');
        $response->assertSee('Custom hero subtitle for alumni.');
        $response->assertSee('Start Here');
        $response->assertSee('Your Matric No');
        $response->assertSee('Proceed');
        $response->assertSee('Spotlights');
        $response->assertSee('Bulletins');
        $response->assertSee('Gatherings');
        $response->assertSee('Footer Brand');
        $response->assertSee('Custom footer tagline.');
        $response->assertSee('© '.date('Y').' Custom Copyright');
        $response->assertDontSee('Welcome to FuLafia Alumni Portal');
    }

    public function test_admin_can_update_landing_page_from_create_event_page(): void
    {
        Role::findOrCreate('administrator');
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $this->actingAs($admin)
            ->get(route('create.event.index'))
            ->assertOk()
            ->assertSee('Landing page')
            ->assertSee('Save landing page')
            ->assertSee('Highlights, News & Events');

        Livewire::actingAs($admin)
            ->test(ManageLandingPage::class)
            ->set('hero_heading', 'Edited Hero')
            ->set('brand_name', 'Edited Brand')
            ->set('footer_heading', 'Edited Footer')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('landing_page_settings', [
            'hero_heading' => 'Edited Hero',
            'brand_name' => 'Edited Brand',
            'footer_heading' => 'Edited Footer',
        ]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Edited Hero')
            ->assertSee('Edited Brand')
            ->assertSee('Edited Footer');
    }
}
