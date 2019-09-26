<?php

/**
 * ipi_sol_entregas_avulsas.php: Cadastro de Entregas Avulsas
 * 
 * Índice: cod_entregas_avulsas
 * Tabela: ipi_entregas_avulsas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Entregas Avulsas');

$acao = validaVarPost('acao');

$tabela = 'ipi_entregas_avulsas';
$chave_primaria = 'cod_entregas_avulsas';

switch($acao)
{
  case 'editar':

    $cod_pizzarias = validaVarPost('cod_pizzarias');
    $cod_entregadores = validaVarPost('cod_entregadores');
    $data_entrega = validaVarPost('data_entrega');
    $hora_entrega = validaVarPost('hora_entrega');
    $bairro = validaVarPost('bairro');
    $obs_entrega_avulsa = validaVarPost('obs_entrega_avulsa');
    
    $con = conectabd();
    
    $data_hora_entrega = data2bd($data_entrega) . " $hora_entrega:00";
    
    $sql_edicao = sprintf("INSERT INTO $tabela (cod_pizzarias, cod_entregadores, data_hora_entrega, bairro, cidade, estado, obs_entrega_avulsa) VALUES ('%s', '%s', '%s', '%s', 'São José dos Campos', 'SP', '%s')", $cod_pizzarias, $cod_entregadores, $data_hora_entrega, $bairro, $obs_entrega_avulsa);
        
    if(mysql_query($sql_edicao))
    {
        mensagemOk('Registro adicionado com êxito!');
    }
    else
    {
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    
    desconectabd($con);
  break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>

<script type="text/javascript" src="../lib/js/calendario.js"></script>

<script>
function carregar_entregadores()
{
	var url = "acao=carregar_entregadores&cod_pizzarias="+$('cod_pizzarias').value;
  new Request.HTML(
  {
      url: 'ipi_sol_entregas_avulsas_ajax.php',
      update: $('cod_entregadores')
  }).send(url);
}

window.addEvent('domready', function()
{
    new vlaDatePicker('data_entrega', {openWith: 'botao_data_entrega', prefillDate: false});
});
</script>

<form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">


	<tr>
		<td class="legenda tdbl tdbt tdbr"><label class="requerido"	for="cod_pizzarias">Pizzaria</label></td>
	</tr>
  <tr>
    <td class="tdbr tdbl sep">
      <select name="cod_pizzarias" id="cod_pizzarias" onChange="javascript:carregar_entregadores()">
        <option value="">Todas as Pizzarias</option>
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
        ?>
      </select>
    </td>
  </tr>



	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido"	for="cod_entregadores">Entregador</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">

			<select name="cod_entregadores" id="cod_entregadores">
				<option value="">Selecione uma Pizzaria</option>
			</select>

		</td>
	</tr>


	
	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido" for="data_entrega">Data</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr">
      <input class="requerido" type="text" name="data_entrega" id="data_entrega" maxlength="10" size="10"	value="<? echo date('d/m/Y') ?>" onkeypress="return MascaraData(this, event);">
      &nbsp;
      <a href="javascript:;" id="botao_data_entrega"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido"
			for="hora_entrega">Hora</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep"><input class="requerido" type="text"
			name="hora_entrega" id="hora_entrega" maxlength="5" size="5"
			value="<? echo date('H:i') ?>"  onkeypress="return MascaraHora(this, event);"></td>
	</tr>

	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido"
			for="bairro">Bairro</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep"><input class="requerido" type="text"
			name="bairro" id="bairro" maxlength="45" size="53"></td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido"
			for="obs_entrega_avulsa">Observação</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep"><input class="requerido" type="text"
			name="obs_entrega_avulsa" id="obs_entrega_avulsa" maxlength="250" size="53"></td>
	</tr>

	<tr>
		<td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
			class="botao" type="submit" value="Cadastrar"></td>
	</tr>

</table>

<input type="hidden" name="acao" value="editar">
<input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">

</form>

<? rodape(); ?>
