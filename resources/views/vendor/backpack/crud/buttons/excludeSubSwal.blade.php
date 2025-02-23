
<a onclick="noty_excludeSub(event, {{ $entry }})" class="btn btn-sm btn-link" href="#" data-toggle="tooltip"
    title="Exclude Project"><i class="la la-ban"></i> Exclude</a>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function noty_excludeSub(event, entry) {
        event.preventDefault();

        const name = entry.name

        Swal.fire({
            title: 'Are You Sure Want To Exclude This Subservice?',
            icon: "question",
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            cancelButtonColor: '#6c757d', // Bootstrap secondary color
            confirmButtonText: 'Confirm',
            confirmButtonColor: '#28a745', // Bootstrap success color
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Excluding This Subservice',
                    icon: 'info',
                    text: 'Please Wait....',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    type: 'GET',
                    url: '/admin/subservice/exclude/' + name,
                    success: function(response) {
                        console.log('Success response:', response);
                        if (response === 'success') {
                            Swal.fire(
                                'Success!',
                                'The Subservice Is Excluded',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Failed',
                                'Server returned unsuccessful response!',
                                'error'
                            );
                        }
                    },
                    error: function(status, error) {
                        console.error('Error:', error);
                        Swal.fire(
                            'Failed',
                            'Error occurred while communicating with the server!',
                            'error'
                        );
                    }
                });
            }
        });

    }
</script>
