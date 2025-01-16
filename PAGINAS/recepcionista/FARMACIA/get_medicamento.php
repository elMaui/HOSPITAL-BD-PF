<?php
require_once '../../Cconexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['nombre'])) {
    $nombre = trim($_GET['nombre']);

    try {
        $conn = Cconexion::ConexionBD();

        $query = "SELECT Nombre FROM Medicamento WHERE Nombre LIKE ?";
        $stmt = $conn->prepare($query);
        $searchTerm = "%$nombre%";
        $stmt->bindParam(1, $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $medicamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($medicamentos) {
            echo json_encode($medicamentos);
        } else {
            echo json_encode(['error' => 'No se encontraron medicamentos con ese nombre.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Solicitud no válida.']);
}
