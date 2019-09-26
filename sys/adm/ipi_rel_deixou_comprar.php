<?php

/**
 * ipi_rel_historico_pedidos.php: Histórico de Pedidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/pedido.php';

cabecalho('Relatório Deixou de Comprar');

$acao = validaVarPost('acao');

$tabela = 'ipi_email_automatico';
$chave_primaria = 'cod_email_automatico';
$quant_pagina = 50;
if($acao=="" && validaVarGet("p")!="")
$acao= "detalhes";
switch($acao) {
  case 'reimprimir':
    $codigo = validaVarPost($chave_primaria);
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $SqlUpdate = "UPDATE $tabela SET reimpressao = 1 WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlUpdate))
      mensagemOk('O(s) pedido(s) foram definidos como REIMPRESSÃO com sucesso!');
    else
      mensagemErro('Erro ao REIMPRIMIR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
  case 'impresso':
    $codigo = validaVarPost($chave_primaria);
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $SqlUpdate = "UPDATE $tabela SET situacao = 'IMPRESSO' WHERE $chave_primaria IN ($indicesSql) AND situacao = 'NOVO'";
    
    if (mysql_query($SqlUpdate))
      mensagemOk('O(s) pedido(s) foram definidos como IMPRESSOS com sucesso!');
    else
      mensagemErro('Erro ao redefinir IMPRESSO o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
  case 'agendamento':
    $codigo = validaVarPost($chave_primaria);
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $SqlUpdate = "UPDATE $tabela SET agendado = 0, horario_agendamento = '00:00:00' WHERE $chave_primaria IN ($indicesSql) AND situacao = 'NOVO'";
    
    if (mysql_query($SqlUpdate))
      mensagemOk('O agendamento do(s) pedido(s) foram apagados com sucesso!');
    else
      mensagemErro('Erro ao apagar agendamento do pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
  case 'cancelar':
    $codigo = validaVarPost($chave_primaria);
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $sql_verificar = "SELECT * FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao='BAIXADO'";
    $res_verificar = mysql_query($sql_verificar);
    $num_verificar = mysql_num_rows($res_verificar);
    if ($num_verificar>0)
    {
      echo "<div style='color: #FF0000; font-weight: bold; font-size: 14px; text-align: center;'>";
      echo "Os pedidos não podem ser cancelados, pois já foram BAIXADOS: ";
      while ($obj_verificar = mysql_fetch_object($res_verificar))
      {
        echo $obj_verificar->$chave_primaria.", ";
      }
      echo "</div><br /><br />";
    }

    //FUSO HORARIO NECESSITA DE CONEXAO COM O BANCO E A VARIAVEL COD_PIZZARIAS    
    $sql_pizzarias = "SELECT cod_pizzarias FROM $tabela WHERE $chave_primaria IN ($indicesSql) LIMIT 1";
    $res_pizzarias = mysql_query($sql_pizzarias);
    $obj_pizzarias = mysql_fetch_object($res_pizzarias);
    $cod_pizzarias = $obj_pizzarias->cod_pizzarias;
    require_once("../../pub_req_fuso_horario1.php");          

    $SqlUpdate = "UPDATE $tabela SET situacao = 'CANCELADO', data_hora_baixa = NOW(), data_hora_cancelamento = NOW(), cod_usuarios_cancelamento='".$_SESSION['usuario']['codigo']."' WHERE $chave_primaria IN ($indicesSql) AND situacao <> 'BAIXADO'";
    $SqlEstornoFidelidade = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, data_validade, pontos) (SELECT cod_clientes, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), pontos_fidelidade_total FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao <> 'BAIXADO')";
    
    if (mysql_query($SqlUpdate) && mysql_query($SqlEstornoFidelidade))
      mensagemOk('O pedido foi CANCELADO com sucesso!');
    else
      mensagemErro('Erro ao CANCELAR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
  case 'detalhes':
    $codigo = (validaVarPost($chave_primaria) ? validaVarPost($chave_primaria) : (validaVarGet("p") ? validaVarGet("p") : 0 )) ;
    
    $pedido = new Pedido();
    echo utf8_decode($pedido->retornar_resumo_pedido_sys($codigo,"h1"));

    $con = conectabd();

    $objBuscaCartao = executaBuscaSimples("SELECT cod_pedidos,forma_pg FROM ipi_pedidos WHERE cod_pedidos = $codigo", $con);
    if ( ($objBuscaCartao->forma_pg=="VISANET") || ($objBuscaCartao->forma_pg=="MASTERCARDNET") || ($objBuscaCartao->forma_pg=="VISANET-CIELO") || ($objBuscaCartao->forma_pg=="MASTERCARDNET-CIELO") || ($objBuscaCartao->forma_pg=="AMEXNET-CIELO") || ($objBuscaCartao->forma_pg=="DINERSNET-CIELO") || ($objBuscaCartao->forma_pg=="ELONET-CIELO") || ($objBuscaCartao->forma_pg=="JCBNET-CIELO") || ($objBuscaCartao->forma_pg=="AURANET-CIELO") )
    {
      echo '<br/>';
      echo '<br/>';
      echo '<p><b>Detalhes Operação Cartão Crédito</b></p>';
      echo '<hr noshade="noshade" color="#D44E08"/>';
      $sql_detalhes_cc = "SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = ".$objBuscaCartao->cod_pedidos;
      $res_detalhes_cc = mysql_query($sql_detalhes_cc);
      while ( $obj_detalhes_cc = mysql_fetch_object($res_detalhes_cc) )
      {
        if ($obj_detalhes_cc->chave=="digitos_cc")
          echo "<br /><strong>".$obj_detalhes_cc->chave."</strong>: **** **** **** ".substr($obj_detalhes_cc->conteudo, -4);
        else
          echo "<br /><strong>".$obj_detalhes_cc->chave."</strong>: ".$obj_detalhes_cc->conteudo;
      }
    }

    echo '<br><br><h3><a href="ipi_rel_historico_pedidos.php">&laquo; Voltar</a></h3><br><br>';
    desconectabd($con);
  break;
}

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

$pedido = (validaVarPost('pedido', '/[0-9]+/')) ? (int) validaVarPost('pedido', '/[0-9]+/') : (validaVarGet('p', '/[0-9]+/') ? (int) validaVarGet('p', '/[0-9]+/') : '');

$cliente = (validaVarPost('cliente')) ? validaVarPost('cliente') : '';
$data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('d/m/Y'));
$data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('d/m/Y'));
$cod_pizzarias = validaVarPost('cod_pizzarias');
$situacao = validaVarPost('situacao');
$origem = validaVarPost('origem');
$entrega = validaVarPost('entrega');
?>

<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  
<!--   <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="pedido">Código do Pedido:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbt tdbr"><input class="requerido" type="text" name="pedido" id="pedido" size="60" value="<? echo $pedido ?>" onkeypress="return ApenasNumero(event)"></td>
  </tr> -->
  
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="cliente">Cliente:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbr tdbt"><input class="requerido" type="text" name="cliente" id="cliente" size="60" value="<? echo $cliente ?>"></td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="data_inicial">Data Inicial:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo $data_inicial ?>" onkeypress="return MascaraData(this, event)">
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
        <option value="">Todas</option>
        <?
        $con = conectabd();
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
        while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
          echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
          
          if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td>
  </tr>

<!--   <tr>
    <td class="legenda tdbl" align="right"><label for="situacao">Situação:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr">
      <select name="situacao" id=situacao>
        <option value="TODOS" <? if($situacao == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="NOVO" <? if($situacao == 'NOVO') echo 'selected' ?>>Novo</option>
        <option value="IMPRESSO" <? if($situacao == 'IMPRESSO') echo 'selected' ?>>Impresso</option>
        <option value="BAIXADO" <? if($situacao == 'BAIXADO') echo 'selected' ?>>Baixado</option>
      </select>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="origem">Origem:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr ">
      <select name="origem" id="origem">
        <option value="TODOS" <? if($origem == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="NET" <? if($origem == 'NET') echo 'selected' ?>>Net</option>
        <option value="TEL" <? if($origem == 'TEL') echo 'selected' ?>>Tel</option>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl sep" align="right"><label for="entrega">Entrega:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="entrega" id="entrega">
        <option value="TODOS" <? if($entrega == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="Entrega" <? if($entrega == 'Entrega') echo 'selected' ?>>Entrega</option>
        <option value="Balcão" <? if($entrega == 'Balcão') echo 'selected' ?>>Balcão</option>
      </select>
    </td>
  </tr> -->
  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
</form>

<br>

<?
if($acao!="")
{
  $con = conectabd();

  $SqlBuscaRegistros = "SELECT ea.*, c.*, pi.nome AS pi_nome,(SELECT data_hora_pedido FROM ipi_pedidos p WHERE p.cod_clientes = c.cod_clientes ORDER BY cod_pedidos DESC LIMIT 1) data_hora_ultimo FROM $tabela ea INNER JOIN ipi_clientes c ON (ea.cod_clientes = c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (ea.cod_pizzarias = pi.cod_pizzarias)  WHERE ea.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ";//  //WHERE ) 

/*  if($pedido > 0)
    $SqlBuscaRegistros .= " AND p.cod_pedidos = '$pedido'";*/
    
  if($cliente != '')
    $SqlBuscaRegistros .= " AND c.nome LIKE '%$cliente%'";
    
  if($cod_pizzarias > 0)
    $SqlBuscaRegistros .= " AND ea.cod_pizzarias = '$cod_pizzarias'";

/*  if($situacao != 'TODOS')
    $SqlBuscaRegistros .= " AND p.situacao = '$situacao'";
    
  if($origem != 'TODOS')
    $SqlBuscaRegistros .= " AND p.origem_pedido = '$origem'";

  if($entrega!= 'TODOS')
    $SqlBuscaRegistros .= " AND p.tipo_entrega = '$entrega'";*/

  if(($data_inicial) && ($data_final)) {
    $data_inicial_sql = data2bd($data_inicial).' 00:00:00'; 
    $data_final_sql = data2bd($data_final).' 23:59:59';
    
    $SqlBuscaRegistros .= " AND ea.data_hora_envio >= '$data_inicial_sql' AND ea.data_hora_envio <= '$data_final_sql'";
  }
    
  $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
  $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

  $SqlBuscaRegistros .= ' ORDER BY ea.data_hora_envio LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
  $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
  $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);

  //echo $SqlBuscaRegistros;

  echo "<center><b>".$numBuscaRegistros." registro(s) encontrado(s)</center></b><br>";

  if ((($quant_pagina * $pagina) == $numBuscaRegistros) && ($pagina != 0) && ($acao == 'excluir')) $pagina--;

  echo '<center>';

  $numpag = ceil(((int) $numBuscaRegistros) / ((int) $quant_pagina));

  for ($b = 0; $b < $numpag; $b++) {
    echo '<form name="frmPaginacao'.$b.'" method="post">';
    echo '<input type="hidden" name="pagina" value="'.$b.'">';
    echo '<input type="hidden" name="acao" value="buscar">';
    
    echo '<input type="hidden" name="cod_pedidos" value="'.$cod_pedidos.'">';
    echo '<input type="hidden" name="cliente" value="'.$cliente.'">';
    echo '<input type="hidden" name="data_inicial" value="'.$data_inicial.'">';
    echo '<input type="hidden" name="data_final" value="'.$data_final.'">';
    echo '<input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'">';
    echo '<input type="hidden" name="situacao" value="'.$situacao.'">';
    echo '<input type="hidden" name="origem" value="'.$origem.'">';
    echo '<input type="hidden" name="entrega" value="'.$entrega.'">';
    
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
}

?>

<br>

<form name="frmBaixa" method="post">
<!--   <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    <tr>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Reimprimir" onclick="reimprimir()"></td>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Impresso" onclick="impresso()"></td>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Imprimir Agora" onclick="agendamento()"></td>
      <td align="left"><input class="botaoAzul" style="font-weight: bold; color: red;" type="button" value="Cancelar" onclick="cancelar()"></td>
    </tr>
  </table> -->

  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <!-- <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td> -->
        <!-- <td align="center" width="70">Pedido</td> -->
        <td align="center">Cliente</td>
        <td align="center" width="130">Pizzaria</td>
        <!-- <td align="center" width="120">Cupom</td> -->
       <!--  <td align="center">Forma de pagamento</td> -->
       <!--  <td align="center" width="70">Situação</td> -->
        
        <td align="center" width="70">Horário do Envio</td>
        <td align="center" width="70">Horário do Último Pedido</td>
      </tr>
    </thead>
    <tbody>
  
    <?
    if($acao!="")
    {
      while($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
        echo '<tr>';
        
        /*echo '<td align="center"><input type="checkbox" class="marcar situacao" name="'.$chave_primaria.'[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';*/
       /* echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.sprintf('%08d', $objBuscaRegistros->$chave_primaria).'</a></td>';*/
        echo '<td align="center"><a style="font-weight:bold" href="ipi_clientes_franquia.php?cc='.$objBuscaRegistros->cod_clientes.'">'.bd2texto($objBuscaRegistros->nome).'</a></td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->pi_nome).'</td>';
        /*if($objBuscaRegistros->tipo_entrega=="Entrega")
        {
          echo '<td align="center">'.bd2texto($objBuscaRegistros->bairro).', '.bd2texto($objBuscaRegistros->endereco).' '.bd2texto($objBuscaRegistros->numero).' comp.:'.bd2texto($objBuscaRegistros->complemento).'</td>';
        }
        else
        {*/
          // echo '<td align="center">'.$objBuscaRegistros->cupom.'</td>';
        /*}*/
        /*echo '<td align="center">'.$objBuscaRegistros->forma_pg.'</td>';*/
        /*echo '<td align="center">'.$objBuscaRegistros->pedidos_situacao.'</td>';*/
        
        
        
        echo '<td align="center">'.bd2datahora($objBuscaRegistros->data_hora_envio).'</td>';
        echo '<td align="center">'.bd2datahora($objBuscaRegistros->data_hora_ultimo).'</td>';
        
        echo '</tr>';
      }
      desconectabd($con);
    }
    

    
    ?>
    
    </tbody>
  </table>

  <input type="hidden" name="acao" value="">
</form>
<? endif; ?>

<? rodape(); ?>
