<?php
require_once '../../Cconexion.php';
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    die("Acceso no autorizado.");
}

$emailUsuario = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correoRespaldo = trim($_POST['correo_respaldo']);
    $telefono = trim($_POST['telefono']);
    $telefonoSecundario = trim($_POST['telefono_secundario']);

    try {
        $conn = Cconexion::ConexionBD();

        // Consulta para actualizar los datos del paciente
        $query = "
            UPDATE Paciente
            SET email2 = :correoRespaldo, tel1 = :telefono, tel2 = :telefonoSecundario
            WHERE email1 = :emailUsuario
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':correoRespaldo', $correoRespaldo);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':telefonoSecundario', $telefonoSecundario);
        $stmt->bindParam(':emailUsuario', $emailUsuario);

        if ($stmt->execute()) {
            echo "
                <script>
                    alert('Datos actualizados correctamente.');
                    window.location.href = 'Cuenta.html';
                </script>
            ";
        } else {
            echo "
                <script>
                    alert('Error al actualizar los datos.');
                    window.history.back();
                </script>
            ";
        }
    } catch (PDOException $e) {
        die("Error en la base de datos: " . $e->getMessage());
    }
} else {
    die("Método no permitido.");
}
?>
