<?php
require_once '../Cconexion.php'; // Ruta ajustada

// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conexión a la base de datos
        $conn = Cconexion::ConexionBD();
        echo "Conexión exitosa<br>";

        // Obtener datos del formulario
        $diagnostico = $_POST['diagnostico'];
        $medicamentos = $_POST['medicamentos'];
        $indicaciones = $_POST['indicaciones'];
        $Observaciones = $_POST['observaciones'];



        // Inicio de transacción
        $conn->beginTransaction();
        echo "Inicio de transacción<br>";

        $recetaQuery = "
            INSERT INTO RECETA (diagnostico, medicamentos, indicaciones, observaciones)
            VALUES (:diagnostico, :medicamentos, :indicaciones, :observaciones)
        ";
        $recetaStmt = $conn->prepare($recetaQuery);
        $recetaStmt->bindParam(':diagnostico', $diagnostico);
        $recetaStmt->bindParam(':medicamentos', $medicamentos);
        $recetaStmt->bindParam(':indicaciones', $indicaciones);
        $recetaStmt->bindParam(':observaciones', $Observaciones);
        $recetaStmt->execute();
        echo "receta insertada<br>";

        // Confirmar transacción
        $conn->commit();
        echo "Transacción confirmada<br>";

    } catch (PDOException $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        echo "Error al insertar receta: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Método no permitido";
}
?>