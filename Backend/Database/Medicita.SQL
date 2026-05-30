-- CREAR BASE LA BASE DE DATOS
CREATE DATABASE BD_MEDICITA;
-- DROP DATABASE BD_MEDICITA; -- ELIMINA LA BASE DE DATOS DE MANERA FISICA
-- ACTIVAR LA BASE DE DATOS A UTILIZAR
USE BD_MEDICITA;

CREATE TABLE USUARIOS
(
	userId 					INT 			NOT NULL PRIMARY KEY AUTO_INCREMENT,
	userPassword			VARCHAR(255) 	NOT NULL,
	usrRol          		ENUM('Paciente', 'Medico', 'Asistente Medico') NOT NULL,
	userEstatus				INT 			NOT NULL,
	userFechaReg			TIMESTAMP 		NOT NULL
);

-- TABLA MEDICO
CREATE TABLE MEDICO
(
	medId 					INT 			NOT NULL PRIMARY KEY AUTO_INCREMENT,
    medNombre 				VARCHAR(50) 	NOT NULL,
    medApePat				VARCHAR(50) 	NOT NULL,
    medApeMat 				VARCHAR(50),
    medSexo					VARCHAR(10) 	NOT NULL,
    medEdad   				INT 			NOT NULL,
    medCorreo				VARCHAR(80) 	NOT NULL,
    medCedula               VARCHAR(20) 	NOT NULL,
    medEstatus				INT 			NOT NULL,
    medFechaReg				TIMESTAMP 		NOT NULL
);

-- TABLA ESPECIALIDAD
CREATE TABLE ESPECIALIDAD 
(
	espeId 					INT 			NOT NULL PRIMARY KEY AUTO_INCREMENT,
	espeNombre 				VARCHAR(50) 	NOT NULL
);

-- TABLA TURNO_MEDICO (Funciona como su contrato de horario fijo)
CREATE TABLE TURNO_MEDICO (
    turId           INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    medId           INT             NOT NULL UNIQUE, -- Relación 1:1, un médico tiene un turno fijo
    turDiasLaborales VARCHAR(100)   NOT NULL COMMENT 'Ej: Lunes a Viernes',
    turHoraEntrada  TIME            NOT NULL COMMENT 'Ej: 14:00:00',
    turHoraSalida   TIME            NOT NULL COMMENT 'Ej: 22:00:00',
    FOREIGN KEY (medId) REFERENCES MEDICO(medId)
);

-- TABLA AUSENCIA_MEDICO (Para sus días de descanso fijo, vacaciones o incapacidad)
CREATE TABLE AUSENCIA_MEDICO (
    ausId           INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    medId           INT             NOT NULL,
    ausTipo         ENUM('Descanso Semanal', 'Vacaciones', 'Incapacidad') NOT NULL,
    ausFecha        DATE            NOT NULL COMMENT 'Fecha exacta en la que no laborará',
    ausMotivo       VARCHAR(200)    NULL,
    FOREIGN KEY (medId) REFERENCES MEDICO(medId)
);

-- TABLA MEDICO_ESPECIALIDAD (Relación N:M entre médicos y especialidades)
CREATE TABLE MEDICO_ESPECIALIDAD
(
	meId 					INT 			NOT NULL PRIMARY KEY AUTO_INCREMENT,
	medId					INT 			NOT NULL,
	espeId 					INT 			NOT NULL,
	
	FOREIGN KEY (medId) REFERENCES MEDICO(medId),
	FOREIGN KEY (espeId) REFERENCES ESPECIALIDAD(espeId)
);

-- TABLA PACIENTE
CREATE TABLE PACIENTE
(
	pacId 					INT 			NOT NULL PRIMARY KEY AUTO_INCREMENT,
    pacNombre 				VARCHAR(50) 	NOT NULL,
    pacApePat				VARCHAR(50) 	NOT NULL,
    pacApeMat 				VARCHAR(50),
    pacSexo					VARCHAR(50) 	NOT NULL,
    pacFechaNac				DATE 			NOT NULL,
    pacNSS                  VARCHAR(20) 	NOT NULL,
    pacCorreo				VARCHAR(80) 	NOT NULL,
	pacTelefono				VARCHAR(20) 	NOT NULL,
    pacPeso					FLOAT 			NOT NULL,
    pacEstatura				float			NOT NULL,
    pacEstatus				INT 			NOT NULL,
    pacFechaReg				TIMESTAMP 		NOT NULL
);

-- TABLA ASISTENTE MEDICO (Recepcionista)
CREATE TABLE ASISTENTE_MEDICO (
    astId           INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    astNombre       VARCHAR(100)    NOT NULL,
    astApePat        VARCHAR(50)     NOT NULL,
    astApeMat        VARCHAR(50)     NULL,
    astEdad         INT             NOT NULL,
    astCorreo       VARCHAR(80)    NOT NULL,
    astTelefono     VARCHAR(20)     NOT NULL,
    astEstatus      INT             NOT NULL,
    astFechaReg     TIMESTAMP       NOT NULL
);

-- TABLA EXPEDIENTE (Relación "Tiene" - 1:1 con Paciente)
CREATE TABLE EXPEDIENTE (
    expId           INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    pacId           INT             NOT NULL UNIQUE,
    expAlergias     TEXT,
    expPadecimientos TEXT,
    expMedicamentos TEXT,
    FOREIGN KEY (pacId) REFERENCES PACIENTE(pacId)
);

-- TABLA CITA, TABLA CON LLAVES FORANEAS DE LAS TABLAS MEDICO Y PACIENTE
CREATE TABLE CITA(
	citId 					INT 			NOT NULL PRIMARY KEY AUTO_INCREMENT,
    medId					INT 			NOT NULL, -- SABER EL TURNO DEL MEDICO PARA ASIGNAR LA CITA
    pacId 					INT 			NOT NULL,
	astId           		INT             NULL, -- Puede ser NULL si la agenda el paciente
    citFecha				TIMESTAMP 		NOT	NULL,
    citHora 				TIME 			NOT NULL,
    citMotivo 				VARCHAR(200)	NOT NULL,
    citEstatus				ENUM('agendada', 'confirmada', 'completada', 'cancelada') NOT NULL DEFAULT 'agendada',
    citFechaReg				TIMESTAMP 		NOT NULL,
	citMotivoCancela 		VARCHAR(255)   NULL,
    
    FOREIGN KEY (medId) REFERENCES MEDICO(medId),
	FOREIGN KEY (astId) REFERENCES ASISTENTE_MEDICO(astId),
    FOREIGN KEY (pacId) REFERENCES PACIENTE(pacId)
);

-- TABLA NOTA_CONSULTA (La entidad robusta que consolida la visita médica)
CREATE TABLE NOTA_CONSULTA (
    notaId          INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    citId           INT             NOT NULL UNIQUE, -- 1 a 1: Una nota pertenece a una cita específica
    medId           INT             NOT NULL,
    pacId           INT             NOT NULL,
    notaAsistencia  BOOLEAN         NOT NULL DEFAULT TRUE COMMENT 'Confirma que el paciente ingresó',
    notaDiagnostico TEXT            NOT NULL COMMENT 'Lo que el doctor observó',
    notaReceta      TEXT            NULL COMMENT 'Medicamentos recetados en esta cita',
    notaFechaCreacion TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (citId) REFERENCES CITA(citId),
    FOREIGN KEY (medId) REFERENCES MEDICO(medId),
    FOREIGN KEY (pacId) REFERENCES PACIENTE(pacId)
);

/**INSERTAR DATOS DE PRUEBA */
-- 1. Insertar Especialidad
INSERT INTO ESPECIALIDAD VALUES 
	(NULL,'Cardiología'), (NULL,'Medicina General');

-- 2. Insertar Médico (Asumimos que ya validaste su estatus)
INSERT INTO MEDICO VALUES 
(NULL, 'Carlos', 'López', 'García', 'Masculino', 45, 'carlos.lopez@medicita.com', 'CED123456', 1, NOW());

-- 3. Vincular Médico con Especialidad (Dr. Carlos López es Cardiólogo)
INSERT INTO MEDICO_ESPECIALIDAD VALUES 
(NULL,1, 1);

-- 4. Asignar el Contrato/Turno al Médico (Lunes a Viernes de 8am a 4pm)
INSERT INTO TURNO_MEDICO VALUES 
(NULL,1, 'Lunes a Viernes', '08:00:00', '16:00:00');

-- 5. Insertar un Paciente
INSERT INTO PACIENTE VALUES 
(NULL, 'Ana', 'Martínez', 'Ruiz', 'Femenino', '1990-05-15', 'NSS987654', 'ana.martinez@email.com', '555-1234', 65.5, 1.68, 1, NOW());

-- 6. Crear el Expediente del Paciente
INSERT INTO EXPEDIENTE VALUES 
(NULL,1, 'Penicilina', 'Hipertensión leve', 'Losartán 50mg');

-- 7. Agendar una Cita (Para mañana a las 10:00 AM)
INSERT INTO CITA 
VALUES (NULL,1, 1, NULL, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', 'Chequeo de presión arterial', 'confirmada', NOW(),NULL);

-- /*INSERTAR MEDICOS */
-- INSERT INTO MEDICO  VALUES
-- (NULL, 'ROBERTO', 'GARCIA', 'BOLLAÑOS', 'PEDIATRIA', 'MASCULINO', 40 , 'ROBERT14GB@HOTMAIL.COM', 1 , NOW()),
-- (NULL, 'KARLA', 'LOPEZ', 'SANCHEZ', 'NUTRICION', 'FEMENINO', 36 , 'KARLALOP@GMAIL.COM', 1 , NOW()),
-- (NULL, 'ANTONIO', 'ESPARZA', 'GOMEZ', 'CIRUJANO GENERAL', 'MASCULINO', 45 , 'ANTONIO784@GMAIL.COM', 1 , NOW()),
-- (NULL, 'OMAR', 'CRUZ', 'RAMIREZ', 'GINECOLOGO', 'MASCULINO', 50 , 'OMARJUA@HOTMAIL.COM', 1 , NOW()),
-- (NULL, 'DANAEH', 'GARCIA', 'LUZ', 'PEDIATRIA', 'FEMENINPO', 32 , 'DANA965@HOTMAIL.COM', 1 , NOW()),
-- (NULL, 'JULIA', 'TORRES', 'PEREZ', 'NEUROLOGIA', 'FEMENINO', 32 , 'JULTORRES@GMAIL.COM', 1 , NOW());


-- /*INSERTAR PACIENTES */
-- INSERT INTO PACIENTE VALUES
-- (NULL, 'MARÍA', 'JUAREZ', 'JIMENEZ', 'FEMENINO', 45 ,  'MARIAJJ2@GMAIL.COM', 70.9, 1.60, 1, NOW()),
-- (NULL, 'NATHASHA', 'ROMANO', 'ORTIZ', 'FEMENINO', 29, 'NATH@OUTLOOK.COM', 55.9, 1.63, 1, NOW()),
-- (NULL, 'STEVEN', 'CURIEL', 'ROGERT', 'MASCULINO', 29,  'STEVEN569@OUTLOOK.COM', 1.85, 75.5, 1, NOW()),
-- (NULL, 'WANDA', 'PEREZ', 'VILLEGAS', 'FEMENINO', 25 ,  'WANDAPERV@GMAIL.COM', 60.9, 1.63, 1, NOW()),
-- (NULL, 'ESMERALDA', 'ARELLANO', 'MARTINEZ', 'FEMENINO', 15, 'ESMEAM@GMAIL.COM', 67.9, 1.55, 1, NOW()),
-- (NULL, 'IAN', 'FERNANDEZ', 'MONTALVO', 'MASCULINO', 35,  'IAN785@OUTLOOK.COM', 1.75, 67.5, 1, NOW()),
-- (NULL, 'ADRIAN', 'SANCHEZ', 'ORTEGA', 'MASCULINO', 22 ,  'ASO@GMAIL.COM', 69.9, 1.68, 1, NOW()),
-- (NULL, 'DIANA', 'WAYNE', 'PRINCE', 'FEMENINO', 30 ,  'DIANAWAY@GMAIL.COM', 65.9, 1.73, 1, NOW());


-- /*INSERTAR CITAS */
-- INSERT INTO CITA VALUES
-- (NULL,1, 5, NOW(), '09:00:00', 'Consulta pediátrica general', 500.00, 1, NOW()),
-- (NULL, 2, 3, NOW() , '10:30:00', 'Evaluación nutricional inicial', 600.00, 1, NOW()),
-- (NULL, 3, 7, NOW() , '11:00:00', 'Consulta preoperatoria', 1200.00, 1, NOW()),
-- (NULL, 4, 2, NOW() , '12:00:00', 'Control ginecológico anual', 700.00, 1, NOW()),
-- (NULL, 5, 6, NOW() , '13:30:00', 'Seguimiento pediátrico', 500.00, 1, NOW()),
-- (NULL, 6, 4, NOW() , '15:00:00', 'Consulta neurológica', 1500.00, 1, NOW()),
-- (NULL, 1, 5, NOW() , '16:00:00', 'Chequeo pediátrico de rutina', 500.00, 1, NOW());

/*FUNCIONES, PROCEDIMIENTOS ALMACENADOS Y TRIGGERS */

DELIMITER //
CREATE TRIGGER trg_completar_cita
AFTER INSERT ON NOTA_CONSULTA
FOR EACH ROW
BEGIN
    -- Cuando se inserta una nota, actualizamos el estatus de la cita correspondiente a 'completada'
    UPDATE CITA 
    SET citEstatus = 'completada' 
    WHERE citId = NEW.citId;
END //

DELIMITER ;


-- PRUEBA A: Insertar una nota de consulta para la cita que agendamos
INSERT INTO NOTA_CONSULTA 
VALUES (NULL,1, 1, 1, TRUE, 'Paciente estable. Presión 120/80. Peso normal.', 'Continuar con Losartán 1 cada 24h.',now());

-- PRUEBA B
SELECT c.citId, p.pacNombre, m.medNombre, c.citFecha, c.citHora, c.citEstatus 
FROM CITA c
INNER JOIN PACIENTE p ON c.pacId = p.pacId
INNER JOIN MEDICO m ON c.medId = m.medId;

-- PRUEBA C
SELECT 
    p.pacNombre AS Paciente, 
    p.pacFechaNac AS Edad, -- (Ojo, si usaste fechaNac, aquí puedes calcular la edad)
    e.expAlergias AS Alergias,
    e.expPadecimientos AS Cronicos,
    n.notaFechaCreacion AS FechaConsulta,
    n.notaDiagnostico AS DiagnosticoPrevio
FROM PACIENTE p
LEFT JOIN EXPEDIENTE e ON p.pacId = e.pacId
LEFT JOIN NOTA_CONSULTA n ON p.pacId = n.pacId
WHERE p.pacId = 1;

-- PRUEBA D
SELECT 
    m.medNombre AS Doctor, 
    e.espeNombre AS Especialidad,
    t.turDiasLaborales AS Dias, 
    t.turHoraEntrada AS Entrada, 
    t.turHoraSalida AS Salida
FROM MEDICO m
INNER JOIN MEDICO_ESPECIALIDAD me ON m.medId = me.medId
INNER JOIN ESPECIALIDAD e ON me.espeId = e.espeId
INNER JOIN TURNO_MEDICO t ON m.medId = t.medId
WHERE m.medEstatus = 1;