<?php

  /**
   * Relatório Ingredientes consumidos no período(em peso/)
   *
   * @version 1.0
   * @package osmuzzarellas
   * 
   * LISTA DE MODIFICAÇÕES:
   *
   * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
   * ======    ==========   ===========   =============================================================
   *
   * 1.0       03/10/2012   PEDRO H       Criado. 
   *
   */ 

  require_once '../../bd.php';
  require_once '../lib/php/formatacao.php';
  require_once '../lib/php/formulario.php';
  require_once '../lib/php/mensagem.php';

  cabecalho('Relatório Ingredientes Consumidos por Período');

  $acao = validaVarPost('acao');

  $exibir_barra_lateral = false;

  $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />
<script src="../lib/js/calendario.js" type="text/javascript"></script>

<script type="text/javascript">
window.addEvent('domready', function()
{
    new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
    new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
    
});

</script>

<?
  $cod_pizzarias = validaVarPost('cod_pizzarias');   
  $data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
  $data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
?>

<div>
  <form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="cod_pizzarias">Pizzaria:</label></td>
    <td class="tdbt sep">&nbsp;</td>
    <td class="tdbr tdbt">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">&nbsp;</option>
        <?
          $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
          $con = conectabd();
          $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
          while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
          {
            echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
            if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
            {
              echo 'selected';
            }
            echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
          }
        ?>
      </select>
    </td>
  </tr>



  <tr>
    <td class="legenda tdbl" align="right"><label for="data_inicial">Data inicial:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr">
      <input class="requerido" type="text" name="data_inicial" id="data_inicial" size="10" value="<? echo bd2data($data_inicial) ?>" onkeypress="return MascaraData(this, event)">
      &nbsp;<a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>



  <tr>
    <td class="legenda tdbl sep" align="right"><label for="data_final">Data final:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <input class="requerido" type="text" name="data_final" id="data_final" size="10" value="<? echo bd2data($data_final) ?>" onkeypress="return MascaraData(this, event)">
      &nbsp;<a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>



  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
  </form>

  <?
    if ( ($cod_pizzarias) || ($mes) )
    {
      $arr_estoque = array();
      $arr_estoque_bebida = array();
      $con = conectar_bd();


      echo '<div align="center">';

      echo '<br />';

      echo ' <table class="listaEdicao" style="width:1000px;" align="center" cellpadding="0" cellspacing="0">';
      echo '  <thead>';
      echo '   <tr>';
      echo '    <td align="center" width="700">Ingrediente</td>';
      echo '    <td align="center" width="100">Consumido</td>';
      echo '    <td align="center" width="100">Compra</td>';
      echo '    <td align="center" width="100">Estoque Final</td>';
      echo '   </tr>';
      echo '  </thead>';

      echo '  <tbody>';


      $sql_buscar_ingredientes = "SELECT cod_ingredientes, ingrediente, cod_unidade_padrao FROM ipi_ingredientes WHERE ativo = '1' ORDER BY ingrediente";
      $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);

      while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
      {
        $cod_ingredientes = $obj_buscar_ingredientes->cod_ingredientes;
        $ingrediente = $obj_buscar_ingredientes->ingrediente;

        $arr_estoque[$cod_ingredientes]['Nome'] = $ingrediente;

        $sql_buscar_ingredientes_und_padrao = "SELECT divisor_comum, abreviatura FROM ipi_unidade_padrao WHERE cod_unidade_padrao = '".$obj_buscar_ingredientes->cod_unidade_padrao."'";

        $res_buscar_ingredientes_und_padrao = mysql_query($sql_buscar_ingredientes_und_padrao);
        $obj_buscar_ingredientes_und_padrao = mysql_fetch_object($res_buscar_ingredientes_und_padrao);



        $sql_buscar_ingredientes_estoque_consumo = "SELECT SUM(quantidade) as consumo FROM ipi_estoque WHERE quantidade < 0 AND data_hora_lancamento BETWEEN '".$data_inicial."' AND '".$data_final."' AND cod_ingredientes = '".$cod_ingredientes."' AND cod_pizzarias = '$cod_pizzarias'";

        $res_buscar_ingredientes_estoque_consumo = mysql_query($sql_buscar_ingredientes_estoque_consumo);
        $obj_buscar_ingredientes_estoque_consumo = mysql_fetch_object($res_buscar_ingredientes_estoque_consumo);

        $arr_estoque[$cod_ingredientes]['qtd_estoque_consumo'] = abs($obj_buscar_ingredientes_estoque_consumo->consumo)/($obj_buscar_ingredientes_und_padrao->divisor_comum ? $obj_buscar_ingredientes_und_padrao->divisor_comum : 1);

       
        $sql_buscar_ingredientes_estoque_compra = "SELECT SUM(quantidade) as compra FROM ipi_estoque WHERE quantidade > 0 AND data_hora_lancamento BETWEEN '".$data_inicial."' AND '".$data_final."' AND cod_ingredientes = '".$cod_ingredientes."' AND cod_pizzarias = '$cod_pizzarias'";

        $res_buscar_ingredientes_estoque_compra = mysql_query($sql_buscar_ingredientes_estoque_compra);
        $obj_buscar_ingredientes_estoque_compra = mysql_fetch_object($res_buscar_ingredientes_estoque_compra);

        $arr_estoque[$cod_ingredientes]['qtd_estoque_compra'] = ($obj_buscar_ingredientes_estoque_compra->compra)/($obj_buscar_ingredientes_und_padrao->divisor_comum ? $obj_buscar_ingredientes_und_padrao->divisor_comum : 1);


        $arr_estoque[$cod_ingredientes]['qtd_estoque_final'] = $arr_estoque[$cod_ingredientes]['qtd_estoque_compra'] - $arr_estoque[$cod_ingredientes]['qtd_estoque_consumo'];

        echo '   <tr>';
        echo '    <td><strong>'.$arr_estoque[$cod_ingredientes]['Nome'].'</strong> '.($obj_buscar_ingredientes_und_padrao->abreviatura ? '(em '.$obj_buscar_ingredientes_und_padrao->abreviatura.')' : '').'</td>';
        echo '    <td align="right">'.(bd2moeda($arr_estoque[$cod_ingredientes]['qtd_estoque_consumo']) ? bd2moeda($arr_estoque[$cod_ingredientes]['qtd_estoque_consumo']) : "0,00").'</td>';  
        echo '    <td align="right">'.(bd2moeda($arr_estoque[$cod_ingredientes]['qtd_estoque_compra']) ? bd2moeda($arr_estoque[$cod_ingredientes]['qtd_estoque_compra']) : "0,00").'</td>'; 
        echo '    <td align="right">'.(bd2moeda($arr_estoque[$cod_ingredientes]['qtd_estoque_final']) ? bd2moeda($arr_estoque[$cod_ingredientes]['qtd_estoque_final']) : "0,00").'</td>';  
        echo '   </tr>';
      }

      echo '  </tbody>';    
      echo ' </table>';
      echo '<br />';

      ///////////////////

      echo '<br />';

      echo ' <table class="listaEdicao" style="width:1000px;" align="center" cellpadding="0" cellspacing="0">';
      echo '  <thead>';
      echo '   <tr>';
      echo '    <td align="center" width="700">Bebida</td>';
      echo '    <td align="center" width="100">Consumido</td>';
      echo '    <td align="center" width="100">Compra</td>';
      echo '    <td align="center" width="100">Estoque Final</td>';
      echo '   </tr>';
      echo '  </thead>';

      echo '  <tbody>';

      $sql_buscar_bebidas = "SELECT ib.cod_bebidas, ib.bebida, ib.cod_unidade_padrao, ic.cod_conteudos, ic.conteudo, ibic.cod_bebidas_ipi_conteudos FROM ipi_bebidas ib INNER JOIN ipi_bebidas_ipi_conteudos ibic ON (ib.cod_bebidas = ibic.cod_bebidas) INNER JOIN ipi_conteudos ic ON (ibic.cod_conteudos = ic.cod_conteudos) ORDER BY ib.bebida, ic.conteudo";
      $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
      while($obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas))
      {
        $cod_bebidas_ipi_conteudos = $obj_buscar_bebidas->cod_bebidas_ipi_conteudos;
        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['cod_bebida'] = $obj_buscar_bebidas->cod_bebidas;
        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['Bebida'] = $obj_buscar_bebidas->bebida;
        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['cod_conteudos'] = $obj_buscar_bebidas->cod_conteudos;
        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['Conteudo'] = $obj_buscar_bebidas->conteudo;

        $sql_buscar_bebidas_und_padrao = "SELECT divisor_comum, abreviatura FROM ipi_unidade_padrao WHERE cod_unidade_padrao = '".$obj_buscar_bebidas->cod_unidade_padrao."'";

        $res_buscar_bebidas_und_padrao = mysql_query($sql_buscar_bebidas_und_padrao);
        $obj_buscar_bebidas_und_padrao = mysql_fetch_object($res_buscar_bebidas_und_padrao);



        $sql_buscar_bebidas_estoque_consumo = "SELECT SUM(quantidade) as consumo FROM ipi_estoque WHERE quantidade < 0 AND data_hora_lancamento BETWEEN '".$data_inicial."' AND '".$data_final."' AND cod_bebidas_ipi_conteudos = '".$cod_bebidas_ipi_conteudos."' AND cod_pizzarias = '$cod_pizzarias'";

        $res_buscar_bebidas_estoque_consumo = mysql_query($sql_buscar_bebidas_estoque_consumo);
        $obj_buscar_bebidas_estoque_consumo = mysql_fetch_object($res_buscar_bebidas_estoque_consumo);

        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_consumo'] = abs(($obj_buscar_bebidas_estoque_consumo->consumo)/($obj_buscar_bebidas_und_padrao->divisor_comum ? $obj_buscar_bebidas_und_padrao->divisor_comum : 1));

        
        $sql_buscar_bebidas_estoque_compra = "SELECT SUM(quantidade) as compra FROM ipi_estoque WHERE quantidade > 0 AND data_hora_lancamento BETWEEN '".$data_inicial."' AND '".$data_final."' AND cod_bebidas_ipi_conteudos = '".$cod_bebidas_ipi_conteudos."' AND cod_pizzarias = '$cod_pizzarias'";

        $res_buscar_bebidas_estoque_compra = mysql_query($sql_buscar_bebidas_estoque_compra);
        $obj_buscar_bebidas_estoque_compra = mysql_fetch_object($res_buscar_bebidas_estoque_compra);

        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_compra'] = ($obj_buscar_bebidas_estoque_compra->compra)/($obj_buscar_bebidas_und_padrao->divisor_comum ? $obj_buscar_bebidas_und_padrao->divisor_comum : 1);


        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_final'] = $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_compra'] - $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_consumo'];

        echo '   <tr>';
        echo '    <td><strong>'.$arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['Bebida'].' - '.$arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['Conteudo'].'</strong> '.($obj_buscar_bebidas_und_padrao->abreviatura ? '(em '.$obj_buscar_bebidas_und_padrao->abreviatura.')' : '').'</td>';       
        echo '    <td align="right">'.(bd2moeda($arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_consumo']) ? bd2moeda($arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_consumo']) : "0,00").'</td>'; 
        echo '    <td align="right">'.(bd2moeda($arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_consumo']) ? bd2moeda($arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_compra']) : "0,00").'</td>'; 
        echo '    <td align="right">'.(bd2moeda($arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_consumo']) ? bd2moeda($arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_final']) : "0,00").'</td>';  
        echo '   </tr>';
      }

      echo '  </tbody>';    
      echo ' </table>';

      echo '<br />';

      /*echo '<pre>';
      print_r($arr_estoque_bebida);
      echo '<br ><br >//////////////////////////////////////////////<br ><br >';
      print_r($arr_estoque);
      echo '<pre>';*/


      desconectar_bd($con);
    }
    else
      echo 'Selecione a pizzaria e o mês!';
  ?>
</div>

<? rodape(); ?>
