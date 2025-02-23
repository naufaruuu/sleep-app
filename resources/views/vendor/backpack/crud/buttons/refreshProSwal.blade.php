<a onclick="noty_refreshPro(event, {{ $entry }})" class="btn btn-sm btn-link" href="#" data-toggle="tooltip"
    title="Refresh Project"><i class="la la-refresh"></i> Refresh</a>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function noty_refreshPro(event, entry) {
        event.preventDefault();

        const name = entry.name

        Swal.fire({
            title: "Are You Sure Want To Hard Refresh This Project?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#6c757d",
            cancelButtonText: "Cancel",
            confirmButtonText: "Confirm"
        }).then(function(result) {
            if (result.value) {
                Swal.fire({
                    title: "Hard Refreshing This Project",
                    text: "Please Wait....",
                    allowOutsideClick: false,
                    icon: 'info',
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                        $.ajax({
                            type: "GET",
                            url: "/admin/project/refresh/" + name,
                            success: function(response) {
                                console.log("Success response:", response);
                                if (response === "success") {
                                    Swal.fire(
                                        "Success!",
                                        "The Project Is Refreshed",
                                        "success"
                                    );
                                } else {
                                    Swal.fire(
                                        "Failed",
                                        "Server returned unsuccessful response!",
                                        "error"
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error:", error);
                                Swal.fire(
                                    "Failed",
                                    "Error occurred while communicating with the server!",
                                    "error"
                                );
                            }
                        });
                    }
                });
            }
        });

    }
</script>
