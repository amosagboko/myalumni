<div>
    <div class="ads-page-header">
        <div>
            <h1 class="ads-page-title">Landing page</h1>
            <p class="ads-page-subtitle">Configure the public homepage copy, images, and section labels. Highlights, news, and events items are managed below.</p>
        </div>
        <div class="ads-page-actions">
            <a href="{{ route('landing') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                <i data-feather="external-link" style="width: 14px; height: 14px;"></i>
                View homepage
            </a>
            <button type="submit" form="landing-page-settings-form" class="btn btn-sm ads-btn-primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Save landing page</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="ads-alert ads-alert-success mb-3" role="alert">{{ session('message') }}</div>
    @endif

    <form id="landing-page-settings-form" wire:submit="save">
        <div class="ads-section">
            <div class="ads-section-card">
                <h2 class="ads-section-title">Branding &amp; navigation</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="page_title" class="form-label">Browser tab title</label>
                        <input type="text" id="page_title" wire:model="page_title" class="form-control form-control-sm @error('page_title') is-invalid @enderror">
                        @error('page_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="brand_name" class="form-label">Navbar brand name</label>
                        <input type="text" id="brand_name" wire:model="brand_name" class="form-control form-control-sm @error('brand_name') is-invalid @enderror">
                        @error('brand_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="login_text" class="form-label">Login link text</label>
                        <input type="text" id="login_text" wire:model="login_text" class="form-control form-control-sm @error('login_text') is-invalid @enderror">
                        @error('login_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="navbar_logo" class="form-label">Navbar logo</label>
                        <input type="file" id="navbar_logo" wire:model="navbar_logo" accept="image/*" class="form-control form-control-sm @error('navbar_logo') is-invalid @enderror">
                        @error('navbar_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Leave empty to keep the current logo. Max 2MB.</div>
                        @if ($navbar_logo)
                            <img src="{{ $navbar_logo->temporaryUrl() }}" alt="New navbar logo preview" class="mt-2 rounded" style="height: 48px; width: auto;">
                        @elseif ($existingNavbarLogo)
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($existingNavbarLogo) }}" alt="Current navbar logo" class="rounded" style="height: 48px; width: auto;">
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="removeNavbarLogo">Reset to default</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="ads-section">
            <div class="ads-section-card">
                <h2 class="ads-section-title">Hero</h2>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="hero_heading" class="form-label">Heading</label>
                        <input type="text" id="hero_heading" wire:model="hero_heading" class="form-control form-control-sm @error('hero_heading') is-invalid @enderror">
                        @error('hero_heading') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="hero_subtitle" class="form-label">Subtitle</label>
                        <textarea id="hero_subtitle" wire:model="hero_subtitle" rows="2" class="form-control form-control-sm @error('hero_subtitle') is-invalid @enderror"></textarea>
                        @error('hero_subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="hero_background" class="form-label">Background image</label>
                        <input type="file" id="hero_background" wire:model="hero_background" accept="image/*" class="form-control form-control-sm @error('hero_background') is-invalid @enderror">
                        @error('hero_background') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Leave empty to keep the current image. Max 4MB.</div>
                        @if ($hero_background)
                            <img src="{{ $hero_background->temporaryUrl() }}" alt="New hero background preview" class="mt-2 rounded" style="max-height: 120px; width: auto;">
                        @elseif ($existingHeroBackground)
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($existingHeroBackground) }}" alt="Current hero background" class="rounded" style="max-height: 80px; width: auto;">
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="removeHeroBackground">Reset to default</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="ads-section">
            <div class="ads-section-card">
                <h2 class="ads-section-title">Onboarding search</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="onboarding_heading" class="form-label">Section heading</label>
                        <input type="text" id="onboarding_heading" wire:model="onboarding_heading" class="form-control form-control-sm @error('onboarding_heading') is-invalid @enderror">
                        @error('onboarding_heading') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="continue_button" class="form-label">Continue button</label>
                        <input type="text" id="continue_button" wire:model="continue_button" class="form-control form-control-sm @error('continue_button') is-invalid @enderror">
                        @error('continue_button') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="onboarding_open_message" class="form-label">Message when onboarding is open</label>
                        <textarea id="onboarding_open_message" wire:model="onboarding_open_message" rows="2" class="form-control form-control-sm @error('onboarding_open_message') is-invalid @enderror"></textarea>
                        @error('onboarding_open_message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="onboarding_closed_message" class="form-label">Message when onboarding is closed</label>
                        <textarea id="onboarding_closed_message" wire:model="onboarding_closed_message" rows="2" class="form-control form-control-sm @error('onboarding_closed_message') is-invalid @enderror"></textarea>
                        @error('onboarding_closed_message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="matric_label" class="form-label">Matriculation field label</label>
                        <input type="text" id="matric_label" wire:model="matric_label" class="form-control form-control-sm @error('matric_label') is-invalid @enderror">
                        @error('matric_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="matric_help" class="form-label">Matriculation help text</label>
                        <input type="text" id="matric_help" wire:model="matric_help" class="form-control form-control-sm @error('matric_help') is-invalid @enderror">
                        @error('matric_help') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="ads-section">
            <div class="ads-section-card">
                <h2 class="ads-section-title">Section titles</h2>
                <p class="text-muted small mb-3">These labels appear on the three homepage columns. The items in each column are still managed in the content list below.</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="highlights_title" class="form-label">Highlights title</label>
                        <input type="text" id="highlights_title" wire:model="highlights_title" class="form-control form-control-sm @error('highlights_title') is-invalid @enderror">
                        @error('highlights_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="highlights_icon" class="form-label">Highlights icon</label>
                        <input type="text" id="highlights_icon" wire:model="highlights_icon" class="form-control form-control-sm @error('highlights_icon') is-invalid @enderror" placeholder="bi-stars">
                        @error('highlights_icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Bootstrap Icon class, e.g. bi-stars</div>
                    </div>
                    <div class="col-md-4">
                        <label for="highlights_empty" class="form-label">Highlights empty message</label>
                        <input type="text" id="highlights_empty" wire:model="highlights_empty" class="form-control form-control-sm @error('highlights_empty') is-invalid @enderror">
                        @error('highlights_empty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="news_title" class="form-label">News title</label>
                        <input type="text" id="news_title" wire:model="news_title" class="form-control form-control-sm @error('news_title') is-invalid @enderror">
                        @error('news_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="news_icon" class="form-label">News icon</label>
                        <input type="text" id="news_icon" wire:model="news_icon" class="form-control form-control-sm @error('news_icon') is-invalid @enderror" placeholder="bi-calendar-event">
                        @error('news_icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="news_empty" class="form-label">News empty message</label>
                        <input type="text" id="news_empty" wire:model="news_empty" class="form-control form-control-sm @error('news_empty') is-invalid @enderror">
                        @error('news_empty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="events_title" class="form-label">Events title</label>
                        <input type="text" id="events_title" wire:model="events_title" class="form-control form-control-sm @error('events_title') is-invalid @enderror">
                        @error('events_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="events_icon" class="form-label">Events icon</label>
                        <input type="text" id="events_icon" wire:model="events_icon" class="form-control form-control-sm @error('events_icon') is-invalid @enderror" placeholder="bi-briefcase">
                        @error('events_icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="events_empty" class="form-label">Events empty message</label>
                        <input type="text" id="events_empty" wire:model="events_empty" class="form-control form-control-sm @error('events_empty') is-invalid @enderror">
                        @error('events_empty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="ads-section mb-4">
            <div class="ads-section-card">
                <h2 class="ads-section-title">Footer</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="footer_heading" class="form-label">Footer heading</label>
                        <input type="text" id="footer_heading" wire:model="footer_heading" class="form-control form-control-sm @error('footer_heading') is-invalid @enderror">
                        @error('footer_heading') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="footer_copyright" class="form-label">Copyright</label>
                        <input type="text" id="footer_copyright" wire:model="footer_copyright" class="form-control form-control-sm @error('footer_copyright') is-invalid @enderror">
                        @error('footer_copyright') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Use <code>:year</code> to insert the current year.</div>
                    </div>
                    <div class="col-12">
                        <label for="footer_tagline" class="form-label">Footer tagline</label>
                        <textarea id="footer_tagline" wire:model="footer_tagline" rows="2" class="form-control form-control-sm @error('footer_tagline') is-invalid @enderror"></textarea>
                        @error('footer_tagline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-sm ads-btn-primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Save landing page</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
