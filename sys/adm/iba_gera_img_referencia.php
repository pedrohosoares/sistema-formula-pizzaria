<?php

$altura = (preg_match('/[0-9]+/', $_GET['altura'])) ? $_GET['altura'] : 1;
$largura = (preg_match('/[0-9]+/', $_GET['largura'])) ? $_GET['largura'] : 1;

$im = imagecreate($largura, $altura);
$branco = imagecolorallocate($im, 255, 255, 255);
$azul = imagecolorallocate($im, 65, 114, 180);
$preto = imagecolorallocate($im, 31, 31, 31);
$cor_fonte = imagecolorallocate($im, 255, 255, 255);

ImageFill($im, 0, 0, $branco);
ImageFilledRectangle($im, 5, 5, $largura - 6, $altura - 6, $azul);
ImageRectangle($im, 0, 0, $largura - 1, $altura - 1, $preto);

//imagestring ($im, 5, 10, 10, 'Banner de Referência', $cor_fonte);
//imagestring ($im, 2, 10, 27, $altura.' x '.$largura.' (A x L)', $cor_fonte);
imagestring ($im, 5, 10, 10, $altura.' x '.$largura.' (A x L)', $cor_fonte);

header ("Content-type: image/gif");
imagegif($im);
?>