<?php
require_once '../Cconexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $conn = Cconexion::ConexionBD();

        // Consulta al usuario
        $query = "
            SELECT TipoUsuario
            FROM Usuario
            WHERE Email_Usuario = :email AND contrasena = :password
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['loggedin'] = true;
            $_SESSION['email'] = $email; // Almacenar el correo en la sesión
            $_SESSION['tipo_usuario'] = $user['TipoUsuario'];

            // Redirigir según el tipo de usuario
            switch ($user['TipoUsuario']) {
                case 3: // Paciente
                    $pacienteQuery = "
                        SELECT ID_paciente
                        FROM Paciente
                        WHERE email1 = :email
                    ";
                    $pacienteStmt = $conn->prepare($pacienteQuery);
                    $pacienteStmt->bindParam(':email', $email);
                    $pacienteStmt->execute();
                    $paciente = $pacienteStmt->fetch(PDO::FETCH_ASSOC);

                    if ($paciente) {
                        $_SESSION['id_usuario'] = $paciente['ID_paciente'];
                        echo "<script>console.log('Redirigiendo a Paciente');</script>";
                        header('Location: ../Paciente/Paciente.html');
                        exit();
                    } else {
                        die("Paciente no encontrado.");
                    }

                case 1: // Doctor
                    $doctorQuery = "
                        SELECT Doctor.ID_Doctor
                        FROM Empleado
                        JOIN Doctor ON Empleado.ID_empleado = Doctor.ID_empleado
                        WHERE email1 = :email
                    ";
                    $doctorStmt = $conn->prepare($doctorQuery);
                    $doctorStmt->bindParam(':email', $email);
                    $doctorStmt->execute();
                    $doctor = $doctorStmt->fetch(PDO::FETCH_ASSOC);

                    if ($doctor) {
                        $_SESSION['id_usuario'] = $doctor['ID_Doctor'];
                        echo "<script>console.log('Redirigiendo a Doctor');</script>";
                        header('Location: ../Doctor/Doc.html');
                        exit();
                    } else {
                        die("Doctor no encontrado.");
                    }

                case 2: // Recepcionista
                    $recepcionistaQuery = "
                        SELECT Recepcionista.ID_Recepcionista
                        FROM Empleado
                        JOIN Recepcionista ON Empleado.ID_empleado = Recepcionista.ID_Empleado
                        WHERE email1 = :email
                    ";
                    $recepcionistaStmt = $conn->prepare($recepcionistaQuery);
                    $recepcionistaStmt->bindParam(':email', $email);
                    $recepcionistaStmt->execute();
                    $recepcionista = $recepcionistaStmt->fetch(PDO::FETCH_ASSOC);

                    if ($recepcionista) {
                        $_SESSION['id_usuario'] = $recepcionista['ID_Recepcionista'];
                        echo "<script>console.log('Redirigiendo a Recepcionista');</script>";
                        header('Location: ../Recepcionista/Recep.html');
                        exit();
                    } else {
                        die("Recepcionista no encontrado.");
                    }

                default:
                    die("Tipo de usuario no reconocido.");
            }
        } else {
            die("Correo o contraseña incorrectos.");
        }
    } catch (PDOException $e) {
        die("Error en la base de datos: " . $e->getMessage());
    }
} else {
    die("Método no permitido.");
}
?>