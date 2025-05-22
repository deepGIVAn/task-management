<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script>
    var notyf = new Notyf({
        duration: 3000,
        position: {
            x: 'center',
            y: 'top',
        },
        types: [
            {
                type: 'success',
                background: '#2ecc71',
                icon: {
                    className: 'notyf__icon--success',
                    tagName: 'i'
                }
            },
            {
                type: 'error',
                background: '#e74c3c',
                icon: {
                    className: 'notyf__icon--error',
                    tagName: 'i'
                }
            }
        ]
    });
</script>


<script>
    function showSuccess(message) {
        notyf.success(message);
    }

    function showError(message) {
        notyf.error(message);
    }

    function confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }
</script>
