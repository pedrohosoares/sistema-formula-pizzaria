<?php

/**
 * ipi_ingrediente.php: Cadastro de Ingrediente
 * 
 * Índice: cod_ingredientes
 * Tabela: ipi_ingredientes
 */
require_once '../sys/lib/php/formulario.php';
require_once '../bd.php';
$tabela = 'ipi_estoque_mapa';
$chave_primaria = 'cod_ingredientes';

?>
<html>
<head><title>Job ESTOQUE</title></head>
<body>

<?
echo date("d/m/Y H:i:s");
/*$data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
$data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');*/
$ingrediente_filtro = "";
$day = date("d");
if($_GET['d'])
{
  $day = $_GET['d'];
}

$mon = date("m");
if($_GET['m'])
{
  $mon = $_GET['m'];
}


$con = conectabd();


$filtro_pizzaria = "";
$filtro_pizzaria_excluir = "";
$filtro_ingrediente_excluir = "";

$sql_atualizar_terminados = "UPDATE ipi_processamento_estoque pe inner join ipi_processamento_estoque_fila pef on pef.cod_processamento = pe.cod_processamento inner join (select count(pef.cod_processamento_fila) as qtd_total, ( SELECT count(cod_processamento_fila) from ipi_processamento_estoque_fila where cod_processamento = pef.cod_processamento and situacao='FINALIZADO') as qtd_finalizados ,cod_processamento from ipi_processamento_estoque_fila pef GROUP BY pef.cod_processamento) cont on cont.cod_processamento = pe.cod_processamento SET pe.situacao='CONCLUIDO' WHERE  cont.qtd_total= cont.qtd_finalizados ";
$res_atualizar_terminados = mysql_query($sql_atualizar_terminados);

$sql_buscar_fila = "SELECT pe.cod_processamento,pe.cod_ingredientes_processar,pe.cod_pizzarias_processar,pef.data_processamento,pef.cod_processamento_fila from ipi_processamento_estoque pe INNER JOIN ipi_processamento_estoque_fila pef on pef.cod_processamento = pe.cod_processamento where pef.situacao='NOVO' ORDER BY pe.cod_pizzarias_processar,pef.data_processamento";
$res_buscar_fila = mysql_query($sql_buscar_fila);
$obj_buscar_fila = mysql_fetch_object($res_buscar_fila);

if($obj_buscar_fila->cod_pizzarias_processar)
{
  $filtro_pizzaria = "WHERE cod_pizzarias in (".$obj_buscar_fila->cod_pizzarias_processar.")";
  $filtro_pizzaria_excluir = " AND cod_pizzarias in (".$obj_buscar_fila->cod_pizzarias_processar.")";
}

if($obj_buscar_fila->cod_ingredientes_processar)
{
  $ingrediente_filtro = $obj_buscar_fila->cod_ingredientes_processar;
  $filtro_ingrediente_excluir = " AND cod_ingredientes in (".$obj_buscar_fila->cod_ingredientes_processar.")";
}

$cod_processamento_fila = $obj_buscar_fila->cod_processamento_fila;
$cod_pizzarias_fila = $obj_buscar_fila->cod_pizzarias_processar;
/*$data_inicial = strtotime(date("Y-$mon-$day").' -1 day');
$data_final = strtotime(date("Y-$mon-$day").' -1 day');
$data_inicial = date("Y-m-d",$data_inicial);
$data_final = date("Y-m-d",$data_final);*/

$data_inicial = $obj_buscar_fila->data_processamento;
$data_final = $obj_buscar_fila->data_processamento;

if($obj_buscar_fila->cod_processamento)
{

    $sql_atualizar_andamento = "UPDATE ipi_processamento_estoque_fila pef inner join ipi_processamento_estoque pe on pe.cod_processamento = pef.cod_processamento set pef.situacao = 'FINALIZADO', pef.data_hora_inicio = NOW(), pef.situacao = 'ANDAMENTO' where pe.cod_pizzarias_processar='$cod_pizzarias_fila' and pe.cod_ingredientes_processar = '$ingrediente_filtro' and pef.data_processamento = '$data_final' and pef.situacao = 'NOVO'";
    //echo $sql_atualizar_andamento;
    $res_atualizar_andamento = mysql_query($sql_atualizar_andamento);


    //die($sql_deletar_movimentacoes);
    $SqlBuscaPizzarias = "SELECT cod_pizzarias FROM ipi_pizzarias $filtro_pizzaria ORDER BY cod_pizzarias";
    $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
    //echo $SqlBuscaPizzarias;
    //die();
    while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
    {
      $cod_pizzarias = $objBuscaPizzarias->cod_pizzarias;
      echo "<h1>$cod_pizzarias - $data_inicial - $data_final</h1>";
      /*echo "<pre>";
      print_r($arr_consumo1);
      echo "</pre>";
      echo "<br/><br/><br/><br/><br/><br/><br/>";
          echo "<pre>";
      print_r($arr_consumo2);
      echo "</pre>";
          echo "<br/><br/><br/><br/><br/><br/><br/>";
          echo "<pre>";
      print_r($arr_consumo3);
      echo "</pre>";*/

      $item = array();
      $usados = array();
      $pAnt = array();//pAnt = primero anterior
      $ingredientes_usados = array();
      $sql_buscar_movimentacoes_antigas = "SELECT SUM(e.quantidade) as quantidade,e.cod_ingredientes,e.tipo_estoque from ipi_estoque e inner join ipi_ingredientes i on i.cod_ingredientes = e.cod_ingredientes where e.cod_pizzarias in($cod_pizzarias) and data_hora_lancamento < '$data_inicial 00:00:00' and e.cod_ingredientes > 0 ";
      if($ingrediente_filtro)
        $sql_buscar_movimentacoes_antigas .= "and e.cod_ingredientes = '$ingrediente_filtro'"; 

     /* if($titulos_subcategorias)
        $sql_buscar_movimentacoes_antigas .= "and i.cod_titulos_subcategorias= '$titulos_subcategorias'"; */
         
      $sql_buscar_movimentacoes_antigas .= " group by e.cod_ingredientes";
      $res_buscar_movimentacoes_antigas = mysql_query($sql_buscar_movimentacoes_antigas);
     // echo $sql_buscar_movimentacoes_antigas."<br/>";
      while($obj_buscar_movimentacoes_antigas = mysql_fetch_object($res_buscar_movimentacoes_antigas))
      {
        $cod_ing = $obj_buscar_movimentacoes_antigas->cod_ingredientes;
        $tipo_est = $obj_buscar_movimentacoes_antigas->tipo_estoque;

        if($tipo_est=="INGREDIENTE")
        {
          $ingredientes_usados[$tipo_est][] = $cod_ing;
          $pAnt[$tipo_est][$cod_ing]['total'] = $pAnt[$tipo_est][$cod_ing]['total'] + $obj_buscar_movimentacoes_antigas->quantidade;

          $pAnt[$tipo_est][$cod_ing]['saldo_anterior'] = $pAnt[$tipo_est][$cod_ing]['saldo_anterior'] + $obj_buscar_movimentacoes_antigas->quantidade;
        }
      }
      
      $sql_buscar_movimentacoes_antigas = "SELECT SUM(e.quantidade) as quantidade,e.cod_bebidas_ipi_conteudos,e.tipo_estoque from ipi_estoque e where e.cod_pizzarias in($cod_pizzarias) and data_hora_lancamento < '$data_inicial 00:00:00' and e.cod_bebidas_ipi_conteudos > 0";

      if($ingrediente_filtro)
        $sql_buscar_movimentacoes_antigas .= " and e.cod_ingredientes = '$ingrediente_filtro'";//cod_bebidas_ipi_conteudos

      /*if($titulos_subcategorias!="" && $titulos_subcategorias!='10')
        $sql_buscar_movimentacoes_antigas .= " and e.cod_bebidas_ipi_conteudos < 0"; */

      $sql_buscar_movimentacoes_antigas .= " group by e.cod_bebidas_ipi_conteudos";
      $res_buscar_movimentacoes_antigas = mysql_query($sql_buscar_movimentacoes_antigas);
      //echo $sql_buscar_movimentacoes_antigas."<br/>";
      while($obj_buscar_movimentacoes_antigas = mysql_fetch_object($res_buscar_movimentacoes_antigas))
      {
        $tipo_est = $obj_buscar_movimentacoes_antigas->tipo_estoque;
        $cod_beb = $obj_buscar_movimentacoes_antigas->cod_bebidas_ipi_conteudos;

        if($obj_buscar_movimentacoes_antigas->tipo_estoque=="BEBIDA")
        {
          $ingredientes_usados[$tipo_est][] = $cod_beb;
          $pAnt[$tipo_est][$cod_beb]['total'] = $pAnt[$tipo_est][$cod_beb]['total'] + $obj_buscar_movimentacoes_antigas->quantidade;

          $pAnt[$tipo_est][$cod_beb]['saldo_anterior'] = $pAnt[$tipo_est][$cod_beb]['saldo_anterior'] + $obj_buscar_movimentacoes_antigas->quantidade;     
        }
      }

      // case (promo or combo or fideli ser promo)

      $sql_buscar_movimentacoes = "SELECT e.*,pp.combo as p_combo,pp.fidelidade as p_fidelidade,pp.promocional as p_promocional,pp.cod_motivo_promocoes as p_motivo,pi.fidelidade as i_fidelidade,pi.promocional as i_promocional,
      pb.combo as b_combo,pb.fidelidade as b_fidelidade,pb.promocional as b_promocional,pb.cod_motivo_promocoes as b_motivo,
      pbo.combo as pbo_combo,pbo.fidelidade as pbo_fidelidade,pbo.promocional as pbo_promocional,pbo.cod_motivo_promocoes as pbo_motivo,
      t.numero_nota_fiscal from ipi_estoque e left join ipi_ingredientes i on i.cod_ingredientes = e.cod_ingredientes left join ipi_estoque_entrada_itens eti on eti.cod_estoque_entrada_itens = e.cod_estoque_entrada_itens left join ipi_titulos t on t.cod_estoque_entrada = eti.cod_estoque_entrada left join ipi_pedidos_pizzas pp on pp.cod_pedidos_pizzas = e.cod_pedidos_pizzas left join ipi_pedidos_bordas pbo on pbo.cod_pedidos_bordas = e.cod_pedidos_bordas left join ipi_pedidos_bebidas pb on pb.cod_pedidos_bebidas = e.cod_pedidos_bebidas left join ipi_pedidos_ingredientes pi on pi.cod_pedidos_ingredientes = e.cod_pedidos_ingredientes where e.cod_pizzarias in($cod_pizzarias) and data_hora_lancamento between '$data_inicial 00:00:00' and '$data_final 23:59:59'";

      if($ingrediente_filtro)
        $sql_buscar_movimentacoes .= " and e.cod_ingredientes = '$ingrediente_filtro'";

     /* if($titulos_subcategorias!="")
        if($titulos_subcategorias!='10')
        {
          $sql_buscar_movimentacoes .= "and i.cod_titulos_subcategorias ='".$titulos_subcategorias."' and e.cod_bebidas_ipi_conteudos < 0"; 
        }
        else
        {
          $sql_buscar_movimentacoes .= " and e.cod_bebidas_ipi_conteudos > 0"; 
        }*/

      //echo $sql_buscar_movimentacoes."<br/><br/>";
      $res_buscar_movimentacoes = mysql_query($sql_buscar_movimentacoes);
      while($obj_buscar_movimentacoes = mysql_fetch_object($res_buscar_movimentacoes))
      {

        if(($ingrediente_filtro!="" && (($obj_buscar_movimentacoes->cod_ingredientes == $ingrediente_filtro) || ($obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos == $ingrediente_filtro) )) || ($ingrediente_filtro=="") )
        {
          if($obj_buscar_movimentacoes->tipo_estoque=="INGREDIENTE")
          {
            $ingredientes_usados[$obj_buscar_movimentacoes->tipo_estoque][] = $obj_buscar_movimentacoes->cod_ingredientes;
            

            $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['total'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['total'] + $obj_buscar_movimentacoes->quantidade;

           // echo "<br/>".$obj_buscar_movimentacoes->cod_ingredientes."SOMOTOTAL<br/>";
            if($obj_buscar_movimentacoes->cod_estoque_tipo_lancamento=='5')
            {
                $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['ajuste'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['ajuste'] + $obj_buscar_movimentacoes->quantidade;
            }
            else
            {
              if($obj_buscar_movimentacoes->quantidade>0)
              {
                $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['entrada'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['entrada'] + $obj_buscar_movimentacoes->quantidade;

                $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['nota_fiscal'] = $obj_buscar_movimentacoes->numero_nota_fiscal."".($item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['nota_fiscal']!="" ? ', ...' : '');
              }
              else
              {
                $venda = true;
                if($obj_buscar_movimentacoes->cod_pedidos_bordas>0)
                {
                  if($obj_buscar_movimentacoes->pbb_promocional == '1' || $obj_buscar_movimentacoes->pbo_combo == '1' || $obj_buscar_movimentacoes->pbo_fidelidade == '1')
                  {
                    if($obj_buscar_movimentacoes->pbo_motivo == '18')
                    {
                      $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['lanche'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['lanche'] + $obj_buscar_movimentacoes->quantidade;
                      $venda = false;
                    }
                    else
                    {
                      if($obj_buscar_movimentacoes->pbo_promocional=='1')
                      {
                        $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['promocional'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['promocional'] + $obj_buscar_movimentacoes->quantidade;
                        $venda = false;
                      }
                      elseif($obj_buscar_movimentacoes->pbo_combo == '1')
                      {
                        $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['combo'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['combo'] + $obj_buscar_movimentacoes->quantidade;
                        $venda = false;
                      }
                      elseif($obj_buscar_movimentacoes->pbo_fidelidade == '1')
                      {
                        $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['fidelidade'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['fidelidade'] + $obj_buscar_movimentacoes->quantidade;
                        $venda = false;
                      }
                    }
                  }
                }
                elseif($obj_buscar_movimentacoes->cod_pedidos_ingredientes > 0)
                {
                  if($obj_buscar_movimentacoes->i_promocional == '1' || $obj_buscar_movimentacoes->i_combo == '1' || $obj_buscar_movimentacoes->i_fidelidade == '1')
                  {
                    if($obj_buscar_movimentacoes->i_motivo == '18')
                    {
                      $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['lanche'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['lanche'] + $obj_buscar_movimentacoes->quantidade;
                      $venda = false;
                    }
                    else
                    {
                      if($obj_buscar_movimentacoes->i_promocional=='1')
                      {
                        $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['promocional'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['promocional'] + $obj_buscar_movimentacoes->quantidade;
                        $venda = false;
                      }
                      elseif($obj_buscar_movimentacoes->i_combo == '1')
                      {
                        $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['combo'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['combo'] + $obj_buscar_movimentacoes->quantidade;
                        $venda = false;
                      }
                      elseif($obj_buscar_movimentacoes->i_fidelidade == '1')
                      {
                        $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['fidelidade'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['fidelidade'] + $obj_buscar_movimentacoes->quantidade;
                        $venda = false;
                      }
                    }
                  }
                }
                elseif($obj_buscar_movimentacoes->cod_pedidos_pizzas > 0)
                {
                  if($obj_buscar_movimentacoes->p_promocional == '1' || $obj_buscar_movimentacoes->p_combo == '1' || $obj_buscar_movimentacoes->p_fidelidade == '1')
                  {
                    if($obj_buscar_movimentacoes->p_motivo == '18')
                    {
                      $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['lanche'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['lanche'] + $obj_buscar_movimentacoes->quantidade;
                      $venda = false;
                    }
                    else
                    {
                      if($obj_buscar_movimentacoes->p_promocional=='1')
                      {
                        $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['promocional'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['promocional'] + $obj_buscar_movimentacoes->quantidade;
                        $venda = false;
                      }
                      elseif($obj_buscar_movimentacoes->p_combo == '1')
                      {
                        $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['combo'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['combo'] + $obj_buscar_movimentacoes->quantidade;
                        $venda = false;
                      }
                      elseif($obj_buscar_movimentacoes->p_fidelidade == '1')
                      {
                        $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['fidelidade'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['fidelidade'] + $obj_buscar_movimentacoes->quantidade;
                        $venda = false;
                      }
                    }
                  }
                }
                
                if($venda!=false)
                {
                  $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['saida'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_ingredientes]['saida'] + $obj_buscar_movimentacoes->quantidade;
                }
              }
            }

          }
          else if($obj_buscar_movimentacoes->tipo_estoque=="BEBIDA")
          {
            $ingredientes_usados[$obj_buscar_movimentacoes->tipo_estoque][] = $obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos;

            $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['total'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['total'] + $obj_buscar_movimentacoes->quantidade;

            if($obj_buscar_movimentacoes->cod_estoque_tipo_lancamento=='5')
            {
              $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['ajuste'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['ajuste'] + $obj_buscar_movimentacoes->quantidade;
            }
            else
            {
              if($obj_buscar_movimentacoes->quantidade>0)
              {
                $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['entrada'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['entrada'] + $obj_buscar_movimentacoes->quantidade;

                $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['nota_fiscal'] = $obj_buscar_movimentacoes->numero_nota_fiscal."".($item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['nota_fiscal']!="" ? ', ...' : '');
              }
              else
              {
                $venda = true;
                if($obj_buscar_movimentacoes->b_promocional == '1' || $obj_buscar_movimentacoes->b_combo == '1' || $obj_buscar_movimentacoes->b_fidelidade == '1')
                {
                  if($obj_buscar_movimentacoes->b_motivo == '18')
                  {
                    $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['lanche'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['lanche'] + $obj_buscar_movimentacoes->quantidade;
                    $venda  = false;
                  }
                  elseif($obj_buscar_movimentacoes->b_promocional=='1')
                  {
                    $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['promocional'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['promocional'] + $obj_buscar_movimentacoes->quantidade;
                    $venda  = false;
                  }
                  elseif($obj_buscar_movimentacoes->b_combo=='1')
                  {
                    $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['combo'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['combo'] + $obj_buscar_movimentacoes->quantidade;
                    $venda  = false;
                  }
                  elseif($obj_buscar_movimentacoes->b_fidelidade)
                  {
                    $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['fidelidade'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['fidelidade'] + $obj_buscar_movimentacoes->quantidade;
                    $venda  = false;
                  }
                }

                if($venda)
                {
                  $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['saida'] = $item[date("Y-m-d",strtotime($obj_buscar_movimentacoes->data_hora_lancamento))][$obj_buscar_movimentacoes->tipo_estoque][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos]['saida'] + $obj_buscar_movimentacoes->quantidade;
                }
              }   
            }     
          }
        }

      }
      $arr_consumo1 = array();
      $arr_bebidas1 = array();
      $arr_precos = array();
                          //ingredientes padroes
        $sql_buscar_pizzas = "SELECT pf.cod_pizzas,pp.cod_tamanhos,pp.quant_fracao,ie.cod_ingredientes,ie.quantidade_estoque_ingrediente,pp.cod_pedidos_pizzas from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_pedidos_fracoes pf on pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_ingredientes_estoque ie on ie.cod_pizzas = pf.cod_pizzas where ie.cod_tamanhos = pp.cod_tamanhos and p.cod_pedidos in (SELECT p.cod_pedidos from ipi_pedidos p inner join ipi_pizzarias pi on pi.cod_pizzarias = p.cod_pizzarias where p.cod_pizzarias in ($cod_pizzarias) AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.situacao IN ('BAIXADO') AND p.cod_pizzarias IN ($cod_pizzarias))";

        $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
        //echo $sql_buscar_pizzas;
        while($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
        {
          $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] = $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] + ($obj_buscar_pizzas->quantidade_estoque_ingrediente / $obj_buscar_pizzas->quant_fracao);
          //echo "<br/>pedido= ".$obj_buscar_pizzas->cod_pedidos_pizzas." tamanho = ".$obj_buscar_pizzas->cod_tamanhos." fracao=".$obj_buscar_pizzas->quant_fracao." qtd=".($obj_buscar_pizzas->quantidade_estoque_ingrediente / $obj_buscar_pizzas->quant_fracao);
        }

        $obj_buscar_pizzas = "";
        $sql_buscar_pizzas2 = "SELECT pf.cod_pizzas,pp.cod_tamanhos,pp.quant_fracao,it.cod_ingredientes,it.quantidade_estoque_extra,pp.cod_pedidos_pizzas from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_pedidos_fracoes pf on pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_pedidos_ingredientes pe on pe.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_ingredientes_ipi_tamanhos it on it.cod_ingredientes = pe.cod_ingredientes where it.cod_pizzarias = 1 and it.cod_tamanhos = pp.cod_tamanhos and pe.ingrediente_padrao = 0  and p.cod_pedidos in (SELECT p.cod_pedidos from ipi_pedidos p inner join ipi_pizzarias pi on pi.cod_pizzarias = p.cod_pizzarias where p.cod_pizzarias in ($cod_pizzarias) AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.situacao IN ('BAIXADO') AND p.cod_pizzarias IN ($cod_pizzarias))";

        $res_buscar_pizzas2 = mysql_query($sql_buscar_pizzas2);
        //echo "<br/>".$sql_buscar_pizzas2;
        while($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas2))
        {
          $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] = $arr_consumo1[$obj_buscar_pizzas->cod_ingredientes] + ($obj_buscar_pizzas->quantidade_estoque_extra / $obj_buscar_pizzas->quant_fracao);
          //echo "<br/>22pedido= ".$obj_buscar_pizzas->cod_pedidos_pizzas." tamanho = ".$obj_buscar_pizzas->cod_tamanhos." fracao=".$obj_buscar_pizzas->quant_fracao." qtd=".($obj_buscar_pizzas->quantidade_estoque_extra / $obj_buscar_pizzas->quant_fracao);
        }
        $sql_buscar_pizzas2 = "SELECT pb.cod_bebidas_ipi_conteudos,pb.quantidade,p.cod_pedidos from ipi_pedidos p inner join ipi_pedidos_bebidas pb on p.cod_pedidos = pb.cod_pedidos where p.cod_pedidos in (SELECT p.cod_pedidos from ipi_pedidos p inner join ipi_pizzarias pi on pi.cod_pizzarias = p.cod_pizzarias where p.cod_pizzarias in ($cod_pizzarias) AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.situacao IN ('BAIXADO') AND p.cod_pizzarias IN ($cod_pizzarias))";

        $res_buscar_pizzas2 = mysql_query($sql_buscar_pizzas2);
        //echo "<br/>".$sql_buscar_pizzas2;
        while($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas2))
        {
          $arr_bebidas1[$obj_buscar_pizzas->cod_bebidas_ipi_conteudos] = $arr_bebidas1[$obj_buscar_pizzas->cod_bebidas_ipi_conteudos] + ($obj_buscar_pizzas->quantidade);
          //echo "<br/>22pedido= ".$obj_buscar_pizzas->cod_pedidos_pizzas." tamanho = ".$obj_buscar_pizzas->cod_tamanhos." fracao=".$obj_buscar_pizzas->quant_fracao." qtd=".($obj_buscar_pizzas->quantidade_estoque_extra / $obj_buscar_pizzas->quant_fracao);
        }
        /*echo "<pre>";
        print_r($ingredientes_usados);
        echo "</pre><br/><br/><br/>";*/

        foreach($ingredientes_usados["INGREDIENTE"] as $pos => $indice)
        {
          $sql_preco_ingrediente = "SELECT uni.divisor_comum,preco_unitario_entrada, quantidade_embalagem_entrada, iee.data_hota_entrada_estoque, it.numero_nota_fiscal FROM ipi_estoque_entrada iee INNER JOIN ipi_estoque_entrada_itens ieei ON (iee.cod_estoque_entrada = ieei.cod_estoque_entrada) INNER JOIN ipi_ingredientes i on i.cod_ingredientes = ieei.cod_ingredientes LEFT JOIN ipi_unidade_padrao uni on uni.cod_unidade_padrao = i.cod_unidade_padrao LEFT JOIN ipi_titulos it ON (it.cod_estoque_entrada = iee.cod_estoque_entrada) WHERE iee.data_hota_entrada_estoque < '$data_final 23:59:59' AND ieei.cod_ingredientes = '".$indice."' AND iee.cod_pizzarias = '".$cod_pizzarias."' ORDER BY iee.data_hota_entrada_estoque DESC LIMIT 1";//AND ieei.cod_ingredientes = '".$indice."'
          //echo '<br>'.$sql_preco_ingrediente.'<br>';
          $res_preco_ingrediente = mysql_query($sql_preco_ingrediente);
          $obj_preco_ingrediente = mysql_fetch_object($res_preco_ingrediente);

         // $arr_valores[$indice]['numero_nota_fiscal'] = $obj_preco_ingrediente->numero_nota_fiscal;

          $arr_precos["INGREDIENTE"][$indice]['quantidade_embalagem_entrada'] = ($obj_preco_ingrediente->quantidade_embalagem_entrada ? $obj_preco_ingrediente->quantidade_embalagem_entrada: 1);/// (($obj_preco_ingrediente->divisor_comum ? $obj_preco_ingrediente->divisor_comum  : 1)) 
          
          $arr_precos["INGREDIENTE"][$indice]['quantidade_total'] = $arr_precos["INGREDIENTE"][$indice]['quantidade_embalagem_entrada'];
          $arr_precos["INGREDIENTE"][$indice]['preco_unitario_entrada'] = $obj_preco_ingrediente->preco_unitario_entrada;
          $date = strtotime($obj_preco_ingrediente->data_hota_entrada_estoque); 
          $arr_precos["INGREDIENTE"][$indice]['data'] = ($date ? date('Y-m-d', $date) : '-');
          $arr_precos["INGREDIENTE"][$indice]['preco_grama'] = ($arr_precos["INGREDIENTE"][$indice]['preco_unitario_entrada']/$arr_precos["INGREDIENTE"][$indice]['quantidade_embalagem_entrada']);

         /*echo "<br/> $indice -$pos - ".$arr_precos["INGREDIENTE"][$pos]['preco_unitario_entrada']." - ".$arr_precos["INGREDIENTE"][$pos]['quantidade_embalagem_entrada'];
          echo "<br/> $indice -$pos - ".$obj_preco_ingrediente->preco_unitario_entrada." - ".$obj_preco_ingrediente->quantidade_embalagem_entrada."<br/>";*/

        }
        /*echo "<pre>";
        print_r($arr_precos);
        echo "</pre><br/><br/><br/>";*/
        if(is_array($ingredientes_usados["BEBIDA"]))
        {
          foreach($ingredientes_usados["BEBIDA"] as $pos => $indice)
          {
            $sql_preco_ingrediente = "SELECT preco_unitario_entrada, quantidade_embalagem_entrada, iee.data_hota_entrada_estoque, it.numero_nota_fiscal FROM ipi_estoque_entrada iee INNER JOIN ipi_estoque_entrada_itens ieei ON (iee.cod_estoque_entrada = ieei.cod_estoque_entrada) LEFT JOIN ipi_titulos it ON (it.cod_estoque_entrada = iee.cod_estoque_entrada) WHERE iee.data_hota_entrada_estoque < '$data_final 23:59:59' AND ieei.cod_bebidas_ipi_conteudos = '".$indice."' AND iee.cod_pizzarias = '".$cod_pizzarias."' ORDER BY iee.data_hota_entrada_estoque DESC LIMIT 1";//AND ieei.cod_ingredientes = '".$indice."'
            //echo $sql_preco_ingrediente.'<br>';
            $res_preco_ingrediente = mysql_query($sql_preco_ingrediente);
            $obj_preco_ingrediente = mysql_fetch_object($res_preco_ingrediente);

           // $arr_valores[$indice]['numero_nota_fiscal'] = $obj_preco_ingrediente->numero_nota_fiscal;

            $arr_precos["BEBIDA"][$indice]['quantidade_embalagem_entrada'] = ($obj_preco_ingrediente->quantidade_embalagem_entrada ? $obj_preco_ingrediente->quantidade_embalagem_entrada : 1);
            
            $arr_precos["BEBIDA"][$indice]['quantidade_total'] = $arr_precos["BEBIDA"][$indice]['quantidade_embalagem_entrada'];
            $arr_precos["BEBIDA"][$indice]['preco_unitario_entrada'] = ($obj_preco_ingrediente->preco_unitario_entrada ? $obj_preco_ingrediente->preco_unitario_entrada : 0);
            $date = strtotime($obj_preco_ingrediente->data_hota_entrada_estoque); 
            $arr_precos["BEBIDA"][$indice]['data'] = ($date ? date('d/m/Y', $date) : '-');
            $arr_precos["BEBIDA"][$indice]['preco_grama'] = ($arr_precos["BEBIDA"][$indice]['preco_unitario_entrada']/($obj_preco_ingrediente->quantidade_embalagem_entrada ? $obj_preco_ingrediente->quantidade_embalagem_entrada : 1));

          }
        }
        ?>
        <table class="listaEdicao" cellpadding="0" cellspacing="0" align="center">
        <thead>
          <tr>
            <td align="center" width="30" align="right">Cod Item</td>
            <td align="center" width="250" align="center">Item de estoque</td>
            <td align="center" width="90" align="center">Grupo</td>
            <td align="center" width="90" align="cener">data da movimentação</td>
            <td align="center" width="90" align="center">Docto de movimentacao</td> 
            <!-- <td align="center" width="90" align="center">Tipo de <br/>Movimentação</td> -->
            <td align="center" width="90" align="right">Saldo anterior</td>
            <td align="center" width="90" align="right">Compras</td>
            <td align="center" width="90" align="right">Ajustes de Iventario</td>
    <!--           <td align="center" width="90" align="right">Devoluções</td>
            <td align="center" width="90" align="right">Amostras</td>
            <td align="center" width="90" align="right">Rebate</td> -->
            <td align="center" width="90" align="right">Venda</td>
            <td align="center" width="90" align="right">Fidelidade</td>
            <td align="center" width="90" align="right">Combo</td>
            <td align="center" width="90" align="right">Promoções</td>
            <td align="center" width="90" align="right">Lanche</td>
            <td align="center" width="90" align="right">Saldo Final</td>
          </tr>
        </thead>
        <tbody>
        <?
       /* echo "<pre>";
        print_r($item);
        echo "</pre><br/><br/><br/>";*/

        /*echo "<pre>";
        print_r($pAnt);
        echo "</pre><br/><br/><br/>";
    echo "<pre>";
                print_r($arr_unidade_padrao);
                echo "</pre>";*/
      $ing_exist = array();
      
      /*$SqlBuscaIngredientes = "SELECT i.cod_ingredientes , (SELECT SUM(e.quantidade) FROM ipi_estoque e WHERE e.cod_ingredientes = i.cod_ingredientes AND e.cod_pizzarias IN ($cod_pizzarias) ) quantidade_atual FROM ipi_ingredientes i LEFT JOIN ipi_unidade_padrao up ON (up.cod_unidade_padrao = i.cod_unidade_padrao) ORDER BY i.ingrediente";
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
      }*/

      $arr_anterior = array();
      $cont_ing = array();   
      
      $sql_deletar_movimentacoes = "DELETE FROM ipi_estoque_mapa where data_movimentacao = '$data_inicial' and cod_pizzarias = '$cod_pizzarias' $filtro_ingrediente_excluir";
      $res_deletar_movimentacoes = mysql_query($sql_deletar_movimentacoes);
      
      foreach($item as $diamov => $ings)
      {
        foreach ($ings as $ing => $items) 
        {
            foreach ($items as $cod => $infos) 
            {
                $saldo_atual = 0;
                $saldo_anterior = 0;

                if($cont_ing[$ing][$cod]>-1) 
                {
                  $cont_ing[$ing][$cod] = $cont_ing[$ing][$cod] + 1;
                  $saldo_anterior = $arr_anterior[$ing][$cod][($cont_ing[$ing][$cod] - 1)];
                }
                else
                {
                  $cont_ing[$ing][$cod] = 0;
                  $saldo_anterior = $pAnt[$ing][$cod]['saldo_anterior']; 
                }


                //$saldo_anterior = $saldo_anterior + $infos["entrada"];
                $entrada = $infos["entrada"];
                $ajuste = $infos["ajuste"];
                $saida = $infos["saida"];
                //$arr_anterior[$ing][$cod][] = $total;

                $total = $saldo_anterior + $infos["entrada"] + $infos["ajuste"] - ($infos["saida"]*-1) - ($infos["combo"]*-1) - ($infos["fidelidade"]*-1) - ($infos["lanche"]*-1) - ($infos["promocional"]*-1);
                //$entrada = $infos["entrada"];

                $combo = $infos["combo"];
                $fidelidade = $infos["fidelidade"];
                $promocional = $infos["promocional"];
                $lanche = $infos["lanche"];

                $nome_exibicao = $pAnt[$ing][$cod]['nome'];
                $arr_anterior[$ing][$cod][] = $total;
                $ing_cod = 0;
                $consumo_teorico = 0;
                $beb_cod = 0;
                $preco_grama = 0;
                if($ing=="INGREDIENTE")
                {
                  /*echo "<pre>";
                  print_r($arr_unidade_padrao);
                  echo "</pre>";die();*/
                  $unidade_padrao = $arr_unidade_padrao[$pAnt[$ing][$cod]['unidade']];
                  //$entrada = round(($entrada/$unidade_padrao["divisor"]),2);
                  $entrada = round(($entrada),3);
                  $ajuste = round(($ajuste),3);
                  $combo =abs((round(($combo),3)));
                  $fidelidade =  abs((round(($fidelidade),3)));
                  $lanche =  abs((round(($lanche),3)));
                  $promocional =  abs((round(($promocional),3)));

                  $saida = abs((round(($saida),3)));
                  $total = round(($total),3);
                  $saldo_anterior =  round(($saldo_anterior),3);
                  $nome_exibicao = $nome_exibicao." (".$unidade_padrao["abr"].")";
                  $ing_cod = $cod;

                  /*$sql_buscar_ultima_nota = "SELECT eti.quantidade_entrada,eti.quantidade_embalagem_entrada,et.cod_estoque_entrada from ipi_estoque_entrada et inner join ipi_estoque_entrada_itens eti on eti.cod_estoque_entrada = et.cod_estoque_entrada where et.cod_pizzarias='$cod_pizzarias' and eti.cod_ingredientes='$cod' order by et.cod_estoque_entrada DESC";
                  $res_buscar_ultima_nota = mysql_query($sql_buscar_ultima_nota);
                  $obj_buscar_ultima_nota = mysql_fetch_object($res_buscar_ultima_nota);*/
                  $consumo_teorico = $arr_consumo1[$cod];
                  $preco_grama = $arr_precos["INGREDIENTE"][$cod]['preco_grama'];
                  $preco_entrada = $arr_precos["INGREDIENTE"][$cod]['preco_unitario_entrada'];
                  $quantidade_entrada = $arr_precos["INGREDIENTE"][$cod]['quantidade_embalagem_entrada'];
                  $data_entrada = $arr_precos["INGREDIENTE"][$cod]['data'];
                }
                else
                {
                  $nome_exibicao = $nome_exibicao." (unit)"; 
                  $beb_cod = $cod;
                  $consumo_teorico = $arr_bebidas1[$cod];       
                  $preco_grama = $arr_precos["BEBIDA"][$cod]['preco_grama'];
                  $preco_entrada = $arr_precos["BEBIDA"][$cod]['preco_unitario_entrada'];
                  $quantidade_entrada = $arr_precos["BEBIDA"][$cod]['quantidade_embalagem_entrada'];
                  $data_entrada = $arr_precos["BEBIDA"][$cod]['data'];
                  $combo =abs($combo);
                  $fidelidade =  abs($fidelidade);
                  $lanche =  abs($lanche);
                  $promocional =  abs($promocional);
                  $saida = abs($saida);
                }
                $saldo_anterior = round($saldo_anterior,3);



                echo "<tr>";
                echo "<td>$cod</td>";
                echo "<td align='center'><a href='javascript:void(0);' onclick='detalhes_ingrediente(\"".$cod."\",\"".$nome_exibicao."\",\"".$unidade_padrao["abr"]."\",\"".$unidade_padrao["divisor"]."\",\"".$diamov."\",\"".($ing=="INGREDIENTE" ? 'ingrediente' : 'bebida')."\")'>".$consumo_teorico." - ".$nome_exibicao." - ".$arr_consumo1[$ing_cod]."</a></td>";
                echo "<td align='center'>".$pAnt[$ing][$cod]['grupo']."</td>";
                echo "<td align='center'>".date("d/m/Y",strtotime($diamov))."</td>";
                echo "<td align='center'>".$infos["nota_fiscal"]."</td>";
                //echo "<td align='center'>Venda</td>";
                echo "<td align='right'>".bd2moeda($saldo_anterior)."</td>";
                echo "<td align='right'>".bd2moeda($entrada)."</td>";
                echo "<td align='right'>".bd2moeda($ajuste)."</td>";
                //echo "<td align='right'>dev</td>";
                //echo "<td align='right'>amo</td>";
                //echo "<td align='right'>re</td>";
                echo "<td align='right'>".bd2moeda(abs($saida))."</td>";
                echo "<td align='right'>".bd2moeda($fidelidade)."</td>";
                echo "<td align='right'>".bd2moeda($combo)."</td>";
                echo "<td align='right'>".bd2moeda($promocional)."</td>";
                echo "<td align='right'>".bd2moeda($lanche)."</td>";
                echo "<td align='right'>".bd2moeda($total)."</td>";
                echo "</tr>";

                $sql_inserir_mapa_estoque = sprintf("INSERT INTO $tabela (cod_pizzarias, cod_ingredientes, cod_bebidas_ipi_conteudos , data_movimentacao , quantidade_teorico , quantidade_compras , quantidade_ajuste , quantidade_vendas , ultima_compra_data , ultima_compra_valor , ultima_compra_quantidade , ultima_compra_preco_grama , saldo_inicial , saldo_final , quantidade_fidelidade , quantidade_combo , quantidade_lanche , quantidade_promocao) values(%d, %d, %d, '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",$cod_pizzarias, $ing_cod, $beb_cod, $data_inicial,$consumo_teorico,$entrada,$ajuste,$saida,$data_entrada,$preco_entrada,$quantidade_entrada,$preco_grama,$saldo_anterior,$total,$fidelidade,$combo,$lanche,$promocional);
                //echo "<br/>".$sql_inserir_mapa_estoque."</br>";
                $res_inserir_mapa_estoque = mysql_query($sql_inserir_mapa_estoque);

              //}
            }
          }
        }
        /*echo "<pre>";
        print_r($arr_anterior);
        echo "</pre><br/><br/><br/>";
              echo "<pre>";
        print_r($cont_ing);
        echo "</pre><br/><br/><br/>";   */ 
             /* arsort($arr_consumo1);
              $arr_nome_ing = array();

              $sql_buscar_uni_padrao = "SELECT u.cod_unidade_padrao,i.ingrediente,i.cod_ingredientes,u.abreviatura,u.divisor_comum from ipi_unidade_padrao u inner join ipi_ingredientes i on i.cod_unidade_padrao = u.cod_unidade_padrao";
              $res_buscar_uni_padrao = mysql_query($sql_buscar_uni_padrao);
              while($obj_buscar_uni_padrao = mysql_fetch_object($res_buscar_uni_padrao))
              {
                $unidades[$obj_buscar_uni_padrao->cod_unidade_padrao]['abr'] = $obj_buscar_uni_padrao->abreviatura;
                $unidades[$obj_buscar_uni_padrao->cod_unidade_padrao]['divisor'] = $obj_buscar_uni_padrao->divisor_comum;
                $ing_unidade[$obj_buscar_uni_padrao->cod_ingredientes] = $obj_buscar_uni_padrao->cod_unidade_padrao;
                $arr_nome_ing[$obj_buscar_uni_padrao->cod_ingredientes] = $obj_buscar_uni_padrao->ingrediente;
              }


              foreach ( $arr_consumo1 as $ingrediente => $quantidade) 
              {
                $arr_unidade = $unidades[$ing_unidade[$ingrediente]];
                $nome_ingrediente =  $arr_nome_ing[$ingrediente];
                $quant_dividida = ($quantidade/$arr_unidade['divisor']);
                $quant_exibir = round($quant_dividida,2)." ".$arr_unidade['abr'];
                echo "<tr><td align='center'><a href='javascript:void(0);' onclick='detalhes_ingrediente(\"".$ingrediente."\",\"".$nome_ingrediente."\",\"".$arr_unidade['abr']."\",\"".$arr_unidade['divisor']."\")'>".$nome_ingrediente."</a></td><td align='center'>".$quant_exibir."</td></tr>";
              }*/
      ?>
          </tbody>
          </table>
        




        <!-- Barra Lateral -->
        
        </tr></table><br/><br/><br/><br/>
      <?
    }
     //$sql_atualizar_andamento = "UPDATE ipi_processamento_estoque_fila set situacao = 'FINALIZADO' where cod_processamento_fila='$cod_processamento_fila'";
    $sql_atualizar_andamento = "UPDATE ipi_processamento_estoque_fila pef inner join ipi_processamento_estoque pe on pe.cod_processamento = pef.cod_processamento set pef.situacao = 'FINALIZADO', pef.data_hora_fim = NOW() where pe.cod_pizzarias_processar='$cod_pizzarias_fila' and pe.cod_ingredientes_processar = '$ingrediente_filtro' and pef.data_processamento = '$data_final' and pef.situacao='ANDAMENTO'";
    //echo $sql_atualizar_andamento;
  $res_atualizar_andamento = mysql_query($sql_atualizar_andamento);
}
 

desconectar_bd($con);
echo "<br/>".date("d/m/Y H:i:s");

?>

</body>
</html>
