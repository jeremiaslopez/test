<?php
// ************************************************************************************************
// FECHA DE ÚLTIMA ACTUALIZACIÓN: 24/01/2019
// USUARIO QUE EDITÓ: Ing. Julio César Morales Crispín
// DESCRIPCIÓN RÁPIDA DE EDICIÓN: Se cambian consultas SQL para que sólo filtre id_forma_pago = 2, es decir pagos hechos en caja
// ************************************************************************************************
include("../../php/Funciones.php");
include("../../pdf/fpdf.php");

$Encabezados=8;
$Titulos=6;
$Contenido=5;

class PDF extends FPDF
	{
		function Footer()	//Pie de página
			{
				$this->SetY(-15);	//Posición: a 1,5 cm del final
				$this->SetFont("Arial","I",8);	//Arial Italic 8
				$this->Cell(0,5,utf8_decode("Página ").$this->PageNo()." de {nb}",0,0,"C");	//Número de página
			}
	}

//Creación del objeto de la clase heredada
$pdf=new PDF("P", "mm", "Letter");	//Crea un archivo PDF de tamaño carta
$pdf->AliasNbPages();

$x = 0;
$y = 16;
$alto_celda=5;

$pdf->AddPage();
$pagina=1;

// Cabecera del Documento
$pdf->SetXY($x+10,$y-1);
$pdf->Cell(196,22,"",0,0,"C"); //Cuadro contenededor Cabecera

$pdf->Image("../../imagenes/UP3.png",$x+11,$y,20); //Logo de la Universidad

$x=6;

$pdf->SetFont("Arial","B",10); //Establece la fuente
$pdf->SetXY($x,$y); //Establece la posición actual de "x" y "y"
$pdf->Cell(200,5,"UNIVERSIDAD PEDREGAL DEL SUR, S.C.",0,0,"C");
$pdf->SetFont("Arial","",7);
$pdf->SetXY($x,$y+3);
$pdf->Cell(200,5,"AV. TRANSMISIONES N. 51 COL. EX HACIENDA SAN JUAN HUIPULCO,",0,0,"C");
$pdf->SetXY($x,$y+6);
$pdf->Cell(200,5,utf8_decode("CIUDAD DE MÉXICO, DELEGACIÓN: TLALPAN, C.P. 14370."),0,0,"C");
$pdf->SetFont("Arial","B",5);
$pdf->SetXY($x,$y+9);
$pdf->Cell(200,5,"TELS.: 5603-5049, 5603-1640 Y 5594-3377",0,0,"C");
$pdf->SetXY($x,$y+11);
$pdf->Cell(200,5,"R.F.C.: UPS-900305-844",0,0,"C");

// Fin de Cabecera
//$sql_id_concepto = "SELECT DISTINCT id_concepto FROM pagos_registros WHERE fecha BETWEEN '".$_POST["Fecha_Inicio"]."' AND '".$_POST["Fecha_Fin"]."' ORDER BY id_concepto;";
$sql_id_concepto = "SELECT DISTINCT id_concepto FROM pagos_registros WHERE id_forma_pago = 2 AND fecha BETWEEN '".$_POST["Fecha_Inicio"]."' AND '".$_POST["Fecha_Fin"]."' ORDER BY id_concepto;";
$resultado_id_concepto = mysql_query($sql_id_concepto, $conexion);

$x=6;
$y=40;
$renglon=1;

$total_tipo_pago[]="";
$numero_pagos[]="";

$pdf->SetXY($x,$y);
$pdf->SetFont("Arial","B",$Encabezados);
$pdf->Cell(200,5,strtoupper(utf8_decode("REPORTE DE INGRESOS   -   VÍA CAJA ")),0,0,"C");
$pdf->SetXY($x,$y+5);
$pdf->Cell(200,5,strtoupper(utf8_decode("CORTE AL DÍA: ".Fecha_Cadena($_POST["Fecha_Inicio"])." AL ".Fecha_Cadena($_POST["Fecha_Fin"]))),0,0,"C");

$y=60;

while($fila_id_concepto = mysql_fetch_array($resultado_id_concepto))
	{
		$id_concepto = $fila_id_concepto["id_concepto"];
		$sql_concepto = "SELECT concepto FROM pagos_concepto WHERE id_concepto = '".$id_concepto."';";
		$resultado_concepto = mysql_query($sql_concepto, $conexion);
		$fila_concepto = mysql_fetch_array($resultado_concepto);
		
		$pdf->SetFillColor(53,71,140);
		$pdf->SetXY($x,$y);
		$pdf->SetFont("Arial","B",$Encabezados);
		$pdf->Cell(200,5,$fila_concepto["concepto"],1,0,"C");
		
		$renglon++;
		
		if ($id_concepto == 2 || $id_concepto == 9 || $id_concepto == 11 || $id_concepto == 12 || $id_concepto == 13)	//REGISTROS DE COLEGIATURA, INSCRIPCIÓN, OTRO, REINSCRIPCIÓN
			{
				if ($id_concepto == 2) $colspan = 7;
				else if ($id_concepto == 9) $colspan = 3;
				else if ($id_concepto == 11) $colspan = 2;
				else if ($id_concepto == 12) $colspan = 2;
				else if ($id_concepto == 13) $colspan = 5;
				
				$sql_carrera_tipo = "SELECT MAX(id_carrera_tipo) AS carrera_tipo FROM carrera_tipo;";
				$resultado_carrera_tipo = mysql_query($sql_carrera_tipo, $conexion);
				$fila_carrera_tipo = mysql_fetch_array($resultado_carrera_tipo);
				$carrera_tipo = $fila_carrera_tipo["carrera_tipo"];
				
				for ($id_carrera_tipo = 1; $id_carrera_tipo <= $carrera_tipo; $id_carrera_tipo++)
					{
						//$sql_pagos = "SELECT * FROM pagos_registros JOIN pagos_concepto USING (id_concepto) WHERE pagos_registros.id_concepto = '".$id_concepto."' AND pagos_registros.id_carrera_tipo = '".$id_carrera_tipo."' AND fecha BETWEEN '".$_POST["Fecha_Inicio"]."' AND '".$_POST["Fecha_Fin"]."' ORDER BY fecha, concepto, id_pago_registro;";
						$sql_pagos = "SELECT * FROM pagos_registros JOIN pagos_concepto USING (id_concepto) WHERE pagos_registros.id_concepto = '".$id_concepto."' AND pagos_registros.id_carrera_tipo = '".$id_carrera_tipo."' AND id_forma_pago = 2 AND fecha BETWEEN '".$_POST["Fecha_Inicio"]."' AND '".$_POST["Fecha_Fin"]."' ORDER BY fecha, concepto, id_pago_registro;";
						$resultado_pagos = mysql_query($sql_pagos, $conexion);
						$registros_pagos = mysql_num_rows($resultado_pagos);
						$pdf->SetFont("Arial","",$Encabezados);
						
						if ($id_carrera_tipo == 1) $leyenda = "LICENCIATURA";
						else if ($id_carrera_tipo == 2) $leyenda = "ESPECIALIDAD";
						else if ($id_carrera_tipo == 3) $leyenda = "MAESTRIA";
						else if ($id_carrera_tipo == 4) $leyenda = "LICENCIATURA CUATRIMESTRAL";
						
						if ($registros_pagos > 0)
							{
								if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
									{
										$pdf->AddPage();
										$renglon=1;
										$pagina++;
										$y=25;
									}
								else
									{
										$y+=$alto_celda;
										$renglon++;
									}
								if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
									{
										$pdf->AddPage();
										$renglon=1;
										$pagina++;
										$y=25;
									}
								else
									{
										$y+=$alto_celda;
										$renglon++;
									}
								
								$pdf->SetFont("Arial","B",$Encabezados);
								$pdf->SetFillColor(78,122,199);
								$pdf->SetXY($x,$y);
								$pdf->Cell(200,5,$leyenda,1,0,"C",1);
								
								if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
									{
										$pdf->AddPage();
										$renglon=1;
										$pagina++;
										$y=25;
									}
								else
									{
										$y+=$alto_celda;
										$renglon++;
									}
								
								$pdf->SetFont("Arial","B",$Titulos);
								$pdf->SetFillColor(127,178,240);
								
								switch($id_concepto)
									{
										case 2:
											$pdf->SetXY($x,$y);
											$pdf->Cell(8,5,"Folio",1,0,"C",1);
											$pdf->SetXY($x+8,$y);
											$pdf->Cell(50,5,"Nombre",1,0,"C",1);
											$pdf->SetXY($x+58,$y);
											$pdf->Cell(15,5,"Carrera",1,0,"C",1);
											$pdf->SetXY($x+73,$y);
											$pdf->Cell(10,5,"% Beca",1,0,"C",1);
											$pdf->SetXY($x+83,$y);
											$pdf->Cell(12,5,"Pagos Anti.",1,0,"C",1);
											$pdf->SetXY($x+95,$y);
											$pdf->Cell(12,5,"Desc. Sem",1,0,"C",1);
											$pdf->SetXY($x+107,$y);
											$pdf->Cell(10,5,"Rec",1,0,"C",1);
											$pdf->SetXY($x+117,$y);
											$pdf->Cell(10,5,"GAFE",1,0,"C",1);
											$pdf->SetXY($x+127,$y);
											$pdf->Cell(38,5,"Observaciones",1,0,"C",1);
											$pdf->SetXY($x+165,$y);
											$pdf->Cell(10,5,"Importe",1,0,"C",1);
											$pdf->SetXY($x+175,$y);
											$pdf->Cell(10,5,"Fecha",1,0,"C",1);
											$pdf->SetXY($x+185,$y);
											$pdf->Cell(15,5,"Forma Pago",1,0,"C",1);
										break;
										
										case 9:
											$pdf->SetXY($x,$y);
											$pdf->Cell(10,5,"Folio",1,0,"C",1);
											$pdf->SetXY($x+10,$y);
											$pdf->Cell(50,5,"Nombre",1,0,"C",1);
											$pdf->SetXY($x+60,$y);
											$pdf->Cell(20,5,"Carrera",1,0,"C",1);
											$pdf->SetXY($x+80,$y);
											$pdf->Cell(10,5,"% Beca",1,0,"C",1);
											$pdf->SetXY($x+90,$y);
											$pdf->Cell(65,5,"Observaciones",1,0,"C",1);
											$pdf->SetXY($x+155,$y);
											$pdf->Cell(15,5,"Importe",1,0,"C",1);
											$pdf->SetXY($x+170,$y);
											$pdf->Cell(15,5,"Fecha",1,0,"C",1);
											$pdf->SetXY($x+185,$y);
											$pdf->Cell(15,5,"Forma Pago",1,0,"C",1);
										break;
										
										case 11:
											$pdf->SetXY($x,$y);
											$pdf->Cell(10,5,"Folio",1,0,"C",1);
											$pdf->SetXY($x+10,$y);
											$pdf->Cell(60,5,"Nombre",1,0,"C",1);
											$pdf->SetXY($x+70,$y);
											$pdf->Cell(20,5,"Carrera",1,0,"C",1);
											$pdf->SetXY($x+90,$y);
											$pdf->Cell(65,5,"Observaciones",1,0,"C",1);
											$pdf->SetXY($x+155,$y);
											$pdf->Cell(15,5,"Importe",1,0,"C",1);
											$pdf->SetXY($x+170,$y);
											$pdf->Cell(15,5,"Fecha",1,0,"C",1);
											$pdf->SetXY($x+185,$y);
											$pdf->Cell(15,5,"Forma Pago",1,0,"C",1);
										break;
										
										case 12:
											$pdf->SetXY($x,$y);
											$pdf->Cell(10,5,"Folio",1,0,"C",1);
											$pdf->SetXY($x+10,$y);
											$pdf->Cell(60,5,"Nombre",1,0,"C",1);
											$pdf->SetXY($x+70,$y);
											$pdf->Cell(20,5,"Carrera",1,0,"C",1);
											$pdf->SetXY($x+90,$y);
											$pdf->Cell(65,5,"Observaciones",1,0,"C",1);
											$pdf->SetXY($x+155,$y);
											$pdf->Cell(15,5,"Importe",1,0,"C",1);
											$pdf->SetXY($x+170,$y);
											$pdf->Cell(15,5,"Fecha",1,0,"C",1);
											$pdf->SetXY($x+185,$y);
											$pdf->Cell(15,5,"Forma Pago",1,0,"C",1);
										break;
										
										case 13:
											$pdf->SetXY($x,$y);
											$pdf->Cell(10,5,"Folio",1,0,"C",1);
											$pdf->SetXY($x+10,$y);
											$pdf->Cell(50,5,"Nombre",1,0,"C",1);
											$pdf->SetXY($x+60,$y);
											$pdf->Cell(20,5,"Carrera",1,0,"C",1);
											$pdf->SetXY($x+80,$y);
											$pdf->Cell(10,5,"% Beca",1,0,"C",1);
											$pdf->SetXY($x+90,$y);
											$pdf->Cell(20,5,"Desc. Extra",1,0,"C",1);
											$pdf->SetXY($x+110,$y);
											$pdf->Cell(10,5,"Rec.",1,0,"C",1);
											$pdf->SetXY($x+120,$y);
											$pdf->Cell(45,5,"Observaciones",1,0,"C",1);
											$pdf->SetXY($x+165,$y);
											$pdf->Cell(10,5,"Importe",1,0,"C",1);
											$pdf->SetXY($x+175,$y);
											$pdf->Cell(10,5,"Fecha",1,0,"C",1);
											$pdf->SetXY($x+185,$y);
											$pdf->Cell(15,5,"Forma Pago",1,0,"C",1);
										break;
											
										default:
											$pdf->SetXY($x,$y);
											$pdf->Cell(10,5,"Folio",1,0,"C",1);
											$pdf->SetXY($x+10,$y);
											$pdf->Cell(80,5,"Nombre",1,0,"C",1);
											$pdf->SetXY($x+90,$y);
											$pdf->Cell(65,5,"Carrera",1,0,"C",1);
											$pdf->SetXY($x+155,$y);
											$pdf->Cell(15,5,"Importe",1,0,"C",1);
											$pdf->SetXY($x+170,$y);
											$pdf->Cell(15,5,"Fecha",1,0,"C",1);
											$pdf->SetXY($x+185,$y);
											$pdf->Cell(15,5,"Forma Pago",1,0,"C",1);
									} // fin switch($id_concepto)
								
								while($fila_pagos = mysql_fetch_array($resultado_pagos))
									{
										$pdf->SetFont("Arial","",$Contenido);
										
										if ($fila_pagos["id_usuario"] == 1)			//ALUMNO
											{
												$sql_alumno = "SELECT id_ciclo_escolar,id_carrera, id_semestre, apellido_paterno, apellido_materno, nombre FROM alumno WHERE cuenta = '".$fila_pagos["cuenta"]."';";
											}
										else if ($fila_pagos["id_usuario"] == 2)	//PROFESOR
											{
												$sql_alumno = "SELECT apellido_paterno, apellido_materno, nombre FROM profesor WHERE id_profesor = '".$fila_pagos["cuenta"]."';";
											}
										else if ($fila_pagos["id_usuario"] == 3)	//ASPIRANTE
											{
												$sql_alumno = "SELECT id_carrera, apellido_paterno, apellido_materno, nombre FROM aspirante WHERE id_aspirante = '".$fila_pagos["cuenta"]."';";
											}
										else if ($fila_pagos["id_usuario"] == 4)	//SIN REGISTRO
											{
												$sql_alumno = "SELECT * FROM pagos_exalumnos WHERE id_pago_registro = '".$fila_pagos["id_pago_registro"]."';";
											}
										
										$resultado_alumno = mysql_query($sql_alumno,$conexion);
										$fila_alumno = mysql_fetch_array($resultado_alumno);
										
										$nombre = trim($fila_alumno["apellido_paterno"] ." ". $fila_alumno["apellido_materno"] ." ". $fila_alumno["nombre"]);
										$sql_carrera = "SELECT abreviatura FROM carrera WHERE id_carrera = '".$fila_alumno["id_carrera"]."';";
										$resultado_carrera = mysql_query($sql_carrera, $conexion);
										$fila_carrera = mysql_fetch_array($resultado_carrera);
										
										$carrera = $fila_carrera["abreviatura"];
										
										$alumnos++;
										
										$array_importe[$j++] =  $fila_pagos["importe"];
										$importe = array_sum($array_importe);
										
										$array_importe_total[$k++] =  $fila_pagos["importe"];
										$importe_total = array_sum($array_importe_total);
										
										$i++;
										
										if ($i%2 == 0) $pdf->SetFillColor(208,229,247);
										else $pdf->SetFillColor(255,255,255);
										
										if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
											{
												$pdf->AddPage();
												$renglon=1;
												$pagina++;
												$y=25;
											}
										else
											{
												$y+=$alto_celda;
												$renglon++;
											}
										
										if($fila_pagos["cancelado"] != "SI")
											{
												$total_tipo_pago[$fila_pagos["id_tipo_pago"]]=$total_tipo_pago[$fila_pagos["id_tipo_pago"]]+$fila_pagos["importe"];
												$numero_pagos[$fila_pagos["id_tipo_pago"]]=$numero_pagos[$fila_pagos["id_tipo_pago"]]+1;
											}
										
										$sql_tipo_pago="SELECT * FROM tipo_pago WHERE id_tipo_pago='".$fila_pagos["id_tipo_pago"]."';";
										$resultado_tipo_pago=mysql_query($sql_tipo_pago, $conexion);
										$fila_tipo_pago=mysql_fetch_array($resultado_tipo_pago);
										
										if ($id_concepto == 2)
											{
												$sql_colegiaturas = "SELECT * FROM pagos_colegiaturas WHERE id_pago_registro = '".$fila_pagos["id_pago_registro"]."';";
												$resultado_colegiaturas = mysql_query($sql_colegiaturas, $conexion);
												$fila_colegiaturas = mysql_fetch_array($resultado_colegiaturas);
												
												$mes=$fila_colegiaturas["mes"];
												
												$sql_beca_mes="SELECT mes_".$mes." FROM clave JOIN becas_mes USING (clave) WHERE cuenta='".$fila_pagos["cuenta"]."' AND id_ciclo_escolar= '".$fila_alumno["id_ciclo_escolar"]."';";
												$resultado_beca_mes=mysql_query($sql_beca_mes, $conexion);
												@$fila_beca_mes=mysql_fetch_array($resultado_beca_mes);
												
												$pdf->SetXY($x,$y);
												$pdf->Cell(8,5,$fila_pagos["id_pago_registro"],1,0,"C",1);
												$pdf->SetXY($x+8,$y);
												$pdf->Cell(50,5,"",1,0,"C",1);
												$pdf->SetXY($x+8,$y);
												
												if ($fila_pagos["cancelado"] == "SI") $nombre="CANCELADO";
												
												$pdf->MultiCell(50,2.5,$nombre,0,"L");
												$pdf->SetXY($x+58,$y);
												$pdf->Cell(15,5,$carrera,1,0,"C",1);
												$pdf->SetXY($x+73,$y);
												$pdf->Cell(10,5,$fila_beca_mes["mes_".$mes],1,0,"C",1);
												$pdf->SetXY($x+83,$y);
												$pdf->Cell(12,5,$fila_colegiaturas["descuento_pago"],1,0,"C",1);
												$pdf->SetXY($x+95,$y);
												$pdf->Cell(12,5,$fila_colegiaturas["descuento_semestral"],1,0,"C",1);
												$pdf->SetXY($x+107,$y);
												$pdf->Cell(10,5,$fila_colegiaturas["recargos"],1,0,"C",1);
												$pdf->SetXY($x+117,$y);
												$pdf->Cell(10,5,$fila_colegiaturas["gafe"],1,0,"C",1);
												$pdf->SetXY($x+127,$y);
												$pdf->Cell(38,5,"",1,0,"C",1);
												$pdf->SetXY($x+127,$y);
												$pdf->MultiCell(38,2.5,$fila_colegiaturas["observaciones"],0,"L");
												$pdf->SetXY($x+165,$y);
												$pdf->Cell(10,5,number_format($fila_pagos["importe"],2),1,0,"C",1);
												$pdf->SetXY($x+175,$y);
												$pdf->Cell(10,5,Fecha($fila_pagos["fecha"]),1,0,"C",1);
												$pdf->SetXY($x+185,$y);
												$pdf->Cell(15,5,$fila_tipo_pago["tipo_pago"],1,0,"C",1);
											} // FIN if ($id_concepto == 2)
										
										if ($id_concepto == 9)
											{
												$sql_inscripcion = "SELECT * FROM pagos_inscripcion WHERE id_pago_registro = '".$fila_pagos["id_pago_registro"]."';";
												$resultado_inscripcion = mysql_query($sql_inscripcion, $conexion);
												$fila_inscripcion = mysql_fetch_array($resultado_inscripcion);
												
												$pdf->SetXY($x,$y);
												$pdf->Cell(10,5,$fila_pagos["id_pago_registro"],1,0,"C",1);
												$pdf->SetXY($x+10,$y);
												$pdf->Cell(50,5,"",1,0,"C",1);
												$pdf->SetXY($x+10,$y);
												
												if ($fila_pagos["cancelado"] == "SI") $nombre="CANCELADO";
												
												$pdf->MultiCell(50,2.5,$nombre,0,"L");
												$pdf->SetXY($x+60,$y);
												$pdf->Cell(20,5,$carrera,1,0,"C",1);
												$pdf->SetXY($x+80,$y);
												$pdf->Cell(10,5,$fila_inscripcion["beca"],1,0,"C",1);
												$pdf->SetXY($x+90,$y);
												$pdf->Cell(65,5,"",1,0,"C",1);
												$pdf->SetXY($x+90,$y);
												$pdf->MultiCell(65,2.5,$fila_inscripcion["observaciones"],0,"L");
												$pdf->SetXY($x+155,$y);
												$pdf->Cell(15,5,number_format($fila_pagos["importe"],2),1,0,"C",1);
												$pdf->SetXY($x+170,$y);
												$pdf->Cell(15,5,Fecha($fila_pagos["fecha"]),1,0,"C",1);
												$pdf->SetXY($x+185,$y);
												$pdf->Cell(15,5,$fila_tipo_pago["tipo_pago"],1,0,"C",1);
											}
												
										if ($id_concepto == 11)
											{
												$sql_otro = "SELECT otro FROM pagos_otros WHERE id_pago_registro = '".$fila_pagos["id_pago_registro"]."';";
												$resultado_otro = mysql_query($sql_otro, $conexion);
												$fila_otro = mysql_fetch_array($resultado_otro);
												
												$pdf->SetXY($x,$y);
												$pdf->Cell(10,5,$fila_pagos["id_pago_registro"],1,0,"C",1);
												$pdf->SetXY($x+10,$y);
												$pdf->Cell(60,5,"",1,0,"C",1);
												$pdf->SetXY($x+10,$y);
												
												if ($fila_pagos["cancelado"] == "SI") $nombre="CANCELADO";
												
												$pdf->MultiCell(50,2.5,$nombre,0,"L");
												$pdf->SetXY($x+70,$y);
												$pdf->Cell(20,5,$carrera,1,0,"C",1);
												$pdf->SetXY($x+90,$y);
												$pdf->Cell(65,5,"",1,0,"C",1);
												$pdf->SetXY($x+90,$y);
												$pdf->MultiCell(65,2.5,$fila_otro["otro"],0,"L");
												$pdf->SetXY($x+155,$y);
												$pdf->Cell(15,5,number_format($fila_pagos["importe"]),1,0,"C",1);
												$pdf->SetXY($x+170,$y);
												$pdf->Cell(15,5,Fecha($fila_pagos["fecha"]),1,0,"C",1);
												$pdf->SetXY($x+185,$y);
												$pdf->Cell(15,5,$fila_tipo_pago["tipo_pago"],1,0,"C",1);
											} // FIN if ($id_concepto == 2)
										
										if ($id_concepto == 12)
											{
												$sql_preinscripcion = "SELECT observaciones FROM pagos_preinscripcion WHERE id_pago_registro = '".$fila_pagos["id_pago_registro"]."';";
												$resultado_preinscripcion = mysql_query($sql_preinscripcion,$conexion);
												$fila_preinscripcion = mysql_fetch_array($resultado_preinscripcion);
												
												$pdf->SetXY($x,$y);
												$pdf->Cell(10,5,$fila_pagos["id_pago_registro"],1,0,"C",1);
												$pdf->SetXY($x+10,$y);
												$pdf->Cell(60,5,"",1,0,"C",1);
												$pdf->SetXY($x+10,$y);
												
												if ($fila_pagos["cancelado"] == "SI") $nombre="CANCELADO";
												
												$pdf->MultiCell(50,2.5,$nombre,0,"L");
												$pdf->SetXY($x+70,$y);
												$pdf->Cell(20,5,$carrera,1,0,"C",1);
												$pdf->SetXY($x+90,$y);
												$pdf->Cell(65,5,"",1,0,"C",1);
												$pdf->SetXY($x+90,$y);
												$pdf->MultiCell(65,2.5,$fila_preinscripcion["observaciones"],0,"L");
												$pdf->SetXY($x+155,$y);
												$pdf->Cell(15,5,number_format($fila_pagos["importe"]),1,0,"C",1);
												$pdf->SetXY($x+170,$y);
												$pdf->Cell(15,5,Fecha($fila_pagos["fecha"]),1,0,"C",1);
												$pdf->SetXY($x+185,$y);
												$pdf->Cell(15,5,$fila_tipo_pago["tipo_pago"],1,0,"C",1);
											} // FIN if ($id_concepto == 12)
												
										if ($id_concepto == 13)
											{
												$sql_reinscripcion = "SELECT * FROM pagos_reinscripcion WHERE id_pago_registro = '".$fila_pagos["id_pago_registro"]."';";
												$resultado_reinscripcion = mysql_query($sql_reinscripcion, $conexion);
												$fila_reinscripcion = mysql_fetch_array($resultado_reinscripcion);
												
												$pdf->SetXY($x,$y);
												$pdf->Cell(10,5,$fila_pagos["id_pago_registro"],1,0,"C",1);
												$pdf->SetXY($x+10,$y);
												$pdf->Cell(50,5,"",1,0,"C",1);
												$pdf->SetXY($x+10,$y);
												
												if ($fila_pagos["cancelado"] == "SI") $nombre="CANCELADO";
												
												$pdf->MultiCell(50,2.5,$nombre,0,"L");
												$pdf->SetXY($x+60,$y);
												$pdf->Cell(20,5,$carrera,1,0,"C",1);
												$pdf->SetXY($x+80,$y);
												$pdf->Cell(10,5,$fila_reinscripcion["beca"],1,0,"C",1);
												$pdf->SetXY($x+90,$y);
												$pdf->Cell(20,5,$fila_reinscripcion["descuento_extra"],1,0,"C",1);
												$pdf->SetXY($x+110,$y);
												$pdf->Cell(10,5,$fila_reinscripcion["recargos"],1,0,"C",1);
												$pdf->SetXY($x+120,$y);
												$pdf->Cell(45,5,"",1,0,"C",1);
												$pdf->SetXY($x+120,$y);
												$pdf->MultiCell(45,2.5,$fila_reinscripcion["observaciones"],0,"L");
												$pdf->SetXY($x+165,$y);
												$pdf->Cell(10,5,number_format($fila_pagos["importe"]),1,0,"C",1);
												$pdf->SetXY($x+175,$y);
												$pdf->Cell(10,5,Fecha($fila_pagos["fecha"]),1,0,"C",1);
												$pdf->SetXY($x+185,$y);
												$pdf->Cell(15,5,$fila_tipo_pago["tipo_pago"],1,0,"C",1);
											} // FIN if ($id_concepto == 13)
									}// FIN while($fila_pagos = mysql_fetch_array($resultado_pagos))
								
								if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
									{
										$pdf->AddPage();
										$pagina++;
										$renglon=1;
										$y=25;
									}
								else
									{
										$y+=$alto_celda;
										$renglon++;
									}
								
								$pdf->SetFont("Arial","B",$Titulos);
								$pdf->SetFillColor(166,162,209);
								$pdf->SetXY($x,$y);
								$pdf->Cell(100,5,"Alumnos: ".$alumnos,"LTB",0,"L",1);
								$pdf->SetXY($x+100,$y);
								$pdf->Cell(100,5,"Subtotal: ".'$ '.number_format($importe,2),"RTB",0,"R",1);
								$alumnos = 0;
								$array_importe = "";
								$i = 0;
							} // FIN if ($registros_pagos > 0)
						
						/*if(($pagina==1 && $renglon>40)||($pagina>1 && $renglon>42)){
						$pdf->AddPage();
						$pagina++;
						$renglon=1;
						$y=25;
						}else{
						$y+=$alto_celda;
						$renglon++;
						}*/
					} // FIN for ($id_carrera_tipo = 1; $id_carrera_tipo <= $carrera_tipo; $id_carrera_tipo++)
				
				if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
					{
						$pdf->AddPage();
						$pagina++;
						$renglon=1;
						$y=25;
					}
				else
					{
						$y+=$alto_celda;
						$renglon++;
					}
				
				$pdf->SetFont("Arial","B",$Titulos);
				$pdf->SetFillColor(210,255,244);
				$pdf->SetXY($x,$y);
				$pdf->Cell(200,5,"TOTAL DE ".$fila_concepto["concepto"].": ".'$ '.number_format($importe_total,2),1,0,"R",1);
				$array_importe_total = "";
				
				if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
					{
						$pdf->AddPage();
						$pagina++;
						$renglon=1;
						$y=25;
					}
				else
					{
						$y+=$alto_celda;
						$renglon++;
					}
				
				if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
					{
						$pdf->AddPage();
						$pagina++;
						$renglon=1;
						$y=25;
					}
				else
					{
						$y+=$alto_celda;
						$renglon++;
					}
			}
		else
			{	//REGISTROS DIFERENTES A COLEGIATURA, INSCRIPCIÓN, OTRO, PREINSCRIPCIÓN Y REINSCRIPCIÓN
				if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
					{
						$pdf->AddPage();
						$pagina++;
						$renglon=1;
						$y=25;
					}
				else
					{
						$y+=$alto_celda;
						$renglon++;
					}	
				
				//$sql_pagos = "SELECT * FROM pagos_registros JOIN pagos_concepto USING (id_concepto) WHERE pagos_registros.id_concepto = '".$id_concepto."' AND fecha BETWEEN '".$_POST["Fecha_Inicio"]."' AND '".$_POST["Fecha_Fin"]."' ORDER BY fecha, id_carrera_tipo, id_pago_registro;";
				$sql_pagos = "SELECT * FROM pagos_registros JOIN pagos_concepto USING (id_concepto) WHERE pagos_registros.id_concepto = '".$id_concepto."' AND id_forma_pago = 2 AND fecha BETWEEN '".$_POST["Fecha_Inicio"]."' AND '".$_POST["Fecha_Fin"]."' ORDER BY fecha, id_carrera_tipo, id_pago_registro;";
				$resultado_pagos = mysql_query($sql_pagos,$conexion);
				$registros_pagos = mysql_num_rows($resultado_pagos);
				
				if ($registros_pagos > 0)
					{
						$pdf->SetFont("Arial","B",$Titulos);
						$pdf->SetFillColor(127,178,240);
						$pdf->SetXY($x,$y);
						$pdf->Cell(10,5,"Folio",1,0,"C",1);
						$pdf->SetXY($x+10,$y);
						$pdf->Cell(75,5,"Nombre",1,0,"C",1);
						$pdf->SetXY($x+85,$y);
						$pdf->Cell(20,5,"Carrera",1,0,"C",1);
						$pdf->SetXY($x+105,$y);
						$pdf->Cell(15,5,"Importe",1,0,"C",1);
						$pdf->SetXY($x+120,$y);
						$pdf->Cell(15,5,"Fecha",1,0,"C",1);
						$pdf->SetXY($x+135,$y);
						$pdf->Cell(15,5,"Forma Pago",1,0,"C",1);
						$pdf->SetXY($x+150,$y);
						$pdf->Cell(50,5,"Observaciones",1,0,"C",1);
						
						while($fila_pagos = mysql_fetch_array($resultado_pagos))
							{
								if ($fila_pagos["id_usuario"] == 1)
									{
										$sql_alumno = "SELECT id_carrera, id_semestre, apellido_paterno, apellido_materno, nombre FROM alumno WHERE cuenta = '".$fila_pagos["cuenta"]."';";
									}
								else if ($fila_pagos["id_usuario"] == 2)
									{
										$sql_alumno = "SELECT apellido_paterno, apellido_materno, nombre FROM profesor WHERE id_profesor = '".$fila_pagos["cuenta"]."';";
									}
								else if ($fila_pagos["id_usuario"] == 3)
									{
										$sql_alumno = "SELECT id_carrera, nombre FROM pagos_preinscripcion WHERE id_pago_registro = '".$fila_pagos["id_pago_registro"]."';";
									}
								else if ($fila_pagos["id_usuario"] == 4)
									{
										$sql_alumno = "SELECT * FROM pagos_exalumnos WHERE id_pago_registro = '".$fila_pagos["id_pago_registro"]."';";
									}
								
								$resultado_alumno = mysql_query($sql_alumno,$conexion);
								$fila_alumno = mysql_fetch_array($resultado_alumno);
								
								$sql_carrera = "SELECT abreviatura FROM carrera WHERE id_carrera = '".$fila_alumno["id_carrera"]."';";
								$resultado_carrera = mysql_query($sql_carrera,$conexion);
								$fila_carrera = mysql_fetch_array($resultado_carrera);
								
								$alumnos++;
								
								$array_importe[$j++] =  $fila_pagos["importe"];
								$importe = array_sum($array_importe);
								
								$i++;
								
								if ($i%2 == 0) $pdf->SetFillColor(208,229,247);
								else $pdf->SetFillColor(255,255,255);
								
								if($fila_pagos["cancelado"] != "SI")
									{
										$total_tipo_pago[$fila_pagos["id_tipo_pago"]]=$total_tipo_pago[$fila_pagos["id_tipo_pago"]]+$fila_pagos["importe"];
										$numero_pagos[$fila_pagos["id_tipo_pago"]]=$numero_pagos[$fila_pagos["id_tipo_pago"]]+1;
									}
								
								$sql_tipo_pago="SELECT * FROM tipo_pago WHERE id_tipo_pago='".$fila_pagos["id_tipo_pago"]."';";
								$resultado_tipo_pago=mysql_query($sql_tipo_pago,$conexion);
								$fila_tipo_pago=mysql_fetch_array($resultado_tipo_pago);
								
								if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
									{
										$pdf->AddPage();
										$pagina++;
										$renglon=1;
										$y=25;
									}
								else
									{
										$y+=$alto_celda;
										$renglon++;
									}
								
								$pdf->SetXY($x,$y);
								$pdf->SetFont("Arial","",$Contenido);
								$pdf->Cell(10,5,$fila_pagos["id_pago_registro"],1,0,"C",1);
								$pdf->SetXY($x+10,$y);
								
								if ($fila_pagos["cancelado"] == "SI")
									$nombre="CANCELADO";
								else
									$nombre=$fila_alumno["apellido_paterno"] ." ". $fila_alumno["apellido_materno"] ." ". $fila_alumno["nombre"];
								$pdf->Cell(75,5,$nombre,1,0,"L",1);
								$pdf->SetXY($x+85,$y);
								$pdf->Cell(20,5,$fila_carrera["abreviatura"],1,0,"C",1);
								$pdf->SetXY($x+105,$y);
								$pdf->Cell(15,5,number_format($fila_pagos["importe"],2),1,0,"C",1);
								$pdf->SetXY($x+120,$y);
								$pdf->Cell(15,5,Fecha($fila_pagos["fecha"]),1,0,"C",1);
								$pdf->SetXY($x+135,$y);
								$pdf->Cell(15,5,$fila_tipo_pago["tipo_pago"],1,0,"C",1);
								$pdf->SetXY($x+150,$y);
								$pdf->Cell(50,5,$fila_pagos["observaciones"],1,0,"C",1);
							} // FIN while($fila_pagos = mysql_fetch_array($resultado_pagos))
						
						if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
							{
								$pdf->AddPage();
								$pagina++;
								$renglon=1;
								$y=25;
							}
						else
							{
								$y+=$alto_celda;
								$renglon++;
							}
						
						$pdf->SetFont("Arial","B",$Titulos);
						$pdf->SetFillColor(166,162,209);
						$pdf->SetXY($x,$y);
						$pdf->Cell(100,5,"Alumnos: ".$alumnos,"LTB",0,"L",1);
						$pdf->SetXY($x+100,$y);
						$pdf->Cell(100,5,"Total: ".'$ '.number_format($importe,2),"RTB",0,"R",1);
					} // FIN if ($registros_pagos > 0)
				
				if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
					{
						$pdf->AddPage();
						$pagina++;
						$renglon=1;
						$y=25;
					}
				else
					{
						$y+=$alto_celda;
						$renglon++;
					}
				
				if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
					{
						$pdf->AddPage();
						$pagina++;
						$renglon=1;
						$y=25;
					}
				else
					{
						$y+=$alto_celda;
						$renglon++;
					}
				
				$alumnos = 0;
				$array_importe = "";
				
				$i = 0;
			} // fin else
	} // fin while($fila_id_concepto = mysql_fetch_array($resultado_id_concepto))

if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
	{
		$pdf->AddPage();
		$pagina++;
		$renglon=1;
		$y=25;
	}
else
	{
		$y+=$alto_celda;
		$renglon++;
	}

if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
	{
		$pdf->AddPage();
		$pagina++;
		$renglon=1;
		$y=25;
	}
else
	{
		$y+=$alto_celda;
		$renglon++;
	}

$pdf->SetFont("Arial","B",$Titulos+1);
$pdf->SetFillColor(127,178,240);
$pdf->SetXY($x+140,$y);
$pdf->Cell(20,5,"Forma Pago",1,0,"C",1);
$pdf->SetXY($x+160,$y);
$pdf->Cell(20,5,"No. Pagos",1,0,"C",1);
$pdf->SetXY($x+180,$y);
$pdf->Cell(20,5,"Monto",1,0,"C",1);

if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
	{
		$pdf->AddPage();
		$pagina++;
		$renglon=1;
		$y=25;
	}
else
	{
		$y+=$alto_celda;
		$renglon++;
	}

$sql_tipo_pago="SELECT * FROM tipo_pago ORDER BY id_tipo_pago;";
$resultado_tipo_pago=mysql_query($sql_tipo_pago, $conexion);

while($fila_tipo_pago=mysql_fetch_array($resultado_tipo_pago))
	{
		$pdf->SetFont("Arial","B",$Contenido+1);
		$pdf->SetXY($x+140,$y);
		$pdf->Cell(20,5,$fila_tipo_pago["tipo_pago"],1,0,"C");
		$pdf->SetXY($x+160,$y);
		$pdf->Cell(20,5,$numero_pagos[$fila_tipo_pago["id_tipo_pago"]],1,0,"C");
		$pdf->SetXY($x+180,$y);
		$pdf->Cell(20,5,"$ ".number_format($total_tipo_pago[$fila_tipo_pago["id_tipo_pago"]],2),1,0,"R");
		
		if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
			{
				$pdf->AddPage();
				$pagina++;
				$renglon=1;
				$y=25;
			}
		else
			{
				$y+=$alto_celda;
				$renglon++;
			}
	}

//$sql_total_general = "SELECT SUM(importe) AS total_general FROM pagos_registros WHERE fecha BETWEEN '".$_POST["Fecha_Inicio"]."' AND '".$_POST["Fecha_Fin"]."';";
$sql_total_general = "SELECT SUM(importe) AS total_general FROM pagos_registros WHERE id_forma_pago = 2 AND fecha BETWEEN '".$_POST["Fecha_Inicio"]."' AND '".$_POST["Fecha_Fin"]."';";
$resultado_total_general = mysql_query($sql_total_general, $conexion);
$fila_total_general = mysql_fetch_array($resultado_total_general);

if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
	{
		$pdf->AddPage();
		$pagina++;
		$renglon=1;
		$y=25;
	}
else
	{
		$y+=$alto_celda;
		$renglon++;
	}

if(($pagina==1 && $renglon>39)||($pagina>1 && $renglon>42))
	{
		$pdf->AddPage();
		$pagina++;
		$renglon=1;
		$y=25;
	}
else
	{
		$y+=$alto_celda;
		$renglon++;
	}

$pdf->SetFont("Arial","B",$Titulos);
$pdf->SetFillColor(166,162,209);
$pdf->SetXY($x,$y);
$pdf->Cell(200,5,"T O T A L  G E N E R A L : ".'$ '.number_format($fila_total_general["total_general"],2),1,0,"R",1);

$pdf->Output();
?>