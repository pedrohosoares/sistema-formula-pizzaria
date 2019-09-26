<?php

/**
 * ipi_cupom.php: Cadastro de Cupom
 * 
 * Índice: cod_cupons
 * Tabela: ipi_cupons
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Leitura dos Emails');

$acao = validaVarPost('acao');


$tabela = 'ipi_cupons';
$chave_primaria = 'cod_cupons';
$quant_pagina = 70;
?>

<?
  $cod_disparo_mensagens = validaVarPost('cod_disparo_mensagens');

?>

<script type="text/javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>
<script>
function verificar(form) {
  if(form.txtNumCupomFiltro.value==""){
    alert("Digite o número do Cupom!");
    form.txtNumCupomFiltro.focus();
    return false;
  }
  
  return true;
}

function trocar(cod_cupons,cod_pizzarias,situacao) 
{
  var idTrocar = 'result_'+cod_cupons+'_'+cod_pizzarias;
  var controle = 'controle_'+cod_cupons+'_'+cod_pizzarias;
  if (document.getElementById(idTrocar).style.display=="none")
  {
    if(document.getElementById(controle).value==0)
    {
      carregaDetalhes(cod_cupons,cod_pizzarias,situacao);
      document.getElementById(controle).value = 1
    }
    document.getElementById(idTrocar).style.display='block';
  }
  else
  {
    document.getElementById(idTrocar).style.display='none';
  }
}

function carregaDetalhes(cod_cupons,cod_pizzarias,situacao) 
{
  var url = 'acao=carregar_detalhes';
  
  url += '&c=' + cod_cupons+'&p=' + cod_pizzarias+'&s='+situacao;
  
  $('tbody_'+cod_cupons+'_'+cod_pizzarias).set('text', 'Carregando...');
  
  new Request.HTML({
  url: 'ipi_rel_cupom_unidade_ajax.php',
  update: $('tbody_'+cod_cupons+'_'+cod_pizzarias)
   }).send(url);
}


</script>

<div id="tabs">
  <!-- Tab Listar -->
  <div class="painelTab" align="center">
    <table><tr>
  
    <!-- Conteúdo -->
    <td class="conteudo">

  <form name="frmFiltro" method="post">
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
<!--     <tr>
      <td class="legenda tdbl tdbt" align="right"><label for="pedido">Código do cupom:</label></td>
      <td class="tdbt">&nbsp;</td>
      <td class="tdbt tdbr"><input class="requerido" type="text" name="txtNumCupomFiltro" id="txtNumCupomFiltro" size="60" value="<? echo $txtNumCupomFiltro ?>" ></td>
    </tr> -->
    
     <tr>
      <td class="legenda tdbl tdbt" align="right"><label for="cod_disparo_mensagens">Disparo:</label></td>
      <td class="tdbt">&nbsp;</td>
      <td class="tdbr tdbt">
        <select name="cod_disparo_mensagens" id="cod_disparo_mensagens">
          <?
          $con = conectabd();
          
          $SqlBuscaPizzarias = "SELECT dm.*,m.assunto FROM ine_disparo_mensagens dm INNER JOIN ine_mensagens m on m.cod_mensagens = dm.cod_mensagens ORDER BY data_hora_disparo DESC";
          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
          while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
            echo '<option value="'.$objBuscaPizzarias->cod_disparo_mensagens.'" ';
            
            if($objBuscaPizzarias->cod_disparo_mensagens == $cod_disparo_mensagens)
              echo 'selected';
            
            echo '>'.bd2texto($objBuscaPizzarias->assunto).' - '.bd2datahora($objBuscaPizzarias->data_hora_disparo).'</option>';
          }
          
          desconectabd($con);
          ?>
          <option value="0" <? if("0" === $cod_disparo_mensagens) echo 'selected'; ?>>Emails sem registro de disparo</option>
        </select>
      </td>
    </tr>

<!--     <tr>
      <td class="legenda tdbl" align="right"><label for="cliente">Cliente:</label></td>
      <td class="">&nbsp;</td>
      <td class="tdbr"><input class="requerido" type="text" name="cliente" id="cliente" size="60" value="<? echo $cliente ?>"></td>
    </tr> -->
    
<!--     <tr>
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
    </tr> -->

    <!-- <tr>
      <td class="legenda tdbl" align="right"><label for="cod_pizzarias">Pizzaria:</label></td>
      <td>&nbsp;</td>
      <td class="tdbr">
        <select name="cod_pizzarias" id="cod_pizzarias">
          <option value="">Todas as Pizzarias</option>
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
    </tr> -->

<!--     <tr>
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


<!--     <tr>
      <td class="legenda tdbl" align="right"><label for="situacao">Situação do pedido:</label></td>
      <td class="">&nbsp;</td>
      <td class="tdbr">
        <select name="situacao" id="situacao">
         <option value="BAIXADO" <? if($situacao == 'BAIXADO') echo 'selected' ?>>Baixado</option>
          <option value="TODOS" <? if($situacao == 'TODOS') echo 'selected' ?>>Todas</option>
          <option value="NOVO" <? if($situacao == 'NOVO') echo 'selected' ?>>Novo</option>
          <option value="IMPRESSO" <? if($situacao == 'IMPRESSO') echo 'selected' ?>>Impresso</option>
          <option value="CANCELADO" <? if($situacao == 'CANCELADO') echo 'selected' ?>>Cancelado</option>
        </select>
      </td>
    </tr> -->

    <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="buscar">
  </form>
    
    <? if ($acao=='buscar'){
      
      echo "<br/><br/><div id='grafico_leitura_email'></div>";
      echo "<script>window.addEvent('domready', function()
    {
      var leitura_email = new FusionCharts('../lib/swf/fusioncharts/msline.swf', 'leitura_email', 800, 400, 0, 0, 'ffffff', 0);
      leitura_email.setDataURL('ipi_rel_leitura_email_ajax.php?grafico=1,".$cod_disparo_mensagens."');
      leitura_email.render('grafico_leitura_email');
    });</script>";

      }
      else
      {
        echo "Selecione um disparo de email.";
      }
      ?>
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
    <!-- <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="#">Atalho 1</a></li>
        </ul>
      </div>
    </td> -->
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Listar -->
    
 </div>

<? rodape(); ?>
