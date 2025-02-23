<a onclick="noty_refreshSubFromRes(event)" class="btn btn-info me-1" href="#" data-toggle="tooltip" title="Refresh Project"><i class="la la-refresh me-1"></i>Hard Refresh</a>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function noty_refreshSubFromRes(event) {
        event.preventDefault();

        const url = window.location.href.split('#')[0]; // Remove fragment
        const name = url.substring(url.lastIndexOf('/') + 1);

        console.log(name);

        Swal.fire({
            title: "Are You Sure Want To Hard Refresh All?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#6c757d",
            cancelButtonText: "Cancel",
            confirmButtonText: "Confirm"
        }).then(function(result) {
            if (result.value) {
                Swal.fire({
                    title: "Hard Refreshing All",
                    text: "Please Wait....",
                    allowOutsideClick: false,
                    icon: 'info',
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                        $.ajax({
                            type: "GET",
                            url: "/admin/subservice/refresh/" + name,
                            success: function(response) {
                                console.log("Success response:", response);
                                if (response === "success") {
                                    Swal.fire(
                                        "Success!",
                                        "The Subservice Is Refreshed",
                                        "success"
                                    ).then(function() {
                                    location.reload();
                                });
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
