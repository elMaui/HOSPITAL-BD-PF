<?php
require_once '../../Cconexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tipo']) && isset($_GET['nombre'])) {
    $tipo = trim($_GET['tipo']);
    $nombre = trim($_GET['nombre']);

    try {
        $conn = Cconexion::ConexionBD();

        // Consulta simplificada solo con el nombre completo
        if ($tipo === 'empleado') {
            $query = "SELECT Nombre + ' ' + ApellidoP + ' ' + ApellidoM AS nombre_completo FROM Empleado WHERE Nombre LIKE ? OR ApellidoP LIKE ?";
        } else {
            $query = "SELECT Nombre + ' ' + ApellidoP + ' ' + ApellidoM AS nombre_completo FROM Paciente WHERE Nombre LIKE ? OR ApellidoP LIKE ?";
        }

        $stmt = $conn->prepare($query);
        $searchTerm = "%$nombre%";
        $stmt->bindParam(1, $searchTerm, PDO::PARAM_STR);
        $stmt->bindParam(2, $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($resultados) {
            echo json_encode($resultados);
        } else {
            echo json_encode(['error' => 'No se encontraron resultados.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Solicitud no válida.']);
}
