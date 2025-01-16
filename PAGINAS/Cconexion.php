<?php

class Cconexion {
    public static function ConexionBD() {
        $host = 'localhost';
        $dbname = 'HOSPITAL';
        $username = 'conexion';
        $password = 'contrasena';
        $puerto = 50105;

        try {
            $conn = new PDO("sqlsrv:Server=$host, $puerto; Database=$dbname", $username, $password);
            return $conn;
        } catch (PDOException $exp) {
            die("No se logró conectar a la base de datos: $dbname, error: $exp.");
        }
    }
}
?>
