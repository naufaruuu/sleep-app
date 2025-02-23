<a onclick="noty_refreshAll(event)" class="btn btn-info me-1" href="#" data-toggle="tooltip" title="Refresh All"><i
        class="la la-refresh me-1"></i> Refresh All Resources</a>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function noty_refreshAll(event) {
        event.preventDefault();
        Swal.fire({
            title: "Are You Sure Want To Refresh All Resources?",
            // text: 'This can take a long time to process',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Refreshing All Resources",
                    text: 'Please Wait, This Can Be Very Long....',
                    icon: 'info',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    type: "GET",
                    url: "/admin/project/refreshAll/",
                    success: function(response) {
                        console.log("Success response:", response);
                        if (response == 'success') {
                            Swal.fire(
                                "Success!",
                                "The Project Is Refreshed",
                                "success"
                            ).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Failed",
                                text: "Server returned an unsuccessful response!",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", error);
                        Swal.fire({
                            title: "Failed",
                            text: "Error occurred while communicating with the server!",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }
        });
    }
</script>
