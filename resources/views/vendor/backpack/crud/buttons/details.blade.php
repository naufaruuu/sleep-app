@if ($crud->hasAccess('details'))
  <a href="{{ url($crud->route.'/'.$entry->getKey().'/details') }}" class="btn btn-sm btn-link text-capitalize"><i class="la la-question"></i> details</a>
@endif