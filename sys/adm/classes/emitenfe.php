<?php
if(!isset($_POST['cod_pedido'])){exit;}
$url = "http://sistema.formulapizzaria.com.br/classes/notas/";
$p = $_POST;
require 'criarnotapdf.class.php';
$cria = new Notapdf();
	#Gera img qrCode
$cria->linkDaNotaFiscal = $p['link_nota'];
$cria->pastaSalvamentoQrCodeArquivo = "qrCodes";
$cria->nomeDaImagemQrCode = $p['cod_pedido'].".png";
$cria->geraQrCode();
	#JuntaQrCodecomNota
$cria->linkHtmlDaNota = $p['link_cupom'];
$cria->pastaSalvamentoQrCodeArquivo = "/classes/qrCodes/";
$cria->juntaNotaComQrCode();

	#Converte para Pdf
$cria->caminhoNota = 'notas/';
$cria->nomeNota = $p['cod_pedido'].'.pdf';
$cria->criarPdf();

	#Imprime a Nota
$cria->notaEmPdf = $cria->nomeNota;
$cria->imprimeNota();
echo $url.$p['cod_pedido'].'.pdf';