<?php
require_once '../../Cconexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tipo']) && isset($_GET['termino'])) {
    $tipo = trim($_GET['tipo']);
    $termino = trim($_GET['termino']);

    try {
        $conn = Cconexion::ConexionBD();

        if (!$conn) {
            error_log("Error: No se pudo conectar a la base de datos.");
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'No se pudo conectar a la base de datos.']);
            exit;
        }

        // Validar tipo permitido
        if (!in_array($tipo, ['Servicio', 'Medicamento'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Tipo no válido.']);
            exit;
        }

        // Definir la consulta SQL con alias específicos para evitar errores
        $tabla = $tipo === 'Servicio' ? 'Servicio' : 'Medicamento';
        $campoID = $tipo === 'Servicio' ? 'ID_Servicio' : 'ID_Medicamento';
        $query = "SELECT $campoID AS ID, Nombre, Precio FROM $tabla WHERE Nombre LIKE :termino";

        $stmt = $conn->prepare($query);
        $terminoBusqueda = "%" . $termino . "%";
        $stmt->bindParam(':termino', $terminoBusqueda, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            error_log("Error en la consulta: " . print_r($errorInfo, true));
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al ejecutar la consulta en la base de datos.']);
            exit;
        }

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados) > 0) {
            error_log("Búsqueda exitosa para '$termino': " . json_encode($resultados));
            http_response_code(200);
            echo json_encode($resultados[0]); // Devuelve el primer resultado de forma directa para simplificar la estructura
        } else {
            error_log("No se encontraron resultados para '$termino' en la tabla $tipo.");
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'No se encontraron resultados.',
                'message' => 'Por favor verifica el término de búsqueda e intenta nuevamente.'
            ]);
        }
    } catch (PDOException $e) {
        error_log("Error en la base de datos: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error en la base de datos: ' . $e->getMessage(),
            'message' => 'Hubo un problema con la base de datos, contacta al administrador.'
        ]);
    } catch (Exception $e) {
        error_log("Error inesperado: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error inesperado: ' . $e->getMessage(),
            'message' => 'Ocurrió un error inesperado, por favor intenta de nuevo.'
        ]);
    }
} else {
    error_log("Solicitud incorrecta: " . print_r($_GET, true));
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Solicitud incorrecta o parámetros faltantes.',
        'message' => 'Asegúrate de enviar los parámetros requeridos (tipo y término).'
    ]);
}
