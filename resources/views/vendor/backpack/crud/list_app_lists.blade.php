{{-- resources/views/vendor/backpack/crud/list_app_lists.blade.php --}}
@extends(backpack_view('blank'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header sticky-header">
                <div class="d-flex justify-content-between align-items-center">
                    {{-- Left side: Title and Current Location --}}
                    <div class="d-flex align-items-center flex-grow-1">
                        <h3 class="card-title mb-0">Project Structure</h3>
                        <div class="current-location ml-3">
                            <span class="text-muted">Current Location:</span>
                            <span id="currentProject" class="text-warning ml-2"></span>
                            <span id="currentSubservice" class="text-primary"></span>
                        </div>
                    </div>
                    
                    {{-- Right side: Legend --}}
                    <div class="legend-box ml-auto d-flex">
                        <div class="d-flex align-items-center mr-3">
                            <i class="las la-folder text-warning mr-1"></i>
                            <span>Project</span>
                        </div>
                        <div class="d-flex align-items-center mr-3">
                            <i class="las la-folder text-primary mr-1"></i>
                            <span>Subservice</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="las la-link text-secondary mr-1"></i>
                            <span>Resource</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body position-relative">
                <div x-data="{ expanded: new Set() }" class="tree-structure">
                    @foreach($projects as $projectIndex => $project)
                    <div class="tree-item" data-project="{{ $project->name }}">
                        <div class="d-flex align-items-center py-2 project-row" 
                             x-data="{ id: 'project-{{ $project->id }}' }">
                            {{-- Expand/Collapse icon --}}
                            <div @click="expanded.has(id) ? expanded.delete(id) : expanded.add(id)" 
                                 class="expand-icon">
                                <i :class="expanded.has(id) ? 'la-chevron-down' : 'la-chevron-right'" 
                                   class="las text-dark"></i>
                            </div>
                            {{-- Project icon and link --}}
                            <a href="{{ env('APP_URL') }}/admin/subservice/{{ $project->name }}" 
                               target="_blank"
                               class="flex-grow-1 d-flex align-items-center text-decoration-none">
                                <i class="las la-folder text-warning mr-2"></i>
                                <span class="font-weight-bold text-dark">{{ $project->name }}</span>
                            </a>
                        </div>
                    
                        <div x-show="expanded.has('project-{{ $project->id }}')"
                             class="tree-branch">
                            @if($project->subservices->count() > 0)
                                @foreach($project->subservices as $subservice)
                                    <div class="tree-item">
                                        <div class="subservice-group" data-subservice="{{ $subservice->name }}">
                                            <div class="d-flex align-items-center py-2 subservice-row"
                                                 x-data="{ id: 'service-{{ $subservice->id }}' }">
                                                {{-- Expand/Collapse icon --}}
                                                <div @click="expanded.has(id) ? expanded.delete(id) : expanded.add(id)"
                                                     class="expand-icon">
                                                    <i :class="expanded.has(id) ? 'la-chevron-down' : 'la-chevron-right'" 
                                                       class="las text-dark"></i>
                                                </div>
                                                {{-- Subservice icon and link --}}
                                                <a href="{{ env('APP_URL') }}/admin/resources/{{ $subservice->name }}" 
                                                   target="_blank"
                                                   class="flex-grow-1 d-flex align-items-center text-decoration-none">
                                                    <i class="las la-folder text-primary mr-2"></i>
                                                    <span class="text-dark">{{ $subservice->name }}</span>
                                                </a>
                                            </div>
                        
                                            <div x-show="expanded.has('service-{{ $subservice->id }}')"
                                                 class="tree-branch">
                                                @if($subservice->resources->count() > 0)
                                                    @foreach($subservice->resources as $resource)
                                                        <div class="d-flex align-items-center py-2 resource-row">
                                                            <span class="expand-icon"></span>
                                                            <i class="las la-link text-secondary mr-2"></i>
                                                            <span class="text-dark">{{ $resource->name }}</span>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="d-flex align-items-center py-2 resource-row">
                                                        <span class="expand-icon"></span>
                                                        <i class="las la-exclamation-circle text-warning mr-2"></i>
                                                        <span class="text-muted">Is resources missing? Check the tagging properly!</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="d-flex align-items-center py-2 subservice-row">
                                    <span class="expand-icon"></span>
                                    <i class="las la-exclamation-circle text-warning mr-2"></i>
                                    <span class="text-muted">Is Subservice missing? Maybe All the Subservice on Excluded Lists.</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const projectEl = entry.target.closest('[data-project]');
                const subserviceEl = entry.target.closest('[data-subservice]');
                
                if (projectEl) {
                    document.getElementById('currentProject').textContent = projectEl.dataset.project;
                }
                if (subserviceEl) {
                    document.getElementById('currentSubservice').textContent = 
                        ' / ' + subserviceEl.dataset.subservice;
                } else {
                    document.getElementById('currentSubservice').textContent = '';
                }
            }
        });
    }, {
        threshold: 0.5,
        rootMargin: '-100px 0px -50% 0px'
    });

    // Observe all project and subservice elements
    document.querySelectorAll('[data-project], [data-subservice]').forEach(el => {
        observer.observe(el);
    });
});
</script>
@endpush

@push('after_styles')
<style>
.expand-icon {
    width: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
}

.project-row,
.subservice-row,
.resource-row {
    display: flex;
    align-items: center;
    padding: 6px 12px;
}

.project-row a,
.subservice-row a {
    display: flex;
    align-items: center;
}

.d-flex.justify-content-between {
    width: 100%;
}

.current-location {
    white-space: nowrap;
    font-size: 14px;
    padding: 4px 12px;
    background-color: #f8f9fa;
    border-radius: 4px;
}
/* Your existing styles */
.tree-structure {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    font-size: 16px;
    line-height: 1.6;
    padding: 1rem;
}

.tree-item {
    margin: 6px 0;
}

.tree-item i {
    width: 28px;
    font-size: 20px;
    text-align: center;
}

/* Project Row Styling */
.project-row {
    border-radius: 6px;
    padding: 6px 12px;
    transition: all 0.2s ease;
    cursor: pointer;
    border-bottom: 2px solid #ffeeba;
    margin-bottom: 8px;
}

.project-row:hover {
    background-color: #f8f9fa;
}

/* Subservice Group Styling */
.subservice-group {
    border-radius: 6px;
    transition: all 0.2s ease;
}

.subservice-group:hover > .subservice-row,
.subservice-group:hover > .tree-branch > .resource-row {
    background-color: #f8f9fa;
}

/* Subservice Row Styling */
.subservice-row {
    border-radius: 6px;
    padding: 6px 12px;
    transition: all 0.2s ease;
    cursor: pointer;
}

/* Resource Row Styling */
.resource-row {
    border-radius: 6px;
    padding: 6px 12px;
    transition: all 0.2s ease;
}

/* Vertical lines for hierarchy */
.tree-branch {
    border-left: 2px solid #dee2e6;
    margin-left: 14px;
    padding-left: 28px;
}

.sticky-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: white;
    border-bottom: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    padding: 1rem;
}

/* Legend Box (now in header) */
.legend-box {
    padding: 0.5rem;
    font-size: 14px;
    white-space: nowrap; /* Prevent wrapping */
    margin-left: 2rem; /* Add some space between location and legend */
}

.legend-box i {
    font-size: 18px;
    width: 20px;
    text-align: center;
}

/* Colors */
.text-warning {
    color: #ffc107 !important;
}

.text-primary {
    color: #4a6ee0 !important;
}

.text-secondary {
    color: #616876 !important;
}

.text-dark {
    color: #2d3748 !important;
}

/* Text weight */
.tree-item span {
    font-weight: 500;
}

/* Icon sizes */
.las {
    font-size: 1.3em;
}

.resource-row .text-muted {
    font-style: italic;
}
</style>
@endpush