<x-alumniadmin-dashboard title="Assign Alumni to Categories | FuLafia Alumni">
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content right-chat-active admin-data-table">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Assign alumni to categories</h1>
                                <p class="ads-page-subtitle">Filter alumni, assign categories individually or in bulk, and export the current dataset.</p>
                            </div>
                            <div class="ads-page-actions">
                                <a href="{{ route('admin.alumni-categories.export') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                    Export
                                </a>
                                <a href="{{ route('admin.alumni-categories.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i data-feather="settings" style="width: 14px; height: 14px;"></i>
                                    Manage categories
                                </a>
                            </div>
                        </div>

                        @livewire('admin.assign-categories')

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', function () {
            setTimeout(function () {
                $('.preloader').fadeOut(300);
                $('.preloader-wrap').fadeOut(300);
            }, 500);
        });

        window.addEventListener('load', function () {
            setTimeout(function () {
                $('.preloader').fadeOut(300);
                $('.preloader-wrap').fadeOut(300);
            }, 1000);
        });

        function initAssignCategoriesFeather() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }

        document.addEventListener('DOMContentLoaded', initAssignCategoriesFeather);
        document.addEventListener('livewire:navigated', initAssignCategoriesFeather);

        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morph.updated', () => {
                initAssignCategoriesFeather();
            });
        }
    </script>
    @endpush
</x-alumniadmin-dashboard>
