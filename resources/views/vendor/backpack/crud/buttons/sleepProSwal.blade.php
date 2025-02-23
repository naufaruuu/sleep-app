<a onclick="noty_sleepPro(event, {{ $entry }})" class="btn btn-sm btn-link" href="#" data-toggle="tooltip"
    title="Sleep Project"><i class="la la-bed"></i> Sleep</a>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function noty_sleepPro(event, entry) {
        event.preventDefault();
        const name = entry.name
        console.log(name);
        // Show loading indicator
        Swal.fire({
            title: "Sleeping This Project",
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            icon: 'info',
            text: 'Please Wait....',
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            type: "GET",
            url: "/admin/project/sleep/" + name,
            success: function(response) {
                console.log("Success response:", response);
                if (response == 'success') {
                    Swal.fire(
                        "Success!",
                        "The subservices on this project is on sleep!",
                        "success"
                    )
                } else {
                    Swal.fire({
                        title: "Failed!",
                        text: "Response: " + response,
                        icon: "error"
                    });
                }
            },
            error: function(status, error) {
                console.error("Error:", error);
                Swal.fire({
                    title: "Failed!",
                    text: "Error: " + error,
                    icon: "error"
                });
            }
        });

    }
</script>
