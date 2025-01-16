<?php
// Configura la conexión a SQL Server
$serverName = "localhost"; //este es el servidor
$connectionOptions = array(
    "Database" => "HOSPITAL", // Cambia por tu nombre de base de datos
    "Uid" => "usuario", // mormon Cambialo por tu usuario
    "PWD" => "contraseña" // mormon cambialo por tu contraseña
);

// Establecer la conexión
$conn = sqlsrv_connect( $serverName, $connectionOptions );

// Verifica si la conexión es exitosa
if( !$conn ) {
    die( print_r(sqlsrv_errors(), true));
}

// Obtener las especialidades
$sql_especialidades = "SELECT id_especialidad, nombre_especialidad FROM Especialidades";
$stmt_especialidades = sqlsrv_query( $conn, $sql_especialidades );

// Obtener los servicios
$sql_servicios = "SELECT id_servicio, nombre_servicio FROM Servicios";
$stmt_servicios = sqlsrv_query( $conn, $sql_servicios );

// Obtener los doctores
$sql_doctores = "SELECT id_doctor, nombre_doctor FROM Doctores";
$stmt_doctores = sqlsrv_query( $conn, $sql_doctores );

// Obtener la fecha de hoy y la fecha máxima (2 días después)
$fecha_hoy = date("Y-m-d");
$fecha_maxima = date("Y-m-d", strtotime("+2 days"));

?>