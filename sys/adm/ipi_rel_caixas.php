<?php

/**
 * ipi_fechamento_caixa.php: Fechamento de caixa
 * 
 * Índice: cod_bebidas
 * Tabela: ipi_bebidas
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório dos Fechamentos de Caixa');

$acao = validaVarPost('acao');
$conexao = conectabd();

if(($acao == '') || ($acao == 'escolher_pizzaria')):
?>

<form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
<table align="center" class="caixa" cellpadding="0" cellspacing="0" width="900">
<?
// Busca o último caixa aberto de acordo com as pizzarias do perfil
$sql_buscar_caixas = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) WHERE p.cod_pizzarias = '" . $_SESSION['usuario']['cod_pizzarias'][0] . "' AND c.situacao = 'FECHADO'";
$res_buscar_caixas = mysql_query($sql_buscar_caixas);
$num_buscar_caixas = mysql_num_rows($res_buscar_caixas);

if (($acao == 'escolher_pizzaria') && (validaVarPost('cod_caixa') > 0))
{
  //echo "<br>AKI1";
  $cod_caixa = validaVarPost('cod_caixa');
  
  $sql_buscar_pizzarias = "SELECT p.nome, cf.numero_caixa, u.nome nome_atendente_abertura, u2.nome nome_atendente_fechamento, c.* FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) LEFT JOIN  ipi_caixas_fisicos cf ON (c.cod_caixas_fisicos = cf.cod_caixas_fisicos) LEFT JOIN nuc_usuarios u ON (u.cod_usuarios = c.cod_usuarios_abertura) LEFT JOIN nuc_usuarios u2 ON (u2.cod_usuarios = c.cod_usuarios_fechamento) WHERE c.cod_caixa = '$cod_caixa'";
  $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
  $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
  
  echo '<tr><td class="tdbl tdbt tdbr sep"><font color="red"><b>'.ucfirst(TIPO_EMPRESA).': ' . bd2texto($obj_buscar_pizzarias->nome) . ' - Caixa: '.$obj_buscar_pizzarias->numero_caixa.' ( Abertura ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' - Fechamento ' . bd2datahora($obj_buscar_pizzarias->data_hora_fechamento) . ' )' . '</b></font></td></tr>';
  echo '<input type="hidden" name="cod_caixa" value="' . $cod_caixa . '">';
  echo '<input type="hidden" name="caixa_extenso" id="caixa_extenso" value="Pizzaria: ' . $obj_buscar_pizzarias->cod_pizzarias . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )">';
  
  $cod_pizzarias = $obj_buscar_pizzarias->cod_pizzarias;
  $data_inicial = $obj_buscar_pizzarias->data_hora_abertura;
  $data_final = date('Y-m-d H:i:s');
}
else if (count($_SESSION['usuario']['cod_pizzarias']) > 1)
{
  //echo "<br>AKI2";
  echo '<tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_caixa">'.ucfirst(TIPO_EMPRESAS).'</label></td></tr>';
  echo '<tr><td class="tdbl tdbr sep"><select class="requerido" name="cod_caixa" id="cod_caixa" onchange="javascript: document.frmIncluir.acao.value = \'escolher_pizzaria\'; document.frmIncluir.submit(); ">';
  echo '<option value="0">Escolha a '.TIPO_EMPRESA.'</option>';
  foreach ($_SESSION['usuario']['cod_pizzarias'] as $cod_pizzarias_valor)
  {
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) LEFT JOIN nuc_usuarios u ON (u.cod_usuarios = c.cod_usuarios_abertura) WHERE p.cod_pizzarias = '$cod_pizzarias_valor' AND c.situacao = 'FECHADO'";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
    echo '<option value="' . $obj_buscar_pizzarias->cod_caixa . '">' . $cod_pizzarias_valor . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</option>';
  }
  echo '</select></td></tr>';
}
else if ($num_buscar_caixas >= 1)
{
  //echo "<br>AKI2";
  echo '<tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_caixa">Selecione o Caixa</label></td></tr>';
  echo '<tr><td class="tdbl tdbr tdbb sep"><select class="requerido" name="cod_caixa" id="cod_caixa" onchange="javascript: document.frmIncluir.acao.value = \'escolher_pizzaria\'; document.frmIncluir.submit(); ">';
  
  echo '<option value="0">Escolha o caixa</option>';
  
  foreach ($_SESSION['usuario']['cod_pizzarias'] as $cod_pizzarias_valor)
  {
    $sql_buscar_pizzarias = "SELECT c.cod_caixa, c.data_hora_fechamento, c.data_hora_abertura, cf.numero_caixa, u.nome FROM ipi_caixa c LEFT JOIN  ipi_caixas_fisicos cf ON (c.cod_caixas_fisicos = cf.cod_caixas_fisicos) LEFT JOIN nuc_usuarios u ON (u.cod_usuarios = c.cod_usuarios_abertura) WHERE c.cod_pizzarias = '$cod_pizzarias_valor' AND c.situacao = 'FECHADO' ORDER BY cod_caixa DESC";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    while ($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
    {
        echo '<option value="' . $obj_buscar_pizzarias->cod_caixa . '">Caixa: ' . bd2texto($obj_buscar_pizzarias->numero_caixa)  . ' ( Aberto: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura).' - Fechado: ' . bd2datahora($obj_buscar_pizzarias->data_hora_fechamento). ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' )' . '</option>';
    }
  }
  echo '</select>';
  echo '</td></tr>';
}
else
{
    echo "<br>Nenhum caixa aberto!";
    /*
    //echo "<br>AKI3";
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) WHERE p.cod_pizzarias = '" . $_SESSION['usuario']['cod_pizzarias'][0] . "' AND c.situacao = 'ABERTO'";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
    
    echo '<tr><td class="tdbl tdbt tdbr sep"><font color="red"><b>'.ucfirst(TIPO_EMPRESA).': ' . $_SESSION['usuario']['cod_pizzarias'][0] . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</b></font></td></tr>';
    echo '<input type="hidden" name="cod_caixa" value="' . $obj_buscar_pizzarias->cod_caixa . '">';
    echo '<input type="hidden" name="caixa_extenso" id="caixa_extenso" value="Pizzaria: ' . $obj_buscar_pizzarias->cod_pizzarias . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )">';
    
    $cod_pizzarias = $_SESSION['usuario']['cod_pizzarias'][0];
    $data_inicial = $obj_buscar_pizzarias->data_hora_abertura;
    $data_final = date('Y-m-d H:i:s');
    */
}

if($cod_pizzarias > 0):
?>

<tr>
  <td class="tdbl tdbr set">
  
  <?
  $cod_caixa = validaVarPost('cod_caixa');
  $cod_caixas_fisicos = $obj_buscar_pizzarias->cod_caixas_fisicos;
  //echo "<br>x:: ".$cod_caixas_fisicos;
  // Relatório de formas de pagamento baixados
  $sql_formas_pg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
	$res_formas_pg = mysql_query($sql_formas_pg);
	$num_formas_pg = mysql_num_rows($res_formas_pg);
	
  $rel_fechamento .= '<table class="listaEdicao" cellpadding="0" cellspacing="0" width="500">';

  $rel_fechamento .= '<tr><td colspan="5" style="background-color: #e5e5e5;" align="center"><b>Formas de Pagamento (Baixados)</b></td></tr>';
  $rel_fechamento .= '<tr>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;">&nbsp;</td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Loja</b></td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Internet</b></td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Total</b></td>';
  $rel_fechamento .= '</tr>';
	
  $total_tel = 0;
  $total_net = 0;
  $total_geral = 0;
	for ($a = 0; $a < $num_formas_pg; $a++)
	{
		$obj_formas_pg = mysql_fetch_object($res_formas_pg);
            
		$rel_fechamento .= '<tr>';
		$rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>' . $obj_formas_pg->forma_pg . '</b></td>';

		$objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '".$obj_buscar_pizzarias->data_hora_abertura."' AND '".$obj_buscar_pizzarias->data_hora_fechamento."' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'TEL' AND p.situacao = 'BAIXADO' AND p.cod_pizzarias = $cod_pizzarias AND cod_caixas_fisicos = '".$cod_caixas_fisicos."'", $conexao);
		$soma_tel = $objBuscaPedidosSoma->soma_tel;
    $total_tel += $soma_tel;
		$rel_fechamento .= '<td align="center">' . bd2moeda($soma_tel) . '</td>';
                
		$objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_net FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.data_hora_pedido BETWEEN '".$obj_buscar_pizzarias->data_hora_abertura."' AND '".$obj_buscar_pizzarias->data_hora_fechamento."' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'NET' AND p.situacao = 'BAIXADO' AND p.cod_pizzarias = $cod_pizzarias AND cod_caixas_fisicos = '".$cod_caixas_fisicos."'", $conexao);
		$soma_net = $objBuscaPedidosSoma->soma_net;
    $total_net += $soma_net;
		$rel_fechamento .= '<td align="center">' . bd2moeda($soma_net) . '</td>';
                
		$rel_fechamento .= '<td align="center"><b>' . bd2moeda($soma_tel + $soma_net + $soma_mesa) . '</b></td>';
		$rel_fechamento .= '</tr>';
    $total_geral += $soma_tel + $soma_net;
	}

  $rel_fechamento .= '<tr>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Total</b></td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>'.bd2moeda($total_tel).'</b></td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>'.bd2moeda($total_net).'</b></td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>'.bd2moeda($total_geral).'</b></td>';
  $rel_fechamento .= '</tr>';

  $rel_fechamento .= '</table><br>';

  // Relatório de formas de pagamento débito
  $res_formas_pg = mysql_query($sql_formas_pg);
  $num_formas_pg = mysql_num_rows($res_formas_pg);

  $rel_fechamento .= '<table class="listaEdicao" cellpadding="0" cellspacing="0" width="500">';
  $rel_fechamento .= '<tr><td colspan="5" style="background-color: #e5e5e5;" align="center"><b>Formas de Pagamento (Débito)</b></td></tr>';
  $rel_fechamento .= '<tr>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;">&nbsp;</td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Loja</b></td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Internet</b></td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>Total</b></td>';
  $rel_fechamento .= '</tr>';
	
  $total_tel = 0;
  $total_net = 0;
  $total_geral = 0;
	for ($a = 0; $a < $num_formas_pg; $a++)
	{
		$obj_formas_pg = mysql_fetch_object($res_formas_pg);
            
		$rel_fechamento .= '<tr>';
		$rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>' . $obj_formas_pg->forma_pg . '</b></td>';

		$objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '".$obj_buscar_pizzarias->data_hora_abertura."' AND '".$obj_buscar_pizzarias->data_hora_fechamento."' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'TEL' AND p.situacao NOT IN ('BAIXADO', 'CANCELADO') AND p.cod_pizzarias = $cod_pizzarias AND cod_caixas_fisicos = '".$cod_caixas_fisicos."'", $conexao);
		$soma_tel = $objBuscaPedidosSoma->soma_tel;
    $total_tel += $soma_tel;
		$rel_fechamento .= '<td align="center">' . bd2moeda($soma_tel) . '</td>';
                
		$objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfg.valor) AS soma_net FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfg ON (pfg.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '".$obj_buscar_pizzarias->data_hora_abertura."' AND '".$obj_buscar_pizzarias->data_hora_fechamento."' AND pfg.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'NET' AND p.situacao NOT IN ('BAIXADO', 'CANCELADO') AND p.cod_pizzarias = $cod_pizzarias AND cod_caixas_fisicos = '".$cod_caixas_fisicos."'", $conexao);
		$soma_net = $objBuscaPedidosSoma->soma_net;
    $total_net += $soma_net;
		$rel_fechamento .= '<td align="center">' . bd2moeda($soma_net) . '</td>';
                
		$rel_fechamento .= '<td align="center"><b>' . bd2moeda($soma_tel + $soma_net + $soma_mesa) . '</b></td>';
		$rel_fechamento .= '</tr>';
    $total_geral += $soma_tel + $soma_net;
	}

  $rel_fechamento .= '<tr>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center"><b>Total</b></td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>'.bd2moeda($total_tel).'</b></td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>'.bd2moeda($total_net).'</b></td>';
  $rel_fechamento .= '<td style="background-color: #e5e5e5;" align="center" width="100"><b>'.bd2moeda($total_geral).'</b></td>';
  $rel_fechamento .= '</tr>';

             
  $rel_fechamento .= '</table><br>';
  echo $rel_fechamento;
  ?>
  
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr">
    <strong>Número do Caixa:</strong>
  </td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <?php echo $obj_buscar_pizzarias->numero_caixa; ?>
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr">
    <strong>Atendente Abertura do Caixa:</strong>
  </td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <?php echo $obj_buscar_pizzarias->nome_atendente_abertura; ?>
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr">
    <strong>Atendente Fechamento do Caixa:</strong>
  </td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <?php echo $obj_buscar_pizzarias->nome_atendente_fechamento; ?>
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr">
    <strong>Observação de abertura do caixa:</strong>
  </td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <?php echo $obj_buscar_pizzarias->obs_caixa_abertura; ?>
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr">
    <strong>Ocorreu erro no atendimento?</strong>
  </td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <?php echo $obj_buscar_pizzarias->erro_atendimento; ?>
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr">
    <strong>Ocorreu erro na cozinha?</strong>
  </td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <?php echo $obj_buscar_pizzarias->erro_cozinha; ?>
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr">
    <strong>Ocorreu erro na entrega/motoboy?</strong>
  </td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <?php echo $obj_buscar_pizzarias->erro_motoboy; ?>
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr">
    <strong>Ocorreu erro no sistema?</strong>
  </td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <?php echo $obj_buscar_pizzarias->erro_sistema; ?>
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr">
    <strong>Contagem do Caixa</strong>
  </td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    R$ <?php echo bd2moeda($obj_buscar_pizzarias->contagem_caixa); ?>
	</td>
</tr>

<tr>
  <td class="legenda tdbl tdbr">
    <strong>Observações Gerais</strong>
  </td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <?php echo $obj_buscar_pizzarias->obs_caixa; ?>
  </td>
</tr>

<? endif; ?>

</table>
<input type="hidden" name="acao" value="">
</form>

<? 
if (validaVarPost('cod_caixa') > 0)
  echo '<a href="'.$PHP_SELF.'">Voltar</a>';

desconectabd($conexao);

endif;
rodape(); 

?>
