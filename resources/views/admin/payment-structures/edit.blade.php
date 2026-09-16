<x-alumniadmin-dashboard>
    <x-admin.surface-styles />

    <div class="main-content right-chat-active admin-surface">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">
                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">Edit payment structure</h1>
                                <p class="ads-page-subtitle">{{ $paymentStructure->name }}</p>
                            </div>
                            <a href="{{ route('admin.payment-structures.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                        </div>

                        <div class="ads-section-card">
                            <form action="{{ route('admin.payment-structures.update', $paymentStructure) }}" method="POST">
                                @csrf
                                @method('PUT')
                                @include('admin.payment-structures.partials.form')
                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-sm ads-btn-primary">Save changes</button>
                                    <a href="{{ route('admin.payment-structures.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard>
