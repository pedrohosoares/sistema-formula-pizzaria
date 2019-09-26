<?php

/**
 * Consulta a última entrada de estoque de cada produto.
 *
 * @version 1.0
 * @package iti
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       08/11/2012   PEDRO H       Criado.
 *
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Consulta entrada produtos');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_titulos';
$tabela = 'ipi_titulos';
$codigo_usuario = $_SESSION['usuario']['codigo'];
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
$ingrediente_filtro = (validaVarPost("ingrediente_filtro") !="" ? validaVarPost("ingrediente_filtro") : validaVarGet("ci"));

$codigo = (validar_var_post('cod_pizzarias') !="" ? validar_var_post('cod_pizzarias') : validar_var_get('cp'));

$data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
$data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
if($acao == "" && $ingrediente_filtro !="")
{
  $acao = "buscar";
}
?>

<script src="../../sys/lib/js/mascara.js" type="text/javascript"></script>

<script>
  window.addEvent('domready', function() 
  {
      new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
      new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
  });

  function editar(cod) {
  var form = new Element('form', {
    'action': 'ipi_consulta_notas_fiscais.php',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': 'cod_titulos',
    'value': cod
  });

  var input2 = new Element('input', {
    'type': 'hidden',
    'name': 'acao',
    'value': 'detalhes'
  });
  
  input.inject(form);
  input2.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}
</script>

<!-- Tab Editar -->
<div class="painelTab">
        
  <form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
      <tr>
        <td class="legenda tdbl tdbt" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
        <td class="tdbt">&nbsp;</td>
        <td class="tdbr tdbt">
          <select name="cod_pizzarias" id="cod_pizzarias">
            <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
            <?
              $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
              $con = conectabd();
              $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
              $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
              
              while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
              {
                echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
                if($objBuscaPizzarias->cod_pizzarias == $codigo)
                {
                  echo 'selected';
                }
                echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
              }
            ?>
          </select>
        </td>
      </tr>

<!--       <tr>
        <td class="legenda tdbl" align="right">
          <label for="data_inicial">Data Inicial:</label>
        </td>
        <td class="">&nbsp;</td>
        <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="8" value="<? echo bd2data($data_inicial); ?>" onkeypress="return MascaraData(this, event)">&nbsp;<a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
        </td>
      </tr>

      <tr>
        <td class="legenda tdbl " align="right">
          <label for="data_final">Data Final:</label>
        </td>
        <td >&nbsp;</td>
        <td class="tdbr ">
          <input class="requerido" type="text" name="data_final" id="data_final" size="8" value="<? echo bd2data($data_final); ?>" onkeypress="return MascaraData(this, event)">&nbsp;<a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
        </td>
      </tr>
 -->
      <tr>
        <td class="legenda tdbl" align="right"><label for="ingrediente_filtro">Ingrediente</label></td>
        <td >&nbsp;</td>
        <td class="tdbr">
          <select name="ingrediente_filtro" id="ingrediente_filtro">
            <option value=""></option>
              <?
              $con = conectabd();
              $sql_buscar_categorias = "SELECT * FROM ipi_ingredientes where cod_ingredientes_baixa = cod_ingredientes ORDER BY ingrediente";
              $res_buscar_categorias = mysql_query($sql_buscar_categorias);
              while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
              {
                  echo '<option value="' . $obj_buscar_categorias->cod_ingredientes . '"';
                  if($obj_buscar_categorias->cod_ingredientes == $ingrediente_filtro)
                  {
                      echo " SELECTED ";
                  }
                  echo '>' . bd2texto($obj_buscar_categorias->ingrediente) . '</option>';
              }
              ?>
          </select>
        </td>
      </tr> 
      <tr>
          <td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td>
      </tr>
  </table>

  <input type="hidden" name="acao" value="buscar">
  </form>

  <br>
  <div style='margin: 0 auto; width: 1000px;'>
  <?
    $conexao = conectar_bd();
    if($acao == 'buscar')
    {
     
      echo ' <table class="listaEdicao" width="1000" align="center" cellpadding="0" cellspacing="0">';
      echo '  <thead>';
      echo '   <tr>';
      echo '    <td align="center" width="400">Ingrediente</td>';
      echo '    <td align="center" width="100">Qtd. de Embalagens</td>';
      echo '    <td align="center" width="100">Qtd. por Embalagem</td>';
      echo '    <td align="center" width="100">Preço Un.</td>';
      echo '    <td align="center" width="100">Preço Total</td>';
      echo '    <td align="center" width="100">Data de compra</td>';
      echo '    <td align="center" width="100">Referente a NF</td>';
      echo '   </tr>';
      echo '  </thead>';

      echo '  <tbody>';

      $sql_buscar_ingredientes = "SELECT ii.cod_ingredientes, ii.ingrediente, iup.divisor_comum, iup.abreviatura FROM ipi_ingredientes ii LEFT JOIN ipi_unidade_padrao iup ON (iup.cod_unidade_padrao = ii.cod_unidade_padrao) ";

      if($ingrediente_filtro)
      {
        $sql_buscar_ingredientes .= " where ii.cod_ingredientes ='$ingrediente_filtro'";
      }
      $sql_buscar_ingredientes .= "ORDER BY ingrediente ASC LIMIT 3";

      $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
      while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
      {
        $sql_buscar_registros = "SELECT ieei.preco_unitario_entrada, ieei.quantidade_embalagem_entrada, ieei.quantidade_entrada, iee.data_hota_entrada_estoque, it.numero_nota_fiscal,it.cod_titulos FROM ipi_estoque_entrada iee LEFT JOIN ipi_estoque_entrada_itens ieei ON (iee.cod_estoque_entrada = ieei.cod_estoque_entrada) LEFT JOIN ipi_titulos it ON (it.cod_estoque_entrada = iee.cod_estoque_entrada) WHERE ieei.tipo_entrada_estoque = 'INGREDIENTE' AND ieei.cod_ingredientes = '".$obj_buscar_ingredientes->cod_ingredientes."' AND iee.cod_pizzarias in ($cod_pizzarias_usuario) AND iee.cod_pizzarias = '".$codigo."' ORDER BY iee.data_hota_entrada_estoque DESC";
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $obj_buscar_registros = mysql_fetch_object($res_buscar_registros);                      
        //echo $sql_buscar_registros.'<br/>';

        
        $abreviatura = ($obj_buscar_ingredientes->abreviatura ? ' (em '.$obj_buscar_ingredientes->abreviatura.')' : '');
        $divisor_comum = ($obj_buscar_ingredientes->divisor_comum ? $obj_buscar_ingredientes->divisor_comum : 1);

        echo '<tr>';
        echo '  <td>'.$obj_buscar_ingredientes->ingrediente.$abreviatura.'</td>';
        echo '  <td align="right">'.($obj_buscar_registros->quantidade_entrada ? ($obj_buscar_registros->quantidade_entrada) : '0').'</td>';
        echo '  <td align="right">'.($obj_buscar_registros->quantidade_embalagem_entrada ? bd2moeda($obj_buscar_registros->quantidade_embalagem_entrada/$divisor_comum) : '0,00').'</td>';
        echo '  <td align="right">R$ '.($obj_buscar_registros->preco_unitario_entrada ? bd2moeda($obj_buscar_registros->preco_unitario_entrada) : '0,00').'</td>';
        $total_produto = $obj_buscar_registros->preco_unitario_entrada * $obj_buscar_registros->quantidade_entrada;
        echo '  <td align="right">R$ '.($total_produto ? bd2moeda($total_produto) : '0,00').'</td>';
        $date = strtotime($obj_buscar_registros->data_hota_entrada_estoque); 
        echo '  <td align="center">'.($date ? date('d/m/Y', $date) : '-').'</td>';
        echo '  <td align="center"><a href="ipi_consulta_notas_fiscais.php?ct='.$obj_buscar_registros->cod_titulos.'" target="_blank">'.($obj_buscar_registros->numero_nota_fiscal !="" ? $obj_buscar_registros->numero_nota_fiscal : "(Nº não digitado)").'</a></td>';
        echo '</tr>';
      }
      echo '</tbody>';
      echo '</table>';

      echo '<br/></br/>';
      if(!$ingrediente_filtro)
      {
        echo ' <table class="listaEdicao" style="width:1000px;" align="center" cellpadding="0" cellspacing="0">';
        echo '  <thead>';
        echo '   <tr>';
        echo '    <td align="center" width="400">Bebida</td>';
        echo '    <td align="center" width="150">Qtd. de Embalagens</td>';
        echo '    <td align="center" width="150">Qtd. por Embalagem</td>';
        echo '    <td align="center" width="150">Preço Un.</td>';
        echo '    <td align="center" width="150">Preço Total</td>';
        echo '    <td align="center" width="150">Data de compra</td>';
        echo '   </tr>';
        echo '  </thead>';

        echo '  <tbody>';

        $sql_buscar_bebidas = "SELECT ibic.cod_bebidas_ipi_conteudos, ib.bebida, ic.conteudo FROM ipi_bebidas ib INNER JOIN ipi_bebidas_ipi_conteudos ibic ON (ibic.cod_bebidas = ib.cod_bebidas) INNER JOIN ipi_conteudos ic ON (ibic.cod_conteudos = ic.cod_conteudos)";
        $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
        while($obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas))
        {
          $sql_buscar_registros = "SELECT ieei.preco_unitario_entrada, ieei.quantidade_embalagem_entrada, ieei.quantidade_entrada, iee.data_hota_entrada_estoque FROM ipi_estoque_entrada iee INNER JOIN ipi_estoque_entrada_itens ieei ON (iee.cod_estoque_entrada = ieei.cod_estoque_entrada) WHERE ieei.tipo_entrada_estoque = 'BEBIDA' AND ieei.cod_bebidas_ipi_conteudos = '".$obj_buscar_bebidas->cod_bebidas_ipi_conteudos."' AND iee.cod_pizzarias in ($cod_pizzarias_usuario) AND iee.cod_pizzarias = '".$codigo."' ORDER BY iee.data_hota_entrada_estoque DESC LIMIT 1";
          $res_buscar_registros = mysql_query($sql_buscar_registros);
          $obj_buscar_registros = mysql_fetch_object($res_buscar_registros);                      
          //echo $sql_buscar_registros.'<br/>';

          echo '<tr>';
          echo '  <td>'.$obj_buscar_bebidas->bebida.' - '.$obj_buscar_bebidas->conteudo.'</td>';
          echo '  <td align="right">'.($obj_buscar_registros->quantidade_entrada ? $obj_buscar_registros->quantidade_entrada : '0').'</td>';
          echo '  <td align="right">'.($obj_buscar_registros->quantidade_embalagem_entrada ? bd2moeda($obj_buscar_registros->quantidade_embalagem_entrada) : '0,00').'</td>';
          echo '  <td align="right">R$ '.($obj_buscar_registros->preco_unitario_entrada ? bd2moeda($obj_buscar_registros->preco_unitario_entrada) : '0,00').'</td>';
          $total_produto = $obj_buscar_registros->preco_unitario_entrada * $obj_buscar_registros->quantidade_entrada;
          echo '  <td align="right">R$ '.($total_produto ? bd2moeda($total_produto) : '0,00').'</td>';
          $date = strtotime($obj_buscar_registros->data_hota_entrada_estoque); 
          echo '  <td align="center">'.($date ? date('d/m/Y', $date) : '-').'</td>';
          echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
      }
    }
    desconectabd($conexao);
  ?>
  </div>

</div>

<?
rodape();
?>
