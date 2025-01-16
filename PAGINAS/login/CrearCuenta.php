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

        // Verificar si el tipo de usuario (paciente) existe
        $tipoUsuario = 3; // Paciente
        $checkTipoUsuarioQuery = "
            SELECT COUNT(*)
            FROM TIPO_USUARIO
            WHERE TipoUsuario = :tipoUsuario
        ";
        $checkTipoUsuarioStmt = $conn->prepare($checkTipoUsuarioQuery);
        $checkTipoUsuarioStmt->bindParam(':tipoUsuario', $tipoUsuario);
        $checkTipoUsuarioStmt->execute();
        $exists = $checkTipoUsuarioStmt->fetchColumn();

        if (!$exists) {
            echo "Error: El tipo de usuario no existe en TIPO_USUARIO<br>";
            throw new Exception("Tipo de usuario no válido");
        }
        echo "Tipo de usuario válido<br>";

        // Obtener datos del formulario
        $nombre = $_POST['nombre'];
        $apellidoP = $_POST['apellidoP'];
        $apellidoM = $_POST['apellidoM'];
        $calle = $_POST['calle'];
        $numDomicilio = $_POST['numDomicilio'];
        $colonia = $_POST['colonia'];
        $municipio = $_POST['municipio'];
        $email = $_POST['email'];
        $email2 = $_POST['email2'] ?? null;
        $tel1 = $_POST['tel1'];
        $tel2 = $_POST['tel2'] ?? null;
        $contrasena = $_POST['password'];

        // Inicio de transacción
        $conn->beginTransaction();
        echo "Inicio de transacción<br>";

        // Insertar usuario primero
        $usuarioQuery = "
            INSERT INTO Usuario (Email_Usuario, contrasena, TipoUsuario)
            VALUES (:email, :contrasena, :tipoUsuario)
        ";
        $usuarioStmt = $conn->prepare($usuarioQuery);
        $usuarioStmt->bindParam(':email', $email);
        $usuarioStmt->bindParam(':contrasena', $contrasena);
        $usuarioStmt->bindParam(':tipoUsuario', $tipoUsuario);
        $usuarioStmt->execute();
        echo "Usuario insertado<br>";

        // Insertar dirección
        $direccionQuery = "
            INSERT INTO Direccion (calle, num_domicilio, colonia, municipio)
            OUTPUT INSERTED.ID_Direccion
            VALUES (:calle, :numDomicilio, :colonia, :municipio)
        ";
        $direccionStmt = $conn->prepare($direccionQuery);
        $direccionStmt->bindParam(':calle', $calle);
        $direccionStmt->bindParam(':numDomicilio', $numDomicilio);
        $direccionStmt->bindParam(':colonia', $colonia);
        $direccionStmt->bindParam(':municipio', $municipio);
        $direccionStmt->execute();
        $direccionID = $direccionStmt->fetch(PDO::FETCH_ASSOC)['ID_Direccion'];
        echo "Dirección insertada con ID: $direccionID<br>";

        // Insertar paciente
        $pacienteQuery = "
            INSERT INTO Paciente (Nombre, ApellidoP, ApellidoM, ID_Direccion, email1, email2, tel1, tel2)
            VALUES (:nombre, :apellidoP, :apellidoM, :direccionID, :email, :email2, :tel1, :tel2)
        ";
        $pacienteStmt = $conn->prepare($pacienteQuery);
        $pacienteStmt->bindParam(':nombre', $nombre);
        $pacienteStmt->bindParam(':apellidoP', $apellidoP);
        $pacienteStmt->bindParam(':apellidoM', $apellidoM);
        $pacienteStmt->bindParam(':direccionID', $direccionID);
        $pacienteStmt->bindParam(':email', $email);
        $pacienteStmt->bindParam(':email2', $email2);
        $pacienteStmt->bindParam(':tel1', $tel1);
        $pacienteStmt->bindParam(':tel2', $tel2);
        $pacienteStmt->execute();
        echo "Paciente insertado<br>";

        // Confirmar transacción
        $conn->commit();
        echo "Transacción confirmada<br>";

        // Redirección al login
        echo "<script>
                alert('Cuenta creada exitosamente. Por favor, inicia sesión.');
                window.location.href = 'login.html'; // Ruta ajustada
              </script>";
    } catch (PDOException $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        echo "Error al crear la cuenta: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Método no permitido";
}
?>