<a onclick="noty_sleepSubfromRes(event)" class="btn btn-secondary me-1" href="#" data-toggle="tooltip" title="Sleep All"><i class="la la-bed me-1"></i> Sleep All</a>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function noty_sleepSubfromRes(event) {
        event.preventDefault();

        console.log(window.location.href);
        console.log("hello");

        const url = window.location.href.split('#')[0]; // Remove fragment
        const name = url.substring(url.lastIndexOf('/') + 1);

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
