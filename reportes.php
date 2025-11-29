<?php
ob_start();

include('conexion.php');

if (isset($_GET['idNota']))
    $idNota = $_GET['idNota'];
$sqlSearchNotas = ("select * from notas n 
                            JOIN clientes c ON n.id_cliente  = c.id_CLIENTE 
                            JOIN equipo e ON n.equipo =  e.id_equipo 
                            JOIN servicio s ON n.servicio =  s.id_servicio
                            JOIN estadoservicio es ON n.estado =  es.id_estado 
                            WHERE id_nota='" . $idNota . "' LIMIT 1 ");
$queryNotas = mysqli_query($conn, $sqlSearchNotas);
$dataNota = mysqli_fetch_array($queryNotas);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SoftGaresp</title>
    <link rel="icon" href="img/logosAZUL.png" />
    <!-- ARCHIVOS PERSONALIZADOS -->
    <link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/softgaresp/css/styleReport.css" />
    <!-- FUENTES -->
    <link rel="stylesheet"
        href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/softgaresp/fontawesome-free-6.5.1-web/fontawesome-free-6.5.1-web/css/all.min.css" />
</head>

<body>
    <div class="container">
        <div id="title">Nota de entrada</div>
        <div class="headReport">
            <div id="logo">
                <img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/softgaresp/img/logos2.jpg" id="imglogo">
                <span id="dataFecha">
                    <p>RFC: GAEA941128IT5</p>
                    <!-- <p>Fecha de entrada: </p> -->
                    <p id="fechaalta">
                        <?php echo $dataNota['fecha_alta'] ?>
                    </p>
                </span>

            </div>
            <div id="servicios">
                <p>Servicio Técnico</p>
                <p id="compimp">computadoras e impresoras</p>
                <p>Cotización de equipos de cómputo</p>
                <p>Desarrollo de paginas web | ecommerce</p>
            </div>
            <div id="notaid">
                <p class="titulo">Num. nota</p>
                <p id="idnota">
                    <?php echo $dataNota['id_nota'] ?>
                </p>
            </div>
        </div>

        <section id="bodyReport">

            <h5 class="titulo">Datos del cliente</h5>
            </p>
            <p class="titlehead">Nombre del cliente:

            </p>
            <p>
                <?php echo $dataNota['nombre'] . " " . $dataNota["apellidos"] ?>
            </p>


            <div class="info">
                <div>
                    <h5 class="titulo">Información del equipo</h5>
                    <table border=1>
                        <thead>
                            <tr>
                                <th>Equipo</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                            </tr>
                        <tbody>
                            <td>
                                <?php echo $dataNota['descripcionEquipo'] ?>
                            </td>
                            <td>
                                <?php echo $dataNota['marca'] ?>
                            </td>
                            <td>
                                <?php echo $dataNota['modelo'] ?>
                            </td>

                        </tbody>
                        </thead>
                    </table>
                </div>


                <div>
                    <h5 class="titulo">servicio</h5>
                    <table table border=1>
                        <thead class="thead-light">
                            <tr>
                                <th>Descripción</th>
                            </tr>
                        <tbody>

                            <td>
                                <?php echo $dataNota['descripcionServicio'] ?>
                            </td>

                        </tbody>
                        </thead>
                    </table>
                </div>
            </div>

            <div id="cantidades">
                <!--  <h5 class="titulo">Pago</h5> -->


                <p>Total servicio: <span><?php echo '$' . $dataNota['costoServicio'] ?>.00</span></p>

                <?php

                if ($dataNota['anticipo'] != 0) {
                    $total = $dataNota['costoServicio'];
                    $anticipo = $dataNota['anticipo'];
                    $restante = $total - $anticipo;
                ?>

                    <p>Anticipo: <span><?php echo '$' . $anticipo ?></span></p>


                    <p>Restante: <span><?php echo '$' . $restante ?></span></p>



                    <!--  <h3>Pagado: <? php/* echo $dataNota['pagado']*/ ?> -->
                <?php
                }
                ?>
            </div>
        </section>

        <div id="footerReport">
            <p id="direccion">Los pinos #17, col. La Mixteca, Tlaxiaco, Oaxaca</p>
            <div>
                <i class="fa-brands fa-whatsapp"></i> <span id="contacto">9531272241</span>
            </div>
            <div>
                <i class="fa-brands fa-facebook"></i> <span>GARESP - Servicio informatico</span>
            </div>
        </div>

</body>

</html>

<?php

$html = ob_get_clean();
require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->set(array('isRemoteEnabled' => true));
$dompdf->setOptions($options);
$dompdf->loadHtml($html);
$dompdf->set_paper(array(0, 0, 609.4488, 396.85), 'mediacarta');
$dompdf->render();
$dompdf->stream("nota_.pdf", array("Attachment" => false));

?> 