<?php
require_once '../../Cconexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_cita'])) {
    $idCita = trim($_GET['id_cita']);

    try {
        $conn = Cconexion::ConexionBD();

        // Consulta para mostrar los datos completos de la cita
        $query = "
            SELECT 
                c.ID_Cita, 
                c.Fecha_cita AS fecha, 
                c.Hora_cita AS hora, 
                c.Estado, 
                c.fecha_Solicitud,
                CONCAT(e.Nombre, ' ', e.ApellidoP, ' ', e.ApellidoM) AS nombre_doctor,
                CONCAT(p.Nombre, ' ', p.ApellidoP, ' ', p.ApellidoM) AS nombre_paciente,
                CONCAT(cons.Edificio, ', Piso ', cons.Piso, ', Número ', cons.Numero) AS consultorio
            FROM Cita c
            JOIN Doctor d ON c.ID_Doctor = d.ID_Doctor
            JOIN Empleado e ON d.ID_Empleado = e.ID_Empleado
            JOIN Paciente p ON c.ID_Paciente = p.ID_Paciente
            JOIN Consultorio cons ON d.ID_Consultorio = cons.ID_Consultorio
            WHERE c.ID_Cita = :idCita
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':idCita', $idCita, PDO::PARAM_STR); // Aseguramos que el tipo coincida
        $stmt->execute();
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cita) {
            // Asegúrate de devolver valores válidos para todos los campos
            foreach ($cita as $key => $value) {
                $cita[$key] = $value ?? 'No disponible';
            }

            echo json_encode($cita);  // Mostrar todos los datos en formato JSON
        } else {
            echo json_encode(['error' => 'Cita no encontrada.']);
        }
    } catch (PDOException $e) {
        // Mensaje de error genérico en producción
        echo json_encode(['error' => 'Error interno en la base de datos.']);
        // Para depuración: descomentar esta línea
        // echo json_encode(['error' => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método no permitido o parámetro faltante.']);
}
?>
