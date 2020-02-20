import 'bootstrap/js/src/alert';
import 'bootstrap/js/src/popover';
import Swal from 'sweetalert2'

document.addEventListener('DOMContentLoaded', function() {

    document.addEventListener('submit', function (event) {
        if (!event.target.matches('.delete-form')) {
            return;
        }

        event.preventDefault();

        const form = event.target;

        console.log(form);

        Swal.fire({
                title: "Are you sure?",
                text: "This entry will be deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.value) {
                    form.submit();
                }
            });
    }, false);

    $('[data-toggle="popover"]').popover();

}, false);