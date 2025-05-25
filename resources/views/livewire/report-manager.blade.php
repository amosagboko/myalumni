<div>
    <div class="container pt-7" style="max-width: 50%; margin: 80px auto 0;">
        <div class="card shadow-xss rounded-xxl border-0 p-4 mb-3">
            <div class="card-body p-0">
                <!-- Search and Filter -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <input type="text" wire:model.live="search" class="form-control rounded-xxl bg-greylight border-0 ps-4 font-xssss text-grey-500 fw-500" placeholder="Search reports...">
                        <select wire:model.live="filter" class="form-select rounded-xxl bg-greylight border-0 ms-2 font-xssss text-grey-500 fw-500">
                            <option value="all">All Reports</option>
                            <option value="draft">Drafts</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div>
                        <a href="{{ route('reports.clearance-form') }}" class="btn btn-primary me-2">
                            <i class="fas fa-file-alt me-1"></i> Clearance Form
                        </a>
                        <button wire:click="$set('showForm', true)" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> New Report
                        </button>
                    </div>
                </div>

                <!-- Report Form -->
                @if($showForm)
                <div class="card shadow-xss rounded-xxl border-0 p-4 mb-4">
                    <form wire:submit="save">
                        <div class="mb-3">
                            <label class="form-label fw-600 text-grey-900 font-xssss">Title</label>
                            <input type="text" wire:model="title" class="form-control rounded-xxl bg-greylight border-0 ps-4 font-xssss text-grey-500 fw-500" placeholder="Enter report title">
                            @error('title') <span class="text-danger font-xssss">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600 text-grey-900 font-xssss">Content</label>
                            <textarea wire:model="content" class="form-control rounded-xxl bg-greylight border-0 ps-4 font-xssss text-grey-500 fw-500" rows="5" placeholder="Enter report content"></textarea>
                            @error('content') <span class="text-danger font-xssss">{{ $message }}</span> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600 text-grey-900 font-xssss">Type</label>
                                <select wire:model="type" class="form-select rounded-xxl bg-greylight border-0 font-xssss text-grey-500 fw-500">
                                    <option value="general">General</option>
                                    <option value="academic">Academic</option>
                                    <option value="career">Career</option>
                                </select>
                                @error('type') <span class="text-danger font-xssss">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600 text-grey-900 font-xssss">Status</label>
                                <select wire:model="status" class="form-select rounded-xxl bg-greylight border-0 font-xssss text-grey-500 fw-500">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                                @error('status') <span class="text-danger font-xssss">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-600 text-grey-900 font-xssss">Attachment</label>
                            <input type="file" wire:model="file" class="form-control rounded-xxl bg-greylight border-0 font-xssss text-grey-500 fw-500">
                            @error('file') <span class="text-danger font-xssss">{{ $message }}</span> @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" wire:click="$set('showForm', false)" class="btn-round-sm bg-greylight text-grey-900 font-xssss me-2">Cancel</button>
                            <button type="submit" class="btn-round-sm bg-current text-white font-xssss">Save Report</button>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Reports List -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="font-xssss fw-600 text-grey-500">Title</th>
                                <th class="font-xssss fw-600 text-grey-500">Type</th>
                                <th class="font-xssss fw-600 text-grey-500">Status</th>
                                <th class="font-xssss fw-600 text-grey-500">Created</th>
                                <th class="font-xssss fw-600 text-grey-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                            <tr>
                                <td class="font-xssss fw-600 text-grey-900">{{ $report->title }}</td>
                                <td class="font-xssss fw-600 text-grey-900">{{ ucfirst($report->type) }}</td>
                                <td>
                                    <span class="badge bg-{{ $report->status === 'published' ? 'success' : ($report->status === 'draft' ? 'warning' : 'secondary') }} font-xssss">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </td>
                                <td class="font-xssss fw-600 text-grey-900">{{ $report->formatted_created_at }}</td>
                                <td>
                                    <div class="d-flex">
                                        <label wire:click="edit({{ $report->id }})" class="badge bg-primary me-2" style="cursor: pointer;">
                                            <x-feather-icon name="edit" size="sm" /> Edit
                                        </label>
                                        @if($report->file_path)
                                        <label wire:click="download({{ $report->id }})" class="badge bg-success me-2" style="cursor: pointer;">
                                            <x-feather-icon name="download" size="sm" /> Download
                                        </label>
                                        @endif
                                        <label wire:click="delete({{ $report->id }})" class="badge bg-danger" style="cursor: pointer;" onclick="return confirm('Are you sure you want to delete this report?')">
                                            <x-feather-icon name="trash-2" size="sm" /> Delete
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center font-xssss fw-600 text-grey-500">No reports found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
</div> 