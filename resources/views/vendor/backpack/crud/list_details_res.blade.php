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

        <!-- Updated Modal Structure with Tabs -->
        <div class="modal" id="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-title">Resource Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-body">
                        <!-- Message for when no pods are found -->
                        <div id="no-pods-message" style="display: none;">
                            <p>No pods found for this resource</p>
                        </div>

                        <!-- Dynamic tabs will be populated here -->
                        <ul class="nav nav-tabs" id="podTabs" role="tablist">
                            <!-- Pod tabs will be dynamically inserted here -->
                        </ul>

                        <!-- Tab content -->
                        <div class="tab-content mt-3" id="podTabContent">
                            <!-- Pod content panes will be dynamically inserted here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="refresh-data">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                                <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
                            </svg>
                            Refresh
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        .select2-drop-active {
            border: none;
        }

        .dark-background {
            background-color: #222 !important;
            color: #fff !important;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #fff #333;
        }

        /* Style for WebKit browsers (Chrome, Safari) */
        .dark-background::-webkit-scrollbar {
            width: 8px;
        }

        .dark-background::-webkit-scrollbar-track {
            background: #333;
        }

        .dark-background::-webkit-scrollbar-thumb {
            background-color: #fff;
            border-radius: 4px;
        }

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

        table.dataTable td,
        table.dataTable th {
            -webkit-box-sizing: border-box !important;
            box-sizing: border-box !important;
        }

        #crudTable tbody tr {
            cursor: pointer;
        }

        .modal-dialog {
            max-width: 90%;
        }

        .modal-body pre {
            font-size: 15px;
        }
    </style>

    {{-- CRUD LIST CONTENT - crud_list_styles stack --}}
    @stack('crud_list_styles')
@endsection

@section('after_scripts')
    @include('crud::inc.datatables_logic')
    <script>
        $(document).ready(function() {
            let currentResourceName = '';
            let currentResourceType = '';
            let podNames = [];
            let defaultTailLines = 20;
            let unlimitedLogs = false;

            function renderClick() {
                let table = $('#crudTable').DataTable();

                function cleanName(data) {
                    const temp = document.createElement('div');
                    temp.innerHTML = data;
                    return temp.textContent.trim();
                }

                table.on('click', 'tbody tr', function(e) {
                    if (e.target.nodeName == 'A' || e.target.parentNode.nodeName == 'A') {
                        return true;
                    }

                    var data = table.row(e.target.closest('tr')).data();
                    var name = cleanName(data[0]);

                    $('#modal-title').html(name);
                    currentResourceName = name;
                    unlimitedLogs = false;

                    // Reset content and hide all pod tabs initially
                    resetModalContent();

                    // Load pods and description
                    loadPods(name);
                    loadResourceDescription(name);

                    // Show the modal
                    $('#modal').appendTo("body").modal('show');
                });
            }

            function resetModalContent() {
                // Hide all pod tabs initially
                $('#podTabs').empty();
                $('#podTabContent').empty();

                // Add description tab (always present)
                $('#podTabs').append(`
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="description-tab" data-bs-toggle="tab" 
                            data-bs-target="#resource-description" type="button" role="tab" 
                            aria-controls="resource-description" aria-selected="false">
                        Description
                    </button>
                </li>
            `);

                $('#podTabContent').append(`
                <div class="tab-pane fade" id="resource-description" role="tabpanel" 
                    aria-labelledby="description-tab">
                    <div id="resource-description-content">
                        <p>Loading description...</p>
                    </div>
                </div>
            `);
            }

            function loadResourceDescription(name) {
                $.ajax({
                    type: "POST",
                    url: "/admin/resource/getDescribe",
                    data: {
                        name: name
                    },
                    success: function(response) {
                        if (response) {
                            $('#resource-description-content').html('<pre class="dark-background">' +
                                response + '</pre>');
                            setMaxHeight();
                        } else {
                            $('#resource-description-content').html('<p>No description available</p>');
                        }
                    },
                    error: function(status, error) {
                        console.error("Error loading description:", error);
                        $('#resource-description-content').html('<p>Error loading description: ' +
                            error + '</p>');
                    }
                });
            }

            function loadPods(name) {
                $.ajax({
                    type: "POST",
                    url: "/admin/resource/getPods",
                    data: {
                        name: name
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data && data.pods && data.pods.length > 0) {
                                podNames = data.pods;
                                currentResourceType = data.resourceType;
                                defaultTailLines = data.defaultTailLines || 20;

                                // Create tabs for each pod
                                podNames.forEach((podName, index) => {
                                    // Create tab
                                    const isActive = index === 0 ? 'active' : '';
                                    const tabButton = `
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link ${isActive}" id="${podName}-tab" 
                                                data-bs-toggle="tab" data-bs-target="#${podName}-logs" 
                                                type="button" role="tab" aria-controls="${podName}-logs" 
                                                aria-selected="${index === 0 ? 'true' : 'false'}">
                                            ${podName} Logs
                                        </button>
                                    </li>
                                `;

                                    // Prepend pod tabs (so they appear before description)
                                    $('#podTabs').prepend(tabButton);

                                    // Create tab content with logs limit toggle
                                    const tabContent = `
                                    <div class="tab-pane fade ${index === 0 ? 'show active' : ''}" 
                                        id="${podName}-logs" role="tabpanel" aria-labelledby="${podName}-tab">
                                        <div class="mb-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input toggle-unlimited-logs" type="checkbox" 
                                                    id="unlimited-logs-${podName}" data-pod="${podName}">
                                                <label class="form-check-label" for="unlimited-logs-${podName}">
                                                    Show all logs (may be slow for large logs)
                                                </label>
                                            </div>
                                        </div>
                                        <div id="${podName}-logs-content">
                                            <p>Loading logs for ${podName}...</p>
                                        </div>
                                    </div>
                                `;

                                    $('#podTabContent').prepend(tabContent);

                                    // Load logs for this pod
                                    loadPodLogs(podName);
                                });

                                // Set up event listeners for unlimited logs toggle
                                $('.toggle-unlimited-logs').on('change', function() {
                                    const podName = $(this).data('pod');
                                    const isUnlimited = $(this).prop('checked');

                                    $(`#${podName}-logs-content`).html(
                                        '<p>Reloading logs...</p>');
                                    loadPodLogs(podName, isUnlimited);
                                });
                            } else {
                                // No pods found - only show description tab
                                $('#description-tab').addClass('active').attr('aria-selected', 'true');
                                $('#resource-description').addClass('show active');
                                $('#no-pods-message').show();
                            }
                        } catch (e) {
                            console.error("Error parsing pods response:", e);
                            $('#no-pods-message').html(
                                '<p>Error loading pods: Invalid response format</p>').show();
                            $('#description-tab').addClass('active').attr('aria-selected', 'true');
                            $('#resource-description').addClass('show active');
                        }
                    },
                    error: function(status, error) {
                        console.error("Error loading pods:", error);
                        $('#no-pods-message').html('<p>Error loading pods: ' + error + '</p>').show();
                        $('#description-tab').addClass('active').attr('aria-selected', 'true');
                        $('#resource-description').addClass('show active');
                    }
                });
            }

            function loadPodLogs(podName, isUnlimited = false) {
                $.ajax({
                    type: "POST",
                    url: "/admin/resource/getLogs",
                    data: {
                        name: currentResourceName,
                        podName: podName,
                        tailLines: isUnlimited ? -1 : defaultTailLines
                    },
                    success: function(response) {
                        if (response) {
                            $(`#${podName}-logs-content`).html('<pre class="dark-background">' +
                                response + '</pre>');
                            setMaxHeight();
                        } else {
                            $(`#${podName}-logs-content`).html('<p>No logs available for this pod</p>');
                        }
                    },
                    error: function(status, error) {
                        console.error(`Error loading logs for pod ${podName}:`, error);
                        $(`#${podName}-logs-content`).html('<p>Error loading logs: ' + error + '</p>');
                    }
                });
            }

            function setMaxHeight() {
                var windowHeight = $(window).height();
                var maxHeight = Math.round(windowHeight * 0.6);
                $('.dark-background').css({
                    'max-height': maxHeight + 'px',
                    'overflow-y': 'auto'
                });
            }

            // Handle refresh button click
            $(document).on('click', '#refresh-data', function() {
                if (currentResourceName) {
                    // Find the active tab
                    const activeTabId = $('.tab-pane.active').attr('id');

                    if (activeTabId === 'resource-description') {
                        $('#resource-description-content').html('<p>Refreshing description...</p>');
                        loadResourceDescription(currentResourceName);
                    } else {
                        // For pod logs, extract pod name from tab id (format: "podName-logs")
                        const podName = activeTabId.replace('-logs', '');
                        const isUnlimited = $(`#unlimited-logs-${podName}`).prop('checked');

                        $(`#${podName}-logs-content`).html('<p>Refreshing logs...</p>');
                        loadPodLogs(podName, isUnlimited);
                    }
                }
            });

            // Update max height when window is resized
            $(window).resize(function() {
                setMaxHeight();
            });

            renderClick();
        });
    </script>
    {{-- CRUD LIST CONTENT - crud_list_scripts stack --}}
    @stack('crud_list_scripts')
@endsection
