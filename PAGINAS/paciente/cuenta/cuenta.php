<?php
require_once '../../Cconexion.php';
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    die("Acceso no autorizado.");
}

$emailUsuario = $_SESSION['email'];

try {
    $conn = Cconexion::ConexionBD();

    // Consulta para obtener información del paciente
    $query = "
        SELECT p.ID_paciente, p.Nombre, p.ApellidoP, p.ApellidoM, p.email1, p.email2, p.tel1, p.tel2,
               d.calle, d.num_domicilio, d.colonia, d.municipio
        FROM Paciente p
        JOIN Direccion d ON p.ID_Direccion = d.ID_Direccion
        WHERE p.email1 = :emailUsuario
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':emailUsuario', $emailUsuario);
    $stmt->execute();
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($paciente) {
        // Información del paciente encontrada
        echo json_encode([
            'nombre_completo' => "{$paciente['Nombre']} {$paciente['ApellidoP']} {$paciente['ApellidoM']}",
            'direccion' => "{$paciente['calle']} {$paciente['num_domicilio']}, {$paciente['colonia']}, {$paciente['municipio']}",
            'email1' => $paciente['email1'],
            'email2' => $paciente['email2'] ?: 'No proporcionado',
            'telefono1' => $paciente['tel1'],
            'telefono2' => $paciente['tel2'] ?: 'No proporcionado'
        ]);
    } else {
        echo json_encode(['error' => 'Datos del paciente no encontrados.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => "Error en la base de datos: " . $e->getMessage()]);
}
?>
