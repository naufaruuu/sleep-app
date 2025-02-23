<a onclick="noty_sleepSub(event, {{ $entry }})" class="btn btn-sm btn-link" href="#" data-toggle="tooltip"
    title="Sleep Subservcice"><i class="la la-bed"></i> Sleep</a>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function noty_sleepSub(event, entry) {
        event.preventDefault();
        const name = entry.name
        console.log(name);
        // Show loading indicator
        Swal.fire({
            title: "Sleeping This Subservice",
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
            url: "/admin/subservice/sleep/" + name,
            success: function(response) {
                console.log("Success response:", response);
                if (response == 'success') {
                    Swal.fire(
                        "Success!",
                        "The subservice is on sleep!",
                        "success"
                    ).then(function() {
                        location.reload();
                    });
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
