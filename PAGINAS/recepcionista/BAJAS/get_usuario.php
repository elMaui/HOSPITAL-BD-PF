<?php
require_once '../../Cconexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email'])) {
    $email = trim($_GET['email']);

    try {
        $conn = Cconexion::ConexionBD();

        // Obtener el tipo de usuario
        $queryTipoUsuario = "SELECT TipoUsuario FROM Usuario WHERE Email_Usuario = :email";
        $stmtTipo = $conn->prepare($queryTipoUsuario);
        $stmtTipo->bindParam(':email', $email, PDO::PARAM_STR);
        $stmtTipo->execute();
        $tipoUsuario = $stmtTipo->fetch(PDO::FETCH_ASSOC)['TipoUsuario'];

        if (!$tipoUsuario) {
            echo json_encode(['error' => 'Usuario no encontrado.']);
            exit();
        }

        // Seleccionar datos de acuerdo al tipo de usuario
        if ($tipoUsuario == 3) { // Paciente
            $queryPaciente = "
                SELECT u.Email_Usuario, p.Nombre, p.ApellidoP, p.ApellidoM, p.tel1, p.tel2, tu.Descripcion AS TipoUsuario
                FROM Usuario u
                JOIN Paciente p ON u.Email_Usuario = p.email1
                JOIN Tipo_Usuario tu ON u.TipoUsuario = tu.TipoUsuario
                WHERE u.Email_Usuario = :email
            ";
            $stmt = $conn->prepare($queryPaciente);
        } else { // Empleado (Doctor o Recepcionista)
            $queryEmpleado = "
                SELECT u.Email_Usuario, e.Nombre, e.ApellidoP, e.ApellidoM, e.tel1, e.tel2, tu.Descripcion AS TipoUsuario
                FROM Usuario u
                JOIN Empleado e ON u.Email_Usuario = e.email1
                JOIN Tipo_Usuario tu ON u.TipoUsuario = tu.TipoUsuario
                WHERE u.Email_Usuario = :email
            ";
            $stmt = $conn->prepare($queryEmpleado);
        }

        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            echo json_encode($usuario);
        } else {
            echo json_encode(['error' => 'No se encontraron datos para el usuario especificado.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error interno en la base de datos.']);
    }
} else {
    echo json_encode(['error' => 'Método no permitido o parámetro faltante.']);
}
?>
