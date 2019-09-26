<?php

  /**
   * Relatório CPV
   *
   * @version 1.0
   * @package osmuzzarellas
   * 
   * LISTA DE MODIFICAÇÕES:
   *
   * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
   * ======    ==========   ===========   =============================================================
   *
   * 1.0       09/11/2012   PEDRO H       Criado.
   *
   */

  require_once '../../bd.php';
  require_once '../lib/php/formatacao.php';
  require_once '../lib/php/formulario.php';
  require_once '../lib/php/mensagem.php';

  cabecalho('Relatório CMV com todos os '.TIPO_PRODUTOS);

  $acao = validaVarPost('acao');
  $cod_pizzarias = validaVarPost('cod_pizzarias');
  $cod_pizzas = (validar_var_post("cod_pizzas") ? validar_var_post("cod_pizzas") : 0);

  $exibir_barra_lateral = false;

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>
<script src="../../sys/lib/js/mascara.js" type="text/javascript"></script>
<script type="text/javascript">
  
/*function carregar_pizzas(pizzaria, pizza)
{
  var url = "acao=carregar_pizzas&cod_pizzarias="+pizzaria+"&cod_pizzas="+pizza;
  new Request.HTML(
  {
    url: 'ipi_rel_cmv_pizza_ajax.php',
    update: $('combo_pizza'),
    method:'post',
    onSuccess: function(tree, element, html)
    {
      $('botaoBuscar').setStylemes('display', 'inline');
    }
    
  }).send(url);
}*/

window.addEvent('domready', function() 
{
});
</script>


<div>
  <form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt sep" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td class="tdbt sep">&nbsp;</td>
    <td class="tdbr tdbt sep">
      <select name="cod_pizzarias" id="cod_pizzarias" >
        <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
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


  <tr id="combo_pizza" name='combo_pizza' class='combo_pizza'>&nbsp;</tr>


  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input id='botaoBuscar' class="botaoAzul" type="submit" value="Buscar" /></td></tr>
  
  </table>

  <input type="hidden" name="acao" value="buscar">
  </form>
    
  <br />

  <div style='width: 1000px;'>
  <?
    if ( ($cod_pizzarias) )
    {
      

      $con = conectar_bd();
      $arr_valores = array();
      $arr_nome_pizzas = array();
      $arr_tamanhos_nomes = array();

      $sql_buscar_tamanhos = "SELECT ipit.preco, ipit.cod_tamanhos, it.tamanho,ipit.cod_pizzas,p.pizza FROM ipi_pizzas_ipi_tamanhos ipit INNER JOIN ipi_tamanhos it ON (it.cod_tamanhos = ipit.cod_tamanhos) INNER JOIN ipi_pizzas p on p.cod_pizzas = ipit.cod_pizzas WHERE ipit.cod_pizzarias = '".$cod_pizzarias."' ORDER BY p.pizza,it.tamanho DESC";//AND ipit.cod_pizzas = '".$cod_pizzas."' 
      //echo $sql_buscar_tamanhos;
      $res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);
      while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
      {
        $cod_tamanhos = $obj_buscar_tamanhos->cod_tamanhos;
        $cod_pizzas = $obj_buscar_tamanhos->cod_pizzas;
        $preco = $obj_buscar_tamanhos->preco;
        $tamanho = $obj_buscar_tamanhos->tamanho;
        $arr_nome_pizzas[$cod_pizzas] = $obj_buscar_tamanhos->pizza;

        $arr_tamanhos_nomes[$cod_tamanhos]['tamanho'] = $tamanho;
        $arr_tamanhos[$cod_pizzas][$cod_tamanhos]['tamanho'] = $tamanho;
        $arr_tamanhos[$cod_pizzas][$cod_tamanhos]['preco'] = $preco; 

        $sql_buscar_receita = "SELECT ii.cod_ingredientes, ii.cod_ingredientes_baixa, ii.ingrediente, iie.quantidade_estoque_ingrediente, iup.divisor_comum, ii.cod_unidade_padrao FROM ipi_ingredientes ii INNER JOIN ipi_ingredientes_estoque iie ON (iie.cod_ingredientes = ii.cod_ingredientes) LEFT JOIN ipi_unidade_padrao iup ON (iup.cod_unidade_padrao = ii.cod_unidade_padrao) WHERE iie.cod_tamanhos = '".$cod_tamanhos."' AND iie.cod_pizzas = '".$obj_buscar_tamanhos->cod_pizzas."'";
        //echo $sql_buscar_receita;
        $res_buscar_receita = mysql_query($sql_buscar_receita);
        while($obj_buscar_receita = mysql_fetch_object($res_buscar_receita))
        { 
          $cod_ingredientes = $obj_buscar_receita->cod_ingredientes;
          $cod_ingredientes_baixa = $obj_buscar_receita->cod_ingredientes_baixa;
          $ingrediente = $obj_buscar_receita->ingrediente;
          $quant_estoque = $obj_buscar_receita->quantidade_estoque_ingrediente;
          $divisor_comum = ($obj_buscar_receita->cod_unidade_padrao > 0 ? $obj_buscar_receita->divisor_comum : '1');

          $arr_valores["ingrediente"][$cod_ingredientes]['ingrediente'] = $ingrediente;
          $arr_valores["ingrediente"][$cod_ingredientes]['divisor_comum'] = $divisor_comum;
          $arr_valores["pizzas"][$cod_pizzas][$cod_ingredientes]['tamanhos'][$cod_tamanhos]['quant_estoque'] = $quant_estoque;
        }
      }

      

     

      

      $arr_preco = array();
      foreach ($arr_valores["ingrediente"] as $indice => $v_ingre) 
      {
        
        $sql_preco_ingrediente = "SELECT ultima_compra_preco_grama from ipi_estoque_mapa where cod_ingredientes = '$indice' and cod_pizzarias in($cod_pizzarias) order by data_movimentacao DESC LIMIT 1 ";

        $res_preco_ingrediente = mysql_query($sql_preco_ingrediente);
        $obj_preco_ingrediente = mysql_fetch_object($res_preco_ingrediente);

        $arr_valores["ingrediente"][$indice]['sql'] = $sql_preco_ingrediente;
        $arr_valores["ingrediente"][$indice]['numero_nota_fiscal'] = $obj_preco_ingrediente->numero_nota_fiscal;

        $arr_valores["ingrediente"][$indice]['quantidade_embalagem_entrada'] = ($obj_preco_ingrediente->quantidade_embalagem_entrada ? $obj_preco_ingrediente->quantidade_embalagem_entrada : 1);
        
        $arr_valores["ingrediente"][$indice]['qtd_embalagem'] = $arr_valores["ingrediente"][$indice]['quantidade_embalagem_entrada'];
        $arr_valores["ingrediente"][$indice]['preco_unitario_entrada'] = ($obj_preco_ingrediente->preco_unitario_entrada ? $obj_preco_ingrediente->preco_unitario_entrada : 0);
        $date = strtotime($obj_preco_ingrediente->data_hota_entrada_estoque); 
        $arr_valores["ingrediente"][$indice]['data'] = ($date ? date('d/m/Y', $date) : '-');
        $arr_valores["ingrediente"][$indice]['preco_grama'] = ($arr_valores["ingrediente"][$indice]['preco_unitario_entrada']/$arr_valores["ingrediente"][$indice]['qtd_embalagem']);
        $arr_valores["ingrediente"][$indice]['preco_grama'] = $obj_preco_ingrediente->ultima_compra_preco_grama;
        //echo '<td><a target="_blank" href="ipi_consulta_entrada_produtos.php?cp='.$cod_pizzarias.'&ci='.$indice.'">'.$arr_valores["ingrediente"][$indice]['ingrediente'].'</a></td>';
        //print_r($v_ingre);
      
      }

      echo '<table class="listaEdicao" width="1000">';
      echo '<thead><tr>';

      echo '<td width="325">&nbsp;</td>'; 
      foreach($arr_tamanhos_nomes as $i_tamanho => $v_tamanho)
      {

        echo '<td align="center" width="225" colspan="4">'.$v_tamanho['tamanho'].'</td>';
      }
      echo "</tr><tr>";
      echo '<td width="325" align="center" colspan="2">Sabor</td>'; 
      $i = 1;
      foreach($arr_tamanhos_nomes as $i_tamanho => $v_tamanho)
      {
        if($i>1) echo '<td align="center" width="50">&nbsp;</td>';
        echo '<td align="center" width="225">Custo dos Ingredientes</td>';
        echo '<td align="center" width="225">Preço de Venda</td>';
        echo '<td align="center" width="225">CMV</td>';
        $i++;
      }
      
      echo '</tr></thead>';
      echo '<tbody>';

      foreach ($arr_valores['pizzas'] as $cod_pizzas => $cod_ingredientes) 
      {
        echo "<tr>";
        echo '<td align="center" colspan="2"><a target="_blank" href="ipi_rel_cmv_pizza.php?cp='.$cod_pizzarias.'&p='.$cod_pizzas.'">'.$arr_nome_pizzas[$cod_pizzas]."</a></td>";
        
        
       

        

        /*echo "<<br/><br/><pre>";
        print_r($cod_ingredientes);
        echo "</pre><br/><Br/>";*/
        foreach ($cod_ingredientes as $c_ingrediente => $tamanhos) 
        {
          //echo '<tr>';
          //echo '<td width="325">'.$arr_valores["ingrediente"][$c_ingrediente]['ingrediente'].'</td>';  ($arr_tamanhos[$cod_pizzas] as $i_tamanho => $v_tamanho)

/*          foreach($arr_tamanhos_nomes as $i_tamanhos => $v_tamanho)
          {
            
            $v_tamanho['gasto_do_ingrediente'] = ($arr_valores["ingrediente"][$c_ingrediente]['preco_grama'] * $v_tamanho['quant_estoque']);
            //$arr_preco[$cod_pizzas][$i_tamanhos] += $v_tamanho['gasto_do_ingrediente'];
           // echo '<td align="center">R$ '.($v_tamanho['gasto_do_ingrediente'] ? bd2moeda($v_tamanho['gasto_do_ingrediente']) : '0,00').'</td>';
           /* echo "<<br/><br/><pre>";
        print_r($v_tamanho);
        echo "</pre><br/><Br/>";*/
          /*}
*/
       /* foreach ($cod_ingredientes as $c_ingrediente => $tamanhos) 
        {
          *///echo '<tr>';
          //echo '<td width="325">'.$arr_valores["ingrediente"][$c_ingrediente]['ingrediente'].'</td>'; 

          foreach($tamanhos['tamanhos'] as $i_tamanhos => $v_tamanho)
          {
            
            $v_tamanho['gasto_do_ingrediente'] = ($arr_valores["ingrediente"][$c_ingrediente]['preco_grama'] * $v_tamanho['quant_estoque']);
            $arr_preco[$cod_pizzas][$i_tamanhos] += $v_tamanho['gasto_do_ingrediente'];
           // echo '<td align="center">R$ '.($v_tamanho['gasto_do_ingrediente'] ? bd2moeda($v_tamanho['gasto_do_ingrediente']) : '0,00').'</td>';
/*            if($c_ingrediente==23)
            {  
                        echo "<<br/><br/><pre>";
                    print_r($v_tamanho);
                    echo "</pre><br/><Br/>";}*/
          }
         // echo '</tr>';
        }
         // echo '</tr>';
       /* }*/

        //echo "<tr><td>";
        //echo '<table class="listaEdicao" width="1000">';

//        echo '<td style="background-color: #E5E5E5;" width="325"><strong>Custo dos Ingredientes</strong></td>';        
        //foreach($arr_preco[$cod_pizzas] as $i_preco => $v_preco)
        //{
        //  echo '<td align="center">Custo ing R$ '.($v_preco > 0 ? bd2moeda($v_preco) : '0,00').'</td>';
        //}
        //echo '</tr>';


        //echo '<tr>';
        //echo '<td style="background-color: #E5E5E5;" width="325"><strong>Preço de venda</strong></td>'; 
        $i = 1;
        foreach($arr_tamanhos_nomes as $i_tamanho => $v_tamanho)
        {        
          $v_tamanho = $arr_tamanhos[$cod_pizzas][$i_tamanho];
          $custo_total = $arr_preco[$cod_pizzas][$i_tamanho];  
          if($i>1) echo '<td align="center" width="50">&nbsp;</td>';
          echo '<td align="center">R$ '.($custo_total > 0 ? bd2moeda($custo_total) : '0,00').'</td>';

          echo '<td align="center" width="225">R$ '.($v_tamanho['preco'] >= 0 ? bd2moeda($v_tamanho['preco']) : '0,00').'</td>';
          
          $faturamento = $v_tamanho['preco'];
          if($faturamento=="" || $faturamento==0) $faturamento = 1;
          $cpv_porc = ($custo_total/$faturamento)*100;
          echo '<td align="center" width="225">'.($cpv_porc >= 0 ? bd2moeda($cpv_porc) : "0,00").'%</td>';
          $i++;
        }
        //echo '</tr>';


        //echo '<tr>';
        //echo '<td style="background-color: #E5E5E5;" width="325"><strong>CMV</strong></td>'; 
        //foreach($arr_tamanhos[$cod_pizzas] as $i_tamanho => $v_tamanho)
        //{              
          
       // }
        echo '</tr>';//<tr><td colspan="4">&nbsp;</td></tr>

        //echo '</table>'; 
        //echo "</td></tr>";
      }
      echo '</tbody>';

      echo "</table>";
      echo '<Br/>Obs.: Devido a precisão da ficha técnica (em gramas), os números acima foram arredondados.';

     // echo '<br />';

      echo '<br />';


      

      /*echo '<pre>';*/
      // echo 'arr_valores <br/>';
      // print_r($arr_valores);
      // echo 'arr_preco: <br/>';
      // print_r($arr_preco);
/*      echo 'arr_tamanhos <br/>';
      print_r($arr_tamanhos);
      echo '</pre>';*/
      desconectar_bd($con);
    }

    else
      echo 'Selecione a '.TIPO_EMPRESA.' !';
  ?>
  </div>
</div>

<? rodape(); ?>