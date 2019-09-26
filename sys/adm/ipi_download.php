<?php
 require_once '../../bd.php';
 require_once '../lib/php/formatacao.php';
 require_once '../lib/php/formulario.php';
 require_once '../lib/php/mensagem.php';

 $chave_primaria = 'cod_softwares';
 $tabela = 'ipi_softwares'; 
 $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
 $codigo = base64_decode(validaVarGet($chave_primaria));
 $obj_download = executaBuscaSimples("SELECT * FROM $tabela ips INNER JOIN ipi_software_permissoes isp ON (isp.$chave_primaria = ips.$chave_primaria) WHERE ips.$chave_primaria = $codigo AND ((isp.cod_pizzarias IN ($cod_pizzarias_usuario)) OR (isp.todas_pizzarias = 1))");
 
 $arquivo = '../../../softwares/'.$obj_download->arquivo;
 if(isset($arquivo) && file_exists($arquivo)){ // faz o teste se a variavel não esta vazia e se o arquivo realmente existe
    switch(strtolower(substr(strrchr(basename($arquivo),"."),1))){ // verifica a extensão do arquivo para pegar o tipo
       case "pdf": $tipo="application/pdf"; break;
       case "exe": $tipo="application/octet-stream"; break;
       case "zip": $tipo="application/zip"; break;
       case "doc": $tipo="application/msword"; break;
       case "xls": $tipo="application/vnd.ms-excel"; break;
       case "ppt": $tipo="application/vnd.ms-powerpoint"; break;
       case "gif": $tipo="image/gif"; break;
       case "png": $tipo="image/png"; break;
       case "jpg": $tipo="image/jpg"; break;
       case "mp3": $tipo="audio/mpeg"; break;
    }
    header("Content-Type: ".$tipo); // informa o tipo do arquivo ao navegador
    header("Content-Length: ".filesize($arquivo)); // informa o tamanho do arquivo ao navegador
    header("Content-Disposition: attachment; filename=".basename($arquivo)); // informa ao navegador que é tipo anexo e faz abrir a janela de download, tambem informa o nome do arquivo
    readfile($arquivo); // lê o arquivo
    exit; // aborta pós-ações
 }
?>

