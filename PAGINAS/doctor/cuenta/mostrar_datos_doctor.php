<?php
require_once '../../Cconexion.php';
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    die("Acceso no autorizado.");
}

$emailUsuario = $_SESSION['email'];

try {
    $conn = Cconexion::ConexionBD();

    // Consulta para obtener información del empleado y doctor
    $query = "
        SELECT e.Nombre, e.ApellidoP, e.ApellidoM, e.email1, e.email2, e.tel1, e.tel2,
               d.calle, d.num_domicilio, d.colonia, d.municipio,
               doc.ID_Doctor, doc.Licencia, esp.nombre AS Especialidad, c.Numero AS Consultorio
        FROM Empleado e
        JOIN Direccion d ON e.ID_Direccion = d.ID_Direccion
        JOIN Doctor doc ON e.ID_Empleado = doc.ID_Empleado
        JOIN Especialidad esp ON doc.ID_Especialidad = esp.ID_Especialidad
        JOIN Consultorio c ON doc.ID_Consultorio = c.ID_Consultorio
        WHERE e.email1 = :emailUsuario
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':emailUsuario', $emailUsuario);
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doctor) {
        // Información del doctor encontrada
        echo json_encode([
            'nombre_completo' => "{$doctor['Nombre']} {$doctor['ApellidoP']} {$doctor['ApellidoM']}",
            'direccion' => "{$doctor['calle']} {$doctor['num_domicilio']}, {$doctor['colonia']}, {$doctor['municipio']}",
            'email1' => $doctor['email1'],
            'email2' => $doctor['email2'] ?: 'No proporcionado',
            'telefono1' => $doctor['tel1'],
            'telefono2' => $doctor['tel2'] ?: 'No proporcionado',
            'id_doctor' => $doctor['ID_Doctor'],
            'licencia' => $doctor['Licencia'],
            'especialidad' => $doctor['Especialidad'],
            'consultorio' => $doctor['Consultorio']
        ]);
    } else {
        echo json_encode(['error' => 'Datos del doctor no encontrados.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => "Error en la base de datos: " . $e->getMessage()]);
}
?>
