// Format currency
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(angka);
}

// AJAX helper
function ajax(url, data) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(data)
    }).then(response => response.json());
}

// Notification system
function checkNotifications() {
    ajax('ajax/handler.php', {
        action: 'get_notifications'
    }).then(response => {
        if (response.success) {
            updateNotificationBadge(response.notifications);
        }
    });
}

function updateNotificationBadge(notifications) {
    const badge = document.getElementById('notification-badge');
    const unread = notifications.filter(n => !n.read_at).length;
    
    if (unread > 0) {
        badge.textContent = unread;
        badge.style.display = 'block';
    } else {
        badge.style.display = 'none';
    }
}

// Form validation
function validateForm(form, rules) {
    const errors = {};
    
    for (const [field, rule] of Object.entries(rules)) {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input) continue;
        
        const value = input.value.trim();
        
        if (rule.includes('required') && !value) {
            errors[field] = 'Field ini wajib diisi';
        }
        
        if (rule.includes('email') && value && !isValidEmail(value)) {
            errors[field] = 'Email tidak valid';
        }
        
        if (rule.includes('numeric') && value && !isNumeric(value)) {
            errors[field] = 'Harus berupa angka';
        }
    }
    
    return errors;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isNumeric(value) {
    return !isNaN(parseFloat(value)) && isFinite(value);
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        new Tooltip(element);
    });
});

// Check notifications periodically
setInterval(checkNotifications, 60000); // Check every minute 