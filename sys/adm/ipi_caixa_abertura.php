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

cabecalho('Fechamento de Caixa');

$acao = validaVarPost('acao');

switch ($acao)
{
    case 'abrir_caixa':
      $cod_caixas_fisicos = validaVarPost('cod_caixas_fisicos');
      $obs_caixa_abertura = validaVarPost('obs_caixa_abertura');
      $cod_pizzarias = validaVarPost('cod_pizzarias');

      $conexao = conectabd();
      require("../../pub_req_fuso_horario1.php");
   
      $sql_abrir_caixa = sprintf("INSERT INTO ipi_caixa (cod_pizzarias, cod_caixas_fisicos, cod_usuarios_abertura, cod_usuarios_fechamento, data_hora_abertura, obs_caixa_abertura, situacao) VALUES ('%s', '%s', '%s', '%s', NOW(), '%s', 'ABERTO')", $cod_pizzarias, $cod_caixas_fisicos, $_SESSION['usuario']['codigo'], $_SESSION['usuario']['codigo'], $obs_caixa_abertura);
      $res_abrir_caixa = mysql_query($sql_abrir_caixa);
      //echo $sql_abrir_caixa;
      if ($res_abrir_caixa)
      {
        echo "Caixa ABERTO com sucesso!";
      }
      else
      {
        echo "Erro ao abrir caixa!";
      }

      desconectabd($conexao);
    break;
}

if(($acao == '') || ($acao == 'escolher_pizzaria')):

$conexao = conectabd();

?>

<script>
function abrir_caixa()
{
	if(validaRequeridos(document.frmIncluir))
	{
		if(confirm('Confirma abertura do caixa ' + $("cod_caixas_fisicos").getSelected().get("text") + '?' )) 
		{ 
			$('botao_submit').disabled = true;
			$('barra_loader').setStyle("display","block");
			$('botao_submit').setStyle("backgroundColor","#CCCCCC");
			$('botao_submit').setStyle("border-color","#999999");
			document.frmIncluir.submit();
		} 
		else
		{
			$('botao_submit').disabled = false;
			$('barra_loader').setStyle("display","none");
			$('botao_submit').setStyle("backgroundColor","#EB8612");
			$('botao_submit').setStyle("border-color","#D44E08");
		}
	}
}
</script>

<form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
<table align="center" class="caixa" cellpadding="0" cellspacing="0">

<?

// Busca o último caixa aberto de acordo com as pizzarias do perfil

if (($acao == 'escolher_pizzaria') && (validaVarPost('cod_caixa') > 0))
{
    //echo "<br>AKI1";
    $cod_caixa = validaVarPost('cod_caixa');
    
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) WHERE c.cod_caixa = '$cod_caixa'";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
    
    echo '<tr><td class="tdbl tdbt tdbr sep"><font color="red"><b>'.ucfirst(TIPO_EMPRESA).': ' . $obj_buscar_pizzarias->cod_pizzarias . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</b></font></td></tr>';
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
        $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_caixa c ON (p.cod_pizzarias = c.cod_pizzarias) WHERE p.cod_pizzarias = '$cod_pizzarias_valor' AND c.situacao = 'ABERTO'";
        $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
        $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
        
        echo '<option value="' . $obj_buscar_pizzarias->cod_caixa . '">' . $cod_pizzarias_valor . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )' . '</option>';
    }
    
    echo '</select></td></tr>';
}
else
{
    //echo "<br>AKI3";
    $sql_buscar_pizzarias = "SELECT p.nome FROM ipi_pizzarias p WHERE p.cod_pizzarias = '" . $_SESSION['usuario']['cod_pizzarias'][0] . "'";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias);
    
    echo '<tr><td class="tdbl tdbt tdbr sep"><font color="red"><b>'.ucfirst(TIPO_EMPRESA).': ' . $_SESSION['usuario']['cod_pizzarias'][0] . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . '</b></font></td></tr>';
    echo '<input type="hidden" name="cod_caixa" value="' . $obj_buscar_pizzarias->cod_caixa . '">';
    echo '<input type="hidden" name="caixa_extenso" id="caixa_extenso" value="Pizzaria: ' . $obj_buscar_pizzarias->cod_pizzarias . ' - ' . bd2texto($obj_buscar_pizzarias->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_pizzarias->data_hora_abertura) . ' )">';
    
    $cod_pizzarias = $_SESSION['usuario']['cod_pizzarias'][0];
    $data_inicial = $obj_buscar_pizzarias->data_hora_abertura;
    $data_final = date('Y-m-d H:i:s');
}

if($cod_pizzarias > 0):
?>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="cod_caixas_fisicos">Qual caixa você está?</label></td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <select class="requerido" name="cod_caixas_fisicos" id="cod_caixas_fisicos">
    <?
    $sql_caixas = "SELECT * FROM ipi_caixas_fisicos cf WHERE cf.cod_pizzarias = '".$cod_pizzarias."' AND cod_caixas_fisicos NOT IN (SELECT cod_caixas_fisicos FROM ipi_caixa WHERE cod_pizzarias = '".$cod_pizzarias."' AND situacao = 'ABERTO') ";
    $res_caixas = mysql_query($sql_caixas);
    $num_caixas = mysql_num_rows($res_caixas);

    if ($num_caixas>0)
    {
      echo '<option value=""></option>';
      while($obj_caixas = mysql_fetch_object($res_caixas))
      {
        echo "<option value='".$obj_caixas->cod_caixas_fisicos."'>".$obj_caixas->numero_caixa."</option>";
      }
    }
    else
    {
      echo '<option value="">Todos caixas cadastrados já estão ABERTOS!</option>';
    }
    ?>
    </select>
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label  for="obs_caixa_abertura">Observações Gerais</label></td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <textarea rows="10" cols="100" id="obs_caixa_abertura" name="obs_caixa_abertura"></textarea>
  </td>
</tr>

<tr>
  <td align="center" class="tdbl tdbb tdbr">
    <div id="barra_loader" style="display: none" align="center">
      Processando...<br />
      <img src="../lib/img/principal/ajax_loader_barra.gif" />
    </div>
    <input name="botao_submit" id="botao_submit" class="botao" type="button" value="Abrir Caixa" onclick="javascript:abrir_caixa();">
  </td>
</tr>

<? else: ?>

<tr>
    <td align="center" class="tdbl tdbb tdbr">&nbsp;</td>
</tr>

<? endif; ?>

</table>

<input type="hidden" name="acao" value="abrir_caixa">
<input type="hidden" name="cod_pizzarias" value="<?php echo $cod_pizzarias;?>">

</form>

<? 

desconectabd($conexao);

endif;

rodape(); 

?>
