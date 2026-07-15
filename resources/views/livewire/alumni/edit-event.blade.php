<div class="row feed-body alumni-event-form-page">
    <div class="col-xl-8 col-xxl-9 col-lg-8">
        <div class="mb-3">
            <a href="{{ route('alumni.events.mine') }}" class="font-xssss fw-600 text-primary text-decoration-none">
                <i class="feather-arrow-left me-1"></i> Back to my events
            </a>
        </div>

        <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
            <div class="card-body p-4">
                <h2 class="fw-700 text-grey-900 font-md mb-1">Edit event</h2>
                <p class="font-xssss text-grey-500 mb-4">
                    Update your community event details. Changes to published events remain visible after save.
                </p>

                <form wire:submit="save">
                    @include('livewire.alumni.partials.event-form-fields', ['existingImagePath' => $existingImagePath])

                    <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                        <button type="submit"
                                class="btn btn-sm rounded-xl font-xssss fw-600 bg-primary-gradiant text-white px-4"
                                wire:loading.attr="disabled"
                                wire:target="save,image">
                            <span wire:loading.remove wire:target="save">Save changes</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                        <a href="{{ route('alumni.events.mine') }}"
                           class="btn btn-sm rounded-xl font-xssss fw-600 bg-greylight text-grey-700 px-4 text-decoration-none">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
