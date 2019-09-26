<?
require_once '../lib/php/sessao.php';
require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once 'ipi_controle_mesas_classe.php';

$conexao = conectar_bd();
$controle_mesas = new ipi_controle_mesas();

$acao = validar_var_post("acao");
$cod_pizzarias_usuario = $_SESSION["usuario"]["cod_pizzarias"][0];
$nome_produto_venda = TIPO_PRODUTO;

switch ($acao)
{
  case "atualizar_mesas":
    echo utf8_encode('<h1>Situação das Mesas:</h1>');
    $sql_mesas = "SELECT m.cod_mesas, m.codigo_cliente_mesa, ms.situacao FROM ipi_mesas m LEFT JOIN ipi_mesas_situacoes ms ON (m.cod_mesas_situacoes = ms.cod_mesas_situacoes) WHERE m.cod_pizzarias=".$cod_pizzarias_usuario." ORDER BY codigo_cliente_mesa";
    $res_mesas = mysql_query($sql_mesas);
    //echo $sql_mesas;
    while($obj_mesas = mysql_fetch_object($res_mesas))
    {
      echo utf8_encode('<div class="mesa '.$obj_mesas->situacao.'" onClick="buscar_mesa(\''.$obj_mesas->codigo_cliente_mesa.'\')">'.$obj_mesas->codigo_cliente_mesa.'</div>');
    }
    echo '<div class="clear"></div>';
  break;

  case "buscar_mesa":
    $codigo_mesa = sprintf("%02d",validar_var_post("codigo_mesa") );
    $arr_resposta = array();
    $sql_mesas = "SELECT m.cod_mesas, m.codigo_cliente_mesa, ms.situacao FROM ipi_mesas m LEFT JOIN ipi_mesas_situacoes ms ON (m.cod_mesas_situacoes = ms.cod_mesas_situacoes) WHERE m.cod_pizzarias=".$cod_pizzarias_usuario." AND m.codigo_cliente_mesa = '".$codigo_mesa."' ORDER BY codigo_cliente_mesa LIMIT 1";
    //echo $sql_mesas;
    $res_mesas = mysql_query($sql_mesas);
    $num_mesas = mysql_num_rows($res_mesas);
    if ($num_mesas>0)
    {
      $obj_mesas = mysql_fetch_object($res_mesas);
      $arr_resposta["existe"] = "S";
      $arr_resposta["cod_mesas"] = $obj_mesas->cod_mesas;
    }
    else
    {
      $arr_resposta["existe"] = "N";
    }
    echo json_encode($arr_resposta);
  break;

  case "selecionar_mesa":
    $arr_resposta = array();
    $cod_mesas = validar_var_post("cod_mesas");

    $sql_mesas = "SELECT m.cod_mesas, m.codigo_cliente_mesa, ms.situacao FROM ipi_mesas m LEFT JOIN ipi_mesas_situacoes ms ON (m.cod_mesas_situacoes = ms.cod_mesas_situacoes) WHERE m.cod_mesas = '".$cod_mesas."' LIMIT 1";
    //echo $sql_mesas;
    $res_mesas = mysql_query($sql_mesas);
    $num_mesas = mysql_num_rows($res_mesas);
    if ($num_mesas>0)
    {
      $obj_mesas = mysql_fetch_object($res_mesas);
      $_SESSION['ipi_mesas']['cod_mesas'] = $cod_mesas;
      $_SESSION['ipi_mesas']['cod_pizzarias'] = $cod_pizzarias_usuario;
      $_SESSION['ipi_mesas']['situacao'] = $obj_mesas->situacao;
      $_SESSION['ipi_mesas']['codigo_cliente_mesa'] = $obj_mesas->codigo_cliente_mesa;
      $arr_resposta["ok"] = "S";
      $arr_resposta["situacao"] = $obj_mesas->situacao;
      $arr_resposta["codigo_cliente_mesa"] = $obj_mesas->codigo_cliente_mesa;
    }
    else
    {
      $arr_resposta["ok"] = "N";
    }

    echo json_encode($arr_resposta);
  break;

  case "existe_mesa":
    $arr_resposta = array();
    if ($controle_mesas->existe_mesa_selecionada())
    {
      $arr_resposta["existe"] = "S";
    }
    else
    {
      $arr_resposta["existe"] = "N";
    }
    echo json_encode($arr_resposta);
  break;

  case "atualizar_comanda":
    $controle_mesas->exibir_pedido();
  break;

  case "produtos_sem_impressao":
    $resp = $controle_mesas->existe_produto_sem_imprimir();
    $arr_resposta = array();
    if ($resp > 0)
    {
      $arr_resposta["ok"] = "S";
    }
    else
    {
      $arr_resposta["ok"] = "N";
    }
    echo json_encode($arr_resposta);
  break;

  case "imprimir_produtos":
    $resp = $controle_mesas->imprimir_produtos();
    $arr_resposta = array();
    if ($resp > 0)
    {
      $arr_resposta["ok"] = "S";
    }
    else
    {
      $arr_resposta["ok"] = "N";
    }
    echo json_encode($arr_resposta);
  break;

  case "limpar_sessao":
    $arr_resposta = array();

    $controle_mesas->limpar_mesa_sessao();

    $arr_resposta["ok"] = "S";
    echo json_encode($arr_resposta);
  break;  

  case "abrir_mesa":
    $arr_resposta = array();

    if ($controle_mesas->abrir_mesa() > 0)
    {
      $arr_resposta["ok"] = "S";
      $arr_resposta["codigo_cliente_mesa"] = $_SESSION['ipi_mesas']['codigo_cliente_mesa'];      
      $arr_resposta["situacao"] =$_SESSION['ipi_mesas']['situacao'];
    }
    else
    {
      $arr_resposta["ok"] = "N";
    }
    echo json_encode($arr_resposta);
  break;  

  case "fechar_mesa":
    $arr_resposta = array();

    $forma_pg = validar_var_post("forma_pg");
    $desconto = validar_var_post("desconto");
    $troco = validar_var_post("troco");
    $frete = validar_var_post("frete");
    $numero_pessoas = validar_var_post("numero_pessoas");
    $obs_pedido = validar_var_post("obs_pedido");

    if ($controle_mesas->fechar_mesa($forma_pg, $desconto, $troco, $frete, $numero_pessoas, $obs_pedido) > 0)
    {
      $arr_resposta["ok"] = "S";
    }
    else
    {
      $arr_resposta["ok"] = "N";
    }

    echo json_encode($arr_resposta);
  break;  

  case "calcular_total":
    $arr_resposta = array();
    $total_pedido = $controle_mesas->calcular_total();
    if ( $total_pedido > 0)
    {
      $arr_resposta["ok"] = "S";
      $arr_resposta["total"] = bd2moeda($total_pedido);
    }
    else
    {
      $arr_resposta["ok"] = "N";
    }
    
    echo json_encode($arr_resposta);
  break;  

  case "carregarCombo":

    $tamanho_pizza = validar_var_post('tamanho_pizza');
    $sabor = validar_var_post('sabor');
    $cod_tipo_pizza = validar_var_post('cod_tipo_pizza');
    $cod_tipo_bebida = validar_var_post('cod_tipo_bebida');

    if (validar_var_post('cod') == "cod_bordas")
    {
      echo utf8_encode("<option value='0'>0 - Não</option>");
      $sqlBordas = "SELECT * FROM ipi_tamanhos t INNER JOIN ipi_tamanhos_ipi_bordas tb ON (tb.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_bordas b ON (tb.cod_bordas=b.cod_bordas) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND tb.cod_pizzarias = '".$cod_pizzarias."' ORDER BY b.borda";
      $resBordas = mysql_query($sqlBordas);
      $linBordas = mysql_num_rows($resBordas);
      
      if ($linBordas > 0)
      {
        for ($a = 0; $a < $linBordas; $a++)
        {
          $objBordas = mysql_fetch_object($resBordas);
          $preco_borda = $objBordas->preco;
          
          if ($_SESSION['ipi_caixa']['combo']['qtde_bordas']>0)                    
          {    
            $preco_borda = "Combo";
          }
          
          echo utf8_encode('<option value="' . $objBordas->cod_bordas . '">' .$objBordas->cod_bordas." - ". $objBordas->borda . ' (' . $preco_borda . ')</option>');
        }
      }
    }
    
    if (validar_var_post('cod') == "num_sabores")
    {
      //$sqlFracoes = "SELECT * FROM ipi_tamanhos_ipi_fracoes_precos tf INNER JOIN ipi_fracoes_precos fp ON (tf.cod_fracoes_precos=fp.cod_fracoes_precos) WHERE tf.cod_tamanhos=" . $tamanho_pizza . " ORDER BY fp.fracao";
      $sqlFracoes = "SELECT * FROM ipi_tamanhos_ipi_fracoes tf INNER JOIN ipi_fracoes f ON (tf.cod_fracoes=f.cod_fracoes) WHERE tf.cod_tamanhos='" . $tamanho_pizza . "' AND tf.cod_pizzarias = '".$cod_pizzarias_usuario."' ORDER BY f.fracoes";

      $resFracoes = mysql_query($sqlFracoes);
      $linFracoes = mysql_num_rows($resFracoes);
      if ($linFracoes > 0)
      {
        for ($a = 0; $a < $linFracoes; $a++)
        {
          
          $objFracoes = mysql_fetch_object($resFracoes);
          
          echo utf8_encode('<option value="' . $objFracoes->fracoes . '">' . $objFracoes->fracoes);
          
          if ($objFracoes->preco != "0.00")
          {
            echo utf8_encode(' (+ R$' . bd2moeda($objFracoes->preco) . ')');
          }
          
          echo utf8_encode('</option>');
        }
      }
    }
    
    if (validar_var_post('cod') == "cod_tipo_massa")
    {
      $sql_tipo_massa = "SELECT * FROM ipi_tamanhos_ipi_tipo_massa tt INNER JOIN ipi_tipo_massa tm ON (tt.cod_tipo_massa=tm.cod_tipo_massa) WHERE tt.cod_tamanhos=" . $tamanho_pizza . " ORDER BY tm.tipo_massa";
      $res_tipo_massa = mysql_query($sql_tipo_massa);
      $num_tipo_massa = mysql_num_rows($res_tipo_massa);
      if ($num_tipo_massa > 0)
      {
        for ($a = 0; $a < $num_tipo_massa; $a++)
        {
          
          $obj_tipo_massa = mysql_fetch_object($res_tipo_massa);
          
          echo utf8_encode('<option value="' . $obj_tipo_massa->cod_tipo_massa  . '">' . $obj_tipo_massa->cod_tipo_massa . " - " . $obj_tipo_massa->tipo_massa);
          
          if ($obj_tipo_massa->preco != "0.00")
          {
            echo utf8_encode(' (+ R$' . bd2moeda($obj_tipo_massa->preco) . ')');
          }
          
          echo utf8_encode('</option>');
        }
      }
    }
    
    if (validar_var_post('cod') == "cod_opcoes_corte")
    {
      $sql_corte = "SELECT * FROM ipi_tamanhos_ipi_opcoes_corte toc INNER JOIN ipi_opcoes_corte oc ON (toc.cod_opcoes_corte=oc.cod_opcoes_corte) WHERE toc.cod_tamanhos=" . $tamanho_pizza . " ORDER BY oc.opcao_corte";
      $res_corte = mysql_query($sql_corte);
      $num_corte = mysql_num_rows($res_corte);
      if ($num_corte > 0)
      {
        for ($a = 0; $a < $num_corte; $a++)
        {
          
          $obj_corte = mysql_fetch_object($res_corte);
          
          echo utf8_encode('<option value="' . $obj_corte->cod_opcoes_corte  . '"');
          if ($obj_corte->tamanho_padrao==1)
            echo ' selected="selected"';
          echo utf8_encode('>' . $obj_corte->cod_opcoes_corte . " - " . $obj_corte->opcao_corte);
          
          if ($obj_corte->preco != "0.00")
          {
            echo utf8_encode(' (+ R$' . bd2moeda($obj_corte->preco) . ')');
          }
          
          echo utf8_encode('</option>');
        }
      }
    }
    
    if (validar_var_post('cod') == "cod_adicionais")
    {
      echo utf8_encode("<option value='0'>0 - Não</option>");
      $sqlBordas = "SELECT * FROM ipi_tamanhos t INNER JOIN ipi_tamanhos_ipi_adicionais ta ON (ta.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_adicionais a ON (ta.cod_adicionais=a.cod_adicionais) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND ta.cod_pizzarias = '".$cod_pizzarias_usuario."' ORDER BY a.adicional";
      $resBordas = mysql_query($sqlBordas);
      $linBordas = mysql_num_rows($resBordas);
      if ($linBordas > 0)
      {
        for ($a = 0; $a < $linBordas; $a++)
        {
          $objBordas = mysql_fetch_object($resBordas);
          echo utf8_encode('<option value="' . $objBordas->cod_adicionais . '">' . $objBordas->cod_adicionais ." - ". $objBordas->adicional . ' (' . bd2moeda($objBordas->preco) . ')</option>');
        }
      }
    }
    
    if (validar_var_post('cod') == "tipo_massa")
    {
      $sqlBordas = "SELECT * FROM ipi_tamanhos t INNER JOIN ipi_tamanhos_ipi_tipo_massa tm ON (tm.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_tipo_massa m ON (tm.cod_tipo_massa=m.cod_tipo_massa) WHERE t.cod_tamanhos=" . $tamanho_pizza . " ORDER BY m.tipo_massa";
      $resBordas = mysql_query($sqlBordas);
      $linBordas = mysql_num_rows($resBordas);
      
      if ($linBordas > 0)
      {
        for ($a = 0; $a < $linBordas; $a++)
        {
          $objBordas = mysql_fetch_object($resBordas);
          
          if($objBordas->preco > 0)
          {
            echo utf8_encode('<option value="' . $objBordas->cod_tipo_massa . '">' . $objBordas->tipo_massa . ' (' . bd2moeda($objBordas->preco) . ')</option>');    
          }
          else
          {
            echo utf8_encode('<option value="' . $objBordas->cod_tipo_massa . '">' . $objBordas->tipo_massa . '</option>');
          }
        }
      }
    }

    //echo "\n\n cod: ".validar_var_post('cod');
    //echo "\n\n sabor: ".$sabor;
    //echo "\n\n cod_tipo_pizza: ".$cod_tipo_pizza;

    if (((validar_var_post('cod') == "cod_pizzas_1") || (validar_var_post('cod') == "cod_pizzas_2") || (validar_var_post('cod') == "cod_pizzas_3") || (validar_var_post('cod') == "cod_pizzas_4")) )
    {
      if ( ($sabor) && ($cod_tipo_pizza) )
      {
        $sqlPizzas = "SELECT p.cod_pizzas,p.codigo_cliente_pizza,p.pizza,pt.preco FROM ipi_tamanhos t INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (pt.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE p.tipo='".$sabor."' AND p.cod_tipo_pizza='".$cod_tipo_pizza."' AND t.cod_tamanhos=" . $tamanho_pizza . " AND pt.cod_pizzarias = '".$cod_pizzarias_usuario."' ORDER BY p.codigo_cliente_pizza,p.pizza";
      }
      elseif ( (!$sabor) && ($cod_tipo_pizza) )
      {
        $sqlPizzas = "SELECT p.cod_pizzas,p.codigo_cliente_pizza,p.pizza,pt.preco FROM ipi_tamanhos t INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (pt.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE p.cod_tipo_pizza='".$cod_tipo_pizza."' AND t.cod_tamanhos=" . $tamanho_pizza . " AND pt.cod_pizzarias = '".$cod_pizzarias_usuario."' ORDER BY p.codigo_cliente_pizza,p.pizza";
      }
      elseif ( ($sabor) && (!$cod_tipo_pizza) )
      {
        $sqlPizzas = "SELECT p.cod_pizzas,p.codigo_cliente_pizza,p.pizza,pt.preco FROM ipi_tamanhos t INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (pt.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE p.tipo='".$sabor."' AND t.cod_tamanhos=" . $tamanho_pizza . " AND pt.cod_pizzarias = '".$cod_pizzarias_usuario."' ORDER BY p.codigo_cliente_pizza,p.pizza";
      }
      else
      {    
        $sqlPizzas = "SELECT p.cod_pizzas,p.codigo_cliente_pizza,p.pizza,pt.preco FROM ipi_tamanhos t INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (pt.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND pt.cod_pizzarias = '".$cod_pizzarias_usuario."' ORDER BY p.codigo_cliente_pizza,p.pizza";
      }
      //echo "\n\n sqlPizzas: ".$sqlPizzas;
      
      $resPizzas = mysql_query($sqlPizzas);
      $linPizzas = mysql_num_rows($resPizzas);
      
      if ($linPizzas > 0)
      {
        for ($a = 0; $a < $linPizzas; $a++)
        {
          $objPizzas = mysql_fetch_object($resPizzas);
          echo utf8_encode('<option value="' . $objPizzas->cod_pizzas . '">' . $objPizzas->codigo_cliente_pizza . " - " . $objPizzas->pizza . ' (' . bd2moeda($objPizzas->preco) . ')</option>');
        }
      }
    }
    
    if (validar_var_post('cod') == "cod_bebidas_conteudos")
    {

    //cod_tipo_bebida
      if ($cod_tipo_bebida)
      {
        $sqlBebidas = "SELECT bc.codigo_cliente_bebida, cp.cod_bebidas_ipi_conteudos, cp.preco, b.bebida, c.conteudo FROM ipi_conteudos_pizzarias cp LEFT JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) LEFT JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) LEFT JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas)  WHERE cp.cod_pizzarias = '".$cod_pizzarias_usuario."' AND b.cod_tipo_bebida='".$cod_tipo_bebida."' AND cp.situacao='ATIVO' ORDER BY bc.codigo_cliente_bebida, b.bebida, c.conteudo";
      }
      else
      {
        $sqlBebidas = "SELECT bc.codigo_cliente_bebida, cp.cod_bebidas_ipi_conteudos, cp.preco, b.bebida, c.conteudo FROM ipi_conteudos_pizzarias cp LEFT JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) LEFT JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) LEFT JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas)  WHERE cp.cod_pizzarias = '".$cod_pizzarias_usuario."' AND cp.situacao='ATIVO' ORDER BY bc.codigo_cliente_bebida, b.bebida, c.conteudo";
      }
      //echo $sqlBebidas;

      $resBebidas = mysql_query($sqlBebidas);
      $linBebidas = mysql_num_rows($resBebidas);
      
      if ($linBebidas > 0)
      {
        for ($a = 0; $a < $linBebidas; $a++)
        {
          $objBebidas = mysql_fetch_object($resBebidas);
          echo utf8_encode('<option value="' . $objBebidas->cod_bebidas_ipi_conteudos . '">' . $objBebidas->codigo_cliente_bebida . " - " . $objBebidas->bebida . " - " . $objBebidas->conteudo . ' (' . bd2moeda($objBebidas->preco) . ')</option>');    
        }
      }
    }    
      
  break;

case "carregar_ingredientes":
    if (validaVarPost('cod_pizzas'))
    {
        $cod_pizzas = validaVarPost('cod_pizzas');
        $cod_tamanhos = validaVarPost('cod_tamanhos');
        $num_fracao = validaVarPost('num_fracao');
        $num_sabores = validaVarPost('num_sabores');
        ?>
        
        <table border="0" width="500">
            <tr>
                <td><span class="laranja">Ingredientes <? echo $num_fracao; ?></span> 
                <input type="text" name="cod_ingredientes_<? echo $num_fracao; ?>_digito" id="cod_ingredientes_<? echo $num_fracao; ?>_digito" class="proximo" style="width: 50px;" onkeypress="javascript:selecionar_box(event, this, 'ingredientes<? echo $num_fracao; ?>[]');" TABINDEX='5'>
                <br>
                <?
                $sql_ingredientes = "SELECT i.cod_ingredientes,i.ingrediente,i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) WHERE ip.cod_pizzas='" . $cod_pizzas . "' AND i.ativo = 1 AND i.consumo = 0 ORDER BY ingrediente";
                $res_ingredientes = mysql_query($sql_ingredientes);
                $num_ingredientes = mysql_num_rows($res_ingredientes);
                if ($num_ingredientes > 0)
                {
                    echo "<table bgcolor='#EEEEEE' width='100%'>";
                    echo "<tr>";
                    for ($a = 0; $a < $num_ingredientes; $a++)
                    {
                        if (($a % 3 == 0) && ($a != 0))
                            echo "</tr><tr>";
                        $obj_ingredientes = mysql_fetch_object($res_ingredientes);
                        echo "<td><small>";
                        $id_troca = "ingredientes" . $num_fracao."_t".$a;
                        $id_normal = "ingredientes_" . $num_fracao."_".$a;
                        echo utf8_encode("<input type='checkbox' onclick='ccbox(this,\"".$id_troca."\")' id='ingredientes_".$num_fracao."_".$a."' name='ingredientes" . $num_fracao . "[]' tabindex='1' value='" . $obj_ingredientes->cod_ingredientes . "' checked='checked' style='border: 0; background: none;' />");
                        
                        
                        //echo "<a href='javascript:;' onMouseover=\"Mostrar('<div style=\'float: left; margin-right: 5px;\'><img src=\'img/ing_mucarela.jpg\'></div><br><strong>".utf8_encode($objIngre->ingrediente)."</strong><br><br>".utf8_encode('Descrição ou alguma dica sobre o ingrediente.')."<br><br>')\" onMouseout=\"Esconder()\">".utf8_encode($objIngre->ingrediente) . "</a><br />";
                        echo utf8_encode($obj_ingredientes->cod_ingredientes . " - " . $obj_ingredientes->ingrediente) . "<br />";
                        //echo "a".$obj_ingredientes->cod_ingredientes_troca;
                        if($obj_ingredientes->cod_ingredientes_troca)
                        {
                            $sql_troca = "SELECT it.preco_troca, itroca.ingrediente FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) INNER JOIN ipi_ingredientes itroca ON (i.cod_ingredientes_troca=itroca.cod_ingredientes) WHERE i.cod_ingredientes = ".$obj_ingredientes->cod_ingredientes." AND it.cod_tamanhos=" . $cod_tamanhos." AND it.cod_pizzarias = ".$cod_pizzarias_usuario;
                          //echo $sql_troca;
                            $res_troca = mysql_query($sql_troca);
                            $obj_troca = mysql_fetch_object($res_troca);

                            echo utf8_encode("<span><input type='checkbox' onclick='ccbox(this,\"".$id_normal."\")' id='ingredientes" . $num_fracao."_t".$a."' name='ingredientes_adicionais" . $num_fracao . "[]' value='TROCA###" . $obj_ingredientes->cod_ingredientes_troca . "###" . $obj_ingredientes->cod_ingredientes . "' style='border: 0; background: none;' />");
                            //echo "<br/><br/>-".$obj_troca->preco_troca."-<br/><br/>";
                            echo "Trocar por ".$obj_troca->ingrediente." (".bd2moeda(arredondar_preco_ingrediente($obj_troca->preco_troca, $num_sabores)).") </span>";
                        }
                        
                        echo "</small></td>";
                    }
                    echo "</tr>";
                    echo "</table>";
                }
                ?>
                <input type="button" name="bt_adicionais_<? echo $num_fracao; ?>" value="Adicionais" onclick="javascript:carregar_adicionais('<? echo $num_fracao; ?>')" TABINDEX='6'/>
                
               </td>
            </tr>
        </table>
        <br />
        <?
    }        
    break;
    case "carregar_adicionais":
        $cod_tamanhos = validar_var_post('cod_tamanhos');
        $num_fracao = validar_var_post('num_fracao');
        $num_sabores = validar_var_post('num_sabores');
        ?>
        
        <table border="0" width="500">
             <tr>
                <td colspan="4"><span class="laranja">Adicionais <? echo $num_fracao; ?></span>
                <input type="text" name="cod_adicionais_<? echo $num_fracao; ?>_digito" id="cod_adicionais_<? echo $num_fracao; ?>_digito"
                class="proximo" style="width: 50px;" onkeypress="javascript:selecionar_box(event, this, 'ingredientes_adicionais<? echo $num_fracao; ?>[]');" TABINDEX='7'>
                <br />
                <?
                $sql_adicionais = "SELECT i.cod_ingredientes,i.ingrediente_abreviado,it.preco FROM ipi_ingredientes_ipi_tamanhos it LEFT JOIN ipi_ingredientes i ON (i.cod_ingredientes=it.cod_ingredientes) WHERE i.adicional AND it.cod_tamanhos='" . $cod_tamanhos . "' AND it.cod_pizzarias = '".$cod_pizzarias_usuario."' AND i.ativo = 1 ORDER BY ingrediente";
                //echo $sql_ingredientes;
                $res_adicionais = mysql_query($sql_adicionais);
                $num_adicionais = mysql_num_rows($res_adicionais);
                if ($num_adicionais > 0)
                {
                    echo "<table cellspacing='5' cellpadding='0' border='0' width='100%'>";
                    echo "<tr><td valign='top' width='156'>";
                    $divisor = floor($num_adicionais / 3);
                    if (($num_adicionais % 3) != 0)
                        $divisor++;
                    for ($a = 0; $a < $num_adicionais; $a++)
                    {
                        if ((($a % $divisor) == 0) && ($a != 0))
                            echo "</td><td valign='top' width='156'>";
                        $obj_adicionais = mysql_fetch_object($res_adicionais);
                        echo "<small>";
                        echo utf8_encode("<input type='checkbox' name='ingredientes_adicionais" . $num_fracao . "[]' tabindex='1' value='" . $obj_adicionais->cod_ingredientes . "' style='border: 0; background: none;' />");
                        
                        echo utf8_encode( $obj_adicionais->cod_ingredientes . " - " . $obj_adicionais->ingrediente_abreviado . " <font style='font-size:9px; '>(" . bd2moeda($obj_adicionais->preco) . ")</font><br />");
                        //echo "<a href='javascript:;' onMouseover=\"Mostrar('<div style=\'float: left; margin-right: 5px;\'><img src=\'img/ing_mucarela.jpg\'></div><br><strong>".utf8_encode($objAdic->ingrediente_abreviado)."</strong><br><br>".utf8_encode('Descrição ou alguma dica sobre o ingrediente.')."<br><br>')\" onMouseout=\"Esconder()\">".utf8_encode($objAdic->ingrediente_abreviado) . " <font style='font-size:9px; '>(" . bd2moeda(arredondar_preco_ingrediente($objAdic->preco, $qtde_sabor)) . ")</font></a><br />";
                        echo "</small>";
                    }
                    echo "</td></tr>";
                    echo "</table>";
                }
                ?>
                </td>
            </tr>
        </table>
        <br />
        <?
    break;

    case "revisar_pedido":

      $cod_pedidos = $controle_mesas->localizar_pedido();
      $cod_pizzarias = validar_var_post("cod_pizzarias");
      echo '<div class="comanda_total_pedido">MESA: '.$_SESSION['ipi_mesas']['codigo_cliente_mesa'].'</div>';
/*
      echo "<pre>";
      print_r($_SESSION['ipi_mesas']);
      echo "</pre>";
*/
      $sql_pizzas = "SELECT cod_pedidos_pizzas, promocional, fidelidade, combo, cod_tamanhos, quant_fracao, situacao_pedidos_pizzas FROM ipi_pedidos_pizzas WHERE cod_pedidos = '".$cod_pedidos."'";
      $res_pizzas = mysql_query($sql_pizzas);
      $num_pizzas = mysql_num_rows($res_pizzas);

      echo utf8_encode('<form method="post" name="frm_revisar_pedido" id="frm_revisar_pedido" action="ipi_controle_mesas_acoes.php" style="display: inline;" >');
      if ($num_pizzas > 0)
      {

        //echo utf8_encode("<h2>PRODUTOS:</h2>");

        echo '<br /><table border="1" cellspacing="0" style="margin: 0px auto">';

        echo '<tr>';
        echo '<td width="40" align="center">';
        echo utf8_encode('<strong>Sel</strong>');
        echo '</td>';

        echo '<td width="400" align="center">';
        echo utf8_encode('<strong>'.mb_strtoupper(TIPO_PRODUTOS).'</strong>');
        echo '</td>';
        echo '</tr>';

        for ($a = 0; $a < $num_pizzas; $a++)
        {
          $obj_pizzas = mysql_fetch_object($res_pizzas);
          $sqlAux = "SELECT fp.preco,fp.cod_tamanhos FROM ipi_tamanhos_ipi_fracoes fp inner join ipi_fracoes f on f.cod_fracoes = fp.cod_fracoes WHERE fp.cod_tamanhos=".$obj_pizzas->cod_tamanhos." AND f.fracoes=".$obj_pizzas->quant_fracao;
          //echo "<br>1: ".$sqlAux;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);

          $cod_tamanhos = $objAux->cod_tamanhos;
          $preco_divisao_fracao = $objAux->preco;

          echo '<tr>';

          echo '<td align="center">';
          echo utf8_encode('<input type="checkbox" name="cod_pedidos_pizzas[]" value="'.$obj_pizzas->cod_pedidos_pizzas.'">');
          echo '</td>';

          echo '<td style="padding: 4px;">';

          //  echo utf8_encode(($a + 1) . 'ª '.ucfirst($nome_produto_venda));

          /*
          //Excluir temparariamente removido
          echo '<form method="post" action="ipi_req_carrinho_acoes.php" name="frmExcluirPizza_' . $a . '" style="margin: 0px">';
          echo '<input type="hidden" name="ind_ses" value="' . $a . '">';
          echo '<input type="hidden" name="acao" value="excluir_pizza">';
          echo '</form>';
          echo "<div style='text-align: right'><a href='javascript:confirmar_excluir_pizza(document.frmExcluirPizza_{$a},\"pizza\");'>Remover</a></div>";
          */


/*
          $sqlAux = "SELECT * FROM ipi_tamanhos WHERE cod_tamanhos=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'];
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          echo utf8_encode('<b>Tamanho:</b> ' . $objAux->tamanho);
*/

          //echo utf8_encode('<br><b>Quantidade de Sabores:</b> ' . $_SESSION['ipi_caixa']['pedido'][$a]['quantidade_fracoes']);

          /*if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] != "0")
          {
          $sqlAux = "SELECT * FROM ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'];
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          echo utf8_encode('<br><b>Borda:</b> ' . $objAux->borda);
          if ($_SESSION['ipi_caixa']['pedido'][$a]['borda_promocional'] == "1")
          echo utf8_encode(" (GRÁTIS)");
          if ($_SESSION['ipi_caixa']['pedido'][$a]['borda_fidelidade'] == "1")
          echo utf8_encode(" (FIDELIDADE)");
          if ($_SESSION['ipi_caixa']['pedido'][$a]['borda_combo'] == "1")
          echo utf8_encode(" (COMBO)");
          }
          else
          {
          echo utf8_encode('<br><b>Borda:</b> Não');
          }*/

          /*if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] != "0")
          {
          $sqlAux = "SELECT * FROM ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'];
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          echo utf8_encode('<br><b>Gergelim:</b> ' . $objAux->adicional);
          }
          else
          {
          echo utf8_encode('<br><b>Gergelim:</b> Não');
          }*/

          /*$sqlAux = "SELECT * FROM ipi_tipo_massa tm INNER JOIN ipi_tamanhos_ipi_tipo_massa tt ON (tm.cod_tipo_massa = tt.cod_tipo_massa) WHERE tm.cod_tipo_massa = ".$_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa']." AND tt.cod_tamanhos = ".$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'];
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);

          echo utf8_encode('<br><b>Tipo da Massa:</b> '.$objAux->tipo_massa);

          if($objAux->preco > 0)
          {
          echo utf8_encode('&nbsp;('.bd2moeda($objAux->preco).')');   
          }*/

          $sql_fracoes = "SELECT cod_pedidos_fracoes, cod_pizzas FROM ipi_pedidos_fracoes WHERE cod_pedidos = '".$cod_pedidos."' AND cod_pedidos_pizzas = '".$obj_pizzas->cod_pedidos_pizzas."'";
          //echo "X: ".$sql_fracoes;
          $res_fracoes = mysql_query($sql_fracoes);
          $num_fracoes = mysql_num_rows($res_fracoes);
          for ($b = 0; $b < $num_fracoes; $b++)
          {
            $obj_fracoes = mysql_fetch_object($res_fracoes);
            if($obj_fracoes->cod_pizzas > 0)
            {


              $sqlAux = "SELECT pt.preco FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $obj_fracoes->cod_pizzas . " AND pt.cod_tamanhos=" . $obj_pizzas->cod_tamanhos . " AND pt.cod_pizzarias = ".$cod_pizzarias;
              //echo "<br>$sqlAux";
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);
              $preco_fracao = ($objAux->preco / $num_fracoes);
              if (($obj_pizzas->promocional != "1") && ($obj_pizzas->fidelidade != "1") && ($obj_pizzas->combo != "1"))
              {
                $preco_fracao = ($objAux->preco / $num_fracoes);
              }
              else
              {
                $preco_fracao = 0;
                if ($obj_pizzas->combo == "1")
                {
                  /*
                  if (!in_array($_SESSION['ipi_caixa']['pedido'][$a]['id_combo'], $arr_combo)) 
                  {
                    $arr_combo[] = $_SESSION['ipi_caixa']['pedido'][$a]['id_combo'];
                    $sqlAux = "SELECT cp.preco FROM ipi_combos c INNER JOIN ipi_combos_pizzarias cp ON (cp.cod_combos = c.cod_combos) WHERE c.cod_combos='" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']."' AND c.situacao='ATIVO' AND cp.cod_pizzarias = '".$cod_pizzarias."'";
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);

                    $preco_fracao = $objAux->preco;
                  }
                  */
                }
              }

  
              $sqlAux = "SELECT codigo_cliente_pizza,pizza FROM ipi_pizzas WHERE cod_pizzas='".$obj_fracoes->cod_pizzas."'";
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);
              echo utf8_encode("<b>Produto:</b> ".$objAux->codigo_cliente_pizza." -> " . $objAux->pizza);//" . ($b + 1) . "


              echo utf8_encode(' - <span style="color:red">');
              if ($obj_pizzas->promocional == "1")
                echo utf8_encode(" GRÁTIS");
              else if ($obj_pizzas->fidelidade == "1")
                echo utf8_encode(" FIDELIDADE");
              else if ($obj_pizzas->combo == "1")
                echo utf8_encode(" COMBO");
              else if ($obj_pizzas->situacao_pedidos_pizzas == "CANCELADO")
                echo utf8_encode(" CANCELADO");
              else
                echo utf8_encode(' R$ '.bd2moeda($preco_fracao).'');
              echo utf8_encode('</span>');


              $sql_ingredientes = "SELECT cod_ingredientes, ingrediente_padrao FROM ipi_pedidos_ingredientes WHERE cod_pedidos = '".$cod_pedidos."' AND cod_pedidos_fracoes='".$obj_fracoes->cod_pedidos_fracoes."'";
              $res_ingredientes = mysql_query($sql_ingredientes);
              $num_ingredientes = mysql_num_rows($res_ingredientes);

              $ingredientes_padroes = array ();
              $ingredientes_nao_padroes = array ();
              $ind_aux_padrao = 0;
              $ind_aux_nao_padrao = 0;
              for ($c = 0; $c < $num_ingredientes; $c++)
              {

                $obj_ingredientes = mysql_fetch_object($res_ingredientes);

                if ($obj_ingredientes->ingrediente_padrao == true)
                {
                  $ingredientes_padroes[$ind_aux_padrao] = $obj_ingredientes->cod_ingredientes;
                  $ind_aux_padrao++;
                }
                else if ($obj_ingredientes->ingrediente_padrao == false)
                {
                  $ingredientes_nao_padroes[$ind_aux_nao_padrao] =  $obj_ingredientes->cod_ingredientes;
                  $ind_aux_nao_padrao++;
                }

              }

              if (count($ingredientes_padroes) > 0)
              {
                $sqlAux = "SELECT ingrediente FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (ip.cod_ingredientes=i.cod_ingredientes) WHERE ip.cod_pizzas='".$obj_fracoes->cod_pizzas."' AND ip.cod_ingredientes NOT IN (" . implode(",", $ingredientes_padroes) . ") AND i.consumo != 1";

                $resAux = mysql_query($sqlAux);
                $linAux = mysql_num_rows($resAux);
                //echo $sqlAux;
                if ($linAux > 0)
                {
                  echo utf8_encode("<br><b>Ingredientes:</b> ");
                  while ($objAux = mysql_fetch_object($resAux))
                  {
                    echo utf8_encode("SEM " . $objAux->ingrediente . ", ");
                  }
                }
              }

              if (count($ingredientes_nao_padroes) > 0)
              {
                $sqlAux = "SELECT ingrediente FROM ipi_ingredientes i WHERE i.cod_ingredientes IN (" . implode(",", $ingredientes_nao_padroes) . ")";
                $resAux = mysql_query($sqlAux);
                $linAux = mysql_num_rows($resAux);
                if ($linAux > 0)
                {
                  echo utf8_encode("<br><b>Adicionais:</b> ");
                  while ($objAux = mysql_fetch_object($resAux))
                  {
                    echo utf8_encode($objAux->ingrediente . ", ");
                  }
                }
              }
              /*
              else
              {
                echo utf8_encode("<br><b>Adicionais:</b> Sem adicionais");
              }
              */
            }
          }
          echo '</td>';
          echo '</tr>';
        }

        echo '</table>';
      }


      // ### Bebidas ###
      $sql_bebidas = "SELECT pb.cod_pedidos_bebidas,c.conteudo, b.bebida, pb.quantidade, pb.preco_inteiro, pb.promocional, pb.fidelidade, pb.combo, pb.situacao_pedidos_bebidas FROM ipi_pedidos_bebidas pb LEFT JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas=b.cod_bebidas) WHERE cod_pedidos = '".$cod_pedidos."'";
      $res_bebidas = mysql_query($sql_bebidas);
      $num_bebidas = mysql_num_rows($res_bebidas);
      if ($num_bebidas > 0)
      {

        //echo utf8_encode("<br><h2>BEBIDAS:</h2>");
        echo '<br /><table border="1" cellspacing="0" style="margin: 0px auto">';

        echo '<tr>';
        echo '<td width="40" align="center">';
        echo utf8_encode('<strong>Sel</strong>');
        echo '</td>';

        echo '<td width="400" align="center">';
        echo utf8_encode('<strong>BEBIDAS</strong>');
        echo '</td>';

        echo '</tr>';

        for ($a = 0; $a < $num_bebidas; $a++)
        {
          $obj_bebidas = mysql_fetch_object($res_bebidas);
          echo '<tr>';

          echo '<td align="center">';
          echo utf8_encode('<input type="checkbox" name="cod_pedidos_bebidas[]" value="'.$obj_bebidas->cod_pedidos_bebidas.'">');
          echo '</td>';

          echo '<td style="padding: 4px;">';
          echo utf8_encode( $obj_bebidas->quantidade .' - ' . $obj_bebidas->bebida . " - " . $obj_bebidas->conteudo . "");

          echo (" <span style='color:red'> - ");
          if ($obj_bebidas->promocional == "1")
            echo utf8_encode(" GRÁTIS");
          else if ($obj_bebidas->fidelidade == "1")
            echo utf8_encode(" FIDELIDADE");
          else if ($obj_bebidas->combo == "1")
            echo utf8_encode(" COMBO");
          else if ($obj_bebidas->situacao_pedidos_bebidas == "CANCELADO")
            echo utf8_encode(" CANCELADO");
          else
            echo utf8_encode( "R$ ".bd2moeda($obj_bebidas->preco_inteiro) );
          echo ("</span>");

          echo '</td>';

          echo '</tr>';
        }
        echo '</table>';

      }



      # TAXAS
      $sql_taxas = "SELECT pt.cod_pedidos_taxas, pt.quantidade, pt.preco_total, mt.taxa, mt.cod_mesas_taxas, pt.situacao_pedidos_taxas  FROM ipi_pedidos_taxas pt LEFT JOIN ipi_mesas_taxas mt ON (pt.cod_mesas_taxas = mt.cod_mesas_taxas) WHERE pt.cod_pedidos = '".$cod_pedidos."'";
      //echo $sql_taxas;
      $res_taxas = mysql_query($sql_taxas);
      $num_taxas = mysql_num_rows($res_taxas);
      if ($num_taxas>0) // SE EXISTE ALGUMA TAXA
      {
        $total_pedido = $controle_mesas->calcular_total_sem_taxas();
        //echo utf8_encode("<br><h2>BEBIDAS:</h2>");
        echo '<br /><table border="1" cellspacing="0" style="margin: 0px auto">';

        echo '<tr>';
        echo '<td width="40" align="center">';
        echo utf8_encode('<strong>Sel</strong>');
        echo '</td>';

        echo '<td width="400" align="center">';
        echo utf8_encode('<strong>TAXAS</strong>');
        echo '</td>';

        echo '</tr>';


        // PEGA TODAS TAXAS DIFERENTES DE SERVIÇO
        $sql_taxas = "SELECT pt.cod_pedidos_taxas, pt.quantidade, pt.preco_total, mt.taxa, mt.cod_mesas_taxas, pt.situacao_pedidos_taxas  FROM ipi_pedidos_taxas pt LEFT JOIN ipi_mesas_taxas mt ON (pt.cod_mesas_taxas = mt.cod_mesas_taxas) WHERE pt.cod_pedidos = '".$cod_pedidos."' AND pt.cod_mesas_taxas <> 1";
        //echo $sql_taxas;
        $res_taxas = mysql_query($sql_taxas);
        $num_taxas = mysql_num_rows($res_taxas);

        for ($a = 0; $a < $num_taxas; $a++)
        {
          $obj_taxas = mysql_fetch_object($res_taxas);
          echo '<tr>';

          echo '<td align="center">';
          echo utf8_encode('<input type="checkbox" name="cod_pedidos_taxas[]" value="'.$obj_taxas->cod_pedidos_taxas.'">');
          echo '</td>';

          echo '<td style="padding: 4px;">';
          if ($obj_taxas->situacao_pedidos_taxas != "CANCELADO")
          {
            echo utf8_encode($obj_taxas->quantidade.' - '.$obj_taxas->taxa.' - <span style="color:red">R$ '.bd2moeda($obj_taxas->preco_total).'</span><br />');
            $total_pedido += $obj_taxas->preco_total;
          }
          else
          {
            echo utf8_encode($obj_taxas->quantidade.' - '.$obj_taxas->taxa.' - <span style="color:red">CANCELADO</span><br />');
          }
          echo '</td>';

          echo '</tr>';
        }

        // PEGA TAXA DE SERVIÇO
        $sql_taxas = "SELECT pt.cod_pedidos_taxas, mtp.valor, pt.quantidade, pt.preco_total, mt.taxa, mt.cod_mesas_taxas, pt.situacao_pedidos_taxas  FROM ipi_pedidos_taxas pt LEFT JOIN ipi_mesas_taxas mt ON (pt.cod_mesas_taxas = mt.cod_mesas_taxas) LEFT JOIN ipi_mesas_taxas_pizzarias mtp ON (pt.cod_mesas_taxas = mtp.cod_mesas_taxas) WHERE pt.cod_pedidos = '".$cod_pedidos."' AND pt.cod_mesas_taxas = 1";
        //echo $sql_taxas;
        $res_taxas = mysql_query($sql_taxas);
        $num_taxas = mysql_num_rows($res_taxas);

        for ($a = 0; $a < $num_taxas; $a++)
        {
          $obj_taxas = mysql_fetch_object($res_taxas);
          echo '<tr>';

          echo '<td align="center">';
          echo utf8_encode('<input type="checkbox" name="cod_pedidos_taxas[]" value="'.$obj_taxas->cod_pedidos_taxas.'">');
          echo '</td>';

          echo '<td style="padding: 4px;">';
          if ($obj_taxas->situacao_pedidos_taxas != "CANCELADO")
          {
            $total_servico = (($total_pedido * $obj_taxas->valor)/100);
            echo utf8_encode($obj_taxas->quantidade.' - '.$obj_taxas->taxa.' - '.bd2moeda($obj_taxas->valor).'% - <span style="color:red">R$ '.bd2moeda($total_servico).'</span><br />');
          }
          else
          {
            echo utf8_encode($obj_taxas->quantidade.' - '.$obj_taxas->taxa.' - <span style="color:red">CANCELADO</span><br />');
          }
          echo '</td>';

          echo '</tr>';
        }


        echo '</table>';
      }
      echo '<input type="hidden" name="acao" id="acao_revisar" value="cancelar_produtos">';

      $sql_colaboradores = "SELECT c.cod_colaboradores, c.nome FROM ipi_colaboradores c WHERE c.cod_tipo_colaboradores = '1' AND c.situacao = 'ATIVO' ORDER BY c.nome";
      $res_colaboradores = mysql_query($sql_colaboradores);
  
      echo '<div style="text-align: left; margin-top:10px; width: 418px; margin: 0 auto; height:26px; ">';
        echo '<input type="checkbox" name="cb_transferir_itens" value="" onClick="javascript:conferir_transferir_itens(this);" />Transferir itens';

        echo '<span style="margin-left: 20px;" id="area_selecionar_mesa_transferir">';
        echo 'para a mesa: <select name="cod_pedidos_transferir" id="cod_pedidos_transferir" size="1">';
        echo utf8_encode('<option value=""></option>');


        $sql_mesas_abertas = "SELECT mp.cod_pedidos, m.codigo_cliente_mesa FROM ipi_mesas_pedidos mp LEFT JOIN ipi_mesas m ON (mp.cod_mesas = m.cod_mesas) WHERE mp.situacao_pedido_mesa = 'ABERTO' AND mp.cod_mesas <> '".$_SESSION['ipi_mesas']['cod_mesas']."' ORDER BY m.codigo_cliente_mesa";
        $res_mesas_abertas = mysql_query($sql_mesas_abertas);
        $num_mesas_abertas = mysql_num_rows($res_mesas_abertas);
        if ($num_mesas_abertas > 0)
        {
          while ($obj_mesas_abertas = mysql_fetch_object($res_mesas_abertas))
          {
            echo utf8_encode('<option value="'.$obj_mesas_abertas->cod_pedidos.'">'.$obj_mesas_abertas->codigo_cliente_mesa.'</option>');
          }
        }
        else
        {
          echo utf8_encode('<option value="">Nenhuma mesa aberta</option>');
        }
        echo '</select>';      
        echo "</span>";
      echo "</div>";


      echo '<div style="text-align: center; margin-top:5px;">';
      echo utf8_encode('Usuário: ');
      echo '<input type="input" size="15" name="usuario_revisar" value="">';
      echo utf8_encode(' Senha: ');
      echo '<input type="password" size="15" name="senha_revisar" value="">';
      //echo utf8_encode('<br />*Usuário e senha é obrigatório apenas para cancelar produtos!');
      echo "</div>";
  
      echo '<div style="text-align: center; margin-top:5px;">';
      echo 'Colaborador: <select name="cod_colaboradores" size="1">';
      echo utf8_encode('<option value=""></option>');
      $sql_colaboradores = "SELECT c.cod_colaboradores, c.nome FROM ipi_colaboradores c WHERE c.cod_tipo_colaboradores = '1' AND c.situacao = 'ATIVO' ORDER BY c.nome";
      $res_colaboradores = mysql_query($sql_colaboradores);
      while ($obj_colaboradores = mysql_fetch_object($res_colaboradores))
      {
        echo utf8_encode('<option value="'.$obj_colaboradores->cod_colaboradores.'">'.$obj_colaboradores->nome.'</option>');
      }
      echo '</select>';
      echo "</div>";

      echo utf8_encode('</form> ');

      if (($num_pizzas) || ($num_bebidas) || ($num_taxas))
      {

        echo '<div style="text-align: center; margin-top:10px;">';

        echo utf8_encode('<input type="button" name="bt_transferir_itens" id="bt_transferir_itens" value="Transferir Itens" onClick="javascript:if(validacao_transferir_itens(document.frm_revisar_pedido)==true){document.frm_revisar_pedido.submit();}" style="margin-right: 30px;">');

        echo utf8_encode('<input type="button" name="bt_cancelar_produtos" id="bt_cancelar_produtos" value="Cancelar Produtos" onClick="javascript:if(validacao_revisar(document.frm_revisar_pedido)==true){document.frm_revisar_pedido.submit();}">');
        echo "<div>";
      }
      else
      {
        echo "<br /><center><b>Pedido vazio!</b></center>";
      }

    break;

  case "imprimir_conta_parcial":

    $arr_resposta = array();

    if ( $controle_mesas->existe_mesa_selecionada() )
    {
      $cod_pedidos = $controle_mesas->localizar_pedido();

      $valor = $controle_mesas->calcular_total_sem_taxas();

      $sql_aux = "SELECT * FROM ipi_pedidos_taxas WHERE cod_pedidos = '".$cod_pedidos."' AND cod_mesas_taxas <> '1' AND situacao_pedidos_taxas <> 'CANCELADO'";
      $res_aux = mysql_query($sql_aux);
      $num_aux = mysql_num_rows($res_aux);
      if ($num_aux>0) //cobrar taxa de serviço e atualizar o valor atual
      {
        $obj_aux = mysql_fetch_object($res_aux);
        $valor += $obj_aux->preco_total;
      }


      $sql_aux = "SELECT * FROM ipi_pedidos_taxas WHERE cod_pedidos = '".$cod_pedidos."' AND cod_mesas_taxas = '1' AND situacao_pedidos_taxas <> 'CANCELADO'";
      $res_aux = mysql_query($sql_aux);
      $num_aux = mysql_num_rows($res_aux);
      if ($num_aux>0) //cobrar taxa de serviço e atualizar o valor atual
      {
        $obj_aux = mysql_fetch_object($res_aux);

        $total_servico = (($valor * $obj_aux->preco_unitario)/100);
        $valor_total_taxas = moeda2bd(bd2moeda($total_servico)); //só para tirar os decimos converte e desconverte

        $sql_taxa_servico = "UPDATE ipi_pedidos_taxas SET preco_total = '".$valor_total_taxas."' WHERE cod_pedidos_taxas='".$obj_aux->cod_pedidos_taxas."'";
        $res_taxa_servico = mysql_query($sql_taxa_servico);
      }

      $valor_total = $valor + $valor_total_taxas; //FIXME: CALCULAR O VALOR SOMENTE DAS TAXAS

      $sql_mesas = "UPDATE ipi_pedidos SET reimpressao = '1', data_hora_final='".date("Y-m-d H:i:s")."', valor = '".$valor."', valor_total = '".$valor_total."' WHERE cod_pedidos = '".$cod_pedidos."'";
      $res_mesas = mysql_query($sql_mesas);
    }

    if ( $res_mesas )
    {
      $arr_resposta["ok"] = "S";
    }
    else
    {
      $arr_resposta["ok"] = "N";
    }
    
    echo json_encode($arr_resposta);
  break;      

}