<?php
require_once '../../Cconexion.php';
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    die("Acceso no autorizado.");
}

$emailUsuario = $_SESSION['email'];

try {
    $conn = Cconexion::ConexionBD();

    // Consulta para obtener información del empleado y recepcionista
    $query = "
        SELECT e.Nombre, e.ApellidoP, e.ApellidoM, e.email1, e.email2, e.tel1, e.tel2,
               d.calle, d.num_domicilio, d.colonia, d.municipio,
               r.ID_Recepcionista
        FROM Empleado e
        JOIN Direccion d ON e.ID_Direccion = d.ID_Direccion
        JOIN Recepcionista r ON e.ID_Empleado = r.ID_Empleado
        WHERE e.email1 = :emailUsuario
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':emailUsuario', $emailUsuario);
    $stmt->execute();
    $recepcionista = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($recepcionista) {
        // Información del recepcionista encontrada
        echo json_encode([
            'nombre_completo' => "{$recepcionista['Nombre']} {$recepcionista['ApellidoP']} {$recepcionista['ApellidoM']}",
            'direccion' => "{$recepcionista['calle']} {$recepcionista['num_domicilio']}, {$recepcionista['colonia']}, {$recepcionista['municipio']}",
            'email1' => $recepcionista['email1'],
            'email2' => $recepcionista['email2'] ?: 'No proporcionado',
            'telefono1' => $recepcionista['tel1'],
            'telefono2' => $recepcionista['tel2'] ?: 'No proporcionado',
            'id_recepcionista' => $recepcionista['ID_Recepcionista']
        ]);
    } else {
        echo json_encode(['error' => 'Datos del recepcionista no encontrados.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => "Error en la base de datos: " . $e->getMessage()]);
}
?>
