document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.querySelector(".menu-toggle");
    const menu = document.querySelector("nav ul");

    // Check if the event listener is already attached
    if (!menuToggle.dataset.listenerAttached) {
        // console.log("Attaching event listener..."); // Debugging log
        menuToggle.dataset.listenerAttached = "true"; // Prevent duplicate listeners
        
        menuToggle.addEventListener("click", function (event) {
            event.stopPropagation();
            // console.log("Menu toggle clicked!");
            menu.classList.toggle("active");
        });
    }
});
