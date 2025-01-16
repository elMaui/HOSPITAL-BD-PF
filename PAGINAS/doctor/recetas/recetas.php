<?php
require_once 'Cconexion.php';

try {
    $conn = Cconexion::ConexionBD();

    // Leer el archivo SQL
    $query = file_get_contents('vistaRecetas.sql');

    if ($query === false) {
        throw new Exception("No se pudo cargar el archivo SQL.");
    }

    // Ejecutar el query
    $stmt = $conn->query($query);

    // Mostrar resultados
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<h3>Receta #" . $row['ID_Receta'] . "</h3>";
        echo "Fecha de emisión: " . $row['Fecha_Emision'] . "<br>";
        echo "Fecha de cita: " . $row['Fecha_cita'] . "<br>";
        echo "Hora de cita: " . $row['Hora_cita'] . "<br>";
        echo "Doctor ID: " . $row['ID_Doctor'] . "<br>";
        echo "Paciente ID: " . $row['ID_Paciente'] . "<br>";
        echo "Diagnóstico: " . $row['Diagnostico'] . "<br>";
        echo "Medicamentos: " . $row['medicamentos'] . "<br>";
        echo "Indicaciones: " . $row['Indicaciones'] . "<br>";
        echo "Observaciones: " . $row['Observaciones'] . "<br><hr>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
