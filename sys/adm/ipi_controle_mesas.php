<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once 'ipi_controle_mesas_classe.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="ISO-8859-1">
<title><?php echo NOME_SITE . " - Controle de Mesas"; ?></title>

<link type="text/css" rel="stylesheet" href="../lib/css/controle_mesas.css">
<script type="text/javascript" src="../lib/js/mascara.js"></script>

<link href="../lib/js/jquery/jquery-ui.css" rel="stylesheet">
<script src="../lib/js/jquery/external/jquery/jquery.js"></script>
<script src="../lib/js/jquery/jquery-ui.js"></script>

<script type="text/javascript" src="../lib/js/shortcut.js"></script>

<script type="text/javascript" src="../lib/js/controle_mesas.js"></script>

</head>
<body>
<?php
//TODO: Pensar como definir qual pizzaria está em operação nas mesas
$cod_pizzarias_usuario = $_SESSION["usuario"]["cod_pizzarias"][0];

$conexao = conectar_bd();


//O input hidden abaixo usado, para depois que adicionar uma bebida ou produto devolver a guia ativa, caso mude para ajax pode remover
?>
<input type="hidden" name="cod_pizzarias_usuario" id="cod_pizzarias_usuario" value="<?php echo $cod_pizzarias_usuario; ?>" />
<input type="hidden" name="tp" id="tp" value="<?php echo (isset($_GET["tp"]))?$_GET["tp"]:''; ?>" />
<input type="hidden" name="erro" id="erro" value="<?php echo (isset($_GET["e"]))?$_GET["e"]:''; ?>" />

<style type="text/css">
<?php  
$sql_aux = "SELECT * FROM ipi_mesas_situacoes ms";
$res_aux = mysql_query($sql_aux);
while($obj_aux = mysql_fetch_object($res_aux))
{
  echo '.'.$obj_aux->situacao.'{ background-color: #'.$obj_aux->cor_situacao.'}'."\n";
}
?>
</style>

<script>
  <?php
  /* montar array js que indexa os codigos dos produtos */
  $sql_buscar_cods = "SELECT codigo_cliente_pizza,cod_pizzas FROM ipi_pizzas";
  $res_buscar_cods = mysql_query($sql_buscar_cods);
  $str_cods = '';
  while($obj_buscar_cods = mysql_fetch_object($res_buscar_cods))
  {
    ($str_cods ? $str_cods .=',' : '');
    $str_cods .= "'$obj_buscar_cods->codigo_cliente_pizza':$obj_buscar_cods->cod_pizzas";
  }
  echo "var arr_cods_produtos = {".$str_cods."};";

  /* montar array js que indexa os codigos das bebidas */
  $sql_buscar_cods = "SELECT codigo_cliente_bebida, cod_bebidas_ipi_conteudos FROM ipi_bebidas_ipi_conteudos";
  $res_buscar_cods = mysql_query($sql_buscar_cods);
  $str_cods = '';
  while($obj_buscar_cods = mysql_fetch_object($res_buscar_cods))
  {
    ($str_cods ? $str_cods .=',' : '');
    $str_cods .= "'$obj_buscar_cods->codigo_cliente_bebida':$obj_buscar_cods->cod_bebidas_ipi_conteudos";
  }
  echo "var arr_cods_bebidas = {".$str_cods."};";
  ?>
</script>


<div id="principal_container">

  <div id="principal_esquerda">
    <div id="esquerda_cabecalho">
      <div id="cabecalho_logo">
        <img src="../../img/logo_sushimax.png" />
      </div>
      <div id="cabecalho_texto">
        Controle de Mesas
      </div>
      <div class="clear"></div>
    </div>
    <div id="esquerda_conteudo">


      <div id="tabs">
        <ul>
          <li><a href="#aba_cliente">Cliente</a></li>
          <li><a href="#aba_produtos"><? echo ucfirst(TIPO_PRODUTOS) ?></a></li>
          <!-- <li><a href="javascript:;">3 - Combo</a></li> -->
          <li><a href="#aba_bebidas">Bebida</a></li>
          <li><a href="#aba_taxas">Taxas</a></li>
          <li><a href="#aba_revisar">Revisar</a></li>
          <li><a href="#aba_fechar">Fechar</a></li>
        </ul>

        <!-- Tab Mesas -->
        <div id="aba_cliente">
          <div id="area_busca">
            Digite o código da Mesa: <input type="text" id="buscar_codigo_mesa" size="8" value="" /> <input type="button" id="" value="Buscar" onclick="javascript:buscar_mesa($('#buscar_codigo_mesa').val());" />
          </div>

          <div id="area_botoes">
            <div id="area_botoes_texto">Opções disponíveis para mesa:</div>

            <input type="button" class="bt_opcoes_habilitado" id="bt_adicionar_comidas" value="+ <? echo ucfirst(TIPO_PRODUTOS) ?>" onclick="javascript:$('#tabs').tabs('option', 'active', 1);" />
            <input type="button" class="bt_opcoes_habilitado" id="bt_adicionar_bebidas" value="+ Bebidas" onclick="javascript:$('#tabs').tabs('option', 'active', 2);" />
            <input type="button" class="bt_opcoes_habilitado" id="bt_abrir_mesa" value="Abrir" onclick="javascript:abrir_mesa();" />
            <input type="button" class="bt_opcoes_habilitado" id="bt_fechar_mesa" value="Fechar" onclick="javascript:guia_fechar_mesa();" />
<!-- 
            <input type="button" class="bt_opcoes_habilitado" id="bt_juntar_mesa" value="Juntar" onclick="javascript:atualizar_mesas();" />
            <input type="button" class="bt_opcoes_habilitado" id="bt_tranferir_itens" value="Transferir" onclick="javascript:atualizar_mesas();" />
            <input type="button" class="bt_opcoes_habilitado" id="bt_atualizar_mesas" value="Atualizar" onclick="javascript:atualizar_mesas();atualizar_comanda();" />
 -->
            <input type="button" class="bt_opcoes_habilitado" id="bt_conta_parcial" value="Parcial" onclick="javascript:imprimir_conta_parcial();" />
            <input type="button" class="bt_opcoes_habilitado" id="bt_inicio" value="F8 - Início" onclick="javascript:limpar_sessao();" />
          </div>

          <div id="area_mesas">
          </div>
        </div>

        <div id="aba_produtos">




        <form method="post" name="frm_pedido_pizzas" action="ipi_controle_mesas_acoes.php">

        <table border="0" cellspacing="0" cellpadding="2" class="tabela" align="center" style="margin: 0px auto">
        <tr>
          <td style="border-bottom: 1px dashed #CCCCCC; border-top: 1px dashed #CCCCCC">
            <?php
            if ( ( ($produto_combo==1) && ($_SESSION['ipi_mesas']['combo']['produtos'][$indice_produto_atual_combo]['tipo']!='PIZZA') )|| ($produto_combo==0))
            {
            ?>

              <table border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="80">
                    Qtde:
                  </td>
                  <td width="150">
                    Tipo de Produto:
                  </td>
                </tr>

                <tr>
                  <td>
                    <input align="top" type="text" name="quantidade_adicionar" id="quantidade_adicionar" maxlength="4" style="width: 60px;" value='1' onkeypress="return ApenasNumero(event);" class="proximo">
                  </td>
                  <td>
                    <select name="cod_tipo_pizza" id="cod_tipo_pizza" size="1" onchange="document.getElementById('cod_tamanhos').selectedIndex = -1;document.getElementById('cod_pizzas_1').innerHTML = '';definir_sabores();">
                      <?php
                      $sql_tipo_pizza = "SELECT * FROM ipi_tipo_pizza WHERE situacao='ATIVO' ORDER BY tipo_pizza";
                      $res_tipo_pizza = mysql_query($sql_tipo_pizza);
                      echo '<option value="">Todos</option>';
                      while($obj_tipo_pizza = mysql_fetch_object($res_tipo_pizza))
                      {
                        echo '<option value="' . $obj_tipo_pizza->cod_tipo_pizza . '">' . bd2texto($obj_tipo_pizza->tipo_pizza) . '</option>';
                      }
                      ?>
                    </select>
                  </td>
                </tr>
              </table>

              <?php
            }
            ?>
          </td>
        </tr>


        <tr>
          <td>
            <!-- 
            <div style="float: left; width: 180px">
            <label>Tamanho do <? echo ucfirst(TIPO_PRODUTO) ?>:</label> 
            <input align="top" type="text" name="cod_tamanhos_digito" id="cod_tamanhos_digito" maxlength="4"  style="width: 50px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_tamanhos);" class="proximo"> 
            </div>
            -->


            <!--
            <br />
            <div style="clear:both"></div>
            <select name="cod_tamanhos"  class="proximo" id="cod_tamanhos" size="4" onchange="javascript:document.getElementById('cod_tamanhos_digito').value=this.value;"
            style="width: 230px;" onblur="javascript:{tamanho_pizza(this);definir_sabores()}" class="proximo">
            <?

            if ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']=="PIZZA")
            {
            $sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos t WHERE t.cod_tamanhos = '" . $_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['cod_tamanhos'] . "' ORDER BY t.tamanho";
            }
            else 
            {
            $sql_buscar_tamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
            }
            $res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);

            while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
            {
            echo '<option value="' . $obj_buscar_tamanhos->cod_tamanhos . '">' . bd2texto($obj_buscar_tamanhos->cod_tamanhos . ' - ' . $obj_buscar_tamanhos->tamanho) . '</option>';
            }
            ?>
            </select>
            -->


            <!--Tamanhos -->
            <input type="hidden" value="1" name="cod_tamanhos" id="cod_tamanhos">
            <input type="hidden" value="1" name="cod_tamanhos_digito" id="cod_tamanhos_digito">

            <!--num sabores -->
            <input type="hidden" name="num_sabores_digito" id="num_sabores_digito" class="proximo" value="1">
            <input type="hidden" value="1" name="num_sabores" id="num_sabores">

            <!-- tipom massa -->
            <input type="hidden" name="cod_tipo_massas_digito" id="cod_tipo_massas_digito" value="1">
            <input type="hidden" value="1" name="cod_tipo_massa" id="cod_tipo_massa">

            <!-- corte -->
            <input type="hidden" value="1" name="cod_opcoes_corte_digito" id="cod_opcoes_corte_digito">
            <input type="hidden" value="1" name="cod_opcoes_corte" id="cod_opcoes_corte" >

            <!-- borda -->
            <input type="hidden" name="cod_bordas_digito" id="cod_bordas_digito" class="proximo" style="width: 50px;" value="0">
            <input type="hidden" name="cod_bordas" id="cod_bordas" size="5" style="width: 230px;" value="0"> 
            <input type="hidden" name="borda_promocial" id="borda_promocial" value='0'/>    

            <!-- adicionaiis -->
            <input type="hidden" name="cod_adicionais_digito" id="cod_adicionais_digito" value="0">
            <input type="hidden" value="0" name="cod_adicionais" id="cod_adicionais" size="5" style="width: 230px;">

          </td>
        </tr>
        <tr>
          <td>
            <label>Sabor:</label><br />
            <input type="text" name="cod_pizzas_digito_1" id="cod_pizzas_digito_1" class="proximo" style="width: 60px;" onblur="javascript:selecionar_codigo(this, frm_pedido_pizzas.cod_pizzas_1);" class="proximo"> 
            <br>

            <select name="cod_pizzas_1" id="cod_pizzas_1" size="15" style="width: 350px;" onchange="javascript:alterar_sabor_input(this.value);" onblur="javascript:carregar_ingredientes('1');" class="proximo">
            </select>

            <input type="hidden" name="num_fracao[]" value="1">
            
            <br/><br/>Observações:<br/>
            <textarea name="observacao_1" id="observacao_1" rows="2" cols="40" class="proximo" ></textarea>

          </td>
        </tr>


        <tr>
          <td>
            <?
            if ( ( ($produto_combo==1) && ($_SESSION['ipi_caixa']['combo']['produtos'][$indice_produto_atual_combo]['tipo']!='PIZZA') )|| ($produto_combo==0))
            {
              ?>
              <div id="fundo_pizza_promocional" style="height: 30px;">
              <input type="checkbox" name="pizza_promocional" id="pizza_promocional" value="1" onfocus="javascript:document.getElementById('fundo_pizza_promocional').style.backgroundColor='#F7F413';" onBlur="javascript:document.getElementById('fundo_pizza_promocional').style.backgroundColor='#FFFFFF';" onclick="javascript:if(this.checked){document.getElementById('cod_motivo_promocoes_pizza').style.display='block';}else{document.getElementById('cod_motivo_promocoes_pizza').style.display='none';}" class="proximo"><b>Promocional</b>
              </div>

              <div style="float: left">
                <select name="cod_motivo_promocoes_pizza" id="cod_motivo_promocoes_pizza" size="1" style="display:none;">
                <?
                $sql_promocoes = "SELECT * FROM ipi_motivo_promocoes WHERE situacao='ATIVO' ORDER BY motivo_promocao";
                $res_promocoes = mysql_query($sql_promocoes);
                echo '<option value=""></option>';
                while($obj_promocoes = mysql_fetch_object($res_promocoes))
                {
                  echo '<option value="' . $obj_promocoes->cod_motivo_promocoes . '">' . bd2texto($obj_promocoes->motivo_promocao) . '</option>';
                }
                ?>
                </select>
              </div>
              <?
            }
            ?>
          </td>
        </tr>

        </table>

        <table border="0" align="center" width="500" cellpadding="0" cellspacing="0" style="margin: 0px auto">
          <tr>
            <td id="sabor_1">
              <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">  
                <!--
                <tr>
                  <td style="height: 30px;" colspan="4"></td>
                </tr>
                -->
                <tr>
                  <td colspan="4" id="ingredientes_1"></td>
                </tr>
                
                <tr>
                  <td colspan="4" id="adicionais_1"></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <?php
        $sql_colaboradores = "SELECT c.cod_colaboradores, c.nome FROM ipi_colaboradores c WHERE c.cod_tipo_colaboradores = '1' AND c.situacao = 'ATIVO' ORDER BY c.nome";
        $res_colaboradores = mysql_query($sql_colaboradores);
        echo '<div style="text-align: center; margin-top:10px;">';
        echo 'Colaborador: <select name="cod_colaboradores" size="1">';
        echo ('<option value=""></option>');
        while ($obj_colaboradores = mysql_fetch_object($res_colaboradores))
        {
          echo ('<option value="'.$obj_colaboradores->cod_colaboradores.'">'.$obj_colaboradores->nome.'</option>');
        }
        echo '</select>';
        echo "</div>";
        ?>

        <br /><table border="0" align="center" width="500" cellpadding="0" cellspacing="0" style="margin: 0px auto">
          <tr>
            <td style="text-align: center">
              <input type="button" name="br_fechar_pedido" onclick="javascript:if(validacao_pizza(document.frm_pedido_pizzas)){document.frm_pedido_pizzas.submit();}" value="Adicionar ao Pedido" class="proximo">
            </td>
          </tr>
        </table>



        <?
        if ($produto_combo==1)
        {
          ?>
          <input type="hidden" name="acao" value="adicionar_pizza_combo">
          <input type="hidden" name="id_combo" value="<? echo $_SESSION['ipi_caixa']['combo']['id_combo']; ?>">
          <input type="hidden" name="indice_atual_combo" value="<? echo $indice_produto_atual_combo; ?>">
          <?
        }
        else if ($produto_combo==0)
        {
          ?>
          <input type="hidden" name="acao" value="adicionar_pizza">
          <?
        }
        ?>
        </form>



        </div>

        <!-- 
        <div class="painelTab">
          Combos
        </div>
         -->

        <div id="aba_bebidas">



        <form method="post" name="frm_pedido_bebidas" action="ipi_controle_mesas_acoes.php">

        <table border="0" cellspacing="0" cellpadding="2" class="tabela" align="center" style="margin: 0px auto">
        <tr>
          <td style="border-bottom: 1px dashed #CCCCCC; border-top: 1px dashed #CCCCCC">


            <table border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td width="80">
                  Qtde:
                </td>
                <td width="150">
                  Tipo de Bebida:
                </td>
              </tr>

              <tr>
                <td>
                  <input align="top" type="text" name="quantidade_adicionar_bebida" id="quantidade_adicionar_bebida" maxlength="4" style="width: 60px;" value='1' onkeypress="return ApenasNumero(event);" class="proximo">
                </td>
                <td>
                  <select name="cod_tipo_bebida" id="cod_tipo_bebida" size="1" onchange="javascript:carregarCombo('cod_bebidas_conteudos')">
                    <?php
                    $sql_tipo_bebida = "SELECT * FROM ipi_tipo_bebida WHERE situacao='ATIVO' ORDER BY tipo_bebida";
                    $res_tipo_bebida = mysql_query($sql_tipo_bebida);
                    echo '<option value="">Todos</option>';
                    while($obj_tipo_bebida = mysql_fetch_object($res_tipo_bebida))
                    {
                      echo '<option value="' . $obj_tipo_bebida->cod_tipo_bebida . '">' . bd2texto($obj_tipo_bebida->tipo_bebida) . '</option>';
                    }
                    ?>
                  </select>
                </td>
              </tr>
            </table>

          </td>
        </tr>

        <tr>
          <td>

            <label>Bebidas:</label><br />
            <input type="text" name="cod_bebidas_digito_1" id="cod_bebidas_digito_1" class="proximo" style="width: 60px;" onblur="javascript:selecionar_codigo_bebidas(this, frm_pedido_bebidas.cod_bebidas_conteudos);" class="proximo"> 
            <br>
            <select name="cod_bebidas_conteudos" id="cod_bebidas_conteudos" size="15" style="width: 350px;" class="proximo">
            </select>
            
          </td>
        </tr>


        <tr>
          <td style="text-align: center">

          <div id="fundo_bebida_promocional" style="text-align:center">
          <input type="checkbox" name="bebida_promocional" id="bebida_promocional" value="1" onfocus="javascript:document.getElementById('fundo_bebida_promocional').style.backgroundColor='#F7F413';" onBlur="javascript:document.getElementById('fundo_bebida_promocional').style.backgroundColor='#FFFFFF';" onclick="javascript:if(this.checked){document.getElementById('cod_motivo_promocoes_bebida').style.display='block';}else{document.getElementById('cod_motivo_promocoes_bebida').style.display='none';}"><b>Promocional</b>
          </div>

          <select name="cod_motivo_promocoes_bebida" id="cod_motivo_promocoes_bebida" size="1" style="width: 230px; display:none; margin: 0px auto">
            <?
            $sql_promocoes = "SELECT * FROM ipi_motivo_promocoes WHERE situacao='ATIVO' ORDER BY motivo_promocao";
            $res_promocoes = mysql_query($sql_promocoes);
            echo '<option value=""></option>';
            while($obj_promocoes = mysql_fetch_object($res_promocoes))
            {
              echo '<option value="' . $obj_promocoes->cod_motivo_promocoes . '">' . bd2texto($obj_promocoes->motivo_promocao) . '</option>';
            }
            ?>
          </select> 

          <?php
          $sql_colaboradores = "SELECT c.cod_colaboradores, c.nome FROM ipi_colaboradores c WHERE c.cod_tipo_colaboradores = '1' AND c.situacao = 'ATIVO' ORDER BY c.nome";
          $res_colaboradores = mysql_query($sql_colaboradores);
          echo '<div style="text-align: center; margin-top:10px;">';
          echo 'Colaborador: <select name="cod_colaboradores" size="1">';
          echo ('<option value=""></option>');
          while ($obj_colaboradores = mysql_fetch_object($res_colaboradores))
          {
            echo ('<option value="'.$obj_colaboradores->cod_colaboradores.'">'.$obj_colaboradores->nome.'</option>');
          }
          echo '</select>';
          echo "</div>";
          ?>


          <br /><input type="button" name="br_fechar_pedido" onclick="javascript:if(validacao_bebida(document.frm_pedido_bebidas)){document.frm_pedido_bebidas.submit();}" value="Adicionar ao Pedido">
          <input type="hidden" name="acao" value="adicionar_bebidas">


          </td>
        </tr>

      </table>
      
      </form>



      </div>


      <div id="aba_taxas">

      <form method="post" name="frm_pedido_taxas" action="ipi_controle_mesas_acoes.php">
        <table border="1" cellspacing="0" cellpadding="2" class="tabela" align="center" style="margin: 0px auto">
          <thead>
            <tr>
              <td width="60" align="center">Qtde</td>
              <td width="200" align="center">Taxa</td>
              <td width="60" align="center">Valor</td>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql_taxas = "SELECT mt.cod_mesas_taxas, mt.taxa, mt.tipo_taxa, mtp.valor FROM ipi_mesas_taxas_pizzarias mtp LEFT JOIN ipi_mesas_taxas mt ON (mtp.cod_mesas_taxas = mt.cod_mesas_taxas) WHERE mtp.cod_pizzarias = '".$cod_pizzarias_usuario."'";
            //echo $sql_taxas;
            $res_taxas = mysql_query($sql_taxas);
            $num_taxas = mysql_num_rows($res_taxas);
            for($a = 0; $a < $num_taxas; $a ++) 
            {
              $obj_taxas = mysql_fetch_object($res_taxas);
              echo '<tr>';
              
              echo '<td align="center">';
              echo '<input name="cod_mesas_taxas[]" type="hidden" value="'.$obj_taxas->cod_mesas_taxas.'">';
              echo '<input name="quantidades_taxas[]" id="quantidades_taxas'.$a.'" class="proxima_bebida" type="text" size="3" maxlength="2" value="0" onClick="javascript:this.select();" onkeypress="return ((
                    ApenasNumero(event))&&(proximo_campo(this, event, \'proxima_bebida\')))">';
              echo '</td>';
              
              echo '<td>';
              echo $obj_taxas->taxa ." (".(($obj_taxas->tipo_taxa=="Valor")?"R$":(($obj_taxas->tipo_taxa=="Percentual")?"%":"")).")";
              echo '</td>';
              
              echo '<td align="right">';
              echo bd2moeda($obj_taxas->valor);
              echo '</td>';
              
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>

        <?php
        $sql_colaboradores = "SELECT c.cod_colaboradores, c.nome FROM ipi_colaboradores c WHERE c.cod_tipo_colaboradores = '1' AND c.situacao = 'ATIVO' ORDER BY c.nome";
        $res_colaboradores = mysql_query($sql_colaboradores);
        echo '<div style="text-align: center; margin-top:10px;">';
        echo 'Colaborador: <select name="cod_colaboradores" size="1">';
        echo ('<option value=""></option>');
        while ($obj_colaboradores = mysql_fetch_object($res_colaboradores))
        {
          echo ('<option value="'.$obj_colaboradores->cod_colaboradores.'">'.$obj_colaboradores->nome.'</option>');
        }
        echo '</select>';
        echo "</div>";
        ?>

        <div style="text-align:center">
          <br />
          <input type="button" name="br_fechar_pedido" onclick="javascript:if(validacao_taxas(document.frm_pedido_taxas)){document.frm_pedido_taxas.submit();}" value="Adicionar ao Pedido">
          <input type="hidden" name="acao" value="adicionar_taxas">
        </div>

      </form>

      </div>


        <div id="aba_revisar">
          <div id="area_revisar_pedido">
            Carregando...
          </div>
        </div>


        <div id="aba_fechar">



        <form method="post" name="frm_fechar_pedido" action="ipi_controle_mesas.php">

          <table border="0">

<!--
          <tr>
            <td align="right"> <b><? echo ucfirst(TIPO_EMPRESA) ?>:</b> </td>
            <td> 
              <select name="cod_pizzarias" id="cod_pizzarias" onchange='carregar_formas_pg(this)'style="width: 200px;">
              <option value="">Selecione a <? echo ucfirst(TIPO_EMPRESA) ?></option>
              <?
              $SqlBuscaPizzarias = "SELECT nome,cod_pizzarias FROM ipi_pizzarias ORDER BY nome";
              $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
              while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
              {
              echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
              if($cod_pizzarias_usuario == $objBuscaPizzarias->cod_pizzarias)
                  echo 'selected="selected"';
              echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
              }
              ?>
              </select>
            </td>
          </tr>


          <tr>
            <td align="right"> <b>Tipo de cliente:</b> </td>
            <td> 
            <select name="cmb_tipo_cliente" id="cmb_tipo_cliente" style="width: 200px;" onChange="alterar_tipo_cliente(this)">
            <option value="PF">Pessoa Física</option>
            <option value="PJ">Pessoa Jurídica</option>
            </select>        
            </td>
          </tr>


          <tr>
            <td align="right" width="150"> <b> <input type="text" value="CPF Nota:" name="lbl_cpf_nota_fiscal" id="lbl_cpf_nota_fiscal" style="border: 0px; text-align: right; font-weight: bold;" size="15" /></b> </td>
            <td width="350"> <input type="text" name="cpf_nota_fiscal" id="cpf_nota_fiscal" class="proximo" style="width: 120px;" onkeypress="return MascaraCPF(this, event);"> </td>
          </tr>

          <tr>
            <td align="right"> <b>Horário Agendamento:</b> </td>
            <td> <input type="text" name="horario_agendamento" id="horario_agendamento" class="proximo" style="width: 80px;" onkeypress="return MascaraHora(this, event);"> </td>
          </tr>

          <tr>
            <td align="right"> <b>Tipo de Entrega:</b> </td>
            <td>  
              <select name="tipo_entrega" id="tipo_entrega" style="width: 200px;" onblur="ajustar_frete_tipo_entrega(this.value)">
              <option value=""></option>
              <option value="Balcão">Balcão</option>
              <option value="Entrega">Entregar</option>
              </select>     
            </td>
          </tr>
-->
          <tr>
            <td align="right"> <b>Forma de pagamento:</b> </td>
            <td> 
              <select name="forma_pg" id="forma_pg" style="width: 200px;">
              <option value=""></option>
              <?
              $sql_formas_pg = "SELECT fp.cod_formas_pg,fp.forma_pg FROM ipi_formas_pg fp INNER JOIN ipi_formas_pg_pizzarias fpp on fpp.cod_formas_pg = fp.cod_formas_pg WHERE fpp.cod_pizzarias = '$cod_pizzarias_usuario' ORDER BY forma_pg";
              $res_formas_pg = mysql_query($sql_formas_pg);
              while($obj_formas_pg = mysql_fetch_object($res_formas_pg)) 
              {
                  echo '<option value="'.$obj_formas_pg->forma_pg.'" ';
                  if(validaVarPost('cod_formas_pg') == $obj_formas_pg->cod_formas_pg)
                      echo 'selected';
                  echo '>'.bd2texto($obj_formas_pg->forma_pg).'</option>';
              }
              ?>
              </select>
            </td>
          </tr>

          <tr>
          <td align="right"> <b>Num. Pessoas na Mesa:</b> </td>
          <td> <input type="text" name="numero_pessoas" id="numero_pessoas" class="proximo" style="width: 40px;" maxlength="2" onkeypress="return ApenasNumero(event)"> </td>
          </tr>

          <tr>
          <td align="right"> <b>Total do Pedido:</b> </td>
          <td> <input type="text" name="txt_total_pedido" id="txt_total_pedido" class="proximo" style="width: 80px;" readonly="readonly" > </td>
          </tr>

          <tr>
          <td align="right"> <b>Desconto:</b> </td>
          <td> 
          <input type="text" name="desconto" id="desconto" class="proximo" style="width: 80px;" value="0,00" onkeypress="return formataMoeda(this, '.', ',', event);" onblur="calcular_total_com_desconto()"> 
          </td>
          </tr>

          <tr>
          <td align="right"> <b>SubTotal:</b> </td>
          <td> 
          <input type="text" name="total_com_desconto" id="total_com_desconto" style="width: 80px;" readonly="readonly" > 
          </td>
          </tr>

          <tr>
          <td align="right"> <b>Frete:</b> </td>
          <td> 
          <input type="text" name="frete" id="frete" style="width: 80px;" onkeypress="return formataMoeda(this, '.', ',', event);" value="<? echo bd2moeda($_SESSION['ipi_caixa']['cliente']['preco_frete']); ?>" onblur="calcular_total_com_frete()"/> <br/><input type='hidden' name="comissao_frete" id="comissao_frete" style="width: 80px;" onkeypress="return formatar_moeda(this, '.', ',', event);" value="<? echo ( $_SESSION['ipi_caixa']['cliente']['valor_comissao_frete'] > 0 ? bd2moeda($_SESSION['ipi_caixa']['cliente']['valor_comissao_frete']) : '0,00' ); ?>"/>
          </td>
          </tr>

          <tr>
          <td align="right"> <b>Total:</b> </td>
          <td> 
          <input type="text" name="total_geral" id="total_geral" style="width: 80px;" readonly="readonly" > 
          </td>
          </tr>

          <tr>
          <td align="right"> <b>Troco para:</b> </td>
          <td> <input type="text" name="troco" id="troco" class="proximo" style="width: 80px;" onkeypress="return formataMoeda(this, '.', ',', event);"> </td>
          </tr>

          <tr>
          <td align="right"> 
          <b>Observações Gerais:</b>
          </td>
          <td> 
          <textarea rows="6" cols="40" id="obs_pedido" name="obs_pedido"></textarea>            
          </td>
          </tr>

          <tr>
          <td align="center" colspan="2"> 

          <input type="button" name="br_fechar_pedido" value="Fechar Pedido" onclick="javascript: if (validar_fechar_pedido(document.frm_fechar_pedido)) { fechar_mesa(); }">

          <input type="hidden" name="acao" value="fechar_pedido">
          <input type="hidden" name="cod_clientes_fechar" id="cod_clientes_fechar" value="">
          <input type="hidden" name="tipo_cliente_fechar" id="tipo_cliente_fechar" value="">

          </td>
          </tr>

          </table>


        </form>




        </div>

      </div>



    </div>
  </div>

  <div id="principal_direita">

    <div id="direita_cabecalho">
      RESUMO DA MESA
    </div>

    <div id="direita_imprimir" style="display:none">
      <input type="button" name="bt_ordenar_impressao" value="Imprimir Itens Pendentes" onclick="javascript:imprimir_produtos();">
    </div>

    <div id="direita_conteudo">
      
    </div>

  </div>

  <div class="clear"></div>
</div>


</body>
</html> 