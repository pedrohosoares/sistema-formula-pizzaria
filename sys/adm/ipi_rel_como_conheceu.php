<?php

/**
 * Tela de consulta e alteração de dados de clientes.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR            DESCRIÇÃO 
 * ======    ==========   ==============   =============================================================
 *
 * 1.0       23/08/2013   FilipeGranato    Criado.
 *
 */
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatorio Como Conheceu');

$tabela = 'ipi_clientes';
$chave_primaria = 'cod_clientes';
$quant_pagina = 50;
$acao = validavarPost('acao');


function normalizar_nome_internet ($nome)
{
    $p = strtr($nome, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ", "aaaaeeiooouucAAAAEEIOOOUUC");
    $p = preg_replace("/[^a-zA-Z0-9]+/", '', $p);
    //$url = strtolower($url);
    //$url = $p;
    return $p;
}

?>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
  <script type="text/javascript" src="../lib/js/calendario.js"></script>
<script type="text/javascript" src="../lib/js/fusioncharts/fusioncharts.js"></script>
<script>
window.addEvent('domready', function() { 
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 


<?
$cod_pizzarias = validavarPost('cod_pizzarias');
$data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
$data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');

?>

</script>
<form name="frmFiltro" method="post" >
<table class="caixa" align="center" cellspacing="0" cellpadding="0">
<tr>
<td class="tdbt tdbl"><label for='cod_pizzarias'><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
<td class="tdbt">&nbsp;</td>
<td class="tdbr tdbt">
  <select name="cod_pizzarias" id="cod_pizzarias">
    <?// 
    $con = conectabd();
    //p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")
    $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") and situacao='ATIVO' ORDER BY p.nome";//pedido do rubens,não mostrar a matrix
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
    <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo bd2data($data_inicial) ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
    <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo bd2data($data_final) ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>

<tr>
	<td colspan='3' align='right' class="tdbr tdbl tdbb">
		<input class='botao' type='submit' value='Buscar'/>
	</td>
</tr>
</table>

<input type="hidden" name="acao" value="buscar">

</form>
<? $con = conectar_bd(); ?>
<? if($acao=='buscar' && $cod_pizzarias): ?>
<br/><br/>
<? echo "<!-- inicio do processamento".date("Y-m-d H:i:s")." -->"; ?>
<div align="center" style='border: 1px black solid;width: 501px;float:left;margin:5px 5px'>
		<h1>Onde Conheceu</h1><h3>(Por cadastros neste periodo)</h3>
		<?	
			$arr_clientes = array();
      $arr_clientes[] = 0;
			$arr_clientes_permitidos = array();
      $arr_clientes_permitidos[] = 0;
			$sql_buscar_clientes_cadastrados = "select c.cod_clientes from ipi_clientes c where c.data_hora_cadastro between '$data_inicial 00:00:00' and '$data_final 23:59:59'";
			//echo "<br/>".$sql_buscar_clientes_cadastrados."<br/>";
			$res_buscar_clientes_cadastrados = mysql_query($sql_buscar_clientes_cadastrados);
			while($obj_buscar_clientes_cadastrados = mysql_fetch_object($res_buscar_clientes_cadastrados))
			{
				$arr_clientes[] = $obj_buscar_clientes_cadastrados->cod_clientes;
			}

			$sql_buscar_clientes_permitidos = "select c.cod_clientes from ipi_clientes c inner join ipi_enderecos e on e.cod_clientes = c.cod_clientes join ipi_cep cep where cep.cep_inicial <= replace(e.cep,'-','') and cep.cep_final >= replace(e.cep,'-','') and cep.cod_pizzarias = '$cod_pizzarias' and c.cod_clientes in (".implode(",",$arr_clientes).")";
			//echo "<br/>".$sql_buscar_clientes_permitidos."<br/>";
			$res_buscar_clientes_permitidos = mysql_query($sql_buscar_clientes_permitidos);
			while($obj_buscar_clientes_permitidos = mysql_fetch_object($res_buscar_clientes_permitidos))
			{
				$arr_clientes_permitidos[] = $obj_buscar_clientes_permitidos->cod_clientes;
			}


			$sql_buscar_bairro_cadastrado = "select count(oc.cod_onde_conheceu) as qtd_conheceu, oc.onde_conheceu from ipi_clientes c inner join ipi_onde_conheceu oc on oc.cod_onde_conheceu = c.cod_onde_conheceu where c.cod_clientes in (".implode(",",$arr_clientes_permitidos).") group by oc.onde_conheceu order by qtd_conheceu DESC";
			

			$res_buscar_bairro_cadastrado = mysql_query($sql_buscar_bairro_cadastrado);
			//echo "<br/>".$sql_buscar_bairro_cadastrado;
			$parametros = '';
			$i = 0;
			$total = 0;
			$tabela = '';
			while($obj_buscar_bairro_cadastrado = mysql_fetch_object($res_buscar_bairro_cadastrado))
			{
				$i++;
				if($i>1)
				{
					$parametros .=',';
				}
				$tabela .= "<tr>";
				$tabela .= "<td>".$obj_buscar_bairro_cadastrado->onde_conheceu."</td>";
				$tabela .= "<td>".$obj_buscar_bairro_cadastrado->qtd_conheceu."</td>";
				$tabela .= "</tr>";

				$parametros .= normalizar_nome_internet($obj_buscar_bairro_cadastrado->onde_conheceu).',';
				$parametros .= $obj_buscar_bairro_cadastrado->qtd_conheceu;
				$total +=$obj_buscar_bairro_cadastrado->qtd_conheceu;
			}
			
		?>
		
		<div id='grfConheceu'></div>
		<script>
		var sabores = new FusionCharts('../lib/swf/fusioncharts/pie3d.swf', 'ONDECONHECEU', 450, 300, 0, 0, 'ffffff', 0);
		sabores.setDataURL('ipi_rel_como_conheceu_ajax.php?param=8,<? echo $i?>,<? echo $parametros; ?>');
		sabores.render('grfConheceu');
		</script>
			<table class="listaEdicao">
				<thead>
					<tr>
					<td>
					Total de Cadastros
					</td>
					<td>
						<? echo $total ?>
					</td>
					</tr>
					<tr>
						<td >
						Onde Conheceu
						</td>
						<td>
						Quantidade
						</td>
					</tr>
				</thead>
				<tbody>
				<? echo $tabela ?>
				</tbody>
			</table>
</div>
<? echo "<!-- fim do primeiro grafico e inicio do segundo ".date("Y-m-d H:i:s")." -->"; ?>
<div align="center" style='border: 1px black solid;width: 501px;float:left;margin:5px 5px'>
                <h1>Onde Conheceu</h1><h3>(Por pedidos neste periodo)</h3>

                <?
                $sql_buscar_bairro_cadastrado = "select count(c.cod_onde_conheceu) as qtd_conheceu, oc.onde_conheceu from ipi_clientes c left join ipi_onde_conheceu oc on oc.cod_onde_conheceu = c.cod_onde_conheceu where c.cod_clientes in (SELECT p.cod_clientes from ipi_pedidos p where p.situacao='BAIXADO' and p.cod_pizzarias = '".$cod_pizzarias."' and data_hora_pedido between '".$data_inicial." 00:00:00' and '".$data_final." 23:59:59') group by oc.onde_conheceu order by qtd_conheceu DESC";
                $res_buscar_bairro_cadastrado = mysql_query($sql_buscar_bairro_cadastrado);
                //echo $sql_buscar_bairro_cadastrado;
                $parametros = '';
                $i = 0;
                $t = 0;
                $t_o = 0;
                $tabela = '';
                $tabela_head = '';
                while($obj_buscar_bairro_cadastrado = mysql_fetch_object($res_buscar_bairro_cadastrado))
                {
                	if($obj_buscar_bairro_cadastrado->onde_conheceu!="")
                	{
                    $i++;
                    if($i>1)
                    {
                      $parametros .=',';
                    }
                    $tabela .= "<tr>";
                    $tabela .= "<td>".$obj_buscar_bairro_cadastrado->onde_conheceu."</td>";
                    $tabela .= "<td>".$obj_buscar_bairro_cadastrado->qtd_conheceu."</td>";
                    $tabela .= "</tr>";

                    $parametros .= normalizar_nome_internet($obj_buscar_bairro_cadastrado->onde_conheceu).',';
                    $parametros .= $obj_buscar_bairro_cadastrado->qtd_conheceu;
                    $t_o+=$obj_buscar_bairro_cadastrado->qtd_conheceu;
                  }
                	$t+=$obj_buscar_bairro_cadastrado->qtd_conheceu;
                }
                        
                ?>

                <div id='grfConheceuPedi'></div>
                <script>
                var sabores2 = new FusionCharts('../lib/swf/fusioncharts/pie3d.swf', 'ONDECONHECEUPED', 450, 300, 0, 0, 'ffffff', 0);
                sabores2.setDataURL('ipi_rel_como_conheceu_ajax.php?param=8,<? echo $i?>,<? echo $parametros; ?>');
                sabores2.render('grfConheceuPedi');
                </script>
                        <table class="listaEdicao">
                        <thead>
                        <tr>
		                      <td width="50%">
		                      Total de clientes: <? echo $t; ?>
		                      </td>
		                      <td  width="50%">
		                      	Total de clientes Com Onde Conheceu: <? echo $t_o; ?>
		                      </td>
                        </tr>
                          <tr>
                              <td >
                              Onde Conheceu
                              </td>
                              <td>
                              Quantidade
                              </td>
                          </tr>
                        </thead>
                        <tbody>
                        <? echo $tabela ?>
                        </tbody>
                </table>
        </div>
<? echo "<!-- fim de tudo ".date("Y-m-d H:i:s")." -->"; ?>
<?endif ; ?>
<?
desconectabd($con);
rodape();
?>
