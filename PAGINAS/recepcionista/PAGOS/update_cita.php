<?php
require_once '../../Cconexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decodificar JSON desde la entrada del cuerpo
    $data = json_decode(file_get_contents('php://input'), true);
    $idCita = trim($data['id_cita'] ?? '');
    $estado = trim($data['estado'] ?? '');

    // Validar que los datos no estén vacíos
    if (empty($idCita) || empty($estado)) {
        echo json_encode(['error' => 'ID de cita o estado faltante.']);
        exit();
    }

    try {
        $conn = Cconexion::ConexionBD();

        // Validación del estado
        $estadosValidos = ['Confirmada', 'Pendiente', 'Cancelada_0', 'Cancelada_50', 'Cancelada_100'];
        if (!in_array($estado, $estadosValidos)) {
            echo json_encode(['error' => 'Estado no válido.']);
            exit();
        }

        // Actualización de cita
        $queryActualizar = "UPDATE Cita SET Estado = :estado WHERE ID_Cita = :idCita";
        $stmtActualizar = $conn->prepare($queryActualizar);
        $stmtActualizar->bindParam(':idCita', $idCita, PDO::PARAM_INT);
        $stmtActualizar->bindParam(':estado', $estado, PDO::PARAM_STR);

        if ($stmtActualizar->execute()) {
            echo json_encode(['success' => 'Cita actualizada correctamente.']);
        } else {
            $errorInfo = $stmtActualizar->errorInfo();
            echo json_encode(['error' => 'Error en SQL: ' . $errorInfo[2]]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método no permitido.']);
}
