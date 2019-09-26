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
 * 1.0       23/03/2013   Tigs          Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/estoque.php';

cabecalho('Inventário');

$chave_primaria = 'cod_titulos';
$tabela = 'ipi_estoque_contagem';
$codigo_usuario = $_SESSION['usuario']['codigo'];
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

$acao = validar_var_post('acao');
$passo = validar_var_post('passo');

$cod_pizzarias = validar_var_post('cod_pizzarias');
$divis = validar_var_post('divisor');

$contagem1 = validar_var_post('contagem1');
$contagem2 = validar_var_post('contagem2');
$contagem3 = validar_var_post('contagem3');
$observacao = validar_var_post('observacao');

$contagem_bebida1 = validar_var_post('contagem_bebida1');
$contagem_bebida2 = validar_var_post('contagem_bebida2');
$contagem_bebida3 = validar_var_post('contagem_bebida3');
$observacao_bebidas = validar_var_post("observacao_bebidas");

// if ($passo!=""){
//   $acao = 'ajustar';
// }
$data_hora = validar_var_post("data_hora");
// echo '<script>alert("'.$passo.'")</script>';
  /*echo "<pre>";
  print_r($_POST);
  echo "</pre>";*/

if($acao == 'ajustar')
{
  $conexao = conectar_bd();
  $arr_estoque_atual = array();
  $ing_exist = array();
  $data_hora[3] = date("Y-m-d H:i:s");
  $estoque = new Estoque();
  $cod_estoque_tipo_lancamento = "5";
  
  $SqlBuscaIngredientes = "SELECT i.cod_ingredientes , (SELECT SUM(e.quantidade) FROM ipi_estoque e WHERE e.cod_ingredientes = i.cod_ingredientes AND e.cod_pizzarias IN ($cod_pizzarias) ) quantidade_atual FROM ipi_ingredientes i LEFT JOIN ipi_unidade_padrao up ON (up.cod_unidade_padrao = i.cod_unidade_padrao) WHERE i.cod_ingredientes_baixa = i.cod_ingredientes ORDER BY i.ingrediente";
  $resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);

  while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) 
  {
    $arr_estoque_atual["ING"][$objBuscaIngredientes->cod_ingredientes] = $objBuscaIngredientes->quantidade_atual;
  }

  $SqlBuscaIngredientes = "SELECT bc.cod_bebidas_ipi_conteudos, (SELECT SUM(e.quantidade) FROM ipi_estoque e WHERE e.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND e.cod_pizzarias IN ($cod_pizzarias) ) quantidade_atual FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON(bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON(bc.cod_conteudos=c.cod_conteudos) LEFT JOIN ipi_conteudos_pizzarias cp ON (bc.cod_bebidas_ipi_conteudos = cp.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias IN ($cod_pizzarias)) ORDER BY b.bebida, c.conteudo";
  $resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);

  //echo "<br>1: ".$SqlBuscaIngredientes;
  while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) 
  {
    $arr_estoque_atual["BEB"][$objBuscaIngredientes->cod_bebidas_ipi_conteudos] = $objBuscaIngredientes->quantidade_atual;
  }
  
  $sql_buscar_ingredientes_existentes = "select cod_ingredientes from ipi_ingredientes";
  $res_buscar_ingredientes_existentes = mysql_query($sql_buscar_ingredientes_existentes);
  while($obj_buscar_ingredientes_existentes = mysql_fetch_object($res_buscar_ingredientes_existentes))
  {
    $ing_exist["ING"][] = $obj_buscar_ingredientes_existentes->cod_ingredientes;
  }

  $sql_buscar_ingredientes_existentes = "select cod_bebidas_ipi_conteudos from ipi_bebidas_ipi_conteudos";
  $res_buscar_ingredientes_existentes = mysql_query($sql_buscar_ingredientes_existentes);
  while($obj_buscar_ingredientes_existentes = mysql_fetch_object($res_buscar_ingredientes_existentes))
  {
    $ing_exist["BEB"][] = $obj_buscar_ingredientes_existentes->cod_bebidas_ipi_conteudos;
  }

  $res_inserir_contagem = true;
  $sql_inserir_inventario = "INSERT INTO ipi_estoque_inventario (cod_usuarios_contagem,cod_pizzarias,data_hora_contagem) values ($codigo_usuario,$cod_pizzarias,'".$data_hora[3]."')";
  $res_inserir_contagem = mysql_query($sql_inserir_inventario);
//echo $sql_inserir_inventario;
  if($res_inserir_contagem)
  {
    $cod_inventarios = mysql_insert_id();
    foreach ($contagem3 as $cod => $quantidae) {

      if($quantidae!="")
      {
        if(in_array($cod, $ing_exist["ING"]))
        {
          $divisor = ($divis[$cod] !="" ? $divis[$cod] : 1);

          $float_estoque_atual = (float) $arr_estoque_atual["ING"][$cod];
          $float_estoque_novo = (float) (moeda2bd($contagem3[$cod])*$divisor);
          $float_estoque_ajuste = $float_estoque_novo + (-1 * $float_estoque_atual);

          $observacao2 = "Lançamento referente a contagem de estoque do dia ".date("d/m/Y",$data_hora[3]);
          //$observacao2 = "Lançamento referente a contagem de estoque do dia 31/03/2013";
          $res_estoque &= $estoque->lancar_estoque($float_estoque_ajuste, $cod, 0, "INGREDIENTE", $cod_pizzarias, $cod_estoque_tipo_lancamento, 0, 0, 0, $observacao2 );
          //echo $sql_inserir_contagem."<br/>";

          $sql_inserir_contagem = sprintf("INSERT INTO $tabela (cod_inventarios, cod_ingredientes, cod_bebidas_ipi_conteudos, quantidade1, quantidade2, quantidade3,quantidade_ajuste, observacao, tipo_contagem ,data_hora_contagem1 ,data_hora_contagem2 ,data_hora_contagem3) values (%d,%d,0,'%s','%s','%s','%s','%s','%s','%s','%s','%s')",$cod_inventarios, $cod,(moeda2bd($quantidae)*$divisor), (moeda2bd($contagem2[$cod])*$divisor), (moeda2bd($contagem3[$cod])*$divisor) ,$float_estoque_ajuste,$observacao[$cod],"INGREDIENTE",$data_hora[1],$data_hora[2],$data_hora[3]);
          $res_inserir_contagem &= mysql_query($sql_inserir_contagem);
          //echo $sql_inserir_contagem."<br/>";
        }
      }
    }

    foreach ($contagem_bebida3 as $cod => $quantidae) {

      if($quantidae!="")
      {
        if(in_array($cod, $ing_exist["BEB"]))
        {

          $float_estoque_atual = (float) $arr_estoque_atual["BEB"][$cod];
          $float_estoque_novo = (float) moeda2bd($contagem_bebida3[$cod]);
          $float_estoque_ajuste = $float_estoque_novo + (-1 * $float_estoque_atual);
          //$float_estoque_ajuste = (-1 * $float_estoque_atual);
          //echo "<Br>$a: "."   #   ".$txt_quantidade_atual_bebidas[$a]."   #   ".$txt_nova_quantidade_bebidas[$a]."    #    ".$float_estoque_ajuste." - ".$cod_ingredientes[$a]." - 0 - BEBIDA - ".$cod_pizzarias." - ".$cod_estoque_tipo_lancamento." - 0 - 0 - ".$obs[$a];

          $observacao2 = "Lançamento referente a contagem de estoque do dia ".date("d/m/Y",$data_hora[3]);
         // $observacao2 = "Lançamento referente a contagem de estoque do dia 31/03/2013";
          $res_estoque &= $estoque->lancar_estoque($float_estoque_ajuste, 0, $cod, "BEBIDA", $cod_pizzarias, $cod_estoque_tipo_lancamento, 0, 0, 0, $observacao2 );

          $sql_inserir_contagem = sprintf("INSERT INTO $tabela (cod_inventarios, cod_ingredientes, cod_bebidas_ipi_conteudos, quantidade1, quantidade2, quantidade3,quantidade_ajuste, observacao, tipo_contagem ,data_hora_contagem1 ,data_hora_contagem2 ,data_hora_contagem3) values (%d,0,%d,'%s','%s','%s','%s','%s','%s','%s','%s','%s')",$cod_inventarios, $cod, moeda2bd($quantidae), moeda2bd($contagem_bebida2[$cod]), moeda2bd($contagem_bebida3[$cod]),$float_estoque_ajuste,$observacao_bebidas[$cod],"BEBIDA",$data_hora[1],$data_hora[2],$data_hora[3]);
          $res_inserir_contagem &= mysql_query($sql_inserir_contagem);
          //echo $sql_inserir_contagem."<br/>";

        }
      }
    }
  }

  if ($res_inserir_contagem)
  {
      mensagemOk('Estoque Ajustado com êxito!');
  }
  else
  {
      mensagemErro('Erro ao ajustar estoque', 'Por favor, verifique se alguma contagem foi deixada em branco.');
  }

  desconectar_bd($conexao);
}

//echo "<br>contagem1: ".$contagem1." XXX";
//echo "<br>contagem_bebida1: ".$contagem_bebida1." XXX";
//echo "<br>contagem1: ".$contagem1[0]." - ".$contagem1[1];

/*if ($passo=='3')
{
  $contagem1 = explode(",", $contagem1[0]);
  $contagem_bebida1 = explode(",", $contagem_bebida1[0]);

  $contagem2 = explode(",", $contagem2[0]);
  $contagem_bebida2 = explode(",", $contagem_bebida2[0]);
}*/

//echo "<pre>";
//print_r($contagem1);
//echo "</pre>";

//echo "<br>contagem1: ".$contagem1[0]." - ".$contagem1[1];
?>
<style>
  .td_vermelho
  {
    background-color:lightpink;
  }
</style>
<script src="../../sys/lib/js/mascara.js" type="text/javascript"></script>
<script>

/*
$$("input[type='text']").keyup(function (e) {
  alert('nenter');
    if (e.keyCode == 13) {
        // Do something
        alert('enter');
    }
});
*/
/*window.addEvent( 'keydown', function( evt ){
   if( evt.key == 'enter')
   {
      alert("pressed");
      return false;
   }
}); //' && evt.shift */
</script>

<!-- Tab Editar -->
<div class="painelTab">
        
  <form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
      <tr>
        <td class="legenda tdbl tdbt sep" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
        <td class="tdbt sep">&nbsp;</td>
        <td class="tdbr tdbt sep">
          <select name="cod_pizzarias" id="cod_pizzarias">
            <option value="">Selecione um(a) <? echo ucfirst(TIPO_EMPRESA)?></option>
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
          <td align="right" class="tdbl tdbb tdbr" colspan="3"><input
              class="botaoAzul" type="submit" value="Buscar"></td>
      </tr>
  </table>
  <input type="hidden" name="acao" value="buscar">
  <input type="hidden" name="passo" value="1">
  </form>

  <br />

  <div style='margin: 0 auto; width: 1000px;'>
  <form name="FrmContagem" id="FrmContagem" method="post">
  <?
    $conexao = conectar_bd();
    if($acao == 'buscar')
    {
      $arr_consumo1 = array();
      $arr_margem_erro = array();
      $sql_buscar_contagems = "SELECT ec.cod_inventarios from $tabela ec inner join ipi_estoque_inventario ev on ev.cod_inventarios = ec.cod_inventarios where day(ec.data_hora_contagem1) = day(now()) and month(ec.data_hora_contagem1) = month(now()) and year(ec.data_hora_contagem1) = year(now()) and ev.cod_pizzarias = '$cod_pizzarias'";
      $res_buscar_contagems = mysql_query($sql_buscar_contagems);
      $num_contagems = mysql_num_rows($res_buscar_contagems);
      $obj_buscar_contagems = mysql_fetch_object($res_buscar_contagems);

      if($num_contagems<=0)
      {

          //ingredientes padroes
          /*$sql_buscar_pizzas = "SELECT pf.cod_pizzas,pp.cod_tamanhos,pp.quant_fracao,ie.cod_ingredientes,ie.quantidade_estoque_ingrediente,pp.cod_pedidos_pizzas from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_pedidos_fracoes pf on pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_ingredientes_estoque ie on ie.cod_pizzas = pf.cod_pizzas where ie.cod_tamanhos = pp.cod_tamanhos and p.cod_pedidos in (SELECT p.cod_pedidos from ipi_pedidos p inner join ipi_pizzarias pi on pi.cod_pizzarias = p.cod_pizzarias where p.cod_pizzarias in ($cod_pizzarias) AND p.data_hora_pedido <= NOW() AND p.situacao IN ('BAIXADO') AND p.cod_pizzarias IN ($cod_pizzarias))";

          $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
          //echo $sql_buscar_pizzas;
          while($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
          {
            $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] = $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] + ($obj_buscar_pizzas->quantidade_estoque_ingrediente / $obj_buscar_pizzas->quant_fracao);
            //echo "<br/>pedido= ".$obj_buscar_pizzas->cod_pedidos_pizzas." tamanho = ".$obj_buscar_pizzas->cod_tamanhos." fracao=".$obj_buscar_pizzas->quant_fracao." qtd=".($obj_buscar_pizzas->quantidade_estoque_ingrediente / $obj_buscar_pizzas->quant_fracao);
          }

          $obj_buscar_pizzas = "";
          $sql_buscar_pizzas2 = "SELECT pf.cod_pizzas,pp.cod_tamanhos,pp.quant_fracao,it.cod_ingredientes,it.quantidade_estoque_extra,pp.cod_pedidos_pizzas from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_pedidos_fracoes pf on pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_pedidos_ingredientes pe on pe.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_ingredientes_ipi_tamanhos it on it.cod_ingredientes = pe.cod_ingredientes where it.cod_pizzarias = 1 and it.cod_tamanhos = pp.cod_tamanhos and pe.ingrediente_padrao = 0  and p.cod_pedidos in (SELECT p.cod_pedidos from ipi_pedidos p inner join ipi_pizzarias pi on pi.cod_pizzarias = p.cod_pizzarias where p.cod_pizzarias in ($cod_pizzarias) AND p.data_hora_pedido <= NOW() AND p.situacao IN ('BAIXADO') AND p.cod_pizzarias IN ($cod_pizzarias))";//BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59'

          $res_buscar_pizzas2 = mysql_query($sql_buscar_pizzas2);
          //echo "<br/>".$sql_buscar_pizzas2;
          while($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas2))
          {
            $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] = $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] + ($obj_buscar_pizzas->quantidade_estoque_extra / $obj_buscar_pizzas->quant_fracao);

            //echo "<br/>22pedido= ".$obj_buscar_pizzas->cod_pedidos_pizzas." tamanho = ".$obj_buscar_pizzas->cod_tamanhos." fracao=".$obj_buscar_pizzas->quant_fracao." qtd=".($obj_buscar_pizzas->quantidade_estoque_extra / $obj_buscar_pizzas->quant_fracao);
          }*/
        if ($passo == '1')
        {
          $codigo = validar_var_post('cod_pizzarias');
          echo ' <table align="center" cellpadding="0" cellspacing="0" border="1">';
          echo '  <thead>';
          echo '   <tr bgcolor="#DDDDDD">';
          echo '    <td align="center" width="300"><strong>Ingrediente</strong></td>';
          echo '    <td align="center" width="100"><strong>Contagem 1</strong></td>';
          echo '   </tr>';
          echo '  </thead>';
          echo '  <tbody>';
          $sql_buscar_ingredientes = "SELECT ii.cod_ingredientes, ii.ingrediente_abreviado as ingrediente, iup.divisor_comum, iup.abreviatura,(select saldo_final from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' and cod_ingredientes = ii.cod_ingredientes order by data_movimentacao DESC LIMIT 1)  as saldo_teorico FROM ipi_ingredientes ii LEFT JOIN ipi_unidade_padrao iup ON (iup.cod_unidade_padrao = ii.cod_unidade_padrao) WHERE ii.cod_ingredientes_baixa = ii.cod_ingredientes and ii.ativo = 1 ORDER BY ingrediente ASC";
          $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
          while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
          { 
            $abreviatura = ($obj_buscar_ingredientes->abreviatura ? ' (em '.$obj_buscar_ingredientes->abreviatura.')' : '');
            $divisor_comum = ($obj_buscar_ingredientes->divisor_comum ? $obj_buscar_ingredientes->divisor_comum : 1);
            
            echo '<tr>';
            echo '  <td>'.$obj_buscar_ingredientes->ingrediente.$abreviatura.'</td>';//.' - '.$obj_buscar_ingredientes->cod_ingredientes
            echo '  <td align="center">'.'<input type="text" name="contagem1['.$obj_buscar_ingredientes->cod_ingredientes.']" value="" size="8"  onkeypress="return formataMoeda(this, \'.\', \',\', event)"/>'.'</td>';// onkeyup="formataMoeda3casas(this, 3)" 
            echo '</tr>';
          }
          echo '</tbody>';
          echo '</table>';

          echo '<br/></br/>';
          echo ' <table align="center" cellpadding="0" cellspacing="0" border="1">';
          echo '  <thead>';
          echo '   <tr bgcolor="#DDDDDD">';
          echo '    <td align="center" width="300">Bebida</td>';
          echo '    <td align="center" width="100">Contagem 1</td>';
          echo '   </tr>';
          echo '  </thead>';
          echo '  <tbody>';
          $sql_buscar_bebidas = "SELECT ibic.cod_bebidas_ipi_conteudos, ib.bebida, ic.conteudo,(select saldo_final from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' and cod_bebidas_ipi_conteudos = ibic.cod_bebidas_ipi_conteudos order by data_movimentacao DESC LIMIT 1)  as saldo_teorico FROM ipi_bebidas ib INNER JOIN ipi_bebidas_ipi_conteudos ibic ON (ibic.cod_bebidas = ib.cod_bebidas) INNER JOIN ipi_conteudos ic ON (ibic.cod_conteudos = ic.cod_conteudos)";
          $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
          $num_buscar_bebidas = mysql_num_rows($res_buscar_bebidas);
          for($a=0; $a<$num_buscar_bebidas; $a++)
          {
            $obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas);
            echo '<tr>';
            echo '  <td>'.$obj_buscar_bebidas->bebida.' - '.$obj_buscar_bebidas->conteudo.'</td>';
            echo '  <td align="center">'.'<input type="text" name="contagem_bebida1['.$obj_buscar_bebidas->cod_bebidas_ipi_conteudos.']" value="" size="8" onkeypress="return formataMoeda(this, \'.\', \',\', event)" />'.'</td>';// onkeyup="formataMoeda3casas(this, 3)"
            echo '</tr>';
          }
          echo '</tbody>';
          echo '</table>';

          echo '<input type="hidden" name="passo" value="2">';
          //echo '<input type="hidden" name="data_hora[1]" value="'.date("Y-m-d H:i:s").'">';
          echo '<input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'">';
          echo '<input type="hidden" name="acao" value="buscar">';
          echo '<br /><input class="botaoAzul" type="button" onclick="$(\'FrmContagem\').submit();" value="Registrar Contagem">';
        }



        if ($passo == '2')
        {


          $codigo = validar_var_post('cod_pizzarias');
          echo ' <table align="center" cellpadding="0" cellspacing="0" border="1">';

          echo '  <thead>';
          echo '   <tr bgcolor="#DDDDDD">';
          echo '    <td align="center" width="300"><strong>Ingrediente</strong></td>';
          echo '    <td align="center" width="100"><strong>Contagem 1</strong></td>';
          echo '    <td align="center" width="100"><strong>Contagem 2</strong></td>';
          echo '   </tr>';
          echo '  </thead>';
          echo '  <tbody>';
          $sql_buscar_ingredientes = "SELECT ii.cod_ingredientes, ii.ingrediente_abreviado as ingrediente, iup.divisor_comum, iup.abreviatura,(select saldo_final from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' and cod_ingredientes = ii.cod_ingredientes order by data_movimentacao DESC LIMIT 1) as saldo_teorico FROM ipi_ingredientes ii LEFT JOIN ipi_unidade_padrao iup ON (iup.cod_unidade_padrao = ii.cod_unidade_padrao) WHERE ii.cod_ingredientes_baixa = ii.cod_ingredientes and ii.ativo = 1  ORDER BY ingrediente ASC";
          $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
          $num_buscar_ingredientes = mysql_num_rows($res_buscar_ingredientes);
          for ($a=0; $a<$num_buscar_ingredientes; $a++)
          {
            $classe = "";
            $obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes);
            $abreviatura = ($obj_buscar_ingredientes->abreviatura ? ' (em '.$obj_buscar_ingredientes->abreviatura.')' : '');
            $divisor_comum = ($obj_buscar_ingredientes->divisor_comum ? $obj_buscar_ingredientes->divisor_comum : 1);
            $arr_margem_erro[$obj_buscar_ingredientes->cod_ingredientes] = floor($obj_buscar_ingredientes->cod_ingredientes / 5) ;

            /*$margem_mais = ($arr_margem_erro[$obj_buscar_ingredientes->cod_ingredientes]/100)+1;
            $margem_menos = 1 - ($arr_margem_erro[$obj_buscar_ingredientes->cod_ingredientes]/100);
            $consumo = ($arr_consumo1[$obj_buscar_ingredientes->cod_ingredientes] > 0 ? $arr_consumo1[$obj_buscar_ingredientes->cod_ingredientes] : 1);
            $porcentagem = ($contagem1[$obj_buscar_ingredientes->cod_ingredientes]/$consumo);
            if($porcentagem <= $margem_menos || $porcentagem >= $margem_mais)
            {
              $classe = "class='td_vermelho'".' - '.$consumo;
            }*/
            //echo "<br/>cont ".((int) $contagem1[$obj_buscar_ingredientes->cod_ingredientes]);
            //echo " - map ".(int) bd2moeda($obj_buscar_ingredientes->saldo_teorico/$divisor_comum);
            if((int)$contagem1[$obj_buscar_ingredientes->cod_ingredientes] == (int) bd2moeda($obj_buscar_ingredientes->saldo_teorico/$divisor_comum) || $contagem1[$obj_buscar_ingredientes->cod_ingredientes] == "")
            {
              echo '<tr >';
            }
            else
            {
              echo '<tr class="td_vermelho" >';
            }
            
            echo '  <td>'.$obj_buscar_ingredientes->ingrediente.$abreviatura.'</td>';//.' - '.bd2moeda($obj_buscar_ingredientes->saldo_teorico/$divisor_comum)
            echo '  <td align="center" '.$classe.'>'.($contagem1[$obj_buscar_ingredientes->cod_ingredientes]).'<input type="hidden" name="contagem1['.$obj_buscar_ingredientes->cod_ingredientes.']" value="'.$contagem1[$obj_buscar_ingredientes->cod_ingredientes].'" size="8" />'.'</td>';
            echo '  <td align="center">'.'<input type="text" name="contagem2['.$obj_buscar_ingredientes->cod_ingredientes.']" value="" size="8"  onkeypress="return formataMoeda(this, \'.\', \',\', event)"/>'.'</td>';// onkeyup="formataMoeda3casas(this, 3)" 
            echo '</tr>';
          }
          echo '</tbody>';
          echo '</table>';

          echo '<br/></br/>';
          echo ' <table align="center" cellpadding="0" cellspacing="0" border="1">';
          echo '  <thead>';
          echo '   <tr bgcolor="#DDDDDD">';
          echo '    <td align="center" width="300">Bebida</td>';
          echo '    <td align="center" width="100">Contagem 1</td>';
          echo '    <td align="center" width="100">Contagem 2</td>';
          echo '   </tr>';
          echo '  </thead>';
          echo '  <tbody>';
          $sql_buscar_bebidas = "SELECT ibic.cod_bebidas_ipi_conteudos, ib.bebida, ic.conteudo,(select saldo_final from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' and cod_bebidas_ipi_conteudos = ibic.cod_bebidas_ipi_conteudos order by data_movimentacao DESC LIMIT 1)  as saldo_teorico FROM ipi_bebidas ib INNER JOIN ipi_bebidas_ipi_conteudos ibic ON (ibic.cod_bebidas = ib.cod_bebidas) INNER JOIN ipi_conteudos ic ON (ibic.cod_conteudos = ic.cod_conteudos)";
          $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
          $num_buscar_bebidas = mysql_num_rows($res_buscar_bebidas);
          for($a=0; $a<$num_buscar_bebidas; $a++)
          {
            $obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas);


            if((int)$contagem_bebida1[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos] == (int) bd2moeda($obj_buscar_bebidas->saldo_teorico) || $contagem_bebida1[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos] == "" )
            {
              echo '<tr >';
            }
            else
            {
              echo '<tr class="td_vermelho" >';
            }

            echo '  <td>'.$obj_buscar_bebidas->bebida.' - '.$obj_buscar_bebidas->conteudo.'</td>';

            echo '  <td align="center">'.($contagem_bebida1[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos]).'<input type="hidden" name="contagem_bebida1['.$obj_buscar_bebidas->cod_bebidas_ipi_conteudos.']" value="'.$contagem_bebida1[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos].'" size="8" /></td>';

            echo '  <td align="center">'.'<input type="text" name="contagem_bebida2['.$obj_buscar_bebidas->cod_bebidas_ipi_conteudos.']" value="" size="8" onkeypress="return formataMoeda(this, \'.\', \',\', event)" />'.'</td>'; //onkeyup="formataMoeda3casas(this, 3)" 
            echo '</tr>';
          }
          echo '</tbody>';
          echo '</table>';


          echo '<input type="hidden" name="passo" value="3">';
          echo '<input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'">';
          echo '<input type="hidden" name="data_hora[1]" value="'.date("Y-m-d H:i:s").'">';

         // echo "<input type='hidden' name='contagem1[]' value='".implode(",",$contagem1)."' />";
         // echo "<input type='hidden' name='contagem_bebida1[]' value='".implode(",",$contagem_bebida1)."' />";

          echo '<input type="hidden" name="acao" value="buscar">';
          echo '<br /><input class="botaoAzul" type="button" onclick="$(\'FrmContagem\').submit();" value="Registrar Contagem">';
        }



        if ($passo == '3')
        { 
          echo '<input type="hidden" name="passo" value="4">';
          echo '<input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'">';
          echo '<input type="hidden" name="acao" value="ajustar">';
          $data_hora1 = $data_hora[1];
          echo '<input type="hidden" name="data_hora[1]" value="'.$data_hora1.'">';
          echo '<input type="hidden" name="data_hora[2]" value="'.date("Y-m-d H:i:s").'">';
          $codigo = validar_var_post('cod_pizzarias');
          echo ' <table align="center" cellpadding="0" cellspacing="0" border="1">';
            /*echo "<tr><td><pre>";
          print_r($contagem1);
          echo "</pre></td></tr>";
                    echo "<tr><td><pre>";
          print_r($contagem2);
          echo "</pre></td></tr>";*/
          echo '  <thead>';
          echo '   <tr bgcolor="#DDDDDD">';
          echo '    <td align="center" width="300"><strong>Ingrediente</strong></td>';
          echo '    <td align="center" width="100"><strong>Contagem 1</strong></td>';
          echo '    <td align="center" width="100"><strong>Contagem 2</strong></td>';
          echo '    <td align="center" width="100"><strong>Contagem 3</strong></td>';
          echo '    <td align="center" width="250"><strong>Observação</strong></td>';
          echo '   </tr>';
          echo '  </thead>';
          echo '  <tbody>';
          $sql_buscar_ingredientes = "SELECT ii.cod_ingredientes, ii.ingrediente_abreviado as ingrediente, iup.divisor_comum, iup.abreviatura,(select saldo_final from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' and cod_ingredientes = ii.cod_ingredientes order by data_movimentacao DESC LIMIT 1) as saldo_teorico FROM ipi_ingredientes ii LEFT JOIN ipi_unidade_padrao iup ON (iup.cod_unidade_padrao = ii.cod_unidade_padrao) WHERE ii.cod_ingredientes_baixa = ii.cod_ingredientes and ii.ativo = 1  ORDER BY ingrediente ASC";
          $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
          $num_buscar_ingredientes = mysql_num_rows($res_buscar_ingredientes);
          for ($a=0; $a<$num_buscar_ingredientes; $a++)
          {
            $obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes);
            $abreviatura = ($obj_buscar_ingredientes->abreviatura ? ' (em '.$obj_buscar_ingredientes->abreviatura.')' : '');
            $divisor_comum = ($obj_buscar_ingredientes->divisor_comum ? $obj_buscar_ingredientes->divisor_comum : 1);

            if((int)$contagem2[$obj_buscar_ingredientes->cod_ingredientes] == (int) bd2moeda($obj_buscar_ingredientes->saldo_teorico/$divisor_comum) || $contagem2[$obj_buscar_ingredientes->cod_ingredientes] == "")
            {
              echo '<tr >';
            }
            else
            {
              echo '<tr class="td_vermelho" >';
            }
            echo '  <td>'.$obj_buscar_ingredientes->ingrediente.$abreviatura.'</td>';
            echo '  <td align="center">'.($contagem1[$obj_buscar_ingredientes->cod_ingredientes]).'<input type="hidden" name="contagem1['.$obj_buscar_ingredientes->cod_ingredientes.']" value="'.$contagem1[$obj_buscar_ingredientes->cod_ingredientes].'" size="8" /></td>';

            echo '  <td align="center">'.($contagem2[$obj_buscar_ingredientes->cod_ingredientes]).'<input type="hidden" name="contagem2['.$obj_buscar_ingredientes->cod_ingredientes.']" value="'.$contagem2[$obj_buscar_ingredientes->cod_ingredientes].'" size="8" /></td>';
            echo '<input type="hidden" name="divisor['.$obj_buscar_ingredientes->cod_ingredientes.']" value="'.$divisor_comum.'"/>';
            echo '  <td align="center">'.'<input  type="text" name="contagem3['.$obj_buscar_ingredientes->cod_ingredientes.']" value="" size="8" onkeypress="return formataMoeda(this, \'.\', \',\', event)" />'.'</td>';//onkeyup="formataMoeda3casas(this, 3)"

            echo '  <td align="center">'.'<input type="text" name="observacao['.$obj_buscar_ingredientes->cod_ingredientes.']" value="" size="30" />'.'</td>';
            echo '</tr>';
          }
          echo '</tbody>';
          echo '</table>';

          echo '<br/></br/>';
          echo ' <table align="center" cellpadding="0" cellspacing="0" border="1">';
          echo '  <thead>';
          echo '   <tr bgcolor="#DDDDDD">';
          echo '    <td align="center" width="300">Bebida</td>';
          echo '    <td align="center" width="100">Contagem 1</td>';
          echo '    <td align="center" width="100">Contagem 2</td>';
          echo '    <td align="center" width="100">Contagem 3</td>';
          echo '    <td align="center" width="250"><strong>Observação</strong></td>';
          echo '   </tr>';
          echo '  </thead>';
          echo '  <tbody>';
          $sql_buscar_bebidas = "SELECT ibic.cod_bebidas_ipi_conteudos, ib.bebida, ic.conteudo,(select saldo_final from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' and cod_bebidas_ipi_conteudos = ibic.cod_bebidas_ipi_conteudos order by data_movimentacao DESC LIMIT 1) as saldo_teorico FROM ipi_bebidas ib INNER JOIN ipi_bebidas_ipi_conteudos ibic ON (ibic.cod_bebidas = ib.cod_bebidas) INNER JOIN ipi_conteudos ic ON (ibic.cod_conteudos = ic.cod_conteudos)";
          $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
          $num_buscar_bebidas = mysql_num_rows($res_buscar_bebidas);
          for($a=0; $a<$num_buscar_bebidas; $a++)
          {
            $obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas);

            if((int)$contagem_bebida2[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos] == (int) bd2moeda($obj_buscar_bebidas->saldo_teorico) || $contagem_bebida2[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos]  == "")
            {
              echo '<tr >';
            }
            else
            {
              echo '<tr class="td_vermelho" >';
            }

            echo '  <td>'.$obj_buscar_bebidas->bebida.' - '.$obj_buscar_bebidas->conteudo.'</td>';

            echo '  <td align="center">'.($contagem_bebida1[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos]).'<input type="hidden" name="contagem_bebida1['.$obj_buscar_bebidas->cod_bebidas_ipi_conteudos.']" value="'.($contagem_bebida1[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos]).'" size="8" /></td>';

            echo '  <td align="center">'.($contagem_bebida2[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos]).'<input type="hidden" name="contagem_bebida2['.$obj_buscar_bebidas->cod_bebidas_ipi_conteudos.']" value="'.($contagem_bebida2[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos]).'" size="8" /></td>';

            echo '  <td align="center">'.'<input type="text" " name="contagem_bebida3['.$obj_buscar_bebidas->cod_bebidas_ipi_conteudos.']" value="" size="8" onkeypress="return formataMoeda(this, \'.\', \',\', event)"/>'.'</td>'; //onkeyup="formataMoeda3casas(this, 3)

            echo '  <td align="center">'.'<input type="text" name="observacao_bebidas['.$obj_buscar_bebidas->cod_bebidas_ipi_conteudos.']" value="" size="30" />'.'</td>';

            echo '</tr>';
          }
          echo '</tbody>';
          echo '</table>';

         
         // echo "<input type='hidden' name='contagem1[]' value='".implode(",",$contagem1)."' />";
          //echo "<input type='hidden' name='contagem_bebida1[]' value='".implode(",",$contagem_bebida1)."' />";

         // echo "<input type='hidden' name='contagem2[]' value='".implode(",",$contagem2)."' />";
          //echo "<input type='hidden' name='contagem_bebida2[]' value='".implode(",",$contagem_bebida2)."' />";
          //echo '<br /><input class="botaoAzul" type="submit" value="Efetuar Ajuste de Estoque">';
          echo '<br /><input class="botaoAzul" type="button" onclick="$(\'FrmContagem\').submit();" value="Efetuar Ajuste de Estoque">';
        }
      }else
      {
        echo "<tr><td>So é permitida uma contagem por dia</td></tr>";
      }

      

    }
    desconectabd($conexao);
       /*           echo "<tr><td><pre>";
        print_r($_POST);
        echo "</pre><Br/><Br/><Br/></td></tr><Br/>";
              echo "<tr><td>1<pre>";
        print_r($contagem1);
        echo "</pre></td></tr>";
                  echo "<tr>2<td><pre>";
        print_r($contagem2);
        echo "</pre></td></tr>";
                          echo "<tr>3<td><pre>";
        print_r($contagem3);
        echo "</pre></td></tr>";*/
  ?>
  </table>

  </form>
  </div>

</div>

<?
rodape();
?>