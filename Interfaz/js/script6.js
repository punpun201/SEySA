document.addEventListener("DOMContentLoaded", function() {
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.querySelector(".container");

    toggleSidebar.addEventListener("click", function() {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
    });
});