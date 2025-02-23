<a onclick="noty_activateSubfromRes(event)" class="btn btn-success me-1" href="#" data-toggle="tooltip" title="Activate All"><i class="la la-play me-1"></i> Activate All</a>

<style>
    .input-label {
        display: block;
        margin-bottom: 5px;
    }

    .input-wrapper {
        margin-bottom: 15px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function noty_activateSubfromRes(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Input Activate Time',
            html: '<div class="input-wrapper">' +
                '<label class="input-label">Until What Time? 24hr Format (1-24)</label>' +
                '<input id="swal-input1" class="swal2-input" type="number">' +
                '</div>' +
                '<div class="input-wrapper">' +
                '<label class="input-label">Or, How Many Hours Do You Want?</label>' +
                '<input id="swal-input2" class="swal2-input" type="number">' +
                '</div>',
            focusConfirm: false,
            allowEnterKey: true,
            preConfirm: () => {
                const input1 = document.getElementById('swal-input1').value;
                const input2 = document.getElementById('swal-input2').value;

                if ((!input1 && !input2) || (input1 && input2)) {
                    Swal.showValidationMessage('Error: Must input in A or B');
                    return false;
                } else {
                    let hour = 0;
                    if (input1) {
                        if (Number(input1) < 1 || Number(input1) > 24) {
                            Swal.showValidationMessage('Error: The Hour Must Be On 24H Format (1-24)');
                            return false;
                        } else {
                            const currentTime = new Date();
                            let currentHour = currentTime.getHours();
                            // currentHour += 7;
                            let input = Number(input1);
                            let day = "Today"
                            if (Number(input1) < currentHour) {
                                input += 24;
                                day = "Tomorrow";
                            }

                            hour = input - currentHour;

                            if (hour == 0) {
                                Swal.showValidationMessage('Error: Time Cannot Be Same As Current Time (' +
                                    currentHour + ')!');
                                return false;

                            } else {
                                Swal.fire({
                                    title: "Activating The Project Until " + input1.toString() +
                                        ":00 " +
                                        day +
                                        " (" + hour + " Hours)",
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    icon: 'info',
                                    text: 'Please Wait....',
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                            }
                        }
                    } else {
                        hour = Number(input2);
                        Swal.fire({
                            title: "Activating The Project For " + hour.toString() + " Hours",
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            text: 'Please Wait....',
                            icon: 'info',
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    }

                    const url = window.location.href.split('#')[0]; // Remove fragment
                    const name = url.substring(url.lastIndexOf('/') + 1);

                    console.log(name);

                    $.ajax({
                        type: "GET",
                        url: "/admin/project/activate/" + name,
                        data: {
                            hour: hour,
                        },
                        success: function(response) {
                            console.log("Success response:", response);
                            if (response == 'success') {
                                Swal.fire({
                                    title: "Succes!",
                                    text: "All subservices are activated for " +
                                        hour
                                        .toString() + " Hours!",
                                    icon: "success"
                                }).then(function() {
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
                            Swal.fire({
                                title: "Failed!",
                                text: "Error: " + error,
                                icon: "error"
                            });
                        }
                    });
                }
            }
        })
        // .then((result) => {
        //     if (result.value[0]) {
        //         // Swal.showValidationMessage('Inputted A: ' + result.value[0] + '');
        //         // return false;

        //     } else {


        //     }
        // });
    }
</script>
