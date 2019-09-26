<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';

$c_vendas_brutas = 13; // Vendas Brutas
$c_imp_venda = 5; // Imposto sobre venda 
$c_out_rec = 19; // Outras Receitas
$c_ded_venda = 34; // Dedução de Venda ???????????????//
$c_cmv = 3; // Custo de Mercadorias Vendidas
$c_folha_enc = 7; // Folha e encargos
$c_fixo_inst_adm = 2; // Fixo Inst e ADM
$c_var_inst_adm = 11; // Variaveis Inst. e Adm
$c_mat_escr_limp = 20; // Materiais (escritorio e limpeza)
$c_entregas = 30; //Entregas ????????????????????????????????????????????
$c_royalties = 22; //Royalties
$c_fundo_mkt = 26; // Fundo De MKT
$c_mkt_local = 6; // MKT LOCAL
$c_desp_cobr_cart = 10; // Despesa com cobrança e cartões
$c_desp_rec_n_ope = 27; // Despesa / receitas não operacionais
$c_desp_rec_fin = 23; // Despesa / receitas financeiras
$c_depreciacao = 31; // Depreciação ??????????????????????????????????
$c_imp_lucro = 28; //Imposto sobre lucro
$c_prolabore = 25; //Prolabore
$c_amortizacao = 13; //Amortizações ?????????????????????????????????
$c_desp_invest = 21; //Despesas com Investimentos
$c_apt_capital = 33; //Aporte de Capital ????????????????????????????????????/
$c_op_credito = 24; //Operação de Crédito
$c_invest_cap_giro = 13; //Investimento em Capital de Giro ?????????????????????????????
$c_rescisoes = 32;

function floor_dec($number,$precision,$separator)
{
    $numberpart=explode($separator,$number);
    $numberpart[1]=substr_replace($numberpart[1],$separator,$precision,0);
    if($numberpart[0]>=0)
    {$numberpart[1]=floor($numberpart[1]);}
    else
    {$numberpart[1]=ceil($numberpart[1]);}

     $ceil_number= array($numberpart[0],$numberpart[1]);
    return implode($separator,$ceil_number);
}

function porcentagem($valor1,$valor2)
{
    $resultado =  round(($valor1*100/$valor2 ),2);
    $i=2;
    if($valor1==0 || $valor2==0)
    {
        return "0 %";
    }
    else 
    {   
        //echo "<Br/>ar -".($valor1*100/$valor2);
        if($resultado == 100 && $valor1!=$valor2)
        {
            while($resultado == 100 && $valor1!=$valor2)
            {
                $i++;
                if($i>5)
                {
                    break;
                }
                $resultado = floor_dec(($valor1*100/$valor2),$i,".");
            }
        }
        else
        {
            while($resultado==0)
            {
                $i++;
                if($i>5)
                {
                    break;
                }
                //echo "<br/>i ".$resultado;//$length = 0.0045;
                $resultado = floor(($valor1*100/$valor2 ) * pow(10,$i) + .5) * pow(10,($i*-1));
                //echo "<br/>".$resultado." - ".pow(10,$i)." - ".pow(10,($i*-1));
                //print "$length";
               // $resultado =  floor_dec(($valor1*100/$valor2 ),$i,'.');
            }
           
        }
    }

     return $resultado." %";
}

function conv_cat_dinheiro($valor)
{
    if($valor!="")
    {
        return bd2moeda($valor);
    }
    else
    {
        return '0,00';
    }
}
?>

<?
$acao = validaVarPost("acao"); 
switch($acao)
{
    case "carregar_contagens":

    $cod_pizzarias = validaVarPost("cod_pizzarias");
    $cod = validaVarPost("cod");
    $tipo = validaVarPost("tipo");
    $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
    switch($tipo)
    {

        case 'contagem_1':
        $conexao = conectabd();
        $sql_buscar_contagens = "select cod_inventarios,data_hora_contagem from  ipi_estoque_inventario where cod_pizzarias = '".$cod."' and cod_pizzarias in ($cod_pizzarias_usuario) order by data_hora_contagem DESC";
        $res_buscar_contagens = mysql_query($sql_buscar_contagens);
         $num_contagems = mysql_num_rows($res_buscar_contagens);
            if($num_contagems<=0)
            {
               echo utf8_encode('<option value="">Nenhum Inventário para selecionar.</option>');
            }
        while($obj_buscar_contagens = mysql_fetch_object($res_buscar_contagens))
        {
           echo utf8_encode("<option value='".$obj_buscar_contagens->cod_inventarios."'>".bd2data(date("Y-m-d",strtotime($obj_buscar_contagens->data_hora_contagem)))." </option>");
        }


        desconectabd($conexao);

        break;
        case 'contagem_2':
        $conexao = conectabd();
        $sql_buscar_contagens = "select cod_inventarios,data_hora_contagem from  ipi_estoque_inventario where cod_inventarios > '".$cod."' and cod_pizzarias = (select cod_pizzarias from ipi_estoque_inventario where cod_inventarios = '".$cod."') and cod_pizzarias in ($cod_pizzarias_usuario) order by data_hora_contagem DESC";
        $res_buscar_contagens = mysql_query($sql_buscar_contagens);
         $num_contagems = mysql_num_rows($res_buscar_contagens);
            if($num_contagems<=0)
            {
               echo utf8_encode('<option value="">Nenhum Inventário mais recente para selecionar.</option>');
            }
        while($obj_buscar_contagens = mysql_fetch_object($res_buscar_contagens))
        {
            echo "<option value='".$obj_buscar_contagens->cod_inventarios."' >".bd2data(date("Y-m-d",strtotime($obj_buscar_contagens->data_hora_contagem)))." </option>";
        }


        desconectabd($conexao);

        break;
    }

    break;
    case "explodir_titulos" : 

    $cod_pizzarias = validaVarPost('filtro_pizzaria');
    $data_inicial_filtro = validaVarPost('data_inicial_filtro');
    $data_final_filtro = validaVarPost('data_final_filtro');
    $filtrar_por = validaVarPost('filtrar_por');
    $cod_titulos_categorias = validaVarPost('categoria');
    $pagina = validaVarPost("pagina");
    $conexao = conectabd();

    if($filtrar_por =="MES_REFERENCIA")
    {
        $filtro_data = "tp.mes_ref BETWEEN month('$data_inicial_filtro') AND month('$data_final_filtro') AND tp.ano_ref BETWEEN year('$data_inicial_filtro') AND year('$data_final_filtro') ";
    }
    else if($filtrar_por =="DATA_PAGAMENTO")
    {
        $filtro_data =  "tp.data_pagamento BETWEEN '$data_inicial_filtro' AND '$data_final_filtro' ";
    }
    else if($filtrar_por =="DATA_CRIADA")
    {
        $filtro_data =  "tp.data_hora_criacao BETWEEN '$data_inicial_filtro' AND '$data_final_filtro' ";
    }
    else if($filtrar_por=="DATA_EMISSAO")
    {
        $filtro_data =  "tp.data_emissao BETWEEN '$data_inicial_filtro' AND '$data_final_filtro' ";
    }else
    {
        $filtro_data = "tp.data_pagamento >= '".$data_inicial_filtro."' AND tp.data_pagamento <= '".$data_final_filtro."'";
    } 

    $filtro_pizzaria = "";

    if ($cod_pizzarias)
        $filtro_pizzaria .= " AND t.cod_pizzarias = ".$cod_pizzarias;

    $sql_buscar_qtd_pags = "SELECT count(tp.cod_titulos_parcelas) as qtd_items FROM ipi_titulos_categorias c left join ipi_titulos_subcategorias s on c.cod_titulos_categorias = s.cod_titulos_categorias INNER JOIN ipi_titulos t on  t.cod_titulos_subcategorias = s.cod_titulos_subcategorias inner join ipi_titulos_parcelas tp on tp.cod_titulos = t.cod_titulos WHERE $filtro_data $filtro_pizzaria AND s.cod_titulos_categorias = '$cod_titulos_categorias'";
    $res_buscar_qtd_pags = mysql_query($sql_buscar_qtd_pags);
    $obj_buscar_qtd_pags = mysql_fetch_object($res_buscar_qtd_pags);
    //echo "<br/>".$sql_buscar_qtd_pags;

    if(!is_numeric($pagina))
        $pagina = 1;

    $qtd_por_pagina = 14;

    $num_pags = ceil($obj_buscar_qtd_pags->qtd_items / $qtd_por_pagina);
            ?>
        <div id='conteudo_modal'>
            <table id="rel_dre" width='800' cellpadding="0" cellspacing="0" align="center">
            <thead>
                <tr>
                    <td colspan='7' align="center">
                     <?     
                     for($i = 1;$i<=$num_pags ; $i++)
                      {
                        if($i==$pagina)
                        {
                          echo "<b>".$i."</b>";
                        }
                        else
                        {
                          echo "<a href='javascript:void(0)' onclick='detalhes_titulos_sem_abrir(\"".$cod_titulos_categorias."\",\"nome_categoria\",\"".$data_inicial_filtro."\",\"".$data_final_filtro."\",\"".$filtrar_por."\",\"".$cod_pizzarias."\",\"".$i."\")'>".$i."</a>";
                        } 
                           echo "&nbsp &nbsp";
                      }
                      ?>
                    </td>
                </tr>

                <tr class='td_cinza'>
                    <td style='width:10%'>
                       <? echo utf8_encode('Lançamento'); ?>
                    </td>
                    <td style='width:15%'>
                        Subcategoria
                    </td>
                    <td  style='width:30%; text-align: center'>
                      <? echo utf8_encode('Descrição'); ?>
                    </td>
                    <td  style='width:5%; text-align: center'>
                        Parcela
                    </td>
                    <td  style='width:20%; text-align: center'>
                       Cedente / Sacado
                    </td>
                    <td  style='width:10%; text-align: center'>
                      <? echo utf8_encode('Débito'); ?>
                    </td>    
                    <td  style='width:10%; text-align: center'>
                      <? echo utf8_encode('Crédito'); ?>
                    </td> 
                </tr>
            </thead>      
          
            <?

            $pagina = $pagina -1;

            $sql_buscar_plano_contas = "SELECT t.cod_clientes,t.cod_entregadores,t.cod_colaboradores,t.cod_fornecedores,t.tipo_cedente_sacado,c.cod_titulos_categorias,s.cod_titulos_subcategorias,c.meta_de_porcentagem,c.titulos_categoria,tp.valor_total,tp.numero_parcela,t.total_parcelas, tp.data_hora_criacao,s.titulos_subcategorias,t.descricao FROM ipi_titulos_categorias c left join ipi_titulos_subcategorias s on c.cod_titulos_categorias = s.cod_titulos_categorias INNER JOIN ipi_titulos t on  t.cod_titulos_subcategorias = s.cod_titulos_subcategorias inner join ipi_titulos_parcelas tp on tp.cod_titulos = t.cod_titulos WHERE $filtro_data $filtro_pizzaria AND s.cod_titulos_categorias = '$cod_titulos_categorias' ORDER BY year(tp.data_hora_criacao),month(tp.data_hora_criacao),day(tp.data_hora_criacao),s.cod_titulos_subcategorias ASC  LIMIT ".($pagina*$qtd_por_pagina).",".$qtd_por_pagina;


            $res_buscar_plano_contas = mysql_query($sql_buscar_plano_contas);
            $num_buscar_plano_contas = mysql_num_rows($res_buscar_plano_contas);
            //echo "<br />".$sql_buscar_plano_contas;
            echo "<tbody>";
            $total = 0;
            while ($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_plano_contas))
            {
                echo "<tr>";
                $debito = 0;
                $credito = 0;
                if($obj_buscar_plano_contas->valor_total>0)
                {
                    $credito = $obj_buscar_plano_contas->valor_total;
                }
                else
                {
                    $debito = $obj_buscar_plano_contas->valor_total;
                }
                echo utf8_encode("<td>".bd2datahora($obj_buscar_plano_contas->data_hora_criacao)."</td>");
                echo utf8_encode("<td>".$obj_buscar_plano_contas->titulos_subcategorias."</td>");
                echo utf8_encode("<td>".$obj_buscar_plano_contas->descricao."</td>");
                echo "<td align='center'>".$obj_buscar_plano_contas->numero_parcela."/".$obj_buscar_plano_contas->total_parcelas."</td><td>";
                if($obj_buscar_plano_contas->tipo_cedente_sacado == 'FORNECEDOR')
                {
                    $obj_buscar_fornecedor = executaBuscaSimples("SELECT nome_fantasia FROM ipi_fornecedores WHERE cod_fornecedores = '" . $obj_buscar_plano_contas->cod_fornecedores . "'", $conexao);
                    
                    echo utf8_encode(bd2texto($obj_buscar_fornecedor->nome_fantasia));
                }
                else if($obj_buscar_plano_contas->tipo_cedente_sacado == 'COLABORADOR')
                {
                    $obj_buscar_colaborador = executaBuscaSimples("SELECT nome FROM ipi_colaboradores WHERE cod_colaboradores = '" . $obj_buscar_plano_contas->cod_colaboradores . "'", $conexao);

                    echo utf8_encode(bd2texto($obj_buscar_colaborador->nome));
                }
                else if($obj_buscar_plano_contas->tipo_cedente_sacado == 'ENTREGADOR')
                {
                    $obj_buscar_entregador = executaBuscaSimples("SELECT nome FROM ipi_entregadores WHERE cod_entregadores = '" . $obj_buscar_plano_contas->cod_entregadores . "'", $conexao);

                    echo utf8_encode(bd2texto($obj_buscar_entregador->nome));
                }
                else if(($obj_buscar_plano_contas->tipo_cedente_sacado == 'PROJETO') || ($obj_buscar_plano_contas->tipo_cedente_sacado == 'CLIENTE') || ($obj_buscar_plano_contas->tipo_cedente_sacado == 'PRODUTO'))
                {
                    $obj_buscar_cliente = executaBuscaSimples("SELECT nome FROM ipi_clientes WHERE cod_clientes = '" . $obj_buscar_plano_contas->cod_clientes . "'", $conexao);

                    echo utf8_encode(bd2texto($obj_buscar_cliente->nome));
                }
                echo " / ".$obj_buscar_plano_contas->tipo_cedente_sacado."</td>";
                echo "<td style='color:red;font-weight:bold' align='right'>".bd2moeda($debito)."</td>";   
                echo "<td style='color:green;font-weight:bold' align='right'>".bd2moeda($credito)."</td>"; 
                echo "</tr>";
                $total += $obj_buscar_plano_contas->valor_total;
            }
            desconectabd($conexao);
            
            ?>
                <tr>
                    <td colspan='5' align="right">Total da pagina
                    </td>
                    <td colspan='2' align="center"><? echo bd2moeda($total); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <? 
    break;
    case "explodir_fornecedores_pagar" :
    $cod_pizzarias = validaVarPost("filtro_pizzaria");
    $pagina = validaVarPost("pagina");
    $conexao = conectabd();

    $filtro_pizzaria = '';
    if ($cod_pizzarias)
        $filtro_pizzaria .= " AND t.cod_pizzarias = '".$cod_pizzarias."'";

    $sql_buscar_qtd_pags = "SELECT count(tp.cod_titulos_parcelas) as qtd_items FROM ipi_titulos_categorias c left join ipi_titulos_subcategorias s on c.cod_titulos_categorias = s.cod_titulos_categorias INNER JOIN ipi_titulos t on  t.cod_titulos_subcategorias = s.cod_titulos_subcategorias inner join ipi_titulos_parcelas tp on tp.cod_titulos = t.cod_titulos WHERE tp.situacao='ABERTO' and tp.valor_total <0 $filtro_pizzaria  AND tp.data_vencimento > NOW()";

    $res_buscar_qtd_pags = mysql_query($sql_buscar_qtd_pags);
    $obj_buscar_qtd_pags = mysql_fetch_object($res_buscar_qtd_pags);
    //echo "<br/>".$sql_buscar_qtd_pags;

    if(!is_numeric($pagina))
        $pagina = 1;

    $qtd_por_pagina = 14;

    $num_pags = ceil($obj_buscar_qtd_pags->qtd_items / $qtd_por_pagina);
            ?>
        <div id='conteudo_modal'>
            <table id="rel_dre" width='800' cellpadding="0" cellspacing="0" align="center">
            <thead>
                <tr>
                    <td colspan='7' align="center">
                     <?     
                     for($i = 1;$i<=$num_pags ; $i++)
                      {
                        if($i==$pagina)
                        {
                          echo "<b>".$i."</b>";
                        }
                        else
                        {
                          echo "<a href='javascript:void(0)' onclick='detalhes_contas_sem_abrir(\"explodir_fornecedores_pagar\",\"".$cod_pizzarias."\",\"".$i."\")'>".$i."</a>";
                        } 
                           echo "&nbsp &nbsp";
                      }
                      ?>
                    </td>
                </tr>

                <tr class='td_cinza'>
                    <td style='width:10%'>
                       <? echo utf8_encode('Lançamento'); ?>
                    </td>
                    <td style='width:15%'>
                        Subcategoria
                    </td>
                    <td  style='width:30%; text-align: center'>
                      <? echo utf8_encode('Descrição'); ?>
                    </td>
                    <td  style='width:5%; text-align: center'>
                        Parcela
                    </td>
                    <td  style='width:20%; text-align: center'>
                       Cedente / Sacado
                    </td>
                    <td  style='width:10%; text-align: center'>
                      <? echo utf8_encode('Débito'); ?>
                    </td>    
                    <td  style='width:10%; text-align: center'>
                      <? echo utf8_encode('Crédito'); ?>
                    </td> 
                </tr>
            </thead>      
          
            <?

            $pagina = $pagina -1;

        $sql_buscar_forn_pagar = "SELECT t.cod_clientes,t.cod_entregadores,t.cod_colaboradores,t.cod_fornecedores,t.tipo_cedente_sacado,c.cod_titulos_categorias,s.cod_titulos_subcategorias,c.meta_de_porcentagem,c.titulos_categoria,tp.valor_total,tp.numero_parcela,t.total_parcelas, tp.data_hora_criacao,s.titulos_subcategorias,t.descricao FROM ipi_titulos_categorias c left join ipi_titulos_subcategorias s on c.cod_titulos_categorias = s.cod_titulos_categorias INNER JOIN ipi_titulos t on  t.cod_titulos_subcategorias = s.cod_titulos_subcategorias inner join ipi_titulos_parcelas tp on tp.cod_titulos = t.cod_titulos WHERE tp.situacao='ABERTO' and tp.valor_total <0  AND tp.data_vencimento > NOW()";

        if($cod_pizzarias)
            $sql_buscar_forn_pagar .= " and t.cod_pizzarias = '".$cod_pizzarias."'";

        $sql_buscar_forn_pagar .= " ORDER BY year(tp.data_hora_criacao),month(tp.data_hora_criacao),day(tp.data_hora_criacao),s.cod_titulos_subcategorias ASC  LIMIT ".($pagina*$qtd_por_pagina).",".$qtd_por_pagina;
        //echo "<br/>".$sql_buscar_forn_pagar;
        $res_buscar_forn_pagar = mysql_query($sql_buscar_forn_pagar);
        echo "<tbody>";
            $total = 0;
            while ($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_forn_pagar))
            {
                echo "<tr>";
                $debito = 0;
                $credito = 0;
                if($obj_buscar_plano_contas->valor_total>0)
                {
                    $credito = $obj_buscar_plano_contas->valor_total;
                }
                else
                {
                    $debito = $obj_buscar_plano_contas->valor_total;
                }
                echo utf8_encode("<td>".bd2datahora($obj_buscar_plano_contas->data_hora_criacao)."</td>");
                echo utf8_encode("<td>".$obj_buscar_plano_contas->titulos_subcategorias."</td>");
                echo utf8_encode("<td>".$obj_buscar_plano_contas->descricao."</td>");
                echo "<td align='center'>".$obj_buscar_plano_contas->numero_parcela."/".$obj_buscar_plano_contas->total_parcelas."</td><td>";
                if($obj_buscar_plano_contas->tipo_cedente_sacado == 'FORNECEDOR')
                {
                    $obj_buscar_fornecedor = executaBuscaSimples("SELECT nome_fantasia FROM ipi_fornecedores WHERE cod_fornecedores = '" . $obj_buscar_plano_contas->cod_fornecedores . "'", $conexao);
                    
                    echo utf8_encode(bd2texto($obj_buscar_fornecedor->nome_fantasia));
                }
                else if($obj_buscar_plano_contas->tipo_cedente_sacado == 'COLABORADOR')
                {
                    $obj_buscar_colaborador = executaBuscaSimples("SELECT nome FROM ipi_colaboradores WHERE cod_colaboradores = '" . $obj_buscar_plano_contas->cod_colaboradores . "'", $conexao);

                    echo utf8_encode(bd2texto($obj_buscar_colaborador->nome));
                }
                else if($obj_buscar_plano_contas->tipo_cedente_sacado == 'ENTREGADOR')
                {
                    $obj_buscar_entregador = executaBuscaSimples("SELECT nome FROM ipi_entregadores WHERE cod_entregadores = '" . $obj_buscar_plano_contas->cod_entregadores . "'", $conexao);

                    echo utf8_encode(bd2texto($obj_buscar_entregador->nome));
                }
                else if(($obj_buscar_plano_contas->tipo_cedente_sacado == 'PROJETO') || ($obj_buscar_plano_contas->tipo_cedente_sacado == 'CLIENTE') || ($obj_buscar_plano_contas->tipo_cedente_sacado == 'PRODUTO'))
                {
                    $obj_buscar_cliente = executaBuscaSimples("SELECT nome FROM ipi_clientes WHERE cod_clientes = '" . $obj_buscar_plano_contas->cod_clientes . "'", $conexao);

                    echo utf8_encode(bd2texto($obj_buscar_cliente->nome));
                }
                echo " / ".$obj_buscar_plano_contas->tipo_cedente_sacado."</td>";
                echo "<td style='color:red;font-weight:bold' align='right'>".bd2moeda($debito)."</td>";   
                echo "<td style='color:green;font-weight:bold' align='right'>".bd2moeda($credito)."</td>"; 
                echo "</tr>";
                $total += $obj_buscar_plano_contas->valor_total;
            }
            desconectabd($conexao);
            
            ?>
                <tr>
                    <td colspan='5' align="right">Total da pagina
                    </td>
                    <td colspan='2' align="center"><? echo bd2moeda($total); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
        <?
    break;
    case "explodir_cart_receber" :
    $cod_pizzarias = validaVarPost("filtro_pizzaria");
    $pagina = validaVarPost("pagina");
    $conexao = conectabd();

    $filtro_pizzaria = '';
    if ($cod_pizzarias)
        $filtro_pizzaria .= " AND t.cod_pizzarias = '".$cod_pizzarias."'";

    $sql_buscar_qtd_pags = "SELECT count(tp.cod_titulos_parcelas) as qtd_items FROM ipi_titulos_categorias c left join ipi_titulos_subcategorias s on c.cod_titulos_categorias = s.cod_titulos_categorias INNER JOIN ipi_titulos t on  t.cod_titulos_subcategorias = s.cod_titulos_subcategorias inner join ipi_titulos_parcelas tp on tp.cod_titulos = t.cod_titulos WHERE tp.situacao='ABERTO' and tp.valor_total >0 $filtro_pizzaria AND tp.data_vencimento > NOW() ";

    $res_buscar_qtd_pags = mysql_query($sql_buscar_qtd_pags);
    $obj_buscar_qtd_pags = mysql_fetch_object($res_buscar_qtd_pags);
    //echo "<br/>".$sql_buscar_qtd_pags;

    if(!is_numeric($pagina))
        $pagina = 1;

    $qtd_por_pagina = 14;

    $num_pags = ceil($obj_buscar_qtd_pags->qtd_items / $qtd_por_pagina);
            ?>
        <div id='conteudo_modal'>
            <table id="rel_dre" width='800' cellpadding="0" cellspacing="0" align="center">
            <thead>
                <tr>
                    <td colspan='7' align="center">
                     <?     
                     for($i = 1;$i<=$num_pags ; $i++)
                      {
                        if($i==$pagina)
                        {
                          echo "<b>".$i."</b>";
                        }
                        else
                        {
                          echo "<a href='javascript:void(0)' onclick='detalhes_contas_sem_abrir(\"explodir_cart_receber\",\"".$cod_pizzarias."\",\"".$i."\")'>".$i."</a>";
                        } 
                           echo "&nbsp &nbsp";
                      }
                      ?>
                    </td>
                </tr>

                <tr class='td_cinza'>
                    <td style='width:10%'>
                       <? echo utf8_encode('Lançamento'); ?>
                    </td>
                    <td style='width:15%'>
                        Subcategoria
                    </td>
                    <td  style='width:30%; text-align: center'>
                      <? echo utf8_encode('Descrição'); ?>
                    </td>
                    <td  style='width:5%; text-align: center'>
                        Parcela
                    </td>
                    <td  style='width:20%; text-align: center'>
                       Cedente / Sacado
                    </td>
                    <td  style='width:10%; text-align: center'>
                      <? echo utf8_encode('Débito'); ?>
                    </td>    
                    <td  style='width:10%; text-align: center'>
                      <? echo utf8_encode('Crédito'); ?>
                    </td> 
                </tr>
            </thead>      
          
            <?

            $pagina = $pagina -1;

        $sql_buscar_forn_pagar = "SELECT t.cod_clientes,t.cod_entregadores,t.cod_colaboradores,t.cod_fornecedores,t.tipo_cedente_sacado,c.cod_titulos_categorias,s.cod_titulos_subcategorias,c.meta_de_porcentagem,c.titulos_categoria,tp.valor_total,tp.numero_parcela,t.total_parcelas, tp.data_hora_criacao,s.titulos_subcategorias,t.descricao FROM ipi_titulos_categorias c left join ipi_titulos_subcategorias s on c.cod_titulos_categorias = s.cod_titulos_categorias INNER JOIN ipi_titulos t on  t.cod_titulos_subcategorias = s.cod_titulos_subcategorias inner join ipi_titulos_parcelas tp on tp.cod_titulos = t.cod_titulos WHERE tp.situacao='ABERTO' and tp.valor_total >0 and tp.data_vencimento > NOW()";

        if($cod_pizzarias)
            $sql_buscar_forn_pagar .= " and t.cod_pizzarias = '".$cod_pizzarias."'";

        $sql_buscar_forn_pagar .= " ORDER BY year(tp.data_hora_criacao),month(tp.data_hora_criacao),day(tp.data_hora_criacao),s.cod_titulos_subcategorias ASC  LIMIT ".($pagina*$qtd_por_pagina).",".$qtd_por_pagina;
        //echo "<br/>".$sql_buscar_forn_pagar;
        $res_buscar_forn_pagar = mysql_query($sql_buscar_forn_pagar);
        echo "<tbody>";
            $total = 0;
            while ($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_forn_pagar))
            {
                echo "<tr>";
                $debito = 0;
                $credito = 0;
                if($obj_buscar_plano_contas->valor_total>0)
                {
                    $credito = $obj_buscar_plano_contas->valor_total;
                }
                else
                {
                    $debito = $obj_buscar_plano_contas->valor_total;
                }
                echo utf8_encode("<td>".bd2datahora($obj_buscar_plano_contas->data_hora_criacao)."</td>");
                echo utf8_encode("<td>".$obj_buscar_plano_contas->titulos_subcategorias."</td>");
                echo utf8_encode("<td>".$obj_buscar_plano_contas->descricao."</td>");
                echo "<td align='center'>".$obj_buscar_plano_contas->numero_parcela."/".$obj_buscar_plano_contas->total_parcelas."</td><td>";
                if($obj_buscar_plano_contas->tipo_cedente_sacado == 'FORNECEDOR')
                {
                    $obj_buscar_fornecedor = executaBuscaSimples("SELECT nome_fantasia FROM ipi_fornecedores WHERE cod_fornecedores = '" . $obj_buscar_plano_contas->cod_fornecedores . "'", $conexao);
                    
                    echo utf8_encode(bd2texto($obj_buscar_fornecedor->nome_fantasia));
                }
                else if($obj_buscar_plano_contas->tipo_cedente_sacado == 'COLABORADOR')
                {
                    $obj_buscar_colaborador = executaBuscaSimples("SELECT nome FROM ipi_colaboradores WHERE cod_colaboradores = '" . $obj_buscar_plano_contas->cod_colaboradores . "'", $conexao);

                    echo utf8_encode(bd2texto($obj_buscar_colaborador->nome));
                }
                else if($obj_buscar_plano_contas->tipo_cedente_sacado == 'ENTREGADOR')
                {
                    $obj_buscar_entregador = executaBuscaSimples("SELECT nome FROM ipi_entregadores WHERE cod_entregadores = '" . $obj_buscar_plano_contas->cod_entregadores . "'", $conexao);

                    echo utf8_encode(bd2texto($obj_buscar_entregador->nome));
                }
                else if(($obj_buscar_plano_contas->tipo_cedente_sacado == 'PROJETO') || ($obj_buscar_plano_contas->tipo_cedente_sacado == 'CLIENTE') || ($obj_buscar_plano_contas->tipo_cedente_sacado == 'PRODUTO'))
                {
                    $obj_buscar_cliente = executaBuscaSimples("SELECT nome FROM ipi_clientes WHERE cod_clientes = '" . $obj_buscar_plano_contas->cod_clientes . "'", $conexao);

                    echo utf8_encode(bd2texto($obj_buscar_cliente->nome));
                }
                echo " / ".$obj_buscar_plano_contas->tipo_cedente_sacado."</td>";
                echo "<td style='color:red;font-weight:bold' align='right'>".bd2moeda($debito)."</td>";   
                echo "<td style='color:green;font-weight:bold' align='right'>".bd2moeda($credito)."</td>"; 
                echo "</tr>";
                $total += $obj_buscar_plano_contas->valor_total;
            }
            desconectabd($conexao);
            
            ?>
                <tr>
                    <td colspan='5' align="right">Total da pagina
                    </td>
                    <td colspan='2' align="center"><? echo bd2moeda($total); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
        <?
    break;
    case "explodir_cmv" :
    $cod_pizzarias = validaVarPost("filtro_pizzaria");
    $pagina = validaVarPost("pagina");
    $dados = validaVarPost("dados");
    $dados = explode("_|_",$dados);

    $conexao = conectabd();

    $data_inicial_filtro = $dados[0];
    $data_final_filtro =  $dados[1];
    $filtrar_por =  $dados[2];
    //$cod_pizzarias = validaVarPost('cod_pizzarias');
    $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
    
     require_once 'ipi_rel_dre_dados_cmv.php';


     $usando_teorico = false;

    if((count($array_dados["ESTOQUE_INICIAL"])>0) && (count($array_dados["ESTOQUE_FINAL"])>0) && (count($array_dados["ENTRADAS"])>0))
    {
        $arr_tabela_dados = array_merge($array_dados["ESTOQUE_INICIAL"],$array_dados["ESTOQUE_FINAL"],$array_dados["ENTRADAS"]);
    }
    else
    {
        $arr_tabela_dados = $array_dados["TEORICO"];
        $usando_teorico = true;
    }

    

    $num_detalhes = count($arr_tabela_dados);
    if(!is_numeric($pagina))
    {
      $pagina = 1;
    }

    $qtd_por_pagina = 20;
    $array_pagina = array_slice($arr_tabela_dados, (($pagina-1)*$qtd_por_pagina),$qtd_por_pagina);

    $num_pags = ceil($num_detalhes / $qtd_por_pagina);

    $total = 0;
    
    $arr_nome_ingredientes = array();
    $arr_nome_bebidas = array();

    $sql_buscar_ingredientes = "select cod_ingredientes,ingrediente from ipi_ingredientes where cod_ingredientes in (".implode(",",array_column($arr_tabela_dados,'cod_ingredientes')).")";
    $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
    while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
    {
        $arr_nome_ingredientes[$obj_buscar_ingredientes->cod_ingredientes] = $obj_buscar_ingredientes->ingrediente;
    }

    $sql_buscar_bebidas = "select c.conteudo,b.bebida,bc.cod_bebidas_ipi_conteudos from ipi_bebidas_ipi_conteudos bc inner join ipi_bebidas b on b.cod_bebidas = bc.cod_bebidas inner join ipi_conteudos c on c.cod_conteudos = bc.cod_conteudos where bc.cod_bebidas_ipi_conteudos in (".implode(",",array_column($arr_tabela_dados,'cod_bebidas_ipi_conteudos')).")";
    $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
    while($obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas))
    {
        $arr_nome_bebidas[$obj_buscar_bebidas->cod_bebidas_ipi_conteudos] = $obj_buscar_bebidas->bebida." - ".$obj_buscar_bebidas->conteudo;
    }

            ?>
        <div id='conteudo_modal'>
            <table id="rel_dre" width='800' cellpadding="0" cellspacing="0" align="center">
            <thead>
                <tr>
                    <td colspan='7' align="center">
                     <?     
                     for($i = 1;$i<=$num_pags ; $i++)
                      {
                        if($i==$pagina)
                        {
                          echo "<b>".$i."</b>";
                        }
                        else
                        {
                          echo "<a href='javascript:void(0)' onclick='detalhes_contas_sem_abrir(\"explodir_cmv\",\"".$cod_pizzarias."\",\"".$i."\")'>".$i."</a>";
                        } 
                           echo "&nbsp &nbsp";
                      }
                      ?>
                    </td>
                </tr>

                <tr class='td_cinza'>
                    <td style='width:10%'>
                       <? echo utf8_encode('TIPO'); ?>
                    </td>
                    <td style='width:30%'>
                        Ingrediente / Bebida
                    </td>
                    <td  style='width:25%; text-align: center'>
                        <? echo utf8_encode('Data Ajuste / Movimentação'); ?>
                    </td>
                    <td  style='width:5%; text-align: center'>
                        Qtde Entrada
                    </td>
                    <td  style='width:20%; text-align: center'>
                      <? if($usando_teorico) { echo utf8_encode("Quantidade Movimentada"); } else echo "Qtde Ajustada / <br/>Qtde na Embalagem"; ?>
                    </td>
                    <td  style='width:10%; text-align: center'>
                      <? echo utf8_encode('Preço do Grama'); ?>
                    </td>    
                    <td  style='width:10%; text-align: center'>
                      Total (R$)
                    </td> 
                </tr>
            </thead>      
            <tbody>
                <? 
                foreach ($array_pagina as $linha) 
                {
                  echo "<tr>";
                  echo "<td>".$linha['tipo']."</td>";
                  if($linha['cod_ingredientes']=="0")
                  {
                      echo utf8_encode("<td>".$arr_nome_bebidas[$linha['cod_bebidas_ipi_conteudos']]."</td>");
                  }
                  else
                      echo utf8_encode("<td>".$arr_nome_ingredientes[$linha['cod_ingredientes']]."</td>");
                  
                  if($linha['tipo']=="ENTRADAS")
                  {
                     echo "<td>".bd2datahora($linha['data_lancamento'])."</td>";
                     echo "<td>".$linha['quantidade_entrada']."</td>";
                     echo "<td>".$linha['quantidade_embalagem']."</td>";
                  }
                  elseif($linha['tipo']=="TEORICO")
                  {
                    echo "<td>".bd2data($linha['data_movimentacao'])."</td>";
                    echo "<td></td>";
                    echo "<td>".$linha['quantidade_movimentada']."</td>";
                  }
                  else
                  {
                    echo "<td>".bd2data(date("Y-m-d",strtotime($linha['data_contagem'])))."</td>";
                    echo "<td></td>";
                    echo "<td>".$linha['quantidade_ajustada']."</td>";

                  }
                  

                  echo "<td>".$linha['preco_grama']."</td>";
                  echo "<td>".bd2moeda($linha['total'])."</td>";
                  echo "</tr>";
                  $total += $linha['total'];
                }

                ?>
                <tr>
                    <td colspan='3' align='left'><? echo utf8_encode('Cálculo') ?> do CMV = ESTOQUE_INICIAL  + ENTRADAS - ESTOQUE_FINAL</td>
                    <td colspan='2' align="right">Total da pagina
                    </td>
                    <td colspan='2' align="center"><? echo bd2moeda($total); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
        <?
    break;

}
