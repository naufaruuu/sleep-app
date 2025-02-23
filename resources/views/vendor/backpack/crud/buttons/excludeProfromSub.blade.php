<a onclick="noty_excludeProfromSub(event)" class="btn btn-danger me-1" href="#" data-toggle="tooltip"
    title="Exclude Project"><i class="la la-ban me-1"></i> Exclude All</a>

<script>
    function noty_excludeProfromSub(event) {
        event.preventDefault();

        const url = window.location.href.split('#')[0]; // Remove fragment
        const name = url.substring(url.lastIndexOf('/') + 1);

        console.log(name);

        Swal.fire({
            title: 'Are You Sure Want To Exclude All?',
            icon: "question",
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            cancelButtonColor: '#6c757d', // Bootstrap secondary color
            confirmButtonText: 'Confirm',
            confirmButtonColor: '#28a745', // Bootstrap success color
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Excluding All',
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
                    url: '/admin/project/exclude/' + name,
                    success: function(response) {
                        console.log('Success response:', response);
                        if (response === 'success') {
                            Swal.fire(
                                'Success!',
                                'The Project Is Excluded',
                                'success'
                            ).then(() => {
                                window.location.href = "{{ env('APP_URL') }}/admin/projects";
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
