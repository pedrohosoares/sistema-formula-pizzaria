<?
require_once 'ipi_session.php';
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';

$acao = validaVarPost('acao', '/detalhes/');
$codigo = $_SESSION['ipi_cliente']['codigo'];

if($acao == 'detalhes') {
  $cod_pedidos = validaVarPost('cod_pedidos', '/[0-9]+/');
  
  $con = conectabd();
  
  $obj_buscar_detalhamento = executaBuscaSimples("SELECT p.*,pi.nome as nome_pizzaria FROM ipi_pedidos p inner join ipi_pizzarias pi on pi.cod_pizzarias = p.cod_pizzarias WHERE p.cod_pedidos = $cod_pedidos AND p.cod_clientes = $codigo", $con);

  echo "<div class='divinterna'>";  
  if ($obj_buscar_detalhamento->apelido)
  {  
        
    echo '<h2 align="center" >'.$obj_buscar_detalhamento->apelido.'</h2>';
    
    echo '<h2 align="center" ><b>Pedido nº '.sprintf('%08d', $obj_buscar_detalhamento->cod_pedidos).' - '.bd2datahora($obj_buscar_detalhamento->data_hora_pedido).'</b></h2>';

    
    echo '<br/>';
  }

  else
  {
        
    echo '<h2 align="center" ><b>Pedido nº '.sprintf('%08d', $obj_buscar_detalhamento->cod_pedidos).' - '.bd2datahora($obj_buscar_detalhamento->data_hora_pedido).'</b></h2>';
    
    echo '<br/>';
  }
  

  $sql_buscar_pedidos_pizzas = 'SELECT * FROM ipi_pedidos_pizzas p INNER JOIN ipi_tamanhos t ON (p.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_tipo_massa m ON (p.cod_tipo_massa = m.cod_tipo_massa) WHERE p.cod_pedidos = ' . $obj_buscar_detalhamento->cod_pedidos . ' ORDER BY cod_pedidos_pizzas';
  $res_buscar_pedidos_pizzas = mysql_query($sql_buscar_pedidos_pizzas);
  //echo $sql_buscar_pedidos_pizzas;
  
  #----------- PIZZAS -------------#
  
      #------- TIPO PIZZA -------#
  
  $promocional = false;
  $fidelidade = false;
  $num_pizza = 1;
  while($obj_buscar_pedidos_pizzas = mysql_fetch_object($res_buscar_pedidos_pizzas)) 
  {
    echo "<div >";
    
    echo "<h4 class='mintitulo'>${num_pizza}&ordm; ".ucfirst(TIPO_PRODUTO);
    if ($obj_buscar_pedidos_pizzas->promocional) 
    {
      echo " (GRÁTIS)";
      $promocional = true;
    }
    elseif ($obj_buscar_pedidos_pizzas->fidelidade) 
    {
      echo " (FIDELIDADE)";
      $fidelidade = true;
    }
    elseif ($obj_buscar_pedidos_pizzas->combo) 
    {
      echo " (COMBO)";
    }
    echo "</h4>";
    
    if($obj_buscar_pedidos_pizzas->preco > 0)
      $valor_quant_fracao = '(R$'.bd2moeda($obj_buscar_pedidos_pizzas->preco).')';
    else
      $valor_quant_fracao = '';
       
    echo '<ul class="lista_infos3__ fonte13">';  
    $arr_aux_tamanhos = explode(')', $obj_buscar_pedidos_pizzas->tamanho);
    echo '<li ><strong>Tamanho:</strong> '.$arr_aux_tamanhos[0].'</li>';
          
    
    
        #------- PIZZA (SABOR E INGREDIENTES) -------#
        
    $sql_buscar_pedidos_fracoes = "SELECT *, fr.preco as preco_pizza FROM ipi_pedidos_fracoes fr INNER JOIN ipi_pizzas p ON (fr.cod_pizzas = p.cod_pizzas) WHERE fr.cod_pedidos = ".$obj_buscar_pedidos_pizzas->cod_pedidos." AND fr.cod_pedidos_pizzas = ".$obj_buscar_pedidos_pizzas->cod_pedidos_pizzas." ORDER BY fracao";
    //echo $sql_buscar_pedidos_fracoes;
    $res_buscar_pedidos_fracoes = mysql_query($sql_buscar_pedidos_fracoes);
    
    while($obj_buscar_pedidos_fracoes = mysql_fetch_object($res_buscar_pedidos_fracoes)) {
      if($obj_buscar_pedidos_pizzas->promocional)
      {
        $valor_pedidos_fracoes = '(GRÁTIS)';  
        $promocional = true;
      }
      else if($obj_buscar_pedidos_pizzas->fidelidade)
      {
        $valor_pedidos_fracoes = '(FIDELIDADE)';
        $fidelidade = true;
      }
      else if($obj_buscar_pedidos_pizzas->combo)
        $valor_pedidos_fracoes = '(COMBO)';
      else
        $valor_pedidos_fracoes = '(R$'.bd2moeda($obj_buscar_pedidos_fracoes->preco_pizza).')';
        
      if($obj_buscar_pedidos_fracoes->fracao != 1)
      {
        echo '<hr/><br />';  
      }
      echo '<li><strong>Sabor:</strong> '.$obj_buscar_pedidos_fracoes->pizza.' '.$valor_pedidos_fracoes.'</li>';


      // Ingredientes padrão do pedido
      $sql_buscar_pedidos_ingredientes_pedido = "SELECT ig.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_ingredientes ig ON (pi.cod_ingredientes = ig.cod_ingredientes) WHERE pi.ingrediente_padrao = 1 AND pi.cod_pedidos_pizzas = ".$obj_buscar_pedidos_fracoes->cod_pedidos_pizzas." AND pi.cod_pedidos = ".$obj_buscar_pedidos_fracoes->cod_pedidos." AND pi.cod_pedidos_fracoes = ".$obj_buscar_pedidos_fracoes->cod_pedidos_fracoes.' ORDER BY ingrediente';
      $res_buscar_pedidos_ingredientes_pedido = mysql_query($sql_buscar_pedidos_ingredientes_pedido);
      #echo $sql_buscar_pedidos_ingredientes_pedido.'<br/>';
      $arr_ing_pedido = array();
      while($obj_buscar_pedidos_ingredientes_pedido = mysql_fetch_object($res_buscar_pedidos_ingredientes_pedido)) 
      {
        $arr_ing_pedido[] = $obj_buscar_pedidos_ingredientes_pedido->cod_ingredientes;
      }      
      //Ingredientes da pizza
      $sql_buscar_pedidos_ingredientes_pizza = "SELECT i.* FROM ipi_pedidos_fracoes ipf INNER JOIN ipi_ingredientes_ipi_pizzas ipip ON (ipf.cod_pizzas = ipip.cod_pizzas) INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ipip.cod_ingredientes) WHERE ipf.cod_pedidos = ".$obj_buscar_pedidos_fracoes->cod_pedidos." AND ipf.cod_pedidos_pizzas = ".$obj_buscar_pedidos_fracoes->cod_pedidos_pizzas." AND ipf.cod_pedidos_fracoes = ".$obj_buscar_pedidos_fracoes->cod_pedidos_fracoes." AND i.ativo = 1 AND i.consumo = 0 AND i.cod_ingredientes not in (116) ORDER BY ingrediente";
      
      $res_buscar_pedidos_ingredientes_pizza = mysql_query($sql_buscar_pedidos_ingredientes_pizza);
      
      #echo $sql_buscar_pedidos_ingredientes_pizza;
      
      // Ingredientes retirados
      /*$sql_buscar_pedidos_ingredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = ".$obj_buscar_detalhamento->cod_pedidos." AND pi.cod_pedidos_pizzas = ".$obj_buscar_pedidos_pizzas->cod_pedidos_pizzas." AND pi.cod_pedidos_fracoes = ".$obj_buscar_pedidos_fracoes->cod_pedidos_fracoes." AND pi.ingrediente_padrao = 1) AND p.cod_pizzas = ".$obj_buscar_pedidos_fracoes->cod_pizzas.' AND i.consumo = 0 ORDER BY ingrediente';
      $res_buscar_pedidos_ingredientes = mysql_query($sql_buscar_pedidos_ingredientes);*/
      
      echo '<li><strong>Ingredientes:</strong> ';
        while($obj_buscar_pedidos_ingredientes_pizza = mysql_fetch_object($res_buscar_pedidos_ingredientes_pizza)) 
        {
          if(in_array($obj_buscar_pedidos_ingredientes_pizza->cod_ingredientes, $arr_ing_pedido))
          {            
            echo ucfirst(mb_strtolower($obj_buscar_pedidos_ingredientes_pizza->ingrediente)).', ';
          }
          else
          {
            echo '<s>'.ucfirst(mb_strtolower($obj_buscar_pedidos_ingredientes_pizza->ingrediente)).'</s>, ';
          }

        }      
      echo '</li>';
      
        #--------ADICIONAIS--------#    
      
      $sql_buscar_adicionais = "SELECT * FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_ingredientes ig ON (pi.cod_ingredientes = ig.cod_ingredientes) WHERE pi.ingrediente_padrao = 0 AND pi.cod_pedidos_pizzas = ".$obj_buscar_pedidos_fracoes->cod_pedidos_pizzas." AND pi.cod_pedidos = ".$obj_buscar_pedidos_fracoes->cod_pedidos." AND pi.cod_pedidos_fracoes = ".$obj_buscar_pedidos_fracoes->cod_pedidos_fracoes.' ORDER BY ingrediente';
      $res_buscar_adicionais = mysql_query($sql_buscar_adicionais);
      $rows_buscar_adicionais = mysql_num_rows($res_buscar_adicionais);
      
      echo '<li><strong>Adicionais:</strong> ';
      if($rows_buscar_adicionais > 0)
      {
        while($obj_buscar_adicionais = mysql_fetch_object($res_buscar_adicionais)) 
        {
          echo ucfirst(mb_strtolower($obj_buscar_adicionais->ingrediente)).' (R$'.bd2moeda($obj_buscar_adicionais->preco).'), ';
        }
      }
      else
      {
        echo 'Nenhum';
      }       
      echo '</li></ul>';
     
    } 
    echo "</div>";
    $num_pizza++;
  }
  
  #----------- BEBIDAS -------------#
  
  $sql_buscar_pedidos_bebidas = "SELECT *, p.preco AS pedidos_preco FROM ipi_pedidos_bebidas p INNER JOIN ipi_bebidas_ipi_conteudos bc ON (p.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) WHERE cod_pedidos = ".$obj_buscar_detalhamento->cod_pedidos;
  $res_buscar_pedidos_bebidas = mysql_query($sql_buscar_pedidos_bebidas);
  $rows_buscar_pedidos_bebidas = mysql_num_rows($res_buscar_pedidos_bebidas);
  
  echo '<br/>';
  echo "<div >";
  
  echo "<strong class='mintitulo'>Bebidas</strong>";
  if ($rows_buscar_pedidos_bebidas > 0)
  {
    while($obj_buscar_pedidos_bebidas = mysql_fetch_object($res_buscar_pedidos_bebidas)) {
          echo '<ul class="fonte13">';
      echo '<li> <strong>Bebida:</strong> '.$obj_buscar_pedidos_bebidas->bebida.' '.$obj_buscar_pedidos_bebidas->conteudo.'</li>';
      echo '<li> <strong>Quantidade:</strong> '.$obj_buscar_pedidos_bebidas->quantidade.'</li>';


      $valor_total_pedido_bebidas = 0;
      
      if($obj_buscar_pedidos_bebidas->promocional) {
        $valor_pedido_bebidas = 'GRÁTIS';
        $valor_total_pedido_bebidas += 0;
        $promocional = true;
      }
      elseif($obj_buscar_pedidos_bebidas->fidelidade) {
        $valor_pedido_bebidas = 'FIDELIDADE';
        $valor_total_pedido_bebidas += 0;
        $fidelidade = true;
      }
      elseif($obj_buscar_pedidos_bebidas->combo) {
        $valor_pedido_bebidas = 'COMBO';
        $valor_total_pedido_bebidas += 0;
      }
      else {
        $valor_pedido_bebidas = 'R$'.bd2moeda($obj_buscar_pedidos_bebidas->pedidos_preco);
        $valor_total_pedido_bebidas += $obj_buscar_pedidos_bebidas->pedidos_preco * $obj_buscar_pedidos_bebidas->quantidade;
      }
      echo '<li> <strong>Valor Unit.:</strong> '.$valor_pedido_bebidas.'</li>';
      echo '<li> <strong>Valor Total:</strong> '.($valor_total_pedido_bebidas ? 'R$'.bd2moeda($valor_total_pedido_bebidas) : '').'</li>';
      echo '</ul>';
    }
  }
  else
  {
    echo '<ul ><li>Sem bebidas.</li></ul>';
    echo '<br /> ';
  }
  echo '</div>';
  
  #----------- BOTTOM ------------#
  
  echo '<div >';
  
  #------ PAGAMENTO ------#
  
  echo '<div >';
  
  echo "<h4 class='mintitulo'>Pagamento</h4>";
  
  echo '<ul class="fonte13" >';
  
  echo '<li>
    <strong>Forma:</strong>
    '.bd2texto($obj_buscar_detalhamento->forma_pg).'
    </li>';
  echo '<li>
    <strong>Valor:</strong>
    '.bd2moeda($obj_buscar_detalhamento->valor_total).'
    </li>';
  
  echo '</ul>';
  
  
  #------ ENTREGA ------#
  
      
  echo "<h4 class='mintitulo'>Entrega</h4><ul class='fonte13'>";
  
  if($obj_buscar_detalhamento->tipo_entrega == 'Balcão') 
  {
    echo '<li><strong>'.$obj_buscar_detalhamento->tipo_entrega.' ('.$obj_buscar_detalhamento->nome_pizzaria.')</strong></li>';
    echo '</ul>';
  }
  else 
  {
    echo '<li>
      <strong>Endereço:</strong>
      '.bd2texto($obj_buscar_detalhamento->endereco).'
      </li>';
    echo '<li>
      <strong>Número:</strong>
      '.bd2texto($obj_buscar_detalhamento->numero).'
      </li>';
    echo '<li>
      <strong>Complemento:</strong>
      '.bd2texto($obj_buscar_detalhamento->complemento).'
      </li>';
    echo '<li>
      <strong>Bairro:</strong>
      '.bd2texto($obj_buscar_detalhamento->bairro).'
      </li>';
    echo '<li>
      <strong>Cidade:</strong>
      '.bd2texto($obj_buscar_detalhamento->cidade).'
      </li>';
    echo '<li>
      <strong>Estado:</strong>
      '.bd2texto($obj_buscar_detalhamento->estado).'
      </li>';
    echo '<li>
      <strong>CEP:</strong>
      '.bd2texto($obj_buscar_detalhamento->cep).'
      </li>';
    echo '</ul>';
  }
  echo '<br/>';
  echo '</div>';
  
  
  // REPETIR PEDIDO
    echo '<a  href="meus_pedidos" style="float:left;margin-right:10px">&laquo; Voltar</a>';
  echo '<form id="frmRepetir" method="post" action="ipi_req_carrinho_acoes.php" class="float_right">';
  echo '<input type="hidden" name="acao" id="acao" value="repetir_pedido" />';
  echo '<input type="hidden" name="cod_pedidos" id="cod_pedidos" value="'.$cod_pedidos.'" />';
  if($promocional == false && $fidelidade == false)
    echo '<input type="submit" alt="Clique para ver mais detalhes do pedido." value="Repetir" class="btn btn-warning" />';
  else
    echo '<p style="text-align: center;">Não é possível repetir um pedido promocional</p>';
  echo '</form>';
    

  //echo '<div class="float_right caixa_historico_pedido_btn_repetir"> <form id="frmCadastro" action="'.$PHP_SELF.'" method="post"> <input type="image" src="img/pc/btn_repetir.png" alt="Repetir esse pedido!" class="botao" /> </form> </div>';
  echo '</div>';
  echo "</div>";
  echo '<div class="bottom_div"> </div>';
  desconectabd($con);
}
else if($acao == '') {
  $con = conectabd();
  
  $SqlBuscaPedidos = "SELECT * FROM ipi_pedidos WHERE cod_clientes = $codigo  ORDER BY cod_pedidos DESC";
  $resBuscaPedidos = mysql_query($SqlBuscaPedidos);

  echo '<br/>';
  echo '<div class="divhistoricopedido" align="center">';
  echo '<br/>';
  echo '<table width="90%" class="tabela_historico">';
  echo '<thead>';
  echo '<tr>';

  echo '<th class="coluna_historico coluna1 fonte15">CÓDIGO</th>';
  echo '<th class="coluna_historico coluna2 fonte15">DATA E HORA</th>';
  echo '<th class="coluna_historico coluna3 fonte15">VALOR TOTAL</th>';
  echo '<th class="coluna_historico coluna4 fonte15">FORMA PGTO</th>';
  echo '<th class="coluna_historico coluna5 fonte15">PEDIDOS</th>';

  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';


  while($objBuscaPedidos = mysql_fetch_object($resBuscaPedidos)) {
   
    echo '<tr>';
    echo '<td align="center" class="coluna_historico coluna1 borda_vermelha">'.sprintf('%08d', $objBuscaPedidos->cod_pedidos).'</td>';
    echo '<td align="center" class="coluna_historico coluna2 borda_vermelha">'.bd2datahora($objBuscaPedidos->data_hora_pedido).'</td>';
    echo '<td align="center" class="coluna_historico coluna3 borda_vermelha"><b>R$ '.bd2moeda($objBuscaPedidos->valor_total).'</b></td>';
    echo '<td align="center" class="coluna_historico coluna4 borda_vermelha"> '.$objBuscaPedidos->forma_pg.' </td>';
    echo '<td align="center" class="coluna_historico coluna5 borda_vermelha">';
    echo '<form style="margin: 0 0 0px" method="post" action="'.$PHP_SELF.'">';
    echo '<input style="margin-bottom: 0px" type="submit" alt="Clique para ver mais detalhes do pedido." value="Detalhes" />';
    echo '<input type="hidden" name="cod_pedidos" value="'.$objBuscaPedidos->cod_pedidos.'" />';
    echo '<input type="hidden" name="acao" value="detalhes" />';
    echo '</form>';
    echo '</td>';
    echo '</tr>';
  }
  
  echo '</tbody>';
  echo '</table>';
  echo '<br/>';
  echo '</div>';
  echo '<div class="bottom_div"> </div>';
  desconectabd($con);
}
?>
