<?

if($_GET["debug"]=="s")
{
  require_once '../../bd.php';
  require_once '../lib/php/formatacao.php';
  require_once '../lib/php/formulario.php';
  require_once '../lib/php/mensagem.php';
}

function array_column(array $input, $columnKey, $indexKey = null) {
        $result = array();
   
        if (null === $indexKey) {
            if (null === $columnKey) {
                // trigger_error('What are you doing? Use array_values() instead!', E_USER_NOTICE);
                $result = array_values($input);
            }
            else {
                foreach ($input as $row) {
                    $result[] = $row[$columnKey];
                }
            }
        }
        else {
            if (null === $columnKey) {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row;
                }
            }
            else {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
   
        return $result;
    }
if($_GET["debug"]=="s")
{
  $con = conectar_bd();
  $cod_categoria = '3';

  $data_inicial_filtro = "2013-05-01 00:00:00";
  $data_final_filtro = "2013-06-01 23:59:59";
  $filtrar_por = "MES_REFERENCIA";
  $cod_pizzarias = 1;
  $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
}

$array_dados = array();
if($tipo_relatorio!="INVENTARIO")
{
  if($filtrar_por =="MES_REFERENCIA")
  {
    $filtro_data = "month(em.data_movimentacao) BETWEEN month('$data_inicial_filtro') AND month('$data_final_filtro') AND year(em.data_movimentacao) BETWEEN year('$data_inicial_filtro') AND year('$data_final_filtro') and em.cod_pizzarias = '$cod_pizzarias' ORDER BY data_movimentacao DESC ";

    $filtro_data_real = "month(data_hora_contagem) between month('$data_inicial_filtro') AND month('$data_final_filtro') AND year(data_hora_contagem) between  year('$data_inicial_filtro') AND year('$data_final_filtro') and cod_pizzarias = '$cod_pizzarias'";

    $filtro_data_estoque = "month(e.data_hora_lancamento) BETWEEN month('$data_inicial_filtro') AND month('$data_final_filtro') AND year(e.data_hora_lancamento) BETWEEN year('$data_inicial_filtro') AND year('$data_final_filtro') and e.cod_pizzarias = '$cod_pizzarias'";
  }
  else
  {
    $filtro_data =  " em.data_movimentacao >= '$data_inicial_filtro' and em.data_movimentacao <= '$data_final_filtro' and em.cod_pizzarias = '$cod_pizzarias' ORDER BY data_movimentacao DESC ";  

    $filtro_data_real = "data_hora_contagem BETWEEN '$data_inicial_filtro' AND '$data_final_filtro' and cod_pizzarias = '$cod_pizzarias'";

    $filtro_data_estoque = "e.data_hora_lancamento BETWEEN '$data_inicial_filtro' AND '$data_final_filtro' and e.cod_pizzarias = '$cod_pizzarias'";  
  }
  // GROUP BY em.data_movimentacao  GROUP BY em.data_movimentacao 



  //(select ultima_compra_preco_grama from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' AND ((cod_ingredientes = em.cod_ingredientes and cod_ingredientes != '0') or (cod_bebidas_ipi_conteudos = em.cod_bebidas_ipi_conteudos and cod_bebidas_ipi_conteudos != '0')) order by data_movimentacao DESC LIMIT 1)


  //AJUSTAR -> ENTRADAS SEREM ENTRE
  //Verificar se as entradas estao dentre o prazo

  $sql_buscar_ultima_contagem = "SELECT (SELECT cod_inventarios from ipi_estoque_inventario where data_hora_contagem between '$data_inicial_filtro' and '$data_final_filtro'  and cod_pizzarias = '$cod_pizzarias' and day(data_hora_contagem) = '01' ORDER BY (data_hora_contagem) ASC LIMIT 1) as primeiro_inventario, (SELECT cod_inventarios from ipi_estoque_inventario where data_hora_contagem between '$data_inicial_filtro' and '$data_final_filtro' and cod_pizzarias = '$cod_pizzarias' and day(data_hora_contagem) = '01' ORDER BY (data_hora_contagem) DESC LIMIT 1) as ultimo_inventario";//$filtro_data_real
  $res_buscar_ultima_contagem = mysql_query($sql_buscar_ultima_contagem);
  $obj_buscar_ultima_contagem = mysql_fetch_object($res_buscar_ultima_contagem);

  //echo "<br/>sql_buscar_ultima_contagem.:".$sql_buscar_ultima_contagem."<br/><br/><br/><br/>";

  $cod_ultima_contagem = $obj_buscar_ultima_contagem->ultimo_inventario;
  $cod_primeira_contagem = $obj_buscar_ultima_contagem->primeiro_inventario;
}
else
{
  //echo "aa";
  $cod_primeira_contagem = $cod_inventario1;
  $cod_ultima_contagem = $cod_inventario2;


}
$nao_achou = 0;
if($cod_primeira_contagem != "")
{
  $sql_buscar_contagems = "SELECT ec.cod_ingredientes,ec.cod_bebidas_ipi_conteudos,ec.data_hora_contagem3,ec.quantidade3,(select ultima_compra_preco_grama from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' and data_movimentacao = DATE_FORMAT(ec.data_hora_contagem3, '%Y-%m-%d')   AND ((cod_ingredientes = ec.cod_ingredientes and cod_ingredientes != '0') or (cod_bebidas_ipi_conteudos = ec.cod_bebidas_ipi_conteudos and cod_bebidas_ipi_conteudos != '0')) LIMIT 1) as preco_grama_antes,(select (sum(quantidade_compras*ultima_compra_preco_grama)/sum(quantidade_compras)) from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' and data_movimentacao between DATE_FORMAT(ec.data_hora_contagem3, '%Y-%m-%d') and DATE_FORMAT((select data_hora_contagem3 from ipi_estoque_contagem where cod_inventarios = '$cod_ultima_contagem' LIMIT 1), '%Y-%m-%d') AND ((cod_ingredientes = ec.cod_ingredientes and cod_ingredientes != '0') or (cod_bebidas_ipi_conteudos = ec.cod_bebidas_ipi_conteudos and cod_bebidas_ipi_conteudos != '0')) LIMIT 1) as preco_grama from ipi_estoque_contagem ec left join ipi_ingredientes i on i.cod_ingredientes = ec.cod_ingredientes where cod_inventarios = '$cod_primeira_contagem' and ( ((ec.cod_ingredientes>0) and (i.considerar_cmv = 1)) or ec.cod_bebidas_ipi_conteudos > 0)";
  $res_buscar_contagems = mysql_query($sql_buscar_contagems);
  $data_hora_contagem1 = "";
  //echo "<br/><br/>".$sql_buscar_contagems."<br/><br/>";
  while($obj_buscar_contagems = mysql_fetch_object($res_buscar_contagems))
  {
    $preco_grama = $obj_buscar_contagems->preco_grama;
    if($preco_grama<=0)
    {
      $preco_grama = $obj_buscar_contagems->preco_grama_antes;
    }
    $array_dados["ESTOQUE_INICIAL"][] = array("tipo"=> "ESTOQUE_INICIAL","cod_ingredientes" => $obj_buscar_contagems->cod_ingredientes,"cod_bebidas_ipi_conteudos" => $obj_buscar_contagems->cod_bebidas_ipi_conteudos,"data_contagem" => $obj_buscar_contagems->data_hora_contagem3,"quantidade_ajustada" => $obj_buscar_contagems->quantidade3,"preco_grama" => $preco_grama,"total" => $total);
    $data_hora_contagem1 = $obj_buscar_contagems->data_hora_contagem3;
  }
 
  //$total = $obj_buscar_contagems->total;

//echo "<br/>sql_buscar_contagems.:".$sql_buscar_contagems;
  if($cod_ultima_contagem != $cod_primeira_contagem )
  {
    $sql_buscar_contagems2 = "SELECT sum(ec.quantidade3 * (select ultima_compra_preco_grama from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' AND ((cod_ingredientes = ec.cod_ingredientes and cod_ingredientes != '0') or (cod_bebidas_ipi_conteudos = ec.cod_bebidas_ipi_conteudos and cod_bebidas_ipi_conteudos != '0')) order by data_movimentacao DESC LIMIT 1 )) as total from ipi_estoque_contagem ec where cod_inventarios = '$cod_ultima_contagem'";


    $sql_buscar_contagems2 = "SELECT ec.cod_ingredientes,ec.cod_bebidas_ipi_conteudos,ec.data_hora_contagem3,ec.quantidade3,(select ultima_compra_preco_grama from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias'   and data_movimentacao = DATE_FORMAT(ec.data_hora_contagem3, '%Y-%m-%d')  AND ((cod_ingredientes = ec.cod_ingredientes and cod_ingredientes != '0') or (cod_bebidas_ipi_conteudos = ec.cod_bebidas_ipi_conteudos and cod_bebidas_ipi_conteudos != '0')) LIMIT 1) as preco_grama_antes,(select (sum(quantidade_compras*ultima_compra_preco_grama)/sum(quantidade_compras)) from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' and data_movimentacao between DATE_FORMAT((select data_hora_contagem3 from ipi_estoque_contagem where cod_inventarios = '$cod_primeira_contagem' LIMIT 1), '%Y-%m-%d') and DATE_FORMAT(ec.data_hora_contagem3, '%Y-%m-%d') AND ((cod_ingredientes = ec.cod_ingredientes and cod_ingredientes != '0') or (cod_bebidas_ipi_conteudos = ec.cod_bebidas_ipi_conteudos and cod_bebidas_ipi_conteudos != '0')) LIMIT 1) as preco_grama from ipi_estoque_contagem ec left join ipi_ingredientes i on i.cod_ingredientes = ec.cod_ingredientes where cod_inventarios = '$cod_ultima_contagem' and ( ((ec.cod_ingredientes>0) and (i.considerar_cmv = 1)) or ec.cod_bebidas_ipi_conteudos > 0)";
    $res_buscar_contagems2 = mysql_query($sql_buscar_contagems2);
    //echo "<br/><Br/>".$sql_buscar_contagems2;
    $data_hora_contagem2 = "";
    while($obj_buscar_contagems2 = mysql_fetch_object($res_buscar_contagems2))
    {
      $preco_grama = $obj_buscar_contagems2->preco_grama;
      if($preco_grama<=0)
      {
        $preco_grama = $obj_buscar_contagems2->preco_grama_antes;
      }
      $array_dados["ESTOQUE_FINAL"][] = array("tipo"=> "ESTOQUE_FINAL","cod_ingredientes" => $obj_buscar_contagems2->cod_ingredientes,"cod_bebidas_ipi_conteudos" => $obj_buscar_contagems2->cod_bebidas_ipi_conteudos,"data_contagem" => $obj_buscar_contagems2->data_hora_contagem3,"quantidade_ajustada" => $obj_buscar_contagems2->quantidade3,"preco_grama" => $preco_grama, "total" => $total);
      $data_hora_contagem2 = $obj_buscar_contagems2->data_hora_contagem3;
    }
    
    $filtro_data_estoque = "e.data_hora_lancamento BETWEEN '$data_hora_contagem1' AND '$data_hora_contagem2' and e.cod_pizzarias = '$cod_pizzarias'";  

    if($filtrar_por =="MES_REFERENCIA")
    {
      $filtro_data = "month(em.data_movimentacao) BETWEEN month('$data_hora_contagem1') AND month('$data_hora_contagem2') AND year(em.data_movimentacao) BETWEEN year('$data_hora_contagem1') AND year('$data_hora_contagem2') and em.cod_pizzarias = '$cod_pizzarias' ORDER BY data_movimentacao DESC ";
    }
    else
    {
      $filtro_data =  " em.data_movimentacao >= '$data_hora_contagem1' and em.data_movimentacao <= '$data_hora_contagem2' and em.cod_pizzarias = '$cod_pizzarias' ORDER BY data_movimentacao DESC ";  
    }

  }
  else
  {
    $nao_achou = 1;
  }
}
else
{
  $nao_achou = 1;
}

if($nao_achou)
{
  if($filtrar_por =="MES_REFERENCIA")
  {
    $filtro_data1 = "month(em.data_movimentacao) <= month(DATE_SUB('$data_inicial_filtro',INTERVAL -1 MONTH)) AND year(em.data_movimentacao) <= year('$data_inicial_filtro') AND year('$data_final_filtro') and em.cod_pizzarias = '$cod_pizzarias' GROUP BY em.data_movimentacao ORDER BY data_movimentacao DESC ";

    $filtro_data_estoque = "month(e.data_hora_lancamento) <= month(DATE_SUB('$data_inicial_filtro',INTERVAL -1 MONTH)) AND year(e.data_hora_lancamento) <= year('$data_final_filtro') and e.cod_pizzarias = '$cod_pizzarias' GROUP BY e.data_hora_lancamento ORDER BY data_hora_lancamento DESC ";
  }
  else
  {
    $filtro_data1 =  "em.data_movimentacao < '$data_inicial_filtro' and em.cod_pizzarias = '$cod_pizzarias' GROUP BY em.data_movimentacao ORDER BY data_movimentacao DESC ";  

    $filtro_data_estoque =  "e.data_hora_lancamento < '$data_inicial_filtro 00:00:00' and e.cod_pizzarias = '$cod_pizzarias' GROUP BY e.data_hora_lancamento ORDER BY data_hora_lancamento DESC ";
  }      
  
  //$filtro_data_estoque = "e.data_hora_lancamento BETWEEN '".date("Y-m-d",strtotime($data_hora_contagem1))."' AND '".date("Y-m-d",strtotime($data_hora_contagem2))."' and e.cod_pizzarias = '$cod_pizzarias'"; 
}

if($data_hora_contagem2 !="" && $data_hora_contagem1 !="")
{
  if($tipo_relatorio!="INVENTARIO")
  {
    $filtro_data =  " em.data_movimentacao >= '".date("Y-m-d",strtotime($data_hora_contagem1))."' and em.data_movimentacao <= '".date("Y-m-d",strtotime($data_hora_contagem2))."' and em.cod_pizzarias = '$cod_pizzarias' ORDER BY data_movimentacao DESC ";  

    $filtro_data_real = "data_hora_contagem BETWEEN '".date("Y-m-d",strtotime($data_hora_contagem1))."' AND '".date("Y-m-d",strtotime($data_hora_contagem2))."' and cod_pizzarias = '$cod_pizzarias'";

    $filtro_data_estoque = "e.data_hora_lancamento BETWEEN '".date("Y-m-d",strtotime($data_hora_contagem1))."' AND '".date("Y-m-d",strtotime($data_hora_contagem2))."' and e.cod_pizzarias = '$cod_pizzarias'";  
  }
  else
  {
    $filtro_data =  " em.data_movimentacao >= '".date("Y-m-d",strtotime($data_hora_contagem1))."' and em.data_movimentacao <= '".date("Y-m-d",strtotime($data_hora_contagem2))."' and em.cod_pizzarias = '$cod_pizzarias' ORDER BY data_movimentacao DESC ";  

    $filtro_data_real = "data_hora_contagem BETWEEN '".date("Y-m-d",strtotime($data_hora_contagem1))."' AND '".date("Y-m-d",strtotime($data_hora_contagem2))."' and cod_pizzarias = '$cod_pizzarias'";

    $filtro_data_estoque = "e.data_hora_lancamento BETWEEN '".date("Y-m-d",strtotime($data_hora_contagem1))."' AND '".date("Y-m-d",strtotime($data_hora_contagem2))."' and e.cod_pizzarias = '$cod_pizzarias'";  
  }
}
else
{
  $data_hora_contagem1 = $data_inicial_filtro;
  $data_hora_contagem2 = $data_final_filtro;


}

$sql_buscar_movimentacoes = "SELECT (SELECT sum(u.quantidade_compras) from ipi_estoque_mapa u where u.cod_pizzarias = em.cod_pizzarias and u.cod_ingredientes = em.cod_ingredientes and u.cod_bebidas_ipi_conteudos = em.cod_bebidas_ipi_conteudos and u.data_movimentacao  between '".date("Y-m-d",strtotime($data_hora_contagem1))."' and '".date("Y-m-d",strtotime($data_hora_contagem2))."' ) as total_entrada_compras,(SELECT sum(u.quantidade_compras*u.ultima_compra_preco_grama) from ipi_estoque_mapa u where u.cod_pizzarias = em.cod_pizzarias and u.cod_ingredientes = em.cod_ingredientes and u.cod_bebidas_ipi_conteudos = em.cod_bebidas_ipi_conteudos and u.data_movimentacao between  '".date("Y-m-d",strtotime($data_hora_contagem1))."' and '".date("Y-m-d",strtotime($data_hora_contagem2))."' ) as total_entrada_compras_dinheiro,em.cod_ingredientes,em.cod_bebidas_ipi_conteudos,em.data_movimentacao as data_movimentacao,em.quantidade_ajuste as quantidade_ajuste,abs(em.quantidade_compras) as quantidade_compras,abs(em.quantidade_vendas) as quantidade_vendas,abs(em.quantidade_fidelidade) as quantidade_fidelidade,abs(em.quantidade_combo) as quantidade_combo,abs(em.quantidade_lanche) as quantidade_lanche,abs(em.quantidade_promocao) as quantidade_promocao,((+ abs(em.quantidade_vendas) + abs(em.quantidade_fidelidade) + abs(em.quantidade_combo) + abs(em.quantidade_lanche) + abs(em.quantidade_promocao))) as movimentacao, em.ultima_compra_preco_grama  as preco_grama from ipi_estoque_mapa em left join ipi_ingredientes i on i.cod_ingredientes = em.cod_ingredientes where em.cod_pizzarias in ($cod_pizzarias_usuario) and ((i.ativo = 1 and i.considerar_cmv = 1 )or em.cod_bebidas_ipi_conteudos > 0) and em.cod_pizzarias in($cod_pizzarias) and $filtro_data";

//(select ultima_compra_preco_grama from ipi_estoque_mapa where cod_pizzarias = '$cod_pizzarias' AND ((cod_ingredientes = em.cod_ingredientes and cod_ingredientes != '0') or (cod_bebidas_ipi_conteudos = em.cod_bebidas_ipi_conteudos and cod_bebidas_ipi_conteudos != '0')) order by data_movimentacao DESC LIMIT 1)

//echo $sql_buscar_movimentacoes."<br/><br/>";
$res_buscar_movimentacoes = mysql_query($sql_buscar_movimentacoes);
while($obj_buscar_movimentacoes = mysql_fetch_object($res_buscar_movimentacoes))
{
  if($obj_buscar_movimentacoes->total_entrada_compras>0)
  {
    $preco_grama = ($obj_buscar_movimentacoes->total_entrada_compras_dinheiro / $obj_buscar_movimentacoes->total_entrada_compras);
  }
  else
  {
    $preco_grama = 0;
  }
  if($preco_grama<=0)
  {
    $preco_grama = $obj_buscar_movimentacoes->ultima_compra_preco_grama;
  }
  $array_dados["TEORICO"][] = array("tipo"=> "TEORICO","cod_ingredientes" => $obj_buscar_movimentacoes->cod_ingredientes,"cod_bebidas_ipi_conteudos" => $obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos,"data_movimentacao" => $obj_buscar_movimentacoes->data_movimentacao,"quantidade_movimentada" => $obj_buscar_movimentacoes->movimentacao,"quantidade_ajuste" => $obj_buscar_movimentacoes->quantidade_ajuste, "quantidade_compras" => $obj_buscar_movimentacoes->quantidade_compras, 
    "quantidade_vendas" => $obj_buscar_movimentacoes->quantidade_vendas, "quantidade_fidelidade" => $obj_buscar_movimentacoes->quantidade_fidelidade, "quantidade_combo" => $obj_buscar_movimentacoes->quantidade_combo, "quantidade_lanche" => $obj_buscar_movimentacoes->quantidade_lanche, "quantidade_promocao" => $obj_buscar_movimentacoes->quantidade_promocao, "preco_grama" => $preco_grama,"total" => ($obj_buscar_movimentacoes->movimentacao * $preco_grama)) ;

    
}

//echo "<br/><br/>".$obj_buscar_movimentacoes->total_dinheiro." - $sql_buscar_movimentacoes<br/>";
// echo "<br/>$total - 1".$sql_buscar_contagems."<br/>";
///echo "<br/>$total2 -2".$sql_buscar_contagems2."<br/>";

$sql_buscar_entradas = "SELECT eei.cod_ingredientes,eei.cod_bebidas_ipi_conteudos,e.data_hora_lancamento,eei.quantidade_entrada,eei.quantidade_embalagem_entrada,(select ultima_compra_preco_grama from ipi_estoque_mapa where cod_ingredientes = e.cod_ingredientes and cod_pizzarias in($cod_pizzarias) and data_movimentacao = DATE_FORMAT(e.data_hora_lancamento, '%Y-%m-%d')  order by data_movimentacao DESC LIMIT 1 ) as preco_grama,(eei.quantidade_entrada * eei.quantidade_embalagem_entrada) as total_quantidade,(eei.quantidade_entrada * eei.quantidade_embalagem_entrada * (select ultima_compra_preco_grama from ipi_estoque_mapa where cod_ingredientes = e.cod_ingredientes and cod_pizzarias in($cod_pizzarias) and data_movimentacao = DATE_FORMAT(e.data_hora_lancamento, '%Y-%m-%d')  order by data_movimentacao DESC LIMIT 1 )) as total_entrada,e.cod_estoque_entrada_itens from ipi_estoque e inner join ipi_estoque_entrada_itens eei on eei.cod_estoque_entrada_itens = e.cod_estoque_entrada_itens left join ipi_ingredientes i on i.cod_ingredientes = e.cod_ingredientes where ( ((e.cod_ingredientes>0) and (i.considerar_cmv = 1)) or e.cod_bebidas_ipi_conteudos > 0) and e.cod_estoque_tipo_lancamento = '2' AND e.cod_estoque_tipo_lancamento = '2' AND $filtro_data_estoque";
//echo "<br/><br/>".$sql_buscar_entradas."<br/><br/>";
$res_buscar_entradas = mysql_query($sql_buscar_entradas);
while($obj_buscar_entradas = mysql_fetch_object($res_buscar_entradas))
{
  $array_dados["ENTRADAS"][] = array("tipo"=> "ENTRADAS","cod_ingredientes" => $obj_buscar_entradas->cod_ingredientes,"cod_bebidas_ipi_conteudos" => $obj_buscar_entradas->cod_bebidas_ipi_conteudos,"data_lancamento" => $obj_buscar_entradas->data_hora_lancamento,"quantidade_embalagem" => $obj_buscar_entradas->quantidade_embalagem_entrada,"quantidade_entrada" => $obj_buscar_entradas->quantidade_entrada,"preco_grama" => $obj_buscar_entradas->preco_grama,"total" => $obj_buscar_entradas->total_entrada, "total_quantidade" => $obj_buscar_entradas->total_quantidade);
}
$total_entradas = $obj_buscar_entradas->total_entrada;

//echo "<br/><br/>te - $total_entradas - ".$sql_buscar_entradas;
//if($)
//print_r($array_dados);


//echo "<td align='left'><a href='javascript:void(0);' onclick='detalhes_titulos(\"".$cod_categoria."\",\"".$arr_tcat[$cod_categoria]['nome_categoria']."\",\"".$data_inicial_filtro."\",\"".$data_final_filtro."\",\"".$filtrar_por."\",\"".$cod_pizzarias."\",\"1\")'> $sinal ".$arr_tcat[$cod_categoria]['nome_categoria']."</a></td>";
//echo "<br/><br/>";

$array_cmv = array();
if((count($array_dados["ESTOQUE_INICIAL"])>0) && (count($array_dados["ESTOQUE_FINAL"])>0) && (count($array_dados["ENTRADAS"])>0))
{
  if(count($array_dados["ESTOQUE_FINAL"])>0)
  {
    for($i = 0; $i < count($array_dados["ESTOQUE_FINAL"]) ; $i++)
    {
      if($array_dados["ESTOQUE_FINAL"][$i]["cod_ingredientes"]>0)
      {
        $array_cmv["ING"][$array_dados["ESTOQUE_FINAL"][$i]["cod_ingredientes"]]["quantidade"] = $array_cmv["ING"][$array_dados["ESTOQUE_FINAL"][$i]["cod_ingredientes"]]["quantidade"] -  ($array_dados["ESTOQUE_FINAL"][$i]["quantidade_ajustada"]!="" ? $array_dados["ESTOQUE_FINAL"][$i]["quantidade_ajustada"] : 0);
        $array_cmv["ING"][$array_dados["ESTOQUE_FINAL"][$i]["cod_ingredientes"]]["ESTOQUE_FINAL"] = ($array_dados["ESTOQUE_FINAL"][$i]["quantidade_ajustada"]!="" ? $array_dados["ESTOQUE_FINAL"][$i]["quantidade_ajustada"] : 0);
        $array_cmv["ING"][$array_dados["ESTOQUE_FINAL"][$i]["cod_ingredientes"]]["preco_grama"] = $array_dados["ESTOQUE_FINAL"][$i]["preco_grama"];
      }
      else if($array_dados["ESTOQUE_FINAL"][$i]["cod_bebidas_ipi_conteudos"]>0)
      {
        $array_cmv["BEB"][$array_dados["ESTOQUE_FINAL"][$i]["cod_bebidas_ipi_conteudos"]]["quantidade"] = $array_cmv["BEB"][$array_dados["ESTOQUE_FINAL"][$i]["cod_bebidas_ipi_conteudos"]]["quantidade"] - ($array_dados["ESTOQUE_FINAL"][$i]["quantidade_ajustada"]!="" ? $array_dados["ESTOQUE_FINAL"][$i]["quantidade_ajustada"] : 0);
        $array_cmv["BEB"][$array_dados["ESTOQUE_FINAL"][$i]["cod_bebidas_ipi_conteudos"]]["ESTOQUE_FINAL"] = ($array_dados["ESTOQUE_FINAL"][$i]["quantidade_ajustada"]!="" ? $array_dados["ESTOQUE_FINAL"][$i]["quantidade_ajustada"] : 0);
         $array_cmv["BEB"][$array_dados["ESTOQUE_FINAL"][$i]["cod_bebidas_ipi_conteudos"]]["preco_grama"] = $array_dados["ESTOQUE_FINAL"][$i]["preco_grama"];
      }
      
    }
    //$estoque_final = array_column($array_dados["ESTOQUE_FINAL"], 'quantidade_ajustada');
    //
    //$ef = array_sum($estoque_final);
    //echo "<br/><br/>";
  }

  if(count($array_dados["ESTOQUE_INICIAL"])>0)
  {
    for($i = 0; $i < count($array_dados["ESTOQUE_INICIAL"]) ; $i++)
    {
      if($array_dados["ESTOQUE_INICIAL"][$i]["cod_ingredientes"]>0)
      {
        $array_cmv["ING"][$array_dados["ESTOQUE_INICIAL"][$i]["cod_ingredientes"]]['quantidade'] = $array_cmv["ING"][$array_dados["ESTOQUE_INICIAL"][$i]["cod_ingredientes"]]['quantidade'] + $array_dados["ESTOQUE_INICIAL"][$i]["quantidade_ajustada"];
        $array_cmv["ING"][$array_dados["ESTOQUE_INICIAL"][$i]["cod_ingredientes"]]['ESTOQUE_INICIAL'] = $array_dados["ESTOQUE_INICIAL"][$i]["quantidade_ajustada"];
      }
      else if($array_dados["ESTOQUE_INICIAL"][$i]["cod_bebidas_ipi_conteudos"]>0)
      {
        $array_cmv["BEB"][$array_dados["ESTOQUE_INICIAL"][$i]["cod_bebidas_ipi_conteudos"]]['quantidade'] = $array_cmv["BEB"][$array_dados["ESTOQUE_INICIAL"][$i]["cod_bebidas_ipi_conteudos"]]['quantidade'] +$array_dados["ESTOQUE_INICIAL"][$i]["quantidade_ajustada"];
        $array_cmv["BEB"][$array_dados["ESTOQUE_INICIAL"][$i]["cod_bebidas_ipi_conteudos"]]['ESTOQUE_INICIAL'] = $array_dados["ESTOQUE_INICIAL"][$i]["quantidade_ajustada"];
      }
      
    }
    //$estoque_inicial = array_column($array_dados["ESTOQUE_INICIAL"], 'quantidade_ajustada');
    //
    //$ei = array_sum($estoque_inicial);
  //echo "<br/><br/>";
  }



  if(count($array_dados["ENTRADAS"])>0)
  {
    for($i = 0; $i < count($array_dados["ENTRADAS"]) ; $i++)
    {
      if($array_dados["ENTRADAS"][$i]["cod_ingredientes"]>0)
      {
        $array_cmv["ING"][$array_dados["ENTRADAS"][$i]["cod_ingredientes"]]['quantidade'] += $array_dados["ENTRADAS"][$i]["total_quantidade"];
        $array_cmv["ING"][$array_dados["ENTRADAS"][$i]["cod_ingredientes"]]['ENTRADAS'] = $array_cmv["ING"][$array_dados["ENTRADAS"][$i]["cod_ingredientes"]]['ENTRADAS'] + $array_dados["ENTRADAS"][$i]["total_quantidade"];
      }
      else if($array_dados["ENTRADAS"][$i]["cod_bebidas_ipi_conteudos"]>0)
      {
        $array_cmv["BEB"][$array_dados["ENTRADAS"][$i]["cod_bebidas_ipi_conteudos"]]['quantidade'] += $array_dados["ENTRADAS"][$i]["total_quantidade"];
        $array_cmv["BEB"][$array_dados["ENTRADAS"][$i]["cod_bebidas_ipi_conteudos"]]['ENTRADAS'] = $array_dados["ENTRADAS"][$i]["total_quantidade"] + $array_cmv["BEB"][$array_dados["ENTRADAS"][$i]["cod_bebidas_ipi_conteudos"]]['ENTRADAS'];
      }
      
    }
    //$entradas= array_column($array_dados["ENTRADAS"], 'total_quantidade');
    //
   // $com = array_sum($entradas);
  }
}

if(count($array_dados["TEORICO"])>0)
  {
    for($i = 0; $i < count($array_dados["TEORICO"]) ; $i++)
    {
      if($array_dados["TEORICO"][$i]["cod_ingredientes"]>0)
      {
        $array_cmv_teorico["ING"][$array_dados["TEORICO"][$i]["cod_ingredientes"]]["quantidade_movimentada"] = $array_cmv_teorico["ING"][$array_dados["TEORICO"][$i]["cod_ingredientes"]]["quantidade_movimentada"] +  ($array_dados["TEORICO"][$i]["quantidade_movimentada"]!="" ? $array_dados["TEORICO"][$i]["quantidade_movimentada"] : 0);
        $array_cmv_teorico["ING"][$array_dados["TEORICO"][$i]["cod_ingredientes"]]["TEORICO"] = ($array_dados["TEORICO"][$i]["quantidade_movimentada"]!="" ? $array_dados["TEORICO"][$i]["quantidade_movimentada"] : 0);
        $array_cmv_teorico["ING"][$array_dados["TEORICO"][$i]["cod_ingredientes"]]["preco_grama"] = $array_dados["TEORICO"][$i]["preco_grama"];
      }
      else if($array_dados["TEORICO"][$i]["cod_bebidas_ipi_conteudos"]>0)
      {
        $array_cmv_teorico["BEB"][$array_dados["TEORICO"][$i]["cod_bebidas_ipi_conteudos"]]["quantidade_movimentada"] = $array_cmv_teorico["BEB"][$array_dados["TEORICO"][$i]["cod_bebidas_ipi_conteudos"]]["quantidade_movimentada"] + ($array_dados["TEORICO"][$i]["quantidade_movimentada"]!="" ? $array_dados["TEORICO"][$i]["quantidade_movimentada"] : 0);
        $array_cmv_teorico["BEB"][$array_dados["TEORICO"][$i]["cod_bebidas_ipi_conteudos"]]["TEORICO"] = ($array_dados["TEORICO"][$i]["quantidade_movimentada"]!="" ? $array_dados["TEORICO"][$i]["quantidade_movimentada"] : 0);
         $array_cmv_teorico["BEB"][$array_dados["TEORICO"][$i]["cod_bebidas_ipi_conteudos"]]["preco_grama"] = $array_dados["TEORICO"][$i]["preco_grama"];
      }
      
    }
    //$estoque_final = array_column($array_dados["ESTOQUE_FINAL"], 'quantidade_ajustada');
    //
    //$ef = array_sum($estoque_final);
    //echo "<br/><br/>";
  }

$soma_cmv = 0;
if(count($array_cmv["ING"])>0)
{
  foreach($array_cmv["ING"] as $codigo => $dados)
  {
    $soma_cmv += $dados['quantidade'] * $dados['preco_grama'];
  }
}
if(count($array_cmv["BEB"])>0)
{
  foreach($array_cmv["BEB"] as $codigo => $dados)
  {
    $soma_cmv += $dados['quantidade'] * $dados['preco_grama'];
  }
}

$soma_cmv_teorico = 0;
foreach($array_cmv_teorico["ING"] as $codigo => $dados)
{
  $soma_cmv_teorico += $dados['quantidade_movimentada'] * $dados['preco_grama'];
}

foreach($array_cmv_teorico["BEB"] as $codigo => $dados)
{
  $soma_cmv_teorico += $dados['quantidade_movimentada'] * $dados['preco_grama'];
}
//if(count($array_dados["TEORICO"])>0)
//{
  //$teorico = array_column($array_dados["TEORICO"], 'total');
  //
  $teorico = $soma_cmv_teorico;
//}

if($_GET["debug"]=="s")
{
  echo "<pre>";
  print_r($array_cmv);
  echo "<br/><br/>";
  print_r($array_dados);
  /*echo "<br/><br/>";
  print_r($estoque_inicial);
  echo "<br/><br/>";
  print_r($estoque_final);
  echo "<br/><br/>";
  print_r($entradas);*/
  echo "</pre>";
  echo "<br/>teorico = ".bd2moeda($teorico);
  echo "<br/>ef = ".bd2moeda($ef);
  echo "<br/>com = ".bd2moeda($com);
  echo "<br/>ei = ".bd2moeda($ei);
  echo "<br/>cmv = ".bd2moeda($soma_cmv);

  desconectar_bd($con);
}
?>