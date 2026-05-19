-- CREAR BASE LA BASE DE DATOS
CREATE DATABASE BD_CITAS;
-- DROP DATABASE BD_CITAS;
-- ACTIVAR LA BASE DE DATOS A UTILIZAR
USE BD_CITAS;

-- 3. CREAR LAS TABLAS DE LA BASE DE DATOS

-- CREACION DE LA TABLA MEDICO
CREATE TABLE MEDICO
(
	medId 					INT 			NOT NULL PRIMARY KEY AUTO_INCREMENT,
    medNombre 				VARCHAR(50) 	NOT NULL,
    medApePat				VARCHAR(50),
    medApeMat 				VARCHAR(50),
    medEspecialidad			VARCHAR(50),
    medSexo					VARCHAR(10) 	NOT NULL,
    medEdad   				INT 			NOT NULL,
    medCorreo				VARCHAR(80) 	NOT NULL,
    medEstatus				INT 			NOT NULL,
    medFechaReg				TIMESTAMP 		NOT NULL
);

-- CREACION DE LA TABLA PACIENTE
CREATE TABLE PACIENTE
(
	pacId 					INT 			NOT NULL PRIMARY KEY AUTO_INCREMENT,
    pacNombre 				VARCHAR(50) 	NOT NULL,
    pacApePat				VARCHAR(50),
    pacApeMat 				VARCHAR(50),
    pacSexo					VARCHAR(50) 	NOT NULL,
    pacEdad   				INT 			NOT NULL,
    pacCorreo				VARCHAR(80) 	NOT NULL,
    pacPeso					FLOAT 			NOT NULL,
    pacEstatura				float			NOT NULL,
    pacEstatus				INT 			NOT NULL,
    pacFechaReg				TIMESTAMP 		NOT NULL
);

-- CREACION DE LA TABLA CITA
-- TABLA CON LLAVES FORANEAS DE LAS TABLAS MEDICO Y PACIENTE
CREATE TABLE CITA(
	citId 					INT 			NOT NULL PRIMARY KEY AUTO_INCREMENT,
    medId					INT 			NOT NULL,
    pacId 					INT 			NOT NULL,
    citFecha				TIMESTAMP 			NULL,
    citHora 				TIME 			NOT NULL,
    citMotivo 				VARCHAR(200)	NOT NULL,
    citCosto				FLOAT   		NOT NULL,
    citEstatus				INT 			NOT NULL,
    citFechaReg				TIMESTAMP 		NOT NULL,
    
    FOREIGN KEY (medId) REFERENCES MEDICO(medId),
    FOREIGN KEY (pacId) REFERENCES PACIENTE(pacId)
);


/*INSERTAR UN MEDICO */
INSERT INTO MEDICO  VALUES
(NULL, 'ROBERTO', 'GARCIA', 'BOLLAÑOS', 'PEDIATRIA', 'MASCULINO', 40 , 'ROBERT14GB@HOTMAIL.COM', 1 , NOW()),
(NULL, 'KARLA', 'LOPEZ', 'SANCHEZ', 'NUTRICION', 'FEMENINO', 36 , 'KARLALOP@GMAIL.COM', 1 , NOW()),
(NULL, 'ANTONIO', 'ESPARZA', 'GOMEZ', 'CIRUJANO GENERAL', 'MASCULINO', 45 , 'ANTONIO784@GMAIL.COM', 1 , NOW()),
(NULL, 'OMAR', 'CRUZ', 'RAMIREZ', 'GINECOLOGO', 'MASCULINO', 50 , 'OMARJUA@HOTMAIL.COM', 1 , NOW()),
(NULL, 'DANAEH', 'GARCIA', 'LUZ', 'PEDIATRIA', 'FEMENINPO', 32 , 'DANA965@HOTMAIL.COM', 1 , NOW()),
(NULL, 'JULIA', 'TORRES', 'PEREZ', 'NEUROLOGIA', 'FEMENINO', 32 , 'JULTORRES@GMAIL.COM', 1 , NOW());


/*INSERTAR UN PACIENTE */
INSERT INTO PACIENTE VALUES
(NULL, 'MARÍA', 'JUAREZ', 'JIMENEZ', 'FEMENINO', 45 ,  'MARIAJJ2@GMAIL.COM', 70.9, 1.60, 1, NOW()),
(NULL, 'NATHASHA', 'ROMANO', 'ORTIZ', 'FEMENINO', 29, 'NATH@OUTLOOK.COM', 55.9, 1.63, 1, NOW()),
(NULL, 'STEVEN', 'CURIEL', 'ROGERT', 'MASCULINO', 29,  'STEVEN569@OUTLOOK.COM', 1.85, 75.5, 1, NOW()),
(NULL, 'WANDA', 'PEREZ', 'VILLEGAS', 'FEMENINO', 25 ,  'WANDAPERV@GMAIL.COM', 60.9, 1.63, 1, NOW()),
(NULL, 'ESMERALDA', 'ARELLANO', 'MARTINEZ', 'FEMENINO', 15, 'ESMEAM@GMAIL.COM', 67.9, 1.55, 1, NOW()),
(NULL, 'IAN', 'FERNANDEZ', 'MONTALVO', 'MASCULINO', 35,  'IAN785@OUTLOOK.COM', 1.75, 67.5, 1, NOW()),
(NULL, 'ADRIAN', 'SANCHEZ', 'ORTEGA', 'MASCULINO', 22 ,  'ASO@GMAIL.COM', 69.9, 1.68, 1, NOW()),
(NULL, 'DIANA', 'WAYNE', 'PRINCE', 'FEMENINO', 30 ,  'DIANAWAY@GMAIL.COM', 65.9, 1.73, 1, NOW());


/*INSERTAR UNA CITA */
INSERT INTO CITA VALUES
(NULL,1, 5, NOW(), '09:00:00', 'Consulta pediátrica general', 500.00, 1, NOW()),
(NULL, 2, 3, NOW() , '10:30:00', 'Evaluación nutricional inicial', 600.00, 1, NOW()),
(NULL, 3, 7, NOW() , '11:00:00', 'Consulta preoperatoria', 1200.00, 1, NOW()),
(NULL, 4, 2, NOW() , '12:00:00', 'Control ginecológico anual', 700.00, 1, NOW()),
(NULL, 5, 6, NOW() , '13:30:00', 'Seguimiento pediátrico', 500.00, 1, NOW()),
(NULL, 6, 4, NOW() , '15:00:00', 'Consulta neurológica', 1500.00, 1, NOW()),
(NULL, 1, 5, NOW() , '16:00:00', 'Chequeo pediátrico de rutina', 500.00, 1, NOW());


UPDATE MEDICO 
SET medEspecialidad='GINECOLOGIA'
WHERE medId=4;

-- INICIA LAS 8 CONSULTAS SOLICITADAS

-- 1. CONSULTA NO.1

SELECT medId ID, CONCAT(medApePat, ' ', medApeMat, ' ', medNombre) 'Medico', medEspecialidad Especialidad
FROM MEDICO
WHERE medEspecialidad = 'PEDIATRIA';

-- 2. CONSULTA NO.2

SELECT CONCAT(medApePat, ' ', medApeMat, ' ', medNombre) Medico, medCorreo Correo,
'MEDICO' AS ESTATUS
FROM MEDICO
WHERE medCorreo LIKE '%gmail.com'
UNION 
SELECT CONCAT(pacNombre, ' ', pacApePat, ' ', pacApeMat) Paciente, pacCorreo Correo,
'PACIENTE'
FROM PACIENTE
WHERE pacCorreo LIKE '%gmail.com';


-- 3. CONSULTA NO.3

SELECT COUNT(citId) 'Citas Registradas'
FROM CITA;

-- 4. CONSULTA NO.4

SELECT SUM(citCosto) 'Suma total de lo costos de citas realizadas'
FROM CITA;

-- 5. CONSULTA NO.5
SELECT AVG(citCosto) 'Costo promedio de las citas'
FROM CITA;

-- 6. CONSULTA NO.6

SELECT CONCAT(pacNombre, ' ', pacApePat, ' ', pacApeMat) Paciente, pacEdad Edad
FROM PACIENTE
WHERE pacEdad = (SELECT MIN(pacEdad) FROM PACIENTE);


-- 7. CONSULTA NO.7

SELECT CONCAT(medNombre, ' ', medApePat, ' ', medApeMat) AS Medico,
  (SELECT COUNT(citId)
FROM CITA
WHERE medId = MEDICO.medId) 'No.Citas atendidas'
FROM MEDICO;

-- 8. CONSULTA NO.8

SELECT  CONCAT(pacNombre, ' ', pacApePat, ' ', pacApeMat) AS Paciente, B.citFecha Fecha, B.citCosto Costo
FROM PACIENTE A, CITA B
WHERE A.pacId  = B.pacId
AND B.citCosto = (SELECT MAX(citCosto) 
FROM CITA);

-- EJERCICIO 1
SELECT CONCAT(pacNombre, pacApePat, pacApeMat) AS PACIENTES, B.citFecha 'FECHA CITA', B.citHora 'HORA CITA'
FROM PACIENTE A
INNER JOIN CITA B ON A.pacId=B.pacId;

/* EJERCICIOS 14/07/2025*/
-- EJERCICIO 2  lista nombre del medico y el nombre del paciente
SELECT CONCAT(medNombre, ' ' , medApePat,'-' ,medApeMat) 'NOMBRE MEDICO', CONCAT(pacNombre, ' ',pacApePat,'-', pacApeMat) AS PACIENTES
FROM MEDICO 
INNER JOIN PACIENTE ON medId=pacId;
-- ORIGINAL
SELECT CONCAT(medNombre, ' ' , medApePat,'-' ,medApeMat) 'NOMBRE MEDICO', CONCAT(pacNombre, ' ',pacApePat,'-', pacApeMat) AS PACIENTES
FROM CITA C
INNER JOIN MEDICO M ON C.medId=M.medId
INNER JOIN PACIENTE P ON C.pacId=P.pacId;

-- EJERCICIO 3 CONSULTA LAS CITAS D EMAYOR COSTO A 400 
SELECT CONCAT(A.medNombre, ' ' , A.medApePat,'-' ,A.medApeMat) 'NOMBRE MEDICO',A.medEspecialidad ESPECIALIDAD, B.citCosto COSTO
FROM MEDICO A, CITA B
WHERE A.medId=B.citId
AND B.citCosto>=400;

-- EJERCICIO 4 MUESTRA LAS CITAS DE MEDICO FEMENINOS Y DATOS DEL PACIENTE
SELECT CONCAT(A.medNombre, ' ' , A.medApePat,'-' ,A.medApeMat) 'NOMBRE MEDICO',
		CONCAT(pacNombre, ' ',pacApePat,'-', pacApeMat) AS PACIENTES, 
		B.citFecha 'FECHA CITA', B.citHora 'HORA CITA'
FROM MEDICO A, CITA B, PACIENTE C 
WHERE A.medId=B.citId
AND  B.citId=C.pacId
AND medSexo='FEMENINO';  

-- EJERCICIO 5 
SELECT  
    CONCAT(A.pacNombre, ' ', A.pacApePat, '-', A.pacApeMat) AS 'NOMBRE PACIENTE', 
    CONCAT(M.medNombre, ' ', M.medApePat, '-', M.medApeMat) AS 'NOMBRE MEDICO', 
    C.citMotivo AS 'MOTIVO', 
    C.citCosto AS 'COSTO'
FROM CITA C
INNER JOIN PACIENTE A ON A.pacId = C.pacId
INNER JOIN MEDICO M ON M.medId = C.medId
WHERE C.citCosto > (SELECT AVG(citCosto) FROM CITA);

-- EXTRAER LOS TRES PRIMEROS CARACTERES DEL NOMBRE
SELECT SUBSTRING('',1,3) DATOS_EXTRAIDOS;

-- EJEMPLOS DE ALTER TABLE PARA AGREGAR UNA NUEVA COLUMNA
ALTER TABLE MEDICO ADD medCelular VARCHAR(15);

-- ELIMINAR UNA COLUMNA A LA TABLA MEDICO
ALTER TABLE MEDICO DROP COLUMN medCelular;

-- MODIFICAR EL TIPO DE DATO DE UNA COLUMNA
ALTER TABLE MEDICO MODIFY medCelular VARCHAR(20);

SELECT * FROM MEDICO;

-- ################################################# 
-- PROCEDIMIENTOS ALMACENADOS 
-- ##################################################
-- CORRE EN SEGUNDO PLANO
DELIMITER //
CREATE PROCEDURE tspListarMedicos
() -- AQUI VAN LOS PARAMETROS SI LOS NECESITA
BEGIN  -- INICIO DEL PROCEDIMIENTO
	SELECT medId 'ID MEDICO', 
    CONCAT(medNombre,' ',medApePat, ' ', medApeMat) 'Medico', 
    medEspecialidad Especialidad
	FROM MEDICO
	ORDER BY medApePat;
END; -- FIN DEL PROCEDIMIENTO

-- EJECUTAR EL PROCEDIMIENTO
CALL tspListarMedicos();

DELIMITER //
CREATE PROCEDURE tspBuscarMedicoEspecialidad
(
 IN espe VARCHAR(50)
) -- AQUI VAN LOS PARAMETROS SI LOS NECESITA
BEGIN  -- INICIO DEL PROCEDIMIENTO
	SELECT medId 'ID MEDICO', 
    CONCAT(medNombre,' ',medApePat, ' ', medApeMat) 'Medico', 
    medEspecialidad Especialidad
	FROM MEDICO
    WHERE medEspecialidad=espe
	ORDER BY medApePat;
END; -- FIN DEL PROCEDIMIENTO

-- EJECUTAR EL PROCEDIMIENTO
CALL tspBuscarMedicoEspecialidad('PEDIATRIA');
CALL tspBuscarMedicoEspecialidad('GINECOLOGIA');

-- SEGUNDO STORED
DELIMITER //
CREATE PROCEDURE tspListarMedicos
()
BEGIN 
	IF EXISTS (SELECT medId FROM MEDICO) THEN
    BEGIN
		SELECT medId 'ID MEDICO', 
			CONCAT(medNombre,' ',medApePat, ' ', medApeMat) 'Medico', 
			medEspecialidad Especialidad
		FROM MEDICO
		ORDER BY medApePat;
	END;
	ELSE
		SELECT 0 'ID MEDICO','NO HAY MEDICOS REGISTRADOS POR EL MOMENTO';
	END IF;
END; -- FIN DEL PROCEDIMIENTO


-- BORRAR UN PROCEDIMIENTO DE MANERA FISICA
DROP PROCEDURE tspListarMedicos;
-- EJECUTAR EL PROCEDIMIENTO
CALL tspListarMedicos();

DELIMITER $$
CREATE PROCEDURE tspBuscarMedicoEspecialidad
(
 IN espe VARCHAR(50)
)
BEGIN
	IF EXISTS (SELECT medId FROM MEDICO WHERE medEspecialidad = espe) THEN
    BEGIN 
		SELECT medId 'ID MEDICO',
			CONCAT(medNombre,' ',medApePat, ' ', medApeMat) 'Medico', 
			medEspecialidad Especialidad
		FROM MEDICO
		WHERE medEspecialidad=espe
		ORDER BY medApePat;
	END;
	ELSE
		SELECT 0 'ID MEDICO', CONCAT('NO HAY MEDICOS ', espe ,' REGISTRADOS CON ESA ESPECIALIDAD') OBSERVACION;
	END IF;
END; -- FIN DEL PROCEDIMIENTO

DROP PROCEDURE tspBuscarMedicoEspecialidad;
CALL tspBuscarMedicoEspecialidad('CARDIOLOGIA');
CALL tspBuscarMedicoEspecialidad('GINECOLOGIA');

/*-- EJERCICIO 2 INSERTAR MEDICO, CONSIDERAR QUE SE DEBA INSERTAR SOLO SI EL CORREO NO ES IGUAL AUN MEDICO
     REGISTRADO, INDICAR QUE EL CORREO YA ESXISTE CUANDO SEA ESTE CASO*/
     
DELIMITER $$
CREATE PROCEDURE tspInsertarMedico
( 
	IN nom varchar(50), 
		pat varchar(50), 
		mat varchar(50), 
		espe varchar(50),
        gen VARCHAR(10),
		edad INT,
		correo varchar(80),
        est INT,
		cel VARCHAR(20)
)
BEGIN
IF EXISTS (SELECT * FROM MEDICO WHERE medCorreo = correo) THEN
		SELECT 0 AS 'ID MEDICO', 'YA EXISTE UN MEDICO REGISTRADO CON ESE CORREO' OBSERVACION;
	ELSE
		INSERT INTO MEDICO (
			medNombre, medApePat, medApeMat, medEspecialidad,
			medSexo, medEdad, medCorreo, medEstatus, medFechaReg, medCelular
		)
		VALUES (
			nom, pat, mat, espe, gen, edad, correo, est, NOW(), cel
		);
		SELECT 1 AS 'ID MEDICO', 'MÉDICO INSERTADO EXITOSAMENTE' OBSERVACION;	
    END IF;
END;

DROP PROCEDURE tspInsertarMedico;
CALL tspInsertarMedico('JUAN', 'PEREZ', 'LORA', 'CARDIOLOGO', 'MASCULINO', 45,'JU.P93R3Z@HOTMAIL.COM',1,'5559876567');
/* METODO CORRRECTO
DELIMITER $$
CREATE PROCEDURE tspInsertarMedico
( 
	IN nom varchar(50), 
		pat varchar(50), 
		mat varchar(50), 
		espe varchar(50),
        gen VARCHAR(10),
		edad INT,
		correo varchar(80),
		cel VARCHAR(20)
)
BEGIN
	IF NOT EXISTS (SELECT * FROM MEDICO WHERE medCorreo = correo) THEN
	BEGIN
			INSERT INTO MEDICO (
				medNombre, medApePat, medApeMat, medEspecialidad,
				medSexo, medEdad, medCorreo, medEstatus, medFechaReg, medCelular)
			VALUES (
				NULL,nom, pat, mat, espe, gen, edad, correo,1, NOW(), cel
			);
			SELECT 1 AS 'ID MEDICO', 'MÉDICO INSERTADO EXITOSAMENTE' ESTATUS;
            END;
		ELSE
			SELECT 0 AS 'ID MEDICO', 'YA EXISTE UN MEDICO REGISTRADO CON ESE CORREO' ESTATUS;
    END IF;
END;

DROP PROCEDURE tspInsertarMedico;
CALL tspInsertarMedico('JUAN', 'PEREZ', 'LORA', 'CARDIOLOGO', 'MASCULINO', 45,'JU.P93R3Z@HOTMAIL.COM',1,'5559876567');*/

/* EJERCICIOS 22/07/2025
-- EJERCICIO 3 MODIFICAR MEDICOS */


DELIMITER $$

CREATE PROCEDURE tspModificarMedico(
	IN 	id INT,
		 nom VARCHAR(50), 
		 pat VARCHAR(50), 
		 mat VARCHAR(50), 
		 espe VARCHAR(50),
		 gen VARCHAR(10),
		 edad INT,
		 correo VARCHAR(80),
		 est INT,
		 cel VARCHAR(20)
)
BEGIN
	IF EXISTS (SELECT 1 FROM medico WHERE medCorreo = correo AND medId <> id) THEN
		SELECT 0 AS 'ESTATUS', 'NO MODIFICADO - CORREO YA ESTÁ REGISTRADO' AS 'OBSERVACION';
	ELSE
		UPDATE MEDICO
		SET 
			medNombre = nom, 
			medApePat = pat, 
			medApeMat = mat, 
			medEspecialidad = espe, 
			medSexo = gen, 
			medEdad = edad,
			medCorreo = correo,
			medEstatus = est, 
			medCelular = cel
		WHERE medId = id;

		SELECT 1 AS 'ESTATUS', 'MODIFICADO EXITOSAMENTE' AS 'OBSERVACION';
	END IF;
END;


DROP PROCEDURE tspModificarMedico;
CALL tspModificarMedico(1, 'JUANITO', 'PEREZ', 'LOPEZ', 'DERMATOLOGIA', 'MASCULINO', 50, 'ju.p3r3z@hotmail.com', 1, '5550000000');
CALL tspModificarMedico(1,'ANTONIO', 'ESPARZA', 'GOMEZ', 'CIRUJANO GENERAL', 'MASCULINO', 45 , 'ANTONIO784@GMAIL.COM', 1 ,'8765456789');


CALL tspModificarMedico('DANIEL', 'VELENCIA', 'HERNANDEZ', 'OTORRINOLARINGOLOGO', 'MASCULINO', 78,'DANIELVH@GMAIL.COM',1,'5559098643');

-- EJERCICIO 4 ELIMINAR MEDICOS
DELIMITER $$
CREATE PROCEDURE tspEliminarMedico
(
IN clave INT
)
BEGIN
	IF NOT EXISTS (SELECT * FROM MEDICO WHERE medId = clave) THEN
        SELECT 0 AS 'ESTADO', 'NO ELIMINADO, NO ENCONTRADO' AS 'OBSERVACION';
    ELSEIF EXISTS (SELECT * FROM MEDICO WHERE medId = clave AND medEstatus = 0) THEN
        SELECT 2 AS 'ESTADO', 'EL MEDICO YA ESTA DADO DE BAJA' AS 'OBSERVACION';
    ELSE
        UPDATE MEDICO SET medEstatus = 0 WHERE medId = clave;
        SELECT 1 AS 'ESTADO', 'ELIMINADO DE FORMA EXITOSA' AS 'OBSERVACION';
	END IF;
END;

DROP PROCEDURE tspEliminarMedico;
CALL tspEliminarMedico(9); -- ELIMINA UN REGISTRO NO REGISTRADO
CALL tspEliminarMedico(7); -- DA EL MENSAJE DE QUE YA HA SIDO DADO DE BAJA EL MEDIDO
CALL tspEliminarMedico(3); -- ELIMINA AL MEDICO REGISTRADO


-- EJERCICIO 5 BUSCAR MEDICOS LIKE O WHERE
DELIMITER $$
CREATE PROCEDURE tspBuscarMedico
(
IN clave INT,
	pat varchar(50)
)
BEGIN
	IF EXISTS (SELECT medId FROM MEDICO WHERE medId = clave OR medApePat=pat ) THEN
    BEGIN 
		SELECT medId 'ID MEDICO',
			CONCAT(medNombre,' ',medApePat, ' ', medApeMat) 'MEDICO', 
			medEspecialidad Especialidad, medSexo Genero, 
            medEdad Edad, medCorreo 'E-mail', medCelular Telefono,
            IF(medEstatus=1,'ACTIVO', 'INACTIVO') Estado, medFechaReg Registro
		FROM MEDICO
		WHERE medId = clave
        OR medApePat=pat
        OR medApePat LIKE CONCAT('%', pat, '%')
		ORDER BY medApePat;
	END;
	ELSE
		SELECT 0 'ID MEDICO', 'LOS DATOS DEL MEDICO NO HAN SIDO ENCONTRADOS' OBSERVACION;
	END IF;
END;

DROP PROCEDURE tspBuscarMedico;
CALL tspBuscarMedico(0, 'PEREZ');
CALL tspBuscarMedico(9, '');



DELIMITER $$
CREATE PROCEDURE tspBuscarMedico
(
IN clave INT,
	pat varchar(50)
)
BEGIN
	IF EXISTS (SELECT medId FROM MEDICO WHERE medId = clave OR medApePat=pat ) THEN
    BEGIN 
		SELECT medId 'ID MEDICO',
			CONCAT(medNombre,' ',medApePat, ' ', medApeMat) 'MEDICO', 
			medEspecialidad Especialidad, medSexo Genero, 
            medEdad Edad, medCorreo 'E-mail', medCelular Telefono,
            IF(medEstatus=1,'ACTIVO', 'INACTIVO') Estado, medFechaReg Registro
		FROM MEDICO
		WHERE medId = clave
        OR medApePat=pat
		ORDER BY medApePat;
	END;
	ELSE
		SELECT 0 'ID MEDICO', 'LOS DATOS DEL MEDICO NO HAN SIDO ENCONTRADOS' OBSERVACION;
	END IF;
END;


CALL tspInsertarMedico('DANIEL', 'VALENCIA', 'HERNANDEZ', 'OTORRINOLARINGOLOGO', 'MASCULINO', 39,'ROBERT14GB@HOTMAIL.COM',1,'7447575948');

CALL tspModificarMedico(1,'ROBERTO', 'GAR', 'BOLLANOS', 'CIRUJIA', 'MASCULINO', 45,'ROBERT14GB@HOTMAIL.COM',1,'7447575948');
CALL tspModificarMedico(1,'ROBERTO', 'GAR', 'BOLIUANOS', 'CIRUJIA', 'MASCULINO', 45,'ROBERT14GB@HOTMAIL.COM',1,'7447575948');
CALL tspModificarMedico(1,'DANIEL', 'VELENCIA', 'HERNANDEZ', 'OTORRINOLARINGOLOGO', 'MASCULINO', 78,'DANIELVH@GMAIL.COM',1,'5559098643');

CALL tspEliminarMedico(9); -- ELIMINA AL MEDICO REGISTRADO
CALL tspEliminarMedico(9); -- DA EL MENSAJE DE QUE YA HA SIDO DADO DE BAJA EL MEDICO
CALL tspEliminarMedico(10); -- ELIMINA UN REGISTRO NO REGISTRADO



CALL tspBuscarMedico(0,'HERNANDEZ');
CALL tspBuscarMedico(10, '');


SELECT * FROM MEDICO;


 -- Administracion de BAses de datos INicio de la materia 6to 
 
SHOW GRANTS FOR "edsigler"@"localhost";
GRANT CREATE ON *.* TO "edsigler"@"localhost";  -- SOLO EL USUARIO PUEDE CREAR UNA BASE DE DATOS O UNA TABLA
FLUSH PRIVILEGES;

GRANT ALL PRIVILEGES ON *.* TO "edsigler"@"localhost"; -- EL USUARIO TIENE TODOS LOS PRIVILEGIOS
FLUSH PRIVILEGES;

REVOKE ALL PRIVILEGES ON *.* FROM "edsigler"@"localhost";  -- REVOCA TODOS LOS PRIVILEGIOS AL USUARIO
FLUSH PRIVILEGES;


GRANT SELECT, INSERT ON bd_citas.MEDICO TO "edsigler"@"localhost";  -- ASIGNA AL USUARIO ALGUN PERMISO ESPECIFICO CONSULTAR E INSERTAR
FLUSH PRIVILEGES;


ALTER USER "edsigler"@"localhost" IDENTIFIED BY "12345chapo"; -- CAMBIA LA CONTRASEÑA DEL USUARIO
FLUSH PRIVILEGES;

DROP USER "edsigler"@"localhost";