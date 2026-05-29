// assets/js/main.js
document.addEventListener('DOMContentLoaded', function() {
    console.log("GridLink app loaded.");
    
    // Enable Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Dark Mode Toggle Logic
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        const themeIcon = themeToggleBtn.querySelector('i');
        
        // Check local storage or system preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }

        themeToggleBtn.addEventListener('click', () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            if (isDark) {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }
        });
    }

    // Password Visibility Toggle Logic
    const togglePasswordElements = document.querySelectorAll('.toggle-password');
    togglePasswordElements.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Form Loading State Logic
    const authForms = document.querySelectorAll('.auth-form');
    authForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            // Basic Client-Side Validation
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('btn-loading');
                    // We don't prevent default here so the form actually submits to PHP
                }
            }
            form.classList.add('was-validated');
        });
    });

    // Interactive Parallax Background Logic
    const sidebar = document.getElementById('interactive-sidebar');
    const animatedBg = document.getElementById('animated-bg');
    
    if (sidebar && animatedBg) {
        sidebar.addEventListener('mousemove', function(e) {
            // Calculate mouse position relative to the center of the sidebar
            const rect = sidebar.getBoundingClientRect();
            const x = e.clientX - rect.left - (rect.width / 2);
            const y = e.clientY - rect.top - (rect.height / 2);
            
            // Move the background slightly in the opposite direction of the mouse
            const moveX = (x / rect.width) * -30;
            const moveY = (y / rect.height) * -30;
            
            animatedBg.style.transform = `translate(${moveX}px, ${moveY}px)`;
        });
        
        sidebar.addEventListener('mouseleave', function() {
            // Reset position when mouse leaves
            animatedBg.style.transform = `translate(0px, 0px)`;
            animatedBg.style.transition = `transform 0.5s ease-out`;
        });
        
        sidebar.addEventListener('mouseenter', function() {
            // Remove transition for smooth tracking while inside
            animatedBg.style.transition = `transform 0.1s ease-out`;
        });
    }
});
