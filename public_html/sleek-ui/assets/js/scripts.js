// assets/js/scripts.js

document.addEventListener("DOMContentLoaded", function () {
    // Example: Show/hide a section when a button is clicked.
    const toggleBtn = document.getElementById("toggle-section");
    const sectionToToggle = document.getElementById("section-to-toggle");

    if (toggleBtn && sectionToToggle) {
        toggleBtn.addEventListener("click", function () {
            sectionToToggle.classList.toggle("hidden");
        });
    }
});