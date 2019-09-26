<?php

/**
 * ipi_rel_sabores_vendidos.php: Sabores Mais Vendidos
 * 
 * �ndice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Ingredientes Mais Vendidos');

$acao = validaVarPost('acao');

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script language="javascript" src="../lib/js/calendario.js"></script>

<script>

window.addEvent('domready', function() { 
  // DatePick
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 

</script>

<?
$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 
$pedido = (validaVarPost('pedido', '/[0-9]+/')) ? (int) validaVarPost('pedido', '/[0-9]+/') : '';
$cliente = (validaVarPost('cliente')) ? validaVarPost('cliente') : '';
$data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('01/m/Y'));
$data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('t/m/Y'));
$cod_pizzarias = validaVarPost('cod_pizzarias');
$cod_tamanhos = validaVarPost('cod_tamanhos');
$situacao = validaVarPost('situacao');
$origem = validaVarPost('origem');
$promocional = validaVarPost('promocional');
?>

<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <!-- 
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="pedido">C�digo do Pedido:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbt tdbr"><input class="requerido" type="text" name="pedido" id="pedido" size="60" value="<? echo $pedido ?>" onkeypress="return ApenasNumero(event)"></td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="cliente">Cliente:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr"><input class="requerido" type="text" name="cliente" id="cliente" size="60" value="<? echo $cliente ?>"></td>
  </tr>
   -->
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="data_inicial">Data Inicial:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbr tdbt"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo $data_inicial ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
    <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo $data_final ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
        <?
        $con = conectabd();
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias ORDER BY nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
        while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
          echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
          
          if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
        }
        
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="cod_pizzarias">Tamanho:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_tamanhos" id="cod_tamanhos">
        <option value="">Todos os Tamanhos</option>
        <?
        $sql_busca_tamanhos = "SELECT * FROM ipi_tamanhos";
        $res_busca_tamanhos = mysql_query($sql_busca_tamanhos);
        while($obj_busca_tamanhos = mysql_fetch_object($res_busca_tamanhos)) 
        {
            echo '<option value="'.$obj_busca_tamanhos->cod_tamanhos.'" ';
            if($obj_busca_tamanhos->cod_tamanhos == $cod_tamanhos)
                echo 'selected';
            echo '>'.bd2texto($obj_busca_tamanhos->tamanho).'</option>';
        }
        desconectabd($con);
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="situacao">Situa��o:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr">
    <?
    if (!$situacao)
        $situacao = "BAIXADO";
    ?>
      <select name="situacao" id=situacao>
        <option value="TODOS" <? if($situacao == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="CANCELADO" <? if($situacao == 'CANCELADO') echo 'selected' ?>>Cancelado</option>
        <option value="BAIXADO" <? if($situacao == 'BAIXADO') echo 'selected' ?>>Baixado</option>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="origem">Origem:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="origem" id="origem">
        <option value="TODOS" <? if($origem == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="NET" <? if($origem == 'NET') echo 'selected' ?>>Net</option>
        <option value="TEL" <? if($origem == 'TEL') echo 'selected' ?>>Tel</option>
      </select>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl sep" align="right"><label for="promocional">Promocional:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="promocional" id="promocional">
        <option value="TODOS" <? if($promocional == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="Sim" <? if($promocional == 'Sim') echo 'selected' ?>>Sim</option>
        <option value="N�o" <? if($promocional == 'N�o') echo 'selected' ?>>N�o</option>
      </select>
    </td>
  </tr>

  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
</form>

<br>

<?
$con = conectabd();

$SqlBuscaRegistros = "SELECT i.ingrediente, (SELECT COUNT(*) FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos pe ON (pp.cod_pedidos = pe.cod_pedidos) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) WHERE pi.cod_ingredientes=i.cod_ingredientes AND pi.ingrediente_padrao = 1 ";

if($origem != 'TODOS')
    $SqlBuscaRegistros .= " AND pe.origem_pedido = '$origem'";

if($origem == 'NET')
    $filtroSql .= " AND pe.origem_pedido IN ('NET','IFOOD')";
    
if($cod_pizzarias)
    $SqlBuscaRegistros .= " AND pe.cod_pizzarias = '$cod_pizzarias'";
    
if($cod_tamanhos)
    $SqlBuscaRegistros .= " AND pp.cod_tamanhos = '$cod_tamanhos'";
    
if(($data_inicial) && ($data_final)) 
{
    $data_inicial_sql = data2bd($data_inicial).' 00:00:00'; 
    $data_final_sql = data2bd($data_final).' 23:59:59';
    $SqlBuscaRegistros .= " AND pe.data_hora_pedido >= '$data_inicial_sql' AND pe.data_hora_pedido <= '$data_final_sql'";
}

if($promocional == 'Sim')
  $SqlBuscaRegistros .= " AND pp.fidelidade = 1 AND pp.promocional = 1";
else if($promocional == 'N�o')
  $SqlBuscaRegistros .= " AND pp.fidelidade = 0 AND pp.promocional = 0";

if($situacao != 'TODOS')
  $SqlBuscaRegistros .= " AND pe.situacao = '$situacao'";


$SqlBuscaRegistros .= " ) quantidade FROM ipi_ingredientes i ORDER BY ingrediente";

$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

?>

<br>

<table class="listaEdicao" cellpadding="0" cellspacing="0">
<thead>
  <tr>
    <td align="center">Ingrediente</td>
    <td align="center">Qtde</td>
  </tr>
</thead>
<tbody>

<?
$total_geral = 0;
while($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) 
{
    echo '<tr>';
    echo '<td align="center">'.bd2texto($objBuscaRegistros->ingrediente).'</td>';
    echo '<td align="center">'.$objBuscaRegistros->quantidade.'</td>';
    echo '</tr>';
    $total_geral += $objBuscaRegistros->quantidade;
}
desconectabd($con);
?>

  <tr>
    <td align="right"><b>Total</b></td>
    <td align="center"><b><? echo $total_geral; ?></b></td>
  </tr>

</tbody>
</table>

<? rodape(); ?>