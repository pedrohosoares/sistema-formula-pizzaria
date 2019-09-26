<?php

/**
 * ipi_rel_sabores_vendidos.php: Bebidas Mais Vendidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Bebidas Mais Vendidas');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 50;
?>

<? if($acao != 'detalhes'): ?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script language="javascript" src="../lib/js/calendario.js"></script>

<script>

function verificaCheckbox(form) {
  var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('situacao')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
   
  if(cInput > 0) {
    if (confirm('Deseja mudar de situação o(s) pedido(s) selecionado(s)?')) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja mudar de situação (BAIXAR / CANCELAR).');
     
    return false;
  }
}

function editar(cod) {
  var form = new Element('form', {
    'action': '<? echo $_SERVER['PHP_SELF'] ?>',
    'method': 'post'
  });
  
  var input1 = new Element('input', {
    'type': 'hidden',
    'name': '<? echo $chave_primaria ?>',
    'value': cod
  });
  
  var input2 = new Element('input', {
    'type': 'hidden',
    'name': 'acao',
    'value': 'detalhes'
  });
  
  input1.inject(form);
  input2.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function cancelar() {
  if(verificaCheckbox(document.frmBaixa)) {
    document.frmBaixa.acao.value = "cancelar";
    document.frmBaixa.submit();
  }
}

function reimprimir() {
  if(verificaCheckbox(document.frmBaixa)) {
    document.frmBaixa.acao.value = "reimprimir";
    document.frmBaixa.submit();
  }
}

function impresso() {
  if(verificaCheckbox(document.frmBaixa)) {
    document.frmBaixa.acao.value = "impresso";
    document.frmBaixa.submit();
  }
}

function agendamento() {
  if(verificaCheckbox(document.frmBaixa)) {
    document.frmBaixa.acao.value = "agendamento";
    document.frmBaixa.submit();
  }
}

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
$ordenacao = validaVarPost('ordenacao');
?>

<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <!-- 
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="pedido">Código do Pedido:</label></td>
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
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";

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
    <td class="legenda tdbl" align="right"><label for="situacao">Situação:</label></td>
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
    <td class="tdbr">
      <select name="promocional" id="promocional">
        <option value="TODOS" <? if($promocional == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="Sim" <? if($promocional == 'Sim') echo 'selected' ?>>Sim</option>
        <option value="Não" <? if($promocional == 'Não') echo 'selected' ?>>Não</option>
      </select>
    </td>
  </tr>

   <tr>
    <td class="legenda tdbl sep" align="right"><label for="ordenacao">Ordenados por:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="ordenacao" id="ordenacao">
        <option value="bebida" <? if($ordenacao == 'bebida') echo 'selected' ?>>Alfabética</option>
        <option value="quantidade" <? if($ordenacao == 'quantidade') echo 'selected' ?>>Mais Vendidos</option>
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

$SqlBuscaRegistros = "SELECT b.bebida, c.conteudo, (SELECT sum(pb.quantidade) FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos pe ON (pb.cod_pedidos = pe.cod_pedidos) WHERE pb.cod_bebidas_ipi_conteudos=bc.cod_bebidas_ipi_conteudos AND pe.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ";

if($origem != 'TODOS')
    $SqlBuscaRegistros .= " AND pe.origem_pedido = '$origem'";
    
if($origem == 'NET')
    $SqlBuscaRegistros .= " AND pe.origem_pedido IN ('NET','IFOOD')";
    
if($cod_pizzarias)
    $SqlBuscaRegistros .= " AND pe.cod_pizzarias = '$cod_pizzarias'";
    
if(($data_inicial) && ($data_final)) 
{
    $data_inicial_sql = data2bd($data_inicial).' 00:00:00'; 
    $data_final_sql = data2bd($data_final).' 23:59:59';
    $SqlBuscaRegistros .= " AND pe.data_hora_pedido >= '$data_inicial_sql' AND pe.data_hora_pedido <= '$data_final_sql'";
}

if($promocional == 'Sim')
  $SqlBuscaRegistros .= " AND pb.fidelidade = 1 AND pb.promocional = 1";
else if($promocional == 'Não')
  $SqlBuscaRegistros .= " AND pb.fidelidade = 0 AND pb.promocional = 0";

if($situacao != 'TODOS')
  $SqlBuscaRegistros .= " AND pe.situacao = '$situacao'";

if ($ordenacao=="")
{
  $ordenacao = "bebida";
}
$SqlBuscaRegistros .= " ) quantidade FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (b.cod_bebidas = bc.cod_bebidas) INNER JOIN ipi_conteudos c ON (c.cod_conteudos = bc.cod_conteudos) ORDER BY ".($ordenacao =="bebida" ?  'bebida, conteudo': $ordenacao.' DESC'  );

$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

//echo $SqlBuscaRegistros;

/*
echo "<center><b>".$numBuscaRegistros." registro(s) encontrado(s)</center></b><br>";

if ((($quant_pagina * $pagina) == $numBuscaRegistros) && ($pagina != 0) && ($acao == 'excluir')) $pagina--;

echo '<center>';

$numpag = ceil(((int) $numBuscaRegistros) / ((int) $quant_pagina));

for ($b = 0; $b < $numpag; $b++) {
  echo '<form name="frmPaginacao'.$b.'" method="post">';
  echo '<input type="hidden" name="pagina" value="'.$b.'>';
  echo '<input type="hidden" name="acao" value="buscar">';
  
  echo '<input type="hidden" name="cod_pedidos" value="'.$cod_pedidos.'">';
  echo '<input type="hidden" name="cliente" value="'.$cliente.'">';
  echo '<input type="hidden" name="data_inicial" value="'.$data_inicial.'">';
  echo '<input type="hidden" name="data_final" value="'.$data_final.'">';
  echo '<input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'">';
  echo '<input type="hidden" name="situacao" value="'.$situacao.'">';
  echo '<input type="hidden" name="origem" value="'.$origem.'">';
  
  echo "</form>";
}

if ($pagina != 0)
  echo '<a href="javascript:;" onclick="javascript:frmPaginacao'.($pagina - 1).'.submit();" style="margin-right: 5px;">&laquo;&nbsp;Anterior</a>';
else
  echo '<span style="margin-right: 5px;">&laquo;&nbsp;Anterior</span>';

for ($b = 0; $b < $numpag; $b++) {
  if ($b != 0)
    echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
  
  if ($pagina != $b)
    echo '<a href="javascript:;" onclick="javascript:frmPaginacao'.$b.'.submit();">'.($b + 1).'</a>';
  else
    echo '<span><b>'.($b + 1).'</b></span>';
}

if (($quant_pagina == $linhasBuscaRegistros) && ((($quant_pagina * $pagina) + $quant_pagina) != $numBuscaRegistros))
  echo '<a href="javascript:;" onclick="javascript:frmPaginacao'.($pagina + 1).'.submit();" style="margin-left: 5px;">Próxima&nbsp;&raquo;</a>';
else
  echo '<span style="margin-left: 5px;">Próxima&nbsp;&raquo;</span>';

echo '</center>';
*/
?>

<br>

<form name="frmBaixa" method="post">
<!-- 
  <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    <tr>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Reimprimir" onclick="reimprimir()"></td>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Impresso" onclick="impresso()"></td>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Imprimir Agora" onclick="agendamento()"></td>
      <td align="left"><input class="botaoAzul" style="font-weight: bold; color: red;" type="button" value="Cancelar" onclick="cancelar()"></td>
    </tr>
  </table>
 -->
  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center">Bebida</td>
        <td align="center">Tamanho</td>
        <td align="center">Qtde</td>
      </tr>
    </thead>
    <tbody>
  
    <?
    $total_geral=0;
    while($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
      echo '<tr>';
      
      //echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.sprintf('%08d', $objBuscaRegistros->$chave_primaria).'</a></td>';
      echo '<td align="center">'.bd2texto($objBuscaRegistros->bebida).'</td>';
      echo '<td align="center">'.bd2texto($objBuscaRegistros->conteudo).'</td>';
      echo '<td align="center">'.number_format($objBuscaRegistros->quantidade, 0, ",", ".").'</td>';
      $total_geral += $objBuscaRegistros->quantidade;
      
      
      echo '</tr>';
    }
    
    desconectabd($con);
    
    ?>
    
      <tr>
        <td align="center" colspan="2"><b>Total</b></td>
        <td align="center"><b><? echo number_format($total_geral, 0, ",", "."); ?></b></td>
      </tr>
    </tbody>
  </table>

  <input type="hidden" name="acao" value="">
</form>

<? endif; ?>

<? rodape(); ?>
