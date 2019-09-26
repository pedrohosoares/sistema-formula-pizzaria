<?
session_start();
require_once '../../config.php';

require_once '../../bd.php';


function arredondar_preco_ingrediente ($preco_ingrediente, $numero_divisoes)
{
  $preco_dividido = $preco_ingrediente / $numero_divisoes;
  $resto = ceil($preco_dividido) - $preco_dividido;
  if ($resto >= 0.50)
  {
    $preco = floor($preco_dividido) + 0.50 + 0.50;
  }
  else
    $preco = ceil($preco_dividido) + ($numero_divisoes ==1 ? 0 : 0.50) ;
  return $preco;
}

function arredondar_preco_ingrediente_antigo ($preco_ingrediente, $numero_divisoes)
{
  $preco_dividido = $preco_ingrediente / $numero_divisoes;
  $resto = ceil($preco_dividido) - $preco_dividido;
  if ($resto > 0.50)
  {
    $preco = floor($preco_dividido) + $resto -0.50;
  }
  else 
    $preco = $preco_dividido ;
  return $preco;
}


class ipi_caixa
{
  private $car_versao = "1.0";

    /**
     * Função que retorna a numeração da versão do carrinho.
     */
    public function versao ()
    {
      return $this->car_versao;
    }
    
    /**
     * Função que retorna a numeração da versão do carrinho.
     */
    public function existe_pedido()
    {
      $ret = isset($_SESSION['ipi_caixa']['pedido']) ? 1 : (isset($_SESSION['ipi_caixa']['bebida']) ? 1 : 0);
      return $ret;
    }    
    
    /**
     * Função que retorna um id de sessão para cada item.
     */
    public function gerar_id_sessao()
    {
      if ($_SESSION['ipi_caixa']['id_sessao_atual']!="") 
      {
        $ret = $_SESSION['ipi_caixa']['id_sessao_atual'] + 1;
      }
      else
      {
        $ret = 1;
      }

      $_SESSION['ipi_caixa']['id_sessao_atual'] = $ret;

      return $ret;
    }    
    
    /**
     * Função que apaga o pedido da sessão.
     */
    public function apagar_pedido()
    {
      unset($_SESSION['ipi_caixa']);
      #unset($_SESSION['refIfood']);
      #unset($_SESSION['pIfood']);
      #unset($_SESSION['pedido_ifood_json']);
      $_SESSION['ipi_caixa']['entregac'] = "Entrega";
      $_SESSION['ipi_caixa']['pizzaria_atual'] = $_SESSION['usuario']['cod_pizzarias'][0];

    }

    /**
     * Função que registra LOG geral do sistema.
     * o parametro palavra_chave deve seguir a seguinte tabela:
     * 
     * PEDIDO_INSERIDO, PEDIDO_TEL_INSERIDO, ERRO_LOGIN_PUBLICO,
     */
    public function log($var_conexao, $palavra_chave, $valor, $cod_pedidos = 0, $cod_usuarios = 0, $cod_clientes = 0)
    {
      if (!$var_conexao)
        $conexao = conectabd();

      $sqlAux = "INSERT INTO ipi_log (data_hora, cod_usuarios, cod_pedidos, cod_clientes, palavra_chave, valor) VALUES (NOW(), $cod_usuarios, $cod_pedidos, $cod_clientes, '$palavra_chave', '$valor' )";
      $resAux = mysql_query($sqlAux);

        //echo "SQL: ".$sqlAux;


      if (!$var_conexao)
        desconectabd($conexao);
    }
    

    /**
     * Função que exibe o calcula e retorna total do pedido.
     */    
    public function calcular_total ()
    {

      $total_carrinho = 0;
      $arr_combo = array();

      $conexao = conectabd();

      $cod_pizzarias = $_SESSION['ipi_caixa']['pizzaria_atual'];
        //echo $cod_pizzarias;
      $numero_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
      if ($numero_pizzas > 0)
      {
        for ($a = 0; $a < $numero_pizzas; $a++)
        {
          $preco_pizza = 0;
          $sqlAux = "SELECT fp.preco,fp.cod_tamanhos FROM ipi_tamanhos_ipi_fracoes fp inner join ipi_fracoes f on f.cod_fracoes = fp.cod_fracoes WHERE fp.cod_pizzarias = '$cod_pizzarias' and fp.cod_tamanhos=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] . " AND f.fracoes=" . $_SESSION['ipi_caixa']['pedido'][$a]['quantidade_fracoes'];
              //echo "<br>1: ".$sqlAux;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          $cod_tamanhos = $objAux->cod_tamanhos;
          $preco_divisao_fracao = $objAux->preco;
          $total_carrinho += $preco_divisao_fracao;
          $preco_pizza += $preco_divisao_fracao;
          $_SESSION['ipi_caixa']['pedido'][$a]['preco_divisao'] = $preco_divisao_fracao;
              //############################
              //echo "<br><br>Divisao: ".$preco_divisao_fracao;
              //echo "<br>Total: ".$total_carrinho;

          if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] != "N")
          {
            $sqlAux = "SELECT preco FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] . " AND cod_tamanhos=" . $cod_tamanhos . " AND cod_pizzarias = " . $cod_pizzarias;
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            if (($_SESSION['ipi_caixa']['pedido'][$a]['borda_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['borda_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['borda_combo'] != "1") )
            {
              $preco_borda = $objAux->preco;
            }
            else
            {
              $preco_borda = 0;
            }
            $_SESSION['ipi_caixa']['pedido'][$a]['preco_borda'] = $preco_borda;
            $total_carrinho += $preco_borda;
            $preco_pizza += $preco_borda;

              //############################
              //echo "<br><br>Borda: ".$preco_borda;
              //echo "<br>Total: ".$total_carrinho;
          }

          if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa'] != '')
          {
            $sqlAux = "SELECT preco FROM ipi_tamanhos_ipi_tipo_massa WHERE cod_tipo_massa=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa'] . " AND cod_tamanhos=" . $cod_tamanhos;
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            $preco_massa = $objAux->preco;
            $total_carrinho += $preco_massa;
            $preco_pizza += $preco_massa;
            $_SESSION['ipi_caixa']['pedido'][$a]['preco_massa'] = $preco_massa;
              //############################
              //echo "<br><br>Massa: ".$preco_massa;
              //echo "<br>Total: ".$total_carrinho;
          }

          if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] != "N")
          {
            $sqlAux = "SELECT preco FROM ipi_tamanhos_ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] . " AND cod_tamanhos=" . $cod_tamanhos . " AND cod_pizzarias = ".$cod_pizzarias;
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            $total_carrinho += $objAux->preco;
            $preco_pizza += $objAux->preco;
            $_SESSION['ipi_caixa']['pedido'][$a]['preco_adicional'] = $objAux->preco;
          }
              //############################
              //echo "<br><br>Adic: ".$objAux->preco;
              //echo "<br>Total: ".$total_carrinho;
              //echo "<br>sqlAux: ".$sqlAux;


          $num_fracoes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao']);
          $_SESSION['ipi_caixa']['pedido'][$a]['preco_total_fracoes'] = 0;
          $preco_fracao_maior = 0;
          for ($b = 0; $b < $num_fracoes; $b++)
          {
            $cod_pizzas = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
            $num_fracao = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['num_fracao'];
            $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos. " AND pt.cod_pizzarias = ".$cod_pizzarias;
                  //echo  "<br>sqlAux: ".$sqlAux;
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);

            if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] == "1")
            {
              $preco_fracao = 0;
            }
            else
            {
              $preco_fracao = $objAux->preco;
            }

            if($preco_fracao > $preco_fracao_maior)
            {
              $preco_fracao_maior = $preco_fracao;
            }
          }

          for ($b = 0; $b < $num_fracoes; $b++)
          {
            $cod_pizzas = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
            $num_fracao = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['num_fracao'];

            if($cod_pizzas > 0)
            {

              $sqlAux = "SELECT pt.preco FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos . " AND pt.cod_pizzarias = ".$cod_pizzarias;
                      //echo "<br>$sqlAux";
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);
              $preco_fracao = ($objAux->preco / $num_fracoes);
              if (($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] != "1"))
              {
                if($_SESSION['ipi_caixa']['pedido'][$a]['preco_promocional']=="1" && $_SESSION['ipi_caixa']['pedido'][$a]['valor_preco_promocional'] !="")
                {
                 $preco_fracao = ($_SESSION['ipi_caixa']['pedido'][$a]['valor_preco_promocional'] / $num_fracoes);
               }
               else
               {
                if(REGRA_PRECO_DIVISAO_PIZZA=="IGUALMENTE")
                {
                  $preco_fracao = ($objAux->preco / $num_fracoes);
                }
                else if(REGRA_PRECO_DIVISAO_PIZZA=="MAIOR")
                {
                  $preco_fracao = ($preco_fracao_maior / $num_fracoes);
                }
                            //ADD POR PEDRO PARA EVITAR A OSCILAÇÃO DE VALORES
                $preco_fracao = ($objAux->preco / $num_fracoes);

                if($_SESSION['ipi_caixa']['pedido'][$a]['porcentagem_promocional']=="1" && $_SESSION['ipi_caixa']['pedido'][$a]['valor_porcentagem_promocional']!="")
                {
                  $preco_fracao = ($preco_fracao*$_SESSION['ipi_caixa']['pedido'][$a]['valor_porcentagem_promocional'])/100;
                }

              }
            }
            else
            {
              $preco_fracao = 0;

              if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] == "1")
              {

                if (!in_array($_SESSION['ipi_caixa']['pedido'][$a]['id_combo'], $arr_combo)) 
                {                       
                  $arr_combo[] = $_SESSION['ipi_caixa']['pedido'][$a]['id_combo'];
                  $sqlAux = "SELECT cp.preco FROM ipi_combos c INNER JOIN ipi_combos_pizzarias cp ON (cp.cod_combos = c.cod_combos) WHERE c.cod_combos='" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']."' AND c.situacao='ATIVO' AND cp.cod_pizzarias = '".$cod_pizzarias."'";
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);

                  $preco_fracao = $objAux->preco;
                }
              }                    
            }
            $total_carrinho += $preco_fracao;
            $preco_pizza += $preco_fracao;
                      //############################
                      //echo "<br><br>Fraça: ".$preco_fracao;
                      //echo "<br>Total: ".$total_carrinho;

            $_SESSION['ipi_caixa']['pedido'][$a]['preco_total_fracoes'] += $preco_fracao;
            $num_ingredientes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes']);
            for ($c = 0; $c < $num_ingredientes; $c++)
            {
              $sqlAux = "SELECT it.preco FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) WHERE it.cod_tamanhos=" . $cod_tamanhos . " AND i.cod_ingredientes=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'] . " AND it.cod_pizzarias = " . $cod_pizzarias;
                        //echo "<br/>".$sqlAux."<br/>";
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);

              if ($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == false)
              {
                if($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_troca'] == false)
                {
                  if($_SESSION['ipi_caixa']['pedido'][$a]['adicionais_inteira'])
                  {
                    $preco_ingrediente_extra = arredondar_preco_ingrediente_antigo($objAux->preco, $num_fracoes);
                               //echo "asdafed".$objAux->preco."  ".$num_fracoes."<br/><br/>";
                  }
                  else
                  {
                    $preco_ingrediente_extra = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                  }
                }
                else
                {
                  $sqlAux = "SELECT it.preco_troca FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) WHERE it.cod_tamanhos=" . $cod_tamanhos . " AND i.cod_ingredientes=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes_troca'] . " AND it.cod_pizzarias = " . $cod_pizzarias;
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);
                  $preco_ingrediente_extra = arredondar_preco_ingrediente($objAux->preco_troca, $num_fracoes);
                }
                $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['preco'] = $preco_ingrediente_extra;
                $total_carrinho += $preco_ingrediente_extra;
                $preco_pizza += $preco_ingrediente_extra;
                          //############################
                          //echo "<br><br>Extra: ".$preco_ingrediente_extra;
                          //echo "<br>Total: ".$total_carrinho;
              }
            }
          }
        }
        $_SESSION['ipi_caixa']['pedido'][$a]['preco_pizza'] = $preco_pizza;
      }

    }

    $numero_bebidas = isset($_SESSION['ipi_caixa']['bebida']) ? count($_SESSION['ipi_caixa']['bebida']) : 0;
    if ($numero_bebidas > 0)
    {

      for ($a = 0; $a < $numero_bebidas; $a++)
      {
        if ( (!$_SESSION['ipi_caixa']['bebida'][$a]['bebida_promocional']) && ($_SESSION['ipi_caixa']['bebida'][$a]['bebida_combo'] != "1") )
        {
          $cod_bebidas_ipi_conteudos = $_SESSION['ipi_caixa']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
          $sqlAux = "SELECT cp.preco FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') INNER JOIN ipi_bebidas b ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $cod_bebidas_ipi_conteudos;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          $preco_bebida = $objAux->preco;
          $quantidade_bebida = $_SESSION['ipi_caixa']['bebida'][$a]['quantidade'];
          $total_carrinho += ($preco_bebida * $quantidade_bebida);
          $_SESSION['ipi_caixa']['bebida'][$a]['preco_bebida'] = ($preco_bebida * $quantidade_bebida);

        }
      }
    }

    desconectabd($conexao);

    $_SESSION['ipi_caixa']['total_pedido'] = $total_carrinho;

    return (bd2moeda($_SESSION['ipi_caixa']['total_pedido']));
  }


    /**
     * Função que cria uma pizza na sessão e retorna o o indice da pizza na sessão.
     */
    public function adicionar_pizza ($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $cod_opcoes_corte, $quantidade_fracoes, $pizza_promocional, $borda_promocional, $cod_motivo_promocoes_pizza, $cod_motivo_promocoes_borda, $id_combo)
    {
      $proximo_indice = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;

      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['cod_tamanhos'] = $cod_tamanhos;
      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['cod_adicionais'] = $cod_adicionais;
      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['cod_bordas'] = $cod_bordas;
      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['cod_tipo_massa'] = $cod_tipo_massa;
      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['cod_opcoes_corte'] = $cod_opcoes_corte;
      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['quantidade_fracoes'] = $quantidade_fracoes;
      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['pizza_promocional'] = $pizza_promocional;
      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['borda_promocional'] = $borda_promocional;
      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['cod_motivo_promocoes_pizza'] = $cod_motivo_promocoes_pizza;
      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['cod_motivo_promocoes_borda'] = $cod_motivo_promocoes_borda;
      $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['pizza_id_sessao'] = $this->gerar_id_sessao();


      if (($_SESSION['ipi_caixa']['combo']['qtde_bordas']>0)&&($cod_bordas != "0"))             
      {    
        $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['borda_combo'] = '1';
        $_SESSION['ipi_caixa']['combo']['qtde_bordas'] = $_SESSION['ipi_caixa']['combo']['qtde_bordas'] - 1;
      }



      if ($cod_bordas)
      {
        $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['borda_id_sessao'] = $this->gerar_id_sessao();
      }
      if ($cod_adicionais)
      {
        $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['adicional_id_sessao'] = $this->gerar_id_sessao();
      }
      if ($id_combo!='')
      {
        $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['pizza_combo'] = '1';
        $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['id_combo'] = $id_combo;
        $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['cod_combos'] = $_SESSION['ipi_caixa']['combo']['cod_combos'];

      }
      else
      {
        $_SESSION['ipi_caixa']['pedido'][$proximo_indice]['pizza_combo'] = '0';
      }


      $this->calcular_total();

      return $proximo_indice;
    }    
    
    
    /**
     * Função que cria uma fração com o indice da pizza passado como paramentro.
     */
    public function adicionar_fracao ($indice_ses_pizza, $cod_pizzas, $num_fracao, $observacao)
    {

      $proximo_indice = isset($_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao']) ? count($_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao']) : 0;
      $_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$proximo_indice]['indice_ses_pizza'] = $indice_ses_pizza;
      $_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$proximo_indice]['cod_pizzas'] = $cod_pizzas;
      $_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$proximo_indice]['num_fracao'] = $num_fracao;
      $_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$proximo_indice]['observacao'] = $observacao;

      $this->calcular_total();

      return $proximo_indice;
    }
    

    /**
     * Função que adicionar um ingrediente a uma fracao da pizza pelo indice da fracao passado como parametro.
     */
    public function adicionar_ingrediente ($indice_ses_pizza, $indice_ses_fracao, $cod_ingredientes, $ingrediente_padrao,$ingrediente_troca = false,$cod_ingredientes_troca = 0)
    {
      $proximo_indice = isset($_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes']) ? count($_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes']) : 0;
      $_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['cod_ingredientes'] = $cod_ingredientes;
      $_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['ingrediente_padrao'] = $ingrediente_padrao;

      $_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['cod_ingredientes_troca'] = $cod_ingredientes_troca;
      $_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['ingrediente_troca'] = $ingrediente_troca;

      $_SESSION['ipi_caixa']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['ingrediente_id_sessao'] = $this->gerar_id_sessao();
      $this->calcular_total();
    }    
    

    
    /**
     * Função que cria uma bebida na sessão .
     */
    public function adicionar_bebida($cod_bebidas_ipi_conteudos, $quantidade, $bebida_promocional, $cod_motivo_promocoes_bebida, $id_combo)
    {
      $proximo_indice = isset($_SESSION['ipi_caixa']['bebida']) ? count($_SESSION['ipi_caixa']['bebida']) : 0;
      $_SESSION['ipi_caixa']['bebida'][$proximo_indice]['cod_bebidas_ipi_conteudos'] = $cod_bebidas_ipi_conteudos;
      $_SESSION['ipi_caixa']['bebida'][$proximo_indice]['quantidade'] = $quantidade;
      $_SESSION['ipi_caixa']['bebida'][$proximo_indice]['bebida_promocional'] = $bebida_promocional;
      $_SESSION['ipi_caixa']['bebida'][$proximo_indice]['cod_motivo_promocoes_bebida'] = $cod_motivo_promocoes_bebida;
      $_SESSION['ipi_caixa']['bebida'][$proximo_indice]['bebida_id_sessao'] = $this->gerar_id_sessao();


      if ($id_combo!='')
      {
        $_SESSION['ipi_caixa']['bebida'][$proximo_indice]['bebida_combo'] = '1';
        $_SESSION['ipi_caixa']['bebida'][$proximo_indice]['id_combo'] = $id_combo;
        $_SESSION['ipi_caixa']['bebida'][$proximo_indice]['cod_combos'] = $_SESSION['ipi_caixa']['combo']['cod_combos'];
      }
      else
      {
        $_SESSION['ipi_caixa']['bebida'][$proximo_indice]['bebida_combo'] = '0';
      }        
    }
    
    
    /*
   * Função que exibe o pedido completo.
     */
    public function exibir_pedido ()
    {
      $this->calcular_total();
      echo "<script>";
      echo "function confirmar_excluir_pizza(frm, tipo)";
      echo "{";
      echo "if (confirm('Deseja realmente excluir esta ' + tipo + ' do seu carrinho?'))";
      echo "  {";
      echo "frm.submit();";
      echo "  }";
      echo "}";
      echo "</script>";



      if ($_SESSION['ipi_caixa']['tipo_cliente']=="ANTIGO")
      {
        $conexao = conectabd();
        $sqlAux = "SELECT c.nome,e.endereco,e.bairro,e.numero,c.celular,e.telefone_1,e.telefone_2,c.observacao ,e.referencia_cliente FROM ipi_clientes c LEFT JOIN ipi_enderecos e ON (c.cod_clientes=e.cod_clientes) WHERE c.cod_clientes=" . $_SESSION['ipi_caixa']['cliente']['cod_clientes'] . " AND e.cod_enderecos=" . $_SESSION['ipi_caixa']['cliente']['cod_enderecos'];
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        if($_SESSION['ipi_caixa']['cliente']['cobertura']=="Fora")
        {
          echo"<b style='color:red;text-align:center'>CEP FORA DA COBERTURA</b><br/>";
        }
        if($_SESSION['ipi_caixa']['pizzaria_atual']!="")
        {
          $sql_buscar_pizzaria = "SELECT p.nome from ipi_pizzarias p where p.cod_pizzarias= '".$_SESSION['ipi_caixa']['pizzaria_atual']."'";
          $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
          $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
          echo "<b>Pizzaria:</b> ".$obj_buscar_pizzaria->nome."<br/>";
        }
        echo "<b>Destino:</b> ".$_SESSION['ipi_caixa']['entregac']."<br/>";
        echo "<b>Cliente:</b> ".$objAux->nome;
        echo "<br><b>Endereço:</b> ".$objAux->endereco.', '.$objAux->numero;
        echo "<br><b>Bairro:</b> ".$objAux->bairro;
           // echo "<br><b>Cel. :</b> ".$objAux->celular;
        echo "<br><b>Tel. 1:</b> ".$objAux->telefone_1;
        echo "<br><b>Tel. 2:</b> ".$objAux->telefone_2;
        echo "<br><b>Pt. Ref.:</b> ".$objAux->referencia_cliente;
        echo "<br><b>OBS Cliente:</b> ".$objAux->observacao;
        desconectabd($conexao);
      }

      if ($_SESSION['ipi_caixa']['tipo_cliente']=="NOVO")
      {
        if($_SESSION['ipi_caixa']['cliente']['cobertura']=="Fora")
        {
          echo"<b style='color:red;text-align:center'>CEP FORA DA COBERTURA</b><br/>";
        }
        if($_SESSION['ipi_caixa']['pizzaria_atual']!="")
        {
          $conexao = conectabd();
          $sql_buscar_pizzaria = "SELECT p.nome from ipi_pizzarias p where p.cod_pizzarias= '".$_SESSION['ipi_caixa']['pizzaria_atual']."'";
          $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
          $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);

              //echo "<br/><br/>".$sql_buscar_pizzaria."<br/>";
          echo "<b>Pizzaria:</b> ".$obj_buscar_pizzaria->nome."<br/>";
          echo "<b>Destino:</b> ".$_SESSION['ipi_caixa']['entregac']."<br/>";
        }
        echo "<b>Cliente:</b> ".$_SESSION['ipi_caixa']['cliente']['nome'];
        echo "<br><b>Endereço:</b> ".$_SESSION['ipi_caixa']['cliente']['endereco'].', '.$_SESSION['ipi_caixa']['cliente']['numero'];
        echo "<br><b>Bairro:</b> ".$_SESSION['ipi_caixa']['cliente']['bairro'];
            //echo "<br><b>Cel. :</b> ".$_SESSION['ipi_caixa']['cliente']['celular'];
        echo "<br><b>Tel. 1:</b> ".$_SESSION['ipi_caixa']['cliente']['telefone_1'];
        echo "<br><b>Tel. 2:</b> ".$_SESSION['ipi_caixa']['cliente']['telefone_2'];
        echo "<br><b>Pt. Ref.:</b> ".$_SESSION['ipi_caixa']['cliente']['ref_cliente'];
        echo "<br><b>OBS Cliente:</b> ".$_SESSION['ipi_caixa']['cliente']['obs_cliente'];
        desconectabd($conexao);
      }

      $numero_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;

      if($_SESSION['ipi_caixa']['cliente']['preco_frete']>0)
      {
        $entrega = $_SESSION['ipi_caixa']['cliente']['preco_frete'];
      }
      else
      {
        $entrega = '0';
      }

      if ($numero_pizzas > 0)
      {

        echo "<h2 style='text-align:center'><b>Pedido Atual</b></h2>";

        if (($numero_pizzas) || ($numero_bebidas))
        {
          echo "<center><b><font color='#FF0000'>Total do pedido: <br /><font color='#FF0000' size='4'>R$ " . bd2moeda(moeda2bd($this->calcular_total()) + $entrega)  . "</font></font></b></center>";
        }
        else
        {
          echo "<br /><center><b>Pedido vazio!</b></center>";
        }

        $conexao = conectabd();
        $num_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
        for ($a = 0; $a < $num_pizzas; $a++)
        {
          $preco_pizza = $_SESSION['ipi_caixa']['pedido'][$a]['preco_pizza'];
          $tem_preco = true;
          echo '<h6>' . ($a + 1) . 'ª Pizza';
          if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] == "1")
          {
            $tem_preco = false;
            echo " (GRÁTIS)";
          }
          if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] == "1")
          {
            $tem_preco = false;
            echo " (FIDELIDADE)";
          }
          if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] == "1")
          {
            $tem_preco = false;
            echo " (COMBO)";
          }   
          if($tem_preco)
          {
            echo " ( <span style='color:red'>R$ ".bd2moeda($preco_pizza)."</span>  )";
          }
          echo '</h6>';

                /*
                  //Excluir temparariamente removido
                echo '<form method="post" action="ipi_req_carrinho_acoes.php" name="frmExcluirPizza_' . $a . '" style="margin: 0px">';
                echo '<input type="hidden" name="ind_ses" value="' . $a . '">';
                echo '<input type="hidden" name="acao" value="excluir_pizza">';
                echo '</form>';
                echo "<div style='text-align: right'><a href='javascript:confirmar_excluir_pizza(document.frmExcluirPizza_{$a},\"pizza\");'>Remover</a></div>";
                */

                $sqlAux = "SELECT tamanho FROM ipi_tamanhos WHERE cod_tamanhos=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                echo '<b>Tamanho:</b> ' . $objAux->tamanho;
                echo '<br><b>Quantidade de Sabores:</b> ' . $_SESSION['ipi_caixa']['pedido'][$a]['quantidade_fracoes'];
                
                if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] != "0")
                {
                  $sqlAux = "SELECT borda FROM ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'];
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);
                  echo '<br><b>Borda:</b> ' . $objAux->borda;
                  if ($_SESSION['ipi_caixa']['pedido'][$a]['borda_promocional'] == "1")
                    echo " (GRÁTIS)";
                  if ($_SESSION['ipi_caixa']['pedido'][$a]['borda_fidelidade'] == "1")
                    echo " (FIDELIDADE)";
                  if ($_SESSION['ipi_caixa']['pedido'][$a]['borda_combo'] == "1")
                    echo " (COMBO)";
                }
                else
                {
                  echo '<br><b>Borda:</b> Não';
                }
                
/*                if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] != "0")
                {
                    $sqlAux = "SELECT adicional FROM ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'];
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    echo '<br><b>Gergelim:</b> ' . $objAux->adicional;
                }
                else
                {
                    echo '<br><b>Gergelim:</b> Não';
                  }*/


                  $sqlAux = "SELECT tm.tipo_massa,tt.preco FROM ipi_tipo_massa tm INNER JOIN ipi_tamanhos_ipi_tipo_massa tt ON (tm.cod_tipo_massa = tt.cod_tipo_massa) WHERE tm.cod_tipo_massa = ".$_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa']." AND tt.cod_tamanhos = ".$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'];
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);
                  echo '<br><b>Tipo da Massa:</b> '.$objAux->tipo_massa;
                  if($objAux->preco > 0)
                  {
                    echo '&nbsp;('.bd2moeda($objAux->preco).')';   
                  }


                  $sqlAux = "SELECT oc.opcao_corte,toc.preco FROM ipi_opcoes_corte oc INNER JOIN ipi_tamanhos_ipi_opcoes_corte toc ON (oc.cod_opcoes_corte = toc.cod_opcoes_corte) WHERE oc.cod_opcoes_corte = ".$_SESSION['ipi_caixa']['pedido'][$a]['cod_opcoes_corte']." AND toc.cod_tamanhos = ".$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'];
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);
                  echo '<br><b>Corte:</b> '.$objAux->opcao_corte;
                  if($objAux->preco > 0)
                  {
                    echo '&nbsp;('.bd2moeda($objAux->preco).')';   
                  }


                  $num_fracoes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao']);
                  for ($b = 0; $b < $num_fracoes; $b++)
                  {
                    if($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'] > 0)
                    {

                      $sqlAux = "SELECT pizza FROM ipi_pizzas WHERE cod_pizzas=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                      $resAux = mysql_query($sqlAux);
                      $objAux = mysql_fetch_object($resAux);
                      echo "<br><br><b>" . ($b + 1) . "º Sabor:</b> " . $objAux->pizza;

                      $num_ingredientes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes']);
                      $ingredientes_padroes = array ();
                      $ingredientes_nao_padroes = array ();
                      $ind_aux_padrao = 0;
                      $ind_aux_nao_padrao = 0;
                      for ($c = 0; $c < $num_ingredientes; $c++)
                      {
                            /*$sqlAux = "SELECT * FROM ipi_ingredientes WHERE cod_ingredientes=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                            $resAux = mysql_query($sqlAux);
                            $objAux = mysql_fetch_object($resAux);*/
                            
                            if ($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == true)
                            {
                              $ingredientes_padroes[$ind_aux_padrao] = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                              $ind_aux_padrao++;
                            }
                            if ($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == false)
                            {
                              $ingredientes_nao_padroes[$ind_aux_nao_padrao] = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                              $ind_aux_nao_padrao++;
                            }
                          }

                          if (count($ingredientes_padroes) > 0)
                          {
                            $sqlAux = "SELECT i.ingrediente FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (ip.cod_ingredientes=i.cod_ingredientes) WHERE ip.cod_pizzas=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'] . " AND ip.cod_ingredientes NOT IN (" . implode(",", $ingredientes_padroes) . ") AND i.consumo != 1";
                            $resAux = mysql_query($sqlAux);
                            $linAux = mysql_num_rows($resAux);
                            //echo $sqlAux;
                            if ($linAux > 0)
                            {
                              echo "<br><b>Ingredientes:</b> ";
                              while ($objAux = mysql_fetch_object($resAux))
                              {
                                echo "SEM " . $objAux->ingrediente . ", ";
                              }
                            }
                          }

                          if (count($ingredientes_nao_padroes) > 0)
                          {
                            $sqlAux = "SELECT i.ingrediente FROM ipi_ingredientes i WHERE i.cod_ingredientes IN (" . implode(",", $ingredientes_nao_padroes) . ")";
                            $resAux = mysql_query($sqlAux);
                            $linAux = mysql_num_rows($resAux);
                            if ($linAux > 0)
                            {
                              echo "<br><b>Adicionais:</b> ";
                              while ($objAux = mysql_fetch_object($resAux))
                              {
                                echo $objAux->ingrediente . ", ";
                              }
                            }
                          }
                          else
                          {
                            echo "<br><b>Adicionais:</b> Sem adicionais";
                          }

                        }

                      }
                    }



                    desconectabd($conexao);
                  }


                  $numero_bebidas = isset($_SESSION['ipi_caixa']['bebida']) ? count($_SESSION['ipi_caixa']['bebida']) : 0;
                  if ($numero_bebidas > 0)
                  {

                    echo "<br><h6>BEBIDAS:</h6> ";
                    $conexao = conectabd();

                    for ($a = 0; $a < $numero_bebidas; $a++)
                    {
                      $sqlAux = "SELECT b.bebida,c.conteudo FROM ipi_bebidas b INNER JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $_SESSION['ipi_caixa']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                      $resAux = mysql_query($sqlAux);
                      $objAux = mysql_fetch_object($resAux);
                /*
                // Excluir temporariamente removido.  
                echo '<form method="post" action="ipi_req_carrinho_acoes.php" name="frmExcluirBebida_' . $a . '" style="margin: 0px">';
                echo '<input type="hidden" name="ind_ses" value="' . $a . '">';
                echo '<input type="hidden" name="acao" value="excluir_bebida">';
                echo '</form>';
                echo "<div style='text-align: right'><a href='javascript:;' onClick='confirmar_excluir_pizza(document.frmExcluirBebida_{$a},\"bebida\");'>Remover</a></div>";
                */
                $preco_bebida = $_SESSION['ipi_caixa']['bebida'][$a]['preco_bebida'];
                $tem_preco = true;

                echo '<b>Quantidade:</b> ' . $_SESSION['ipi_caixa']['bebida'][$a]['quantidade'];

                if ($_SESSION['ipi_caixa']['bebida'][$a]['bebida_promocional'] == "1")
                {
                  $tem_preco = false;
                  echo " (GRÁTIS)";
                }
                if ($_SESSION['ipi_caixa']['bebida'][$a]['bebida_fidelidade'] == "1")
                {
                  $tem_preco = false;
                  echo " (FIDELIDADE)";
                }
                if ($_SESSION['ipi_caixa']['bebida'][$a]['bebida_combo'] == "1")
                {
                  $tem_preco = false;
                  echo " (COMBO)";
                }
                if($tem_preco)
                {
                  echo " ( <span style='color:red'>R$ ".bd2moeda($preco_bebida)."</span> ) ";
                }
                echo '<br><b>Sabor:</b> ' . $objAux->bebida . " - " . $objAux->conteudo . "<hr />";
              }

              desconectabd($conexao);
            }

            if ($entrega>0)
            {
              echo "<br/><b>Frete: <span style='color:red'>R$ ".bd2moeda($entrega)."</span></b> "; 
            }

            if (($numero_pizzas) || ($numero_bebidas))
            {
              echo "<br /><br /><center><b><font color='#FF0000'>Total do pedido: <br /><font color='#FF0000' size='4'>R$ " . bd2moeda(moeda2bd($this->calcular_total()) + $entrega) . "</font></font></b></center>";
            }
            else
            {
              echo "<br /><center><b>Pedido vazio!</b></center>";
            }

          }    




    /**
     * Função que finaliza o pedido, transfere a SESSION para o BD e retorna o numero do pedido.
     */
    
    public function finalizar_pedido ($cod_pizzarias, $obs_pedido, $cpf_nota_fiscal, $forma_pg, $valor_formas, $tipo_entrega, $horario_agendamento, $troco, $desconto,$frete = 0,$comissao_frete = 0)
    {
        //die($forma_pg);
      $conexao = conectabd();
      require("../../pub_req_fuso_horario1.php");
      
      if ($_SESSION['ipi_caixa']['tipo_cliente']=="NOVO")
      {
            // $sql_novo_cliente = sprintf("INSERT INTO ipi_clientes (cod_onde_conheceu, nome, email, cpf, nascimento, celular, cod_clientes_indicador, indicador_recebeu_pontos, origem_cliente,observacao,data_hora_cadastro, sexo) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%d', 0, 'TEL', '%s','%s', '%s')",
            //              $_SESSION['ipi_caixa']['cliente']['cod_onde_conheceu'], $_SESSION['ipi_caixa']['cliente']['nome'], $_SESSION['ipi_caixa']['cliente']['email'], $_SESSION['ipi_caixa']['cliente']['cpf'], data2bd($_SESSION['ipi_caixa']['cliente']['nascimento']), $_SESSION['ipi_caixa']['cliente']['celular'], 0,$_SESSION['ipi_caixa']['cliente']['obs_cliente'], date("Y-m-d H:i:s"), $_SESSION['ipi_caixa']['cliente']['sexo']);
            // $res_novo_cliente = mysql_query($sql_novo_cliente);
            // $codigo_novo_cliente = mysql_insert_id();

            // $sql_endereco_cliente = sprintf("INSERT INTO ipi_enderecos (apelido, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2, cod_clientes, referencia_endereco,referencia_cliente) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s')",
            //                   'Endereço Padrão', ($_SESSION['ipi_caixa']['cliente']['endereco']), ($_SESSION['ipi_caixa']['cliente']['numero']), ($_SESSION['ipi_caixa']['cliente']['complemento']), ($_SESSION['ipi_caixa']['cliente']['edificio']), ($_SESSION['ipi_caixa']['cliente']['bairro']), ($_SESSION['ipi_caixa']['cliente']['cidade']), ($_SESSION['ipi_caixa']['cliente']['estado']), ($_SESSION['ipi_caixa']['cliente']['cep']), ($_SESSION['ipi_caixa']['cliente']['telefone_1']), ($_SESSION['ipi_caixa']['cliente']['telefone_2']), $codigo_novo_cliente,($_SESSION['ipi_caixa']['cliente']['ref_endereco']),($_SESSION['ipi_caixa']['cliente']['ref_cliente']));
            // $res_endereco_cliente = mysql_query($sql_endereco_cliente);
            // $codigo_novo_endereco = mysql_insert_id();
            //echo "<br>1: ".$sql_endereco_cliente;

        $sqlCliente = "SELECT * FROM ipi_clientes c INNER JOIN ipi_enderecos e ON (e.cod_clientes=c.cod_clientes) WHERE c.cod_clientes=" . $_SESSION['ipi_caixa']['codigo_novo_cliente'] . " AND e.cod_enderecos=" . $_SESSION['ipi_caixa']['codigo_novo_endereco'];
        $resCliente = mysql_query($sqlCliente);
        $objCliente = mysql_fetch_object($resCliente);
      }
      else if ($_SESSION['ipi_caixa']['tipo_cliente']=="ANTIGO")
      {
        $sql_novo_cliente = sprintf("UPDATE ipi_clientes SET cod_onde_conheceu='%s', nome='%s', email='%s', cpf='%s', nascimento='%s', celular='%s', observacao='%s', sexo = '%s' WHERE cod_clientes = '%s'", $_SESSION['ipi_caixa']['cliente']['cod_onde_conheceu'], $_SESSION['ipi_caixa']['cliente']['nome'], $_SESSION['ipi_caixa']['cliente']['email'], $_SESSION['ipi_caixa']['cliente']['cpf'], data2bd($_SESSION['ipi_caixa']['cliente']['nascimento']), $_SESSION['ipi_caixa']['cliente']['celular'], $_SESSION['ipi_caixa']['cliente']['obs_cliente'], $_SESSION['ipi_caixa']['cliente']['sexo'], $_SESSION['ipi_caixa']['cliente']['cod_clientes']);
        $res_novo_cliente = mysql_query($sql_novo_cliente);
            //echo "<br>1: ".$sql_novo_cliente;

        $sql_endereco_cliente = sprintf("UPDATE ipi_enderecos SET endereco = '%s', numero = '%s', complemento = '%s', edificio = '%s', bairro = '%s', cidade = '%s', estado = '%s', cep = '%s', telefone_1 = '%s', telefone_2 = '%s', cod_clientes = '%s', referencia_endereco = '%s' , referencia_cliente = '%s'  WHERE cod_enderecos = '%s'",
          $_SESSION['ipi_caixa']['cliente']['endereco'], $_SESSION['ipi_caixa']['cliente']['numero'], $_SESSION['ipi_caixa']['cliente']['complemento'], ($_SESSION['ipi_caixa']['cliente']['edificio']), $_SESSION['ipi_caixa']['cliente']['bairro'], $_SESSION['ipi_caixa']['cliente']['cidade'], $_SESSION['ipi_caixa']['cliente']['estado'], $_SESSION['ipi_caixa']['cliente']['cep'], $_SESSION['ipi_caixa']['cliente']['telefone_1'], $_SESSION['ipi_caixa']['cliente']['telefone_2'],  $_SESSION['ipi_caixa']['cliente']['cod_clientes'], ($_SESSION['ipi_caixa']['cliente']['ref_endereco']),($_SESSION['ipi_caixa']['cliente']['ref_cliente']),  $_SESSION['ipi_caixa']['cliente']['cod_enderecos'] );
        $res_endereco_cliente = mysql_query($sql_endereco_cliente);
            //echo "<br>2: ".$sql_endereco_cliente;

        $sqlCliente = "SELECT * FROM ipi_clientes c INNER JOIN ipi_enderecos e ON (e.cod_clientes=c.cod_clientes) WHERE c.cod_clientes=" . $_SESSION['ipi_caixa']['cliente']['cod_clientes'] . " AND e.cod_enderecos=" . $_SESSION['ipi_caixa']['cliente']['cod_enderecos'];
        $resCliente = mysql_query($sqlCliente);
        $objCliente = mysql_fetch_object($resCliente);
            //echo "<br>1: ".$sqlCliente;
      }

      $agendado = ($horario_agendamento != '' ? 1 : 0);

      $desconto = ($desconto=="" ? 0 : moeda2bd($desconto));

      $frete = ($frete==""? 0 : $frete);

      $comissao_frete = ($comissao_frete=="" ? 0 : $comissao_frete);

      $total_pedido = $_SESSION['ipi_caixa']['total_pedido'] - $desconto + $frete;

      $total_desconto = $_SESSION['ipi_caixa']['total_pedido'] - $desconto;

      $total_pedido_limpo = $_SESSION['ipi_caixa']['total_pedido'];

      $impressao_fiscal = "0";

      if($frete<=0)
      {
        $comissao_frete = 0;
      } 
        //else if($comissao_frete == 0 || $frete < $comissao_frete)  //CLIENTE PEDIU PARA LIBERAR A COMISSÃO MAIOR QUE O FRETE, VAI FUNCIONAR ASSIM POR 20 DIAS ATENTENDENDO UM CHAMADO DELE, ASSIM QUE PASSAR O PERIODO VOLTAR A TRAVA.
      else if($comissao_frete == 0)
      {
        $comissao_frete = $frete;
      }

      if ($cpf_nota_fiscal != '')
      {
       $impressao_fiscal = "1";
     }

     if($impressao_fiscal == "0")
     {
      $sql_impressao = "SELECT impressao_automatica FROM ipi_pizzarias p WHERE cod_pizzarias = ".$cod_pizzarias;
          //echo "<br />sql_fiscal: ".$sql_impressao;
      $res_impressao = mysql_query($sql_impressao);
      $obj_impressao = mysql_fetch_object($res_impressao);
      $impressao_fiscal = $obj_impressao->impressao_automatica;
    }
/*
        if($forma_pg !="DINHEIRO" && $forma_pg !="TICKET" && $forma_pg !="CHEQUE" )
        {
            $impressao_fiscal = "1";
        }
*/
        if ( ($cpf_nota_fiscal == '') && ($impressao_fiscal=="1") )
        {
          $cpf_nota_fiscal = "000.000.000-00";
        }
        #$forma_pagamento = $forma_pg;
        $forma_pagamento = "Múltiplas";
        #$_SESSION['refIfood']);
        #$_SESSION['pIfood']);
        
        $ifood_polling = "";#isset($_SESSION['refIfood'])?$_SESSION['refIfood']:"";
        $origem = "TEL";#!empty($ifood_polling)?'IFOOD':'TEL';
        $pedido_ifood_json = "";isset($_SESSION['pedido_ifood_json'])?$_SESSION['pedido_ifood_json']:"";
        $sqlInserirPedido = "INSERT INTO ipi_pedidos (cod_clientes, cod_pizzarias, cod_usuarios_pedido, data_hora_pedido, valor, valor_entrega, valor_comissao_frete, valor_total, desconto, forma_pg, situacao, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2,referencia_endereco,referencia_cliente, tipo_entrega, horario_agendamento, agendado, pontos_fidelidade_total, obs_pedido, origem_pedido, data_hora_inicial, data_hora_final, impressao_fiscal,cpf,ifood_polling,pedido_ifood_json,nome_cliente) VALUES 
        ('" . $objCliente->cod_clientes . "', '" . $cod_pizzarias . "', '" . $_SESSION['usuario']['codigo'] . "', '" . date("Y-m-d H:i:s") . "',  '" . $total_pedido_limpo . "', ".$frete.",'".$comissao_frete."', '" . $total_pedido . "', ".$desconto.", '" . $forma_pagamento . "', 'NOVO', '" . $_SESSION['ipi_caixa']['cliente']['endereco'] . "', '" . $_SESSION['ipi_caixa']['cliente']['numero'] . "', '" . $_SESSION['ipi_caixa']['cliente']['complemento'] . "', '" . $_SESSION['ipi_caixa']['cliente']['edificio'] . "', '" . $_SESSION['ipi_caixa']['cliente']['bairro'] . "', '" . $_SESSION['ipi_caixa']['cliente']['cidade'] . "', '" . $_SESSION['ipi_caixa']['cliente']['estado'] . "', '" . $_SESSION['ipi_caixa']['cliente']['cep'] . "', '" . $_SESSION['ipi_caixa']['cliente']['telefone_1'] . "', '" . $_SESSION['ipi_caixa']['cliente']['telefone_2'] . "','" . ($_SESSION['ipi_caixa']['cliente']['ref_endereco']). "','" .( $_SESSION['ipi_caixa']['cliente']['ref_cliente']) ."', '" . $tipo_entrega . "', '" . $horario_agendamento . "', '$agendado', '" . $_SESSION['ipi_caixa']['fidelidade_pontos_gastos'] . "', '".texto2bd($obs_pedido)."', '".$origem."', '".$_SESSION['ipi_caixa']['data_hora_inicial']."', '".date('Y-m-d H:i:s',strtotime('+3 hours'))."', '".$impressao_fiscal."','".$cpf_nota_fiscal."','".$ifood_polling."','".$pedido_ifood_json."','".$_SESSION['ipi_caixa']['cliente']['nome']."')";
        //echo "<br>0: ".$sqlInserirPedido;
        $resInserirPedido = mysql_query($sqlInserirPedido);
        $cod_pedidos = mysql_insert_id();
        
        $this->log($conexao, "PEDIDO_TEL_INSERIDO", "", $cod_pedidos, 0, $_SESSION['ipi_caixa']['cod_clientes']);


        //IMPRIMIR EM IMPRESSORA COZINHA
        $cod_mesas = 1; //Forçado para esse código
        $cod_impressoras = 1; //Forçado para esse código
        $cod_caloboradores = 1; //Forçado para esse código

        $sql_abrir_mesa = "INSERT INTO ipi_mesas_pedidos (cod_pedidos, cod_mesas, cod_usuarios_abertura, data_hora_abertura, situacao_pedido_mesa) VALUES ('".$cod_pedidos."', ".$cod_mesas.", '".$_SESSION['usuario']['codigo']."', '".date("Y-m-d H:i:s")."', 'ABERTO')";
        //echo $sql_abrir_mesa;
        $res_abrir_mesa = mysql_query($sql_abrir_mesa);
        $cod_mesas_pedidos = mysql_insert_id();
        //fIM IMPRIMIR EM IMPRESSORA COZINHA


        //FORMAS DE PAGAMENTO
        if ( is_array($forma_pg) == true )
        {
          $cnt_formas = count($forma_pg);
          for ($a=0; $a<$cnt_formas; $a++)
          {
            $sql_forma_pgto = "INSERT INTO ipi_pedidos_formas_pg (cod_pedidos, cod_formas_pg, valor) VALUES ($cod_pedidos, '".$forma_pg[$a]."', '" . moeda2bd($valor_formas[$a]) . "')";
            //echo "<br>$a: ".$sql_forma_pgto;
            $res_forma_pgto = mysql_query($sql_forma_pgto);
          }
        }
        else
        {
          $sql_forma_pgto = "INSERT INTO ipi_pedidos_formas_pg (cod_pedidos, cod_formas_pg, valor) VALUES ($cod_pedidos, '".$forma_pg."', '" . moeda2bd($valor_formas) . "')";
          //echo "<br>X: ".$sql_forma_pgto;
          $res_forma_pgto = mysql_query($sql_forma_pgto);
        }
        
        if (($troco != "0") && ($troco != ""))
        {
          $sqlDetalhesPgto = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos, 'TROCO', '" . moeda2bd($troco) . "')";
          $resDetalhesPgto = mysql_query($sqlDetalhesPgto);
        }
        
        if ($cpf_nota_fiscal != '')
        {
          $sqlDetalhesPgto = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos, 'CPF_NOTA_PAULISTA', '" . $cpf_nota_fiscal . "')";
          $resDetalhesPgto = mysql_query($sqlDetalhesPgto);
        }
        
        /*
        if ($_SESSION['ipi_caixa']['pagamento']['tipo'] == "VISA")
        {
            $sqlDetPag = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'cod_pedido_operadora','" . $_SESSION['ipi_caixa']['pagamento']['cod_pedido_operadora'] . "')";
            $resDetPag = mysql_query($sqlDetPag);
            
            $sqlAux = "SELECT * FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_caixa']['pagamento']['cod_pedido_operadora'] . "'";
            $resAux = mysql_query($sqlAux);
            
            while ($objAux = mysql_fetch_object($resAux))
            {
                $sqlCupom = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'" . $objAux->chave . "','" . $objAux->valor . "')";
                $resCupom = mysql_query($sqlCupom);
            }
            
            $sqlAux = "DELETE FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_caixa']['pagamento']['cod_pedido_operadora'] . "'";
            $resAux = mysql_query($sqlAux);
        }
        */
        
        
        
        $numero_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
        if ($numero_pizzas > 0)
        {
          for ($a = 0; $a < $numero_pizzas; $a++)
          {

            $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_fracoes fp WHERE fp.cod_tamanhos=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] . " AND fp.cod_fracoes =" . $_SESSION['ipi_caixa']['pedido'][$a]['quantidade_fracoes'] . " AND fp.cod_pizzarias = ".$cod_pizzarias;
                //echo  "<br>sqlAuxX: ".$sqlAux."<br/>";
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            $cod_tamanhos = $objAux->cod_tamanhos;

            $sqlAux2 = "SELECT * FROM ipi_tamanhos_ipi_tipo_massa WHERE cod_tamanhos=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] . " AND cod_tipo_massa=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa'];
            $resAux2 = mysql_query($sqlAux2);
            $objAux2 = mysql_fetch_object($resAux2);

            $sqlAux3 = "SELECT * FROM ipi_combos_produtos WHERE cod_tamanhos='" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] . "' AND cod_combos = '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']."'";
            $resAux3 = mysql_query($sqlAux3);
            $objAux3 = mysql_fetch_object($resAux3);


            if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo']=="1")
            {
              if ( !is_array( $arr_id_combo_pedido_combo[ $_SESSION['ipi_caixa']['pedido'][$a]['id_combo'] ] ) )
              {
                $sql_aux_combo = "SELECT * FROM ipi_combos c INNER JOIN ipi_combos_pizzarias cp ON (c.cod_combos = cp.cod_combos) WHERE c.cod_combos = '".$_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']."' AND c.situacao='ATIVO' AND cp.cod_pizzarias = '".$cod_pizzarias."'";
                $res_aux_combo = mysql_query($sql_aux_combo);
                $obj_aux_combo = mysql_fetch_object($res_aux_combo);

                      // FIXME Não foi tratado para pagamentos de COMBOS com FIDELIDADE
                $sql_combos_pedidos = "INSERT INTO ipi_pedidos_combos (cod_combos, cod_pedidos, pontos_fidelidade, preco, fidelidade, numero_combo) VALUES('".$_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']."' , '".$cod_pedidos."', '', '".$obj_aux_combo->preco."', '0', '".$_SESSION['ipi_caixa']['pedido'][$a]['id_combo']."')";
                $res_combos_pedidos = mysql_query($sql_combos_pedidos);
                $cod_combos_pedidos = mysql_insert_id();

                $arr_id_combo_pedido_combo[ $_SESSION['ipi_caixa']['pedido'][$a]['id_combo'] ]['cod_pedidos_combos'] = $cod_combos_pedidos;
              }
            }


            $sqlInsPedPizzas = "INSERT INTO ipi_pedidos_pizzas (cod_pedidos_combos, cod_combos_produtos, cod_pedidos, cod_tamanhos, cod_tipo_massa, cod_opcoes_corte, cod_motivo_promocoes, quant_fracao, preco, preco_massa, promocional, fidelidade, combo) VALUES ('".$arr_id_combo_pedido_combo[$_SESSION['ipi_caixa']['pedido'][$a]['id_combo']]['cod_pedidos_combos']."', '" . $objAux3->cod_combos_produtos . "', '" . $cod_pedidos . "', '" . $cod_tamanhos . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa'] . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_opcoes_corte'] . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_motivo_promocoes_pizza'] . "','" . $_SESSION['ipi_caixa']['pedido'][$a]['quantidade_fracoes'] . "', '" . $objAux->preco . "', '" . $objAux2->preco . "','" . $_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] . "')";
            $resInsPedPizzas = mysql_query($sqlInsPedPizzas);
            $cod_pedidos_pizzas = mysql_insert_id();
                //echo "<br>1: ".$sqlInsPedPizzas;

                //ENVIAR PARA PRINTER DA PRINTER DA COZINHA 
            $tipo_impressao = 'PRODUTOS';
            $cod_pedidos_bebidas = 0;
                //$tipo_impressao = 'BEBIDAS';
                //$cod_pedidos_pizzas = 0;
            $sql_impressao = "INSERT INTO ipi_mesas_impressao (cod_pizzarias, cod_mesas_pedidos, cod_pedidos, cod_pedidos_pizzas, cod_pedidos_bebidas, tipo_impressao, cod_impressoras, situacao_impressao) VALUES ($cod_pizzarias, $cod_mesas_pedidos, $cod_pedidos, $cod_pedidos_pizzas, $cod_pedidos_bebidas, '".$tipo_impressao."', '$cod_impressoras', 'AGUARDANDO_IMPRESSAO')";
            $res_impressao = mysql_query($sql_impressao);        
                //ENVIAR PARA PRINTER DA PRINTER DA COZINHA 


            if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] != "N")
            {
              $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] . " AND cod_tamanhos=" . $cod_tamanhos. " AND cod_pizzarias = " . $cod_pizzarias;
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);
                    //echo  "<br>sqlAux: ".$sqlAux;

              if ($_SESSION['ipi_caixa']['pedido'][$a]['borda_promocional'] == "1")
              {
                $preco_borda = 0;
              }
              else
              {
                $preco_borda = $objAux->preco;
              }

              $sqlAux4 = "SELECT * FROM ipi_combos_produtos WHERE tipo='BORDA' AND cod_combos = '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']."'";
              $resAux4 = mysql_query($sqlAux4);
              $objAux4 = mysql_fetch_object($resAux4);
                    //echo "<br>4: ".$sqlAux4;

              $sqlBorda = "INSERT INTO ipi_pedidos_bordas (cod_pedidos, cod_pedidos_pizzas, cod_bordas, cod_pedidos_combos, cod_motivo_promocoes, cod_combos_produtos, preco, pontos_fidelidade, promocional, fidelidade, combo) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] . "', '".$arr_id_combo_pedido_combo[$_SESSION['ipi_caixa']['pedido'][$a]['id_combo']]['cod_pedidos_combos']."', '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_motivo_promocoes_borda'] . "', '" . $objAux4->cod_combos_produtos . "' , '" . $preco_borda . "', '', '" . $_SESSION['ipi_caixa']['pedido'][$a]['borda_promocional'] . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['borda_fidelidade'] . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['borda_combo'] . "')";
              $resBorda = mysql_query($sqlBorda);
                    //echo "<Br>2: ".$sqlBorda;
            }

            if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] != "N")
            {
              $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] . " AND cod_tamanhos=" . $cod_tamanhos . " AND cod_pizzarias = ".$cod_pizzarias;
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);
              $sqlAdicional = "INSERT INTO ipi_pedidos_adicionais (cod_pedidos, cod_pedidos_pizzas, cod_adicionais, preco, pontos_fidelidade) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] . "', '" . $objAux->preco . "', '')";
              $resAdicional = mysql_query($sqlAdicional);
            }

            $num_fracoes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao']);
                //echo "<br>num_fracoes: ".$num_fracoes;
            $preco_fracao_maior = 0;
            for ($b = 0; $b < $num_fracoes; $b++)
            {
              $cod_pizzas = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
              $num_fracao = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['num_fracao'];
              $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos. " AND pt.cod_pizzarias = ".$cod_pizzarias;
                    //echo  "<br>sqlAux: ".$sqlAux;
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);

              if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] == "1")
              {
                $preco_fracao = 0;
              }
              else
              {
                $preco_fracao = $objAux->preco;
              }

              if($preco_fracao > $preco_fracao_maior)
              {
                $preco_fracao_maior = $preco_fracao;
              }
            }


            for ($b = 0; $b < $num_fracoes; $b++)
            {

              $cod_pizzas = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
              $num_fracao = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['num_fracao'];
              $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos. " AND pt.cod_pizzarias = ".$cod_pizzarias;
                    //echo  "<br>sqlAux: ".$sqlAux;
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);

              if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] == "1")
              {
                $preco_fracao = 0;
              }
              else
              {
                if($_SESSION['ipi_caixa']['pedido'][$a]['preco_promocional']=="1" && $_SESSION['ipi_caixa']['pedido'][$a]['valor_preco_promocional'] !="")
                {
                  $preco_fracao = ($_SESSION['ipi_caixa']['pedido'][$a]['valor_preco_promocional'] / $num_fracoes);
                }
                else
                {
                  if(REGRA_PRECO_DIVISAO_PIZZA=="IGUALMENTE")
                  {
                    $preco_fracao = ($objAux->preco / $num_fracoes);
                  }
                  else if(REGRA_PRECO_DIVISAO_PIZZA=="MAIOR")
                  {
                    $preco_fracao = ($preco_fracao_maior / $num_fracoes);
                  }

                  if($_SESSION['ipi_caixa']['pedido'][$a]['porcentagem_promocional']=="1" && $_SESSION['ipi_caixa']['pedido'][$a]['valor_porcentagem_promocional']!="")
                  {
                    $preco_fracao = ($preco_fracao*$_SESSION['ipi_caixa']['pedido'][$a]['valor_porcentagem_promocional'])/100;
                  }
                }

              }

              $sqlPedFracoes = "INSERT INTO ipi_pedidos_fracoes (cod_pedidos, cod_pedidos_pizzas, cod_pizzas, fracao, preco, pontos_fidelidade_pizza, obs_fracao) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pizzas . "', '" . $num_fracao . "', '" . $preco_fracao . "', '', '".$_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['observacao']."')";
              $resPedFracoes = mysql_query($sqlPedFracoes);
              $cod_pedidos_fracoes = mysql_insert_id();

              $sqlAux = "SELECT * FROM ipi_pizzas WHERE cod_pizzas=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    //echo "<br>".$sqlAux;
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);

              $num_ingredientes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes']);
              for ($c = 0; $c < $num_ingredientes; $c++)
              {
                $cod_ingredientes = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                $ingrediente_padrao = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'];
                $ingrediente_troca = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_troca'];
                $cod_ingrediente_trocado = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes_troca'];

                if($ingrediente_troca)
                {

                  $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_ingredientes=" . $cod_ingrediente_trocado . " AND it.cod_tamanhos=" . $cod_tamanhos. " AND it.cod_pizzarias = ".$cod_pizzarias;
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);
                  $preco_ingrediente = arredondar_preco_ingrediente($objAux->preco_troca, $num_fracoes);
                }
                else
                {
                  $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_ingredientes=" . $cod_ingredientes . " AND it.cod_tamanhos=" . $cod_tamanhos. " AND it.cod_pizzarias = ".$cod_pizzarias;
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);
                  if($_SESSION['ipi_caixa']['pedido'][$a]['adicionais_inteira'])
                  {
                    $preco_ingrediente = arredondar_preco_ingrediente_antigo($objAux->preco, $num_fracoes);
                               //echo "asdafed".$preco_ingrediente_extra."  ";
                  }
                  else
                  {
                    $preco_ingrediente = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                  }
                          //$preco_ingrediente = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                }

                $sqlPedIngredientes = "INSERT INTO ipi_pedidos_ingredientes (cod_pedidos, cod_pedidos_pizzas, cod_pedidos_fracoes, cod_ingredientes, cod_ingrediente_trocado, preco, pontos_fidelidade, ingrediente_padrao) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pedidos_fracoes . "', '" . $cod_ingredientes . "', '" . $cod_ingrediente_trocado . "', '" . $preco_ingrediente . "', '', '" . $ingrediente_padrao . "')";
                $resPedIngredientes = mysql_query($sqlPedIngredientes);

              }
            }
            
          }

        }
        
        $numero_bebidas = isset($_SESSION['ipi_caixa']['bebida']) ? count($_SESSION['ipi_caixa']['bebida']) : 0;
        if ($numero_bebidas > 0)
        {

          for ($a = 0; $a < $numero_bebidas; $a++)
          {
            $cod_bebidas_ipi_conteudos = $_SESSION['ipi_caixa']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
            $sqlAux = "SELECT * FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') INNER JOIN ipi_bebidas b ON (b.cod_bebidas=bc.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $cod_bebidas_ipi_conteudos;
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            if ($_SESSION['ipi_caixa']['bebida'][$a]['bebida_promocional'] == "1")
            {
              $preco_bebida = 0;

              $sqlAux2 = "SELECT * FROM ipi_cupons WHERE cupom ='" . $_SESSION['ipi_caixa']['cupom'] . "'";
              $resAux2 = mysql_query($sqlAux2);
              $objAux2 = mysql_fetch_object($resAux2);
              $numAux2 = mysql_num_rows($resAux2);

              if ($numAux2 > 0)
              {
                $sqlPedCupom = "INSERT INTO ipi_pedidos_ipi_cupons (cod_pedidos, cod_cupons) VALUES ('" . $cod_pedidos . "', '" . $objAux2->cod_cupons . "')";
                $resPedCupom = mysql_query($sqlPedCupom);

                if ($objAux2->promocao == "0")
                {
                  $sqlCupom = "UPDATE ipi_cupons SET valido=0 WHERE cod_cupons='" . $objAux2->cod_cupons . "'";
                  $resCupom = mysql_query($sqlCupom);
                }
              }
            }
            else
            {
              $preco_bebida = $objAux->preco;
            }


            $sqlAux6 = "SELECT * FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') WHERE bc.cod_bebidas_ipi_conteudos = ".$_SESSION['ipi_caixa']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
            $resAux6 = mysql_query($sqlAux6);
            $objAux6 = mysql_fetch_object($resAux6);

            $sqlAux5 = "SELECT * FROM ipi_combos_produtos cp WHERE cp.tipo='BEBIDA' AND cp.cod_conteudos='".$objAux6->cod_conteudos."' AND cp.cod_combos = '" . $_SESSION['ipi_caixa']['bebida'][$a]['cod_combos']."'";
            $resAux5 = mysql_query($sqlAux5);
            $objAux5 = mysql_fetch_object($resAux5);


            $quantidade_bebida = $_SESSION['ipi_caixa']['bebida'][$a]['quantidade'];
            $sqlPedBebidas = "INSERT INTO ipi_pedidos_bebidas (cod_pedidos, cod_bebidas_ipi_conteudos, cod_combos_produtos, cod_pedidos_combos, cod_motivo_promocoes, preco, pontos_fidelidade, quantidade, promocional, fidelidade, combo) VALUES ('" . $cod_pedidos . "', '" . $cod_bebidas_ipi_conteudos . "', '" . $objAux5->cod_combos_produtos . "', '".$arr_id_combo_pedido_combo[$_SESSION['ipi_caixa']['bebida'][$a]['id_combo']]['cod_pedidos_combos']."', '".$_SESSION['ipi_caixa']['bebida'][$a]['cod_motivo_promocoes_bebida']."', '" . $preco_bebida . "', '', '" . $quantidade_bebida . "', '" . $_SESSION['ipi_caixa']['bebida'][$a]['bebida_promocional'] . "', '" . $_SESSION['ipi_caixa']['bebida'][$a]['bebida_fidelidade'] . "', '" . $_SESSION['ipi_caixa']['bebida'][$a]['bebida_combo'] . "')";
            $resPedBebidas = mysql_query($sqlPedBebidas);
            $cod_pedidos_bebidas = mysql_insert_id();
                //echo "<br>3: ".$sqlPedBebidas;

                //ENVIAR PARA PRINTER DA PRINTER DA COZINHA 
                //$tipo_impressao = 'PRODUTOS';
                //$cod_pedidos_bebidas = 0;
            $tipo_impressao = 'BEBIDAS';
            $cod_pedidos_pizzas = 0;
            $sql_impressao = "INSERT INTO ipi_mesas_impressao (cod_pizzarias, cod_mesas_pedidos, cod_pedidos, cod_pedidos_pizzas, cod_pedidos_bebidas, tipo_impressao, cod_impressoras, situacao_impressao) VALUES ($cod_pizzarias, $cod_mesas_pedidos, $cod_pedidos, $cod_pedidos_pizzas, $cod_pedidos_bebidas, '".$tipo_impressao."', '$cod_impressoras', 'AGUARDANDO_IMPRESSAO')";
            $res_impressao = mysql_query($sql_impressao);        
                //ENVIAR PARA PRINTER DA PRINTER DA COZINHA 

          }
        }

        //ORDEM DE IMPRESSÃO NA PRINTER DA COZINHA
        $sql_impressao = "INSERT INTO ipi_mesas_ordem_impressao (cod_pizzarias, cod_impressoras, cod_usuarios_impressao, cod_colaboradores_impressao, data_hora_impressao, tipo_impressao, situacao_ordem_impressao) VALUES ('".$cod_pizzarias."', '".$cod_impressoras."','".$_SESSION['usuario']['codigo']."', '".$cod_caloboradores."', '".date("Y-m-d H:i:s")."', 'IMPRIMIR_PRODUTOS', 'NOVO')";
        $res_impressao = mysql_query($sql_impressao);
        $cod_mesas_ordem_impressao = mysql_insert_id();
        $sql_imprimir = "UPDATE ipi_mesas_impressao mi SET cod_mesas_ordem_impressao = '".$cod_mesas_ordem_impressao."', situacao_impressao = 'ENVIADO_IMPRESSORA' WHERE mi.cod_pedidos = '".$cod_pedidos."' AND mi.cod_mesas_pedidos = '".$cod_mesas_pedidos."' AND mi.situacao_impressao = 'AGUARDANDO_IMPRESSAO' AND cod_impressoras = '".$cod_impressoras."'";
        $res_imprimir = mysql_query($sql_imprimir);
        //ORDEM DE IMPRESSÃO NA PRINTER DA COZINHA

        
        $fidelidade_descontar = isset($_SESSION['ipi_caixa']['fidelidade_pontos_gastos']) ? $_SESSION['ipi_caixa']['fidelidade_pontos_gastos'] : 0;
        $fidelidade_descontar = $fidelidade_descontar * (-1);
        
        if ($fidelidade_descontar != 0)
        {
          $sqlPedCupom = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, pontos) VALUES ('" . $_SESSION['ipi_cliente']['codigo'] . "', NOW(), " . $fidelidade_descontar . ")";
          $resPedCupom = mysql_query($sqlPedCupom);
        }
        
        $_SESSION[ipi_cliente][pontos_fidelidade] = $_SESSION[ipi_cliente][pontos_fidelidade] + $fidelidade_descontar;

        desconectabd($conexao);
        /*
        $resulta = false;
        $A = $_SESSION['usuario']['cod_pizzarias'];
        foreach($A as $ia=>$va){
          if($va == 24 or $va == 14 or $va == 22 or $va == 20){
            $resulta =  true;
          }
        }
        if($resulta){
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $this->server."/nfe2/autorizar.json?ref=" . $this->ref . "&token=" . $this->token);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_POST, 1);
          $body = curl_exec($ch);
          $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
          #file_get_contents("https://formulasys.encontresuafranquia.com.br/criapasta_criaimprime_pedido.php?cod_pedidos=".$cod_pedidos);
        }*/
        return $cod_pedidos;
      }    

      public function sugerir_combo()
      {
        $sugestao = false;
        $cod_tamanho_quadradinha = 4; // Variável presetada na mão com o cod_tamanhos para a quadradinha        
        $cod_tamanho_quadrada = 3; // Variável presetada na mão com o cod_tamanhos para a quadrada
        $cod_conteudos_lata = 2; // Variável presetada na mão com o cod_conteudos para a lata
        $cod_conteudos_2litros = 1; // Variável presetada na mão com o cod_conteudos para a 2 litros
        
        $numero_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
        if ($numero_pizzas > 0)
        {
          $conexao = conectabd();
          $pizza_quadradinha_doce = 0;
          $pizza_quadradinha_salgada = 0;
          $pizza_quadrada_doce = 0;
          $pizza_quadrada_salgada = 0;
          $borda = 0;
          $refri_lata = 0;
          $refri_2litros = 0;

          for ($a = 0; $a < $numero_pizzas; $a++)
          {



            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] != "1") && ((int)$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] == (int)$cod_tamanho_quadradinha) )
            {
              $num_fracoes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao']);
              for ($b = 0; $b < $num_fracoes; $b++)
              {
                if($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'] > 0)
                {

                  $sqlAux = "SELECT tipo FROM ipi_pizzas WHERE cod_pizzas=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);

                  if ($objAux->tipo=="Doce")
                  {
                    $pizza_quadradinha_doce++;
                  }
                  elseif ($objAux->tipo=="Salgado")
                  {
                    $pizza_quadradinha_salgada++;
                  }
                }
              }
            }



            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] != "1") && ((int)$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'] == (int)$cod_tamanho_quadrada) )
            {
              $num_fracoes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao']);
              for ($b = 0; $b < $num_fracoes; $b++)
              {
                if($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'] > 0)
                {

                  $sqlAux = "SELECT tipo FROM ipi_pizzas WHERE cod_pizzas=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);
                  if ($objAux->tipo=="Doce")
                  {
                    $pizza_quadrada_doce++;
                  }
                  elseif ($objAux->tipo=="Salgado")
                  {
                    $pizza_quadrada_salgada++;
                  }
                }
              }
            }



            if ( ($_SESSION['ipi_caixa']['pedido'][$a]['borda_promocional'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['borda_fidelidade'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['borda_combo'] != "1") && ($_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] != "0") )
            {
              $borda++;
            }



          }


          $cod_conteudos_lata = 2;
          $cod_conteudos_2litros = 1;


          $numero_bebidas = isset($_SESSION['ipi_caixa']['bebida']) ? count($_SESSION['ipi_caixa']['bebida']) : 0;
          if ($numero_bebidas > 0)
          {
            for ($a = 0; $a < $numero_bebidas; $a++)
            {
              $sqlAux = "SELECT c.cod_conteudos FROM ipi_bebidas b INNER JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $_SESSION['ipi_caixa']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);


              if ($objAux->cod_conteudos==$cod_conteudos_lata)
              {
                $refri_lata+=$_SESSION['ipi_caixa']['bebida'][$a]['quantidade'];
              }
              elseif ($objAux->cod_conteudos==$cod_conteudos_2litros)
              {
                $refri_2litros+=$_SESSION['ipi_caixa']['bebida'][$a]['quantidade'];
              }

            }
          }


            // Primeira sugestão 
          if (($pizza_quadradinha_salgada>=1)&&($borda==1)&&($refri_lata==1))
          {
            $sugestao = "Converter pedido para Combo Individual";
            $sugestao .= '<input type="hidden" name="cod_combos" value="1">';
          }
            // Segunda sugestão 
          else if (($pizza_quadradinha_doce==1)&&($pizza_quadrada_salgada==1)&&($refri_2litros==1))
          {
            $sugestao = "Converter pedido para Combo Mania";
            $sugestao .= '<input type="hidden" name="cod_combos" value="2">';
          }
            // Terceira sugestão
          else if (($pizza_quadrada_salgada==4)&&($pizza_quadradinha_doce==2)&&($refri_2litros==2))
          {
            $sugestao = "Converter pedido para Combo Festa";
            $sugestao .= '<input type="hidden" name="cod_combos" value="3">';
          }
            // Quarta sugestão 
          else if (($pizza_quadrada_salgada==2)&&($pizza_quadradinha_doce==1)&&($refri_2litros==1))
          {
            $sugestao = "Converter pedido para Combo Família";
            $sugestao .= '<input type="hidden" name="cod_combos" value="4">';
          }

            /*
            $sugestao .= "<br><br>pizza_quadradinha_doce: ".$pizza_quadradinha_doce;
            $sugestao .= "<br>pizza_quadradinha_salgada: ".$pizza_quadradinha_salgada;
            $sugestao .= "<br>pizza_quadrada_doce: ".$pizza_quadrada_doce;
            $sugestao .= "<br>pizza_quadrada_salgada: ".$pizza_quadrada_salgada;
            $sugestao .= "<br>borda: ".$borda;
            $sugestao .= "<br>refri_lata: ".$refri_lata;
            $sugestao .= "<br>refri_2litros: ".$refri_2litros;
            $sugestao .= "<br>";
            */
            
            
            desconectabd($conexao);
          }
          return ($sugestao);
        }
      }
      ?>
