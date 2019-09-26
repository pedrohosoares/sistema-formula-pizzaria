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
<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    <tr> 
      <td align="right" class="" colspan="3" style=" border-top: 1px solid #e1e1e1; border-left: 1px solid #e1e1e1; padding-top: 15px; border-right: 1px solid #e1e1e1; "> 
      </td> 
    </tr>
    <tr>
      <td class="legenda tdbl" align="right"><label for="data_inicial">Informe a data:</label></td>
      <td>&nbsp;</td>
      <td class="tdbr">
        <?php 
        $ano = date('Y');
        $anoInicio = '2019';
        ?>
        <select name="ano">
          <?php for ($i=$anoInicio; $i <= $ano; $i++) { 
            if(isset($_POST['ano']) and $_POST['ano'] == $i){
              $selected = "selected";
            }else{
              $selected = "";
            }
            ?>
            <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
            <?php
          } ?>
        </select>
        &nbsp;
        &nbsp;
        <?php 
        $ano = array(
          '01'=>'Janeiro',
          '02'=>'Fevereiro',
          '03'=>"Mar&ccedil;o",
          '04'=>'Abril',
          '05'=>'Maio',
          '06'=>'Junho',
          '07'=>'Julho',
          '08'=>'Agosto',
          '09'=>'Setembro',
          '10'=>'Outubro',
          '11'=>'Novembro',
          '12'=>'Dezembro'
        );
        ?>
        <select name="mes">
          <?php foreach($ano as $i=>$v){ 
            if(isset($_POST['mes']) and $_POST['mes'] == $i){
              $selected = "selected";
            }else{
              $selected = "";
            }
            ?>
            <option <?php echo $selected; ?> value="<?php echo $i; ?>"><?php echo $v; ?></option>
          <?php } ?>
        </select>
        <select name="pizzaria">
          <?php 
          while($pa = mysql_fetch_assoc($pizzarias)){
            echo "<option value='".$pa['cnpj']."'>".$pa['nome']."</option>";
          }
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <td align="right" class="tdbl tdbb tdbr" colspan="3">
        <input class="botaoAzul buscarForm" type="submit" value="Buscar">
      </td>
    </tr>
  </table>
  <input type="hidden" name="acao" value="buscar">
</form>
<script type="text/javascript">
  const submit = document.querySelector('.buscarForm');
  const form = document.querySelector('form[name="frmFiltro"]');
  const tbody = document.querySelector('#tbodynotas');
  function ajaxBaixaArquivo(){
    submit.onclick = function(r){
      r.preventDefault();
      submit.setAttribute('disabled','true');
      submit.value = "Buscando...";
      let ano = document.querySelector('select[name="ano"]').value;
      let mes = document.querySelector('select[name="mes"]').value;
      let empresa = document.querySelector('select[name="pizzaria"]').value;
      let url = 'https://formulasys.encontresuafranquia.com.br/dir.php?ano='+ano+'&mes='+mes+'&cnpj='+empresa+"&chave=7FibdgZ2zNAVkvsYEdsV275s40rA123.";
      var enviaPedido = new XMLHttpRequest();
      enviaPedido.open('GET',url,true);
      enviaPedido.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
      enviaPedido.onreadystatechange = function() {
        if (this.readyState == XMLHttpRequest.DONE || this.readyState == 4) {
          let json = JSON.parse(this.responseText);
          var html = "";
          json.forEach(function(v,i){
            let nomeQuebrado = v.split('/');
            let codPedidos = nomeQuebrado[5].split('_');
            let ref = codPedidos[1].split('.');
            html = "<tr>";
            html += "<td align='center'>"+codPedidos[0]+"</td>";
            html += "<td align='center'>"+nomeQuebrado[4]+"/"+nomeQuebrado[3]+"/"+nomeQuebrado[2]+"</td>";
            html += "<td align='center'><a href='http://formulasys.encontresuafranquia.com.br/"+v+"'>XML</a></td>";
            html += "<td align='center'>"+ref+"</td>";
            html += "</tr>";
            document.querySelector('#tbodynotas').innerHTML += html;
          });
        }
        submit.removeAttribute('disabled');
        submit.value = "Buscar";
      }
      enviaPedido.send();
    }
  }
  const arquivoszipados = document.querySelector('table.arquivoszipados tbody');
  ajaxBaixaArquivo();
</script>
<br>
<style type="text/css">
  a.dow{
    padding: 5px; border: 1px solid #CCC; margin-top: 2px; display: block;
  }
</style>
<table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
  <tr>
    <td width="50" align="left"></td>
  </tr>
</table>
<table class="listaEdicao" cellpadding="0" cellspacing="0">
  <thead>
    <tr>
      <th>COD PEDIDOS</th>
      <th>Data Hora final do Pedido</th>
      <th>XML</th>
      <th>REF Nota</th>
    </tr>
  </thead>
  <tbody id='tbodynotas'>

  </tbody>
</table>

<? rodape(); ?>
