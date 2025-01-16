document.addEventListener("DOMContentLoaded", () => {
    const toggleButtons = document.querySelectorAll(".toggle-button");

    toggleButtons.forEach(button => {
        button.addEventListener("click", () => {
            const patientItem = button.closest(".patient-item");
            const details = patientItem.querySelector(".patient-details");

            // Toggle visibility of details
            if (details.style.display === "none" || !details.style.display) {
                details.style.display = "block";
                button.classList.add("rotate");
            } else {
                details.style.display = "none";
                button.classList.remove("rotate");
            }
        });
    });
});