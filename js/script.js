document.addEventListener('DOMContentLoaded', function() {
    // Initialize all JavaScript functionality
    
    // Auto-update current time
    updateCurrentTime();
    setInterval(updateCurrentTime, 60000);
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize dynamic form fields
    initializeDynamicFields();
    
    // Initialize search functionality
    initializeSearch();

    // ✅ Initialize dark/light theme toggle
    initializeThemeToggle();
});

function updateCurrentTime() {
    const timeElements = document.querySelectorAll('#currentTime');
    if (timeElements.length > 0) {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        timeElements.forEach(element => {
            element.textContent = now.toLocaleDateString('en-US', options);
        });
    }
}

function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    highlightFieldError(field);
                } else {
                    clearFieldError(field);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    });
}

function highlightFieldError(field) {
    field.style.borderColor = '#dc3545';
    field.style.boxShadow = '0 0 0 2px rgba(220, 53, 69, 0.2)';
}

function clearFieldError(field) {
    field.style.borderColor = '';
    field.style.boxShadow = '';
}

function initializeDynamicFields() {
    // Luggage field toggle
    const luggageCheckbox = document.getElementById('HasLuggage');
    const luggageField = document.getElementById('luggageNumberField');
    
    if (luggageCheckbox && luggageField) {
        luggageCheckbox.addEventListener('change', function() {
            luggageField.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                const luggageInput = luggageField.querySelector('input');
                if (luggageInput) luggageInput.value = '';
            }
        });
    }
    
    // Real-time badge preview
    const badgePreviewElements = {
        name: document.querySelector('.badge-name'),
        organization: document.querySelector('.badge-organization'),
        number: document.querySelector('.badge-number')
    };
    
    if (badgePreviewElements.name) {
        const formInputs = document.querySelectorAll('input[name="FullName"], input[name="Organization"]');
        formInputs.forEach(input => {
            input.addEventListener('input', updateBadgePreview);
        });
    }
}

function updateBadgePreview() {
    const fullName = document.querySelector('input[name="FullName"]')?.value || 'Visitor Name';
    const organization = document.querySelector('input[name="Organization"]')?.value || 'Organization';
    
    const badgeName = document.querySelector('.badge-name');
    const badgeOrg = document.querySelector('.badge-organization');
    
    if (badgeName) badgeName.textContent = fullName;
    if (badgeOrg) badgeOrg.textContent = organization;
}

function initializeSearch() {
    const searchInputs = document.querySelectorAll('input[type="text"][id="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchForm = this.closest('form') || this.parentElement;
                if (searchForm) {
                    searchForm.submit();
                }
            }
        });
    });
}

// 🌙 THEME TOGGLE FUNCTIONALITY
function initializeThemeToggle() {
    const toggleBtn = document.getElementById('theme-toggle');
    const body = document.body;

    if (!toggleBtn) return;

    // Load saved theme
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-theme');
        toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
    }

    toggleBtn.addEventListener('click', () => {
        body.classList.toggle('dark-theme');

        if (body.classList.contains('dark-theme')) {
            localStorage.setItem('theme', 'dark');
            toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            localStorage.setItem('theme', 'light');
            toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        }
    });
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '1000';
    notification.style.minWidth = '300px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Export functions for reports
function exportToCSV(data, filename) {
    const csvContent = "data:text/csv;charset=utf-8," + data;
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportToPDF(elementId, filename) {
    const element = document.getElementById(elementId);
    if (element) {
        const opt = {
            margin: 1,
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        
        // You would need to include html2pdf.js library for this to work
        // html2pdf().from(element).set(opt).save();
        showNotification('PDF export requires additional library', 'warning');
    }
}

// Dashboard statistics auto-update
function updateDashboardStats() {
    // This would typically make an AJAX call to get updated stats
    console.log('Updating dashboard statistics...');
}

// Auto-refresh for active visitors
function startAutoRefresh(interval = 30000) {
    setInterval(() => {
        if (window.location.pathname.includes('dashboard.php') || 
            window.location.pathname.includes('visitor-management.php')) {
            window.location.reload();
        }
    }, interval);
}

// Start auto-refresh on dashboard and visitor management pages
if (window.location.pathname.includes('dashboard.php') || 
    window.location.pathname.includes('visitor-management.php')) {
    startAutoRefresh(60000); // Refresh every minute
}