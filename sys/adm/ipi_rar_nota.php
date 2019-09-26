<?php

/**
 * ipi_rel_historico_pedidos.php: Histórico de Pedidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */
header('Content-Type: text/html; charset=ISO-8859-1');
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/pedido.php';


cabecalho('Notas Fiscais');
?>
<?php  
$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 50;
$con = conectabd();
$id_pizzarias = $_SESSION['usuario']['cod_pizzarias'];
$id_pizzarias = implode(',', $id_pizzarias);
$sql = "SELECT cod_pizzarias,nome,merchant_id,ifood_ligado,cnpj FROM ipi_pizzarias WHERE cod_pizzarias IN ($id_pizzarias)";
$pizzarias = mysql_query($sql);


$usuario = $_SESSION['usuario']['cod_pizzarias'];
$idsUsuario = implode(',', $usuario);
$con = conectabd();
$sql = "SELECT cnpj FROM ipi_pizzarias WHERE cod_pizzarias IN ($idsUsuario)";
$result = mysql_query($sql);
$cnpj = '';
while($v = mysql_fetch_assoc($result)){
  $cnpjAjax[] = $v['cnpj'];
  $cnpj.=$v['cnpj'].',';
}
$cnpjAjax = implode(',', $cnpjAjax);
$cnpj = substr($cnpj, 0,strlen($cnpj)-1);
$cnpj = str_replace(array('.','/','-'),'', $cnpj);
?>
<style type="text/css">
  .botaoAzul{
    background-color: #CEE1EF;
    border: 1px solid #80B5D0;
    color: #224466;
    padding: 2px;
    font-size: 18px;
    -moz-border-radius-bottomleft: 3px;
    -moz-border-radius-bottomright: 3px;
    -moz-border-radius-topleft: 3px;
    -moz-border-radius-topright: 3px;
  }
</style>
<script type="text/javascript">
  const arquivoszipados = document.querySelector('table.arquivoszipados tbody');
  function ajaxRecuperaZips(){
    let vetor = '';
    let url = 'https://formulasys.encontresuafranquia.com.br/focusnfe/folderzip.php?cnpj='+"<?php echo $cnpjAjax; ?>&chave=7FibdgZ2zNAVkvsYEdsV275s40rA123.";
    var enviaPedido = new XMLHttpRequest();
    enviaPedido.open('GET',url,true);
    enviaPedido.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    enviaPedido.onreadystatechange = function() {
      if (this.readyState == XMLHttpRequest.DONE || this.readyState == 4) {
        let json = JSON.parse(this.responseText);
        json.forEach(function(v,i){
          v.forEach(function(vv,ii){
            console.log(vv);
            let arquivo = vv.split('/').pop();
            let cp = vv.split('/');
            let data = arquivo.split('.zip').shift();
            let dataSeparada = data.split('-');
            vetor += "<tr><td align='center'>"+cp[2]+"</td><td align='center'>"+dataSeparada[1]+"/"+cp[3]+"</td><td align='center'><a class='dow' download href='https://formulasys.encontresuafranquia.com.br/"+vv+"'>Download do Arquivo</a></td></tr>";
          });
        });
        if(vetor.length >0){
          document.querySelector('table.arquivoszipados tbody').innerHTML = vetor;
        }else{
          document.querySelector('table.arquivoszipados tbody').innerHTML = "Nenhum arquivo até o momento";
        }
      }
    }
    enviaPedido.send();
  }
  ajaxRecuperaZips();
</script>
<br>
<style type="text/css">
  a.dow{
    padding: 5px; border: 1px solid #CCC; margin-top: 2px; display: block;
  }
</style>
<table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
  <tr>
    <td align='center'><strong>CNPJ</strong></td>
    <td align='center'><strong>Data</strong></td>
    <td align='center'><strong>Arquivo</strong></td>
  </tr>
</table>
<table class="arquivoszipados listaEdicao" cellpadding="0" cellspacing="0" style=" width: 100%; padding: 5px; ">
  <tbody>
    <tr>
      <td width="50" align="left">Verificando arquivo compactado das notas para download..</td>
    </tr>
  </tbody>
</table>

<? rodape(); ?>
