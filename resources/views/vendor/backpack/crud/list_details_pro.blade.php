@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
        trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
        $crud->entity_name_plural => url($crud->route),
        trans('backpack::crud.list') => false,
    ];

    // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none"
        bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</h1>
        <p class="ms-2 ml-2 mb-0" id="datatable_info_stack" bp-section="page-subheading">{!! $crud->getSubheading() ?? '' !!}</p>
    </section>
@endsection

@section('content')
    {{-- Default box --}}
    <div class="row" bp-section="crud-operation-list">

        {{-- THE ACTUAL CONTENT --}}
        <div class="{{ $crud->getListContentClass() }}">

            <div class="row mb-2 align-items-center">
                <div class="col-sm-9">
                    @if ($crud->buttons()->where('stack', 'top')->count() || $crud->exportButtons())
                        <div class="d-print-none {{ $crud->hasAccess('create') ? 'with-border' : '' }}">

                            @include('crud::inc.button_stack', ['stack' => 'top'])

                        </div>
                    @endif
                </div>
                <div class="col-sm-3">
                    <div id="datatable_search_stack" class="mt-sm-0 mt-2 d-print-none">
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path>
                                    <path d="M21 21l-6 -6"></path>
                                </svg>
                            </span>
                            <input type="search" class="form-control"
                                placeholder="{{ trans('backpack::crud.search') }}..." />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Backpack List Filters --}}
            @if ($crud->filtersEnabled())
                @include('crud::inc.filters_navbar')
            @endif

            <div class="{{ backpack_theme_config('classes.tableWrapper') }}">
                <table id="crudTable"
                    class="{{ backpack_theme_config('classes.table') ?? 'table table-striped table-hover nowrap rounded card-table table-vcenter card d-table shadow-xs border-xs' }}"
                    data-responsive-table="{{ (int) $crud->getOperationSetting('responsiveTable') }}"
                    data-has-details-row="{{ (int) $crud->getOperationSetting('detailsRow') }}"
                    data-has-bulk-actions="{{ (int) $crud->getOperationSetting('bulkActions') }}"
                    data-has-line-buttons-as-dropdown="{{ (int) $crud->getOperationSetting('lineButtonsAsDropdown') }}"
                    cellspacing="0">
                    <thead>
                        <tr>
                            {{-- Table columns --}}
                            @foreach ($crud->columns() as $column)
                                <th data-orderable="{{ var_export($column['orderable'], true) }}"
                                    data-priority="{{ $column['priority'] }}" data-column-name="{{ $column['name'] }}"
                                    {{--
                    data-visible-in-table => if developer forced field in table with 'visibleInTable => true'
                    data-visible => regular visibility of the field
                    data-can-be-visible-in-table => prevents the column to be loaded into the table (export-only)
                    data-visible-in-modal => if column apears on responsive modal
                    data-visible-in-export => if this field is exportable
                    data-force-export => force export even if field are hidden
                    --}} {{-- If it is an export field only, we are done. --}}
                                    @if (isset($column['exportOnlyField']) && $column['exportOnlyField'] === true) data-visible="false"
                      data-visible-in-table="false"
                      data-can-be-visible-in-table="false"
                      data-visible-in-modal="false"
                      data-visible-in-export="true"
                      data-force-export="true"
                    @else
                      data-visible-in-table="{{ var_export($column['visibleInTable'] ?? false) }}"
                      data-visible="{{ var_export($column['visibleInTable'] ?? true) }}"
                      data-can-be-visible-in-table="true"
                      data-visible-in-modal="{{ var_export($column['visibleInModal'] ?? true) }}"
                      @if (isset($column['visibleInExport']))
                         @if ($column['visibleInExport'] === false)
                           data-visible-in-export="false"
                           data-force-export="false"
                         @else
                           data-visible-in-export="true"
                           data-force-export="true" @endif
                                @else data-visible-in-export="true" data-force-export="false" @endif
                            @endif
                            >
                            {{-- Bulk checkbox --}}
                            @if ($loop->first && $crud->getOperationSetting('bulkActions'))
                                {!! View::make('crud::columns.inc.bulk_actions_checkbox')->render() !!}
                            @endif
                            {!! $column['label'] !!}
                            </th>
                            @endforeach

                            @if ($crud->buttons()->where('stack', 'line')->count())
                                <th data-orderable="false" data-priority="{{ $crud->getActionsColumnPriority() }}"
                                    data-visible-in-export="false" data-action-column="true">
                                    {{ trans('backpack::crud.actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            {{-- Table columns --}}
                            @foreach ($crud->columns() as $column)
                                <th>
                                    {{-- Bulk checkbox --}}
                                    @if ($loop->first && $crud->getOperationSetting('bulkActions'))
                                        {!! View::make('crud::columns.inc.bulk_actions_checkbox')->render() !!}
                                    @endif
                                    {!! $column['label'] !!}
                                </th>
                            @endforeach

                            @if ($crud->buttons()->where('stack', 'line')->count())
                                <th>{{ trans('backpack::crud.actions') }}</th>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($crud->buttons()->where('stack', 'bottom')->count())
                <div id="bottom_buttons" class="d-print-none text-sm-left">
                    @include('crud::inc.button_stack', ['stack' => 'bottom'])
                    <div id="datatable_button_stack" class="float-right float-end text-right hidden-xs"></div>
                </div>
            @endif

        </div>

        <div class="modal" id="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-title">Modal title</h5>
                    </div>
                    <div class="modal-body" id="modal-body">
                        <p>Loading subservice. Please Wait...</p>
                    </div>
                    <!-- Replace the commented out modal footer with this active one -->
                    <!-- Replace the commented out modal footer with this active one -->
                    <div class="modal-footer">
                        <div class="btn-group ms-auto">

                            <button type="button" class="btn btn-primary  me-2" id="refreshModalButton">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">

                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                                    <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
                                </svg>
                                Refresh
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M18 6l-12 12"></path>
                                    <path d="M6 6l12 12"></path>
                                </svg>
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after_styles')
    {{-- DATA TABLES --}}
    @basset('https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css')
    @basset('https://cdn.datatables.net/fixedheader/3.3.1/css/fixedHeader.dataTables.min.css')
    @basset('https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css')
    <style>
        /* Modal Styles */
        .modal-dialog {
            max-width: 90%;
            max-height: 90vh;
            width: 90%;
            height: 90vh;
            overflow-y: auto;
        }

        .modal-content {
            height: 100%;
            overflow: hidden;
        }

        .modal-body {
            max-height: calc(90vh - 120px);
            padding: 1rem;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .modal-header,
        .modal-footer {
            border-color: rgba(98, 105, 118, 0.16);
            padding: 1rem 1.5rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 500;
        }

        /* Table Styles */
        .table-responsive {
            overflow-y: auto;
            overflow-x: auto;
            max-height: calc(90vh - 160px);
        }

        .table-vcenter td,
        .table-vcenter th {
            vertical-align: middle;
        }

        .card-table tr:hover {
            background-color: rgba(var(--tblr-primary-rgb), 0.02);
        }

        /* Buttons & Filters */
        .subservice-filter-buttons {
            display: flex;
            flex-wrap: wrap;
        }

        .subservice-filter {
            transition: all 0.2s ease;
        }

        .subservice-filter.active {
            font-weight: 600;
        }

        .btn-outline-primary {
            color: #206bc4;
            border-color: #206bc4;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary.active {
            color: #ffffff;
            background-color: #206bc4;
            border-color: #206bc4;
        }

        .btn-outline-secondary {
            color: #626976;
            border-color: #dadcde;
        }

        .btn-outline-secondary:hover,
        .btn-outline-secondary.active {
            color: #ffffff;
            background-color: #626976;
            border-color: #626976;
        }

        /* Button Sizes */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 3px;
        }

        /* Card Styles */
        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            background-color: #fff;
            border: 1px solid rgba(98, 105, 118, 0.16);
            border-radius: 4px;
            box-shadow: rgba(30, 41, 59, 0.04) 0 2px 4px 0;
        }

        .card-body {
            flex: 1 1 auto;
            padding: 1.5rem;
        }

        .subheader {
            font-size: 0.625rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #626976;
        }

        .h1 {
            font-size: 2rem;
            font-weight: 700;
        }

        .progress {
            height: 0.5rem;
            background-color: #e6e7e9;
            border-radius: 4px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25em 0.5em;
            font-size: 0.75em;
            font-weight: 500;
            border-radius: 4px;
        }

        .badge.bg-green {
            background-color: #2fb344;
            color: #fff;
        }

        .badge.bg-azure {
            background-color: #4299e1;
            color: #fff;
        }

        .badge.bg-red {
            background-color: #d63939;
            color: #fff;
        }

        .badge.bg-blue-lt {
            color: #206bc4;
            background-color: #e1e8f0;
        }

        .badge.bg-success {
            background-color: #2fb344;
        }

        .badge.bg-warning {
            background-color: #f59f00;
        }

        .badge.bg-danger {
            background-color: #d63939;
        }

        .badge.bg-info {
            background-color: #4299e1;
        }

        .badge.bg-secondary {
            background-color: #626976;
        }

        /* Empty State */
        .empty {
            text-align: center;
            padding: 2rem;
        }

        .empty-img {
            margin-bottom: 1rem;
        }

        .empty-img svg {
            height: 4rem;
            width: 4rem;
            stroke: #626976;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .empty-subtitle {
            font-size: 0.875rem;
            color: #626976;
        }

        .empty-action {
            margin-top: 1.5rem;
        }

        /* Loading Spinner */
        .spinner-border {
            width: 2rem;
            height: 2rem;
            border: 0.25em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border .75s linear infinite;
        }

        @keyframes spinner-border {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .modal-dialog {
                max-width: 90%;
                max-height: 90vh;
                width: 90%;
                height: 90vh;
                overflow-y: auto;
            }

            .col-sm-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Select2 Overrides */
        .select2-drop-active,
        .select2-container .select2-choices .select2-search-field input,
        .select2-container .select2-choice,
        .select2-container .select2-choices,
        .select2-container-active .select2-choice {
            border: none;
            box-shadow: none;
        }

        .select2-container--bootstrap .select2-dropdown {
            margin-top: -2px;
            margin-left: -1px;
        }

        .backpack-filter {
            width: 100%;
        }

        .backpack-filter.country {
            width: 80%;
        }

        .input-group.date {
            width: 190px;
            max-width: 100%;
        }

        .daterangepicker.dropdown-menu {
            z-index: 3001 !important;
        }

        .form-container {
            display: flex;
            align-items: center;
        }

        .form-container input[type="text"] {
            margin-right: 10px;
        }

        .form-container button {
            margin-left: auto;
        }

        #bp-filters-navbar>ul>li.nav-item {
            display: flex;
            align-items: end;
        }

        #crudTable tbody tr {
            cursor: pointer;
        }
    </style>
    {{-- CRUD LIST CONTENT - crud_list_styles stack --}}
    @stack('crud_list_styles')
@endsection

@section('after_scripts')
    @include('crud::inc.datatables_logic')
    <script>
        $(document).ready(function() {

            function renderClick() {
                $('#crudTable tbody').on('click', 'tr', function(e) {
                    if (e.target.nodeName === "A" || e.target.parentNode.nodeName === "A" ||
                        e.target.className === "slider round" || e.target.nodeName === "INPUT") {
                        return true;
                    }

                    var table = $('#crudTable').DataTable();
                    var data = table.row(this).data();
                    if (!data) return;

                    $('#modal-body').html(
                        '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Loading resources...</p></div>'
                    );

                    var name = $(data[0]).text().trim();
                    $('#modal-title').html(name);

                    getData(name);
                    $('#modal').appendTo("body").modal('show');
                });
            }

            renderClick();

            // Refresh button functionality
            $('#refreshModalButton').on('click', function() {
                const currentName = $('#modal-title').text().trim();
                $('#modal-body').html(
                    '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Refreshing resources...</p></div>'
                );
                getData(currentName);
            });

            function getData(name) {
                $.ajax({
                    type: "POST",
                    url: "/admin/project/popupProject",
                    data: {
                        name: name,
                    },
                    success: function(response) {
                        if (response && response.length > 0) {
                            renderResourceDashboard(response, name);
                        } else {
                            // Display "No data" message with the name
                            $('#modal-body').html(
                                `<div class="empty">
                            <div class="empty-img">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-database-off" width="50" height="50" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M12.983 8.978c3.955 -.182 7.017 -1.446 7.017 -2.978c0 -1.657 -3.582 -3 -8 -3c-1.661 0 -3.204 .19 -4.483 .515m-3.139 1.126c-.238 .418 -.378 .87 -.378 1.359c0 1.657 3.582 3 8 3"></path>
                                    <path d="M4 6v6c0 1.657 3.582 3 8 3c.986 0 1.93 -.067 2.802 -.19m3.187 -.82c1.251 -.53 2.011 -1.228 2.011 -1.99v-6"></path>
                                    <path d="M4 12v6c0 1.657 3.582 3 8 3c3.217 0 5.991 -.712 7.261 -1.74m.739 -3.26v-4"></path>
                                    <path d="M3 3l18 18"></path>
                                </svg>
                            </div>
                            <p class="empty-title">No resources found</p>
                            <p class="empty-subtitle text-muted">No resources available for ${name}</p>
                        </div>`
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", error);
                        $('#modal-body').html(
                            `<div class="empty">
                        <div class="empty-img text-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-triangle" width="50" height="50" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M12 9v2m0 4v.01"></path>
                            <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"></path>
                        </svg>
                        </div>
                        <p class="empty-title">Error loading resources</p>
                        <p class="empty-subtitle text-muted">Server returned: ${xhr.status} ${status}</p>
                        <div class="empty-action">
                            <button class="btn btn-primary" onclick="getData('${name}')">
                                Try Again
                            </button>
                        </div>
                    </div>`
                        );
                    }
                });
            }

            function renderResourceDashboard(data, projectName) {
                $('#modal-body').html('');

                // Prepare stats
                const totalResources = data.length;
                const stats = calculateStats(data);
                const totalSubservices = [...new Set(data.map(item => item.subservice))].length;

                // Create a container with summary stats
                let summaryHtml = `
        <div class="mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h3 class="mb-0">${projectName}</h3>
                            <div class="text-muted">${totalResources} resource${totalResources !== 1 ? 's' : ''} across ${totalSubservices} subservice${totalSubservices !== 1 ? 's' : ''}</div>
                        </div>
                        <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-filter" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z"></path>
                                </svg>
                                Filter
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item resource-filter" href="#" data-filter="all">All</a></li>
                                <li><a class="dropdown-item resource-filter" href="#" data-filter="healthy">Healthy only</a></li>
                                <li><a class="dropdown-item resource-filter" href="#" data-filter="unhealthy">Unhealthy only</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item resource-filter" href="#" data-filter="running">Running only</a></li>
                                <li><a class="dropdown-item resource-filter" href="#" data-filter="sleeping">Sleeping only</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item subservice-filter" href="#" data-filter="all">All Subservices</a></li>
                                ${[...new Set(data.map(item => item.subservice))].map(subservice => 
                                    `<li><a class="dropdown-item subservice-filter" href="#" data-filter="${subservice}">${subservice}</a></li>`
                                ).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Healthy</div>
                            <div class="ms-auto lh-1">
                                <div class="badge bg-success">${stats.healthy}</div>
                            </div>
                        </div>
                        <div class="h1 mb-3">${calculatePercentage(stats.healthy, stats.activeTotal)}%</div>
                        <div class="progress mb-0">
                            <div class="progress-bar bg-success" style="width: ${calculatePercentage(stats.healthy, stats.activeTotal)}%" role="progressbar" aria-valuenow="${stats.healthy}" aria-valuemin="0" aria-valuemax="${stats.activeTotal}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Unhealthy</div>
                            <div class="ms-auto lh-1">
                                <div class="badge bg-danger">${stats.unhealthy}</div>
                            </div>
                        </div>
                        <div class="h1 mb-3">${calculatePercentage(stats.unhealthy, stats.activeTotal)}%</div>
                        <div class="progress mb-0">
                            <div class="progress-bar bg-danger" style="width: ${calculatePercentage(stats.unhealthy, stats.activeTotal)}%" role="progressbar" aria-valuenow="${stats.unhealthy}" aria-valuemin="0" aria-valuemax="${stats.activeTotal}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Running</div>
                            <div class="ms-auto lh-1">
                                <div class="badge bg-primary">${stats.running}</div>
                            </div>
                        </div>
                        <div class="h1 mb-3">${((stats.running/totalResources)*100).toFixed(0)}%</div>
                        <div class="progress mb-0">
                            <div class="progress-bar bg-primary" style="width: ${((stats.running/totalResources)*100).toFixed(0)}%" role="progressbar" aria-valuenow="${stats.running}" aria-valuemin="0" aria-valuemax="${totalResources}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Sleeping</div>
                            <div class="ms-auto lh-1">
                                <div class="badge bg-info">${stats.sleeping}</div>
                            </div>
                        </div>
                        <div class="h1 mb-3">${((stats.sleeping/totalResources)*100).toFixed(0)}%</div>
                        <div class="progress mb-0">
                            <div class="progress-bar bg-info" style="width: ${((stats.sleeping/totalResources)*100).toFixed(0)}%" role="progressbar" aria-valuenow="${stats.sleeping}" aria-valuemin="0" aria-valuemax="${totalResources}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

                // Create table
                // Create table with subservice filter buttons next to search
                // Create table with subservice filter buttons next to search with a balanced layout
                // Create table with subservice filter buttons next to search with a balanced layout
                let tableHtml = `
<div class="table-responsive card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center mb-2">
            <!-- Search input with minimum width -->
            <div class="input-icon me-3" style="min-width: 250px; width: 25%;">
                <input type="text" class="form-control" id="resourceSearchInput" placeholder="Search...">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path>
                        <path d="M21 21l-6 -6"></path>
                    </svg>
                </span>
            </div>
            
            <!-- Subservice filter buttons with horizontal scrolling when needed -->
            <div class="flex-grow-1 subservice-buttons-container" style="overflow-x: auto; white-space: nowrap; padding-bottom: 5px;">
                <div class="subservice-filter-buttons">
                    <button class="btn btn-sm btn-outline-secondary subservice-filter me-1 mb-1 active" data-filter="all">All</button>
                    ${[...new Set(data.map(item => item.subservice))].map(subservice => 
                        `<button class="btn btn-sm btn-outline-primary subservice-filter me-1 mb-1" data-filter="${subservice}">${subservice}</button>`
                    ).join('')}
                </div>
            </div>
        </div>
    </div>
    <table class="table table-vcenter card-table" id="resourcesTable">
        <!-- Table headers remain the same -->
        <thead>
            <tr>
                <th>Name</th>
                <th>Subservice</th>
                <th>Active Left</th>
                <th>Namespace</th>
                <th>Type</th>
                <th>Status</th>
                <th>Replica</th>
                <th>Health</th>
                <th>Ready</th>
            </tr>
        </thead>
        <tbody>`;

                data.forEach(function(item) {
                    // Get the health status
                    const healthStatus = getHealthStatus(item.ready);
                    const healthClass = getHealthClass(healthStatus);
                    const statusClass = getStatusClass(item.status);

                    // Construct the URL for the subservice
                    const subserviceUrl =
                        `${window.location.origin}/admin/resources/${encodeURIComponent(item.subservice)}`;

                    tableHtml += `
                <tr class="resource-row" 
                    data-status="${item.status ? item.status.toLowerCase() : ''}" 
                    data-health="${healthStatus}"
                    data-subservice="${item.subservice}">
                    <td class="font-weight-bold">${item.name}</td>
                    <td>
                        <a href="${subserviceUrl}" target="_blank" class="text-primary">${item.subservice}</a>
                    </td>
                    <td>
                        <span class="badge ${item.active_left === 'No Auto Sleep' ? 'bg-secondary' : 'bg-primary'}">${item.active_left}</span>
                    </td>
                    <td>${item.namespace}</td>
                    <td>
                        <span class="badge badge-sm bg-blue-lt">
                            ${item.type}
                        </span>
                    </td>
                    <td>
                        <span class="badge ${statusClass}">${item.status}</span>
                    </td>
                    <td>${item.replica}</td>
                    <td>
                        <span class="badge ${healthClass}">${capitalizeFirstLetter(healthStatus)}</span>
                    </td>
                    <td>${formatReadyRatio(item.ready)}</td>
                </tr>`;
                });

                tableHtml += `
                </tbody>
            </table>
        </div>`;

                $('#modal-body').html(summaryHtml + tableHtml);

                // Initialize search functionality
                $('#resourceSearchInput').on('keyup', function() {
                    const value = $(this).val().toLowerCase();
                    $("#resourcesTable tbody tr").filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });

                // Initialize filter functionality
                $('.resource-filter').on('click', function(e) {
                    e.preventDefault();
                    const filter = $(this).data('filter');

                    if (filter === 'all') {
                        $('.resource-row').show();
                    } else if (filter === 'healthy' || filter === 'unhealthy') {
                        $('.resource-row').hide();
                        $(`.resource-row[data-health="${filter}"]`).show();
                    } else {
                        $('.resource-row').hide();
                        $(`.resource-row[data-status="${filter}"]`).show();
                    }
                });

                // Initialize subservice filter
                // Initialize subservice filter buttons
                $('.subservice-filter').on('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all buttons
                    $('.subservice-filter').removeClass('active');

                    // Add active class to the clicked button
                    $(this).addClass('active');

                    const filter = $(this).data('filter');

                    if (filter === 'all') {
                        $('.resource-row').show();
                    } else {
                        $('.resource-row').hide();
                        $(`.resource-row[data-subservice="${filter}"]`).show();
                    }
                });
            }

            // Calculate stats including unactive (0/0) as a separate category
            function calculateStats(data) {
                const stats = {
                    healthy: 0,
                    unhealthy: 0,
                    unactive: 0,
                    running: 0,
                    sleeping: 0,
                    activeTotal: 0
                };

                data.forEach(item => {
                    // Check health based on replica ratio
                    const healthStatus = getHealthStatus(item.ready);

                    if (healthStatus === 'healthy') {
                        stats.healthy++;
                        stats.activeTotal++;
                    } else if (healthStatus === 'unhealthy') {
                        stats.unhealthy++;
                        stats.activeTotal++;
                    } else if (healthStatus === 'unactive') {
                        stats.unactive++;
                        // Don't increment activeTotal for unactive
                    }

                    // Check status
                    if (item.status) {
                        const status = item.status.toLowerCase();
                        if (status === 'running') {
                            stats.running++;
                        } else if (status === 'sleeping') {
                            stats.sleeping++;
                        }
                    }
                });

                return stats;
            }

            // Return 'healthy', 'unhealthy', or 'unactive'
            function getHealthStatus(ready) {
                if (!ready) return 'unactive';

                const parts = ready.split('/');
                if (parts.length !== 2) return 'unactive';

                const actual = parseInt(parts[0]);
                const desired = parseInt(parts[1]);

                if (actual === 0 && desired === 0) {
                    return 'unactive';
                } else if (actual === desired) {
                    return 'healthy';
                } else {
                    return 'unhealthy';
                }
            }

            function getHealthClass(status) {
                switch (status) {
                    case 'healthy':
                        return 'bg-success';
                    case 'unhealthy':
                        return 'bg-danger';
                    case 'unactive':
                        return 'bg-secondary';
                    default:
                        return 'bg-secondary';
                }
            }

            function getStatusClass(status) {
                if (!status) return 'bg-secondary';

                switch (status.toLowerCase()) {
                    case 'running':
                        return 'bg-green';
                    case 'sleeping':
                        return 'bg-azure';
                    case 'stopped':
                        return 'bg-red';
                    case 'disabled':
                        return 'bg-secondary';
                    default:
                        return 'bg-secondary';
                }
            }

            function formatReadyRatio(ready) {
                if (!ready) return '0/0';

                const parts = ready.split('/');
                if (parts.length !== 2) return ready;

                const actual = parseInt(parts[0]);
                const desired = parseInt(parts[1]);

                if (actual === 0 && desired === 0) {
                    // Unactive
                    return `<span class="text-secondary">${ready}</span>`;
                } else if (actual === desired) {
                    // Healthy
                    return `<span class="text-success">${ready}</span>`;
                } else if (actual === 0) {
                    // Completely unhealthy
                    return `<span class="text-danger">${ready}</span>`;
                } else {
                    // Partially healthy
                    return `<span class="text-warning">${ready}</span>`;
                }
            }

            function calculatePercentage(value, total) {
                if (total === 0) return 0;
                return ((value / total) * 100).toFixed(0);
            }

            function capitalizeFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }
        });
    </script>
    {{-- CRUD LIST CONTENT - crud_list_scripts stack --}}
    @stack('crud_list_scripts')
@endsection
