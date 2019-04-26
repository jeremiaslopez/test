<?php 
// ************************************************************************************************
// FECHA DE ÚLTIMA ACTUALIZACIÓN: 06/02/2018
// USUARIO QUE EDITÓ: Jeremias López Deara
// DESCRIPCIÓN RÁPIDA DE EDICIÓN: Se crea el archivo
// ************************************************************************************************
include ("../../php/Funciones.php");

$sql_carrera = "SELECT id_carrera, id_carrera_tipo, carrera FROM carrera WHERE id_carrera = '".$_GET["id_Carrera"]."';";
$resultado_carrera = mysql_query($sql_carrera,$conexion);
$fila_carrera = mysql_fetch_array($resultado_carrera);

$id_Carrera_Tipo = $fila_carrera["id_carrera_tipo"];
if ($id_Carrera_Tipo == 1)
	{
		$sql_alumno = "SELECT DISTINCT cuenta, apellido_paterno, apellido_materno, nombre FROM alumno JOIN alumno_ciclo USING (cuenta) WHERE alumno_ciclo.id_ciclo_escolar = '".$_GET["id_Ciclo_Escolar"]."' AND alumno_ciclo.id_grupo = '".$_GET["id_Grupo"]."' AND alumno_ciclo.id_estatus = 1 ORDER BY apellido_paterno, apellido_materno, nombre;";
	}
else if (($id_Carrera_Tipo == 2) || ($id_Carrera_Tipo == 3) || ($id_Carrera_Tipo == 4))
	{
		$sql_alumno = "SELECT DISTINCT cuenta, apellido_paterno, apellido_materno, nombre FROM alumno JOIN alumno_ciclo USING (cuenta) WHERE alumno_ciclo.id_ciclo_escolar = '".$_GET["id_Ciclo_Escolar"]."' AND alumno_ciclo.id_semestre = '".$_GET["id_Semestre"]."' AND alumno_ciclo.id_grupo = '".$_GET["id_Grupo"]."' AND alumno_ciclo.id_estatus = 1 ORDER BY apellido_paterno, apellido_materno, nombre;";
	}

$resultado_alumno = mysql_query($sql_alumno, $conexion);
@$registros_alumno = mysql_num_rows($resultado_alumno);
//echo $sql_alumno;
$array = array();
$contador = 1;
if ($registros_alumno > 0)
	{
		while($fila_alumno = mysql_fetch_array($resultado_alumno)){

			$sql_plan_estudios = "SELECT MAX(alumno_ciclo.id_plan_estudios) AS id_plan_estudios, acuerdo_sep, fecha_acuerdo, creditos_sep FROM plan_estudios JOIN alumno_ciclo USING (id_plan_estudios) WHERE cuenta = '".$fila_alumno["cuenta"]."' GROUP BY plan_estudios.id_plan_estudios DESC;";
			$resultado_plan_estudios = mysql_query($sql_plan_estudios,$conexion);
			$fila_plan_estudios = mysql_fetch_array($resultado_plan_estudios);
			
			$Creditos_SEP = $fila_plan_estudios["creditos_sep"];
			$suma_creditos = 0;
			for ($id_semestre = 1; $id_semestre <= $_GET["id_Semestre"]; $id_semestre++)
			{
				$sql_historial = "SELECT calificacion, tipo, fecha, id_semestre, materia, creditos FROM historial JOIN materia USING (id_materia) WHERE cuenta = '".$fila_alumno["cuenta"]."' AND id_semestre = '".$id_semestre."' AND tipo_sep = 'SI' AND calificacion != '5' AND calificacion != 'NP' AND calificacion != 'S' AND calificacion != '' ORDER BY clave_materia ASC";
				$resultado_historial = mysql_query($sql_historial, $conexion);
				while($fila_historial = mysql_fetch_array($resultado_historial))
				{
					$suma_creditos = ($suma_creditos + $fila_historial["creditos"])."asasds<br>";
				}			
			}

			$Porcentaje_Creditos = round((($suma_creditos * 100) / $Creditos_SEP), 2);

			$array1 = array("id" => $contador++, "cuenta" => utf8_encode($fila_alumno["cuenta"]),
			"nombre" => utf8_encode($fila_alumno["apellido_paterno"])." ".utf8_encode($fila_alumno["apellido_materno"])." ".utf8_encode($fila_alumno["nombre"]),
			"credito" => $Porcentaje_Creditos." %");
			array_push($array, $array1);
		}

	}

echo '{"data":'.json_encode($array)."}";
mysql_close($conexion);
?>