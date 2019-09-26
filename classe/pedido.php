<?

require_once dirname(__FILE__) . '/../bd.php';

/**
 * Classe de Pedido
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       27/04/2012   Thiago        Criado.
 *
 */
class Pedido
{

    /**
    * Retorna o pedido em HTML pronto para visualização.
    *
    * @param string $cod_pedidos
    * 
    * @return o pedido em formato HTML
    */

    public function gerar_html_pedido($cod_pedidos,$codigo = 0) 
    {
      $pedido_html = "";
      $conexao = conectar_bd();
      

      if($codigo !=0)
      {
        $sql_busca_codigo_pedido = "SELECT * FROM ipi_pedidos WHERE cod_pedidos = '".$cod_pedidos."' AND cod_clientes = $codigo ORDER BY cod_pedidos LIMIT 1";
      }
      else
      {
        $sql_busca_codigo_pedido = "SELECT * FROM ipi_pedidos WHERE cod_pedidos = '".$cod_pedidos."' ORDER BY cod_pedidos LIMIT 1";
      }
      

     // $pedido_html .= $sql_busca_codigo_pedido ;
      $res_busca_codigo_pedido = mysql_query($sql_busca_codigo_pedido);
      $obj_busca_codigo_pedido = mysql_fetch_object($res_busca_codigo_pedido);
      $cod_pizzarias = $obj_busca_codigo_pedido->cod_pizzarias;
      $SqlBuscaPedidosPizzas = 'SELECT * FROM ipi_pedidos_pizzas p INNER JOIN ipi_tamanhos t ON (p.cod_tamanhos = t.cod_tamanhos) WHERE p.cod_pedidos = '.$obj_busca_codigo_pedido->cod_pedidos.' ORDER BY cod_pedidos_pizzas';
      $resBuscaPedidosPizzas = mysql_query($SqlBuscaPedidosPizzas);
       //$pedido_html .= $SqlBuscaPedidosPizzas;
      //$pedido_html .= "<br />".$SqlBuscaPedidosPizzas;

      // Variáveis de controle.

      $num_pizza = 1;
      while($objBuscaPedidosPizzas = mysql_fetch_object($resBuscaPedidosPizzas)) 
      {

        $pedido_html .= "<h4>Pizza $num_pizza</h4>";
        $pedido_html .= '<hr noshade size="1" color="#EB891A">';
        
        $pedido_html .= '<table>';

        $pedido_html .= '<tr>';
        $pedido_html .= '<td><b>Tamanho da Pizza:</b></td>';
        $pedido_html .= '<td width="50">&nbsp;</td>';
        $pedido_html .= '<td><b>Quantidade de Sabores:</b></td>';
        $pedido_html .= '</tr>';
        $pedido_html .= '<tr>';
        $pedido_html .= '<td>'.$objBuscaPedidosPizzas->tamanho.'</td>';
        $pedido_html .= '<td width="50">&nbsp;</td>';
        $pedido_html .= '<td>'.$objBuscaPedidosPizzas->quant_fracao.'</td>';
        $pedido_html .= '</tr>';
        
        $pedido_html .= '<tr><td colspan="3">&nbsp;</td></tr>';
        
        $pedido_html .= '<tr>';
        $pedido_html .= '<td><b>Borda:</b></td>';
        $pedido_html .= '<td width="50">&nbsp;</td>';
        $pedido_html .= '<td><b>Gergelim:</b></td>';
        $pedido_html .= '</tr>';

        $pedido_html .= '<tr>';
        $objBuscaPedidosBorda = executaBuscaSimples("SELECT * FROM ipi_pedidos_bordas p INNER JOIN ipi_bordas b ON (p.cod_bordas = b.cod_bordas) WHERE p.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND p.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas, $conexao);
        
        if($objBuscaPedidosBorda->borda) 
        {
          $pedido_html .= '<td>'.$objBuscaPedidosBorda->borda.'</td>';
        }
        else 
        {
          $pedido_html .= '<td>Não</td>';
        }
        $pedido_html .= '<td width="50">&nbsp;</td>';
        $objBuscaPedidosAdicional = executaBuscaSimples("SELECT * FROM ipi_pedidos_adicionais p INNER JOIN ipi_adicionais a ON (p.cod_adicionais = a.cod_adicionais) WHERE p.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND p.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas, $conexao);
        
        if($objBuscaPedidosAdicional->adicional) 
        {
          $pedido_html .= '<td>'.$objBuscaPedidosAdicional->adicional.'</td>';
        }
        else 
        {
          $pedido_html .= '<td>Não</td>';
        }
        $pedido_html .= '</tr>';


        $pedido_html .= '<tr><td colspan="3">&nbsp;</td></tr>';

        
        $pedido_html .= '<tr>';
        $pedido_html .= '<td><b>Tipo de Massa:</b></td>';
        $pedido_html .= '<td width="50">&nbsp;</td>';
        $pedido_html .= '<td><b>Corte:</b></td>';
        $pedido_html .= '</tr>';


        $pedido_html .= '<tr>';
        $objBuscaTipoMassa = executaBuscaSimples("SELECT * FROM ipi_tipo_massa tm INNER JOIN ipi_tamanhos_ipi_tipo_massa tt ON (tm.cod_tipo_massa = tt.cod_tipo_massa) WHERE tm.cod_tipo_massa = '".$objBuscaPedidosPizzas->cod_tipo_massa."' AND tt.cod_tamanhos = '".$objBuscaPedidosPizzas->cod_tamanhos."'", $conexao);
        if($objBuscaTipoMassa->tipo_massa) 
        {
          $pedido_html .= '<td>'.$objBuscaTipoMassa->tipo_massa.'</td>';
        }
        else 
        {
          $pedido_html .= '<td>Não</td>';
        }


        $pedido_html .= '<td width="50">&nbsp;</td>';
        $objBuscaCorte = executaBuscaSimples("SELECT * FROM ipi_opcoes_corte oc INNER JOIN ipi_tamanhos_ipi_opcoes_corte toc ON (oc.cod_opcoes_corte = toc.cod_opcoes_corte) WHERE oc.cod_opcoes_corte = '".$objBuscaPedidosPizzas->cod_opcoes_corte."' AND toc.cod_tamanhos = '".$objBuscaPedidosPizzas->cod_tamanhos."'", $conexao);
        if($objBuscaCorte->opcao_corte) 
        {
          $pedido_html .= '<td>'.$objBuscaCorte->opcao_corte.'</td>';
        }
        else 
        {
          $pedido_html .= '<td>Não Selecionado</td>';
        }
        $pedido_html .= '</tr>';




        $pedido_html .= '</table>';
        
        
        $SqlBuscaPedidosFracoes = "SELECT * FROM ipi_pedidos_fracoes fr INNER JOIN ipi_pizzas p ON (fr.cod_pizzas = p.cod_pizzas) WHERE fr.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND fr.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas." ORDER BY fracao";
        $resBuscaPedidosFracoes = mysql_query($SqlBuscaPedidosFracoes);
        
        while($objBuscaPedidosFracoes = mysql_fetch_object($resBuscaPedidosFracoes)) 
        {

          $pedido_html .= '<br><br><b class="laranja">'.$objBuscaPedidosFracoes->fracao.'º sabor:</b> <b>'.$objBuscaPedidosFracoes->pizza.'</b>';
          $pedido_html .= '<br><br><b>Ingredientes Retirados:</b>';
          $SqlBuscaPedidosIngredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = ".$obj_busca_codigo_pedido->cod_pedidos." AND pi.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas." AND pi.cod_pedidos_fracoes = ".$objBuscaPedidosFracoes->cod_pedidos_fracoes." AND pi.ingrediente_padrao = 1) AND i.consumo != 1 AND p.cod_pizzas = ".$objBuscaPedidosFracoes->cod_pizzas.' ORDER BY ingrediente';
          $resBuscaPedidosIngredientes = mysql_query($SqlBuscaPedidosIngredientes);
          
          $pedido_html .= '<ol style="margin-bottom: 10px; margin-top: 10px;">';
          while($objBuscaPedidosIngredientes = mysql_fetch_object($resBuscaPedidosIngredientes)) 
          {
            $pedido_html .= '<li>'.$objBuscaPedidosIngredientes->ingrediente.'</li>';
          }
          $pedido_html .= '</ol>';

          $pedido_html .= '<b>Ingredientes Adicionados:</b>';
          $SqlBuscaPedidosExtra = "SELECT * FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_ingredientes ig ON (pi.cod_ingredientes = ig.cod_ingredientes) WHERE pi.ingrediente_padrao = 0 AND pi.cod_pedidos_pizzas = ".$objBuscaPedidosFracoes->cod_pedidos_pizzas." AND pi.cod_pedidos = ".$objBuscaPedidosFracoes->cod_pedidos." AND ig.consumo != 1 AND pi.cod_pedidos_fracoes = ".$objBuscaPedidosFracoes->cod_pedidos_fracoes.' ORDER BY ingrediente';
          $resBuscaPedidosExtra = mysql_query($SqlBuscaPedidosExtra);
          $pedido_html .= '<ol style="margin-bottom: 10px; margin-top: 10px;">';
          while($objBuscaPedidosExtra = mysql_fetch_object($resBuscaPedidosExtra)) 
          {
            $pedido_html .= '<li>'.$objBuscaPedidosExtra->ingrediente.'</li>';
          }
          $pedido_html .= '</ol>';
        }
        $num_pizza++;
      }
      
      $pedido_html .= "<h4>Bebida</h4>";
      $pedido_html .= '<hr noshade size="1" color="#EB891A">';

      $pedido_html .= '<table>';
      $pedido_html .= '<tr>';
      $pedido_html .= '<td><b>Quantidade:</b></td>';
      $pedido_html .= '<td width="50">&nbsp;</td>';
      $pedido_html .= '<td><b>Bebida:</b></td>';
      $pedido_html .= '<td width="50">&nbsp;</td>';
      $pedido_html .= '<td><b>Conteúdo:</b></td>';
      $pedido_html .= '</tr>';

      $SqlBuscaPedidosBebidas = "SELECT * FROM ipi_pedidos_bebidas p INNER JOIN ipi_bebidas_ipi_conteudos bc ON (p.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) WHERE cod_pedidos = ".$obj_busca_codigo_pedido->cod_pedidos;
      $resBuscaPedidosBebidas = mysql_query($SqlBuscaPedidosBebidas);
      while($objBuscaPedidosBebidas = mysql_fetch_object($resBuscaPedidosBebidas)) 
      {
        $pedido_html .= '<tr>';
        $pedido_html .= '<td>'.$objBuscaPedidosBebidas->quantidade.'</td>';
        $pedido_html .= '<td width="50">&nbsp;</td>';
        $pedido_html .= '<td>'.$objBuscaPedidosBebidas->bebida.'</td>';
        $pedido_html .= '<td width="50">&nbsp;</td>';
        $pedido_html .= '<td>'.$objBuscaPedidosBebidas->conteudo.'</td>';
        $pedido_html .= '</tr>';
        $pedido_html .= '<tr><td colspan="7"></td></tr>';
      }
      $pedido_html .= '</table>';
      
      $pedido_html .= '<br /><br /><br />';


      desconectabd($conexao);
      return $pedido_html;
    }
    

    public function buscar_pedidos($cod_pedidos)
    {
      $conexao = conectar_bd();
      $sql_busca_codigo_pedido = "SELECT * FROM ipi_pedidos WHERE cod_pedidos = '".$cod_pedidos."' ORDER BY cod_pedidos LIMIT 1";
      $res_busca_codigo_pedido = mysql_query($sql_busca_codigo_pedido);
      $obj_busca_codigo_pedido = mysql_fetch_object($res_busca_codigo_pedido);
      desconectabd($conexao);
      return $obj_busca_codigo_pedido;
    }
    public function Utf8_ansi($valor='') {
      $Utf8_ansi2 = array(
        "u00c0" =>"À",
        "u00c1" =>"Á",
        "u00c2" =>"Â",
        "u00c3" =>"Ã",
        "u00c4" =>"Ä",
        "u00c5" =>"Å",
        "u00c6" =>"Æ",
        "u00c7" =>"Ç",
        "u00c8" =>"È",
        "u00c9" =>"É",
        "u00ca" =>"Ê",
        "u00cb" =>"Ë",
        "u00cc" =>"Ì",
        "u00cd" =>"Í",
        "u00ce" =>"Î",
        "u00cf" =>"Ï",
        "u00d1" =>"Ñ",
        "u00d2" =>"Ò",
        "u00d3" =>"Ó",
        "u00d4" =>"Ô",
        "u00d5" =>"Õ",
        "u00d6" =>"Ö",
        "u00d8" =>"Ø",
        "u00d9" =>"Ù",
        "u00da" =>"Ú",
        "u00db" =>"Û",
        "u00dc" =>"Ü",
        "u00dd" =>"Ý",
        "u00df" =>"ß",
        "u00e0" =>"à",
        "u00e1" =>"á",
        "u00e2" =>"â",
        "u00e3" =>"ã",
        "u00e4" =>"ä",
        "u00e5" =>"å",
        "u00e6" =>"æ",
        "u00e7" =>"ç",
        "u00e8" =>"è",
        "u00e9" =>"é",
        "u00ea" =>"ê",
        "u00eb" =>"ë",
        "u00ec" =>"ì",
        "u00ed" =>"í",
        "u00ee" =>"î",
        "u00ef" =>"ï",
        "u00f0" =>"ð",
        "u00f1" =>"ñ",
        "u00f2" =>"ò",
        "u00f3" =>"ó",
        "u00f4" =>"ô",
        "u00f5" =>"õ",
        "u00f6" =>"ö",
        "u00f8" =>"ø",
        "u00f9" =>"ù",
        "u00fa" =>"ú",
        "u00fb" =>"û",
        "u00fc" =>"ü",
        "u00fd" =>"ý",
        "u00ff" =>"ÿ",
        "u2022u2022u2022u2022 "=>"******"
      );
      return strtr($valor, $Utf8_ansi2);      
    }
    public function retornar_resumo_pedido_sys($cod_pedidos,$tipo_titulo = "h2")
    {
      $conexao = conectabd();
      $retorno = "";
      $sql = "SELECT cod_pizzarias,pedido_ifood_json,data_hora_inicial FROM ipi_pedidos WHERE cod_pedidos='".$cod_pedidos."'";
      $sql = mysql_query($sql);
      $sql = mysql_fetch_object($sql);
      $pedido_ifood_json = $sql->pedido_ifood_json;
      if(!empty($pedido_ifood_json)){
        $json = json_decode($json,true);
        $json = json_decode($sql->pedido_ifood_json,true);
        $order = $json['order'];
        $retorno .='<h1 align="center">Pedido '.$cod_pedidos.'</h1>';
        $retorno .='<br><br>';
        $retorno .='<label>Cliente</label>';
        $retorno .='<br>';
        $retorno .='<hr noshadow="noshadow" size="1" color="#D44E08">';
        $retorno .='<br>';
        $retorno .='<table>';
        $retorno .='<tbody>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Nome: </label>';
        $retorno .='<a> '.htmlentities($order['customer']['name']).'</a>';
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>E-mail: </label>';
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Telefone 1: </label>';
        $retorno .='<a> '.htmlentities($this->Utf8_ansi($order['customer']['phone'])).'</a>';
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Telefone 2: </label>';
        $retorno .='<a> '.$this->Utf8_ansi($order['customer']['phone']).'</a>';
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Celular: </label>';
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>'.htmlentities('Endereço:').' </label>';
        $retorno .=htmlentities($this->Utf8_ansi($order['deliveryAddress']['formattedAddress']));
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Num: </label>';
        $retorno .=$this->Utf8_ansi($order['deliveryAddress']['streetNumber']);
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Comp: </label>';
        $retorno .=htmlentities($this->Utf8_ansi($order['deliveryAddress']['complement']));
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>'.htmlentities('Referência').': </label>';
        $retorno .=htmlentities($this->Utf8_ansi($order['deliveryAddress']['reference']));
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Bairro: </label>';
        $retorno .=htmlentities($this->Utf8_ansi($order['deliveryAddress']['neighborhood']));
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Cep: </label>';
        $retorno .=htmlentities($this->Utf8_ansi($order['deliveryAddress']['postalCode']));
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Cidade: </label>';
        $retorno .=htmlentities($this->Utf8_ansi($order['deliveryAddress']['city'])).' - '.$this->Utf8_ansi($order['deliveryAddress']['state']);
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='</tbody>';
        $retorno .='</table>';
        $retorno .='<br><br>';
        $retorno .='<label>Pedido do Ifood | PAGAMENTOS</label>';
        $retorno .='<br>';
        $retorno .='<hr noshadow="noshadow" size="1" color="#D44E08">';
        $retorno .='<br>';
        $retorno .='<table>';
        $retorno .='<tbody>';
        foreach ($order['payments'] as $key => $value) {
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>Tipo: </label>';
          $retorno .=htmlentities($this->Utf8_ansi($value['name']));
          $retorno .='</td>';
          $retorno .='</tr>';
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>'.htmlentities('Código:').' </label>';
          $retorno .=htmlentities($this->Utf8_ansi($value['code']));
          $retorno .='</td>';
          $retorno .='</tr>';
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>Valor: </label>';
          $retorno .='R$'.number_format($value['value'],2);
          $retorno .='</td>';
          $retorno .='</tr>';
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>'.htmlentities('Pré-pago').': </label>';
          $prepago = $value['prepaid'] == true?"Sim":htmlentities("Não");
          $retorno .=$prepago;
          $retorno .='</td>';
          $retorno .='</tr>';
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>'.htmlentities('Transação').': </label>';
          $retorno .=htmlentities($this->Utf8_ansi($value['transaction']));
          $retorno .='</td>';
          $retorno .='</tr>';
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>-------------</label>';
          $retorno .='</td>';
          $retorno .='</tr>';
        }
        $retorno .='</tbody>';
        $retorno .='</table>';

        $retorno .='<br><br>';
        $retorno .='<label>Itens do Ifood</label>';
        $retorno .='<br>';
        $retorno .='<hr noshadow="noshadow" size="1" color="#D44E08">';
        $retorno .='<br>';
        $retorno .='<table>';
        $retorno .='<tbody>';
        foreach ($order['items'] as $key => $value) {
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>Nome: </label>';
          $retorno .=htmlentities($this->Utf8_ansi($value['name'])).' - Quantidade: '.$this->Utf8_ansi($value['quantity']);
          $retorno .='</td>';
          $retorno .='</tr>';
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>'.htmlentities('Preço').': </label>';
          $retorno .='R$'.number_format($value['price'],2);
          $retorno .='</td>';
          $retorno .='</tr>';
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>Desconto: </label>';
          $retorno .='R$'.number_format($value['discount'],2);
          $retorno .='</td>';
          $retorno .='</tr>';
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>Adicional: </label>';
          $retorno .='R$'.number_format($value['addition'],2);
          $retorno .='</td>';
          $retorno .='</tr>';
          $retorno .='<tr>';
          $retorno .='<td>';
          $retorno .='<label>ExternalCode: </label>';
          $retorno .=$value['externalCode'];
          $retorno .='</td>';
          $retorno .='</tr>';
          if(isset($value['observations'])){
                $retorno .='<tr>';
                $retorno .='<td>';
                $retorno .='<label> '.htmlentities($this->Utf8_ansi($value['observations'])).'</label>';
                $retorno .='</td>';
                $retorno .='</tr>';
              }
          if(isset($value['subItems'])){
            $n = 1;
            foreach($value['subItems'] as $si=>$vs){
              $retorno .='<tr>';
              $retorno .='<td>';
              $retorno .='<label>Sub itens '.$n.'</label>';
              $retorno .='</td>';
              $retorno .='</tr>';
              $retorno .='<tr>';
              $retorno .='<td>';
              $retorno .='<label style="margin-left: 20px;">Nome: '.htmlentities($this->Utf8_ansi($vs['name'])).' - Quantidade: '.$vs['quantity'].'</label>';
              $retorno .='</td>';
              $retorno .='</tr>';
              $retorno .='<tr>';
              $retorno .='<td>';
              $retorno .='<label style="margin-left: 20px;">'.htmlentities('Preço').': R$'.$vs['totalPrice'].'</label>';
              $retorno .='</td>';
              $retorno .='</tr>';
              $retorno .='<tr>';
              $retorno .='<td>';
              $retorno .='<label style="margin-left: 20px;">Desconto: R$'.$vs['discount'].'</label>';
              $retorno .='</td>';
              $retorno .='</tr>';
              $retorno .='<tr>';
              $retorno .='<td>';
              $retorno .='<label style="margin-left: 20px;">Adicional: R$'.$vs['addition'].'</label>';
              $retorno .='</td>';
              $retorno .='</tr>';
              $retorno .='<tr>';
              $retorno .='<td>';
              $retorno .='<label style="margin-left: 20px;">ExternalCode: '.$vs['externalCode'].'</label>';
              $retorno .='</td>';
              $retorno .='</tr>';
              if(isset($vs['observations'])){
                $retorno .='<tr>';
                $retorno .='<td>';
                $retorno .='<label style="margin-left: 20px;">'.htmlentities($this->Utf8_ansi($vs['observations'])).'</label>';
                $retorno .='</td>';
                $retorno .='</tr>';
              }
              $retorno .='<tr>';
              $retorno .='<td>';
              $retorno .='<label>-------------</label>';
              $retorno .='</td>';
              $retorno .='</tr>';
              $n = $n+1;
            }
          }
        }
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Sub Total: </label> R$'.$order['subTotal'];
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>'.htmlentities('Preço Total').': </label> R$'.$order['totalPrice'];
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='<tr>';
        $retorno .='<td>';
        $retorno .='<label>Taxa de Entrega: </label> R$'.$order['deliveryFee'];
        $retorno .='</td>';
        $retorno .='</tr>';
        $retorno .='</tbody>';
        $retorno .='</table>';

      }
      if(empty($sql->pedido_ifood_json)){
        $sql_buscar_cliente = "SELECT p.*, e.nome AS e_nome, c.nome AS c_nome, c.email as email, c.celular as celular, u.nome as nome_aten FROM ipi_clientes c INNER JOIN ipi_pedidos p ON (p.cod_clientes = c.cod_clientes) LEFT JOIN ipi_entregadores e ON (p.cod_entregadores = e.cod_entregadores) LEFT JOIN nuc_usuarios u on u.cod_usuarios = p.cod_usuarios_pedido WHERE p.cod_pedidos = '$cod_pedidos'";
        $res_buscar_cliente = mysql_query($sql_buscar_cliente);
        $obj_buscar_cliente = mysql_fetch_object($res_buscar_cliente);


        $retorno .= '<'.$tipo_titulo.' align="center">Pedido ' . sprintf('%08d',$cod_pedidos) . '</'.$tipo_titulo.'><br><br>';
        $sql_cupom = "SELECT * FROM ipi_pedidos_ipi_cupons pc INNER JOIN ipi_cupons c ON (pc.cod_cupons = c.cod_cupons) WHERE pc.cod_pedidos = '$cod_pedidos'";
        $res_cupom = mysql_query($sql_cupom);
        $obj_cupom = mysql_fetch_object($res_cupom);
      //$retorno .= "<Br>1: ".$sql_cupom;

        $objBuscaTroco = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = '".$cod_pedidos."' AND chave = 'TROCO'", $conexao);
        $objBuscaDetalhamentoCPFPaulista = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = '".$cod_pedidos."' AND chave = 'CPF_NOTA_PAULISTA'", $conexao);
        $retorno .= '<label>Cliente</label><br><hr noshadow="noshadow" size="1" color="#D44E08"><br>';

        $retorno .= '<table>';
        $retorno .= '<tr><td><label>Nome:</label> <a style="font-weight:bold" href="ipi_clientes_franquia.php?cc='.$obj_buscar_cliente->cod_clientes.'">'. htmlentities(bd2texto($obj_buscar_cliente->c_nome)) . '</a></td></tr>';
        $retorno .= '<tr><td><label>E-mail:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->email)) . '</td></tr>';
        $retorno .= '<tr><td><label>Telefone 1:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->telefone_1)) . '</td></tr>';
        $retorno .= '<tr><td><label>Telefone 2:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->telefone_2)) . '</td></tr>';
        $retorno .= '<tr><td><label>Celular:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->celular)) . '</td></tr>';
        $retorno .= '<tr><td><label>' . htmlentities('Endereço').':</label> ' . htmlentities(bd2texto($obj_buscar_cliente->endereco)) . '</td></tr>';
        $retorno .= '<tr><td><label>Num.:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->numero)) . ' <label>Comp.:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->complemento)) . ' <label>Edif.:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->complemento)) . '</td></tr>';
        $retorno .= '<tr><td><label>Bairro:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->bairro)) . ' - <label>Cep:</label>' .htmlentities(bd2texto($obj_buscar_cliente->cep)) .'</td></tr>';
        $retorno .= '<tr><td><label>Cidade:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->cidade)) . ' - ' . htmlentities(bd2texto($obj_buscar_cliente->estado)) . '</td></tr>';
        $retorno .= '</table>';

        $retorno .= '<br><br><label>Pedido</label><br><hr noshadow="noshadow" size="1" color="#D44E08"><br>';

        $retorno .= '<table>';
        $retorno .= '<tr><td><label>Obs do Pedido:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->obs_pedido)) . '</td></tr>';
        $retorno .= '<tr><td><label>CPF Paulista:</label> ' . htmlentities(bd2texto($objBuscaDetalhamentoCPFPaulista->conteudo)) . '</td></tr>';
        $retorno .= '<tr><td><label>Valor:</label>R$ ' . htmlentities(bd2moeda($obj_buscar_cliente->valor)) . '</td></tr>';
        if($obj_buscar_cliente->valor_entrega>0)
        {
          $retorno .= '<tr><td><label>Valor da Entrega:</label> R$ ' . htmlentities(bd2moeda($obj_buscar_cliente->valor_entrega)) . '</td></tr>';
        }
        $pgmentoIfoodSql = "SELECT * FROM ipi_pedidos_formas_pg WHERE cod_pedidos ='".$cod_pedidos."'";
        $respgmentoIfoodSql = mysql_query($pgmentoIfoodSql);
        $pgmentoIfood = mysql_fetch_object($respgmentoIfoodSql);
        $pgmentoIfood = json_decode($pgmentoIfood->pagamento_json,true);
        #echo "<pre>";
        #var_dump($pgmentoIfood);
        #echo "</pre>";
        foreach($pgmentoIfood as $ivo=>$vo){
          if($vo['prepaid'] == true){
            $prepaid = "Sim";
          }else{
            $prepaid = "Não";
          }
          if(isset($vo['name'])){
            $retorno .= '<tr><td><label>Name: </label>'.$vo['name'].'</tr></td>';
          }
          if(isset($vo['value'])){
            $retorno .= '<tr><td><label>Value: </label>R$'.$vo['value'].'</tr></td>';
          }
          if(isset($vo['code'])){
            $retorno .= '<tr><td><label>Code: </label>'.$vo['code'].'</tr></td>';
          }
          if(isset($vo['prepaid'])){
            $retorno .= '<tr><td><label>Pago Online: </label>'.$prepaid.'</tr></td>';
          }
        }
        $retorno .= '<tr><td><label>Desconto:</label> R$ ' . htmlentities(bd2moeda(($obj_buscar_cliente->desconto!="" && $obj_buscar_cliente->desconto>0? $obj_buscar_cliente->desconto : '0.00' ))) . '</td></tr>';
        $retorno .= '<tr><td><label>Valor Total:</label> R$ ' . htmlentities(bd2moeda($obj_buscar_cliente->valor_total)) . '</td></tr>';
        $retorno .= '<tr><td><label>Troco:</label> ' . htmlentities(($objBuscaTroco->conteudo != '' ? 'R$ ' . bd2moeda($objBuscaTroco->conteudo) : 'Não' )). '</td></tr>';
        $retorno .= '<tr><td><label>'.htmlentities('Situação').':</label> ' . htmlentities(bd2texto($obj_buscar_cliente->situacao)) . '</td></tr>';
        $retorno .= '<tr><td> '; 
        $sql_formas_pg_detalhada = "SELECT * FROM `ipi_pedidos_formas_pg` pfp inner join ipi_formas_pg fp on pfp.cod_formas_pg = fp.cod_formas_pg WHERE pfp.cod_pedidos = '".$cod_pedidos."'";
        $res_formas_pg_detalhada = mysql_query($sql_formas_pg_detalhada);
        while ($obj_formas_pg_detalhada = mysql_fetch_object($res_formas_pg_detalhada)) 
        {
          if(!empty($obj_formas_pg_detalhada->pagamento_json)){
            $json = json_decode($obj_formas_pg_detalhada->pagamento_json,true);
            $prepaid = false;
            
            if($json[0]['prepaid'] == true or $json[0]['prepaid'] == 'true'){
              $json = " (Pago Online no Ifood)";
            }else{
              $json = " (Não pago Online)";
            }
          }
          $array_formas_pg[] = $obj_formas_pg_detalhada->forma_pg;
          // $retorno .=  htmlentities(bd2texto($obj_formas_pg_detalhada->forma_pg))."";
        }
        $retorno .= htmlentities (implode(", ", $array_formas_pg)).$json;


        $retorno .= '</td></tr>';
        $retorno .= '<tr><td><label>Origem do Pedido:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->origem_pedido)) . '</td></tr>';
        $retorno .= '<tr><td><label>'.htmlentities('Horário de Inicio:').'</label> ' . htmlentities(bd2datahora($obj_buscar_cliente->data_hora_inicial)) . '</td></tr>';
        $retorno .= '<tr><td><label>'.htmlentities('Horário de Finalização:').'</label> ' . htmlentities(bd2datahora($obj_buscar_cliente->data_hora_final)) . '</td></tr>';
        if($obj_buscar_cliente->origem_pedido=="TEL")
        {
          $retorno .= '<tr><td><label>Atendente que fez o  pedido:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->nome_aten)) . '</td></tr>';
        }
        $retorno .= '<tr><td><label>Tipo de Entrega:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->tipo_entrega)) . '</td></tr>';
        $retorno .= '<tr><td><label>Entregador:</label> ' . htmlentities(bd2texto($obj_buscar_cliente->e_nome)) . '</td></tr>';
        $retorno .= '<tr><td><label>Agendado:</label> ' . htmlentities((($obj_buscar_cliente->agendado) ? bd2texto($obj_buscar_cliente->horario_agendamento) : 'Não')) . '</td></tr>';
        $retorno .= '<tr><td><label>Cupom:</label> ' . htmlentities((($obj_cupom->cupom!="") ? $obj_cupom->cupom." (".$obj_cupom->produto.")" : 'Não')) . '</td></tr>';
        $retorno .= '</table><br>';

        $retorno .= '<br><label>Pizzas</label><br /><hr noshadow="noshadow" size="1" color="#D44E08"><br />';

        $sql_buscar_pizzas = "SELECT pp.*, pb.cod_bordas, b.borda, pb.promocional borda_promocional, pb.fidelidade borda_fidelidade, pb.combo borda_combo, pa.cod_adicionais, a.adicional, tp.tipo_massa, t.tamanho,oc.opcao_corte,pb.preco as preco_borda,pa.preco as preco_adicional FROM ipi_pedidos_pizzas pp INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_tipo_massa tp ON (pp.cod_tipo_massa = tp.cod_tipo_massa) LEFT JOIN ipi_pedidos_adicionais pa ON (pp.cod_pedidos = pa.cod_pedidos AND pp.cod_pedidos_pizzas = pa.cod_pedidos_pizzas) LEFT JOIN ipi_adicionais a ON (pa.cod_adicionais = a.cod_adicionais) LEFT JOIN ipi_pedidos_bordas pb ON (pp.cod_pedidos = pb.cod_pedidos AND pp.cod_pedidos_pizzas = pb.cod_pedidos_pizzas) LEFT JOIN ipi_bordas b ON (pb.cod_bordas = b.cod_bordas) LEFT JOIN ipi_opcoes_corte oc ON (pp.cod_opcoes_corte = oc.cod_opcoes_corte) WHERE pp.cod_pedidos = '$cod_pedidos'";
      //echo $sql_buscar_pizzas;
        $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
        $num_buscar_pizzas = mysql_num_rows($res_buscar_pizzas);


        for($p = 1; $p <= $num_buscar_pizzas; $p++)
        {
          $obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas);

          $pizza_preco = true;
          $texto_pizza = '';

          $retorno .= '<table>';
          $retorno .= "<tr><td colspan=\"9\" style=\"color: #D44E08;\"><b>${p}&ordf; Pizza";
          if ($obj_buscar_pizzas->promocional)
          {
            $retorno .= htmlentities(" (Grátis)");
            $pizza_preco = false;
            $texto_pizza = htmlentities(" (Grátis)");
          }
          if ($obj_buscar_pizzas->fidelidade)
          {
            $retorno .= " (Fidelidade)";
            $pizza_preco = false;
            $texto_pizza = " (Fidelidade)";
          }
          if ($obj_buscar_pizzas->combo)
          {
            $retorno .= " (Combo)";
            $pizza_preco = false;
            $texto_pizza = " (Combo)";
          }

          $retorno .= "</b></td></tr>";
          $retorno .= '<tr>';
          $retorno .= "<td><label>Tamanho:</label> " . htmlentities(bd2texto($obj_buscar_pizzas->tamanho)) . "</td>";
          $retorno .= '<td width="10">&nbsp;</td>';
          $retorno .= "<td><label>Quant. Sabores:</label> " . htmlentities(bd2texto($obj_buscar_pizzas->quant_fracao))." ".($obj_buscar_pizzas->preco!=0 ? " <br/>R$ ".bd2moeda($obj_buscar_pizzas->preco) : '')."</td>";
          $retorno .= '<td width="10">&nbsp;</td>';
          $retorno .= "<td><label>Borda:</label> ";
          $retorno .= (($obj_buscar_pizzas->cod_bordas > 0) ? htmlentities(bd2texto($obj_buscar_pizzas->borda))." <br/>R$ ".bd2moeda($obj_buscar_pizzas->preco_borda) : htmlentities('Não'));


          if ($obj_buscar_pizzas->borda_promocional)
          {
            $retorno .= htmlentities(" (Grátis)");
          }
          if ($obj_buscar_pizzas->borda_fidelidade)
          {
            $retorno .= " (Fidelidade)";
          }
          if ($obj_buscar_pizzas->borda_combo)
          {
            $retorno .= " (Combo)";
          }

          $retorno .= "</td>";
/*          $retorno .= '<td width="10">&nbsp;</td>';
$retorno .= "<td><label>Gergelim:</label> " . (($obj_buscar_pizzas->cod_adicionais > 0) ? htmlentities(bd2texto($obj_buscar_pizzas->adicional))." <br/>R$ ".bd2moeda($obj_buscar_pizzas->preco_adicional) : htmlentities('Não')) . "</td>";*/
$retorno .= '<td width="10">&nbsp;</td>';
$retorno .= "<td><label>Tipo de Massa:</label> " . htmlentities(bd2texto($obj_buscar_pizzas->tipo_massa)) . "</td>";
$retorno .= '<td width="10">&nbsp;</td>';
$retorno .= "<td><label>Tipo de Corte:</label> " . htmlentities(bd2texto($obj_buscar_pizzas->opcao_corte)) . "</td>";
$retorno .= '</tr>';
$retorno .= '</table>';

$sql_buscar_fracoes = "SELECT *,pf.preco as preco_fracao FROM ipi_pedidos_pizzas pp INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pizzas p ON (pf.cod_pizzas = p.cod_pizzas) WHERE pp.cod_pedidos = '$cod_pedidos' AND pp.cod_pedidos_pizzas = '" . $obj_buscar_pizzas->cod_pedidos_pizzas . "' ORDER BY pf.fracao";
$res_buscar_fracoes = mysql_query($sql_buscar_fracoes);
$num_buscar_fracoes = mysql_num_rows($res_buscar_fracoes);

for($f = 1; $f <= $num_buscar_fracoes; $f++)
{
  $obj_buscar_fracoes = mysql_fetch_object($res_buscar_fracoes);
  $retorno .= '<table style="margin-left: 30px;">';
  $retorno .= "<tr><td><label>${f} &ordm; Sabor:</label> " . htmlentities(bd2texto($obj_buscar_fracoes->pizza));
  $cod_pizzarias = "SELECT cod_pizzarias FROM ipi_pedidos WHERE cod_pedidos='".$cod_pedidos."'";
  $res_buscar_pizzarias = mysql_query($cod_pizzarias);
  $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
  if($num_buscar_fracoes >1){
    $divisor = 2;
  }else{
    $divisor = 1;
  }
  $preco = "SELECT preco/$divisor AS metade FROM ipi_pizzas_ipi_tamanhos WHERE cod_pizzas='".$obj_buscar_fracoes->cod_pizzas."' AND cod_pizzarias='".$obj_buscar_pizzarias->cod_pizzarias."' AND cod_tamanhos='".$obj_buscar_fracoes->cod_tamanhos."'";
  $res_buscar_preco_fracao = mysql_query($preco);
  $num_buscar_preco_fracao = mysql_query($preco);
  $obj_buscar_preco_fracao = mysql_fetch_object($num_buscar_preco_fracao);
  if(!$pizza_preco)
  {
    $retorno .= $texto_pizza."</td></tr>";
  }
  elseif($obj_buscar_fracoes->preco_fracao!=0)
  {
    $retorno.= " R$ ".number_format($obj_buscar_preco_fracao->metade,2)."</td></tr>";
  }
  if(!empty($obj_buscar_fracoes->obs_fracao)){
    $retorno .= '<tr><td><b>Obs: </b>';
    $retorno .= $obj_buscar_fracoes->obs_fracao.'</td></tr>';
  }
  if(!empty($obj_buscar_fracoes->obs_ifood)){
    $retorno .= '<tr><td><b>Nome Pedido Ifood: </b>';
    $retorno .= $obj_buscar_fracoes->obs_ifood.'</td></tr>';
  }
  $retorno .= "<tr><td style='padding-left: 30px;'> ";

              //$sql_buscar_ingredientes = "SELECT * FROM ipi_pedidos_pizzas pp INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) INNER JOIN ipi_ingredientes i ON (pi.cod_ingredientes = i.cod_ingredientes) WHERE pp.cod_pedidos = '$cod_pedidos' AND pp.cod_pedidos_pizzas = '" . $obj_buscar_pizzas->cod_pedidos_pizzas . "' AND pf.cod_pedidos_fracoes = '" . $obj_buscar_fracoes->cod_pedidos_fracoes . "' AND pi.ingrediente_padrao = 0 ORDER BY ingrediente";

  $sql_buscar_ingredientes = "SELECT * FROM ipi_pedidos_pizzas pp INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) INNER JOIN ipi_ingredientes i ON (pi.cod_ingredientes = i.cod_ingredientes) WHERE pp.cod_pedidos = '$cod_pedidos' AND pp.cod_pedidos_pizzas = '" . $obj_buscar_pizzas->cod_pedidos_pizzas . "' AND pf.cod_pedidos_fracoes = '" . $obj_buscar_fracoes->cod_pedidos_fracoes . "' ORDER BY ingrediente";
  $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
  $num_buscar_ingredientes = mysql_num_rows($res_buscar_ingredientes);
              //$retorno .= "<Br>1: ".$sql_buscar_ingredientes;

  $ingredientes_padroes = array();
  $ingredientes_nao_padroes = array();
  $ingredientes_troca = array();
  $ind_aux_padrao = 0;
  $ind_aux_nao_padrao = 0;
  for ($c = 0; $c < $num_buscar_ingredientes; $c++)
  {
    $obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes);

    $sqlAux = "SELECT * FROM ipi_ingredientes WHERE cod_ingredientes=" . $obj_buscar_ingredientes->cod_ingredientes;
                //$retorno .= "<Br>1: ".$sqlAux;
    $resAux = mysql_query($sqlAux);
    $objAux = mysql_fetch_object($resAux);

    if ($obj_buscar_ingredientes->ingrediente_padrao == 1)
    {
      $ingredientes_padroes[$ind_aux_padrao] = $objAux->cod_ingredientes;
      $ind_aux_padrao++;
    }

    if ($obj_buscar_ingredientes->ingrediente_padrao == 0)
    {
      $ingredientes_nao_padroes[$ind_aux_nao_padrao] = $objAux->cod_ingredientes;
      $ingredientes_troca[$ind_aux_nao_padrao] = $obj_buscar_ingredientes->cod_ingrediente_trocado;
      $ind_aux_nao_padrao++;
    }
  }


  if (count($ingredientes_padroes) > 0)
  {
    $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (ip.cod_ingredientes=i.cod_ingredientes) WHERE ip.cod_pizzas=" . $obj_buscar_fracoes->cod_pizzas . " AND ip.cod_ingredientes NOT IN (" . implode(",", $ingredientes_padroes) . ") AND i.consumo != 1";
    $resAux = mysql_query($sqlAux);
    $linAux = mysql_num_rows($resAux);
                    //$retorno .= "<Br>1: ".$sqlAux;
    if ($linAux > 0)
    {

      while ($objAux = mysql_fetch_object($resAux))
      {
        $retorno .= "<br/><b>Retirar:</b> ";
        $retorno .= htmlentities($objAux->ingrediente);
      }
    }
  }


  if (count($ingredientes_nao_padroes) > 0)
  {
    $sqlAux = "SELECT *,pi.preco as preco_ingrediente FROM ipi_ingredientes i inner join ipi_pedidos_ingredientes pi on pi.cod_ingredientes = i.cod_ingredientes WHERE i.cod_ingredientes IN (" . implode(",", $ingredientes_nao_padroes) . ") AND pi.cod_pedidos = '".$obj_buscar_cliente->cod_pedidos."' AND pi.cod_pedidos_pizzas = '".$obj_buscar_pizzas->cod_pedidos_pizzas."' AND pi.cod_pedidos_fracoes = ".$obj_buscar_fracoes->cod_pedidos_fracoes." and pi.ingrediente_padrao =0";
                    //$retorno .= "<Br>1: ".$sqlAux;
    $resAux = mysql_query($sqlAux);
    $linAux = mysql_num_rows($resAux);
    if ($linAux > 0)
    {
      $z = 0;
      while ($objAux = mysql_fetch_object($resAux))
      {
        $retorno .= "<br><b>Adicionar:</b> ";
        $retorno .= htmlentities($objAux->ingrediente);
        $preco = $objAux->preco_ingrediente;
        if($ingredientes_nao_padroes[$z]==$objAux->cod_ingredientes && $ingredientes_troca[$z] !=0)
        {
          $sqlAux2 = "SELECT * FROM ipi_ingredientes i WHERE i.cod_ingredientes = '" .$ingredientes_troca[$z]. "'";
                              //$retorno .= "<Br>1: ".$sqlAux2;
          $resAux2 = mysql_query($sqlAux2);
          $linAux2 = mysql_num_rows($resAux2);
          $objAux2 = mysql_fetch_object($resAux2);
          $retorno .=' (Troca do '.htmlentities($objAux2->ingrediente).')';
                              //$preco = $objAux2->preco_ingrediente;
        }
        $retorno .= " R$ ".bd2moeda($preco);
        $z++;
      }
    }
  }


/*
              if($num_buscar_ingredientes == 0)
              {
                  $retorno .= htmlentities('Não');
              }
              else
              {
                  while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
                  {
                      $retorno .= htmlentities(bd2texto($obj_buscar_ingredientes->ingrediente)) . ', ';
                  }
              }
*/
              $retorno .= '</td></tr><tr><td>&nbsp;</td></tr></table>';
            }
          }

          $retorno .= '<br><label>Bebidas</label><br><hr noshadow="noshadow" size="1" color="#D44E08">';

          $sql_buscar_bebidas = "SELECT *,pb.preco as preco_bebida FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) WHERE p.cod_pedidos = '$cod_pedidos'";
          $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
          $num_buscar_bebidas = mysql_num_rows($res_buscar_bebidas);

          if($num_buscar_bebidas == 0)
          {
            $retorno .= '<br><label>Sem Bebidas</label><br/>';
          }
          else
          {
            $retorno .= '<br><table>';
            $retorno .= '<tr><td align="center" width="10%"><strong>Quantidade</strong></td><td align="center" width="60%"><strong>Bebida</strong></td><td align="center" width="15%"><strong>Conteudo</strong></td><td align="center" width="15%"><strong>Valor</strong></td></tr>';
            while($obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas))
            {
              $retorno .= '<tr><td align="center">' . $obj_buscar_bebidas->quantidade . '</td><td align="center"> ' . htmlentities(bd2texto($obj_buscar_bebidas->bebida)) . '</td><td align="center">'. htmlentities(bd2texto($obj_buscar_bebidas->conteudo));
              $retorno .= '</td>';

              $retorno .= '<td align="center">';

              $preco = true;
              if ($obj_buscar_bebidas->promocional)
              {
                $retorno .= htmlentities(" (Grátis)");
                $preco = false;
              }

              if ($obj_buscar_bebidas->fidelidade)
              {
                $retorno .= " (Fidelidade)";
                $preco = false;
              }

              if ($obj_buscar_bebidas->combo)
              {
                $retorno .= " (Combo)";
                $preco = false;
              }

              if($preco)
              {
                $retorno .= " R$ ".bd2moeda($obj_buscar_bebidas->preco_bebida);
              }


              $retorno .= '</td></tr>';
            }

            $retorno .= '</table>';
          }

          $sql_buscar_dados = "SELECT * from ipi_clientes_informacao where cod_pedidos = '".$cod_pedidos."'";
          $res_buscar_dados = mysql_query($sql_buscar_dados);
          $obj_buscar_dados = mysql_fetch_object($res_buscar_dados);
          if(mysql_num_rows($res_buscar_dados)>0)
          {
            $retorno .= '<br><label>Dados do Computador em que o pedido foi feito</label><br><hr noshadow="noshadow" size="1" color="#D44E08">';

            $retorno .= '<br><table>';
            $retorno .= htmlentities('<tr><td ><strong>Navegador</strong> : '.bd2texto($obj_buscar_dados->nome_navegador).' <strong>&nbsp; Versão</strong> : '.bd2texto($obj_buscar_dados->versao_navegador).' <strong>&nbsp; Idioma</strong> : '.bd2texto($obj_buscar_dados->idioma).'</td></tr>
              <tr><td><strong>Plataforma do sistema</strong> : '.bd2texto($obj_buscar_dados->nome_plataforma).'</td></tr><tr><td><strong>User agent do navegador</strong> : '.bd2texto($obj_buscar_dados->user_agent));
            
            $retorno .= '</table>';
          }

        }
      /**$retorno .= '<br><br><center>';
      $retorno .= '<input type="button" class="botao" value="Fechar" onclick="fechar_detalhes_pedidos()">';
      $retorno .= '</center>';*/
      desconectabd($conexao);

      return $retorno;
      
    }


    public function verficiar_promocional($cod_pedidos,$cod_clientes = 0)
    {
      $conexao = conectar_bd();
      if($codigo !=0)
      {
        $sql_busca_codigo_pedido = "SELECT * FROM ipi_pedidos WHERE cod_pedidos = '".$cod_pedidos."' AND cod_clientes = $cod_clientes ORDER BY cod_pedidos LIMIT 1";
      }
      else
      {
        $sql_busca_codigo_pedido = "SELECT * FROM ipi_pedidos WHERE cod_pedidos = '".$cod_pedidos."' ORDER BY cod_pedidos LIMIT 1";
      }
      

      $res_busca_codigo_pedido = mysql_query($sql_busca_codigo_pedido);
      $obj_busca_codigo_pedido = mysql_fetch_object($res_busca_codigo_pedido);
      
      $SqlBuscaPedidosPizzas = 'SELECT * FROM ipi_pedidos_pizzas p INNER JOIN ipi_tamanhos t ON (p.cod_tamanhos = t.cod_tamanhos) WHERE p.cod_pedidos = '.$obj_busca_codigo_pedido->cod_pedidos.' ORDER BY cod_pedidos_pizzas';
      $resBuscaPedidosPizzas = mysql_query($SqlBuscaPedidosPizzas);


      // Criado duas variáveis (gastos_promocionais, gastos_fidelidade) para impedir repetir pedidos c
      $gastos_promocionais=0;
      $gastos_fidelidade=0;
      // Variáveis de controle.

      $num_pizza = 1;
      while($objBuscaPedidosPizzas = mysql_fetch_object($resBuscaPedidosPizzas)) 
      {
        if ($objBuscaPedidosPizzas->promocional)
          $gastos_promocionais+=1;
        if ($objBuscaPedidosPizzas->fidelidade)
          $gastos_fidelidade+=1;

        $objBuscaPedidosBorda = executaBuscaSimples("SELECT * FROM ipi_pedidos_bordas p INNER JOIN ipi_bordas b ON (p.cod_bordas = b.cod_bordas) WHERE p.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND p.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas, $conexao);
        if ($objBuscaPedidosBorda->promocional)
          $gastos_promocionais+=1;
        if ($objBuscaPedidosBorda->fidelidade)
          $gastos_fidelidade+=1;

        $objBuscaPedidosAdicional = executaBuscaSimples("SELECT * FROM ipi_pedidos_adicionais p INNER JOIN ipi_adicionais a ON (p.cod_adicionais = a.cod_adicionais) WHERE p.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND p.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas, $conexao);
        if ($objBuscaPedidosAdicional->promocional)
          $gastos_promocionais+=1;
        if ($objBuscaPedidosAdicional->fidelidade)
          $gastos_fidelidade+=1;

        $objBuscaTipoMassa = executaBuscaSimples("SELECT * FROM ipi_tipo_massa tm INNER JOIN ipi_tamanhos_ipi_tipo_massa tt ON (tm.cod_tipo_massa = tt.cod_tipo_massa) WHERE tm.cod_tipo_massa = '".$objBuscaPedidosPizzas->cod_tipo_massa."' AND tt.cod_tamanhos = '".$objBuscaPedidosPizzas->cod_tamanhos."'", $conexao);
        if ($objBuscaTipoMassa->promocional)
          $gastos_promocionais+=1;
        if ($objBuscaTipoMassa->fidelidade)
          $gastos_fidelidade+=1;

        $objBuscaCorte = executaBuscaSimples("SELECT * FROM ipi_opcoes_corte oc INNER JOIN ipi_tamanhos_ipi_opcoes_corte toc ON (oc.cod_opcoes_corte = toc.cod_opcoes_corte) WHERE oc.cod_opcoes_corte = '".$objBuscaPedidosPizzas->cod_opcoes_corte."' AND toc.cod_tamanhos = '".$objBuscaPedidosPizzas->cod_tamanhos."'", $conexao);
        if ($objBuscaCorte->promocional)
          $gastos_promocionais+=1;
        if ($objBuscaCorte->fidelidade)
          $gastos_fidelidade+=1;

        $SqlBuscaPedidosFracoes = "SELECT * FROM ipi_pedidos_fracoes fr INNER JOIN ipi_pizzas p ON (fr.cod_pizzas = p.cod_pizzas) WHERE fr.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND fr.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas." ORDER BY fracao";
        $resBuscaPedidosFracoes = mysql_query($SqlBuscaPedidosFracoes);

        while($objBuscaPedidosFracoes = mysql_fetch_object($resBuscaPedidosFracoes)) 
        {

          $SqlBuscaPedidosIngredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = ".$obj_busca_codigo_pedido->cod_pedidos." AND pi.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas." AND pi.cod_pedidos_fracoes = ".$objBuscaPedidosFracoes->cod_pedidos_fracoes." AND pi.ingrediente_padrao = 1) AND i.consumo != 1 AND p.cod_pizzas = ".$objBuscaPedidosFracoes->cod_pizzas.' ORDER BY ingrediente';
          $resBuscaPedidosIngredientes = mysql_query($SqlBuscaPedidosIngredientes);

          while($objBuscaPedidosIngredientes = mysql_fetch_object($resBuscaPedidosIngredientes)) 
          {
            if ($objBuscaPedidosExtra->promocional)
              $gastos_promocionais+=1;
            if ($objBuscaPedidosExtra->fidelidade)
              $gastos_fidelidade+=1;
          }
          $SqlBuscaPedidosExtra = "SELECT * FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_ingredientes ig ON (pi.cod_ingredientes = ig.cod_ingredientes) WHERE pi.ingrediente_padrao = 0 AND pi.cod_pedidos_pizzas = ".$objBuscaPedidosFracoes->cod_pedidos_pizzas." AND pi.cod_pedidos = ".$objBuscaPedidosFracoes->cod_pedidos." AND ig.consumo != 1 AND pi.cod_pedidos_fracoes = ".$objBuscaPedidosFracoes->cod_pedidos_fracoes.' ORDER BY ingrediente';
          $resBuscaPedidosExtra = mysql_query($SqlBuscaPedidosExtra);
          while($objBuscaPedidosExtra = mysql_fetch_object($resBuscaPedidosExtra)) 
          {
            if ($objBuscaPedidosExtra->promocional)
              $gastos_promocionais+=1;
            if ($objBuscaPedidosExtra->fidelidade)
              $gastos_fidelidade+=1;
          }
        }
        $num_pizza++;
      }


      $SqlBuscaPedidosBebidas = "SELECT * FROM ipi_pedidos_bebidas p INNER JOIN ipi_bebidas_ipi_conteudos bc ON (p.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) WHERE cod_pedidos = ".$obj_busca_codigo_pedido->cod_pedidos;
      $resBuscaPedidosBebidas = mysql_query($SqlBuscaPedidosBebidas);
      while($objBuscaPedidosBebidas = mysql_fetch_object($resBuscaPedidosBebidas)) 
      {
        if ($objBuscaPedidosBebidas->promocional)
          $gastos_promocionais+=1;
        if ($objBuscaPedidosBebidas->fidelidade)
          $gastos_fidelidade+=1;

      }

      desconectabd($conexao);
      if (($gastos_promocionais)||($gastos_fidelidade))
      {
        return true;
      }
      else
      {
        return false;
      }

    }

  }

  ?>