document.addEventListener("DOMContentLoaded", function () {
    // Sidebar toggle functionality
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    
    toggleSidebar.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
    });
    
    // Search functionality
    const searchInput = document.getElementById("searchInput");
    searchInput.addEventListener("input", function () {
        let filter = this.value.toLowerCase();
        let cards = document.querySelectorAll(".personnel-card");

        cards.forEach(card => {
            let name = card.querySelector("h3").innerText.toLowerCase();
            if (name.includes(filter)) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }
        });
    });
    
    // Add Personnel Form Modal
    const addPersonnelBtn = document.getElementById("addPersonnelBtn");
    const personnelFormOverlay = document.getElementById("personnelFormOverlay");
    const cancelBtn = document.getElementById("cancelBtn");
    
    addPersonnelBtn.addEventListener("click", function () {
        personnelFormOverlay.style.display = "flex";
    });
    
    cancelBtn.addEventListener("click", function () {
        personnelFormOverlay.style.display = "none";
    });
    
    // Hide form overlay when clicking outside
    personnelFormOverlay.addEventListener("click", function (e) {
        if (e.target === personnelFormOverlay) {
            personnelFormOverlay.style.display = "none";
        }
    });
});
