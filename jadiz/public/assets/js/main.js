/**
 * Main JavaScript File
 * Common functions and utilities
 */

// Add CSRF token to all AJAX requests
$(document).ready(function() {
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            // Get CSRF token from meta tag or form
            var token = $('meta[name="csrf-token"]').attr('content') || $('[name="csrf_token"]').val();
            if (token) {
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }
        }
    });
});

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertClass = 'alert-' + type;
    const alert = $(`<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`);
    
    $('#alerts').prepend(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        alert.fadeOut(function() { $(this).remove(); });
    }, 5000);
}

/**
 * Validate email
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate password strength
 */
function validatePassword(password) {
    const minLength = 12;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};:'",.<>?]/.test(password);
    
    return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar;
}

/**
 * Format date
 */
function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('en-US', options);
}

/**
 * Make AJAX request with error handling
 */
function makeRequest(url, method = 'GET', data = null) {
    return $.ajax({
        url: url,
        type: method,
        data: data,
        dataType: 'json',
        error: function(xhr, status, error) {
            console.error('Request failed:', error);
            if (xhr.status === 401) {
                window.location.href = '/jadiz/public/auth/resident_login.php';
            }
        }
    });
}
