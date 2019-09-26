<?php


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/pedido.php';


$acao = validaVarPost('acao');
switch($acao) {
  case 'carregar_pizzas':
    $id = validaVarPost('id');//\"pizzastr_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_tamanhos."\"
    $dados = explode('_',$id);
    $filtro = '';
    $con = conectar_bd();
    $data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('d/m/Y'));
    $data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('d/m/Y'));
    $cod_pizzarias = validaVarPost('cod_pizzarias');
    $entrega = validaVarPost('entrega');
    if($cod_pizzarias !="")
    {
      $filtro .= " AND p.cod_pizzarias = '$cod_pizzarias'";
    }

    if($entrega !="")
    {
      $filtro .= " AND p.tipo_entrega = '$entrega'";
    }

    if(($data_inicial) && ($data_final)) {
      $filtro .= " AND p.data_hora_pedido >= '$data_inicial' AND p.data_hora_pedido <= '$data_final'";
     // $filtro .= " AND p.data_hora_pedido >= '$data_inicial_sql' AND p.data_hora_pedido <= '$data_final_sql'";
    }

    if($dados[1]=='salgada' || $dados[1]=='doce')
    {
      $sql_buscar_total_pizzas = "SELECT pp.cod_pedidos_pizzas,pp.quant_fracao,pf.fracao,t.tamanho,p.cod_pedidos,p.data_hora_pedido,pf.preco as tamanho_texto,t.cod_tamanhos as codigo_tamanho,pp.quant_fracao as quantidade_fracao from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_tamanhos t on t.cod_tamanhos = pp.cod_tamanhos INNER JOIN ipi_pedidos_fracoes pf ON pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas
INNER JOIN ipi_pizzas pizza ON pizza.cod_pizzas = pf.cod_pizzas where p.cod_usuarios_pedido = '".$dados[2]."' and p.situacao='BAIXADO' ";
if ($dados[1]=='salgada')
{
  $sql_buscar_total_pizzas.= "and (pizza.tipo='".ucfirst($dados[1])."' OR pizza.tipo='Salgado')";
}
else
{
  $sql_buscar_total_pizzas.= "and pizza.tipo='".ucfirst($dados[1])."'";
}

 $sql_buscar_total_pizzas.= " and pp.cod_tamanhos='".$dados[3]."'";

      $sql_buscar_total_pizzas .= $filtro." GROUP BY pp.cod_pedidos_pizzas";
      $res_buscar_total_pizzas = mysql_query($sql_buscar_total_pizzas);
      // echo $sql_buscar_total_pizzas;
      echo '<table class="listaEdicao" cellpadding="0" cellspacing="0">';
      echo "<thead>";

      echo "<td>Codigo do pedido</td>";
      echo "<td>Horario do pedido</td>";
      echo "<td>Total do pedido</td>";

      echo "</thead><tbody>";
      while($obj_buscar_total_pizzas = mysql_fetch_object($res_buscar_total_pizzas))
      {
        echo "<tr>";
        echo "<td><a  target='_blank' href='ipi_rel_historico_pedidos.php?p=".$obj_buscar_total_pizzas->cod_pedidos."'>".$obj_buscar_total_pizzas->cod_pedidos."</a></td>";
        echo "<td>".bd2datahora($obj_buscar_total_pizzas->data_hora_pedido)."</td>";
        echo "<td>".bd2moeda($obj_buscar_total_pizzas->tamanho_texto)."</td>";
        echo "</tr>";

      }
      echo "</tbody></table>";
    }
    else
    {  
     
      $sql_buscar_total_pizzas = "SELECT p.cod_pedidos,p.valor_total,p.data_hora_pedido,pp.cod_pedidos_pizzas as total_vendidas,t.tamanho,t.cod_tamanhos, ipf.preco from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_tamanhos t on t.cod_tamanhos = pp.cod_tamanhos INNER JOIN ipi_pedidos_fracoes ipf ON (ipf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) where p.cod_usuarios_pedido = '".$dados[1]."' and p.situacao='BAIXADO' and pp.cod_tamanhos = '".$dados[2]."'";
       $sql_buscar_total_pizzas.= $filtro;
      // echo $sql_buscar_total_pizzas."<br/><br/>";
      $res_buscar_total_pizzas = mysql_query($sql_buscar_total_pizzas);
        
      echo '<table class="listaEdicao" cellpadding="0" cellspacing="0">';
      echo "<thead>";

      echo "<td>Codigo do pedido</td>";
      echo "<td>Horario do pedido</td>";
      echo "<td>Total do pedido</td>";

      $total_por_atendente = 0;
      echo "</thead><tbody>";
      while($obj_buscar_total_pizzas = mysql_fetch_object($res_buscar_total_pizzas))
      {
        echo "<tr>";
        echo "<td><a target='_blank' href='ipi_rel_historico_pedidos.php?p=".$obj_buscar_total_pizzas->cod_pedidos."'>".$obj_buscar_total_pizzas->cod_pedidos."</a></td>";
        echo "<td>".bd2datahora($obj_buscar_total_pizzas->data_hora_pedido)."</td>";
        echo "<td>".bd2moeda($obj_buscar_total_pizzas->preco)."</td>";
        echo "</tr>";
        $total_por_atendente += $obj_buscar_total_pizzas->preco;

      }

        echo "<tr>";
        echo "<td>&nbsp;</td>";
        echo "<td>Total:</td>";
        echo "<td>".bd2moeda($total_por_atendente)."</td>";
        echo "</tr>";


      echo "</tbody></table>";
    }

    desconectar_bd($con);
  break;
  case 'carregar_bebidas':
    $id = validaVarPost('id');//\"pizzastr_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_tamanhos."\"
    $dados = explode('_',$id);

    $data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('d/m/Y'));
    $data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('d/m/Y'));
    $cod_pizzarias = validaVarPost('cod_pizzarias');
    $entrega = validaVarPost('entrega');

    $con = conectar_bd();
    //$sql_buscar_total_pizzas = "SELECT p.cod_pedidos,p.valor_total,p.data_hora_pedido,pp.cod_pedidos_pizzas as total_vendidas,t.tamanho,t.cod_tamanhos from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_tamanhos t on t.cod_tamanhos = pp.cod_tamanhos where p.cod_usuarios_pedido = '".$dados[1]."' and p.situacao='BAIXADO' and pp.cod_tamanhos = '".$dados[2]."'";

    $sql_buscar_total_pizzas = "SELECT pb.preco as preco_ind,pb.cod_pedidos_bebidas as total_vendidas,pb.quantidade,p.cod_pedidos,p.valor_total,p.data_hora_pedido,bc.cod_bebidas_ipi_conteudos,c.conteudo,b.bebida from ipi_pedidos_bebidas pb inner join ipi_pedidos p on p.cod_pedidos = pb.cod_pedidos inner join ipi_bebidas_ipi_conteudos bc on bc.cod_bebidas_ipi_conteudos = pb.cod_bebidas_ipi_conteudos inner join ipi_conteudos c on c.cod_conteudos = bc.cod_conteudos inner join ipi_bebidas b on b.cod_bebidas = bc.cod_bebidas where p.cod_usuarios_pedido = '".$dados[1]."' and p.situacao='BAIXADO' and pb.cod_bebidas_ipi_conteudos = '".$dados[2]."'";

    if($cod_pizzarias !="")
    {
      $sql_buscar_total_pizzas .= " AND p.cod_pizzarias = '$cod_pizzarias'";
    }

    if($entrega !="")
    {
      $sql_buscar_total_pizzas .= " AND p.tipo_entrega = '$entrega'";
    }

    if(($data_inicial) && ($data_final)) {
      $sql_buscar_total_pizzas .= " AND p.data_hora_pedido >= '$data_inicial' AND p.data_hora_pedido <= '$data_final'";
    }
    //echo $sql_buscar_total_pizzas."<br/><br/>";
    $res_buscar_total_pizzas = mysql_query($sql_buscar_total_pizzas);
      
    echo '<table id="pizzastdconteudo_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" class="listaEdicao" cellpadding="0" cellspacing="0">';
    echo "<thead>";

    echo "<td width='20%'>Quantidae</td>";
    echo "<td width='20%'>Preco Individual das Bebidas</td>";
    echo "<td width='20%'>Total das bebidas</td>";
    echo "<td width='20%'>Codigo do pedido</td>"; 
    echo "<td width='20%'>Horario do pedido</td>";


    echo "</thead><tbody>";
    $total_coluna = 0;
    while($obj_buscar_total_pizzas = mysql_fetch_object($res_buscar_total_pizzas))
    {
      echo "<tr>";
      echo "<td>".$obj_buscar_total_pizzas->quantidade."</td>";
      echo "<td>R$ ".bd2moeda($obj_buscar_total_pizzas->preco_ind)."</td>";
      echo "<td>R$ ".bd2moeda($obj_buscar_total_pizzas->preco_ind*$obj_buscar_total_pizzas->quantidade)."</td>";
      echo "<td><a target='_blank' href='ipi_rel_historico_pedidos.php?p=".$obj_buscar_total_pizzas->cod_pedidos."'>".$obj_buscar_total_pizzas->cod_pedidos."</a></td>";
      echo "<td>".bd2datahora($obj_buscar_total_pizzas->data_hora_pedido)."</td>";
      $total_coluna += $obj_buscar_total_pizzas->preco_ind*$obj_buscar_total_pizzas->quantidade;
      echo "</tr>";

    }

    echo "<tr>";
    echo "<td>&nbsp;</td>";
    echo "<td>Total</td>";
    echo "<td>".bd2moeda($total_coluna)."</td>";
    echo "<td>&nbsp;</td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "</tbody></table>";

    desconectar_bd($con);
  break;
  case 'carregar_adicionais':
    $id = validaVarPost('id');//\"pizzastr_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_tamanhos."\"
    $dados = explode('_',$id);

    $data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('d/m/Y'));
    $data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('d/m/Y'));
    $cod_pizzarias = validaVarPost('cod_pizzarias');
    $entrega = validaVarPost('entrega');

    $con = conectar_bd();
    //$sql_buscar_total_pizzas = "SELECT p.cod_pedidos,p.valor_total,p.data_hora_pedido,pp.cod_pedidos_pizzas as total_vendidas,t.tamanho,t.cod_tamanhos from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_tamanhos t on t.cod_tamanhos = pp.cod_tamanhos where p.cod_usuarios_pedido = '".$dados[1]."' and p.situacao='BAIXADO' and pp.cod_tamanhos = '".$dados[2]."'";

    //$sql_buscar_total_pizzas = "SELECT pb.preco as preco_ind,pb.cod_pedidos_bebidas as total_vendidas,pb.quantidade,p.cod_pedidos,p.valor_total,p.data_hora_pedido,bc.cod_bebidas_ipi_conteudos,c.conteudo,b.bebida from ipi_pedidos_bebidas pb inner join ipi_pedidos p on p.cod_pedidos = pb.cod_pedidos inner join ipi_bebidas_ipi_conteudos bc on bc.cod_bebidas_ipi_conteudos = pb.cod_bebidas_ipi_conteudos inner join ipi_conteudos c on c.cod_conteudos = bc.cod_conteudos inner join ipi_bebidas b on b.cod_bebidas = bc.cod_bebidas where p.cod_usuarios_pedido = '".$dados[1]."' and p.situacao='BAIXADO' and pb.cod_bebidas_ipi_conteudos = '".$dados[2]."'";

    $sql_buscar_total_pizzas = "SELECT pi.preco,pizza.pizza,pi.cod_pedidos_ingredientes as total_vendidas,i.ingrediente,i.cod_ingredientes,p.cod_pedidos,p.data_hora_pedido from ipi_pedidos_ingredientes pi inner join ipi_pedidos p on p.cod_pedidos = pi.cod_pedidos inner join ipi_ingredientes i on i.cod_ingredientes = pi.cod_ingredientes inner join ipi_pedidos_fracoes pf on pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes inner join ipi_pizzas pizza on pizza.cod_pizzas = pf.cod_pizzas where p.cod_usuarios_pedido = '".$dados[1]."' and p.situacao='BAIXADO' and pi.cod_ingredientes = '".$dados[2]."' and pi.ingrediente_padrao = 0 ";

    if($cod_pizzarias !="")
    {
      $sql_buscar_total_pizzas .= " AND p.cod_pizzarias = '$cod_pizzarias'";
    }

    if($entrega !="")
    {
      $sql_buscar_total_pizzas .= " AND p.tipo_entrega = '$entrega'";
    }

    if(($data_inicial) && ($data_final)) {
      $sql_buscar_total_pizzas .= " AND p.data_hora_pedido >= '$data_inicial' AND p.data_hora_pedido <= '$data_final'";
    }
    //echo $sql_buscar_total_pizzas."<br/><br/>";
    $res_buscar_total_pizzas = mysql_query($sql_buscar_total_pizzas);
      
    echo '<table id="pizzastdconteudo_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" class="listaEdicao" cellpadding="0" cellspacing="0">';
    echo "<thead>";

    echo "<td width='20%'>Quantidade</td>";
    echo "<td width='20%'>Preco do adicional</td>";
    echo "<td width='20%'>Foi adicionado em</td>";
    echo "<td width='20%'>Codigo do pedido</td>"; 
    echo "<td width='20%'>Horario do pedido</td>";


    echo "</thead><tbody>";
    $total_coluna = 0;
    while($obj_buscar_total_pizzas = mysql_fetch_object($res_buscar_total_pizzas))
    {
      echo "<tr>";
      echo "<td>".utf8_encode($obj_buscar_total_pizzas->ingrediente)."</td>";
      echo "<td>".$obj_buscar_total_pizzas->preco."</td>";
      echo "<td>".utf8_encode($obj_buscar_total_pizzas->pizza)."</td>";
      echo "<td><a target='_blank' href='ipi_rel_historico_pedidos.php?p=".$obj_buscar_total_pizzas->cod_pedidos."'>".$obj_buscar_total_pizzas->cod_pedidos."</a></td>";
      echo "<td>".bd2datahora($obj_buscar_total_pizzas->data_hora_pedido)."</td>";
      echo "</tr>";
      $total_coluna += $obj_buscar_total_pizzas->preco;
    }

    echo "<tr>";
    echo "<td>Total</td>";
    echo "<td>".bd2moeda($total_coluna)."</td>";
    echo "<td>&nbsp;</td>";
    echo "<td>&nbsp;</td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "</tbody></table>";

    desconectar_bd($con);
  break;
}

?>