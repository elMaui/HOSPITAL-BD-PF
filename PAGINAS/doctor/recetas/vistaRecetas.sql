USE HOSPITAL;
GO

CREATE VIEW vw_RecetasConPacientes
AS
SELECT 
    r.ID_Receta, 
    r.Fecha_Emision, 
    c.Fecha_cita, 
    c.Hora_cita,
    p.apellidoP + ' ' + p.apellidoM + ' ' + p.nombre AS Nombre_Completo, 
    r.Diagnostico, 
    r.medicamentos, 
    r.Indicaciones, 
    r.Observaciones
FROM 
    RECETA r
INNER JOIN 
    cita c ON r.ID_Cita = c.ID_cita
JOIN PACIENTE p ON p.ID_paciente = c.ID_Paciente
ORDER BY 
    r.Fecha_Emision DESC;
GO
