@if ($crud->hasAccess('sleep'))
  <a href="{{ url($crud->route.'/'.$entry->getKey().'/sleep') }}" class="btn btn-sm btn-link text-capitalize"><i class="la la-question"></i> sleep</a>
@endif