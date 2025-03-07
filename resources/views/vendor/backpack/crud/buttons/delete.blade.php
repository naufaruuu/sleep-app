@if ($crud->hasAccess('delete', $entry))
    <a href="javascript:void(0)" onclick="deleteEntry(this)" data-route="{{ url($crud->route . '/' . $entry->getKey()) }}"
        class="btn btn-sm btn-link" data-button-type="delete">
        <span><i class="la la-trash"></i> {{ trans('backpack::crud.delete') }}</span>
    </a>
@endif

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts') @if (request()->ajax())
@endpush
@endif
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$('document').ready(function(){});
    if (typeof deleteEntry != 'function') {
        $("[data-button-type=delete]").unbind('click');

        function deleteEntry(button) {
            // ask for confirmation before deleting an item
            // e.preventDefault();
            var route = $(button).attr('data-route');

            Swal.fire({
                title: "{!! trans('backpack::base.warning') !!}",
                text: "{!! trans('backpack::crud.delete_confirm') !!}",
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                cancelButtonColor: '#6c757d', // Bootstrap secondary color
                confirmButtonText: 'Confirm',
                confirmButtonColor: '#28a745', // Bootstrap success color
                dangerMode: true,
            }).then((value) => {
                if (value.isConfirmed) {
                    $.ajax({
                        url: route,
                        type: 'DELETE',
                        success: function(result) {
                            if (result == 1) {
                                // Redraw the table
                                if (typeof crud != 'undefined' && typeof crud.table !=
                                    'undefined') {
                                    // Move to previous page in case of deleting the only item in table
                                    if (crud.table.rows().count() === 1) {
                                        crud.table.page("previous");
                                    }

                                    crud.table.draw(false);
                                }

                                // Show a success notification bubble
                                new Noty({
                                    type: "success",
                                    text: "{!! '<strong>' .
                                        trans('backpack::crud.delete_confirmation_title') .
                                        '</strong><br>' .
                                        trans('backpack::crud.delete_confirmation_message') !!}"
                                }).show();

                                // Hide the modal, if any
                                $('.modal').modal('hide');
                            } else {
                                // if the result is an array, it means 
                                // we have notification bubbles to show
                                if (result instanceof Object) {
                                    // trigger one or more bubble notifications 
                                    Object.entries(result).forEach(function(entry, index) {
                                        var type = entry[0];
                                        entry[1].forEach(function(message, i) {
                                            new Noty({
                                                type: type,
                                                text: message
                                            }).show();
                                        });
                                    });
                                } else { // Show an error alert
                                    Swal.fire({
                                        title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                                        text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
                                        icon: "error",
                                        timer: 4000,
                                        buttons: false,
                                    });
                                }
                            }
                        },
                        error: function(result) {
                            // Show an alert with the result
                            Swal.fire({
                                title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                                text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                        }
                    });
                }
            });

        }
    }

    // make it so that the function above is run after each DataTable draw event
    // crud.addFunctionToDataTablesDrawEventQueue('deleteEntry');
</script>
@if (!request()->ajax())
@endpush
@endif
