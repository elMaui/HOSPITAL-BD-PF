<?php
require_once '../../Cconexion.php';

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Asegurar que el contenido devuelto sea JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = trim($data['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['error' => 'Correo electrónico faltante.']);
        exit();
    }

    try {
        $conn = Cconexion::ConexionBD();

        // Obtener tipo de usuario y referencia (ID_Paciente o ID_Empleado)
        $queryTipoUsuario = "
            SELECT u.TipoUsuario, COALESCE(p.ID_Paciente, e.ID_Empleado) AS ID_Referencia
            FROM Usuario u
            LEFT JOIN Paciente p ON u.Email_Usuario = p.email1
            LEFT JOIN Empleado e ON u.Email_Usuario = e.email1
            WHERE u.Email_Usuario = :email
        ";
        $stmtTipo = $conn->prepare($queryTipoUsuario);
        $stmtTipo->bindParam(':email', $email, PDO::PARAM_STR);
        $stmtTipo->execute();
        $resultado = $stmtTipo->fetch(PDO::FETCH_ASSOC);

        if (!$resultado) {
            echo json_encode(['error' => 'Usuario no encontrado.']);
            exit();
        }

        $tipoUsuario = $resultado['TipoUsuario'];
        $idReferencia = $resultado['ID_Referencia'];

        if (!$idReferencia) {
            echo json_encode(['error' => 'No se encontró un ID válido para el usuario.']);
            exit();
        }

        // Eliminar según tipo de usuario
        if ($tipoUsuario == 1 || $tipoUsuario == 2) { // Doctor o Recepcionista
            // Eliminar jornadas asociadas al empleado
            $queryEliminarJornadas = "DELETE FROM JORNADA WHERE ID_Empleado = :idEmpleado";
            $stmtEliminarJornadas = $conn->prepare($queryEliminarJornadas);
            $stmtEliminarJornadas->bindParam(':idEmpleado', $idReferencia, PDO::PARAM_INT);
            $stmtEliminarJornadas->execute();

            if ($tipoUsuario == 1) { // Doctor
                $queryCitasDoctor = "SELECT ID_Cita FROM Cita WHERE ID_Doctor = :idDoctor";
                $stmtCitasDoctor = $conn->prepare($queryCitasDoctor);
                $stmtCitasDoctor->bindParam(':idDoctor', $idReferencia, PDO::PARAM_INT);
                $stmtCitasDoctor->execute();
                $citas = $stmtCitasDoctor->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($citas)) {
                    $idsCitas = implode(',', $citas);

                    // Eliminar cancelaciones
                    $queryEliminarCancelaciones = "DELETE FROM CANCELACION WHERE ID_Cita IN ($idsCitas)";
                    $conn->exec($queryEliminarCancelaciones);

                    // Eliminar recetas
                    $queryEliminarRecetas = "DELETE FROM RECETA WHERE ID_Cita IN ($idsCitas)";
                    $conn->exec($queryEliminarRecetas);

                    // Eliminar pagos
                    $queryEliminarPagos = "DELETE FROM PAGO_CITA WHERE ID_Cita IN ($idsCitas)";
                    $conn->exec($queryEliminarPagos);
                }

                $queryEliminarCitas = "DELETE FROM Cita WHERE ID_Doctor = :idDoctor";
                $stmtEliminarCitas = $conn->prepare($queryEliminarCitas);
                $stmtEliminarCitas->bindParam(':idDoctor', $idReferencia, PDO::PARAM_INT);
                $stmtEliminarCitas->execute();

                $queryEliminarDoctor = "DELETE FROM Doctor WHERE ID_Empleado = :idEmpleado";
                $stmtEliminarDoctor = $conn->prepare($queryEliminarDoctor);
                $stmtEliminarDoctor->bindParam(':idEmpleado', $idReferencia, PDO::PARAM_INT);
                $stmtEliminarDoctor->execute();
            } else { // Recepcionista
                // Obtener ID del recepcionista
                $queryRecepcionista = "SELECT ID_Recepcionista FROM Recepcionista WHERE ID_Empleado = :idEmpleado";
                $stmtRecepcionista = $conn->prepare($queryRecepcionista);
                $stmtRecepcionista->bindParam(':idEmpleado', $idReferencia, PDO::PARAM_INT);
                $stmtRecepcionista->execute();
                $idRecepcionista = $stmtRecepcionista->fetchColumn();

                if ($idRecepcionista) {
                    // Eliminar pagos asociados al recepcionista
                    $queryEliminarPagoServ = "DELETE FROM PAGO_SERV WHERE ID_Recep = :idRecepcionista";
                    $stmtEliminarPagoServ = $conn->prepare($queryEliminarPagoServ);
                    $stmtEliminarPagoServ->bindParam(':idRecepcionista', $idRecepcionista, PDO::PARAM_INT);
                    $stmtEliminarPagoServ->execute();
                }

                $queryEliminarRecepcionista = "DELETE FROM Recepcionista WHERE ID_Empleado = :idEmpleado";
                $stmtEliminarRecepcionista = $conn->prepare($queryEliminarRecepcionista);
                $stmtEliminarRecepcionista->bindParam(':idEmpleado', $idReferencia, PDO::PARAM_INT);
                $stmtEliminarRecepcionista->execute();
            }

            $queryEliminarEmpleado = "DELETE FROM Empleado WHERE ID_Empleado = :idEmpleado";
            $stmtEliminarEmpleado = $conn->prepare($queryEliminarEmpleado);
            $stmtEliminarEmpleado->bindParam(':idEmpleado', $idReferencia, PDO::PARAM_INT);
            $stmtEliminarEmpleado->execute();
        } elseif ($tipoUsuario == 3) { // Paciente
            $queryCitasPaciente = "SELECT ID_Cita FROM Cita WHERE ID_Paciente = :idPaciente";
            $stmtCitasPaciente = $conn->prepare($queryCitasPaciente);
            $stmtCitasPaciente->bindParam(':idPaciente', $idReferencia, PDO::PARAM_INT);
            $stmtCitasPaciente->execute();
            $citas = $stmtCitasPaciente->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($citas)) {
                $idsCitas = implode(',', $citas);

                // Eliminar cancelaciones
                $queryEliminarCancelaciones = "DELETE FROM CANCELACION WHERE ID_Cita IN ($idsCitas)";
                $conn->exec($queryEliminarCancelaciones);

                // Eliminar recetas
                $queryEliminarRecetas = "DELETE FROM RECETA WHERE ID_Cita IN ($idsCitas)";
                $conn->exec($queryEliminarRecetas);

                // Eliminar pagos
                $queryEliminarPagos = "DELETE FROM PAGO_CITA WHERE ID_Cita IN ($idsCitas)";
                $conn->exec($queryEliminarPagos);
            }

            $queryEliminarCitas = "DELETE FROM Cita WHERE ID_Paciente = :idPaciente";
            $stmtEliminarCitas = $conn->prepare($queryEliminarCitas);
            $stmtEliminarCitas->bindParam(':idPaciente', $idReferencia, PDO::PARAM_INT);
            $stmtEliminarCitas->execute();

            $queryEliminarPaciente = "DELETE FROM Paciente WHERE ID_Paciente = :idPaciente";
            $stmtEliminarPaciente = $conn->prepare($queryEliminarPaciente);
            $stmtEliminarPaciente->bindParam(':idPaciente', $idReferencia, PDO::PARAM_INT);
            $stmtEliminarPaciente->execute();
        }

        $queryEliminarUsuario = "DELETE FROM Usuario WHERE Email_Usuario = :email";
        $stmtEliminarUsuario = $conn->prepare($queryEliminarUsuario);
        $stmtEliminarUsuario->bindParam(':email', $email, PDO::PARAM_STR);
        $stmtEliminarUsuario->execute();

        echo json_encode(['success' => 'Usuario eliminado correctamente.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
}
?>
