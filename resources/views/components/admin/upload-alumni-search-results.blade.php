@props([
    'alumni',
    'programmes',
    'departments',
    'faculties',
    'years',
    'categories',
    'embedded' => false,
])

<x-admin.surface-styles />
<x-admin.data-table-styles />

<div class="main-content right-chat-active admin-surface admin-data-table">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left pe-0">
            <div class="row">
                <div class="col-12">

                    <div class="ads-page-header">
                        <div>
                            <h1 class="ads-page-title">Search results</h1>
                            <p class="ads-page-subtitle">
                                {{ $alumni->total() }} {{ Str::plural('record', $alumni->total()) }} found
                            </p>
                        </div>
                        <div class="ads-page-actions">
                            <a href="{{ route('upload.alumni') }}" class="btn btn-sm btn-outline-secondary">
                                <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
                                Back to upload
                            </a>
                        </div>
                    </div>

                    <div class="ads-section">
                        <div class="ads-section-card" style="padding-bottom: 0.75rem;">
                            <h2 class="ads-section-title">Refine search</h2>
                            <form action="{{ route('upload.alumni.search') }}" method="GET" class="ads-filter-form">
                                <div class="ads-filter-field">
                                    <label for="search_programme">Programme</label>
                                    <select name="programme" id="search_programme" class="form-select form-select-sm ads-select">
                                        <option value="">All programmes</option>
                                        @foreach ($programmes as $programme)
                                            <option value="{{ $programme }}" @selected(request('programme') == $programme)>{{ $programme }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field">
                                    <label for="search_department">Department</label>
                                    <select name="department" id="search_department" class="form-select form-select-sm ads-select">
                                        <option value="">All departments</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department }}" @selected(request('department') == $department)>{{ $department }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field">
                                    <label for="search_faculty">Faculty</label>
                                    <select name="faculty" id="search_faculty" class="form-select form-select-sm ads-select">
                                        <option value="">All faculties</option>
                                        @foreach ($faculties as $faculty)
                                            <option value="{{ $faculty }}" @selected(request('faculty') == $faculty)>{{ $faculty }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field">
                                    <label for="search_year">Year</label>
                                    <select name="year_of_graduation" id="search_year" class="form-select form-select-sm ads-select ads-select-narrow">
                                        <option value="">All years</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" @selected(request('year_of_graduation') == $year)>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field">
                                    <label for="search_category">Category</label>
                                    <select name="category_id" id="search_category" class="form-select form-select-sm ads-select">
                                        <option value="">All categories</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ads-filter-field" style="flex: 0 0 auto; min-width: auto;">
                                    <label aria-hidden="true">&nbsp;</label>
                                    <button type="submit" class="btn btn-sm ads-btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="adt-panel">
                        @if ($alumni->count() > 0)
                            <div class="table-responsive">
                                <table class="table adt-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Matric number</th>
                                            <th>Programme</th>
                                            <th>Department</th>
                                            <th>Faculty</th>
                                            <th>Year</th>
                                            <th>Degree class</th>
                                            <th>Category</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($alumni as $alum)
                                            <tr>
                                                <td>{{ $alum->user?->name ?? '—' }}</td>
                                                <td>{{ $alum->matric_number }}</td>
                                                <td>{{ $alum->programme }}</td>
                                                <td>{{ $alum->department }}</td>
                                                <td>{{ $alum->faculty }}</td>
                                                <td>{{ $alum->year_of_graduation }}</td>
                                                <td>{{ $alum->degree_class ?? '—' }}</td>
                                                <td>{{ $alum->category?->name ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($alumni->hasPages())
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2 border-top">
                                    <p class="font-xssss text-grey-500 mb-0">
                                        Showing {{ $alumni->firstItem() }}–{{ $alumni->lastItem() }}
                                        of {{ $alumni->total() }}
                                    </p>
                                    {{ $alumni->withQueryString()->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        @else
                            <div class="adt-empty">
                                <p class="adt-empty-text mb-0">No alumni found matching your search criteria.</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
@endpush
