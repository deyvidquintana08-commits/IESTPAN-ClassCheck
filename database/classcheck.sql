CREATE DATABASE IF NOT EXISTS classcheck CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE classcheck;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS auditoria;
DROP TABLE IF EXISTS solicitudes_reapertura;
DROP TABLE IF EXISTS plazos_registro;
DROP TABLE IF EXISTS calificaciones;
DROP TABLE IF EXISTS asistencias;
DROP TABLE IF EXISTS horarios;
DROP TABLE IF EXISTS asignaciones_docentes;
DROP TABLE IF EXISTS matriculas;
DROP TABLE IF EXISTS unidades_didacticas;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS carreras;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE carreras (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(180) NOT NULL UNIQUE,
 activa TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE usuarios (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombres VARCHAR(100) NOT NULL,
 apellidos VARCHAR(100) NOT NULL,
 dni VARCHAR(12) NULL UNIQUE,
 correo VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 rol ENUM('administrador','docente','alumno') NOT NULL,
 estado ENUM('pendiente','activo','inactivo') NOT NULL DEFAULT 'activo',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_usuarios_rol_estado(rol,estado)
) ENGINE=InnoDB;

CREATE TABLE matriculas (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 alumno_id INT UNSIGNED NOT NULL,
 carrera_id INT UNSIGNED NOT NULL,
 modulo_num TINYINT UNSIGNED NOT NULL,
 ciclo_num TINYINT UNSIGNED NOT NULL,
 estado ENUM('activa','retirada','finalizada') NOT NULL DEFAULT 'activa',
 creada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_matricula_alumno FOREIGN KEY(alumno_id) REFERENCES usuarios(id) ON DELETE CASCADE,
 CONSTRAINT fk_matricula_carrera FOREIGN KEY(carrera_id) REFERENCES carreras(id),
 CONSTRAINT chk_modulo CHECK(modulo_num BETWEEN 1 AND 3),
 CONSTRAINT chk_ciclo CHECK(ciclo_num BETWEEN 1 AND 2),
 UNIQUE KEY uq_matricula_activa_contexto(alumno_id, carrera_id, modulo_num, ciclo_num, estado)
) ENGINE=InnoDB;

CREATE TABLE unidades_didacticas (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 carrera_id INT UNSIGNED NOT NULL,
 modulo_num TINYINT UNSIGNED NOT NULL,
 ciclo_num TINYINT UNSIGNED NOT NULL,
 nombre VARCHAR(150) NOT NULL,
 activa TINYINT(1) NOT NULL DEFAULT 1,
 CONSTRAINT fk_unidad_carrera FOREIGN KEY(carrera_id) REFERENCES carreras(id),
 CONSTRAINT chk_unidad_modulo CHECK(modulo_num BETWEEN 1 AND 3),
 CONSTRAINT chk_unidad_ciclo CHECK(ciclo_num BETWEEN 1 AND 2),
 UNIQUE KEY uq_unidad(nombre,carrera_id,modulo_num,ciclo_num)
) ENGINE=InnoDB;

CREATE TABLE asignaciones_docentes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 docente_id INT UNSIGNED NOT NULL,
 unidad_id INT UNSIGNED NOT NULL,
 asignada_por INT UNSIGNED NULL,
 creada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(docente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
 FOREIGN KEY(unidad_id) REFERENCES unidades_didacticas(id) ON DELETE CASCADE,
 FOREIGN KEY(asignada_por) REFERENCES usuarios(id) ON DELETE SET NULL,
 UNIQUE KEY uq_docente_unidad(docente_id,unidad_id)
) ENGINE=InnoDB;

CREATE TABLE horarios (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 unidad_id INT UNSIGNED NOT NULL,
 docente_id INT UNSIGNED NOT NULL,
 carrera_id INT UNSIGNED NOT NULL,
 modulo_num TINYINT UNSIGNED NOT NULL,
 ciclo_num TINYINT UNSIGNED NOT NULL,
 dia_semana TINYINT UNSIGNED NOT NULL,
 hora_inicio TIME NOT NULL,
 hora_fin TIME NOT NULL,
 aula VARCHAR(50) NOT NULL,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(unidad_id) REFERENCES unidades_didacticas(id),
 FOREIGN KEY(docente_id) REFERENCES usuarios(id),
 FOREIGN KEY(carrera_id) REFERENCES carreras(id),
 CHECK(dia_semana BETWEEN 1 AND 7),
 CHECK(hora_inicio < hora_fin),
 INDEX idx_horario_docente_dia(docente_id,dia_semana,hora_inicio),
 INDEX idx_horario_grupo(carrera_id,modulo_num,ciclo_num,dia_semana)
) ENGINE=InnoDB;

CREATE TABLE plazos_registro (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 unidad_id INT UNSIGNED NOT NULL,
 docente_id INT UNSIGNED NOT NULL,
 fecha_clase DATE NOT NULL,
 tipo ENUM('asistencia','notas') NOT NULL,
 abierto_en DATETIME NOT NULL,
 vencimiento DATETIME NOT NULL,
 estado ENUM('abierto','reabierto','cerrado') NOT NULL DEFAULT 'abierto',
 abierto_por INT UNSIGNED NULL,
 reabierto_por INT UNSIGNED NULL,
 reabierto_en DATETIME NULL,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(unidad_id) REFERENCES unidades_didacticas(id),
 FOREIGN KEY(docente_id) REFERENCES usuarios(id),
 FOREIGN KEY(abierto_por) REFERENCES usuarios(id) ON DELETE SET NULL,
 FOREIGN KEY(reabierto_por) REFERENCES usuarios(id) ON DELETE SET NULL,
 UNIQUE KEY uq_plazo(unidad_id,docente_id,fecha_clase,tipo),
 INDEX idx_plazo_vencimiento(vencimiento,estado)
) ENGINE=InnoDB;

CREATE TABLE asistencias (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 plazo_id INT UNSIGNED NOT NULL,
 alumno_id INT UNSIGNED NOT NULL,
 estado ENUM('presente','ausente','tardanza') NOT NULL,
 registrado_por INT UNSIGNED NOT NULL,
 registrado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(plazo_id) REFERENCES plazos_registro(id),
 FOREIGN KEY(alumno_id) REFERENCES usuarios(id),
 FOREIGN KEY(registrado_por) REFERENCES usuarios(id),
 UNIQUE KEY uq_asistencia_alumno_plazo(plazo_id,alumno_id)
) ENGINE=InnoDB;

CREATE TABLE calificaciones (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 plazo_id INT UNSIGNED NOT NULL,
 alumno_id INT UNSIGNED NOT NULL,
 concepto VARCHAR(100) NOT NULL,
 nota DECIMAL(5,2) NOT NULL,
 registrado_por INT UNSIGNED NOT NULL,
 registrado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(plazo_id) REFERENCES plazos_registro(id),
 FOREIGN KEY(alumno_id) REFERENCES usuarios(id),
 FOREIGN KEY(registrado_por) REFERENCES usuarios(id),
 CHECK(nota >= 0 AND nota <= 20),
 UNIQUE KEY uq_nota_concepto(plazo_id,alumno_id,concepto)
) ENGINE=InnoDB;

CREATE TABLE solicitudes_reapertura (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 plazo_id INT UNSIGNED NOT NULL,
 docente_id INT UNSIGNED NOT NULL,
 motivo TEXT NOT NULL,
 estado ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 decidido_por INT UNSIGNED NULL,
 decidido_en DATETIME NULL,
 horas_concedidas SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 decision_nota VARCHAR(500) NULL,
 FOREIGN KEY(plazo_id) REFERENCES plazos_registro(id),
 FOREIGN KEY(docente_id) REFERENCES usuarios(id),
 FOREIGN KEY(decidido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
 INDEX idx_solicitudes_estado(estado,creado_en)
) ENGINE=InnoDB;

CREATE TABLE auditoria (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id INT UNSIGNED NULL,
 accion VARCHAR(80) NOT NULL,
 detalles VARCHAR(1000) NULL,
 ip VARCHAR(45) NULL,
 user_agent VARCHAR(250) NULL,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
 INDEX idx_auditoria_fecha(creado_en),
 INDEX idx_auditoria_usuario(usuario_id)
) ENGINE=InnoDB;

INSERT INTO carreras(nombre) VALUES
('Arquitectura de Plataformas y Tecnologías de la Información'),
('Enfermería Técnica'),
('Fisioterapia y Rehabilitación');
