<?
session_start();
require_once '../../bd.php';

function arredondar_preco_ingrediente ($preco_ingrediente, $numero_divisoes)
{
    $preco_dividido = $preco_ingrediente / $numero_divisoes;
    $resto = ceil($preco_dividido) - $preco_dividido;
    if ($resto >= 0.50)
    {
        $preco = floor($preco_dividido) + 0.50;
    }
    else
        $preco = ceil($preco_dividido);
    return $preco;
}


class ipi_controle_mesas
{


  private $car_versao = "1.0";
  private $nome_produto_venda = TIPO_PRODUTO;
  private $cod_clientes_mesa = 1; //código para dar baixa em pedidos de clientes anonimos

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
  public function existe_mesa_selecionada()
  {
      $ret = isset($_SESSION['ipi_mesas']['cod_mesas']) ? 1 : (isset($_SESSION['ipi_mesas']['cod_mesas']) ? 1 : 0);
      return $ret;
  }

  /**
   * Função que apaga o pedido da sessão.
   */
  public function limpar_mesa_sessao()
  {
      unset($_SESSION['ipi_mesas']);
  }

  /**
  * Função que exibe o calcula e retorna total do pedido.
  */    
  public function calcular_total_sem_taxas()
  {
    if ( $this->existe_mesa_selecionada() == true )
    {
      $total_pedido = 0;
      $cod_pedidos = $this->localizar_pedido();
      $cod_pizzarias = $this->localizar_pizzaria_por_pedido($cod_pedidos);

      $sql_pizzas = "SELECT cod_pedidos_pizzas, promocional, fidelidade, combo, cod_tamanhos, quant_fracao, situacao_pedidos_pizzas FROM ipi_pedidos_pizzas WHERE cod_pedidos = '".$cod_pedidos."'";
      $res_pizzas = mysql_query($sql_pizzas);
      $num_pizzas = mysql_num_rows($res_pizzas);
      if ($num_pizzas > 0)
      {
        for ($a = 0; $a < $num_pizzas; $a++)
        {
          $obj_pizzas = mysql_fetch_object($res_pizzas);
          $preco_lanche = 0;
          $sqlAux = "SELECT fp.preco,fp.cod_tamanhos FROM ipi_tamanhos_ipi_fracoes fp inner join ipi_fracoes f on f.cod_fracoes = fp.cod_fracoes WHERE fp.cod_tamanhos=".$obj_pizzas->cod_tamanhos." AND f.fracoes=".$obj_pizzas->quant_fracao;
          //echo "<br>1: ".$sqlAux;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);

          $cod_tamanhos = $objAux->cod_tamanhos;
          $preco_divisao_fracao = $objAux->preco;
          $total_carrinho += $preco_divisao_fracao;
          $preco_lanche += $preco_divisao_fracao;

          //############################
          //echo "<br><br>Divisao: ".$preco_divisao_fracao;
          //echo "<br>Total: ".$total_carrinho;

          /*if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] != "N")
          {
          $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] . " AND cod_tamanhos=" . $cod_tamanhos . " AND cod_pizzarias = " . $cod_pizzarias;
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
          $total_carrinho += $preco_borda;

          //############################
          //echo "<br><br>Borda: ".$preco_borda;
          //echo "<br>Total: ".$total_carrinho;
          }*/

          /*if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa'] != '')
          {
          $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_tipo_massa WHERE cod_tipo_massa=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa'] . " AND cod_tamanhos=" . $cod_tamanhos;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          $preco_massa = $objAux->preco;
          $total_carrinho += $preco_massa;

          //############################
          //echo "<br><br>Massa: ".$preco_massa;
          //echo "<br>Total: ".$total_carrinho;
          }*/
/*
          // Gergelim
          if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] != "N")
          {
            $sqlAux = "SELECT preco FROM ipi_tamanhos_ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] . " AND cod_tamanhos=" . $cod_tamanhos . " AND cod_pizzarias = ".$cod_pizzarias;
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            $total_carrinho += $objAux->preco;
            $preco_lanche += $objAux->preco;
          }
*/
          //############################
          //echo "<br><br>Adic: ".$objAux->preco;
          //echo "<br>Total: ".$total_carrinho;
          //echo "<br>sqlAux: ".$sqlAux;


          $sql_pedidos_fracoes = "SELECT cod_pedidos_fracoes, cod_pizzas FROM ipi_pedidos_fracoes WHERE cod_pedidos = '".$cod_pedidos."' AND cod_pedidos_pizzas = '".$obj_pizzas->cod_pedidos_pizzas."'";
          $res_pedidos_fracoes = mysql_query($sql_pedidos_fracoes);
          $num_pedidos_fracoes = mysql_num_rows($res_pedidos_fracoes);
          $num_fracoes = $obj_pizzas->quant_fracao;

          for ($b = 0; $b < $num_pedidos_fracoes; $b++)
          {
            $obj_pedidos_fracoes = mysql_fetch_object($res_pedidos_fracoes);

            $cod_pizzas = $obj_pedidos_fracoes->cod_pizzas;
            $num_fracao = $obj_pedidos_fracoes->fracao;

            if($cod_pizzas > 0)
            {
              $sqlAux = "SELECT pt.preco FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos . " AND pt.cod_pizzarias = ".$cod_pizzarias;
              //echo "<br>$sqlAux";
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);
              $preco_fracao = ($objAux->preco / $num_fracoes);
              if (($obj_pizzas->promocional != "1") && ($obj_pizzas->fidelidade != "1") && ($obj_pizzas->combo != "1"))
              {
                if ($obj_pizzas->situacao_pedidos_pizzas != "CANCELADO")
                {
                  $preco_fracao = ($objAux->preco / $num_fracoes);
                }
                else
                {
                  $preco_fracao = 0;
                }
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

              $total_carrinho += $preco_fracao;
              $preco_lanche += $preco_fracao;

              //############################
              //echo "<br><br>Fraça: ".$preco_fracao;
              //echo "<br>Total: ".$total_carrinho;
              if ($obj_pizzas->situacao_pedidos_pizzas != "CANCELADO")
              {

                $sql_ingredientes = "SELECT cod_ingredientes, cod_ingrediente_trocado, ingrediente_padrao FROM ipi_pedidos_ingredientes WHERE cod_pedidos = '".$cod_pedidos."' AND cod_pedidos_fracoes='".$obj_pedidos_fracoes->cod_pedidos_fracoes."'";
                //echo "<br><br>x: ".$sql_ingredientes;
                $res_ingredientes = mysql_query($sql_ingredientes);
                $num_ingredientes = mysql_num_rows($res_ingredientes);

                for ($c = 0; $c < $num_ingredientes; $c++)
                {
                  $obj_ingredientes = mysql_fetch_object($res_ingredientes);
                  $sqlAux = "SELECT it.preco FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) WHERE it.cod_tamanhos=" . $cod_tamanhos . " AND i.cod_ingredientes=".$obj_ingredientes->cod_ingredientes." AND it.cod_pizzarias = '".$cod_pizzarias."'";
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);

                  if ($obj_ingredientes->ingrediente_padrao == false)
                  {

                    //$preco_ingrediente_extra = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                    if($obj_ingredientes->cod_ingrediente_trocado == false)
                    {
                      $preco_ingrediente_extra = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                    }
                    else
                    {
                      $sqlAux = "SELECT it.preco_troca FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) WHERE it.cod_tamanhos=" . $cod_tamanhos . " AND i.cod_ingredientes=" . $obj_ingredientes->cod_ingrediente_trocado . " AND it.cod_pizzarias = " . $cod_pizzarias;
                      $resAux = mysql_query($sqlAux);
                      $objAux = mysql_fetch_object($resAux);
                      $preco_ingrediente_extra = arredondar_preco_ingrediente($objAux->preco_troca, $num_fracoes);
                    }
                    //############################
                    //echo "<br><br>Extra: ".$preco_ingrediente_extra;
                    //echo "<br>Total: ".$total_carrinho;
                    $total_carrinho += $preco_ingrediente_extra;
                    $preco_lanche += $preco_ingrediente_extra;
                  }
                }
              }


            }
          }
          $total_pedido += $preco_lanche;
        }
      }




      $sql_bebidas = "SELECT c.conteudo, b.bebida, pb.quantidade, pb.preco, pb.situacao_pedidos_bebidas, pb.promocional FROM ipi_pedidos_bebidas pb LEFT JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas=b.cod_bebidas) WHERE cod_pedidos = '".$cod_pedidos."'";
      //echo $sql_bebidas;
      $res_bebidas = mysql_query($sql_bebidas);
      $num_bebidas = mysql_num_rows($res_bebidas);
      if ($num_bebidas>0)
      {
        $total_bebidas = 0;
        for ($a = 0; $a < $num_bebidas; $a++)
        {
          $obj_bebidas = mysql_fetch_object($res_bebidas);
          if (!$obj_bebidas->promocional)
          {
            //echo "<br>AKi: ".$obj_bebidas->situacao_pedidos_bebidas;
            if ($obj_bebidas->situacao_pedidos_bebidas != "CANCELADO")
            {
              $preco_bebida = $obj_bebidas->preco;
              $quantidade_bebida = $obj_bebidas->quantidade;
              $total_bebidas += ($preco_bebida * $quantidade_bebida);
              //echo "\n\n$a: ". $preco_bebida ." x ".$quantidade_bebida." = ". $total_bebidas;
            }
            else
            {
              $total_bebidas += 0;
            }
          }
        }
        $total_pedido += $total_bebidas;
      }

      return($total_pedido);
    }
    return (-1);
  }
    

  /**
  * Função que exibe o calcula e retorna total do pedido.
  */    
  public function calcular_total()
  {
    if ( $this->existe_mesa_selecionada() == true )
    {
      $total_pedido = 0;
      $cod_pedidos = $this->localizar_pedido();
      $cod_pizzarias = $this->localizar_pizzaria_por_pedido($cod_pedidos);

      $sql_pizzas = "SELECT cod_pedidos_pizzas, promocional, fidelidade, combo, cod_tamanhos, quant_fracao, situacao_pedidos_pizzas FROM ipi_pedidos_pizzas WHERE cod_pedidos = '".$cod_pedidos."'";
      $res_pizzas = mysql_query($sql_pizzas);
      $num_pizzas = mysql_num_rows($res_pizzas);
      if ($num_pizzas > 0)
      {
        for ($a = 0; $a < $num_pizzas; $a++)
        {
          $obj_pizzas = mysql_fetch_object($res_pizzas);
          $preco_lanche = 0;
          $sqlAux = "SELECT fp.preco,fp.cod_tamanhos FROM ipi_tamanhos_ipi_fracoes fp inner join ipi_fracoes f on f.cod_fracoes = fp.cod_fracoes WHERE fp.cod_tamanhos=".$obj_pizzas->cod_tamanhos." AND f.fracoes=".$obj_pizzas->quant_fracao;
          //echo "<br>1: ".$sqlAux;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);

          $cod_tamanhos = $objAux->cod_tamanhos;
          $preco_divisao_fracao = $objAux->preco;
          //$total_carrinho += $preco_divisao_fracao;
          //$preco_lanche += $preco_divisao_fracao;

          //############################
          //echo "<br><br>Divisao: ".$preco_divisao_fracao;
          //echo "<br>Total: ".$total_carrinho;

          /*if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] != "N")
          {
          $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] . " AND cod_tamanhos=" . $cod_tamanhos . " AND cod_pizzarias = " . $cod_pizzarias;
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
          $total_carrinho += $preco_borda;

          //############################
          //echo "<br><br>Borda: ".$preco_borda;
          //echo "<br>Total: ".$total_carrinho;
          }*/

          /*if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa'] != '')
          {
          $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_tipo_massa WHERE cod_tipo_massa=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa'] . " AND cod_tamanhos=" . $cod_tamanhos;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          $preco_massa = $objAux->preco;
          $total_carrinho += $preco_massa;

          //############################
          //echo "<br><br>Massa: ".$preco_massa;
          //echo "<br>Total: ".$total_carrinho;
          }*/
/*
          // Gergelim
          if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] != "N")
          {
            $sqlAux = "SELECT preco FROM ipi_tamanhos_ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] . " AND cod_tamanhos=" . $cod_tamanhos . " AND cod_pizzarias = ".$cod_pizzarias;
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            $total_carrinho += $objAux->preco;
            $preco_lanche += $objAux->preco;
          }
*/
          //############################
          //echo "<br><br>Adic: ".$objAux->preco;
          //echo "<br>Total: ".$total_carrinho;
          //echo "<br>sqlAux: ".$sqlAux;


          $sql_pedidos_fracoes = "SELECT cod_pedidos_fracoes, cod_pizzas FROM ipi_pedidos_fracoes WHERE cod_pedidos = '".$cod_pedidos."' AND cod_pedidos_pizzas = '".$obj_pizzas->cod_pedidos_pizzas."'";
          $res_pedidos_fracoes = mysql_query($sql_pedidos_fracoes);
          $num_pedidos_fracoes = mysql_num_rows($res_pedidos_fracoes);
          $num_fracoes = $obj_pizzas->quant_fracao;

          for ($b = 0; $b < $num_pedidos_fracoes; $b++)
          {
            $obj_pedidos_fracoes = mysql_fetch_object($res_pedidos_fracoes);

            $cod_pizzas = $obj_pedidos_fracoes->cod_pizzas;
            $num_fracao = $obj_pedidos_fracoes->fracao;

            if($cod_pizzas > 0)
            {
              $sqlAux = "SELECT pt.preco FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos . " AND pt.cod_pizzarias = ".$cod_pizzarias;
              //echo "<br>$sqlAux";
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);
              $preco_fracao = ($objAux->preco / $num_fracoes);
              if (($obj_pizzas->promocional != "1") && ($obj_pizzas->fidelidade != "1") && ($obj_pizzas->combo != "1"))
              {
                if ($obj_pizzas->situacao_pedidos_pizzas != "CANCELADO")
                {
                  $preco_fracao = ($objAux->preco / $num_fracoes);
                }
                else
                {
                  $preco_fracao = 0;
                }
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
//echo "<Br>aki: ".$obj_pizzas->situacao_pedidos_pizzas;
//echo "<Br>ak2: ".$preco_fracao;
              $total_carrinho += $preco_fracao;
              $preco_lanche += $preco_fracao;

              //############################
              //echo "<br><br>Fraça: ".$preco_fracao;
              //echo "<br>Total: ".$total_carrinho;

              if ($obj_pizzas->situacao_pedidos_pizzas != "CANCELADO")
              {


                $sql_ingredientes = "SELECT cod_ingredientes, cod_ingrediente_trocado, ingrediente_padrao FROM ipi_pedidos_ingredientes WHERE cod_pedidos = '".$cod_pedidos."' AND cod_pedidos_fracoes='".$obj_pedidos_fracoes->cod_pedidos_fracoes."'";
                //echo "<br><br>x: ".$sql_ingredientes;
                $res_ingredientes = mysql_query($sql_ingredientes);
                $num_ingredientes = mysql_num_rows($res_ingredientes);

                for ($c = 0; $c < $num_ingredientes; $c++)
                {
                  $obj_ingredientes = mysql_fetch_object($res_ingredientes);
                  $sqlAux = "SELECT it.preco FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) WHERE it.cod_tamanhos=" . $cod_tamanhos . " AND i.cod_ingredientes=".$obj_ingredientes->cod_ingredientes." AND it.cod_pizzarias = '".$cod_pizzarias."'";
                  $resAux = mysql_query($sqlAux);
                  $objAux = mysql_fetch_object($resAux);

                  if ($obj_ingredientes->ingrediente_padrao == false)
                  {

                    //$preco_ingrediente_extra = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                    if($obj_ingredientes->cod_ingrediente_trocado == false)
                    {
                      $preco_ingrediente_extra = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                    }
                    else
                    {
                      $sqlAux = "SELECT it.preco_troca FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) WHERE it.cod_tamanhos=" . $cod_tamanhos . " AND i.cod_ingredientes=" . $obj_ingredientes->cod_ingrediente_trocado . " AND it.cod_pizzarias = " . $cod_pizzarias;
                      $resAux = mysql_query($sqlAux);
                      $objAux = mysql_fetch_object($resAux);
                      $preco_ingrediente_extra = arredondar_preco_ingrediente($objAux->preco_troca, $num_fracoes);
                    }
                    //############################
                    //echo "<br><br>Extra: ".$preco_ingrediente_extra;
                    //echo "<br>Total: ".$total_carrinho;
                    $total_carrinho += $preco_ingrediente_extra;
                    $preco_lanche += $preco_ingrediente_extra;
                  }
                }
              }

            }
          }
          $total_pedido += $preco_lanche;
        }
      }




      $sql_bebidas = "SELECT c.conteudo, b.bebida, pb.quantidade, pb.preco, pb.situacao_pedidos_bebidas, pb.promocional FROM ipi_pedidos_bebidas pb LEFT JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas=b.cod_bebidas) WHERE cod_pedidos = '".$cod_pedidos."'";
      //echo $sql_bebidas;
      $res_bebidas = mysql_query($sql_bebidas);
      $num_bebidas = mysql_num_rows($res_bebidas);
      if ($num_bebidas>0)
      {
        $total_bebidas = 0;
        for ($a = 0; $a < $num_bebidas; $a++)
        {
          $obj_bebidas = mysql_fetch_object($res_bebidas);
          if (!$obj_bebidas->promocional)
          {
            if ($obj_bebidas->situacao_pedidos_bebidas != "CANCELADO")
            {
              $preco_bebida = $obj_bebidas->preco;
              $quantidade_bebida = $obj_bebidas->quantidade;
              $total_bebidas += ($preco_bebida * $quantidade_bebida);
              //echo "\n\n$a: ". $preco_bebida ." x ".$quantidade_bebida." = ". $total_bebidas;
            }
            else
            {
              $total_bebidas += 0;
            }
          }
        }
        $total_pedido += $total_bebidas;
      }

//echo "\n\n total_pedido: ".$total_pedido;
//echo "\n\n total_taxas: ".$total_taxas;

      //Pegar todas as taxas diferentes da taxa de serviço, para depois aplicar o serviço
      $sql_taxas = "SELECT pt.preco_total, mtp.valor, pt.situacao_pedidos_taxas, pt.cod_mesas_taxas FROM ipi_pedidos_taxas pt LEFT JOIN ipi_mesas_taxas_pizzarias mtp ON (pt.cod_mesas_taxas = mtp.cod_mesas_taxas) WHERE pt.cod_pedidos = '".$cod_pedidos."' AND pt.cod_mesas_taxas <> 1";

      $res_taxas = mysql_query($sql_taxas);
      $num_taxas = mysql_num_rows($res_taxas);
      if ($num_taxas>0)
      {
        $total_taxas = 0;

        for ($a = 0; $a < $num_taxas; $a++)
        {
          $obj_taxas = mysql_fetch_object($res_taxas);

          if ($obj_taxas->situacao_pedidos_taxas != "CANCELADO")
          {
            $valor_total_taxas = $obj_taxas->preco_total;
          }
          else
          {
            $valor_total_taxas = 0;
          }

          $total_taxas += $valor_total_taxas;
          //echo "\n\n$a: ". $preco_bebida ." x ".$quantidade_bebida." = ". $total_bebidas;
        }
        $total_pedido += $total_taxas;
      }

      //Aplicar TAXA DE SERVIÇO
      $sql_taxas = "SELECT pt.preco_total, mtp.valor, pt.situacao_pedidos_taxas, pt.cod_mesas_taxas FROM ipi_pedidos_taxas pt LEFT JOIN ipi_mesas_taxas_pizzarias mtp ON (pt.cod_mesas_taxas = mtp.cod_mesas_taxas) WHERE pt.cod_pedidos = '".$cod_pedidos."' AND pt.cod_mesas_taxas = 1";

      $res_taxas = mysql_query($sql_taxas);
      $num_taxas = mysql_num_rows($res_taxas);
      if ($num_taxas>0)
      {
        $total_taxas = 0;

        for ($a = 0; $a < $num_taxas; $a++)
        {
          $obj_taxas = mysql_fetch_object($res_taxas);
          if ($obj_taxas->situacao_pedidos_taxas != "CANCELADO")
          {
            $total_servico = (($total_pedido * $obj_taxas->valor)/100);
            $valor_total_taxas = moeda2bd(bd2moeda($total_servico)); //só para tirar os decimos converte e desconverte
          }
          else
          {
            $valor_total_taxas = 0;
          }

          $total_taxas += $valor_total_taxas;
          //echo "\n\n$a: ". $preco_bebida ." x ".$quantidade_bebida." = ". $total_bebidas;
        }
        $total_pedido += $total_taxas;
      }


      return($total_pedido);
    }
    return (-1);
  }
    


  /*
  * Função que abre a mesa
  */
  public function abrir_mesa()
  {
    if ( $this->existe_mesa_selecionada() == true )
    {

      $sql_mesas = "SELECT m.cod_mesas, m.codigo_cliente_mesa, ms.situacao FROM ipi_mesas m LEFT JOIN ipi_mesas_situacoes ms ON (m.cod_mesas_situacoes = ms.cod_mesas_situacoes) WHERE m.cod_mesas = '".$_SESSION['ipi_mesas']['cod_mesas']."' AND m.cod_mesas_situacoes = '1' LIMIT 1";
      //echo $sql_mesas;
      $res_mesas = mysql_query($sql_mesas);
      $num_mesas = mysql_num_rows($res_mesas);
      if ($num_mesas>0)
      {
        $obj_mesas = mysql_fetch_object($res_mesas);
        $_SESSION['ipi_mesas']['situacao'] = $obj_mesas->situacao;

        $sql_mesas = "UPDATE ipi_mesas SET cod_mesas_situacoes = '2' WHERE cod_mesas = '".$_SESSION['ipi_mesas']['cod_mesas']."'";
        //echo $sql_mesas;
        $res_mesas = mysql_query($sql_mesas);
      }

      $sql_mesas = "SELECT * FROM ipi_mesas_pedidos mp WHERE mp.cod_mesas = '".$_SESSION['ipi_mesas']['cod_mesas']."' AND mp.situacao_pedido_mesa='ABERTO' LIMIT 1";
      $res_mesas = mysql_query($sql_mesas);
      $num_mesas = mysql_num_rows($res_mesas);
//echo "X: ".$num_mesas;
      if ($num_mesas==0)
      {

        $sql_ins_pedido = "INSERT INTO ipi_pedidos (cod_clientes, cod_pizzarias, cod_usuarios_pedido, data_hora_pedido, valor, valor_entrega,valor_comissao_frete, valor_total, desconto, forma_pg, situacao, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2,referencia_endereco,referencia_cliente, tipo_entrega, horario_agendamento, agendado, pontos_fidelidade_total, obs_pedido, origem_pedido, data_hora_inicial, data_hora_final, impressao_fiscal) VALUES 
        ('" . $this->cod_clientes_mesa . "', '" . $_SESSION['ipi_mesas']['cod_pizzarias'] . "', '" . $_SESSION['usuario']['codigo'] . "', '" . date("Y-m-d H:i:s") . "', '0', '0','0', '0','0', 'NAO SELECIONADA', 'MESA', '', '', '', '', '', '', '', '', '', '','','', 'Mesa', '0', '0', '0', '','MESA', '" . date("Y-m-d H:i:s") . "', '', '0')";
        $res_ins_pedido = mysql_query($sql_ins_pedido);
        $cod_pedidos = mysql_insert_id();
        //echo "Z: ".$sql_ins_pedido;

        $sql_abrir_mesa = "INSERT INTO ipi_mesas_pedidos (cod_pedidos, cod_mesas, cod_usuarios_abertura, data_hora_abertura, situacao_pedido_mesa) VALUES ('".$cod_pedidos."', ".$_SESSION['ipi_mesas']['cod_mesas'].", '".$_SESSION['usuario']['codigo']."', '".date("Y-m-d H:i:s")."', 'ABERTO')";
        //echo $sql_abrir_mesa;
        $res_abrir_mesa = mysql_query($sql_abrir_mesa);
        $cod_mesas_pedidos = mysql_insert_id();

        if ($res_abrir_mesa)
        {
          $cod_mesas_taxas = "1"; //TAXA DE SERVIÇO É 1
          $quantidade = 1; // Taxa de serviço é só uma
          $cod_colaboradores = 0; //Quando abre não tem o colaborador só nas demais telas
          $this->adicionar_taxa($cod_colaboradores, $_SESSION['ipi_mesas']['cod_pizzarias'], $cod_mesas_taxas, $quantidade);

          return 1;
        }
        else
        {
          return 0;
        }
      }
      else
      {
        return -1;
      }
    }
    else
    {
      return -2;
    }
  }


  /*
  * Função que fecha a mesa
  */
  public function fechar_mesa($forma_pg, $desconto, $troco, $frete, $numero_pessoas, $obs_pedido)
  {
    if ( $this->existe_mesa_selecionada() == true )
    {

      $sql_mesas_pedidos = "SELECT * FROM ipi_mesas_pedidos mp WHERE mp.cod_mesas = '".$_SESSION['ipi_mesas']['cod_mesas']."' AND mp.situacao_pedido_mesa='ABERTO' LIMIT 1";
      //echo $sql_mesas_pedidos;
      $res_mesas_pedidos = mysql_query($sql_mesas_pedidos);
      $num_mesas_pedidos = mysql_num_rows($res_mesas_pedidos);
      if ($num_mesas_pedidos>0)
      {
        $valor = $this->calcular_total_sem_taxas();
        $valor_total_pedido = $this->calcular_total(); //FIXME: CALCULAR O VALOR SOMENTE DAS TAXAS

/*        echo "<Br>valor sem taxas: ".$valor;
        echo "<Br>valor_total_pedido: ".$valor_total_pedido;
die();*/
        $obj_mesas_pedidos = mysql_fetch_object($res_mesas_pedidos);

        $sql_mesas = "UPDATE ipi_mesas SET cod_mesas_situacoes = '1' WHERE cod_mesas = '".$_SESSION['ipi_mesas']['cod_mesas']."'";
        //echo $sql_mesas;
        $res_mesas = mysql_query($sql_mesas);

        $sql_mesas_pedidos = "UPDATE ipi_mesas_pedidos SET cod_usuarios_fechamento = '".$_SESSION['usuario']['codigo']."', data_hora_fechamento = '".date("Y-m-d H:i:s")."', situacao_pedido_mesa = 'CONCLUIDO', numero_pessoas = '".$numero_pessoas."' WHERE cod_mesas_pedidos = '".$obj_mesas_pedidos->cod_mesas_pedidos."'";
        //echo "\n\n".$sql_mesas_pedidos;
        $res_mesas_pedidos = mysql_query($sql_mesas_pedidos);

        $sql_aux = "SELECT count(cod_pedidos_bebidas) total_bebidas FROM ipi_pedidos_bebidas WHERE cod_pedidos = '".$obj_mesas_pedidos->cod_pedidos."'";
        //echo "\n\n".$sql_aux;
        $res_aux = mysql_query($sql_aux);
        $obj_aux = mysql_fetch_object($res_aux);
        $total_bebidas = $obj_aux->total_bebidas;

        $sql_aux = "SELECT count(cod_pedidos_pizzas) total_pizzas FROM ipi_pedidos_pizzas WHERE cod_pedidos = '".$obj_mesas_pedidos->cod_pedidos."'";
        $res_aux = mysql_query($sql_aux);
        $obj_aux = mysql_fetch_object($res_aux);
        $total_pizzas = $obj_aux->total_pizzas;
        //echo "\n\n".$sql_aux;
        $total_produtos = $total_bebidas + $total_pizzas;
        //echo "\n\n x: ".$total_produtos;

        if ( ($total_produtos) > 0 )
        {

          $valor_desconto = moeda2bd($desconto);
          $valor_entrega = moeda2bd($frete);
          $valor_total = $valor_total_pedido - $valor_desconto + $valor_entrega;

          $sql_aux = "UPDATE ipi_pedidos SET situacao = 'BAIXADO', reimpressao = 0, forma_pg = '".utf8_decode($forma_pg)."', data_hora_final='".date("Y-m-d H:i:s")."', desconto = '".$valor_desconto."', valor_entrega = '".$valor_entrega."', valor = '".$valor."', valor_total = '".$valor_total."', obs_pedido = '".$obs_pedido."' WHERE cod_pedidos = '".$obj_mesas_pedidos->cod_pedidos."'";
          $res_aux = mysql_query($sql_aux);

          $sql_aux = "SELECT * FROM ipi_pedidos_taxas WHERE cod_pedidos = '".$obj_mesas_pedidos->cod_pedidos."' AND cod_mesas_taxas <> '1' AND situacao_pedidos_taxas <> 'CANCELADO'";
          $res_aux = mysql_query($sql_aux);
          $num_aux = mysql_num_rows($res_aux);
          if ($num_aux>0) //cobrar taxa de serviço e atualizar o valor atual
          {
            $obj_aux = mysql_fetch_object($res_aux);
            $valor += $obj_aux->preco_total;
          }

          $sql_aux = "SELECT * FROM ipi_pedidos_taxas WHERE cod_pedidos = '".$obj_mesas_pedidos->cod_pedidos."' AND cod_mesas_taxas = '1' AND situacao_pedidos_taxas <> 'CANCELADO'";
          $res_aux = mysql_query($sql_aux);
          $num_aux = mysql_num_rows($res_aux);
          if ($num_aux>0) //cobrar taxa de serviço e atualizar o valor atual
          {
            $obj_aux = mysql_fetch_object($res_aux);

            $total_servico = (($valor * $obj_aux->preco_unitario)/100);
            $valor_total_taxas = moeda2bd(bd2moeda($total_servico)); //só para tirar os decimos converte e desconverte

            $sql_taxa_servico = "UPDATE ipi_pedidos_taxas SET preco_total = '".$valor_total_taxas."' WHERE cod_pedidos_taxas='".$obj_aux->cod_pedidos_taxas."'";
            $res_taxa_servico = mysql_query($sql_taxa_servico);
            //echo $sql_taxa_servico;
            //die();
          }

          if (($troco != "0") && ($troco != "") && ($troco != "0.00"))
          {
            $sql_detalhes = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos, 'TROCO', '" . moeda2bd($troco) . "')";
            $res_detalhes = mysql_query($sql_detalhes);
          }

        }
        else
        {
          $sql_aux = "UPDATE ipi_pedidos SET situacao = 'CANCELADO', cod_usuarios_cancelamento='".$_SESSION['usuario']['codigo']."', data_hora_cancelamento='".date("Y-m-d H:i:s")."' , data_hora_final='".date("Y-m-d H:i:s")."' WHERE cod_pedidos = '".$obj_mesas_pedidos->cod_pedidos."'";
          $res_aux = mysql_query($sql_aux);
        }

        if ($res_mesas_pedidos)
        {
          return 1;
        }
        else
        {
          return 0;
        }        
      }
      else
      {
        return -1;
      }
    }
    else
    {
      return -2;
    }
  }

  /*
  * Função que exibe o pedido completo.
  */
  public function exibir_pedido()
  {
    if ( $this->existe_mesa_selecionada() )
    {
      $cod_pedidos = $this->localizar_pedido();

      echo '<h2>MESA: '.$_SESSION['ipi_mesas']['codigo_cliente_mesa'].'</h2>';
/*
      echo "<pre>";
      print_r($_SESSION['ipi_mesas']);
      echo "</pre>";
*/
      $sql_pizzas = "SELECT cod_pedidos_pizzas, promocional, fidelidade, combo, cod_tamanhos, situacao_pedidos_pizzas FROM ipi_pedidos_pizzas WHERE cod_pedidos = '".$cod_pedidos."'";
      $res_pizzas = mysql_query($sql_pizzas);
      $num_pizzas = mysql_num_rows($res_pizzas);
      if ($num_pizzas>0)
      {
        echo '<h1>PRODUTOS</h1>';

        for ($a = 0; $a < $num_pizzas; $a++)
        {
          $obj_pizzas = mysql_fetch_object($res_pizzas);

  /*
          $sqlAux = "SELECT * FROM ipi_tamanhos WHERE cod_tamanhos=" . $obj_pizzas->cod_tamanhos;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          echo utf8_encode('<b>Tamanho:</b> ' . $objAux->tamanho);
  */

          $sql_fracoes = "SELECT cod_pedidos_fracoes, cod_pizzas FROM ipi_pedidos_fracoes WHERE cod_pedidos = '".$cod_pedidos."' AND cod_pedidos_pizzas = '".$obj_pizzas->cod_pedidos_pizzas."'";
          //echo "X: ".$sql_fracoes;
          $res_fracoes = mysql_query($sql_fracoes);
          $num_fracoes = mysql_num_rows($res_fracoes);
          for ($b = 0; $b < $num_fracoes; $b++)
          {
            $obj_fracoes = mysql_fetch_object($res_fracoes);
            if($obj_fracoes->cod_pizzas > 0)
            {

              $sqlAux = "SELECT codigo_cliente_pizza,pizza FROM ipi_pizzas WHERE cod_pizzas='".$obj_fracoes->cod_pizzas."'";
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);
              echo utf8_encode("<b>Produto:</b> ".$objAux->codigo_cliente_pizza." -> " . $objAux->pizza);//" . ($b + 1) . "
              if ($obj_pizzas->situacao_pedidos_pizzas == "CANCELADO")
                echo " - <span style='color:red'>(" . $obj_pizzas->situacao_pedidos_pizzas.")</span>";
              /*
              echo utf8_encode(' - <span style="color:red">');
              if ($obj_pizzas->promocional == "1")
                echo utf8_encode(" (GRÁTIS)");
              else if ($obj_pizzas->fidelidade == "1")
                echo utf8_encode(" (FIDELIDADE)");
              else if ($obj_pizzas->combo == "1")
                echo utf8_encode(" (COMBO)");
              else
                echo utf8_encode(' R$ '.bd2moeda($preco_fracao).'');
              echo utf8_encode('</span>');
              */

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
                if ($linAux > 0)
                {
                  echo "<br><b>Ingredientes:</b> ";
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
                  echo "<br><b>Adicionais:</b> ";
                  while ($objAux = mysql_fetch_object($resAux))
                  {
                    echo utf8_encode($objAux->ingrediente . ", ");
                  }
                }
              }
              /*
              else
              {
                echo "<br><b>Adicionais:</b> Sem adicionais";
              }
              */
            }

            //echo "<br/><b>Preço: </b><b style='color:red'>R$ ".bd2moeda($_SESSION['ipi_caixa']['pedido'][$a]['preco_total'])."</b><br/>";
            echo "<br />";
          }
        }
        echo "<br />";
      }




      // ### Bebidas ###
      $sql_bebidas = "SELECT c.conteudo, b.bebida, pb.quantidade, pb.situacao_pedidos_bebidas FROM ipi_pedidos_bebidas pb LEFT JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas=b.cod_bebidas) WHERE cod_pedidos = '".$cod_pedidos."'";
      $res_bebidas = mysql_query($sql_bebidas);
      $num_bebidas = mysql_num_rows($res_bebidas);
      //echo $sql_bebidas;
      if ($num_bebidas>0)
      {
        echo '<h1>BEBIDAS</h1>';
        while ($obj_bebidas = mysql_fetch_object($res_bebidas))
        {
          echo utf8_encode($obj_bebidas->quantidade.' - '.$obj_bebidas->bebida.' - '.$obj_bebidas->conteudo);
          if ($obj_bebidas->situacao_pedidos_bebidas == "CANCELADO")
            echo " - <span style='color:red'>(" . $obj_bebidas->situacao_pedidos_bebidas.")</span>";
          echo '<br />';
        }
      }


      $total_pedido_sem_taxas = $this->calcular_total_sem_taxas();
      if ($total_pedido_sem_taxas > 0)
      {
        echo '<div class="comanda_total_pedido">SUBTOTAL: '.bd2moeda($total_pedido_sem_taxas).'</div>';
      }

      //##TAXAS
      $sql_taxas = "SELECT pt.quantidade, pt.preco_total, mt.taxa, mt.cod_mesas_taxas, mtp.valor  FROM ipi_pedidos_taxas pt LEFT JOIN ipi_mesas_taxas mt ON (pt.cod_mesas_taxas = mt.cod_mesas_taxas)  LEFT JOIN ipi_mesas_taxas_pizzarias mtp ON (pt.cod_mesas_taxas = mtp.cod_mesas_taxas)WHERE pt.cod_pedidos = '".$cod_pedidos."'";
      //echo $sql_taxas;
      $res_taxas = mysql_query($sql_taxas);
      $num_taxas = mysql_num_rows($res_taxas);
      if ($num_taxas>0)
      {
        echo '<h1>TAXAS</h1>';
        for ($a = 0; $a < $num_taxas; $a++)
        {
          $obj_taxas = mysql_fetch_object($res_taxas);
          if ($obj_taxas->cod_mesas_taxas == 1)
          {
            $total_pedido = $this->calcular_total_sem_taxas();
            //echo $total_pedido;
            $total_servico = (($total_pedido * $obj_taxas->preco_total)/100);

            echo utf8_encode($obj_taxas->quantidade.' - '.$obj_taxas->taxa.' - '.bd2moeda($obj_taxas->valor).'% <br />');
          }
          else
          {
            echo utf8_encode($obj_taxas->quantidade.' - '.$obj_taxas->taxa.' - R$ '.bd2moeda($obj_taxas->preco_total).'<br />');
          }
        }
      }


      $total_pedido = $this->calcular_total();
      if ($total_pedido > 0)
      {
        echo '<div class="comanda_total_pedido">TOTAL: '.bd2moeda($total_pedido).'</div>';
      }

      if ( ($num_bebidas==0)&&($num_pizzas==0) )
      {
        echo "<div style='text-align:center'><br />Nenhum produto adicionado na mesa!</div>";
      }

    }
    else
    {
      echo "<div style='text-align:center'><br />Nenhuma mesa selecionada!</div>";
    }
  }

  /*
  * Função que localiza cod_mesa_pedidos.
  */    
  public function localizar_mesa_pedido()
  {
    $sql_mesas_pedidos = "SELECT * FROM ipi_mesas_pedidos mp WHERE mp.cod_mesas = '".$_SESSION['ipi_mesas']['cod_mesas']."' AND mp.situacao_pedido_mesa='ABERTO' LIMIT 1";
    //echo $sql_mesas_pedidos;
    $res_mesas_pedidos = mysql_query($sql_mesas_pedidos);
    $num_mesas_pedidos = mysql_num_rows($res_mesas_pedidos);
    if ($num_mesas_pedidos>0)
    {
      $obj_mesas_pedidos = mysql_fetch_object($res_mesas_pedidos);
      return $obj_mesas_pedidos->cod_mesas_pedidos;
    }
    else
    {
      return -1;
    }
  }


  /*
  * Função que localiza cod_pedidos.
  */    
  public function localizar_pedido()
  {
    $sql_mesas_pedidos = "SELECT * FROM ipi_mesas_pedidos mp WHERE mp.cod_mesas = '".$_SESSION['ipi_mesas']['cod_mesas']."' AND mp.situacao_pedido_mesa='ABERTO' LIMIT 1";
    //echo $sql_mesas_pedidos;
    $res_mesas_pedidos = mysql_query($sql_mesas_pedidos);
    $num_mesas_pedidos = mysql_num_rows($res_mesas_pedidos);
    if ($num_mesas_pedidos>0)
    {
      $obj_mesas_pedidos = mysql_fetch_object($res_mesas_pedidos);
      return $obj_mesas_pedidos->cod_pedidos;
    }
    else
    {
      return -1;
    }
  }

  /*
  * Função que localiza cod_mesas_pedido.
  */    
  public function localizar_mesas_pedido()
  {
    $sql_mesas_pedidos = "SELECT * FROM ipi_mesas_pedidos mp WHERE mp.cod_mesas = '".$_SESSION['ipi_mesas']['cod_mesas']."' AND mp.situacao_pedido_mesa='ABERTO' LIMIT 1";
    $res_mesas_pedidos = mysql_query($sql_mesas_pedidos);
    $num_mesas_pedidos = mysql_num_rows($res_mesas_pedidos);
    if ($num_mesas_pedidos>0)
    {
      $obj_mesas_pedidos = mysql_fetch_object($res_mesas_pedidos);
      return $obj_mesas_pedidos->cod_mesas_pedidos;
    }
    else
    {
      return -1;
    }
  }


  /*
  * Função que localizar_pizzaria_por_pedido cod_pedidos.
  */    
  public function localizar_pizzaria_por_pedido($cod_pedidos)
  {
    $sql_pedidos = "SELECT p.cod_pizzarias FROM ipi_pedidos p WHERE cod_pedidos = '".$cod_pedidos."' LIMIT 1";
    //echo $sql_pedidos;
    $res_pedidos = mysql_query($sql_pedidos);
    $num_pedidos = mysql_num_rows($res_pedidos);
    if ($num_pedidos>0)
    {
      $obj_pedidos = mysql_fetch_object($res_pedidos);
      return $obj_pedidos->cod_pizzarias;
    }
    else
    {
      return -1;
    }
  }

  /**
   * Função que cria uma bebida na sessão .
   */
  public function adicionar_bebida($cod_colaboradores, $cod_pizzarias, $cod_bebidas_ipi_conteudos, $quantidade, $bebida_promocional, $cod_motivo_promocoes_bebida, $id_combo)
  {
    $cod_pedidos = $this->localizar_pedido();
    
    /*
    echo "<br>cod_pedidos: ".$cod_pedidos;
    echo "<br>cod_pizzarias: ".$cod_pizzarias;
    echo "<br>cod_bebidas_ipi_conteudos: ".$cod_bebidas_ipi_conteudos;
    echo "<br>quantidade: ".$quantidade;
    echo "<br>bebida_promocional: ".$bebida_promocional;
    echo "<br>cod_motivo_promocoes_bebida: ".$cod_motivo_promocoes_bebida;
    echo "<br>id_combo: ".$id_combo;
    */

    if ($cod_pedidos > 0)
    {

      $sqlAux = "SELECT cp.preco, cp.cod_impressoras FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') INNER JOIN ipi_bebidas b ON (b.cod_bebidas=bc.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $cod_bebidas_ipi_conteudos;

      $resAux = mysql_query($sqlAux);
      $objAux = mysql_fetch_object($resAux);
      //echo "\n\nBeb: ".$sqlAux;
      //die();

      if ($bebida_promocional == "1")
      {
        $preco_bebida = 0;
        
        $sqlAux2 = "SELECT * FROM ipi_cupons WHERE cupom ='" . $_SESSION['ipi_mesas']['cupom'] . "'";
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
      $preco_total = $preco_bebida * $quantidade;

      $sql_bebidas = "INSERT INTO ipi_pedidos_bebidas (cod_pedidos, cod_pedidos_combos, cod_bebidas_ipi_conteudos, cod_motivo_promocoes, cod_combos_produtos, cod_usuarios_inclusao, cod_colaboradores_inclusao, data_hora_inclusao, preco, pontos_fidelidade, quantidade, promocional, fidelidade, combo, preco_inteiro) VALUES 
      ('".$cod_pedidos."', '', '".$cod_bebidas_ipi_conteudos."', '".$cod_motivo_promocoes_bebida."', '', '".$_SESSION['usuario']['codigo']."', '".$cod_colaboradores."', '".date("Y-m-d H:i:s")."', '".$preco_bebida."', 0, '".$quantidade."', 0, 0, 0, '".$preco_total."')";
      //echo "\n\nBeb: ".$sql_bebidas;
      //die();
      $res_bebidas = mysql_query($sql_bebidas);
      $cod_pedidos_bebidas = mysql_insert_id();

      $cod_pedidos_pizzas = 0;
      $cod_mesas_pedidos = $this->localizar_mesas_pedido();
      $this->fila_impressao_produtos($cod_pizzarias, $cod_mesas_pedidos, $cod_pedidos, $cod_pedidos_pizzas, $cod_pedidos_bebidas, $objAux->cod_impressoras);
      if ($res_bebidas)
      {
        return 1;
      }
      else
      {
        return 0;
      }            
    }
    else
    {
      return -1;
    }
  }



    /**
     * Função que cria uma pizza na sessão e retorna o o indice da pizza na sessão.
     */
    public function adicionar_taxa($cod_colaboradores, $cod_pizzarias, $cod_mesas_taxas, $quantidade)
    {

      $cod_pedidos = $this->localizar_pedido();
        //die($cod_pedidos);
      if ($cod_pedidos > 0)
      {
        $sql_taxas = "SELECT * FROM ipi_mesas_taxas_pizzarias WHERE cod_pizzarias = '".$cod_pizzarias."' AND cod_mesas_taxas = '".$cod_mesas_taxas."'";
        $res_taxas = mysql_query($sql_taxas);
        $num_taxas = mysql_num_rows($res_taxas);
        if ($num_taxas > 0 )
        {
          $obj_taxas = mysql_fetch_object($res_taxas);
          $preco_unitario = $obj_taxas->valor;
          $preco_total = $quantidade * $preco_unitario;

          $sql_pedidos_taxas = "INSERT INTO ipi_pedidos_taxas (cod_pedidos, cod_mesas_taxas, cod_usuarios_inclusao, cod_colaboradores_inclusao, data_hora_inclusao, quantidade, preco_unitario, preco_total) VALUES 
          ('".$cod_pedidos."', '".$cod_mesas_taxas."', '".$_SESSION['usuario']['codigo']."', '".$cod_colaboradores."','".date("Y-m-d H:i:s")."', '".$quantidade."', '".$preco_unitario."', '".$preco_total."')";
          //echo $sql_pedidos_taxas;
          //die();
          $res_pedidos_taxas = mysql_query($sql_pedidos_taxas);
          $cod_pedidos_taxas = mysql_insert_id();
        }
        return $cod_pedidos_taxas;
      }
      else
      {
        return -1;
      }        
    }    
    

    /**
     * Função que cria uma pizza na sessão e retorna o o indice da pizza na sessão.
     */
    public function adicionar_pizza ($cod_colaboradores, $cod_pizzarias, $cod_pizzas, $cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $cod_opcoes_corte, $quantidade_fracoes, $pizza_promocional, $borda_promocional, $cod_motivo_promocoes_pizza, $cod_motivo_promocoes_borda, $id_combo)
    {

      $cod_pedidos = $this->localizar_pedido();
        //die($cod_pedidos);
      if ($cod_pedidos > 0)
      {
        $cod_pedidos_combos = 0;
        $cod_combos_produtos = 0;
        if ($id_combo)  //Ajustar pro controle de mesas
        {
          $pizza_combo=1;
        }

        //FIXME: $cod_pizzas foi colocado apenas para 1 sabor esse cliente não precisa mais sabores.
        $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos. " AND pt.cod_pizzarias = ".$cod_pizzarias;
        //echo  "<br>sqlAux: ".$sqlAux;
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        $cod_impressoras = $objAux->cod_impressoras;
//die("X: ".$cod_impressoras);
        $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_fracoes fp WHERE fp.cod_tamanhos=" . $cod_tamanhos . " AND fp.cod_fracoes =" . $quantidade_fracoes . " AND fp.cod_pizzarias = ".$cod_pizzarias;
        //echo  "<Br><BR> sqlAux: ".$sqlAux;
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);



        $sql_pedidos_pizzas = "INSERT INTO ipi_pedidos_pizzas (cod_pedidos_combos, cod_combos_produtos, cod_pedidos, cod_tamanhos, cod_tipo_massa, cod_opcoes_corte, cod_motivo_promocoes, cod_usuarios_inclusao, cod_colaboradores_inclusao, data_hora_inclusao, quant_fracao, preco, preco_massa, promocional, fidelidade, combo) VALUES ('".$cod_pedidos_combos."', '" . $cod_combos_produtos . "', '" . $cod_pedidos . "', '" . $cod_tamanhos . "', '" . $cod_tipo_massa . "', '" . $cod_opcoes_corte . "', '" . $cod_motivo_promocoes_pizza . "', '".$_SESSION['usuario']['codigo']."', '".$cod_colaboradores."', '".date("Y-m-d H:i:s")."','" . $quantidade_fracoes . "', '" . $objAux->preco . "', '" . $objAux2->preco . "','" . $pizza_promocional . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] . "', '" . $pizza_combo . "')";
        //echo  "<Br><BR> sql_pedidos_pizzas: ".$sql_pedidos_pizzas;
        //die();
        $res_pedidos_pizzas = mysql_query($sql_pedidos_pizzas);
        $cod_pedidos_pizzas = mysql_insert_id();
        
        $cod_pedidos_bebidas = 0;
        $cod_mesas_pedidos = $this->localizar_mesas_pedido();
        $this->fila_impressao_produtos($cod_pizzarias, $cod_mesas_pedidos, $cod_pedidos, $cod_pedidos_pizzas, $cod_pedidos_bebidas, $cod_impressoras);


        if ($cod_bordas != "0")
        {
          $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas=" . $cod_bordas . " AND cod_tamanhos=" . $cod_tamanhos. " AND cod_pizzarias = " . $cod_pizzarias;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          //echo  "<br>sqlAux: ".$sqlAux;
          
          if ($borda_promocional == "1")
          {
              $preco_borda = 0;
          }
          else
          {
              $preco_borda = $objAux->preco;
          }
          
          // AJUSTAR OS COMBOS
          //$sqlAux4 = "SELECT * FROM ipi_combos_produtos WHERE tipo='BORDA' AND cod_combos = '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']."'";

          //$resAux4 = mysql_query($sqlAux4);
          //$objAux4 = mysql_fetch_object($resAux4);
          //echo "<br>4: ".$sqlAux4;
          $cod_pedidos_combos = 0; //TODO fixado
          $cod_combos_produtos = 0; //TODO fixado
          $borda_combo = 0; //TODO fixado
          $borda_fidelidade = 0; //TODO fixado

          $sqlBorda = "INSERT INTO ipi_pedidos_bordas (cod_pedidos, cod_pedidos_pizzas, cod_bordas, cod_pedidos_combos, cod_motivo_promocoes, cod_combos_produtos, preco, pontos_fidelidade, promocional, fidelidade, combo) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_bordas . "', '".$cod_pedidos_combos."', '" . $cod_motivo_promocoes_borda . "', '" . $cod_combos_produtos . "' , '" . $preco_borda . "', '', '" . $borda_promocional . "', '" . $borda_fidelidade . "', '" . $borda_combo . "')";
          $resBorda = mysql_query($sqlBorda);
          //echo "<Br>2: ".$sqlBorda;
        }

        return $cod_pedidos_pizzas;
      }
      else
      {
        return -1;
      }        
    }    
    
    
    /**
     * Função que cria uma fração com o indice da pizza passado como paramentro.
     */
    public function adicionar_fracao ($cod_pizzarias, $cod_pedidos, $cod_pedidos_pizzas, $cod_pizzas, $num_fracoes, $cod_tamanhos, $num_fracao, $observacao)
    {

      $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos. " AND pt.cod_pizzarias = ".$cod_pizzarias;
      //echo  "<br>sqlAux: ".$sqlAux;
      $resAux = mysql_query($sqlAux);
      $objAux = mysql_fetch_object($resAux);

      if ($_SESSION['ipi_mesas']['pedido'][$a]['pizza_promocional'] == "1")
      {
        $preco_fracao = 0;
      }
      else
      {
        $preco_fracao = ($objAux->preco / $num_fracoes);
      }

      $sqlPedFracoes = "INSERT INTO ipi_pedidos_fracoes (cod_pedidos, cod_pedidos_pizzas, cod_pizzas, fracao, preco, pontos_fidelidade_pizza, obs_fracao) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pizzas . "', '" . $num_fracao . "', '" . $preco_fracao . "', '0', '".$observacao."')";
      //echo  "<br>sqlFracao: ".$sqlPedFracoes;
      //die();
      $resPedFracoes = mysql_query($sqlPedFracoes);
      $cod_pedidos_fracoes = mysql_insert_id();

      return $cod_pedidos_fracoes;
    }
    

    /**
     * Função que adicionar um ingrediente a uma fracao da pizza pelo indice da fracao passado como parametro.
     */
    public function adicionar_ingrediente ($cod_pedidos, $cod_pedidos_pizzas, $cod_pedidos_fracoes, $cod_ingredientes, $cod_tamanhos, $num_fracoes, $ingrediente_padrao, $ingrediente_troca = false,$cod_ingrediente_trocado = 0)
    {

      $cod_pizzarias = $this->localizar_pizzaria_por_pedido($cod_pedidos);

      if($ingrediente_troca)
      {
        $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_ingredientes=" . $cod_ingrediente_trocado . " AND it.cod_tamanhos=" . $cod_tamanhos. " AND it.cod_pizzarias = ".$cod_pizzarias;
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        $preco_ingrediente = arredondar_preco_ingrediente($objAux->preco_troca, $num_fracoes);
        //echo "<br><br>sqlAux1: ".$sqlAux;
      }
      else
      {
        $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_ingredientes=" . $cod_ingredientes . " AND it.cod_tamanhos=" . $cod_tamanhos. " AND it.cod_pizzarias = ".$cod_pizzarias;
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        $preco_ingrediente = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
        //echo "<br><br>sqlAux2: ".$sqlAux;
      }
      $sqlPedIngredientes = "INSERT INTO ipi_pedidos_ingredientes (cod_pedidos, cod_pedidos_pizzas, cod_pedidos_fracoes, cod_ingredientes, cod_ingrediente_trocado, preco, pontos_fidelidade, ingrediente_padrao) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pedidos_fracoes . "', '" . $cod_ingredientes . "', '" . $cod_ingrediente_trocado . "', '" . $preco_ingrediente . "', '', '" . $ingrediente_padrao . "')";
      $resPedIngredientes = mysql_query($sqlPedIngredientes);
      //echo "<br><br>sqlPedIngredientes: ".$sqlPedIngredientes;
    }    
    
    /**
     * Função que adicionar um ingrediente a uma fracao da pizza pelo indice da fracao passado como parametro.
     */
    public function fila_impressao_produtos($cod_pizzarias, $cod_mesas_pedidos, $cod_pedidos, $cod_pedidos_pizzas, $cod_pedidos_bebidas, $cod_impressoras)
    {

      if ($cod_pedidos_bebidas == "")
        $cod_pedidos_bebidas = 0;

      if ($cod_pedidos_pizzas == "")
        $cod_pedidos_pizzas = 0;      

      if ($cod_pedidos_bebidas != 0)
      {
        $tipo_impressao = 'BEBIDAS';
      }
      elseif ($cod_pedidos_pizzas != 0)
      {
        $tipo_impressao = 'PRODUTOS';
      }

      $sql_impressao = "INSERT INTO ipi_mesas_impressao (cod_pizzarias, cod_mesas_pedidos, cod_pedidos, cod_pedidos_pizzas, cod_pedidos_bebidas, tipo_impressao, cod_impressoras, situacao_impressao) VALUES ($cod_pizzarias, $cod_mesas_pedidos, $cod_pedidos, $cod_pedidos_pizzas, $cod_pedidos_bebidas, '".$tipo_impressao."', '$cod_impressoras', 'AGUARDANDO_IMPRESSAO')";
      $res_impressao = mysql_query($sql_impressao);
      //echo $sql_impressao;
      //die();
      if ($res_impressao)
      {
        return 1;
      }
      else
      {
        return 0;
      }   
    }

    /**
     * Função que imprime os produtos enfileirados
     */
    public function imprimir_produtos()
    {
      $cod_pedidos = $this->localizar_pedido();
      $cod_mesas_pedidos = $this->localizar_mesas_pedido();
        //die($cod_pedidos);
      if ( ($cod_pedidos > 0) && ($cod_mesas_pedidos > 0) )
      {
        $cod_pizzarias = $this->localizar_pizzaria_por_pedido($cod_pedidos);


        $cod_caloboradores = 0;

        $sql_impressoras = "SELECT DISTINCT cod_impressoras FROM ipi_mesas_impressao mi  WHERE mi.cod_pedidos = '".$cod_pedidos."' AND mi.cod_mesas_pedidos = '".$cod_mesas_pedidos."' AND mi.situacao_impressao = 'AGUARDANDO_IMPRESSAO'";
        $res_impressoras = mysql_query($sql_impressoras);
        while ($obj_impressoras = mysql_fetch_object($res_impressoras))
        {
          

          $sql_impressao = "INSERT INTO ipi_mesas_ordem_impressao (cod_pizzarias, cod_impressoras, cod_usuarios_impressao, cod_colaboradores_impressao, data_hora_impressao, tipo_impressao, situacao_ordem_impressao) VALUES ('".$cod_pizzarias."', '".$obj_impressoras->cod_impressoras."','".$_SESSION['usuario']['codigo']."', '".$cod_caloboradores."', '".date("Y-m-d H:i:s")."', 'IMPRIMIR_PRODUTOS', 'NOVO')";
          $res_impressao = mysql_query($sql_impressao);
          $cod_mesas_ordem_impressao = mysql_insert_id();

          $sql_imprimir = "UPDATE ipi_mesas_impressao mi SET cod_mesas_ordem_impressao = '".$cod_mesas_ordem_impressao."', situacao_impressao = 'ENVIADO_IMPRESSORA' WHERE mi.cod_pedidos = '".$cod_pedidos."' AND mi.cod_mesas_pedidos = '".$cod_mesas_pedidos."' AND mi.situacao_impressao = 'AGUARDANDO_IMPRESSAO' AND cod_impressoras = '".$obj_impressoras->cod_impressoras."'";
          $res_imprimir = mysql_query($sql_imprimir);

        }

        //echo $sql_impressao;
        //die();
        if ($res_impressao)
        {
          return 1;
        }
        else
        {
          return 0;
        }   
      }
      else
      {
        return -1;
      }
    }

    /**
     * Função que imprime os produtos enfileirados
     */
    public function existe_produto_sem_imprimir()
    {
      $cod_pedidos = $this->localizar_pedido();
      $cod_mesas_pedidos = $this->localizar_mesas_pedido();
        //die($cod_pedidos);
      if ( ($cod_pedidos > 0) && ($cod_mesas_pedidos > 0) )
      {
        $sql_imprimir = "SELECT * FROM ipi_mesas_impressao mi WHERE mi.cod_pedidos = '".$cod_pedidos."' AND mi.cod_mesas_pedidos = '".$cod_mesas_pedidos."' AND mi.situacao_impressao = 'AGUARDANDO_IMPRESSAO' LIMIT 1";
        $res_imprimir = mysql_query($sql_imprimir);
        $num_imprimir = mysql_num_rows($res_imprimir);

        //echo $sql_impressao;
        //die();
        if ($num_imprimir > 0)
        {
          return 1;
        }
        else
        {
          return 0;
        }   
      }
      else
      {
        return -1;
      }
    }

    /**
     * Função que cancela uma pizza
     */
    public function cancelar_pizza($cod_colaboradores, $cod_pedidos_pizzas)
    {
      $sql_cancelamento = "UPDATE ipi_pedidos_pizzas SET cod_colaboradores_cancelamento = '".$cod_colaboradores."', data_hora_cancelamento='".date("Y-m-d H:i:s")."', situacao_pedidos_pizzas='CANCELADO' WHERE cod_pedidos_pizzas = '".$cod_pedidos_pizzas."'";
      //echo $sql_cancelamento;
      //die();
      $res_cancelamento = mysql_query($sql_cancelamento);
      if ($res_cancelamento)
      {
        return 1;
      }
      else
      {
        return -1;
      }
    }

    /**
     * Função que cancela uma bebida
     */
    public function cancelar_bebida($cod_colaboradores, $cod_pedidos_bebidas)
    {
      $sql_cancelamento = "UPDATE ipi_pedidos_bebidas SET cod_colaboradores_cancelamento = '".$cod_colaboradores."', data_hora_cancelamento='".date("Y-m-d H:i:s")."', situacao_pedidos_bebidas='CANCELADO' WHERE cod_pedidos_bebidas = '".$cod_pedidos_bebidas."'";
      //echo $sql_cancelamento;
      //die();
      $res_cancelamento = mysql_query($sql_cancelamento);
      if ($res_cancelamento)
      {
        return 1;
      }
      else
      {
        return -1;
      }
    }

    /**
     * Função que cancela uma bebida
     */
    public function cancelar_taxa($cod_colaboradores, $cod_pedidos_taxas)
    {

      $sql_cancelamento = "UPDATE ipi_pedidos_taxas SET cod_colaboradores_cancelamento = '".$cod_colaboradores."', data_hora_cancelamento='".date("Y-m-d H:i:s")."', situacao_pedidos_taxas='CANCELADO' WHERE cod_pedidos_taxas = '".$cod_pedidos_taxas."'";
      //echo $sql_cancelamento;
      //die();
      $res_cancelamento = mysql_query($sql_cancelamento);
      if ($res_cancelamento)
      {
        return 1;
      }
      else
      {
        return -1;
      }

    }

    /**
     * Função que transfere para outa mesa uma bebida
     */
    public function transferir_de_mesa_bebida($cod_colaboradores, $cod_pedidos_bebidas, $cod_pedidos_nova_mesa)
    {

      $sql_transferir = "UPDATE ipi_pedidos_bebidas SET cod_pedidos = '".$cod_pedidos_nova_mesa."' WHERE cod_pedidos_bebidas = '".$cod_pedidos_bebidas."'";
      echo "<br>bebida: ".$sql_transferir;
      //die();
      $res_transferir = mysql_query($sql_transferir);
      if ($res_transferir)
      {
        return 1;
      }
      else
      {
        return -1;
      }

    }

    /**
     * Função que transfere para outa mesa uma bebida
     */
    public function transferir_de_mesa_pizza($cod_colaboradores, $cod_pedidos_pizzas, $cod_pedidos_nova_mesa)
    {
      $sql_transferir = "SET foreign_key_checks = 0;";
      $res_transferir = mysql_query($sql_transferir);
      //echo "<br>ipi_pedidos_pizzas: ".$sql_transferir;

      $sql_transferir = "UPDATE ipi_pedidos_pizzas SET cod_pedidos = '".$cod_pedidos_nova_mesa."' WHERE cod_pedidos_pizzas = '".$cod_pedidos_pizzas."'";
      $res_transferir = mysql_query($sql_transferir);
      //echo "<br>ipi_pedidos_pizzas: ".$sql_transferir;

      $sql_transferir = "UPDATE ipi_pedidos_fracoes SET cod_pedidos = '".$cod_pedidos_nova_mesa."' WHERE cod_pedidos_pizzas = '".$cod_pedidos_pizzas."'";
      $res_transferir = mysql_query($sql_transferir);
      //echo "<br>ipi_pedidos_fracoes: ".$sql_transferir;

      $sql_transferir = "UPDATE ipi_pedidos_ingredientes SET cod_pedidos = '".$cod_pedidos_nova_mesa."' WHERE cod_pedidos_pizzas = '".$cod_pedidos_pizzas."'";
      $res_transferir = mysql_query($sql_transferir);
      //echo "<br>ipi_pedidos_ingredientes: ".$sql_transferir;

      $sql_transferir = "UPDATE ipi_pedidos_bordas SET cod_pedidos = '".$cod_pedidos_nova_mesa."' WHERE cod_pedidos_pizzas = '".$cod_pedidos_pizzas."'";
      $res_transferir = mysql_query($sql_transferir);
      //echo "<br>ipi_pedidos_bordas: ".$sql_transferir;

      $sql_transferir = "UPDATE ipi_pedidos_adicionais SET cod_pedidos = '".$cod_pedidos_nova_mesa."' WHERE cod_pedidos_pizzas = '".$cod_pedidos_pizzas."'";
      $res_transferir = mysql_query($sql_transferir);
      //echo "<br>ipi_pedidos_adicionais: ".$sql_transferir;

      $sql_transferir = "SET foreign_key_checks = 1;";
      $res_transferir = mysql_query($sql_transferir);

      //die();
      if ($res_transferir)
      {
        return 1;
      }
      else
      {
        return -1;
      }

    }

        /**
     * Função que transfere para outa mesa uma bebida
     */
    public function transferir_de_mesa_taxa($cod_colaboradores, $cod_pedidos_taxas, $cod_pedidos_nova_mesa)
    {

      $sql_transferir = "UPDATE ipi_pedidos_taxas SET cod_pedidos = '".$cod_pedidos_nova_mesa."' WHERE cod_pedidos_taxas = '".$cod_pedidos_taxas."'";
      echo "<br>Taxa: ".$sql_transferir;
      //die();
      $res_transferir = mysql_query($sql_transferir);
      if ($res_transferir)
      {
        return 1;
      }
      else
      {
        return -1;
      }

    }

}
