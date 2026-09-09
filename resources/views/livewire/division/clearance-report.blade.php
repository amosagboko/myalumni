<div>
    <x-admin.surface-styles />
    <x-admin.data-table-styles />

    <div class="main-content admin-surface admin-data-table" style="padding-right: 1.25rem;">
        <div class="middle-sidebar-bottom">
            <div class="middle-sidebar-left pe-0">
                <div class="row">
                    <div class="col-12">

                        <div class="ads-page-header">
                            <div>
                                <h1 class="ads-page-title">{{ $pageTitle }}</h1>
                                <p class="ads-page-subtitle">Current clearance status for alumni, with last action details.</p>
                            </div>
                        </div>

                        @unless ($officeEnabled)
                            <div class="ads-alert ads-alert-error mb-3">
                                Clearance for this office is currently disabled by admin.
                            </div>
                        @endunless

                        <div
                            class="grad-stats"
                            id="grad-stats-dashboard"
                            data-stats="{{ json_encode($chartData) }}"
                            wire:ignore
                        >
                            <div class="grad-stats-panel">
                                <h2 class="grad-stats-title">Statistics by year of graduation</h2>
                                <p class="grad-stats-subtitle">Cleared and not cleared totals for each graduating class.</p>

                                <div class="grad-stats-filters">
                                    <div>
                                        <label for="grad-stats-year">Year</label>
                                        <select id="grad-stats-year" aria-label="Filter by graduation year">
                                            <option value="all">All years</option>
                                            @foreach ($chartData as $stat)
                                                <option value="{{ $stat['year'] }}">{{ $stat['year'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" id="grad-stats-clear">Clear filter</button>
                                </div>

                                <div class="grad-stats-cards" id="grad-stats-cards"></div>
                                <div id="grad-stats-chart"></div>
                            </div>
                        </div>

                        <div class="adt-panel">
                            <div class="adt-toolbar">
                                <div class="adt-filters">
                                    <div class="adt-search">
                                        <i data-feather="search" class="adt-search-icon"></i>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.400ms="search"
                                            class="form-control form-control-sm"
                                            placeholder="Alumni or matric…"
                                        >
                                    </div>
                                    <select wire:model.live="year" class="form-select form-select-sm adt-select">
                                        <option value="">All years</option>
                                        @foreach ($years as $graduationYear)
                                            <option value="{{ $graduationYear }}">{{ $graduationYear }}</option>
                                        @endforeach
                                    </select>
                                    <select wire:model.live="status" class="form-select form-select-sm adt-select">
                                        <option value="">All statuses</option>
                                        <option value="cleared">Cleared</option>
                                        <option value="not_cleared">Not cleared</option>
                                    </select>
                                    <div class="adt-search">
                                        <i data-feather="user" class="adt-search-icon"></i>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.400ms="actorName"
                                            class="form-control form-control-sm"
                                            placeholder="Actor name…"
                                        >
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="clearFilters">
                                        Clear
                                    </button>
                                </div>
                            </div>

                            <div class="adt-table-wrap">
                                <table class="adt-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <button type="button" class="btn btn-link p-0 text-decoration-none" wire:click="sortBy('when')">When</button>
                                            </th>
                                            <th>
                                                <button type="button" class="btn btn-link p-0 text-decoration-none" wire:click="sortBy('alumni')">Alumni</button>
                                            </th>
                                            <th>
                                                <button type="button" class="btn btn-link p-0 text-decoration-none" wire:click="sortBy('matric')">Matric</button>
                                            </th>
                                            <th>
                                                <button type="button" class="btn btn-link p-0 text-decoration-none" wire:click="sortBy('status')">Status</button>
                                            </th>
                                            <th>
                                                <button type="button" class="btn btn-link p-0 text-decoration-none" wire:click="sortBy('actor')">Actor</button>
                                            </th>
                                            <th>Reason</th>
                                            <th>
                                                <button type="button" class="btn btn-link p-0 text-decoration-none" wire:click="sortBy('year')">Year</button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($rows as $row)
                                            <tr>
                                                <td>
                                                    @if ($row->cleared_at)
                                                        {{ \Illuminate\Support\Carbon::parse($row->cleared_at)->format('M d, Y g:i A') }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>{{ $row->alumni_name }}</td>
                                                <td>{{ $row->matric_number }}</td>
                                                <td>
                                                    @if ($row->is_cleared)
                                                        <span class="badge bg-success">Cleared</span>
                                                    @else
                                                        <span class="badge bg-secondary">Not cleared</span>
                                                    @endif
                                                </td>
                                                <td>{{ $row->actor_name ?: '—' }}</td>
                                                <td>{{ $fixedReason }}</td>
                                                <td>{{ $row->year_of_graduation ?: '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">No alumni found for the selected filters.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="adt-pagination mt-3">
                                {{ $rows->links() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .grad-stats {
            --gs-bg: #ffffff;
            --gs-border: #e5e7eb;
            --gs-muted: #6b7280;
            --gs-text: #111827;
            --gs-green: #16a34a;
            --gs-red: #dc2626;
            --gs-blue: #2563eb;
            --gs-green-soft: #dcfce7;
            --gs-red-soft: #fee2e2;
            --gs-blue-soft: #dbeafe;
            --gs-radius: 12px;
            font-family: inherit;
            color: var(--gs-text);
        }

        .grad-stats * {
            box-sizing: border-box;
        }

        .grad-stats-panel {
            background: var(--gs-bg);
            border: 1px solid var(--gs-border);
            border-radius: var(--gs-radius);
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .grad-stats-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
            color: var(--gs-text);
        }

        .grad-stats-subtitle {
            margin: 0 0 1rem;
            color: var(--gs-muted);
            font-size: 0.9rem;
        }

        .grad-stats-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .grad-stats-filters label {
            display: block;
            font-size: 0.8rem;
            color: var(--gs-muted);
            margin-bottom: 0.3rem;
        }

        .grad-stats-filters select,
        .grad-stats-filters button {
            height: 38px;
            border: 1px solid var(--gs-border);
            border-radius: 10px;
            background: #fff;
            padding: 0 0.85rem;
            font-size: 0.9rem;
            color: var(--gs-text);
        }

        .grad-stats-filters button {
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .grad-stats-filters button:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .grad-stats-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.85rem;
            margin-bottom: 1.25rem;
        }

        @media (min-width: 768px) {
            .grad-stats-cards {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .grad-stats-card {
            background: #fff;
            border: 1px solid var(--gs-border);
            border-top-width: 3px;
            border-radius: var(--gs-radius);
            padding: 1rem 1.1rem;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .grad-stats-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(17, 24, 39, 0.06);
        }

        .grad-stats-card.is-cleared { border-top-color: var(--gs-green); }
        .grad-stats-card.is-not-cleared { border-top-color: var(--gs-red); }
        .grad-stats-card.is-total { border-top-color: var(--gs-blue); }

        .grad-stats-card-label {
            display: block;
            font-size: 0.8rem;
            color: var(--gs-muted);
            text-transform: lowercase;
            margin-bottom: 0.35rem;
        }

        .grad-stats-card-value {
            font-size: 1.75rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1.1;
        }

        .grad-stats-card.is-cleared .grad-stats-card-value { color: var(--gs-green); }
        .grad-stats-card.is-not-cleared .grad-stats-card-value { color: var(--gs-red); }
        .grad-stats-card.is-total .grad-stats-card-value { color: var(--gs-blue); }

        .grad-stats-chart-card {
            border: 1px solid var(--gs-border);
            border-radius: var(--gs-radius);
            padding: 1rem 1.1rem 1.15rem;
            background: #fff;
        }

        .grad-stats-chart-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0 0 1rem;
            color: var(--gs-text);
        }

        .grad-stats-bars {
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
        }

        .grad-stats-bar-row {
            display: grid;
            grid-template-columns: 52px 1fr 64px;
            align-items: center;
            gap: 0.65rem;
        }

        .grad-stats-bar-year {
            font-size: 0.85rem;
            color: var(--gs-muted);
            font-variant-numeric: tabular-nums;
        }

        .grad-stats-bar-track {
            height: 12px;
            background: #f3f4f6;
            border-radius: 999px;
            overflow: hidden;
        }

        .grad-stats-bar-fill {
            height: 100%;
            width: 0;
            border-radius: 999px;
            background: var(--gs-red);
            transition: width 0.7s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.4s ease;
        }

        .grad-stats-bar-value {
            text-align: right;
            font-size: 0.85rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            color: var(--gs-text);
        }

        .grad-stats-donut-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 0 0.25rem;
        }

        .grad-stats-donut {
            position: relative;
            width: 180px;
            height: 180px;
        }

        .grad-stats-donut svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .grad-stats-donut-ring {
            fill: none;
            stroke-width: 16;
            stroke-linecap: butt;
            transition: stroke-dasharray 0.7s cubic-bezier(0.22, 1, 0.36, 1), stroke 0.3s ease;
        }

        .grad-stats-donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .grad-stats-donut-total {
            font-size: 1.6rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1;
            color: var(--gs-text);
        }

        .grad-stats-donut-caption {
            font-size: 0.75rem;
            color: var(--gs-muted);
            text-transform: lowercase;
            margin-top: 0.2rem;
        }

        .grad-stats-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem 1.25rem;
            font-size: 0.85rem;
            color: var(--gs-text);
        }

        .grad-stats-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .grad-stats-legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .grad-stats-legend-dot.is-cleared { background: var(--gs-green); }
        .grad-stats-legend-dot.is-not-cleared { background: var(--gs-red); }

        .grad-stats-empty {
            color: var(--gs-muted);
            font-size: 0.9rem;
            padding: 1rem 0;
            text-align: center;
        }

        .grad-stats-pager {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
            padding-top: 0.85rem;
            border-top: 1px solid var(--gs-border);
        }

        .grad-stats-pager-meta {
            font-size: 0.8rem;
            color: var(--gs-muted);
            font-variant-numeric: tabular-nums;
        }

        .grad-stats-pager-actions {
            display: flex;
            gap: 0.5rem;
        }

        .grad-stats-pager-btn {
            height: 34px;
            min-width: 76px;
            border: 1px solid var(--gs-border);
            border-radius: 10px;
            background: #fff;
            padding: 0 0.75rem;
            font-size: 0.85rem;
            color: var(--gs-text);
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, opacity 0.2s ease;
        }

        .grad-stats-pager-btn:hover:not(:disabled) {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .grad-stats-pager-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        (function () {
            if (window.__gradStatsDashboardBooted) {
                window.__gradStatsDashboardBoot && window.__gradStatsDashboardBoot();
                return;
            }
            window.__gradStatsDashboardBooted = true;

            function formatNumber(value) {
                return Number(value || 0).toLocaleString();
            }

            function parseData(root) {
                try {
                    const raw = root.getAttribute('data-stats') || '[]';
                    const parsed = JSON.parse(raw);
                    return Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    return [];
                }
            }

            function populateYearOptions(select, data) {
                const current = select.value || 'all';
                select.innerHTML = '<option value="all">All years</option>';
                data.forEach(function (item) {
                    const option = document.createElement('option');
                    option.value = String(item.year);
                    option.textContent = String(item.year);
                    select.appendChild(option);
                });
                const exists = Array.from(select.options).some(function (opt) {
                    return opt.value === current;
                });
                select.value = exists ? current : 'all';
            }

            function renderCards(container, cleared, notCleared) {
                const total = cleared + notCleared;
                container.innerHTML =
                    '<div class="grad-stats-card is-cleared">' +
                        '<span class="grad-stats-card-label">cleared</span>' +
                        '<div class="grad-stats-card-value">' + formatNumber(cleared) + '</div>' +
                    '</div>' +
                    '<div class="grad-stats-card is-not-cleared">' +
                        '<span class="grad-stats-card-label">not cleared</span>' +
                        '<div class="grad-stats-card-value">' + formatNumber(notCleared) + '</div>' +
                    '</div>' +
                    '<div class="grad-stats-card is-total">' +
                        '<span class="grad-stats-card-label">total</span>' +
                        '<div class="grad-stats-card-value">' + formatNumber(total) + '</div>' +
                    '</div>';
            }

            function renderBars(container, data, page, onPageChange) {
                if (!data.length) {
                    container.innerHTML = '<div class="grad-stats-empty">No graduation-year statistics available.</div>';
                    return;
                }

                const perPage = 6;
                const totalPages = Math.max(1, Math.ceil(data.length / perPage));
                const currentPage = Math.min(Math.max(1, page || 1), totalPages);
                const start = (currentPage - 1) * perPage;
                const pageItems = data.slice(start, start + perPage);
                const end = start + pageItems.length;

                const maxNotCleared = Math.max.apply(null, data.map(function (item) {
                    return Number(item.notCleared || 0);
                }).concat([1]));

                let rowsHtml = '';
                pageItems.forEach(function (item, index) {
                    const absoluteIndex = start + index;
                    const opacity = Math.max(0.35, 1 - (absoluteIndex * 0.1));
                    const width = Math.round((Number(item.notCleared || 0) / maxNotCleared) * 100);
                    rowsHtml +=
                        '<div class="grad-stats-bar-row">' +
                            '<div class="grad-stats-bar-year">' + item.year + '</div>' +
                            '<div class="grad-stats-bar-track">' +
                                '<div class="grad-stats-bar-fill" style="opacity:' + opacity + ';" data-width="' + width + '"></div>' +
                            '</div>' +
                            '<div class="grad-stats-bar-value">' + formatNumber(item.notCleared) + '</div>' +
                        '</div>';
                });

                const pagerHtml = totalPages > 1
                    ? '<div class="grad-stats-pager">' +
                        '<div class="grad-stats-pager-meta">Showing ' + (start + 1) + '–' + end + ' of ' + data.length + ' years · Page ' + currentPage + ' of ' + totalPages + '</div>' +
                        '<div class="grad-stats-pager-actions">' +
                            '<button type="button" class="grad-stats-pager-btn" data-page-action="prev"' + (currentPage <= 1 ? ' disabled' : '') + '>Previous</button>' +
                            '<button type="button" class="grad-stats-pager-btn" data-page-action="next"' + (currentPage >= totalPages ? ' disabled' : '') + '>Next</button>' +
                        '</div>' +
                      '</div>'
                    : '';

                container.innerHTML =
                    '<div class="grad-stats-chart-card">' +
                        '<h3 class="grad-stats-chart-title">Not cleared by year</h3>' +
                        '<div class="grad-stats-bars">' + rowsHtml + '</div>' +
                        pagerHtml +
                    '</div>';

                requestAnimationFrame(function () {
                    container.querySelectorAll('.grad-stats-bar-fill').forEach(function (bar) {
                        bar.style.width = bar.getAttribute('data-width') + '%';
                    });
                });

                if (typeof onPageChange === 'function') {
                    container.querySelectorAll('[data-page-action]').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            const action = btn.getAttribute('data-page-action');
                            if (action === 'prev' && currentPage > 1) {
                                onPageChange(currentPage - 1);
                            }
                            if (action === 'next' && currentPage < totalPages) {
                                onPageChange(currentPage + 1);
                            }
                        });
                    });
                }
            }

            function renderDonut(container, year, cleared, notCleared) {
                const total = cleared + notCleared;
                const circumference = 2 * Math.PI * 54;
                const clearedPct = total > 0 ? (cleared / total) * 100 : 0;
                const notClearedPct = total > 0 ? (notCleared / total) * 100 : 0;
                const clearedLen = total > 0 ? (cleared / total) * circumference : 0;
                const notClearedLen = total > 0 ? (notCleared / total) * circumference : circumference;

                container.innerHTML =
                    '<div class="grad-stats-chart-card">' +
                        '<h3 class="grad-stats-chart-title">Breakdown for ' + year + '</h3>' +
                        '<div class="grad-stats-donut-wrap">' +
                            '<div class="grad-stats-donut">' +
                                '<svg viewBox="0 0 140 140" aria-hidden="true">' +
                                    '<circle cx="70" cy="70" r="54" fill="none" stroke="#f3f4f6" stroke-width="16"></circle>' +
                                    '<circle class="grad-stats-donut-ring" cx="70" cy="70" r="54" stroke="#dc2626" stroke-dasharray="0 ' + circumference + '" data-len="' + notClearedLen + '" data-offset="0"></circle>' +
                                    '<circle class="grad-stats-donut-ring" cx="70" cy="70" r="54" stroke="#16a34a" stroke-dasharray="0 ' + circumference + '" data-len="' + clearedLen + '" data-offset="' + (-notClearedLen) + '"></circle>' +
                                '</svg>' +
                                '<div class="grad-stats-donut-center">' +
                                    '<div class="grad-stats-donut-total">' + formatNumber(total) + '</div>' +
                                    '<div class="grad-stats-donut-caption">total</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="grad-stats-legend">' +
                                '<span class="grad-stats-legend-item"><span class="grad-stats-legend-dot is-cleared"></span>Cleared (' + clearedPct.toFixed(1) + '%)</span>' +
                                '<span class="grad-stats-legend-item"><span class="grad-stats-legend-dot is-not-cleared"></span>Not cleared (' + notClearedPct.toFixed(1) + '%)</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>';

                requestAnimationFrame(function () {
                    container.querySelectorAll('.grad-stats-donut-ring[data-len]').forEach(function (ring) {
                        const len = Number(ring.getAttribute('data-len') || 0);
                        const offset = Number(ring.getAttribute('data-offset') || 0);
                        ring.style.strokeDasharray = len + ' ' + circumference;
                        ring.style.strokeDashoffset = String(offset);
                    });
                });
            }

            function renderDashboard(root) {
                const data = parseData(root);
                const yearSelect = root.querySelector('#grad-stats-year');
                const clearBtn = root.querySelector('#grad-stats-clear');
                const cards = root.querySelector('#grad-stats-cards');
                const chart = root.querySelector('#grad-stats-chart');

                if (!yearSelect || !clearBtn || !cards || !chart) {
                    return;
                }

                if (!root.dataset.barPage) {
                    root.dataset.barPage = '1';
                }

                populateYearOptions(yearSelect, data);

                function paint() {
                    const selected = yearSelect.value || 'all';

                    if (!data.length) {
                        cards.innerHTML = '';
                        chart.innerHTML = '<div class="grad-stats-empty">No graduation-year statistics available.</div>';
                        return;
                    }

                    if (selected === 'all') {
                        const cleared = data.reduce(function (sum, item) { return sum + Number(item.cleared || 0); }, 0);
                        const notCleared = data.reduce(function (sum, item) { return sum + Number(item.notCleared || 0); }, 0);
                        renderCards(cards, cleared, notCleared);
                        renderBars(chart, data, Number(root.dataset.barPage || 1), function (nextPage) {
                            root.dataset.barPage = String(nextPage);
                            paint();
                        });
                        return;
                    }

                    const yearItem = data.find(function (item) {
                        return String(item.year) === String(selected);
                    });

                    if (!yearItem) {
                        cards.innerHTML = '';
                        chart.innerHTML = '<div class="grad-stats-empty">No statistics for the selected year.</div>';
                        return;
                    }

                    renderCards(cards, Number(yearItem.cleared || 0), Number(yearItem.notCleared || 0));
                    renderDonut(chart, yearItem.year, Number(yearItem.cleared || 0), Number(yearItem.notCleared || 0));
                }

                if (!yearSelect.dataset.bound) {
                    yearSelect.addEventListener('change', function () {
                        if ((yearSelect.value || 'all') === 'all') {
                            root.dataset.barPage = '1';
                        }
                        paint();
                    });
                    clearBtn.addEventListener('click', function () {
                        yearSelect.value = 'all';
                        root.dataset.barPage = '1';
                        paint();
                    });
                    yearSelect.dataset.bound = '1';
                }

                paint();
            }

            function boot() {
                document.querySelectorAll('#grad-stats-dashboard').forEach(renderDashboard);
            }

            window.__gradStatsDashboardBoot = boot;

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot);
            } else {
                boot();
            }

            document.addEventListener('livewire:navigated', boot);
        })();
    </script>
    @endpush
</div>
