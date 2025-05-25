<x-alumniadmin-dashboard>
    <div class="main-content-body pt-7 mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <div class="card mx-auto" style="max-width: 700px;">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h4 class="card-title mb-0">Alumni Years</h4>
                        <a href="{{ route('alumni-years.create') }}" class="btn btn-primary btn-sm">
                            Add New Year
                        </a>
                    </div>
                    <div class="card-body p-3">
                        @if(session('success'))
                            <div class="alert alert-success py-2 mb-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger py-2 mb-3">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Year</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($alumniYears as $year)
                                        <tr>
                                            <td>{{ $year->year }}</td>
                                            <td>{{ $year->start_date->format('M d, Y') }}</td>
                                            <td>{{ $year->end_date->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge {{ $year->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $year->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('alumni-years.edit', $year) }}" class="btn btn-outline-primary me-2">
                                                        Edit
                                                    </a>
                                                    <form action="{{ route('alumni-years.destroy', $year) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this year?')">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="feather-calendar mb-2" style="font-size: 2rem;"></i>
                                                    <p class="mb-0">No alumni years found.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $alumniYears->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-alumniadmin-dashboard> 