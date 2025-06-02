-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: marci
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `maneja_cajas` tinyint(1) DEFAULT 1,
  `usa_cajas` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (35,'MEDICAMENTOS',1,1),(36,'JABONES',0,0),(37,'DESODORANTE',1,1),(38,'MULTIVITAMINICO',1,1);
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('natural','empresa') NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `identificacion` varchar(50) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'natural','Cliente Prueba','',NULL,NULL,NULL),(2,'natural','ALFONSO CARO GUTIERREZ','72345678','alfonsoc@gmail.com','cra 38d#76-26','3156787890'),(3,'natural','JJONATAN','1140874555','j-113@hotmail.com','cra 41G#113-125','3004580231'),(5,'natural','PUBLICO','101010100101','publico@gmail.com','CALLE PRINCIPAL ','3455555'),(6,'','','','','',''),(7,'','','','','',''),(8,'','','','','',''),(9,'','','','','',''),(10,'','','','','',''),(11,'','','','','',''),(12,'','','','','',''),(13,'','','','','',''),(14,'','','','','',''),(15,'','','','','','');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` int(11) NOT NULL,
  `fecha_compra` datetime NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proveedor_id` (`proveedor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
INSERT INTO `compras` VALUES (6,7,'2025-05-18 21:54:28',250000.00,'COMPRA CAJA DE 50 LOSARTAN DE 50 mg'),(7,7,'2025-05-22 11:16:30',1000000.00,'');
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion_impuestos`
--

DROP TABLE IF EXISTS `configuracion_impuestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion_impuestos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `porcentaje` decimal(5,2) NOT NULL,
  `numero_factura_inicial` int(11) NOT NULL DEFAULT 100,
  `comision_mesero` decimal(5,2) DEFAULT 0.00,
  `aplica_impuesto` tinyint(1) DEFAULT 1,
  `aplica_comision` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion_impuestos`
--

LOCK TABLES `configuracion_impuestos` WRITE;
/*!40000 ALTER TABLE `configuracion_impuestos` DISABLE KEYS */;
INSERT INTO `configuracion_impuestos` VALUES (1,19.00,148,10.00,1,1),(2,8.00,100,0.00,1,0);
/*!40000 ALTER TABLE `configuracion_impuestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contabilidad`
--

DROP TABLE IF EXISTS `contabilidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contabilidad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('ingreso','egreso','impuesto','ganancia') NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contabilidad`
--

LOCK TABLES `contabilidad` WRITE;
/*!40000 ALTER TABLE `contabilidad` DISABLE KEYS */;
/*!40000 ALTER TABLE `contabilidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datos_empresa`
--

DROP TABLE IF EXISTS `datos_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datos_empresa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(255) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `resolucion` varchar(255) NOT NULL,
  `nit` varchar(50) NOT NULL,
  `dv` varchar(5) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `correo_electronico` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_empresa`
--

LOCK TABLES `datos_empresa` WRITE;
/*!40000 ALTER TABLE `datos_empresa` DISABLE KEYS */;
INSERT INTO `datos_empresa` VALUES (3,'DROGUERIA LONDRES',' Cl. 54 #41-03 Local 2','2323232424242','72155856-0','45','57 53707950','');
/*!40000 ALTER TABLE `datos_empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_venta`
--

DROP TABLE IF EXISTS `detalles_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_venta` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `laboratorio_id` int(11) NOT NULL,
  `usar_unidad` tinyint(1) DEFAULT 1,
  `cantidad` int(11) NOT NULL,
  `unidad` varchar(20) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_venta`
--

LOCK TABLES `detalles_venta` WRITE;
/*!40000 ALTER TABLE `detalles_venta` DISABLE KEYS */;
INSERT INTO `detalles_venta` VALUES (0,137,244,0,1,3,'3',1500.00,0.00),(0,138,245,0,1,5,'5',1000.00,0.00),(0,139,245,0,1,5,'5',1000.00,0.00),(0,140,250,0,1,10,'10',1000.00,0.00),(0,141,250,0,1,5,'5',1000.00,0.00),(0,142,250,0,0,1,'15',0.00,15000.00),(0,143,250,0,1,10,'10',1000.00,0.00),(0,144,250,0,1,5,'5',1000.00,0.00),(0,145,250,0,1,10,'10',1000.00,0.00),(0,146,250,0,1,3,'3',1000.00,0.00),(0,147,251,0,0,1,'10',0.00,20000.00),(0,148,251,0,1,5,'5',2000.00,0.00),(0,149,251,0,1,3,'3',2000.00,0.00),(0,150,251,0,1,2,'2',2000.00,0.00),(0,151,250,0,1,10,'10',1000.00,0.00),(0,152,250,0,1,5,'5',1000.00,0.00),(0,153,250,0,0,1,'15',0.00,15000.00),(0,154,250,0,1,10,'10',1000.00,0.00),(0,155,250,0,1,3,'3',1000.00,0.00),(0,156,251,0,0,1,'15',0.00,20000.00),(0,162,254,0,0,1,'1',0.00,6000.00),(0,179,250,0,1,5,'5',1000.00,0.00),(0,180,250,0,1,12,'12',1000.00,0.00),(0,193,256,3,0,10,'1',0.00,5000.00),(0,194,251,2,1,10,'10',2000.00,0.00),(0,195,251,2,1,10,'10',2000.00,0.00),(0,196,258,1,1,10,'10',1000.00,0.00),(0,197,258,1,1,10,'10',1000.00,0.00),(0,198,258,1,1,10,'10',1000.00,0.00),(0,199,258,1,1,10,'10',1000.00,0.00),(0,200,258,1,1,10,'10',1000.00,0.00),(0,201,258,1,1,20,'20',1000.00,0.00),(0,202,258,1,1,20,'20',1000.00,0.00),(0,203,258,1,1,20,'20',1000.00,0.00),(0,204,259,3,0,10,'10',0.00,8000.00),(0,205,259,3,0,8,'8',0.00,8000.00),(0,206,261,2,1,10,'10',1000.00,0.00),(0,207,261,2,0,10,'10',0.00,1500.00),(0,208,261,2,0,10,'10',0.00,1500.00),(0,209,261,2,0,10,'10',0.00,1500.00),(0,210,261,2,0,10,'10',0.00,1500.00),(0,211,261,2,0,10,'10',0.00,1500.00),(0,212,261,2,1,10,'10',1000.00,0.00),(0,213,261,2,1,10,'10',1000.00,0.00),(0,214,261,2,0,10,'10',0.00,1500.00),(0,215,261,2,1,10,'10',1000.00,0.00),(0,216,261,2,1,5,'5',1000.00,0.00),(0,217,261,2,1,5,'5',1000.00,0.00),(0,218,261,2,1,5,'5',1000.00,0.00),(0,219,264,3,1,15,'15',1700.00,0.00),(0,220,264,3,1,15,'15',1700.00,0.00),(0,221,264,3,0,1,'1',0.00,30000.00),(0,222,264,3,0,1,'1',0.00,30000.00),(0,223,264,3,1,10,'10',1700.00,0.00),(0,224,264,3,1,10,'10',1700.00,0.00),(0,225,264,3,1,20,'20',1700.00,0.00),(0,226,264,3,1,20,'20',1700.00,0.00),(0,227,264,3,1,15,'15',1700.00,0.00),(0,228,264,3,1,15,'15',1700.00,0.00),(0,229,263,4,1,10,'10',1000.00,0.00),(0,230,263,4,1,10,'10',1000.00,0.00),(0,231,264,3,1,10,'10',1700.00,0.00),(0,232,264,3,1,10,'10',1700.00,0.00),(0,233,264,3,1,10,'10',1700.00,0.00),(0,234,264,3,1,10,'10',1700.00,0.00),(0,235,264,3,1,10,'10',1700.00,0.00),(0,240,264,3,1,10,'10',1700.00,0.00),(0,241,264,3,1,10,'10',1700.00,0.00),(0,242,264,3,1,10,'10',1700.00,0.00),(0,243,264,3,1,10,'10',1700.00,0.00),(0,244,264,3,1,10,'10',1700.00,0.00),(0,245,264,3,1,10,'10',1700.00,0.00),(0,246,264,3,1,10,'10',1700.00,0.00),(0,247,264,3,1,10,'10',1700.00,0.00),(0,254,264,3,1,10,'10',1700.00,0.00),(0,255,264,3,1,10,'10',1700.00,0.00),(0,256,264,3,1,10,'10',1700.00,0.00),(0,257,258,1,1,20,'20',1000.00,0.00),(0,261,264,3,1,10,'10',1700.00,0.00),(0,262,264,3,1,20,'20',1700.00,0.00),(0,263,264,3,1,10,'10',1700.00,0.00),(0,264,264,3,1,1,'30 unidades/caja',0.00,30000.00),(0,265,264,3,1,40,'40',1700.00,0.00),(0,266,264,3,1,20,'20',1700.00,0.00),(0,267,264,3,1,10,'10',1700.00,0.00),(0,268,264,3,1,10,'10',1700.00,0.00),(0,269,264,3,1,20,'20',1700.00,0.00),(0,270,264,3,1,20,'20',1700.00,0.00),(0,271,264,3,1,10,'10',1700.00,0.00),(0,272,264,3,1,1,'30 unidades/caja',0.00,30000.00),(0,273,264,3,1,10,'10',1700.00,0.00),(0,274,264,3,1,20,'20',1700.00,0.00),(0,275,264,3,1,10,'10',1700.00,0.00),(0,276,264,3,1,20,'20',1700.00,0.00),(0,277,264,3,1,1,'30 unidades/caja',0.00,30000.00),(0,278,259,3,1,1,'1',0.00,8000.00),(0,279,263,4,1,20,'20',1000.00,0.00),(0,280,263,4,1,10,'10',1000.00,0.00),(0,281,258,1,1,10,'10',1000.00,0.00),(0,282,258,1,1,10,'10',1000.00,0.00),(0,283,258,1,1,10,'10',1000.00,0.00),(0,284,258,1,1,10,'10',1000.00,0.00),(0,285,258,1,1,10,'10',1000.00,0.00),(0,286,258,1,1,10,'10',1000.00,0.00),(0,287,258,1,1,10,'10',1000.00,0.00),(0,288,261,2,1,10,'10',1000.00,0.00),(0,289,261,2,1,5,'5',1000.00,0.00),(0,290,261,2,1,10,'10',1000.00,0.00),(0,291,261,2,1,5,'5',1000.00,0.00),(0,292,263,4,1,20,'20',1000.00,0.00),(0,293,263,4,1,10,'10',1000.00,0.00),(0,294,261,2,1,5,'5',1000.00,0.00),(0,295,264,3,1,10,'10',1700.00,0.00),(0,296,264,3,1,20,'20',1700.00,0.00),(0,297,264,3,1,10,'10',1700.00,0.00),(0,298,264,3,1,10,'10',1700.00,0.00),(0,299,264,3,1,10,'10',1700.00,0.00),(0,300,264,3,1,10,'10',1700.00,0.00),(0,301,264,3,1,10,'10',1700.00,0.00),(0,302,264,3,1,10,'10',1700.00,0.00),(0,303,264,3,1,10,'10',1700.00,0.00),(0,304,264,3,1,10,'10',1700.00,0.00),(0,305,264,3,1,10,'10',1700.00,0.00),(0,306,264,3,1,10,'10',1700.00,0.00),(0,307,264,3,1,10,'10',1700.00,0.00);
/*!40000 ALTER TABLE `detalles_venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas`
--

DROP TABLE IF EXISTS `facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesero_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `comision` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mesero_id` (`mesero_id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas`
--

LOCK TABLES `facturas` WRITE;
/*!40000 ALTER TABLE `facturas` DISABLE KEYS */;
INSERT INTO `facturas` VALUES (101,45,376200.00,'2025-01-23','0000001',34200.00),(102,45,225000.00,'2025-01-23','0000002',0.00),(103,51,456500.00,'2025-02-05','0000003',41500.00),(104,45,280000.00,'2025-02-05','0000004',0.00),(105,45,415000.00,'2025-02-05','0000005',0.00),(106,45,14000.00,'2025-05-06','0000005',0.00);
/*!40000 ALTER TABLE `facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formas_pago`
--

DROP TABLE IF EXISTS `formas_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formas_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('efectivo','transferencia') NOT NULL,
  `banco` varchar(255) DEFAULT NULL,
  `cuenta` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formas_pago`
--

LOCK TABLES `formas_pago` WRITE;
/*!40000 ALTER TABLE `formas_pago` DISABLE KEYS */;
INSERT INTO `formas_pago` VALUES (6,'efectivo','',''),(7,'transferencia','NEQUI','3013166902'),(8,'transferencia','DATAFONO','23232323232');
/*!40000 ALTER TABLE `formas_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingresos_egresos`
--

DROP TABLE IF EXISTS `ingresos_egresos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingresos_egresos` (
  `id` int(11) NOT NULL,
  `tipo` enum('Ingreso','Egreso') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingresos_egresos`
--

LOCK TABLES `ingresos_egresos` WRITE;
/*!40000 ALTER TABLE `ingresos_egresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `ingresos_egresos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `laboratorios`
--

DROP TABLE IF EXISTS `laboratorios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `laboratorios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laboratorios`
--

LOCK TABLES `laboratorios` WRITE;
/*!40000 ALTER TABLE `laboratorios` DISABLE KEYS */;
INSERT INTO `laboratorios` VALUES (3,'ABBOT'),(1,'ECOFARMAS'),(2,'LASANTEN'),(4,'SANTER');
/*!40000 ALTER TABLE `laboratorios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modulos`
--

DROP TABLE IF EXISTS `modulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modulos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modulos`
--

LOCK TABLES `modulos` WRITE;
/*!40000 ALTER TABLE `modulos` DISABLE KEYS */;
INSERT INTO `modulos` VALUES (1,'Productos'),(2,'Ventas'),(3,'Reportes'),(4,'Impuestos'),(5,'Datos de la Empresa'),(6,'Clientes'),(7,'Formas de Pago'),(8,'Proveedores'),(9,'Compras'),(10,'Contabilidad'),(11,'Roles'),(12,'Copia de Seguridad');
/*!40000 ALTER TABLE `modulos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto_laboratorio`
--

DROP TABLE IF EXISTS `producto_laboratorio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `producto_laboratorio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `laboratorio_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `producto_id` (`producto_id`,`laboratorio_id`),
  KEY `laboratorio_id` (`laboratorio_id`),
  CONSTRAINT `producto_laboratorio_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `producto_laboratorio_ibfk_2` FOREIGN KEY (`laboratorio_id`) REFERENCES `laboratorios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto_laboratorio`
--

LOCK TABLES `producto_laboratorio` WRITE;
/*!40000 ALTER TABLE `producto_laboratorio` DISABLE KEYS */;
INSERT INTO `producto_laboratorio` VALUES (87,258,1),(83,259,3),(85,260,3),(82,261,2),(84,262,3),(88,263,4),(79,264,3),(89,265,3);
/*!40000 ALTER TABLE `producto_laboratorio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `precio_por_unidad` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `stock_cajas` int(11) DEFAULT 0,
  `unidades_por_caja` int(11) DEFAULT 0,
  `unidades_restantes` int(11) DEFAULT 0,
  `unidades_vendidas_acumuladas` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=267 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (258,'ACETAMINOFEN','FIEBRE Y DOLOR DE CABEZA',20000.00,0,1000.00,'2025-06-10',35,'',1,20,10,10),(259,'PROTEX','JABON DE BAÑO',8000.00,0,8000.00,'2025-06-11',36,'',0,0,20,0),(260,'ROLONG','DESODORANTE',25000.00,0,25000.00,'2025-06-11',36,'',0,0,10,0),(261,'LOSARTAN','PARA LA PRESION ARTERIAL',30000.00,0,1000.00,'2025-06-11',35,'7706569021618',1,15,10,5),(262,'REXONA','JABON PARA EL BAÑO',8000.00,0,8000.00,'2025-06-12',36,'',0,0,10,0),(263,'MOXAR 5mg','',30000.00,0,1000.00,'2025-07-12',35,'7702195393426',2,30,60,0),(264,'SHOTB','CAPSULA BLANDA ',30000.00,0,1700.00,'2025-06-12',38,'650240032158',3,30,100,20),(265,'multivitaminico','',1.15,0,0.90,'2025-05-29',35,'',1,9,9,0),(266,'multivitaminico','',50000.00,0,5000.00,'2025-05-21',35,'123456789',1,1,1,0);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('natural','empresa') NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `identificacion` varchar(50) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (7,'natural','JUAN CARLOS','76234567','MERCADO PUBLICO','juanca@gmail.com','3784567');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes`
--

DROP TABLE IF EXISTS `reportes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venta_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `impuesto` decimal(10,2) NOT NULL,
  `total_con_impuesto` decimal(10,2) NOT NULL,
  `forma_pago_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `banco` varchar(255) DEFAULT NULL,
  `cuenta` varchar(255) DEFAULT NULL,
  `monto_efectivo` decimal(10,2) DEFAULT 0.00,
  `monto_otra_forma_pago` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_factura` (`numero_factura`),
  KEY `venta_id` (`venta_id`)
) ENGINE=InnoDB AUTO_INCREMENT=528 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes`
--

LOCK TABLES `reportes` WRITE;
/*!40000 ALTER TABLE `reportes` DISABLE KEYS */;
INSERT INTO `reportes` VALUES (394,126,3,'0000001','2025-05-08 09:19:17',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(395,127,2,'0000002','2025-05-08 10:16:40',13500.00,0.00,13500.00,6,13500.00,'0',NULL,0.00,0.00),(396,128,3,'0000003','2025-05-08 10:17:55',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(397,129,2,'0000005','2025-05-08 10:42:33',4500.00,0.00,4500.00,6,4500.00,'0',NULL,0.00,0.00),(398,130,3,'0000006','2025-05-08 11:02:30',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(399,131,2,'0000007','2025-05-08 11:17:04',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(400,132,3,'0000008','2025-05-08 11:20:05',3000.00,0.00,3000.00,6,3000.00,'0',NULL,0.00,0.00),(401,133,3,'0000009','2025-05-08 11:34:28',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(402,134,3,'0000010','2025-05-08 11:36:07',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(403,137,2,'0000011','2025-05-08 16:17:08',4500.00,0.00,4500.00,6,4500.00,'0',NULL,0.00,0.00),(404,138,3,'0000012','2025-05-08 16:18:54',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(405,139,3,'0000013','2025-05-08 16:21:23',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(406,140,2,'0000015','2025-05-08 17:27:37',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(407,141,3,'0000016','2025-05-08 17:28:41',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(408,142,3,'0000017','2025-05-08 17:40:06',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(409,143,2,'0000018','2025-05-08 17:42:20',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(410,144,2,'0000019','2025-05-08 17:43:15',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(411,145,2,'0000020','2025-05-09 11:02:25',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(412,146,3,'0000021','2025-05-09 11:06:09',3000.00,0.00,3000.00,6,3000.00,'0',NULL,0.00,0.00),(413,147,2,'0000022','2025-05-09 11:30:50',20000.00,0.00,20000.00,7,20000.00,'0',NULL,10000.00,10000.00),(414,148,3,'0000023','2025-05-10 09:44:08',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(415,149,2,'0000024','2025-05-10 11:46:20',6000.00,0.00,6000.00,6,6000.00,'0',NULL,0.00,0.00),(416,150,2,'0000025','2025-05-10 13:01:09',4000.00,0.00,4000.00,6,4000.00,'0',NULL,0.00,0.00),(417,151,2,'0000026','2025-05-10 14:29:13',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(418,152,3,'0000027','2025-05-10 14:32:21',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(419,153,2,'0000028','2025-05-10 14:34:45',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(420,154,3,'0000029','2025-05-10 15:16:28',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(421,155,2,'0000030','2025-05-10 15:59:30',3000.00,0.00,3000.00,6,3000.00,'0',NULL,0.00,0.00),(422,156,2,'0000031','2025-05-10 16:03:50',21600.00,1600.00,21600.00,6,20000.00,'0',NULL,0.00,0.00),(424,179,2,'0000035','2025-05-11 00:28:01',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(425,180,3,'0000036','2025-05-11 00:29:57',12000.00,0.00,12000.00,6,12000.00,'0',NULL,0.00,0.00),(426,193,2,'0000037','2025-05-11 11:39:14',50000.00,0.00,50000.00,6,50000.00,'0',NULL,0.00,0.00),(427,194,2,'0000038','2025-05-11 12:02:54',20000.00,0.00,20000.00,6,20000.00,'0',NULL,0.00,0.00),(428,195,2,'0000039','2025-05-11 13:02:47',20000.00,0.00,20000.00,6,20000.00,'0',NULL,0.00,0.00),(429,196,2,'0000040','2025-05-11 17:51:06',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(430,197,2,'0000041','2025-05-11 17:55:48',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(431,198,2,'0000042','2025-05-11 18:03:33',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(432,199,2,'0000043','2025-05-11 18:29:59',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(433,200,2,'0000044','2025-05-11 18:55:19',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(434,201,2,'0000045','2025-05-11 19:16:56',20000.00,0.00,20000.00,6,20000.00,'0',NULL,0.00,0.00),(435,202,2,'0000046','2025-05-11 19:28:44',20000.00,0.00,20000.00,6,20000.00,'0',NULL,0.00,0.00),(436,203,3,'0000047','2025-05-11 19:31:25',20000.00,0.00,20000.00,6,20000.00,'0',NULL,0.00,0.00),(437,204,2,'0000048','2025-05-12 16:40:39',80000.00,0.00,80000.00,6,80000.00,'0',NULL,0.00,0.00),(438,205,2,'0000049','2025-05-12 16:42:01',64000.00,0.00,64000.00,6,64000.00,'0',NULL,0.00,0.00),(439,206,2,'0000051','2025-05-12 16:46:59',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(440,207,3,'0000050','2025-05-12 16:50:03',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(441,208,2,'0000054','2025-05-12 16:57:05',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(442,209,3,'0000055','2025-05-12 17:10:49',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(443,210,3,'0000056','2025-05-12 17:22:34',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(444,211,2,'0000057','2025-05-12 17:25:41',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(445,212,2,'0000058','2025-05-12 17:37:00',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(446,213,3,'0000059','2025-05-12 17:39:07',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(447,214,2,'0000060','2025-05-12 17:40:25',15000.00,0.00,15000.00,6,15000.00,'0',NULL,0.00,0.00),(448,215,2,'0000061','2025-05-12 17:55:58',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(449,216,3,'0000062','2025-05-12 18:02:52',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(450,217,3,'0000064','2025-05-12 18:06:00',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(451,218,3,'0000065','2025-05-12 22:11:29',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(452,219,3,'0000066','2025-05-12 22:17:42',25500.00,0.00,25500.00,7,25500.00,'0',NULL,10000.00,15500.00),(453,220,3,'0000067','2025-05-12 22:21:19',25500.00,0.00,25500.00,6,25500.00,'0',NULL,0.00,0.00),(454,221,2,'0000068','2025-05-12 22:26:07',30000.00,0.00,30000.00,7,30000.00,'0',NULL,10000.00,20000.00),(455,222,3,'0000069','2025-05-12 22:27:49',30000.00,0.00,30000.00,8,30000.00,'0',NULL,10000.00,20000.00),(456,223,3,'0000070','2025-05-12 22:30:23',17000.00,0.00,17000.00,6,17000.00,'0',NULL,7000.00,10000.00),(457,224,3,'0000071','2025-05-12 22:33:16',17000.00,0.00,17000.00,7,17000.00,'0',NULL,7000.00,10000.00),(458,225,2,'0000073','2025-05-12 22:43:47',34000.00,0.00,34000.00,7,34000.00,'0',NULL,10000.00,24000.00),(459,226,3,'0000074','2025-05-12 22:46:07',34000.00,0.00,34000.00,6,34000.00,'0',NULL,0.00,0.00),(460,227,2,'0000075','2025-05-12 22:50:10',25500.00,0.00,25500.00,6,25500.00,'0',NULL,0.00,0.00),(461,228,3,'0000076','2025-05-12 22:52:10',25500.00,0.00,25500.00,6,25500.00,'0',NULL,0.00,0.00),(462,229,3,'0000077','2025-05-13 09:31:35',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(463,230,3,'0000078','2025-05-13 09:35:59',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(464,231,3,'0000079','2025-05-13 09:37:51',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(465,232,2,'0000080','2025-05-13 09:46:41',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(466,233,3,'0000081','2025-05-13 10:01:39',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(467,234,3,'0000082','2025-05-13 10:12:44',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(468,235,3,'0000083','2025-05-13 10:14:33',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(469,240,3,'0000084','2025-05-13 10:27:57',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(470,241,2,'0000085','2025-05-13 10:29:38',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(471,242,3,'0000086','2025-05-13 10:33:54',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(472,243,3,'0000087','2025-05-13 10:41:26',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(473,244,3,'0000088','2025-05-13 10:42:40',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(474,245,3,'0000089','2025-05-13 10:44:19',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(475,246,3,'0000090','2025-05-13 10:46:49',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(476,247,3,'0000091','2025-05-13 10:47:38',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(477,254,3,'0000094','2025-05-13 11:08:09',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(478,255,3,'0000095','2025-05-13 11:11:49',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(479,256,3,'0000096','2025-05-13 11:12:47',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(480,257,3,'0000097','2025-05-13 11:13:59',20000.00,0.00,20000.00,6,20000.00,'0',NULL,0.00,0.00),(481,261,3,'0000098','2025-05-13 11:32:35',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(482,262,3,'0000100','2025-05-13 11:33:40',34000.00,0.00,34000.00,6,34000.00,'0',NULL,0.00,0.00),(483,263,3,'0000101','2025-05-13 11:34:44',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(484,264,3,'0000102','2025-05-13 11:47:02',30000.00,0.00,30000.00,6,30000.00,'0',NULL,0.00,0.00),(485,265,3,'0000103','2025-05-13 11:50:55',68000.00,0.00,68000.00,6,68000.00,'0',NULL,0.00,0.00),(486,266,3,'0000104','2025-05-13 11:55:37',34000.00,0.00,34000.00,6,34000.00,'0',NULL,0.00,0.00),(487,267,3,'0000105','2025-05-13 11:56:48',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(488,268,5,'0000106','2025-05-13 11:57:43',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(489,269,3,'0000107','2025-05-13 11:58:24',34000.00,0.00,34000.00,6,34000.00,'0',NULL,0.00,0.00),(490,270,2,'0000108','2025-05-13 12:10:09',34000.00,0.00,34000.00,6,34000.00,'0',NULL,0.00,0.00),(491,271,3,'0000109','2025-05-13 12:10:43',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(492,272,2,'0000110','2025-05-13 12:13:51',30000.00,0.00,30000.00,6,30000.00,'0',NULL,0.00,0.00),(493,273,5,'0000111','2025-05-13 12:22:02',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(494,274,3,'0000112','2025-05-13 12:22:40',34000.00,0.00,34000.00,6,34000.00,'0',NULL,0.00,0.00),(495,275,3,'0000113','2025-05-13 13:34:21',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(496,276,3,'0000114','2025-05-13 13:35:35',34000.00,0.00,34000.00,6,34000.00,'0',NULL,0.00,0.00),(497,277,3,'0000115','2025-05-13 13:36:41',30000.00,0.00,30000.00,6,30000.00,'0',NULL,0.00,0.00),(498,278,2,'0000116','2025-05-13 13:50:56',8000.00,0.00,8000.00,6,8000.00,'0',NULL,0.00,0.00),(499,279,3,'0000117','2025-05-13 13:52:28',20000.00,0.00,20000.00,6,20000.00,'0',NULL,0.00,0.00),(500,280,2,'0000118','2025-05-13 13:53:00',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(501,281,2,'0000119','2025-05-13 13:57:16',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(502,282,3,'0000120','2025-05-13 13:58:01',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(503,283,2,'0000121','2025-05-13 13:58:27',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(504,284,2,'0000122','2025-05-13 13:59:54',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(505,285,3,'0000123','2025-05-13 14:05:08',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(506,286,3,'0000124','2025-05-13 14:05:50',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(507,287,2,'0000125','2025-05-13 14:10:55',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(508,288,2,'0000126','2025-05-13 14:22:48',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(509,289,3,'0000127','2025-05-13 14:24:07',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(510,290,2,'0000128','2025-05-13 14:27:29',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(511,291,3,'0000129','2025-05-13 14:28:22',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(512,292,2,'0000130','2025-05-13 14:35:46',20000.00,0.00,20000.00,6,20000.00,'0',NULL,0.00,0.00),(513,293,2,'0000131','2025-05-13 14:37:08',10000.00,0.00,10000.00,6,10000.00,'0',NULL,0.00,0.00),(514,294,3,'0000133','2025-05-13 14:42:20',5000.00,0.00,5000.00,6,5000.00,'0',NULL,0.00,0.00),(515,295,2,'0000134','2025-05-13 14:44:31',17000.00,0.00,17000.00,7,17000.00,'0',NULL,7000.00,10000.00),(516,296,3,'0000135','2025-05-13 14:45:32',34000.00,0.00,34000.00,6,34000.00,'0',NULL,0.00,0.00),(517,297,2,'0000136','2025-05-15 08:24:12',18360.00,1360.00,18360.00,6,17000.00,'0',NULL,0.00,0.00),(518,298,2,'0000138','2025-05-15 08:32:43',18360.00,1360.00,18360.00,6,17000.00,'0',NULL,0.00,0.00),(519,299,3,'0000139','2025-05-27 15:42:32',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(520,300,3,'0000140','2025-05-27 17:10:11',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(521,301,3,'0000141','2025-05-27 17:23:15',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(522,302,3,'0000142','2025-05-27 17:24:06',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(523,303,2,'0000143','2025-05-27 17:25:02',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(524,304,3,'0000145','2025-05-27 17:33:23',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(525,305,3,'0000146','2025-05-27 17:41:48',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(526,306,3,'0000147','2025-05-27 17:43:58',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00),(527,307,3,'0000148','2025-05-27 17:45:32',17000.00,0.00,17000.00,6,17000.00,'0',NULL,0.00,0.00);
/*!40000 ALTER TABLE `reportes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (19,'Administrador'),(20,'Usuario'),(21,'usuarios');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_modulos`
--

DROP TABLE IF EXISTS `usuario_modulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario_modulos` (
  `usuario_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  PRIMARY KEY (`usuario_id`,`modulo_id`),
  KEY `fk_modulo` (`modulo_id`),
  KEY `idx_usuario_id` (`usuario_id`),
  CONSTRAINT `fk_modulo` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuario_modulos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `usuario_modulos_ibfk_2` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla de relación entre usuarios y módulos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_modulos`
--

LOCK TABLES `usuario_modulos` WRITE;
/*!40000 ALTER TABLE `usuario_modulos` DISABLE KEYS */;
INSERT INTO `usuario_modulos` VALUES (17,1),(17,2),(17,3),(17,4),(17,5),(17,6),(17,7),(17,8),(17,9),(17,10),(17,11),(17,12),(21,1),(21,2),(21,3),(21,4),(21,5),(21,6),(21,7),(21,8),(21,9),(21,10),(21,11),(21,12),(22,1),(22,2),(22,3),(22,4),(22,5),(22,6),(22,7),(22,8),(22,9),(22,10),(22,11),(22,12),(23,1),(23,2),(23,3),(23,4),(23,5),(23,6),(23,7),(23,8),(23,9),(23,10),(23,11),(23,12);
/*!40000 ALTER TABLE `usuario_modulos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (17,'admin','$2y$10$aMtCnp7iD0j5hgEWmUCuDuOQic0CB4MoxZQArPJNrlxdjyHFwaLEG',19),(21,'EINER','$2y$10$81EHqsg8pRz9qPTOHv/oIOe/sb5bzmzTygflYQWPo6XZr4bteBcBO',19),(22,'ERICK','$2y$10$J5W55tood/z6yDdRo2TukecXPq49RO426AHjtp6kWexsFW5kuaiCq',19),(23,'DIEGO','$2y$10$tnSrPyFu0tDYkC.vmEOv..5NdPFkFb8.0c3yLtBnuqS06zFdLgvhm',19);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `forma_pago_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `impuesto` decimal(10,2) NOT NULL,
  `total_con_impuesto` decimal(10,2) NOT NULL,
  `cuenta` varchar(255) DEFAULT NULL,
  `mesero_id` int(11) DEFAULT NULL,
  `mesa_id` int(11) DEFAULT NULL,
  `monto_efectivo` decimal(10,2) DEFAULT 0.00,
  `monto_otra_forma_pago` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_factura` (`numero_factura`),
  UNIQUE KEY `numero_factura_2` (`numero_factura`),
  KEY `cliente_id` (`cliente_id`),
  KEY `forma_pago_id` (`forma_pago_id`),
  KEY `mesero_id` (`mesero_id`),
  KEY `mesa_id` (`mesa_id`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`forma_pago_id`) REFERENCES `formas_pago` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=308 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (126,3,6,'2025-05-08 09:19:17','0000001',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(127,2,6,'2025-05-08 10:16:40','0000002',13500.00,0.00,13500.00,NULL,NULL,NULL,0.00,0.00),(128,3,6,'2025-05-08 10:17:55','0000003',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(129,2,6,'2025-05-08 10:42:33','0000005',4500.00,0.00,4500.00,NULL,NULL,NULL,0.00,0.00),(130,3,6,'2025-05-08 11:02:30','0000006',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(131,2,6,'2025-05-08 11:17:04','0000007',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(132,3,6,'2025-05-08 11:20:05','0000008',3000.00,0.00,3000.00,NULL,NULL,NULL,0.00,0.00),(133,3,6,'2025-05-08 11:34:28','0000009',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(134,3,6,'2025-05-08 11:36:07','0000010',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(137,2,6,'2025-05-08 16:17:08','0000011',4500.00,0.00,4500.00,NULL,NULL,NULL,0.00,0.00),(138,3,6,'2025-05-08 16:18:54','0000012',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(139,3,6,'2025-05-08 16:21:23','0000013',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(140,2,6,'2025-05-08 17:27:37','0000015',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(141,3,6,'2025-05-08 17:28:41','0000016',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(142,3,6,'2025-05-08 17:40:06','0000017',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(143,2,6,'2025-05-08 17:42:20','0000018',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(144,2,6,'2025-05-08 17:43:15','0000019',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(145,2,6,'2025-05-09 11:02:25','0000020',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(146,3,6,'2025-05-09 11:06:09','0000021',3000.00,0.00,3000.00,NULL,NULL,NULL,0.00,0.00),(147,2,7,'2025-05-09 11:30:50','0000022',20000.00,0.00,20000.00,NULL,NULL,NULL,10000.00,10000.00),(148,3,6,'2025-05-10 09:44:08','0000023',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(149,2,6,'2025-05-10 11:46:20','0000024',6000.00,0.00,6000.00,NULL,NULL,NULL,0.00,0.00),(150,2,6,'2025-05-10 13:01:09','0000025',4000.00,0.00,4000.00,NULL,NULL,NULL,0.00,0.00),(151,2,6,'2025-05-10 14:29:13','0000026',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(152,3,6,'2025-05-10 14:32:21','0000027',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(153,2,6,'2025-05-10 14:34:45','0000028',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(154,3,6,'2025-05-10 15:16:28','0000029',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(155,2,6,'2025-05-10 15:59:30','0000030',3000.00,0.00,3000.00,NULL,NULL,NULL,0.00,0.00),(156,2,6,'2025-05-10 16:03:50','0000031',20000.00,1600.00,21600.00,NULL,NULL,NULL,0.00,0.00),(162,2,6,'2025-05-10 23:43:14','0000034',6000.00,0.00,6000.00,NULL,NULL,NULL,0.00,0.00),(179,2,6,'2025-05-11 00:28:01','0000035',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(180,3,6,'2025-05-11 00:29:57','0000036',12000.00,0.00,12000.00,NULL,NULL,NULL,0.00,0.00),(193,2,6,'2025-05-11 11:39:14','0000037',50000.00,0.00,50000.00,NULL,NULL,NULL,0.00,0.00),(194,2,6,'2025-05-11 12:02:54','0000038',20000.00,0.00,20000.00,NULL,NULL,NULL,0.00,0.00),(195,2,6,'2025-05-11 13:02:47','0000039',20000.00,0.00,20000.00,NULL,NULL,NULL,0.00,0.00),(196,2,6,'2025-05-11 17:51:06','0000040',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(197,2,6,'2025-05-11 17:55:48','0000041',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(198,2,6,'2025-05-11 18:03:33','0000042',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(199,2,6,'2025-05-11 18:29:59','0000043',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(200,2,6,'2025-05-11 18:55:19','0000044',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(201,2,6,'2025-05-11 19:16:56','0000045',20000.00,0.00,20000.00,NULL,NULL,NULL,0.00,0.00),(202,2,6,'2025-05-11 19:28:44','0000046',20000.00,0.00,20000.00,NULL,NULL,NULL,0.00,0.00),(203,3,6,'2025-05-11 19:31:25','0000047',20000.00,0.00,20000.00,NULL,NULL,NULL,0.00,0.00),(204,2,6,'2025-05-12 16:40:39','0000048',80000.00,0.00,80000.00,NULL,NULL,NULL,0.00,0.00),(205,2,6,'2025-05-12 16:42:01','0000049',64000.00,0.00,64000.00,NULL,NULL,NULL,0.00,0.00),(206,2,6,'2025-05-12 16:46:59','0000051',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(207,3,6,'2025-05-12 16:50:03','0000050',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(208,2,6,'2025-05-12 16:57:05','0000054',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(209,3,6,'2025-05-12 17:10:49','0000055',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(210,3,6,'2025-05-12 17:22:34','0000056',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(211,2,6,'2025-05-12 17:25:41','0000057',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(212,2,6,'2025-05-12 17:37:00','0000058',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(213,3,6,'2025-05-12 17:39:07','0000059',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(214,2,6,'2025-05-12 17:40:25','0000060',15000.00,0.00,15000.00,NULL,NULL,NULL,0.00,0.00),(215,2,6,'2025-05-12 17:55:58','0000061',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(216,3,6,'2025-05-12 18:02:52','0000062',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(217,3,6,'2025-05-12 18:06:00','0000064',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(218,3,6,'2025-05-12 22:11:29','0000065',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(219,3,7,'2025-05-12 22:17:42','0000066',25500.00,0.00,25500.00,NULL,NULL,NULL,10000.00,15500.00),(220,3,6,'2025-05-12 22:21:19','0000067',25500.00,0.00,25500.00,NULL,NULL,NULL,0.00,0.00),(221,2,7,'2025-05-12 22:26:07','0000068',30000.00,0.00,30000.00,NULL,NULL,NULL,10000.00,20000.00),(222,3,8,'2025-05-12 22:27:49','0000069',30000.00,0.00,30000.00,NULL,NULL,NULL,10000.00,20000.00),(223,3,6,'2025-05-12 22:30:23','0000070',17000.00,0.00,17000.00,NULL,NULL,NULL,7000.00,10000.00),(224,3,7,'2025-05-12 22:33:16','0000071',17000.00,0.00,17000.00,NULL,NULL,NULL,7000.00,10000.00),(225,2,7,'2025-05-12 22:43:47','0000073',34000.00,0.00,34000.00,NULL,NULL,NULL,10000.00,24000.00),(226,3,6,'2025-05-12 22:46:07','0000074',34000.00,0.00,34000.00,NULL,NULL,NULL,0.00,0.00),(227,2,6,'2025-05-12 22:50:10','0000075',25500.00,0.00,25500.00,NULL,NULL,NULL,0.00,0.00),(228,3,6,'2025-05-12 22:52:10','0000076',25500.00,0.00,25500.00,NULL,NULL,NULL,0.00,0.00),(229,3,6,'2025-05-13 09:31:35','0000077',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(230,3,6,'2025-05-13 09:35:59','0000078',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(231,3,6,'2025-05-13 09:37:51','0000079',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(232,2,6,'2025-05-13 09:46:41','0000080',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(233,3,6,'2025-05-13 10:01:39','0000081',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(234,3,6,'2025-05-13 10:12:44','0000082',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(235,3,6,'2025-05-13 10:14:33','0000083',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(240,3,6,'2025-05-13 10:27:57','0000084',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(241,2,6,'2025-05-13 10:29:38','0000085',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(242,3,6,'2025-05-13 10:33:54','0000086',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(243,3,6,'2025-05-13 10:41:26','0000087',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(244,3,6,'2025-05-13 10:42:40','0000088',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(245,3,6,'2025-05-13 10:44:19','0000089',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(246,3,6,'2025-05-13 10:46:49','0000090',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(247,3,6,'2025-05-13 10:47:38','0000091',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(254,3,6,'2025-05-13 11:08:09','0000094',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(255,3,6,'2025-05-13 11:11:49','0000095',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(256,3,6,'2025-05-13 11:12:47','0000096',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(257,3,6,'2025-05-13 11:13:59','0000097',20000.00,0.00,20000.00,NULL,NULL,NULL,0.00,0.00),(261,3,6,'2025-05-13 11:32:35','0000098',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(262,3,6,'2025-05-13 11:33:40','0000100',34000.00,0.00,34000.00,NULL,NULL,NULL,0.00,0.00),(263,3,6,'2025-05-13 11:34:44','0000101',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(264,3,6,'2025-05-13 11:47:02','0000102',30000.00,0.00,30000.00,NULL,NULL,NULL,0.00,0.00),(265,3,6,'2025-05-13 11:50:55','0000103',68000.00,0.00,68000.00,NULL,NULL,NULL,0.00,0.00),(266,3,6,'2025-05-13 11:55:37','0000104',34000.00,0.00,34000.00,NULL,NULL,NULL,0.00,0.00),(267,3,6,'2025-05-13 11:56:48','0000105',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(268,5,6,'2025-05-13 11:57:43','0000106',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(269,3,6,'2025-05-13 11:58:24','0000107',34000.00,0.00,34000.00,NULL,NULL,NULL,0.00,0.00),(270,2,6,'2025-05-13 12:10:09','0000108',34000.00,0.00,34000.00,NULL,NULL,NULL,0.00,0.00),(271,3,6,'2025-05-13 12:10:43','0000109',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(272,2,6,'2025-05-13 12:13:51','0000110',30000.00,0.00,30000.00,NULL,NULL,NULL,0.00,0.00),(273,5,6,'2025-05-13 12:22:02','0000111',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(274,3,6,'2025-05-13 12:22:40','0000112',34000.00,0.00,34000.00,NULL,NULL,NULL,0.00,0.00),(275,3,6,'2025-05-13 13:34:21','0000113',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(276,3,6,'2025-05-13 13:35:35','0000114',34000.00,0.00,34000.00,NULL,NULL,NULL,0.00,0.00),(277,3,6,'2025-05-13 13:36:41','0000115',30000.00,0.00,30000.00,NULL,NULL,NULL,0.00,0.00),(278,2,6,'2025-05-13 13:50:56','0000116',8000.00,0.00,8000.00,NULL,NULL,NULL,0.00,0.00),(279,3,6,'2025-05-13 13:52:28','0000117',20000.00,0.00,20000.00,NULL,NULL,NULL,0.00,0.00),(280,2,6,'2025-05-13 13:53:00','0000118',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(281,2,6,'2025-05-13 13:57:16','0000119',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(282,3,6,'2025-05-13 13:58:01','0000120',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(283,2,6,'2025-05-13 13:58:27','0000121',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(284,2,6,'2025-05-13 13:59:54','0000122',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(285,3,6,'2025-05-13 14:05:08','0000123',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(286,3,6,'2025-05-13 14:05:50','0000124',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(287,2,6,'2025-05-13 14:10:55','0000125',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(288,2,6,'2025-05-13 14:22:48','0000126',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(289,3,6,'2025-05-13 14:24:07','0000127',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(290,2,6,'2025-05-13 14:27:29','0000128',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(291,3,6,'2025-05-13 14:28:22','0000129',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(292,2,6,'2025-05-13 14:35:46','0000130',20000.00,0.00,20000.00,NULL,NULL,NULL,0.00,0.00),(293,2,6,'2025-05-13 14:37:08','0000131',10000.00,0.00,10000.00,NULL,NULL,NULL,0.00,0.00),(294,3,6,'2025-05-13 14:42:20','0000133',5000.00,0.00,5000.00,NULL,NULL,NULL,0.00,0.00),(295,2,7,'2025-05-13 14:44:31','0000134',17000.00,0.00,17000.00,NULL,NULL,NULL,7000.00,10000.00),(296,3,6,'2025-05-13 14:45:32','0000135',34000.00,0.00,34000.00,NULL,NULL,NULL,0.00,0.00),(297,2,6,'2025-05-15 08:24:12','0000136',17000.00,1360.00,18360.00,NULL,NULL,NULL,0.00,0.00),(298,2,6,'2025-05-15 08:32:43','0000138',17000.00,1360.00,18360.00,NULL,NULL,NULL,0.00,0.00),(299,3,6,'2025-05-27 15:42:32','0000139',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(300,3,6,'2025-05-27 17:10:11','0000140',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(301,3,6,'2025-05-27 17:23:15','0000141',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(302,3,6,'2025-05-27 17:24:06','0000142',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(303,2,6,'2025-05-27 17:25:02','0000143',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(304,3,6,'2025-05-27 17:33:23','0000145',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(305,3,6,'2025-05-27 17:41:48','0000146',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(306,3,6,'2025-05-27 17:43:58','0000147',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00),(307,3,6,'2025-05-27 17:45:32','0000148',17000.00,0.00,17000.00,NULL,NULL,NULL,0.00,0.00);
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas_productos`
--

DROP TABLE IF EXISTS `ventas_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ventas_productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `venta_id` (`venta_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `ventas_productos_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  CONSTRAINT `ventas_productos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas_productos`
--

LOCK TABLES `ventas_productos` WRITE;
/*!40000 ALTER TABLE `ventas_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `ventas_productos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-01 21:58:12
