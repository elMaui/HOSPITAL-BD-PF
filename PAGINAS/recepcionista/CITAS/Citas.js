document.addEventListener("DOMContentLoaded", () => {
    const calendarBody = document.getElementById("calendar-body");
    const selectedDay = document.getElementById("selectedDay");
    const tituloMes = document.getElementById("tituloMes");

    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();

    const renderCalendar = () => {
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const lastDate = new Date(currentYear, currentMonth + 1, 0).getDate();

        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        tituloMes.textContent = `${monthNames[currentMonth]} ${currentYear}`;

        calendarBody.innerHTML = ""; // Limpiar calendario

        let date = 1;
        for (let i = 0; i < 6; i++) {
            const row = document.createElement("tr");

            for (let j = 0; j < 7; j++) {
                const cell = document.createElement("td");

                if ((i === 0 && j < (firstDay === 0 ? 6 : firstDay - 1)) || date > lastDate) {
                    cell.textContent = "";
                } else {
                    const currentDate = date; // Correct scope issue
                    cell.textContent = currentDate;
                    cell.addEventListener("click", () => {
                        selectedDay.textContent = `Día seleccionado: ${currentDate} de ${monthNames[currentMonth]} de ${currentYear}`;
                        highlightSelected(cell);
                    });
                    date++;
                }
                row.appendChild(cell);
            }

            calendarBody.appendChild(row);
        }
    };

    const highlightSelected = (cell) => {
        const allCells = document.querySelectorAll("#calendar-body td");
        allCells.forEach((c) => c.classList.remove("selected"));
        cell.classList.add("selected");
    };

    document.getElementById("anterior").addEventListener("click", () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar();
    });

    document.getElementById("posterior").addEventListener("click", () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    });

    renderCalendar();
});