<div class="mb-3">
    <label for="eventname" class="form-label font-xssss fw-600 text-grey-700">Event name <span class="text-danger">*</span></label>
    <input type="text"
           id="eventname"
           wire:model="eventname"
           class="form-control rounded-xl font-xssss @error('eventname') is-invalid @enderror"
           placeholder="e.g. Class of 2020 reunion dinner">
    @error('eventname') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="date" class="form-label font-xssss fw-600 text-grey-700">Date</label>
        <input type="date"
               id="date"
               wire:model="date"
               class="form-control rounded-xl font-xssss @error('date') is-invalid @enderror">
        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="venue" class="form-label font-xssss fw-600 text-grey-700">Venue</label>
        <input type="text"
               id="venue"
               wire:model="venue"
               class="form-control rounded-xl font-xssss @error('venue') is-invalid @enderror"
               placeholder="Location or online link label">
        @error('venue') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label for="description" class="form-label font-xssss fw-600 text-grey-700">Description</label>
    <textarea id="description"
              wire:model="description"
              rows="5"
              class="form-control rounded-xl font-xssss @error('description') is-invalid @enderror"
              placeholder="Tell alumni what this event is about"></textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="link" class="form-label font-xssss fw-600 text-grey-700">External link</label>
    <input type="url"
           id="link"
           wire:model="link"
           class="form-control rounded-xl font-xssss @error('link') is-invalid @enderror"
           placeholder="https://">
    @error('link') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-4">
    <label for="image" class="form-label font-xssss fw-600 text-grey-700">Cover image</label>
    @if(!empty($existingImagePath))
        <div class="mb-2">
            <img src="{{ asset('storage/' . $existingImagePath) }}"
                 alt="Current event image"
                 class="rounded-xxl"
                 style="max-height: 160px; object-fit: cover;">
        </div>
    @endif
    <input type="file"
           id="image"
           wire:model="image"
           accept="image/*"
           class="form-control rounded-xl font-xssss @error('image') is-invalid @enderror">
    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div wire:loading wire:target="image" class="font-xsssss text-grey-500 mt-1">Uploading image...</div>
</div>
