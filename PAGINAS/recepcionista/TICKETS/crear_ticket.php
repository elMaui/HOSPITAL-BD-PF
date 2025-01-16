<?php
require_once '../../Cconexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $folio_ticket = $input['folio_ticket'] ?? '';
    $tipo = $input['tipo'] ?? '';
    $items = $input['items'] ?? [];
    $total = $input['total'] ?? 0.00;

    if (empty($folio_ticket) || empty($tipo) || empty($items)) {
        echo json_encode(['error' => 'Datos incompletos para generar el ticket.']);
        exit;
    }

    try {
        $conn = Cconexion::ConexionBD();
        $conn->beginTransaction();

        // Insertar ticket
        $insertTicketQuery = "INSERT INTO Ticket (FolioTicket, Total) VALUES (:folio_ticket, :total)";
        $stmtTicket = $conn->prepare($insertTicketQuery);
        $stmtTicket->bindParam(':folio_ticket', $folio_ticket, PDO::PARAM_STR);
        $stmtTicket->bindParam(':total', $total, PDO::PARAM_STR);
        $stmtTicket->execute();

        $idTicket = $conn->lastInsertId();

        // Insertar cada ítem como parte del ticket
        if ($tipo === 'Servicio') {
            $insertItemQuery = "INSERT INTO Detalle_Ticket (ID_Ticket, ID_Servicio, Cantidad_Servicio) VALUES (:id_ticket, :id_item, 1)";
        } elseif ($tipo === 'Medicamento') {
            $insertItemQuery = "INSERT INTO Detalle_Ticket (ID_Ticket, ID_Medicamento, Cantidad_Med) VALUES (:id_ticket, :id_item, 1)";
        } else {
            echo json_encode(['error' => 'Tipo de ítem no válido.']);
            exit;
        }

        $stmtItem = $conn->prepare($insertItemQuery);

        foreach ($items as $item) {
            $nombre = trim(explode(' - $', $item)[0]); // Obtener solo el nombre del ítem

            // Consultar ID del ítem por su nombre
            $queryID = $tipo === 'Servicio' ?
                "SELECT ID_Servicio AS ID FROM Servicio WHERE Nombre = :nombre" :
                "SELECT ID_Medicamento AS ID FROM Medicamento WHERE Nombre = :nombre";

            $stmtID = $conn->prepare($queryID);
            $stmtID->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmtID->execute();
            $idItem = $stmtID->fetch(PDO::FETCH_ASSOC)['ID'] ?? null;

            if (!$idItem) {
                throw new Exception("No se encontró el ID del ítem: $nombre.");
            }

            $stmtItem->bindParam(':id_ticket', $idTicket, PDO::PARAM_INT);
            $stmtItem->bindParam(':id_item', $idItem, PDO::PARAM_INT);
            $stmtItem->execute();
        }

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Ticket creado exitosamente.', 'folio' => $folio_ticket]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['error' => "Error: " . $e->getMessage()]); // Descomentado para depuración
    }
} else {
    echo json_encode(['error' => 'Método no permitido.']);
}
