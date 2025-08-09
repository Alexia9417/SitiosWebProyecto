-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-08-2025 a las 04:48:36
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sitio`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CambiarRolUsuario` (IN `p_IdUsuario` INT, IN `p_NuevoIdTipoUsuario` INT)   BEGIN
    -- Verifica que el usuario exista
    IF EXISTS (
        SELECT 1 FROM usuario WHERE IdUsuario = p_IdUsuario
    ) THEN
        -- Verifica que el nuevo rol exista
        IF EXISTS (
            SELECT 1 FROM tipousuario WHERE IdTipoUsuario = p_NuevoIdTipoUsuario
        ) THEN
            -- Actualiza el rol
            UPDATE usuario
            SET IdTipoUsuario = p_NuevoIdTipoUsuario
            WHERE IdUsuario = p_IdUsuario;
        ELSE
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El nuevo IdTipoUsuario no existe en tipousuario';
        END IF;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El IdUsuario no existe';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertarTareaDesdeNotificacion` (IN `p_IdMesa` INT, IN `p_AvisoTipo` VARCHAR(255))   BEGIN
    DECLARE v_NumeroMesa INT;
    DECLARE v_DescripTarea VARCHAR(255);
    DECLARE v_IdMesero INT;

    -- Obtener el número de la mesa
    SELECT Numero INTO v_NumeroMesa
    FROM Mesa
    WHERE IdMesa = p_IdMesa;

    -- Obtener el idMesero de la mesa
    SELECT IdMesero INTO v_IdMesero
    FROM Mesa
    WHERE IdMesa = p_IdMesa;

    -- Determinar la descripción de la tarea basada en el tipo de aviso
    IF p_AvisoTipo = 'Pedir agua' THEN
        SET v_DescripTarea = CONCAT('La Mesa #', v_NumeroMesa, ' pide Agua');
    ELSEIF p_AvisoTipo = 'Llamar mesero' THEN
        SET v_DescripTarea = CONCAT('La Mesa #', v_NumeroMesa, ' pide asistencia');
    END IF;

    -- Insertar la tarea en la tabla Tareas
    INSERT INTO Tareas (DescripTarea, IdMesa, Estado, TiempoEstimado, idMesero)
    VALUES (v_DescripTarea, p_IdMesa, 'Pendiente', 10, v_IdMesero); -- TiempoEstimado es un valor de ejemplo
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_afectar_kpis` (IN `p_chef_id` BIGINT, IN `p_metric` VARCHAR(50), IN `p_cantidad` INT, IN `p_tipo` VARCHAR(10))   BEGIN
    DECLARE v_valor_actual DECIMAL(10,2);

    SELECT Valor
    INTO v_valor_actual
    FROM kpi_medicion
    WHERE IdChef    = p_chef_id
      AND Nombre    = p_metric
    ORDER BY ts DESC
    LIMIT 1;

    IF p_tipo = 'sumar' THEN
        UPDATE kpi_medicion
        SET Valor = LEAST(100, v_valor_actual + p_cantidad),
            ts    = NOW()
        WHERE IdChef = p_chef_id
          AND Nombre = p_metric;
    ELSEIF p_tipo = 'restar' THEN
        UPDATE kpi_medicion
        SET Valor = GREATEST(0, v_valor_actual - p_cantidad),
            ts    = NOW()
        WHERE IdChef = p_chef_id
          AND Nombre = p_metric;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cambiar_rol_suario` (IN `p_IdUsuario` INT, IN `p_NuevoIdTipoUsuario` INT)   BEGIN
    -- Verifica que el usuario exista
    IF EXISTS (
        SELECT 1 FROM usuario WHERE IdUsuario = p_IdUsuario
    ) THEN
        -- Verifica que el nuevo rol exista
        IF EXISTS (
            SELECT 1 FROM tipousuario WHERE IdTipoUsuario = p_NuevoIdTipoUsuario
        ) THEN
            -- Actualiza el rol
            UPDATE usuario
            SET IdTipoUsuario = p_NuevoIdTipoUsuario
            WHERE IdUsuario = p_IdUsuario;
        ELSE
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El nuevo IdTipoUsuario no existe en tipousuario';
        END IF;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El IdUsuario no existe';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_completar_solicitud_chef` (IN `p_solicitud_id` BIGINT)   BEGIN
  DECLARE v_chef_id    BIGINT;
  DECLARE v_accion_id  INT;
  DECLARE v_estres     INT;
  DECLARE v_energia    INT;
  DECLARE v_conc       INT;

  -- 1) Leer chef y acción sólo si está pendiente
  SELECT IdChef, IdAccion
  INTO v_chef_id, v_accion_id
  FROM accion_chef
  WHERE IdAccionChef = p_solicitud_id
    AND Estado       = 'pendiente';

  -- 2) Marcar como completada y anotar FinAccion
  UPDATE accion_chef
  SET Estado     = 'completada',
      FinAccion  = NOW()
  WHERE IdAccionChef = p_solicitud_id;

  -- 3) Leer impactos desde el catálogo de acciones
  SELECT Estres, Energia, Concentracion
  INTO v_estres, v_energia, v_conc
  FROM accion
  WHERE IdAccion = v_accion_id;

  -- 4) Aplicar impactos a los 3 KPIs
  UPDATE kpi_medicion
  SET Valor = GREATEST(0, LEAST(100, Valor + v_estres)),
      ts    = NOW()
  WHERE IdChef = v_chef_id
    AND Nombre = 'estres';

  UPDATE kpi_medicion
  SET Valor = GREATEST(0, LEAST(100, Valor + v_energia)),
      ts    = NOW()
  WHERE IdChef = v_chef_id
    AND Nombre = 'energia';

  UPDATE kpi_medicion
  SET Valor = GREATEST(0, LEAST(100, Valor + v_conc)),
      ts    = NOW()
  WHERE IdChef = v_chef_id
    AND Nombre = 'concentracion';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_gestionar_orden_plato_estacion_simple` (IN `p_opcion` TINYINT, IN `p_orden_id` BIGINT, IN `p_estado_plato_id` INT)   BEGIN
    DECLARE done              INT DEFAULT FALSE;
    DECLARE v_idDetalle       BIGINT;
    DECLARE v_estacion_id     INT;
    DECLARE v_tiempo          INT;
    DECLARE v_chef_id         BIGINT;

    DECLARE cur CURSOR FOR
      SELECT do2.IdDetalle, p.IdEstacion, p.Tiempo
      FROM detalle_orden AS do2
      JOIN platillo      AS p  ON do2.IdPlatillo = p.IdPlatillo
      WHERE do2.IdOrden = p_orden_id;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;
    read_loop: LOOP
      FETCH cur INTO v_idDetalle, v_estacion_id, v_tiempo;
      IF done THEN LEAVE read_loop; END IF;

      -- Elegir chef aleatorio para esa estación
      SELECT ce.IdChef
      INTO v_chef_id
      FROM chef_estacion AS ce
      WHERE ce.IdEstacion = v_estacion_id
      ORDER BY RAND()
      LIMIT 1;

      IF p_opcion = 1 THEN
        INSERT INTO orden_plato_estacion (
          IdOrdenPlatillo, IdEstacion, IdPlatoEstado,
          TiempoEstimado, FinPreparacion, IdChef
        ) VALUES (
          v_idDetalle, v_estacion_id, p_estado_plato_id,
          v_tiempo, NULL, v_chef_id
        );

        CALL sp_afectar_kpis(v_chef_id, 'estres', FLOOR(RAND()*10+1), 'sumar');
        CALL sp_afectar_kpis(v_chef_id, 'energia', FLOOR(RAND()*8+1), 'restar');
        CALL sp_afectar_kpis(v_chef_id, 'concentracion', FLOOR(RAND()*5+1), 'restar');

      ELSEIF p_opcion = 2 THEN
        UPDATE orden_plato_estacion
        SET IdPlatoEstado   = p_estado_plato_id,
            FinPreparacion = DATE_ADD(NOW(), INTERVAL TiempoEstimado SECOND)
        WHERE IdOrdenPlatillo = v_idDetalle
          AND IdEstacion      = v_estacion_id;

        SELECT IdChef INTO v_chef_id
        FROM orden_plato_estacion
        WHERE IdOrdenPlatillo = v_idDetalle
          AND IdEstacion      = v_estacion_id
        LIMIT 1;

        CALL sp_afectar_kpis(v_chef_id, 'estres', FLOOR(RAND()*5+1), 'sumar');
        CALL sp_afectar_kpis(v_chef_id, 'energia', FLOOR(RAND()*4+1), 'restar');
        CALL sp_afectar_kpis(v_chef_id, 'concentracion', FLOOR(RAND()*3+1), 'restar');
      END IF;
    END LOOP;
    CLOSE cur;

    -- Sincronizar estados en detalle_orden
    UPDATE detalle_orden
    SET IdEstadoPlatillo = p_estado_plato_id
    WHERE IdOrden = p_orden_id;

    -- Si es nueva asignación, pasar orden a EnCurso
    IF p_opcion = 1 THEN
      UPDATE orden
      SET Estado = 'EnCurso'
      WHERE IdOrden = p_orden_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GestionMeseroArea` (IN `p_IdArea` INT, IN `p_IdUsuario` INT, IN `p_Asignar` TINYINT(1))   BEGIN
    -- 1) Validaciones comunes
    IF NOT EXISTS (SELECT 1 FROM area WHERE IdArea = p_IdArea) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Área no existe';
    END IF;
    IF NOT EXISTS (
        SELECT 1 
        FROM usuario 
        WHERE IdUsuario = p_IdUsuario 
          AND IdTipoUsuario = 2
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Usuario no existe o no es mesero';
    END IF;

    -- 2) Lógica de asignar vs desasignar
    IF p_Asignar = 1 THEN
        -- Inserta vínculo (si no existe)
        INSERT IGNORE INTO area_mesero (IdArea, IdUsuario)
            VALUES (p_IdArea, p_IdUsuario);
        -- Propaga asignación a todas las mesas de esa área
        UPDATE mesa
           SET IdMesero = p_IdUsuario
         WHERE IdArea = p_IdArea;
    ELSE
        -- Elimina vínculo (si existe), sin lanzar error
        DELETE IGNORE FROM area_mesero
         WHERE IdArea = p_IdArea
           AND IdUsuario = p_IdUsuario;
        -- Quita mesero de las mesas de esa área
        UPDATE mesa
           SET IdMesero = NULL
         WHERE IdArea = p_IdArea
           AND IdMesero = p_IdUsuario;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_iniciar_solicitud_rapida` (IN `p_chef_id` INT, IN `p_nombre` VARCHAR(50))   main: BEGIN
    DECLARE v_accion_id INT;
    DECLARE v_duracion  INT;
    DECLARE v_fin       DATETIME;
    DECLARE v_habilitar DATETIME;

    -- Obtener ID y duración de la acción
    SELECT IdAccion, Duracion
    INTO v_accion_id, v_duracion
    FROM accion
    WHERE Nombre = p_nombre
    LIMIT 1;

    -- Verificar si ya existe una solicitud pendiente para esta acción
    IF EXISTS (
        SELECT 1
        FROM accion_chef ac
        WHERE ac.IdChef = p_chef_id
          AND ac.IdAccion = v_accion_id
          AND ac.Estado = 'pendiente'
    ) THEN
        -- No insertar si ya existe una pendiente
        LEAVE main;
    END IF;

    -- Calcular fechas
    SET v_fin       = DATE_ADD(NOW(), INTERVAL v_duracion SECOND);
    SET v_habilitar = DATE_ADD(v_fin, INTERVAL (v_duracion * 3) SECOND);

    -- Insertar nueva acción
    INSERT INTO accion_chef (
        IdChef, IdAccion, Tipo, Estado,
        InicioAccion, FinAccion, Habilitar
    ) VALUES (
        p_chef_id, v_accion_id, 'rapida', 'pendiente',
        NOW(), v_fin, v_habilitar
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_kpis_por_chef_id` (IN `p_chef_id` BIGINT)   BEGIN
  SELECT 
    u.IdUsuario     AS chef_id,
    CONCAT(u.Nombre, ' ', u.Apellidos) AS nombre_completo,
    k.Nombre        AS kpi,
    k.Valor,
    k.ts            AS fecha,
    k.Minimo as min,
    CASE 
      WHEN k.Nombre = 'estres'        THEN IF(k.Valor < 60, 'Normal', 'Alto')
      WHEN k.Nombre = 'energia'       THEN IF(k.Valor > 40, 'Normal', 'Baja')
      WHEN k.Nombre = 'concentracion' THEN IF(k.Valor > 50, 'Normal', 'Baja')
      ELSE 'Desconocido'
    END AS estado
  FROM usuario u
  JOIN (
    SELECT km1.*
    FROM kpi_medicion km1
    JOIN (
      SELECT IdChef, Nombre, MAX(ts) AS max_ts
      FROM kpi_medicion
      GROUP BY IdChef, Nombre
    ) km2
      ON km1.IdChef = km2.IdChef
     AND km1.Nombre = km2.Nombre
     AND km1.ts     = km2.max_ts
  ) k ON u.IdUsuario = k.IdChef
  WHERE u.IdTipoUsuario = (
    SELECT IdTipoUsuario 
    FROM tipousuario 
    WHERE Nombre = 'Chef' 
    LIMIT 1
  )
    AND u.IdUsuario = p_chef_id
  ORDER BY k.Nombre;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_marcar_platillo_listo` (IN `p_id_orden` INT, IN `p_id_platillo` INT)   BEGIN
    DECLARE v_id_detalle INT;
    DECLARE v_estado_actual INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- Obtener el IdDetalle (detalle_orden)
    SELECT IdDetalle, IdEstadoPlatillo
    INTO v_id_detalle, v_estado_actual
    FROM detalle_orden
    WHERE IdOrden = p_id_orden AND IdPlatillo = p_id_platillo
    LIMIT 1;


    -- 1. Actualizar detalle_orden a 'Listo'
    UPDATE detalle_orden
    SET IdEstadoPlatillo = 4
    WHERE IdDetalle = v_id_detalle;

    -- 2. Actualizar orden_plato_estacion a 'Listo'
    UPDATE orden_plato_estacion
    SET IdPlatoEstado = 4
    WHERE IdOrdenPlatillo = v_id_detalle;

    -- 3. Eliminar registros en orden_plato_estacion con estado 'Listo' (4)
    DELETE FROM orden_plato_estacion
    WHERE IdOrdenPlatillo = v_id_detalle AND IdPlatoEstado = 4;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_order_report` (IN `p_option` TINYINT, IN `p_order_id` BIGINT)   BEGIN
  IF p_option = 1 THEN
    SELECT
      o.IdOrden                    AS NOrden,
      o.IdMesa                     AS NMesa,
      TIME(o.Fecha)                AS Llego,
      (SUM(p.Tiempo * do2.Cantidad)) AS TiempoTotal,
      o.Estado                     AS Estado
    FROM orden o
    LEFT JOIN detalle_orden do2 ON o.IdOrden = do2.IdOrden
    LEFT JOIN platillo p         ON do2.IdPlatillo = p.IdPlatillo
    GROUP BY o.IdOrden, o.Fecha, o.Estado
    ORDER BY o.Fecha DESC;

  ELSEIF p_option = 2 THEN
    IF p_order_id IS NULL OR p_order_id = 0 THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Para p_option = 2 debes proporcionar un p_order_id válido';
    END IF;
    SELECT
      do2.IdPlatillo             AS IDPlato,
      p.Nombre                   AS NombrePlato,
      do2.Cantidad               AS Cantidad,
      ep.Nombre                  AS EstadoPlato,
      p.Tiempo                   AS TiempoEstimadoPorUnidad
    FROM detalle_orden do2
    JOIN platillo p     ON do2.IdPlatillo       = p.IdPlatillo
    JOIN estado_plato ep ON do2.IdEstadoPlatillo = ep.IdEstadoPlato
    WHERE do2.IdOrden = p_order_id
    ORDER BY do2.IdDetalle;
  ELSE
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Opción inválida: usa 1 para resumen, 2 para detalle';
  END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_mensaje_empleado` (IN `p_IdEmpleado` INT, IN `p_IdAccionChef` INT, IN `p_Estado` VARCHAR(75))   BEGIN
    INSERT INTO mensaje_empleado
      (IdEmpleado, IdAccion, Estado)
    VALUES
      (p_IdEmpleado, p_IdAccionChef, p_Estado);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accion`
--

CREATE TABLE `accion` (
  `IdAccion` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Duracion` int(11) DEFAULT 5,
  `Estres` int(11) DEFAULT 0,
  `Energia` int(11) DEFAULT 0,
  `Concentracion` int(11) DEFAULT 0,
  `Tipo` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `accion`
--

INSERT INTO `accion` (`IdAccion`, `Nombre`, `Duracion`, `Estres`, `Energia`, `Concentracion`, `Tipo`) VALUES
(1, 'Tomar agua', 5, -2, 2, 4, 'rapido'),
(2, 'Comer algo', 6, -5, 10, 4, 'rapido'),
(3, 'Descanso corto', 5, -8, 5, 5, 'rapido'),
(4, 'Estiramiento físico', 5, -4, 0, -3, 'rapido'),
(5, 'Respirar profundo', 5, 0, -2, -6, 'rapido'),
(6, 'Pedir Descanso', 40, -12, 5, 3, 'emergencia'),
(7, 'Salida Anticipada', 5, -1, -1, -1, 'emergencia'),
(8, 'Asistencia Medica', 1, 20, -30, -40, 'emergencia'),
(9, 'Ayudante Adicional', 1, 10, -10, -15, 'emergencia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accioncliente`
--

CREATE TABLE `accioncliente` (
  `IdAccion` int(11) NOT NULL,
  `TipoAccion` varchar(50) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `FechaHora` datetime NOT NULL,
  `MontoPropina` decimal(10,2) DEFAULT NULL,
  `IdMesero` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `accioncliente`
--

INSERT INTO `accioncliente` (`IdAccion`, `TipoAccion`, `IdUsuario`, `FechaHora`, `MontoPropina`, `IdMesero`) VALUES
(1, 'Dejar Propina', 5, '2025-08-04 09:29:22', 100.00, 6),
(2, 'Dejar Propina', 5, '2025-08-04 11:39:50', 20.00, 6),
(3, 'Dejar Propina', 5, '2025-08-04 11:58:12', 0.00, 6),
(4, 'Dejar Propina', 5, '2025-08-04 12:01:15', 20.00, 6),
(5, 'Dejar Propina', 5, '2025-08-04 12:04:23', 0.00, 6),
(6, 'Dejar Propina', 5, '2025-08-04 12:12:41', 10.00, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accion_chef`
--

CREATE TABLE `accion_chef` (
  `IdAccionChef` int(11) NOT NULL,
  `IdChef` int(11) NOT NULL,
  `IdAccion` int(11) NOT NULL,
  `Tipo` varchar(45) DEFAULT 'rapido',
  `Estado` enum('pendiente','aprobada','rechazada','completada') DEFAULT 'pendiente',
  `InicioAccion` datetime DEFAULT current_timestamp(),
  `FinAccion` datetime DEFAULT NULL,
  `Habilitar` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `accion_chef`
--

INSERT INTO `accion_chef` (`IdAccionChef`, `IdChef`, `IdAccion`, `Tipo`, `Estado`, `InicioAccion`, `FinAccion`, `Habilitar`) VALUES
(56, 1, 2, 'rapido', 'completada', '2025-08-01 12:07:19', '2025-08-01 12:08:19', NULL),
(57, 1, 2, 'rapido', 'completada', '2025-08-01 12:08:43', '2025-08-01 12:09:43', NULL),
(58, 1, 1, 'rapido', 'completada', '2025-08-02 10:51:54', '2025-08-03 21:23:59', NULL),
(59, 1, 2, 'rapido', 'completada', '2025-08-03 20:07:23', '2025-08-03 20:08:23', NULL),
(60, 1, 1, 'rapida', 'completada', '2025-08-03 21:26:56', '2025-08-03 21:27:56', NULL),
(61, 1, 1, 'rapida', 'completada', '2025-08-03 21:28:01', '2025-08-03 21:29:01', NULL),
(62, 1, 8, 'rapida', 'completada', '2025-08-03 21:34:42', '2025-08-03 21:35:42', NULL),
(63, 1, 7, 'rapida', 'completada', '2025-08-03 21:36:08', '2025-08-03 21:41:08', NULL),
(64, 1, 2, 'rapida', 'completada', '2025-08-03 21:45:30', '2025-08-04 09:36:35', NULL),
(65, 1, 1, 'rapida', 'completada', '2025-08-04 09:26:36', '2025-08-04 09:36:31', NULL),
(66, 1, 2, 'rapida', 'completada', '2025-08-04 09:36:39', '2025-08-04 09:37:39', NULL),
(67, 1, 2, 'rapida', 'completada', '2025-08-04 09:37:49', '2025-08-04 09:38:49', NULL),
(68, 1, 4, 'rapida', 'completada', '2025-08-04 10:00:51', '2025-08-04 10:01:51', NULL),
(69, 1, 1, 'rapida', 'completada', '2025-08-04 10:02:04', '2025-08-04 10:02:05', NULL),
(70, 1, 1, 'rapida', 'completada', '2025-08-04 10:05:03', '2025-08-04 10:05:04', NULL),
(71, 1, 2, 'rapida', 'completada', '2025-08-04 10:05:28', '2025-08-04 10:05:29', NULL),
(72, 1, 1, 'rapida', 'completada', '2025-08-04 10:07:03', '2025-08-04 10:07:13', NULL),
(73, 1, 1, 'rapida', 'completada', '2025-08-07 10:51:59', '2025-08-07 10:52:01', NULL),
(74, 1, 1, 'rapida', 'completada', '2025-08-07 15:56:23', '2025-08-07 15:56:25', NULL),
(75, 1, 3, 'rapida', 'completada', '2025-08-07 15:56:34', '2025-08-07 15:56:40', NULL),
(76, 1, 2, 'rapida', 'completada', '2025-08-07 15:56:54', '2025-08-07 15:57:00', NULL),
(77, 1, 1, 'rapida', 'completada', '2025-08-07 15:59:32', '2025-08-07 15:59:38', NULL),
(78, 1, 1, 'rapida', 'completada', '2025-08-07 16:01:52', '2025-08-07 16:01:57', NULL),
(79, 1, 1, 'rapida', 'completada', '2025-08-07 16:03:25', '2025-08-07 16:03:30', NULL),
(80, 1, 2, 'rapida', 'completada', '2025-08-07 16:04:52', '2025-08-07 16:04:58', NULL),
(81, 1, 1, 'rapida', 'completada', '2025-08-07 16:05:38', '2025-08-07 16:05:43', NULL),
(82, 1, 1, 'rapida', 'completada', '2025-08-07 16:06:00', '2025-08-07 16:06:05', NULL),
(83, 1, 2, 'rapida', 'completada', '2025-08-07 16:26:51', '2025-08-07 16:26:57', NULL),
(84, 1, 1, 'rapida', 'completada', '2025-08-07 17:57:48', '2025-08-07 17:57:53', NULL),
(85, 1, 3, 'rapida', 'completada', '2025-08-07 17:58:08', '2025-08-07 17:58:13', NULL),
(86, 1, 6, 'rapida', 'completada', '2025-08-07 18:40:20', '2025-08-07 18:41:03', NULL),
(87, 1, 7, 'rapida', 'completada', '2025-08-07 18:41:09', '2025-08-07 18:41:15', NULL),
(88, 1, 7, 'rapida', 'completada', '2025-08-07 18:41:14', '2025-08-07 18:41:19', NULL),
(89, 1, 6, 'rapida', 'completada', '2025-08-07 20:41:31', '2025-08-07 20:42:11', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area`
--

CREATE TABLE `area` (
  `IdArea` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `area`
--

INSERT INTO `area` (`IdArea`, `Nombre`) VALUES
(1, 'Area Norte'),
(2, 'Area Sur'),
(4, 'Area Vip'),
(3, 'Terraza');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area_mesero`
--

CREATE TABLE `area_mesero` (
  `IdArea` int(11) NOT NULL,
  `IdUsuario` int(11) DEFAULT NULL,
  `Estado` varchar(45) DEFAULT 'Sin Mesero'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `area_mesero`
--

INSERT INTO `area_mesero` (`IdArea`, `IdUsuario`, `Estado`) VALUES
(1, 2, 'Sin Mesero'),
(2, 2, 'Sin Mesero'),
(3, 2, 'Sin Mesero'),
(4, 2, 'Sin Mesero');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificacion`
--

CREATE TABLE `calificacion` (
  `IdCalificacion` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `Estrellas` int(11) DEFAULT NULL,
  `Comentario` varchar(100) DEFAULT NULL,
  `FechaHora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `calificacion`
--

INSERT INTO `calificacion` (`IdCalificacion`, `IdUsuario`, `Estrellas`, `Comentario`, `FechaHora`) VALUES
(1, 1, 4, 'Hola esta es una prueba', '2025-07-29 05:34:52'),
(2, 5, 3, 'd', '2025-08-04 19:48:28'),
(4, 6, 4, 'Hola esta es una prueba', '2025-07-29 05:34:52'),
(11, 2, 5, 'Excelente atención, muy recomendable.', '2025-07-30 10:15:20'),
(12, 3, 3, 'Todo bien, aunque podría mejorar el tiempo de espera.', '2025-07-30 14:22:10'),
(13, 4, 1, 'La experiencia no fue buena, no lo recomendaría.', '2025-07-31 08:45:05'),
(14, 5, 4, 'Buena comida, pero un poco caro para lo que es.', '2025-07-31 13:11:42'),
(15, 6, 5, '¡Fantástico! Sin duda volveré.', '2025-08-01 17:55:33'),
(16, 7, 2, 'El servicio fue lento y la comida fría.', '2025-08-02 09:03:58'),
(17, 10, 4, 'Me gusta mucho el ambiente', '2025-08-09 03:34:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `IdCategoria` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`IdCategoria`, `Nombre`) VALUES
(1, 'Entradas'),
(2, 'Ensaladas'),
(3, 'Pastas'),
(4, 'Pizzas'),
(5, 'Carnes'),
(6, 'Guarniciones'),
(7, 'Postres'),
(8, 'Bebidas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chef_estacion`
--

CREATE TABLE `chef_estacion` (
  `IdChef` int(11) NOT NULL,
  `IdEstacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chef_estacion`
--

INSERT INTO `chef_estacion` (`IdChef`, `IdEstacion`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 4),
(2, 5),
(2, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden`
--

CREATE TABLE `detalle_orden` (
  `IdDetalle` int(11) NOT NULL,
  `IdOrden` int(11) NOT NULL,
  `IdPlatillo` int(11) NOT NULL,
  `Cantidad` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `IdEstadoPlatillo` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Disparadores `detalle_orden`
--
DELIMITER $$
CREATE TRIGGER `trg_cerrar_orden` AFTER UPDATE ON `detalle_orden` FOR EACH ROW BEGIN
  -- Si el plato acaba de pasar al estado 4 ("Listo")
  IF NEW.IdEstadoPlatillo = 4 THEN
    -- Verificar que no queden otros platos pendientes o en curso
    IF NOT EXISTS (
      SELECT 1
      FROM `detalle_orden`
      WHERE IdOrden = NEW.IdOrden
        AND IdEstadoPlatillo <> 4
    ) THEN
      -- Marcar la orden como "Listo"
      UPDATE `orden`
      SET Estado = 'Listo'
      WHERE IdOrden = NEW.IdOrden;
    END IF;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deudas`
--

CREATE TABLE `deudas` (
  `IdDeuda` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `Deuda` decimal(10,2) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estacion`
--

CREATE TABLE `estacion` (
  `IdEstacion` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Espacios` tinyint(3) UNSIGNED NOT NULL DEFAULT 2 COMMENT 'Número de platillos que puede preparar simultáneamente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estacion`
--

INSERT INTO `estacion` (`IdEstacion`, `Nombre`, `Espacios`) VALUES
(1, 'Horno', 4),
(2, 'Fría', 6),
(3, 'Parrilla', 5),
(4, 'Plancha', 5),
(5, 'Bebidas', 5),
(6, 'Freidora', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadomesero`
--

CREATE TABLE `estadomesero` (
  `IdMesero` int(11) NOT NULL,
  `Estres` decimal(5,2) NOT NULL,
  `Energia` decimal(5,2) NOT NULL,
  `Eficiencia` decimal(5,2) NOT NULL,
  `UltimaActualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_plato`
--

CREATE TABLE `estado_plato` (
  `IdEstadoPlato` int(11) NOT NULL,
  `Nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_plato`
--

INSERT INTO `estado_plato` (`IdEstadoPlato`, `Nombre`) VALUES
(3, 'Cocinando'),
(5, 'Entregado'),
(4, 'Listo'),
(1, 'No asignado'),
(2, 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kpi_medicion`
--

CREATE TABLE `kpi_medicion` (
  `IdKpi` int(11) NOT NULL,
  `IdChef` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Valor` decimal(5,2) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `Minimo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `kpi_medicion`
--

INSERT INTO `kpi_medicion` (`IdKpi`, `IdChef`, `Nombre`, `Valor`, `ts`, `Minimo`) VALUES
(1, 1, 'estres', 64.00, '2025-08-08 02:42:11', 90),
(2, 1, 'energia', 68.00, '2025-08-08 02:42:11', 15),
(3, 1, 'concentracion', 4.00, '2025-08-08 02:42:11', 20),
(4, 2, 'estres', 47.00, '2025-08-07 23:16:25', 90),
(5, 2, 'energia', 78.00, '2025-08-07 23:16:25', 15),
(6, 2, 'concentracion', 21.00, '2025-08-07 23:16:25', 20),
(7, 3, 'estres', 76.00, '2025-07-29 21:20:22', 90),
(8, 3, 'energia', 48.00, '2025-07-29 21:20:22', 15),
(9, 3, 'concentracion', 78.00, '2025-07-29 21:20:23', 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajegerencia`
--

CREATE TABLE `mensajegerencia` (
  `id` int(11) NOT NULL,
  `idMesero` int(11) NOT NULL,
  `Mensaje` varchar(255) NOT NULL,
  `LlamadaGerencia` tinyint(1) DEFAULT 0,
  `FechaHora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajegerencia`
--

INSERT INTO `mensajegerencia` (`id`, `idMesero`, `Mensaje`, `LlamadaGerencia`, `FechaHora`) VALUES
(2, 6, 'prueba 2', 0, '2025-08-04 09:06:18'),
(4, 6, 'hola', 0, '2025-08-04 09:11:19'),
(5, 6, 'Queja Escalada a gerencia: hola', 0, '2025-08-04 09:14:34'),
(7, 6, 'Mesero Maria está llamando a gerencia', 0, '2025-08-04 09:18:55'),
(8, 2, '', 0, '2025-08-08 09:11:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_cocina`
--

CREATE TABLE `mensajes_cocina` (
  `id` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `id_mesero` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `estado` varchar(50) DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensaje_empleado`
--

CREATE TABLE `mensaje_empleado` (
  `IdMensaje` int(11) NOT NULL,
  `IdEmpleado` int(11) NOT NULL,
  `IdAccion` int(11) NOT NULL,
  `FechaHora` datetime NOT NULL DEFAULT current_timestamp(),
  `Estado` varchar(75) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesa`
--

CREATE TABLE `mesa` (
  `IdMesa` int(11) NOT NULL,
  `Numero` int(11) NOT NULL,
  `Ubicacion` varchar(100) DEFAULT NULL,
  `Capacidad` int(11) NOT NULL,
  `IdCliente` int(11) DEFAULT NULL,
  `IdMesero` int(11) DEFAULT NULL,
  `IdArea` int(11) DEFAULT NULL,
  `MesaEstado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesa`
--

INSERT INTO `mesa` (`IdMesa`, `Numero`, `Ubicacion`, `Capacidad`, `IdCliente`, `IdMesero`, `IdArea`, `MesaEstado`) VALUES
(1, 1, 'Cerca de la ventana', 2, NULL, 5, 1, 0),
(2, 2, 'Esquina izquierda', 4, NULL, 2, 1, 1),
(3, 3, 'Centro del salón', 4, NULL, 2, 1, 0),
(4, 4, 'Terraza', 2, NULL, 5, 2, 1),
(5, 5, 'Cerca de la entrada', 6, NULL, 2, 2, 0),
(6, 6, 'Zona privada', 2, NULL, 2, 2, 0),
(7, 7, 'Esquina derecha', 4, NULL, 2, 2, 0),
(8, 8, 'Patio exterior', 2, NULL, 2, 3, 0),
(9, 9, 'Cerca del bar', 3, NULL, 2, 3, 0),
(10, 10, 'Ventana lateral', 4, NULL, 5, 3, 0),
(11, 11, 'Segundo piso', 6, NULL, 2, 4, 0),
(12, 12, 'Zona infantil', 5, NULL, 5, 4, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodopago`
--

CREATE TABLE `metodopago` (
  `IdPago` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `NombreTitular` varchar(100) NOT NULL,
  `NumeroTarjeta` varchar(20) NOT NULL,
  `FechaVenc` date NOT NULL,
  `Saldo` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodopago`
--

INSERT INTO `metodopago` (`IdPago`, `IdUsuario`, `NombreTitular`, `NumeroTarjeta`, `FechaVenc`, `Saldo`) VALUES
(2, 7, 'Stef C', '1234 1234 1235 5525', '2027-09-01', 50000.00),
(3, 10, 'Carlos Camacho', '5558 5848 4894 8448', '2028-09-01', 499945.55);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `IdNotificacion` int(11) NOT NULL,
  `Hora` datetime NOT NULL,
  `Descripcion` varchar(100) NOT NULL,
  `AvisoTipo` varchar(20) NOT NULL,
  `IdMesa` int(11) DEFAULT NULL,
  `IdUsuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificacion`
--

INSERT INTO `notificacion` (`IdNotificacion`, `Hora`, `Descripcion`, `AvisoTipo`, `IdMesa`, `IdUsuario`) VALUES
(1, '2025-08-04 11:51:51', 'Llamar Mesero', 'Llamar Mesero', 1, 1),
(2, '2025-08-04 12:22:23', 'Pedir agua', 'Pedir agua', 1, 1),
(3, '2025-08-04 22:53:03', 'Pedir agua', 'Pedir agua', 12, 7),
(4, '2025-08-04 16:13:18', 'Llamar Mesero', 'Llamar Mesero', 1, 1),
(5, '2025-08-04 16:13:38', 'Pedir agua', 'Pedir agua', 1, 1),
(8, '2025-08-04 17:40:18', 'Llamar Mesero', 'Llamar Mesero', 11, 7),
(9, '2025-08-07 21:02:12', 'Llamar Mesero', 'Llamar Mesero', 1, 2),
(10, '2025-08-07 21:02:14', 'Pedir agua', 'Pedir agua', 1, 2),
(11, '2025-08-07 21:02:19', 'Pedir agua', 'Pedir agua', 1, 2),
(12, '2025-08-08 19:35:11', 'Llamar Mesero', 'Llamar Mesero', 2, 10);

--
-- Disparadores `notificacion`
--
DELIMITER $$
CREATE TRIGGER `AfterInsertNotificacion` AFTER INSERT ON `notificacion` FOR EACH ROW BEGIN
    CALL InsertarTareaDesdeNotificacion(NEW.IdMesa, NEW.AvisoTipo);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden`
--

CREATE TABLE `orden` (
  `IdOrden` int(11) NOT NULL,
  `IdMesa` int(11) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `Estado` enum('Pendiente','','Listo','Cancelado','EnCurso') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_plato_estacion`
--

CREATE TABLE `orden_plato_estacion` (
  `IdOrdenPlatilloEstacion` int(11) NOT NULL,
  `IdOrdenPlatillo` int(11) NOT NULL,
  `IdEstacion` int(11) NOT NULL,
  `IdChef` int(11) DEFAULT NULL,
  `IdPlatoEstado` int(11) NOT NULL DEFAULT 1,
  `FinPreparacion` datetime DEFAULT NULL,
  `TiempoEstimado` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `IdPago` int(11) NOT NULL,
  `IdMesa` int(11) DEFAULT NULL,
  `Monto` decimal(10,2) DEFAULT NULL,
  `FechaPago` datetime DEFAULT NULL,
  `MetodoPago` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `platillo`
--

CREATE TABLE `platillo` (
  `IdPlatillo` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `IdEstacion` int(11) NOT NULL,
  `Tiempo` int(10) UNSIGNED NOT NULL,
  `IdCategoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `platillo`
--

INSERT INTO `platillo` (`IdPlatillo`, `Nombre`, `Precio`, `IdEstacion`, `Tiempo`, `IdCategoria`) VALUES
(1, 'Bruschetta', 4.50, 2, 20, 1),
(2, 'Caprese Salad', 5.00, 2, 21, 3),
(3, 'Antipasto Platter', 8.00, 2, 21, 1),
(4, 'Spaghetti Carbonara', 12.00, 3, 25, 3),
(5, 'Penne all’Arrabbiata', 11.00, 3, 37, 3),
(6, 'Gnocchi al Pomodoro', 10.50, 1, 51, 3),
(7, 'Lasagna al forno', 13.00, 1, 48, 3),
(8, 'Bistecca alla Fiorentina', 25.00, 3, 36, 5),
(9, 'Eggplant Parmigiana', 11.50, 1, 17, 3),
(10, 'Risotto ai Funghi', 14.00, 1, 38, 3),
(11, 'Spaghetti all’Assassina', 12.50, 3, 26, 3),
(12, 'Chicken Parmesan', 15.00, 1, 19, 3),
(13, 'Pesto Pasta', 11.00, 3, 34, 3),
(14, 'Tiramisu', 6.00, 2, 16, 7),
(15, 'Panna Cotta', 5.50, 2, 11, 7),
(16, 'Porchetta', 17.00, 3, 10, 3),
(17, 'Pollo alla Parmigiana', 16.00, 1, 16, 3),
(18, 'Ciambotta', 10.00, 1, 34, 3),
(19, 'Gnocchi al Gorgonzola', 12.00, 1, 24, 3),
(20, 'Tagliatelle Bolognese', 13.50, 3, 20, 3),
(21, 'Coca Cola', 2.00, 2, 5, 8),
(22, 'Papas fritas', 3.50, 4, 10, 6),
(23, 'Pizza Pepperoni', 8.00, 3, 45, 2),
(24, 'Hamburguesa Clásica', 7.00, 4, 13, 1),
(25, 'Cerveza', 3.00, 2, 5, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `queja`
--

CREATE TABLE `queja` (
  `IdQueja` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `Comentario` varchar(100) NOT NULL,
  `FechaHora` datetime NOT NULL,
  `Estado` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `queja`
--

INSERT INTO `queja` (`IdQueja`, `IdUsuario`, `Comentario`, `FechaHora`, `Estado`) VALUES
(1, 6, 'Cliente en la mesa 3 no esta coperando', '2025-08-04 09:13:16', 'Pendiente de revisar'),
(2, 6, 'Cliente en la mesa 5 esta cumpliendo años', '2025-08-04 09:14:13', 'Pendiente de revisar'),
(3, 6, 'hola', '2025-08-04 09:14:20', 'Pendiente de revisar'),
(4, 6, 'prueba 4', '2025-08-04 09:19:43', 'Pendiente de revisar'),
(5, 6, 'prueba 5 borrado', '2025-08-04 09:20:27', 'Pendiente de revisar'),
(7, 5, 'sd', '2025-08-04 19:51:48', 'Pendiente de Revisar'),
(8, 10, 'que feo lugar', '2025-08-09 03:35:56', 'Pendiente de Revisar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registrohoras`
--

CREATE TABLE `registrohoras` (
  `IdRegistro` int(11) NOT NULL,
  `IdMesero` int(11) NOT NULL,
  `HoraEntrada` datetime NOT NULL,
  `HoraSalida` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registrohoras`
--

INSERT INTO `registrohoras` (`IdRegistro`, `IdMesero`, `HoraEntrada`, `HoraSalida`) VALUES
(1, 6, '2025-08-04 09:58:42', NULL),
(2, 6, '2025-08-04 14:26:23', NULL),
(3, 6, '2025-08-04 14:52:15', NULL),
(4, 2, '2025-08-07 12:09:40', '2025-08-07 12:46:37'),
(5, 2, '2025-08-07 12:47:45', '2025-08-08 20:38:33'),
(6, 2, '2025-08-08 09:11:44', '2025-08-08 20:38:33'),
(7, 2, '2025-08-08 19:46:10', '2025-08-08 20:38:33'),
(8, 2, '2025-08-08 20:39:29', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `DescripTarea` varchar(255) NOT NULL,
  `idMesero` int(11) NOT NULL,
  `idMesa` int(11) NOT NULL,
  `Estado` varchar(20) DEFAULT 'Pendiente' CHECK (`Estado` in ('Pendiente','En Proceso','Completada')),
  `TiempoEstimado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `DescripTarea`, `idMesero`, `idMesa`, `Estado`, `TiempoEstimado`) VALUES
(1, 'La Mesa #1 pide asistencia', 2, 1, 'Completada', 10),
(2, 'La Mesa #1 pide Agua', 2, 1, 'Completada', 10),
(3, 'La Mesa #12 pide Agua', 2, 12, 'Completada', 10),
(4, 'La Mesa #1 pide asistencia', 2, 1, 'Completada', 10),
(5, 'La Mesa #1 pide Agua', 2, 1, 'Completada', 10),
(6, 'La Mesa #11 pide asistencia', 2, 11, 'Completada', 10),
(7, 'La Mesa #1 pide asistencia', 2, 1, 'Completada', 10),
(8, 'La Mesa #1 pide Agua', 2, 1, 'Completada', 10),
(9, 'La Mesa #1 pide Agua', 2, 1, 'Completada', 10),
(10, 'La Mesa #2 pide asistencia', 2, 2, 'Pendiente', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipousuario`
--

CREATE TABLE `tipousuario` (
  `IdTipoUsuario` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipousuario`
--

INSERT INTO `tipousuario` (`IdTipoUsuario`, `Nombre`) VALUES
(1, 'Gerencia'),
(2, 'Mesero'),
(3, 'Cliente'),
(4, 'Chef'),
(5, 'Caos'),
(6, 'Empleado'),
(7, 'Defcon'),
(8, 'VistaGeneral');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `IdUsuario` int(11) NOT NULL,
  `Usuario` varchar(50) NOT NULL,
  `Contraseña` varchar(100) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellidos` varchar(50) NOT NULL,
  `IdTipoUsuario` int(11) NOT NULL,
  `verificado` tinyint(1) NOT NULL DEFAULT 0,
  `codigo_verificacion` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`IdUsuario`, `Usuario`, `Contraseña`, `Nombre`, `Apellidos`, `IdTipoUsuario`, `verificado`, `codigo_verificacion`) VALUES
(1, 'juan.perez', 'secret123', 'Juan', 'Pérez', 4, 0, NULL),
(2, 'carlos.camacho', '$2y$10$zU5xQd5Y2RdjuWFd8wK1tOTSkxDr8.uvILsELXNxGg2kT8.oATlsa', 'Carlos', 'Camacho', 2, 0, NULL),
(3, 'marta.lopez', 'pass789', 'Marta', 'López', 3, 0, NULL),
(4, 'mesero1@gmail.com', 'mesero123', 'Mario', 'Jimenez', 7, 0, NULL),
(5, 'userPrueba@gmail.com', '$2y$10$Moqp2ll3y8..xdbUcNvvpuoKcXfgzmK7OD/ArugfG88GyZ3ehSNoC', 'Ricardo', 'Gomez', 1, 0, NULL),
(6, 'mariPerez@mesero.com', 'mariP', 'Maria', 'Perez', 5, 0, NULL),
(7, 'lopez@gmail.com', 'lopezs', 'Karlas', 'Lopez', 8, 0, NULL),
(8, 'colnmeth@gmail.com', 'kolp99', 'Hulphi', 'Hitchert', 6, 0, NULL),
(9, 'foybout99@gmail.com', '99kolni', 'Klaus', 'Bouchtker', 6, 0, NULL),
(10, 'custefanny@gmail.com', '$2y$10$S.bXKsmwlYA6y9ZTaaX9fuNgqIk0ipecyC6eFaiyS8egtGr7zGn9q', 'Montse', 'Calvo', 3, 0, '511884');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `view_estaciones_por_chef`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `view_estaciones_por_chef` (
`chef_id` int(11)
,`nombre_chef` varchar(101)
,`estacion_id` int(11)
,`nombre_estacion` varchar(50)
,`slots` tinyint(3) unsigned
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_calificaciones`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_calificaciones` (
`Nombre` varchar(101)
,`Estrellas` int(11)
,`Comentario` varchar(100)
,`FechaHora` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_empleados_detalle`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_empleados_detalle` (
`IdUsuario` int(11)
,`Usuario` varchar(50)
,`Contraseña` varchar(100)
,`Nombre` varchar(50)
,`Apellidos` varchar(50)
,`IdTipoUsuario` int(11)
,`TipoUsuario` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_metrica_general`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_metrica_general` (
`Mesero` decimal(23,0)
,`Chef` decimal(23,0)
,`PromedioEnergia` decimal(6,2)
,`PromedioConcentracion` decimal(6,2)
,`PromedioEstres` decimal(6,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_metrica_usuario`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_metrica_usuario` (
`IdUsuario` int(11)
,`Metrica` varchar(50)
,`Valor` decimal(5,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_orden_plato_estacion_resumen`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_orden_plato_estacion_resumen` (
`numero_orden` int(11)
,`estacion_id` int(11)
,`id_platillo` int(11)
,`nombre_plato` varchar(100)
,`tiempo_estimado` int(10) unsigned
,`fin_preparacion` datetime
,`estado` varchar(30)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_solicitud_chef_detalle`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_solicitud_chef_detalle` (
`solicitud_id` int(11)
,`chef_id` int(11)
,`nombre_chef` varchar(101)
,`accion` varchar(50)
,`tipo` varchar(45)
,`estado` enum('pendiente','aprobada','rechazada','completada')
,`ts_creacion` datetime
,`ts_fin_accion` datetime
,`habilitar` date
,`duracion_minutos` int(11)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `view_estaciones_por_chef`
--
DROP TABLE IF EXISTS `view_estaciones_por_chef`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_estaciones_por_chef`  AS SELECT `u`.`IdUsuario` AS `chef_id`, concat(`u`.`Nombre`,' ',`u`.`Apellidos`) AS `nombre_chef`, `e`.`IdEstacion` AS `estacion_id`, `e`.`Nombre` AS `nombre_estacion`, `e`.`Espacios` AS `slots` FROM ((`usuario` `u` join `chef_estacion` `ce` on(`u`.`IdUsuario` = `ce`.`IdChef`)) join `estacion` `e` on(`ce`.`IdEstacion` = `e`.`IdEstacion`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_calificaciones`
--
DROP TABLE IF EXISTS `vw_calificaciones`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_calificaciones`  AS SELECT concat(`u`.`Nombre`,' ',`u`.`Apellidos`) AS `Nombre`, `c`.`Estrellas` AS `Estrellas`, `c`.`Comentario` AS `Comentario`, `c`.`FechaHora` AS `FechaHora` FROM (`calificacion` `c` join `usuario` `u` on(`c`.`IdUsuario` = `u`.`IdUsuario`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_empleados_detalle`
--
DROP TABLE IF EXISTS `vw_empleados_detalle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_empleados_detalle`  AS SELECT `u`.`IdUsuario` AS `IdUsuario`, `u`.`Usuario` AS `Usuario`, `u`.`Contraseña` AS `Contraseña`, `u`.`Nombre` AS `Nombre`, `u`.`Apellidos` AS `Apellidos`, `u`.`IdTipoUsuario` AS `IdTipoUsuario`, `tp`.`Nombre` AS `TipoUsuario` FROM (`usuario` `u` join `tipousuario` `tp` on(`u`.`IdTipoUsuario` = `tp`.`IdTipoUsuario`)) WHERE `tp`.`Nombre` <> 'Cliente' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_metrica_general`
--
DROP TABLE IF EXISTS `vw_metrica_general`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_metrica_general`  AS SELECT `c`.`Mesero` AS `Mesero`, `c`.`Chef` AS `Chef`, `k`.`PromedioEnergia` AS `PromedioEnergia`, `k`.`PromedioConcentracion` AS `PromedioConcentracion`, `k`.`PromedioEstres` AS `PromedioEstres` FROM ((select sum(`tp`.`Nombre` = 'Mesero') AS `Mesero`,sum(`tp`.`Nombre` = 'Chef') AS `Chef` from (`usuario` `u` join `tipousuario` `tp` on(`u`.`IdTipoUsuario` = `tp`.`IdTipoUsuario`)) where `tp`.`Nombre` in ('Mesero','Chef')) `c` join (select round(avg(case when `kpi`.`Nombre` = 'energia' then `kpi`.`Valor` end),2) AS `PromedioEnergia`,round(avg(case when `kpi`.`Nombre` = 'concentracion' then `kpi`.`Valor` end),2) AS `PromedioConcentracion`,round(avg(case when `kpi`.`Nombre` = 'estres' then `kpi`.`Valor` end),2) AS `PromedioEstres` from ((`usuario` `u` join `tipousuario` `tp` on(`u`.`IdTipoUsuario` = `tp`.`IdTipoUsuario`)) join `kpi_medicion` `kpi` on(`u`.`IdUsuario` = `kpi`.`IdChef`))) `k`) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_metrica_usuario`
--
DROP TABLE IF EXISTS `vw_metrica_usuario`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_metrica_usuario`  AS SELECT `u`.`IdUsuario` AS `IdUsuario`, `kpi`.`Nombre` AS `Metrica`, `kpi`.`Valor` AS `Valor` FROM ((`usuario` `u` join `tipousuario` `tp` on(`u`.`IdTipoUsuario` = `tp`.`IdTipoUsuario`)) join `kpi_medicion` `kpi` on(`u`.`IdUsuario` = `kpi`.`IdChef`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_orden_plato_estacion_resumen`
--
DROP TABLE IF EXISTS `vw_orden_plato_estacion_resumen`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_orden_plato_estacion_resumen`  AS SELECT `o`.`IdOrden` AS `numero_orden`, `oe`.`IdEstacion` AS `estacion_id`, `p`.`IdPlatillo` AS `id_platillo`, `p`.`Nombre` AS `nombre_plato`, `oe`.`TiempoEstimado` AS `tiempo_estimado`, `oe`.`FinPreparacion` AS `fin_preparacion`, `ep`.`Nombre` AS `estado` FROM ((((`orden_plato_estacion` `oe` join `detalle_orden` `do2` on(`oe`.`IdOrdenPlatillo` = `do2`.`IdDetalle`)) join `orden` `o` on(`do2`.`IdOrden` = `o`.`IdOrden`)) join `platillo` `p` on(`do2`.`IdPlatillo` = `p`.`IdPlatillo`)) join `estado_plato` `ep` on(`oe`.`IdPlatoEstado` = `ep`.`IdEstadoPlato`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_solicitud_chef_detalle`
--
DROP TABLE IF EXISTS `vw_solicitud_chef_detalle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_solicitud_chef_detalle`  AS SELECT `s`.`IdAccionChef` AS `solicitud_id`, `s`.`IdChef` AS `chef_id`, concat(`u`.`Nombre`,' ',`u`.`Apellidos`) AS `nombre_chef`, `a`.`Nombre` AS `accion`, `s`.`Tipo` AS `tipo`, `s`.`Estado` AS `estado`, `s`.`InicioAccion` AS `ts_creacion`, `s`.`FinAccion` AS `ts_fin_accion`, `s`.`Habilitar` AS `habilitar`, `a`.`Duracion` AS `duracion_minutos` FROM ((`accion_chef` `s` join `accion` `a` on(`s`.`IdAccion` = `a`.`IdAccion`)) join `usuario` `u` on(`s`.`IdChef` = `u`.`IdUsuario`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `accion`
--
ALTER TABLE `accion`
  ADD PRIMARY KEY (`IdAccion`),
  ADD UNIQUE KEY `nombre` (`Nombre`);

--
-- Indices de la tabla `accioncliente`
--
ALTER TABLE `accioncliente`
  ADD PRIMARY KEY (`IdAccion`),
  ADD KEY `id_usuario` (`IdUsuario`),
  ADD KEY `id_mesero` (`IdMesero`);

--
-- Indices de la tabla `accion_chef`
--
ALTER TABLE `accion_chef`
  ADD PRIMARY KEY (`IdAccionChef`),
  ADD KEY `catalogo_id` (`IdAccion`),
  ADD KEY `fk_solicitud_usuario_idx` (`IdChef`);

--
-- Indices de la tabla `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`IdArea`),
  ADD UNIQUE KEY `Nombre` (`Nombre`);

--
-- Indices de la tabla `area_mesero`
--
ALTER TABLE `area_mesero`
  ADD PRIMARY KEY (`IdArea`),
  ADD KEY `area_mesero_ibfk_2` (`IdUsuario`);

--
-- Indices de la tabla `calificacion`
--
ALTER TABLE `calificacion`
  ADD PRIMARY KEY (`IdCalificacion`),
  ADD KEY `IdUsuario` (`IdUsuario`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`IdCategoria`);

--
-- Indices de la tabla `chef_estacion`
--
ALTER TABLE `chef_estacion`
  ADD PRIMARY KEY (`IdChef`,`IdEstacion`),
  ADD KEY `fk_chef_estacion_estacion` (`IdEstacion`);

--
-- Indices de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD PRIMARY KEY (`IdDetalle`),
  ADD KEY `estado_plato_id` (`IdEstadoPlatillo`),
  ADD KEY `idx_op_orden_estado` (`IdOrden`,`IdEstadoPlatillo`),
  ADD KEY `detalle_orden_ibfk_2` (`IdPlatillo`);

--
-- Indices de la tabla `deudas`
--
ALTER TABLE `deudas`
  ADD PRIMARY KEY (`IdDeuda`),
  ADD KEY `deudas_ibfk_1` (`IdUsuario`);

--
-- Indices de la tabla `estacion`
--
ALTER TABLE `estacion`
  ADD PRIMARY KEY (`IdEstacion`),
  ADD UNIQUE KEY `nombre` (`Nombre`);

--
-- Indices de la tabla `estadomesero`
--
ALTER TABLE `estadomesero`
  ADD PRIMARY KEY (`IdMesero`);

--
-- Indices de la tabla `estado_plato`
--
ALTER TABLE `estado_plato`
  ADD PRIMARY KEY (`IdEstadoPlato`),
  ADD UNIQUE KEY `nombre` (`Nombre`);

--
-- Indices de la tabla `kpi_medicion`
--
ALTER TABLE `kpi_medicion`
  ADD PRIMARY KEY (`IdKpi`),
  ADD KEY `fk_kpi_usuario_idx` (`IdChef`);

--
-- Indices de la tabla `mensajegerencia`
--
ALTER TABLE `mensajegerencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mensajegerencia_ibfk_1` (`idMesero`);

--
-- Indices de la tabla `mensajes_cocina`
--
ALTER TABLE `mensajes_cocina`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mensaje_empleado`
--
ALTER TABLE `mensaje_empleado`
  ADD PRIMARY KEY (`IdMensaje`),
  ADD KEY `idx_emp_msg_empleado` (`IdEmpleado`),
  ADD KEY `idx_emp_msg_accionchef` (`IdAccion`);

--
-- Indices de la tabla `mesa`
--
ALTER TABLE `mesa`
  ADD PRIMARY KEY (`IdMesa`),
  ADD UNIQUE KEY `Numero` (`Numero`),
  ADD KEY `IdCliente` (`IdCliente`),
  ADD KEY `IdMesero` (`IdMesero`),
  ADD KEY `mesa_ibfk_3` (`IdArea`);

--
-- Indices de la tabla `metodopago`
--
ALTER TABLE `metodopago`
  ADD PRIMARY KEY (`IdPago`),
  ADD UNIQUE KEY `IdUsuario` (`IdUsuario`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`IdNotificacion`),
  ADD KEY `IdMesa` (`IdMesa`),
  ADD KEY `IdUsuario` (`IdUsuario`);

--
-- Indices de la tabla `orden`
--
ALTER TABLE `orden`
  ADD PRIMARY KEY (`IdOrden`),
  ADD KEY `idx_orden_estado` (`Estado`),
  ADD KEY `mesa_ordenn_ibfk_1_idx` (`IdMesa`);

--
-- Indices de la tabla `orden_plato_estacion`
--
ALTER TABLE `orden_plato_estacion`
  ADD PRIMARY KEY (`IdOrdenPlatilloEstacion`),
  ADD KEY `orden_plato_id` (`IdOrdenPlatillo`),
  ADD KEY `estacion_id` (`IdEstacion`),
  ADD KEY `estado_plato_id` (`IdPlatoEstado`),
  ADD KEY `fk_ope_usuario_idx` (`IdChef`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`IdPago`),
  ADD KEY `IdMesa` (`IdMesa`);

--
-- Indices de la tabla `platillo`
--
ALTER TABLE `platillo`
  ADD PRIMARY KEY (`IdPlatillo`),
  ADD KEY `estacion_id` (`IdEstacion`),
  ADD KEY `platillo_categoria_ibfk_1_idx` (`IdCategoria`);

--
-- Indices de la tabla `queja`
--
ALTER TABLE `queja`
  ADD PRIMARY KEY (`IdQueja`),
  ADD KEY `IdUsuario` (`IdUsuario`);

--
-- Indices de la tabla `registrohoras`
--
ALTER TABLE `registrohoras`
  ADD PRIMARY KEY (`IdRegistro`),
  ADD KEY `registrohoras_ibfk_1` (`IdMesero`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tareas_ibfk_1` (`idMesero`),
  ADD KEY `tareas_ibfk_2` (`idMesa`);

--
-- Indices de la tabla `tipousuario`
--
ALTER TABLE `tipousuario`
  ADD PRIMARY KEY (`IdTipoUsuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`IdUsuario`),
  ADD UNIQUE KEY `Usuario` (`Usuario`),
  ADD KEY `IdTipoUsuario` (`IdTipoUsuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `accion`
--
ALTER TABLE `accion`
  MODIFY `IdAccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `accioncliente`
--
ALTER TABLE `accioncliente`
  MODIFY `IdAccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `accion_chef`
--
ALTER TABLE `accion_chef`
  MODIFY `IdAccionChef` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de la tabla `area`
--
ALTER TABLE `area`
  MODIFY `IdArea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `calificacion`
--
ALTER TABLE `calificacion`
  MODIFY `IdCalificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `IdCategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `IdDetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de la tabla `deudas`
--
ALTER TABLE `deudas`
  MODIFY `IdDeuda` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estacion`
--
ALTER TABLE `estacion`
  MODIFY `IdEstacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `kpi_medicion`
--
ALTER TABLE `kpi_medicion`
  MODIFY `IdKpi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `mensajegerencia`
--
ALTER TABLE `mensajegerencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `mensajes_cocina`
--
ALTER TABLE `mensajes_cocina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensaje_empleado`
--
ALTER TABLE `mensaje_empleado`
  MODIFY `IdMensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `mesa`
--
ALTER TABLE `mesa`
  MODIFY `IdMesa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `metodopago`
--
ALTER TABLE `metodopago`
  MODIFY `IdPago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `IdNotificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `orden`
--
ALTER TABLE `orden`
  MODIFY `IdOrden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `orden_plato_estacion`
--
ALTER TABLE `orden_plato_estacion`
  MODIFY `IdOrdenPlatilloEstacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `IdPago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `platillo`
--
ALTER TABLE `platillo`
  MODIFY `IdPlatillo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `queja`
--
ALTER TABLE `queja`
  MODIFY `IdQueja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `registrohoras`
--
ALTER TABLE `registrohoras`
  MODIFY `IdRegistro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tipousuario`
--
ALTER TABLE `tipousuario`
  MODIFY `IdTipoUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `accioncliente`
--
ALTER TABLE `accioncliente`
  ADD CONSTRAINT `fk_accioncliente_mesero` FOREIGN KEY (`IdMesero`) REFERENCES `usuario` (`IdUsuario`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_accioncliente_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `accion_chef`
--
ALTER TABLE `accion_chef`
  ADD CONSTRAINT `fk_solicitud_usuario` FOREIGN KEY (`IdChef`) REFERENCES `usuario` (`IdUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_solicitud_usuario 2` FOREIGN KEY (`IdAccion`) REFERENCES `accion` (`IdAccion`);

--
-- Filtros para la tabla `area_mesero`
--
ALTER TABLE `area_mesero`
  ADD CONSTRAINT `area_mesero_ibfk_1` FOREIGN KEY (`IdArea`) REFERENCES `area` (`IdArea`),
  ADD CONSTRAINT `area_mesero_ibfk_2` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `calificacion`
--
ALTER TABLE `calificacion`
  ADD CONSTRAINT `calificacion_ibfk_1` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `chef_estacion`
--
ALTER TABLE `chef_estacion`
  ADD CONSTRAINT `fk_chef_estacion_estacion` FOREIGN KEY (`IdEstacion`) REFERENCES `estacion` (`IdEstacion`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chef_estacion_usuario` FOREIGN KEY (`IdChef`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `detalle_orden_ibfk_1` FOREIGN KEY (`IdOrden`) REFERENCES `orden` (`IdOrden`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_orden_ibfk_2` FOREIGN KEY (`IdPlatillo`) REFERENCES `platillo` (`IdPlatillo`),
  ADD CONSTRAINT `detalle_orden_ibfk_3` FOREIGN KEY (`IdEstadoPlatillo`) REFERENCES `estado_plato` (`IdEstadoPlato`);

--
-- Filtros para la tabla `deudas`
--
ALTER TABLE `deudas`
  ADD CONSTRAINT `deudas_ibfk_1` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `estadomesero`
--
ALTER TABLE `estadomesero`
  ADD CONSTRAINT `estadomesero_ibfk_1` FOREIGN KEY (`IdMesero`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `kpi_medicion`
--
ALTER TABLE `kpi_medicion`
  ADD CONSTRAINT `fk_kpi_usuario` FOREIGN KEY (`IdChef`) REFERENCES `usuario` (`IdUsuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajegerencia`
--
ALTER TABLE `mensajegerencia`
  ADD CONSTRAINT `mensajegerencia_ibfk_1` FOREIGN KEY (`idMesero`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `mensaje_empleado`
--
ALTER TABLE `mensaje_empleado`
  ADD CONSTRAINT `fk_msg_acc` FOREIGN KEY (`IdAccion`) REFERENCES `accion` (`IdAccion`),
  ADD CONSTRAINT `fk_msg_emp` FOREIGN KEY (`IdEmpleado`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `mesa`
--
ALTER TABLE `mesa`
  ADD CONSTRAINT `mesa_ibfk_1` FOREIGN KEY (`IdCliente`) REFERENCES `usuario` (`IdUsuario`),
  ADD CONSTRAINT `mesa_ibfk_2` FOREIGN KEY (`IdMesero`) REFERENCES `usuario` (`IdUsuario`),
  ADD CONSTRAINT `mesa_ibfk_3` FOREIGN KEY (`IdArea`) REFERENCES `area` (`IdArea`);

--
-- Filtros para la tabla `metodopago`
--
ALTER TABLE `metodopago`
  ADD CONSTRAINT `metodopago_ibfk_1` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`IdMesa`) REFERENCES `mesa` (`IdMesa`),
  ADD CONSTRAINT `notificacion_ibfk_2` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `orden`
--
ALTER TABLE `orden`
  ADD CONSTRAINT `mesa_ordenn_ibfk_1` FOREIGN KEY (`IdMesa`) REFERENCES `mesa` (`IdMesa`);

--
-- Filtros para la tabla `orden_plato_estacion`
--
ALTER TABLE `orden_plato_estacion`
  ADD CONSTRAINT `fk_ope_usuario` FOREIGN KEY (`IdChef`) REFERENCES `usuario` (`IdUsuario`) ON DELETE SET NULL,
  ADD CONSTRAINT `orden_plato_estacion_ibfk_1` FOREIGN KEY (`IdOrdenPlatillo`) REFERENCES `detalle_orden` (`IdDetalle`) ON DELETE CASCADE,
  ADD CONSTRAINT `orden_plato_estacion_ibfk_2` FOREIGN KEY (`IdEstacion`) REFERENCES `estacion` (`IdEstacion`),
  ADD CONSTRAINT `orden_plato_estacion_ibfk_3` FOREIGN KEY (`IdPlatoEstado`) REFERENCES `estado_plato` (`IdEstadoPlato`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`IdMesa`) REFERENCES `mesa` (`IdMesa`);

--
-- Filtros para la tabla `platillo`
--
ALTER TABLE `platillo`
  ADD CONSTRAINT `platillo_categoria_ibfk_1` FOREIGN KEY (`IdCategoria`) REFERENCES `categoria` (`IdCategoria`),
  ADD CONSTRAINT `platillo_ibfk_1` FOREIGN KEY (`IdEstacion`) REFERENCES `estacion` (`IdEstacion`);

--
-- Filtros para la tabla `queja`
--
ALTER TABLE `queja`
  ADD CONSTRAINT `queja_ibfk_1` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `registrohoras`
--
ALTER TABLE `registrohoras`
  ADD CONSTRAINT `registrohoras_ibfk_1` FOREIGN KEY (`IdMesero`) REFERENCES `usuario` (`IdUsuario`);

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`idMesero`) REFERENCES `usuario` (`IdUsuario`),
  ADD CONSTRAINT `tareas_ibfk_2` FOREIGN KEY (`idMesa`) REFERENCES `mesa` (`IdMesa`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`IdTipoUsuario`) REFERENCES `tipousuario` (`IdTipoUsuario`);

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `ev_cleanup_ready` ON SCHEDULE EVERY 5 MINUTE STARTS '2025-07-28 00:44:08' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  DECLARE v_id_listo INT;
  SELECT `IdEstadoPlato` INTO v_id_listo 
    FROM `estado_plato` WHERE `Nombre` = 'Listo' LIMIT 1;

  DELETE ope
  FROM `orden_plato_estacion` AS ope
  WHERE ope.`IdPlatoEstado` = v_id_listo;
END$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_marcar_listos` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-07-28 00:41:54' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  DECLARE v_id_listo INT;
  DECLARE v_id_encurso INT;

  SELECT `IdEstadoPlato` INTO v_id_listo 
    FROM `estado_plato` WHERE `Nombre` = 'Listo' LIMIT 1;
  SELECT `IdEstadoPlato` INTO v_id_encurso 
    FROM `estado_plato` WHERE `Nombre` = 'EnCurso' LIMIT 1;

  -- 1) Caducados a Listo en chef.orden_plato_estacion
  UPDATE `orden_plato_estacion` AS ope
  SET ope.`IdPlatoEstado` = v_id_listo
  WHERE ope.`IdPlatoEstado` = v_id_encurso
    AND ope.`FinPreparacion` <= NOW();

  -- 2) Propagar a chef.detalle_orden
  UPDATE `detalle_orden` AS d
  JOIN `orden_plato_estacion` AS ope
    ON d.`IdDetalle` = ope.`IdOrdenPlatillo`
  SET d.`IdEstadoPlatillo` = ope.`IdPlatoEstado`
  WHERE ope.`IdPlatoEstado` = v_id_listo;
END$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_mark_ready` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-07-27 23:15:55' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  DECLARE v_id_listo INT;
  DECLARE v_id_encurso INT;

  -- Obtener IDs de estado
  SELECT `IdEstadoPlato` INTO v_id_listo 
    FROM `estado_plato` WHERE `Nombre` = 'Listo' LIMIT 1;
  SELECT `IdEstadoPlato` INTO v_id_encurso 
    FROM `estado_plato` WHERE `Nombre` = 'EnCurso' LIMIT 1;

  -- 1) Marca como Listo en chef.orden_plato_estacion
  UPDATE `orden_plato_estacion` AS ope
  SET ope.`IdPlatoEstado` = v_id_listo
  WHERE ope.`IdPlatoEstado` = v_id_encurso
    AND ope.`FinPreparacion` <= NOW();

  -- 2) Propaga ese cambio a chef.detalle_orden
  UPDATE `detalle_orden` AS d
  JOIN `orden_plato_estacion` AS ope
    ON d.`IdDetalle` = ope.`IdOrdenPlatillo`
  SET d.`IdEstadoPlatillo` = ope.`IdPlatoEstado`
  WHERE ope.`IdPlatoEstado` = v_id_listo;

  -- 3) Marca la orden como Listo si TODOS sus detalles están listos
  UPDATE `orden` AS o
  JOIN (
    SELECT d2.`IdOrden`
    FROM `detalle_orden` AS d2
    GROUP BY d2.`IdOrden`
    HAVING SUM(d2.`IdEstadoPlatillo` <> v_id_listo) = 0
  ) AS ready
    ON o.`IdOrden` = ready.`IdOrden`
  SET o.`Estado` = 'Listo'
  WHERE o.`Estado` = 'EnCurso';
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
