<?php

/**
 * Resultados das Enquetes.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       12/05/2010   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Resultados das Enquetes');

$acao = validaVarPost('acao');
$acao = (validar_var_get('acao') ? validar_var_get('acao') : $acao);

$tabela = 'ipi_enquetes';
$chave_primaria = 'cod_enquetes';

$cod_enquetes = 1; // Forçado para a enquete mail
?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_interna.css" />
<!--<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />-->
<link rel="stylesheet" type="text/css" media="screen" href="../lib/js/datepicker_vista/datepicker_vista.css" />

<script type="text/javascript" src="../lib/js/calendario.js"></script>
<script type="text/javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>
<script type="text/javascript" src="../lib/js/tabs_interna.js"></script>

<script>

function trocar(id_trocar)
{
  if (document.getElementById(id_trocar).style.display == 'none')
  {
    document.getElementById(id_trocar).style.display = 'block';
  }
  else
  {
    document.getElementById(id_trocar).style.display = 'none';
  }
}

function detalhes_pedido(cod_clientes_ipi_enquete_respostas, cod_pedidos, visualizacao)
{
	var iebody = (document.compatMode && document.compatMode != "BackCompat") ? document.documentElement : document.body;
    var dsoctop = document.all ? iebody.scrollTop : pageYOffset;
    
    var divFundo = new Element('div', 
    {
        'id': 'divFundo',
        'styles': 
        {
            'position': 'absolute',
            'top': 0,
            'left': 0,
            'background-color': '#ffffff',
            'height': document.documentElement.clientHeight + dsoctop,
            'width':  document.documentElement.clientWidth,
            'z-index': 99998,
            'background-color': '#FFFFFF'
        }
    });
    
    var divMsg = new Element('div', 
    {
        'id': 'divMsg',
        'styles': 
        {
            'position': 'absolute',
            'left': (document.body.clientWidth - 800) / 2,
            'background-color': '#ffffff',
            'border': '2px solid #D44E08',
            'width' : 800,
            'height': 500,
            'padding': 20,
            'z-index': 99999,
            'overflow': 'auto'
        }
    });
    
    var win = window;
    var middle = win.getScrollTop() + (win.getHeight() / 2);
    var top = Math.max(0, middle - (500 / 2));
              
    divMsg.setStyle('top', top);
    
    var url = 'acao=detalhes_pedido&cod_clientes_ipi_enquete_respostas=' + cod_clientes_ipi_enquete_respostas + '&cod_pedidos=' + cod_pedidos + '&visualizacao=' + visualizacao;
    
    new Request.HTML(
    {
        url: 'ipi_enquete_resultado_ajax.php',
        update: divMsg,
        onComplete: function()
        {
			        	
        }
    }).send(url);
    
    divFundo.setStyle('opacity', 0.7);
    
    $(document.body).adopt(divMsg);
    $(document.body).adopt(divFundo);
}


function enviar_resposta(cod_clientes_ipi_enquete_respostas, cod_pedidos, tipo_reposta)
{
	  $('tipo_reposta').setProperty('value', tipo_reposta);
    var resposta_a = new Array();
    var resposta_html = document.getElementsByName('resposta[]');
    var cliente_ipi_resposta_a = new Array();
    var cliente_ipi_resposta_h = document.getElementsByName('cod_clientes_ipi_enquete_respostas[]');
    for(var i=0;i<resposta_html.length;i++)
    {
      resposta_a.push(resposta_html[i].value);
    }
    for(var a=0;a<cliente_ipi_resposta_h.length;a++)
    {
      cliente_ipi_resposta_a.push(cliente_ipi_resposta_h.item(a).value);
    }
    var cupom = document.getElementsByName('cod_respostas_cupom[]');
    var cupom_a = new Array();
    for(var c=0;c<cupom.length;c++)
    {
        cupom_a.push(cupom.item(c).value);
    }   
    var cabecalho = document.getElementById('cabecalho_resp');
    var url = 'acao=enviar_resposta&cod_pedidos='+cod_pedidos+'&tipo_reposta='+tipo_reposta+'&cod_clientes_ipi_enquete_respostas='+cliente_ipi_resposta_a+'&resposta='+resposta_a.join("@@=")+'&cabecalho='+cabecalho.value+'&cupom='+cupom_a;
    
    new Request.JSON({url: 'ipi_enquete_resultado_ajax.php',
      onComplete: function(resposta)
		  {
        //alert(resposta.status);
        if(resposta.status=="ok")
        {
			    cancelar_resposta();

			    // Apagando todos os botões de responder da enquete!
			    $each($$('input'), function(obj, index)
			    {
        		    if ((obj.getProperty('type') == 'button') && (obj.getProperty('class').match('responder_' + cod_pedidos)))
        			{
        		        obj.destroy();
        			}
		        });
        }
		  }
    }).send(url);
}

function adicionar_resposta_automatica(num,cod_respostas)
{

    var url = 'acao=resposta_padrao&cod_respostas=' + cod_respostas;
    
    new Request.HTML(
    {
        url: 'ipi_enquete_resultado_ajax.php',
        update: 'resposta_pizzaria_'+num,
        onComplete: function()
        {
         
        }
    }).send(url);
    
    var url = 'acao=verificar_cupom&cod_respostas=' + cod_respostas;
    new Request.JSON({url: 'ipi_enquete_resultado_ajax.php',
      onComplete: function(resposta)
          {
            if(resposta.cod!="nenhum")
            {
               $('cod_respostas_cupom_'+num).value=resposta.cod;
            }
          }
    }).send(url);
}

function cancelar_resposta()
{
	$('divMsg').destroy();
	$('divFundo').destroy();    
}

window.addEvent('domready', function() 
{
    new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial'});
    new vlaDatePicker('data_final', {openWith: 'botao_data_final'});
    
    if($defined(document.getElementById('tabs_internas')))
    {
      var tabs_internas = new TabsInterna('tabs_internas');
      <? if (validar_var_get('acao')) echo 'tabs_internas.irpara('.validar_var_get('cod').');'; ?>
    }
});

</script>

<?


$data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
$data_inicial = (validar_var_get('acao') ? date('Y-m-d', strtotime('-15 day')) : $data_inicial);

$data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
$data_final = (validar_var_get('acao') ? date('Y-m-d') : $data_final);

$cod_pizzarias = validaVarPost('cod_pizzarias');
$cod_pizzarias = (validar_var_get('acao') ? false : $cod_pizzarias);

?>

<form name="frmFiltro" method="post">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
    <tr>
        <td class="legenda tdbl tdbt" align="right"><label for="data_inicial">Data
        Inicial:</label></td>
        <td class="tdbt ">&nbsp;</td>
        <td class="tdbr tdbt "><input class="requerido" type="text"
            name="data_inicial" id="data_inicial" size="8"
            value="<?
            echo bd2data($data_inicial)?>"
            onkeypress="return MascaraData(this, event)"> &nbsp; <a
            href="javascript:void(0);" id="botao_data_inicial"><img
            src="../lib/img/principal/botao-data.gif"></a></td>
    </tr>

    <tr>
        <td class="legenda tdbl " align="right"><label for="data_final">Data
        Final:</label></td>
        <td >&nbsp;</td>
        <td class="tdbr "><input class="requerido" type="text"
            name="data_final" id="data_final" size="8"
            value="<?
            echo bd2data($data_final)?>"
            onkeypress="return MascaraData(this, event)"> &nbsp; <a
            href="javascript:void(0);" id="botao_data_final"><img
            src="../lib/img/principal/botao-data.gif"></a></td>
    </tr>


    <tr>
        <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
        <td>&nbsp;</td>
        <td class="tdbr ">
          <select name="cod_pizzarias" id="cod_pizzarias">
            <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
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
        <td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Filtrar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="buscar">

</form>

<br><br>

<?php if($acao == 'buscar'): ?>

<?php 

$conexao = conectabd();

$sql_buscar_perguntas = "SELECT * FROM ipi_enquete_perguntas WHERE cod_enquetes = '$cod_enquetes' and pergunta_pessoal = 0 ORDER BY cod_enquete_perguntas";
$res_buscar_perguntas = mysql_query($sql_buscar_perguntas);

$num_grafico = 0;

echo '<table border="0" align="center" style="margin: 0px auto;"><tr>';

while ($obj_buscar_perguntas = mysql_fetch_object($res_buscar_perguntas))
{
    echo '<td style="border: none !important;" aling="center" width="350"><p align="center"><b>' . bd2texto($obj_buscar_perguntas->pergunta) . '</b></p><br><div id="indicador_' . $num_grafico . '" style="margin: 0px auto; width: 300px;"></div></td>';
    
    $num_grafico++;
}

echo '</tr></table>';

?>

<div id="tabs_internas" style="width: 100%; min-width: 700px;">
    <div class="menuTabInterno">
    <ul>
        <?php 
        
        $sql_buscar_perguntas = "SELECT * FROM ipi_enquete_perguntas WHERE cod_enquetes = '$cod_enquetes' AND pergunta_pessoal = 0 ORDER BY cod_enquete_perguntas";
        $res_buscar_perguntas = mysql_query($sql_buscar_perguntas);
        
        while ($obj_buscar_perguntas = mysql_fetch_object($res_buscar_perguntas))
        {
        	echo '<li><a href="javascript:;">' . bd2texto($obj_buscar_perguntas->pergunta) . '</a></li>';
        }
        
        ?>
    </ul>
    </div>
    
    <?php 
      
    $data_inicial_sql = ($data_inicial) . ' 00:00:00';
    $data_final_sql = ($data_final) . ' 23:59:59';

    $sql_buscar_pedidos_tempo = "SELECT cod_pedidos from ipi_pedidos where 1=1 ";
    if (($data_inicial) && ($data_final))
    {
      $sql_buscar_pedidos_tempo .= "AND data_hora_pedido >= '$data_inicial_sql' AND data_hora_pedido <= '$data_final_sql'";
    }

    if($cod_pizzarias)
    {
      $sql_buscar_pedidos_tempo .= "AND cod_pizzarias = '$cod_pizzarias'";    
    }
    else
    {
      $sql_buscar_pedidos_tempo .= " AND cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
    }

    $res_buscar_pedidos_tempo = mysql_query($sql_buscar_pedidos_tempo);
    while($obj_buscar_pedidos_tempo = mysql_fetch_object($res_buscar_pedidos_tempo))
    {
      $arr_pedidos[] = $obj_buscar_pedidos_tempo->cod_pedidos;
    }

    $sql_buscar_perguntas = "SELECT * FROM ipi_enquete_perguntas WHERE cod_enquetes = '$cod_enquetes' ORDER BY cod_enquete_perguntas";
    $res_buscar_perguntas = mysql_query($sql_buscar_perguntas);
    
    $num_grafico = 0;
    
    while ($obj_buscar_perguntas = mysql_fetch_object($res_buscar_perguntas)):
    	
	?>
	
	<div class="painelTabInterno">
	<table width="650" align="center" style="margin: 0px auto;">
		<tr>
			<td width="500" id="grafico_pizza_<?php echo $num_grafico; ?>"></td>
			<td width="200" id="grid_<?php echo $num_grafico; ?>"></td>
		</tr>
	</table>
	
	<br>
	
	<?php

	$sql_buscar_respostas = "SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas = '$obj_buscar_perguntas->cod_enquete_perguntas'";
    $res_buscar_respostas = mysql_query($sql_buscar_respostas);
    
    echo '<table class="listaEnquete" border="0" cellspacing="0">';
    
    echo '<tr>';
    echo '<td width="40" align="center"><b>Votos</b></td>';
    echo '<td align="center"><b>Resposta</b></td>';
    echo '</tr>';
    
    while ($obj_buscar_respostas = mysql_fetch_object($res_buscar_respostas))
    {
        $sql_buscar_numero_respostas = "SELECT COUNT(cod_enquete_respostas) quantidade FROM ipi_clientes_ipi_enquete_respostas cer LEFT JOIN ipi_pedidos p ON (cer.cod_pedidos=p.cod_pedidos) WHERE p.cod_pedidos in(".implode(',',$arr_pedidos).") ";

        $sql_buscar_numero_respostas .= " AND cer.cod_enquete_respostas = '".$obj_buscar_respostas->cod_enquete_respostas."'";
      //echo "<br>1: ".$sql_buscar_numero_respostas;
        
        $obj_buscar_numero_respostas = executaBuscaSimples($sql_buscar_numero_respostas, $conexao);
        
        $quantidade = ($obj_buscar_numero_respostas->quantidade > 0) ? $obj_buscar_numero_respostas->quantidade : 0;
        
        echo '<tr>';
        echo '<td align="right">' . $quantidade . '</td>';
        echo '<td>';
        
        if ($obj_buscar_respostas->justifica)
        {
            echo "<a href='javascript:trocar(\"tabRespJus" . $obj_buscar_respostas->cod_enquete_respostas . "\");' title='Clique para as justificativas'>" . $obj_buscar_respostas->resposta . "</a>";
        }
        else
        {
            echo $obj_buscar_respostas->resposta;
        }
        
        if ($obj_buscar_respostas->justifica)
        {
            echo '<div id="tabRespJus' . $obj_buscar_respostas->cod_enquete_respostas . '" style="display: none;"><br>';
            
            $sql_buscar_detalhes = "SELECT cer.*, c.nome, p.*, e.nome AS entregadores_nome FROM ipi_clientes_ipi_enquete_respostas cer INNER JOIN ipi_clientes c ON (c.cod_clientes=cer.cod_clientes) INNER JOIN ipi_pedidos p ON (cer.cod_pedidos = p.cod_pedidos) LEFT JOIN ipi_entregadores e ON (p.cod_entregadores = e.cod_entregadores) WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND cer.cod_enquete_respostas=" . $obj_buscar_respostas->cod_enquete_respostas." AND p.cod_pedidos in(".implode(',',$arr_pedidos).") ";

            $res_buscar_detalhes = mysql_query($sql_buscar_detalhes);
            $num_buscar_detalhes = mysql_num_rows($res_buscar_detalhes);
            
            if ($num_buscar_detalhes > 0)
            {
                echo '<table class="listaEnquete" cellspacing="0" align="center" style="margin: 0px auto;">';
                
                echo '<thead>';
                echo '<tr>';
                echo '<td align="center"><b>Justificativa</b></td>';
                echo '<td width="80" align="center"><b>Data Resposta</b></td>';
                echo '<td width="80" align="center"><b>Data do Pedido</b></td>';
                echo '<td width="80" align="center"><b>Num. Pedido</b></td>';
                echo '<td width="200" align="center"><b>Cliente</b></td>';
                echo '<td width="160" align="center"><b>Bairro</b></td>';
                echo '<td width="80" align="center"><b>Entregador</b></td>';
                echo '<td width="80" align="center"><b>Responder</b></td>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                while ($obj_buscar_detalhes = mysql_fetch_object($res_buscar_detalhes))
                {
                    echo '<tr>';
                    echo '<td>' . bd2texto($obj_buscar_detalhes->justificativa) . '&nbsp;</td>';
                    echo '<td align="center">' . bd2datahora($obj_buscar_detalhes->data_hora_resposta) . '&nbsp;</td>';
                    echo '<td align="center">' . bd2datahora($obj_buscar_detalhes->data_hora_pedido) . '&nbsp;</td>';
                    echo '<td align="center">' . sprintf("%08d", $obj_buscar_detalhes->cod_pedidos) . '&nbsp;</td>';
                    echo '<td align="center">' . '<a href="ipi_clientes_franquia.php?cc='.$obj_buscar_detalhes->cod_clientes.'" target="_blank" title="Clique Aqui e acesse o cadastro do cliente.">' . bd2texto($obj_buscar_detalhes->nome) . '</a>&nbsp;</td>';
                    echo '<td align="center">' . bd2texto($obj_buscar_detalhes->bairro) . '&nbsp;</td>';
                    echo '<td align="center">' . bd2texto($obj_buscar_detalhes->entregadores_nome) . '&nbsp;</td>';
                    
                    if($obj_buscar_detalhes->respondida_pizzaria || $obj_buscar_detalhes->respondida_pizzaria_tel)
                    {
                    	echo '<td align="center"><small>Respondida em: ' . bd2datahora($obj_buscar_detalhes->data_hora_resposta_pizzaria) . '</small><br><input type="button" class="botaoAzul responder_' . $obj_buscar_detalhes->cod_pedidos. '" value="Visualizar" onclick="detalhes_pedido(' . $obj_buscar_detalhes->cod_clientes_ipi_enquete_respostas . ', ' . $obj_buscar_detalhes->cod_pedidos . ', 1)"></td>';
                    }
                    else
                    {
                    	echo '<td align="center"><input type="button" class="botaoAzul responder_' . $obj_buscar_detalhes->cod_pedidos. '" value="Responder" onclick="detalhes_pedido(' . $obj_buscar_detalhes->cod_clientes_ipi_enquete_respostas . ', ' . $obj_buscar_detalhes->cod_pedidos . ', 0)"></td>';
                    }
                    
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
            }
            else
            {
                echo "<center>Nenhum voto nesta resposta!</center><br>";
            }
            
            echo '<br></div>';
        }
        
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    ?>
	
	</div>
	
	<?php 
    
	$num_grafico++;
	
	endwhile;
    
    ?>
    
</div>

<?php 

$sql_buscar_perguntas = "SELECT * FROM ipi_enquete_perguntas WHERE cod_enquetes = '$cod_enquetes' and  pergunta_pessoal = 0 ORDER BY cod_enquete_perguntas";
$res_buscar_perguntas = mysql_query($sql_buscar_perguntas);

$num_grafico = 0;

while ($obj_buscar_perguntas = mysql_fetch_object($res_buscar_perguntas)):
    ?>

    <script>
    var indicadores_<? echo $num_grafico; ?> = new FusionCharts('../lib/swf/fusioncharts/angulargauge.swf', 'grafico <?echo $num_grafico; ?>', 300, 150, 0, 0, 'ffffff', 0);
    indicadores_<? echo $num_grafico; ?>.setDataURL('ipi_enquete_resultado_dados.php?param=1,<? echo $codigo_enquetes; ?>,<? echo $obj_buscar_perguntas->cod_enquete_perguntas; ?>,<? echo $data_inicial?>,<? echo $data_final?>,<? echo $cod_pizzarias?>,<? echo utf8_encode($obj_buscar_perguntas->pergunta); ?>');
    indicadores_<? echo $num_grafico; ?>.render('indicador_<? echo $num_grafico; ?>');

    
    var pizza_<?php echo $num_grafico ?> = new FusionCharts('../lib/swf/fusioncharts/doughnut2d.swf', 'grafico<?php echo $num_grafico ?>', 500, 200, 0, 0, 'ffffff', 0);
    pizza_<?php echo $num_grafico ?>.setDataURL('ipi_enquete_resultado_dados.php?param=2,<? echo $cod_enquetes; ?>,<? echo $obj_buscar_perguntas->cod_enquete_perguntas; ?>,<? echo $data_inicial?>,<? echo $data_final?>,<? echo $cod_pizzarias?>,<? echo utf8_encode($obj_buscar_perguntas->pergunta); ?>');
    pizza_<?php echo $num_grafico ?>.render('grafico_pizza_<?php echo $num_grafico ?>');

    
	  var grid_<? echo $num_grafico; ?> = new FusionCharts('../lib/swf/fusioncharts/ssgrid.swf', 'grafico <?echo $num_grafico; ?>', 200, 200, 0, 0, 'ffffff', 0);
	  grid_<? echo $num_grafico; ?>.setDataURL('ipi_enquete_resultado_dados.php?param=3,<? echo $cod_enquetes; ?>,<? echo $obj_buscar_perguntas->cod_enquete_perguntas; ?>,<? echo $data_inicial?>,<? echo $data_final?>,<? echo $cod_pizzarias?>,<? echo utf8_encode($obj_buscar_perguntas->pergunta); ?>');
	  grid_<? echo $num_grafico; ?>.render('grid_<? echo $num_grafico; ?>');
    </script>

    <?
    $num_grafico++;
endwhile;

desconectabd($conexao);

?>

<?php endif; ?>

<?php rodape(); ?>
