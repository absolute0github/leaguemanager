// Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-dismiss alerts after 5 seconds
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        if (!alert.querySelector('.btn-close').onclick) {
            setTimeout(function() {
                alert.classList.remove('show');
                setTimeout(function() {
                    alert.remove();
                }, 150);
            }, 5000);
        }
    });

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

// Utility functions
function showAlert(message, type = 'info') {
    var alertElement = document.createElement('div');
    alertElement.className = 'alert alert-' + type + ' alert-dismissible fade show';
    alertElement.role = 'alert';
    alertElement.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';

    var container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alertElement, container.firstChild);
    }
}

function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Format date
function formatDate(dateString) {
    var options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Format currency
function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}
