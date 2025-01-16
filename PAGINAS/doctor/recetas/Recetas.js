document.addEventListener("DOMContentLoaded", () => {
    // Controlar la visibilidad de Emitir receta
    const unavailableMessage = document.querySelector(".recipe-form .unavailable");
    const availableForm = document.querySelector(".recipe-form .available");

    const isInAppointmentTime = () => {
        // Aquí podrías implementar la lógica para determinar si está en el horario de la cita.
        // Por ejemplo, podría depender de una llamada a la base de datos o del reloj del sistema.
        return true; // Cambia esto según la lógica real.
    };

    if (isInAppointmentTime()) {
        unavailableMessage.style.display = "none";
        availableForm.style.display = "block";
    } else {
        unavailableMessage.style.display = "block";
        availableForm.style.display = "none";
    }

    // Controlar los desplegables de recetas emitidas
    const toggleButtons = document.querySelectorAll(".toggle-button");

    toggleButtons.forEach(button => {
        button.addEventListener("click", () => {
            const recipeItem = button.closest(".recipe-item");
            const details = recipeItem.querySelector(".recipe-details");

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