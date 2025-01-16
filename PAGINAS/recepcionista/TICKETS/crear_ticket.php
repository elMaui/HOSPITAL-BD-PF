<?php
require_once '../../Cconexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $folio_ticket = $input['folio_ticket'] ?? '';
    $tipo = $input['tipo'] ?? '';
    $items = $input['items'] ?? [];
    $total = $input['total'] ?? 0.00;

    if (empty($folio_ticket) || empty($tipo) || empty($items)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos incompletos para generar el ticket.']);
        exit;
    }

    try {
        $conn = Cconexion::ConexionBD();

        if (!$conn) {
            throw new Exception("Error al conectar a la base de datos.");
        }

        $conn->beginTransaction();

        // Verificar sesión de usuario
        $emailUsuario = $_SESSION['email'] ?? '';
        if (empty($emailUsuario)) {
            throw new Exception("Usuario no autenticado.");
        }

        // Obtener ID del recepcionista
        $recepcionistaQuery = "
            SELECT Recepcionista.ID_Recepcionista
            FROM Empleado
            JOIN Recepcionista ON Empleado.ID_empleado = Recepcionista.ID_Empleado
            WHERE Empleado.email1 = :email
        ";
        $stmtRecepcionista = $conn->prepare($recepcionistaQuery);
        $stmtRecepcionista->bindParam(':email', $emailUsuario, PDO::PARAM_STR);
        $stmtRecepcionista->execute();
        $recepcionista = $stmtRecepcionista->fetch(PDO::FETCH_ASSOC);

        if (!$recepcionista) {
            throw new Exception("Recepcionista no encontrado.");
        }

        $idRecepcionista = $recepcionista['ID_Recepcionista'];

        // Insertar registro en Pago_Serv y obtener ID generado
        $insertPagoServQuery = "INSERT INTO Pago_Serv (status, Total, ID_Recep) OUTPUT INSERTED.ID_PagoServ VALUES ('Pendiente', :total, :id_recep)";
        $stmtPagoServ = $conn->prepare($insertPagoServQuery);
        $stmtPagoServ->bindParam(':total', $total, PDO::PARAM_STR);
        $stmtPagoServ->bindParam(':id_recep', $idRecepcionista, PDO::PARAM_INT);
        $stmtPagoServ->execute();

        $idPagoServ = $stmtPagoServ->fetch(PDO::FETCH_ASSOC)['ID_PagoServ'] ?? null;

        if (!$idPagoServ) {
            throw new Exception("No se pudo obtener el ID del pago.");
        }

        // Insertar cada ítem en la tabla Ticket
        $insertTicketQuery = "INSERT INTO Ticket (ID_Ticket, ID_Servicio, FolioTicket, ID_Medicamento, Cantidad_Med, Cantidad_Servicio) VALUES (:id_ticket, :id_servicio, :folio_ticket, :id_medicamento, :cantidad_med, :cantidad_servicio)";
        $stmtTicket = $conn->prepare($insertTicketQuery);

        foreach ($items as $item) {
            if (strpos($item, ' - $') === false) {
                throw new Exception("Formato de ítem incorrecto: $item. Asegúrate de usar 'Nombre - $Precio'.");
            }

            list($nombre, $precio) = explode(' - $', $item);
            $nombre = trim($nombre);

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

            $idServicio = $tipo === 'Servicio' ? $idItem : null;
            $idMedicamento = $tipo === 'Medicamento' ? $idItem : null;
            $cantidadServicio = $tipo === 'Servicio' ? 1 : 0;
            $cantidadMed = $tipo === 'Medicamento' ? 1 : 0;

            $stmtTicket->bindValue(':id_ticket', null, PDO::PARAM_NULL);
            $stmtTicket->bindParam(':id_servicio', $idServicio, PDO::PARAM_INT | PDO::PARAM_NULL);
            $stmtTicket->bindParam(':folio_ticket', $folio_ticket, PDO::PARAM_STR);
            $stmtTicket->bindParam(':id_medicamento', $idMedicamento, PDO::PARAM_INT | PDO::PARAM_NULL);
            $stmtTicket->bindParam(':cantidad_med', $cantidadMed, PDO::PARAM_INT);
            $stmtTicket->bindParam(':cantidad_servicio', $cantidadServicio, PDO::PARAM_INT);

            if (!$stmtTicket->execute()) {
                throw new Exception("Error al insertar el ítem: $nombre.");
            }
        }

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Ticket creado exitosamente.', 'folio' => $folio_ticket, 'id_pago' => $idPagoServ]);
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['error' => "Error: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
}
