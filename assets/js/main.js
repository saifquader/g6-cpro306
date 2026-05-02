// assets/js/main.js
document.addEventListener('DOMContentLoaded', function() {
    // Basic initialization
    console.log("GridLink app loaded.");
    
    // Enable Bootstrap tooltips if any
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
