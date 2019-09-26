<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório DRE');
?>


<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<link  href="../lib/js/moodialog/css/MooDialog.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="../lib/js/calendario.js"></script>
<script src="../lib/js/moodialog/MooDialog.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/Overlay.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Fx.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Alert.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Request.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Confirm.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Prompt.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Error.js" type="text/javascript"></script>
<script>
window.addEvent('domready', function() { 
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 

  function detalhes_titulos(cod_categorias,nome_categoria,data_inicial_filtro,data_final_filtro,filtrar_por,filtro_pizaria,pagina)
  {
    var opcoes = "method:'post'";//method:'post'  
    var variaveis = 'acao=explodir_titulos&categoria='+cod_categorias+'&nome_categoria='+nome_categoria+'&data_inicial_filtro='+data_inicial_filtro+'&data_final_filtro='+data_final_filtro+'&filtrar_por='+filtrar_por+'&filtro_pizzaria='+filtro_pizaria+'&pagina='+pagina;
    var reqDialog = new MooDialog.Request('ipi_rel_dre_novo_ajax.php',variaveis,opcoes, {
      'class': 'MooDialog',
      autoOpen: false,
      title: nome_categoria
    });

    // You want the request dialog instance to set the onRequest message, so you have to do it in two steps.
    reqDialog.setRequestOptions({
      onRequest: function(){
        alert('teste');
        reqDialog.setContent('loading...');
      }
    }).open();
  }

  function detalhes_titulos_sem_abrir(cod_categorias,nome_categoria,data_inicial_filtro,data_final_filtro,filtrar_por,filtro_pizaria,pagina)
  {
    var opcoes = "method:'post'";//method:'post'  
    var variaveis = 'acao=explodir_titulos&categoria='+cod_categorias+'&nome_categoria='+nome_categoria+'&data_inicial_filtro='+data_inicial_filtro+'&data_final_filtro='+data_final_filtro+'&filtrar_por='+filtrar_por+'&filtro_pizzaria='+filtro_pizaria+'&pagina='+pagina;

    new Request.HTML(
    {
      url: 'ipi_rel_dre_novo_ajax.php',
      update: 'conteudo_modal',
      method:'post'
    }).send(variaveis);

  }

function detalhes_contas(acao,nome_exibir,cod_pizzarias,pagina)
  {
    <?
    $data_inicial_filtro = data2bd(validaVarPost('data_inicial'))." 00:00:00";
    $data_final_filtro = data2bd(validaVarPost('data_final'))." 23:59:59";
    $filtrar_por = validaVarPost('filtrar_filtro');
    $filtro_dados = $data_inicial_filtro."_|_".$data_final_filtro."_|_".$filtrar_por;
    ?>
    var filtro_dados = <? echo "'".$filtro_dados."'" ?>;
    var opcoes = "method:'post'";//method:'post'  
    var variaveis = 'acao='+acao+'&filtro_pizzaria='+cod_pizzarias+"&pagina="+pagina+"&dados="+filtro_dados;
    var reqDialog = new MooDialog.Request('ipi_rel_dre_novo_ajax.php',variaveis,opcoes, {
      'class': 'MooDialog',
      autoOpen: false,
      title: nome_exibir
    });

    // You want the request dialog instance to set the onRequest message, so you have to do it in two steps.
    reqDialog.setRequestOptions({
      onRequest: function(){
        alert('teste');
        reqDialog.setContent('loading...');
      }
    }).open();
  }

  function detalhes_contas_sem_abrir(acao,cod_pizzarias,pagina)
  {
    <?
    $data_inicial_filtro = data2bd(validaVarPost('data_inicial'))." 00:00:00";
    $data_final_filtro = data2bd(validaVarPost('data_final'))." 23:59:59";
    $filtrar_por = validaVarPost('filtrar_filtro');
    $filtro_dados = $data_inicial_filtro."_|_".$data_final_filtro."_|_".$filtrar_por;
    ?>

    var filtro_dados = <? echo "'".$filtro_dados."'" ?>;
    var opcoes = "method:'post'";//method:'post'  
    var variaveis = 'acao='+acao+'&filtro_pizzaria='+cod_pizzarias+"&pagina="+pagina+"&dados="+filtro_dados;

    new Request.HTML(
    {
      url: 'ipi_rel_dre_novo_ajax.php',
      update: 'conteudo_modal',
      method:'post'
    }).send(variaveis);

  }
</script>

<style>
.td_verde td
{
  background-color: #00b050 ;
  color:white;
  border: 1px solid #CCCCCC;
  border-bottom: 1px solid #CCCCCC;
}
.td_azul td 
{
  border: 1px solid #CCCCCC;
  border-bottom: 1px solid #CCCCCC;
  color:white;
  background-color: #0070c0;
}
.td_cinza td
{
    background-color: #D8D8D8;
    color:black;
    border: 1px solid #CCCCCC;
    border-bottom: 1px solid #CCCCCC;
}
.td_cinza
{
    background-color: #D8D8D8;
    color:black;
    border: 1px solid #CCCCCC;
    border-bottom: 1px solid #CCCCCC;
}
.td_cinza_claro
{
    background-color: #E5DFEC;
    color:black;
    border: 1px solid #CCCCCC;
    border-bottom: 1px solid #CCCCCC;
}
.td_cinza_neg td
{
    background-color: #D8D8D8;
    color:black;
    font-weight: bold;
    border: 1px solid #CCCCCC;
    border-bottom: 1px solid #CCCCCC;
}
.letra_vermelha 
{
    color: red;
    font-weight: bold;
}
.letra_verde 
{
    color: #00b050;
    font-weight: bold;
}
#rel_dre
{
  margin: 0px auto;
  border-right: 1px solid #CCCCCC;
}

#rel_dre td
{
  padding: 2px;
  border-top: 1px solid #CCCCCC;
  border-left: 1px solid #CCCCCC;
}
</style>

<?


$filtrar_por = validaVarPost('filtrar_filtro');
$data_inicial_filtro = validaVarPost('data_inicial');
$data_final_filtro = validaVarPost('data_final');

$data_inicial_filtro = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
$data_final_filtro = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');

$cod_pizzarias = validaVarPost('cod_pizzarias');
$situacao = validaVarPost("situacao_filtro");

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

function linha_categoria($sinal,$cod_categoria,&$arr_tcat,$c='')
{   
    $data_inicial_filtro = data2bd(validaVarPost('data_inicial'));
    $data_final_filtro = data2bd(validaVarPost('data_final'));
    $filtrar_por = validaVarPost('filtrar_filtro');
    $cod_pizzarias = validaVarPost('cod_pizzarias');

    $classe = '';
    $porc = porcentagem($arr_tcat[$cod_categoria]['total'],$arr_tcat['13']['total']);
    if($porc=="")
    {
      $classe = '';
    }
    else
    {
      if($porc>(float)bd2moeda($arr_tcat[$cod_categoria]['meta_porcentagem']))
      {
          $classe= 'letra_vermelha';
      }
      else
      {
        $classe = 'letra_verde';
      }
    }
  
    echo "<tr >";

    echo "<td align='left'><a href='javascript:void(0);' onclick='detalhes_titulos(\"".$cod_categoria."\",\"".$arr_tcat[$cod_categoria]['nome_categoria']."\",\"".$data_inicial_filtro."\",\"".$data_final_filtro."\",\"".$filtrar_por."\",\"".$cod_pizzarias."\",\"1\")'> $sinal ".$arr_tcat[$cod_categoria]['nome_categoria']."</a></td>";
    echo "<td align='right' class='".$classe."' class='$c'>".conv_cat_dinheiro($arr_tcat[$cod_categoria]['total'])."</td>";
    echo "<td align='right' class='$c'>".porcentagem($arr_tcat[$cod_categoria]['total'],$arr_tcat['13']['total'] )."</td>";
    echo "<td align='right'></td>";
    echo "<td align='right'>".bd2moeda((($arr_tcat[$cod_categoria]['meta_porcentagem']*$arr_tcat['13']['total'])/100))."</td>";
    echo "<td align='right' >".bd2moeda($arr_tcat[$cod_categoria]['meta_porcentagem'])."".($arr_tcat[$cod_categoria]['meta_porcentagem'] !="" ? "  %" : "")."</td></tr>";
}

function linha_cmv($sinal,&$arr_tcat,$c='')
{   
  $cod_categoria = '3';

  $data_inicial_filtro = data2bd(validaVarPost('data_inicial'))." 00:00:00";
  $data_final_filtro = data2bd(validaVarPost('data_final'))." 23:59:59";
  $filtrar_por = validaVarPost('filtrar_filtro');
  $cod_pizzarias = validaVarPost('cod_pizzarias');
  $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

  require_once 'ipi_rel_dre_dados_cmv.php';
  $classe = '';

  $porc = porcentagem($teorico,$arr_tcat['13']['total']);
  if($porc=="")
  {
    $classe = '';
  }
  else
  {
    if($porc>(float)bd2moeda($arr_tcat[$cod_categoria]['meta_porcentagem']))
    {
      //$classe= 'letra_vermelha';
    }
    else
    {
      $classe = 'letra_verde';
    }
  }
  

  echo "<tr >";

  //echo "<td align='left'><a href='javascript:void(0);' onclick='detalhes_titulos(\"".$cod_categoria."\",\"".$arr_tcat[$cod_categoria]['nome_categoria']."\",\"".$data_inicial_filtro."\",\"".$data_final_filtro."\",\"".$filtrar_por."\",\"".$cod_pizzarias."\",\"1\")'> $sinal ".$arr_tcat[$cod_categoria]['nome_categoria']."</a></td>";
  

  //echo "<td align='left'><a href='javascript:void(0);' onclick='detalhes_contas(\"explodir_cmv\",\"".$arr_tcat[$cod_categoria]['nome_categoria']."\",\"".$cod_pizzarias."\",1)'> $sinal ".$arr_tcat[$cod_categoria]['nome_categoria']."</a></td>";

  echo "<td align='left'> $sinal ".$arr_tcat[$cod_categoria]['nome_categoria']."</td>";

  echo '
    <form name="frmFiltroTeorico" method="post" action="ipi_rel_cmv_teorico.php" target="_blank">
      <input type="hidden"  value="'.$cod_pizzarias.'" name="cod_pizzarias" id="cod_pizzarias">
      <input type="hidden" value="'.validaVarPost('data_inicial').'" name="data_inicial" id="data_inicial">

      <input type="hidden" value="'.validaVarPost('data_final').'" name="data_final" id="data_final">
    <input type="hidden" name="acao" value="buscar"></form>';

  if((count($array_dados["ESTOQUE_INICIAL"])>0) && (count($array_dados["ESTOQUE_FINAL"])>0) && (count($array_dados["ENTRADAS"])>0))
  {
    $cmv_real = $soma_cmv;
    $arr_tcat[$cod_categoria]['total'] = $cmv_real;
    echo '
    <form name="frmFiltroReal" method="post" action="ipi_rel_cmv_inventario.php" target="_blank">
      <input type="hidden"  value="'.$cod_pizzarias.'" name="cod_pizzarias" id="cod_pizzarias">
      <input type="hidden" value="'.$cod_primeira_contagem.'" name="contagem_1" id="contagem_1">

      <input type="hidden" value="'.$cod_ultima_contagem.'" name="contagem_2" id="contagem_2">
    <input type="hidden" name="acao" value="buscar"></form>';

     echo "<td align='right' class='".$classe."' class='$c'><a href='javascript:void(0)' onclick='javascript:document.frmFiltroReal.submit()'>".conv_cat_dinheiro($cmv_real)."</a></td>";
     echo "<td align='right' class='$c'>".porcentagem($cmv_real,$arr_tcat['13']['total'] )."</td>";


  }
  else
  {
    $cmv_real = $teorico;
    $arr_tcat[$cod_categoria]['total'] = $cmv_real;

     echo "<td align='right' class='".$classe."' class='$c'><a href='javascript:void(0)' onclick='javascript:document.frmFiltroTeorico.submit()'>".conv_cat_dinheiro($cmv_real)."</a></td>";
     echo "<td align='right' class='$c'>".porcentagem($cmv_real,$arr_tcat['13']['total'] )."</td>";

  }

  $arr_tcat[$cod_categoria.'_ideal']['total'] = $teorico;
  echo "<td align='right'></td>";

  echo "<td align='right'><a href='javascript:void(0)' onclick='javascript:document.frmFiltroTeorico.submit()'>".bd2moeda($teorico)."</a></td>";//$obj_buscar_movimentacoes->total_dinheiro
  $ideal_porc = porcentagem($teorico,$arr_tcat['13']['total']);//$obj_buscar_movimentacoes->total_dinheiro
  echo "<td align='right'>".$ideal_porc."</td></tr>";

  //echo "<td align='right'></td></tr>";
}

?>

<form name="frmFiltro" method="post">

 <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbr tdbt">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <!-- <option value="">Todas as Pizzarias</option> -->
        <?
        $con = conectabd();
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY p.nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
        {
          echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
          if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
            echo 'selected';
          echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
        }
        desconectabd($con);
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="data_inicial">Data Inicial:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo bd2data($data_inicial_filtro) ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
    <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo bd2data($data_final_filtro) ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>

    <tr>
        <td class="legenda tdbl" align="right"><label for="filtrar_filtro">Filtrar por:</label></td>
        <td class="">&nbsp;</td>
        <td class="tdbr ">
            <select name="filtrar_filtro">
               <option value="MES_REFERENCIA" <? if($filtrar_por=="MES_REFERENCIA") echo "SELECTED='SELECTED'"; ?>>Data de Competência</option>
               <option value="DATA_CRIADA" <? if($filtrar_por=="DATA_CRIADA") echo "SELECTED='SELECTED'"; ?>>Data de Criação</option>

                <option value="DATA_PAGAMENTO" <? if($filtrar_por=="DATA_PAGAMENTO") echo "SELECTED='SELECTED'"; ?>>Data de Pagamento</option>
               <!-- <option value="DATA_VENCIMENTO" <? if($filtrar_por=="DATA_VENCIMENTO") echo "SELECTED='SELECTED'"; ?>>Data de Vencimento</option> -->
<!--
                <option value="DATA_EMISSAO" <? if($filtrar_por=="DATA_EMISSAO") echo "SELECTED='SELECTED'"; ?>>Data de Emissão</option>
                <option value="DATA_CRIADA" <? if($filtrar_por=="DATA_CRIADA") echo "SELECTED='SELECTED'"; ?>>Data de Criação</option>-->
               

            </select>
        </td>
    </tr>

<!--    <tr>
      <td class="legenda tdbl " align="right"><label for="situacao_filtro">CMV:</label></td>
      <td class="">&nbsp;</td>
      <td class="tdbr ">
          <select name="situacao_filtro">
              <option value="titulos"<? if($situacao=="titulos") echo "SELECTED='SELECTED'"; ?>>Títulos Pagos</option>
              <option value="ideal"<? if($situacao=="ideal") echo "SELECTED='SELECTED'"; ?>>Ideal (Ficha Técnica)</option>
              <option value="real"<? if($situacao=="real") echo "SELECTED='SELECTED'"; ?>>Real (Inventário)</option>
          </select>
      </td>
    </tr>  -->
  <tr><td align="center" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"><input type='hidden' name='acao' value='buscar' /></td></tr>

  </table>

</form>

<br /><br />
<?$acao = validaVarPost("acao"); 
/*$data_inicial_filtro = data2bd($data_inicial_filtro);
$data_final_filtro = data2bd($data_final_filtro);*/


if($acao=="buscar"): ?>

        <?
        $arr_tcat = array();//Array para guardar a soma total por categoria

        if($filtrar_por =="MES_REFERENCIA")
        {
            $filtro_data = "tp.mes_ref BETWEEN month('$data_inicial_filtro') AND month('$data_final_filtro') AND tp.ano_ref BETWEEN year('$data_inicial_filtro') AND year('$data_final_filtro') ";
            $filtro_data_abertos = "tp.mes_ref > month('$data_final_filtro') AND tp.ano_ref >= year('$data_final_filtro')"; 
        }
        else if($filtrar_por =="DATA_PAGAMENTO")
        {
            $filtro_data =  "tp.data_pagamento BETWEEN '$data_inicial_filtro' AND '$data_final_filtro' and tp.situacao = 'PAGO'";
            $filtro_data_abertos =  "tp.data_pagamento > '$data_final_filtro' ";
        }
        else if($filtrar_por =="DATA_CRIADA")
        {
            $filtro_data =  "tp.data_hora_criacao BETWEEN '$data_inicial_filtro' AND '$data_final_filtro' ";
            $filtro_data_abertos =  "tp.data_hora_criacao > '$data_final_filtro' ";
        }
        else if($filtrar_por=="DATA_EMISSAO")
        {
            $filtro_data =  "tp.data_emissao BETWEEN '$data_inicial_filtro' AND '$data_final_filtro' ";
            $filtro_data_abertos =  "tp.data_emissao > '$data_final_filtro' ";
        }else
        {
            $filtro_data = "tp.data_vencimento >= '".$data_inicial_filtro."' AND tp.data_vencimento <= '".$data_final_filtro."'";
            $filtro_data_abertos = "tp.data_vencimento > '".$data_final_filtro."' ";
        } 

        $filtro_pizzaria = "";

        if ($cod_pizzarias)
            $filtro_pizzaria .= " AND t.cod_pizzarias = ".$cod_pizzarias;
        /*if($situacao != 'TODOS')
        {
            $filtro_situacao .= " AND tp.situacao = '$situacao'";
        }*/
        $conexao = conectabd();
        //$sql_buscar_plano_contas = "SELECT sum(tp.valor_total) valor_total,c.cod_titulos_categorias,c.titulos_categoria FROM ipi_titulos_subcategorias s inner join ipi_titulos_categorias c on c.cod_titulos_categorias = s.cod_titulos_categorias INNER JOIN ipi_titulos t ON (t.cod_titulos_subcategorias = s.cod_titulos_subcategorias) inner join ipi_titulos_parcelas tp on tp.cod_titulos = t.cod_titulos WHERE $filtro_data GROUP BY c.cod_titulos_categorias";

        $sql_buscar_plano_contas = "SELECT c.cod_titulos_categorias,c.meta_de_porcentagem,c.titulos_categoria, (SELECT sum(tp.valor_total) from ipi_titulos t inner join ipi_titulos_parcelas tp on tp.cod_titulos = t.cod_titulos where t.cod_titulos_subcategorias = s.cod_titulos_subcategorias AND $filtro_data $filtro_pizzaria ) as valor_total FROM ipi_titulos_categorias c  left join ipi_titulos_subcategorias s on c.cod_titulos_categorias = s.cod_titulos_categorias";


        $res_buscar_plano_contas = mysql_query($sql_buscar_plano_contas);
        $num_buscar_plano_contas = mysql_num_rows($res_buscar_plano_contas);
        //echo "<br />".$sql_buscar_plano_contas;

        while ($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_plano_contas))
        {
            $arr_tcat[$obj_buscar_plano_contas->cod_titulos_categorias]['total'] = $arr_tcat[$obj_buscar_plano_contas->cod_titulos_categorias]['total'] + abs($obj_buscar_plano_contas->valor_total);
            $arr_tcat[$obj_buscar_plano_contas->cod_titulos_categorias]['nome_categoria'] = $obj_buscar_plano_contas->titulos_categoria;
            $arr_tcat[$obj_buscar_plano_contas->cod_titulos_categorias]['meta_porcentagem'] = $obj_buscar_plano_contas->meta_de_porcentagem;
            //imprimir_plano_contas($obj_buscar_plano_contas->cod_titulos_categorias, $espaco, $filtro_data,$filtro_situacao, $data_inicial_filtro, $data_final_filtro, $cod_pizzarias);
        }

        /*echo "<br/><pre>";
        print_r($arr_tcat);
        echo "</pre>";*/
        ?>


        <?
        $sql_buscar_forn_pagar = "SELECT sum(tp.valor_total) as valor_pagar from ipi_titulos_parcelas tp inner join ipi_titulos t on t.cod_titulos = tp.cod_titulos where tp.situacao='ABERTO' and tp.valor_total <0  and $filtro_data_abertos";
        if($cod_pizzarias)
            $sql_buscar_forn_pagar .= " and t.cod_pizzarias = '".$cod_pizzarias."'";
        $res_buscar_forn_pagar = mysql_query($sql_buscar_forn_pagar);
        $obj_buscar_forn_pagar = mysql_fetch_object($res_buscar_forn_pagar);
        //$valor_porcentagem = porcentagem($obj_buscar_forn_pagar->valor_pagar,$arr_tcat[$c_vendas_brutas]['total'] );

        $sql_buscar_cont_receber = "SELECT sum(tp.valor_total) as valor_pagar from ipi_titulos_parcelas tp inner join ipi_titulos t on t.cod_titulos = tp.cod_titulos where tp.situacao='ABERTO' and tp.valor_total >0  and $filtro_data_abertos";
        if($cod_pizzarias)
            $sql_buscar_cont_receber .= " and t.cod_pizzarias = '".$cod_pizzarias."'";
        $res_buscar_cont_receber = mysql_query($sql_buscar_cont_receber);
        $obj_buscar_cont_receber = mysql_fetch_object($res_buscar_cont_receber);
        //echo $sql_buscar_cont_receber;
        //$valor_porcentagem = porcentagem($obj_buscar_forn_pagar->valor_pagar,$arr_tcat[$c_vendas_brutas]['total'] );


        $capital_giro = $obj_buscar_cont_receber->valor_pagar + $obj_buscar_forn_pagar->valor_pagar;
        ?>
        <table id="rel_dre" width='650' cellpadding="0" cellspacing="0" align="center">
        <thead>
              <tr>
              <td></td>
              <td colspan='5' align='center' style='background-color:#C6D9F0;color:black;'>DRE Sintético -<? echo "(".date("d/m/Y",strtotime($data_inicial_filtro)) ." - ".date("d/m/Y",strtotime($data_final_filtro))." )"; ?></td>
              <!-- <td></td> -->
              <!-- <td colspan='2' align='center'></td> -->
            </tr>
            <tr>
              <td></td>
              <td colspan='2' align='center' style='background-color:#C6D9F0;color:black;'>Realizado</td>
              <td></td>
              <td colspan='2' align='center' class='td_cinza'>Ideal</td>
            </tr>
            <tr class=''>
                <td style='width:46%'>
                    
                </td>
                <td  style='width:12%; text-align: center;color:black;font-weight:bold'>
                    (R$)
                </td>
                <td  style='width:8%; text-align: center;'>
                    (%)
                </td>

                <td  style='width:4%; text-align: center'>                   
                </td>

                <td  style='width:8%; text-align: center;font-weight:bold;color:black'>
                    (R$)
                </td>
                <td  style='width:12%; text-align: center'>
                    (%)
                </td>    
            </tr>
        </thead>
        <tbody>
            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>
            <tr class='td_cinza'>
                <td align='left'><? echo $arr_tcat[$c_vendas_brutas]['nome_categoria']; ?></td>
                <td align='right'><? echo conv_cat_dinheiro($arr_tcat[$c_vendas_brutas]['total']); ?></td>
                <td align='right'>100%</td>
                <td align='right'></td>
                <td align='right'><? echo bd2moeda(((100*$arr_tcat[$c_vendas_brutas]['total'])/100)); ?></td>
                <td align='right'>100%</td>
            </tr>
            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>
            <? linha_categoria('(-)',$c_imp_venda,$arr_tcat); ?>

            <? linha_categoria('(+)',$c_out_rec,$arr_tcat); ?>
<!--             <tr class='td_cinza'>
                <td align='left'><? echo $arr_tcat[13]['nome_categoria']; ?></td>
                <td align='right'><? echo $arr_tcat[13]['total']; ?></td>
                <td align='right'>100%</td>
            </tr> -->

            <? linha_categoria('(-)',$c_ded_venda,$arr_tcat); ?>
<!--             <tr class=''>
                <td align='left'>(-) DEDUÇÕES DE VENDA</td> 
                <td align='right'></td>
                <td align='right'></td>
                <td align='right'></td>
                <td align='right'></td>
                <td align='right'></td>
            </tr> -->
            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>
            <?
            $vendas_liquidas = $arr_tcat[$c_vendas_brutas]['total'] + $arr_tcat[$c_out_rec]['total'] - $arr_tcat[$c_imp_venda]['total'];

            $vendas_liquidas_ideal = $arr_tcat[$c_vendas_brutas]['total'] + ((bd2moeda($arr_tcat[$c_out_rec]['meta_porcentagem'])-$arr_tcat[$c_imp_venda]['meta_porcentagem'])*$arr_tcat[$c_vendas_brutas]['total'])/100;

            ?>
            <tr class='td_cinza'>
                <td align='left'>(=) Venda Liquida</td>
                <td align='right'><? echo conv_cat_dinheiro($vendas_liquidas) ?></td>
                <td align='right'><? echo porcentagem($vendas_liquidas,$arr_tcat[$c_vendas_brutas]['total']) ?></td>
                <td align='right'></td>
                <td align='right'><? echo conv_cat_dinheiro($vendas_liquidas_ideal) ?></td>
                <td align='right'><? echo porcentagem($vendas_liquidas_ideal,$arr_tcat[$c_vendas_brutas]['total']) ?></td>
            </tr>
                        <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>

            <? linha_cmv('(-)',$arr_tcat); ?>


            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>
            <?
            $lucro_bruto = $vendas_liquidas - $arr_tcat[$c_cmv]['total'];
            $lucro_bruto_ideal = $vendas_liquidas_ideal - $arr_tcat[$c_cmv."_ideal"]['total'];
            ?>
            <tr class='td_cinza_neg'>
                <td align='left' style='border-color:black;border-right:0'>(=) Lucro Bruto</td>
                <td align='right' style='border-color:black;border-left:0;border-right:0'><? echo conv_cat_dinheiro($lucro_bruto); ?></td>
                <td align='right' style='border-color:black;border-left:0;border-right:0'><? echo porcentagem( $lucro_bruto,$arr_tcat[$c_vendas_brutas]['total'] ) ?></td>
                <td align='right' style='border-color:black;border-left:0;border-right:0'></td>
                <td align='right' style='border-color:black;border-left:0;border-right:0'><? echo conv_cat_dinheiro($lucro_bruto_ideal); ?></td>
                <td align='right' style='border-color:black;border-left:0'><? echo porcentagem( $lucro_bruto_ideal,$arr_tcat[$c_vendas_brutas]['total'] ) ?></td>
            </tr>
            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>
            <?
            $desp_operacionais = $arr_tcat[$c_folha_enc]['total'] + $arr_tcat[$c_fixo_inst_adm]['total'] + $arr_tcat[$c_var_inst_adm]['total'] + $arr_tcat[$c_mat_escr_limp]['total'] + $arr_tcat[$c_royalties]['total'] + $arr_tcat[$c_fundo_mkt]['total'] + $arr_tcat[$c_mkt_local]['total'] + $arr_tcat[$c_desp_cobr_cart]['total'] + $arr_tcat[$c_entregas]['total'];
            
            $porc_desp_operacionais = bd2moeda($arr_tcat[$c_folha_enc]['meta_porcentagem']) + bd2moeda($arr_tcat[$c_fixo_inst_adm]['meta_porcentagem']) + bd2moeda($arr_tcat[$c_var_inst_adm]['meta_porcentagem']) + bd2moeda($arr_tcat[$c_mat_escr_limp]['meta_porcentagem']) + bd2moeda($arr_tcat[$c_royalties]['meta_porcentagem']) + bd2moeda($arr_tcat[$c_fundo_mkt]['meta_porcentagem']) + bd2moeda($arr_tcat[$c_mkt_local]['meta_porcentagem']) + bd2moeda($arr_tcat[$c_desp_cobr_cart]['meta_porcentagem']) + bd2moeda($arr_tcat[$c_entregas]['meta_porcentagem']);

            $desp_operacionais_ideal = $arr_tcat[$c_vendas_brutas]['total']*$porc_desp_operacionais/100;

            ?>
            <tr class='td_cinza_neg'>
                <td align='left'>Despesas Operacionais</td>
                <td align='right'><? echo conv_cat_dinheiro($desp_operacionais); ?></td>
                <td align='right'><? echo porcentagem($desp_operacionais,$arr_tcat[$c_vendas_brutas]['total'] ) ?></td>
                <td align='right'></td>
                <td align='right'><? echo conv_cat_dinheiro($desp_operacionais_ideal); ?></td>
                <td align='right'><? echo porcentagem($desp_operacionais_ideal,$arr_tcat[$c_vendas_brutas]['total'] ) ?></td>
            </tr>
            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>
            <? linha_categoria('(-)',$c_folha_enc,$arr_tcat); ?>

            <? linha_categoria('(-)',$c_fixo_inst_adm,$arr_tcat); ?>

            <? linha_categoria('(-)',$c_var_inst_adm,$arr_tcat); ?>

            <? linha_categoria('(-)',$c_mat_escr_limp,$arr_tcat); ?>
            
            <? linha_categoria('(-)',$c_entregas,$arr_tcat); ?>

            <? linha_categoria('(-)',$c_royalties,$arr_tcat); ?>

            <? linha_categoria('(-)',$c_fundo_mkt,$arr_tcat); ?>

            <? linha_categoria('(-)',$c_mkt_local,$arr_tcat); ?>

            <? linha_categoria('(-)',$c_desp_cobr_cart,$arr_tcat); ?>
            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>
            <?
            $res_operacional = $lucro_bruto - $desp_operacionais;
            $res_operacional_ideal = $lucro_bruto_ideal - $desp_operacionais_ideal;
            ?>
            <tr class='td_verde'>
                <td align='left'>(=) Resultado Operacional Líquido (EBITDA)</td>
                <td align='right'><? echo conv_cat_dinheiro($res_operacional); ?></td>
                <td align='right'><? echo porcentagem($res_operacional,$arr_tcat[$c_vendas_brutas]['total']); ?></td>
                <td align='right'></td>
                <td align='right'><? echo conv_cat_dinheiro($res_operacional_ideal); ?></td>
                <td align='right'><? echo porcentagem($res_operacional_ideal,$arr_tcat[$c_vendas_brutas]['total']); ?></td>
            </tr>
            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>

            <? linha_categoria('(- +)',$c_desp_rec_n_ope,$arr_tcat); ?>

            <? linha_categoria('(- +)',$c_desp_rec_fin,$arr_tcat); ?>

            <? linha_categoria('(-)',$c_depreciacao,$arr_tcat); ?>

            <? linha_categoria('(-)',$c_rescisoes,$arr_tcat); ?>
<!--             <tr class=''>
                <td align='left'>(-) <? echo 'RESCISÕES'; ?></td> 
                <td align='right'></td>
                <td align='right'></td>
                <td align='right'></td>
                <td align='right' colspan='2'></td>
            </tr> -->

            <? linha_categoria('(-)',$c_imp_lucro,$arr_tcat); ?>
            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>
            <?
            $res_liquido = $res_operacional - $arr_tcat[$c_imp_lucro]['total'] - $arr_tcat[$c_desp_rec_fin]['total'] - $arr_tcat[$c_desp_rec_n_ope]['total'] - $arr_tcat[$c_depreciacao]['total'] - $arr_tcat[$c_rescisoes]['total'];$

            $res_liquido_ideal = $res_operacional_ideal - ( bd2moeda($arr_tcat[$c_imp_lucro]['meta_porcentagem']) - bd2moeda($arr_tcat[$c_desp_rec_fin]['meta_porcentagem']) - bd2moeda($arr_tcat[$c_depreciacao]['meta_porcentagem'])- bd2moeda($arr_tcat[$c_rescisoes]['meta_porcentagem']) - bd2moeda($arr_tcat[$c_desp_rec_n_ope]['meta_porcentagem']))*$arr_tcat[$c_vendas_brutas]['total']/100;
            ?>
            <tr class='td_verde'>
                <td align='left'>(=) Resultado Liquido</td>
                <td align='right'><? echo conv_cat_dinheiro($res_liquido); ?></td>
                <td align='right'><? echo porcentagem($res_liquido,$arr_tcat[$c_vendas_brutas]['total'] ) ?></td>
                <td align='right'></td>
                <td align='right'><? echo conv_cat_dinheiro($res_liquido_ideal); ?></td>
                <td align='right'><? echo porcentagem($res_liquido_ideal,$arr_tcat[$c_vendas_brutas]['total'] ) ?></td>
            </tr>
            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr>
            <?
            $ger_caixa = $arr_tcat[$c_prolabore]['total'] - $arr_tcat[$c_desp_invest]['total'] - $arr_tcat[$c_op_credito]['total'] ;//+ $capital_giro;
            $ger_caixa_ideal = (bd2moeda($arr_tcat[$c_prolabore]['meta_porcentagem']) - bd2moeda($arr_tcat[$c_desp_invest]['meta_porcentagem']) - bd2moeda($arr_tcat[$c_op_credito]['meta_porcentagem']))*$arr_tcat[$c_vendas_brutas]['total']/100 ;

            ?>
            <tr class='td_cinza'>
                <td align='left'>(- +) Movimento de caixa</td>
                <td align='right' style='font-weight:bold' class='td_cinza_claro'><? echo conv_cat_dinheiro($ger_caixa); ?></td>
                <td align='right' class='td_cinza_claro'><? echo porcentagem($ger_caixa,$arr_tcat[$c_vendas_brutas]['total']); ?></td>
                <td align='right'></td>
                <td align='right'><? echo conv_cat_dinheiro($ger_caixa_ideal); ?></td>
                <td align='right'><? echo porcentagem($ger_caixa_ideal,$arr_tcat[$c_vendas_brutas]['total']); ?></td>
            </tr>
            
            <? linha_categoria('(-)',$c_prolabore,$arr_tcat,'td_cinza_claro'); ?>
<!--             <tr class=''>
                <td align='left'>(-) <? echo $arr_tcat[$c_amortizacao]['nome_categoria']; ?></td>
                <td align='right'><? echo conv_cat_dinheiro($arr_tcat[$c_amortizacao]['total']); ?></td>
                <td align='right'>100%</td>
            </tr> -->
            <tr class=''>
                <td align='left'>(-) AMORTIZAÇÃO</td> <!-- falta calcular -->
                <td align='right' class='letra_verde'>0,00</td>
                <td align='right' class='td_cinza_claro'></td>
                <td align='right'></td>
                <td align='right' colspan='2'></td>
            </tr>

            <? linha_categoria('(-)',$c_desp_invest,$arr_tcat,'td_cinza_claro'); ?>
<!--             <tr class=''>
                <td align='left'>(+) <? echo $arr_tcat[$c_apt_capital]['nome_categoria']; ?></td>
                <td align='right'><? echo conv_cat_dinheiro($arr_tcat[$c_apt_capital]['total']); ?></td>
                <td align='right'>100%</td>
            </tr> -->

            <? linha_categoria('(+)',$c_apt_capital,$arr_tcat,'td_cinza_claro'); ?>
<!--             <tr class=''>
                <td align='left'>(+) APORTE CAPITAL</td> 
                <td align='right' class='td_cinza_claro'></td>
                <td align='right' class='td_cinza_claro'></td>
                <td align='right'></td>
                <td align='right' colspan='2'></td>
            </tr> -->


            <? linha_categoria('(+)',$c_op_credito,$arr_tcat,'td_cinza_claro'); ?>
<!--             <tr class=''>
                <td align='left'>(-) <? echo $arr_tcat[$c_invest_cap_giro]['nome_categoria']; ?></td>
                <td align='right'><? echo conv_cat_dinheiro($arr_tcat[$c_invest_cap_giro]['total']); ?></td>
                <td align='right'>100%</td>
            </tr> -->
<!--                         <tr class=''>
                <td align='left'>(- +) INVESTIMENTO EM CAPITAL DE GIRO </td> 
                <td align='right' style='font-weight:bold'class='td_cinza_claro'><? echo bd2moeda($capital_giro); ?></td>
                <td align='right' class='td_cinza_claro'><? echo porcentagem($capital_giro,$arr_tcat[$c_vendas_brutas]['total']) ;?></td>
                <td align='right' ></td>
                <td align='right' colspan='2'></td>
            </tr>
            <tr class=''>
                <td align='left' style='padding-left: 30px;'>(-) VARIAÇÃO NO ESTOQUE</td> <!-- falta calcular 
                <td align='right' class='td_cinza_claro'></td>
                <td align='right' class='td_cinza_claro'></td>
                <td align='right'></td>
                <td align='right' colspan='2'></td>
            </tr>


            <tr class=''>
                <td align='left' style='padding-left: 30px;'><a href='javascript:void(0)' onclick='detalhes_contas("explodir_cart_receber","VARIAÇÃO EM CARTÕES E CONTAS A RECEBER",<? echo '"'.$cod_pizzarias.'"' ?>,1)'>(-) VARIAÇÃO EM CARTÕES E CONTAS A RECEBER</a></td> 
                <td align='right' class='td_cinza_claro'><? echo bd2moeda($obj_buscar_cont_receber->valor_pagar); ?></td>
                <td align='right' class='td_cinza_claro'></td>
                <td align='right'></td>
                <td align='right' colspan='2'></td>
            </tr>


            <tr class=''>
                <td align='left' style='padding-left: 30px;'><a href='javascript:void(0)' onclick='detalhes_contas("explodir_fornecedores_pagar","VARIAÇÃO EM FORNECEDORES A PAGAR",<? echo '"'.$cod_pizzarias.'"' ?>,1)'>(+) VARIAÇÃO EM FORNECEDORES A PAGAR</a></td> 
                <td align='right' class='td_cinza_claro'><? echo bd2moeda($obj_buscar_forn_pagar->valor_pagar); ?></td>
                <td align='right' class='td_cinza_claro'></td>
                <td align='right'></td>
                <td align='right' colspan='2'></td>
            </tr>-->
            <tr>
               <td colspan='6'>&nbsp;</td>
            </tr> 
            <?
            $ger_caixa = $res_liquido + $ger_caixa;
            $ger_caixa_ideal = $res_liquido_ideal + $ger_caixa_ideal;
            ?>
            <tr class='td_azul'>
                <td align='left'>(=) Resultado de Caixa do Período</td>
                <td align='right'><? echo conv_cat_dinheiro($ger_caixa); ?></td>
                <td align='right'><? echo porcentagem($ger_caixa,$arr_tcat[$c_vendas_brutas]['total']); ?></td>
                <td align='right'></td>
                <td align='right'><? echo conv_cat_dinheiro($ger_caixa_ideal); ?></td>
                <td align='right'><? echo porcentagem($ger_caixa_ideal,$arr_tcat[$c_vendas_brutas]['total']); ?></td>
            </tr>
            <?

        desconectabd($conexao);
        
        ?>
      
        </tbody>
    </table>
<? endif; ?>

<? rodape(); ?>
