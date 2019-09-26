<?php

/**
 * ipi_mensagem_pizzaria.php: Envio de mensagem para pizzaria
 * 
 * Índice: cod_mensagem_pizzarias
 * Tabela: ipi_mensagem_pizzarias
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Enviar Mensagem à Pizzaria');

$acao = validaVarPost('acao');

switch ($acao)
{
    case 'enviar':
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $data = data2bd(validaVarPost('data'));
        $hora = validaVarPost('hora');
        $mensagem_pizzaria = validaVarPost('mensagem_pizzaria');
        
        $conexao = conectabd();
        
        if($cod_pizzarias == 'TODAS')
        {
            $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
            $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
            
            
            while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
            {
                $arr_cod_pizzarias[] = $obj_buscar_pizzarias->cod_pizzarias;
            }
        }
        else
        {
            $arr_cod_pizzarias[] = $cod_pizzarias;
        }
        
        $res_inserir_mensagem = true;
        
        foreach($arr_cod_pizzarias as $cor_arr_cod_pizzarias)
        {
        
            $sql_inserir_mensagem = sprintf("INSERT INTO ipi_mensagem_pizzarias (cod_pizzarias, cod_usuarios, mensagem_pizzaria, data_hora_exibicao) VALUE ('%s', '%s', '%s', '%s')",
                                    $cor_arr_cod_pizzarias, $_SESSION['usuario']['codigo'], texto2bd($mensagem_pizzaria), "$data $hora:00");
                                    
            $res_inserir_mensagem &= mysql_query($sql_inserir_mensagem);

        }
        
        if($res_inserir_mensagem)
        {
            mensagemOk('Registro adicionado com êxito!');
        }
        else
        {
            mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        
        desconectabd($conexao);
        
        break;
}

?>

<form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
<table align="center" class="caixa" cellpadding="0" cellspacing="0">



<tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_caixa"><? echo ucfirst(TIPO_EMPRESA)?></label></td></tr>
<tr><td class="tdbl tdbr sep">

	<select class="requerido" name="cod_pizzarias" id="cod_pizzarias">
		<option value=""></option>
		
		<?

		if(count($_SESSION['usuario']['cod_pizzarias']) > 0)
		{
		    echo '<option value="TODAS">Todas</option>';
		}
		
		$conexao = conectabd();
		
		$sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
        $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
        
        while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
        {
            echo '<option value="' . $obj_buscar_pizzarias->cod_pizzarias . '">' . bd2texto($obj_buscar_pizzarias->nome) . '</option>';
        }
		
        desconectabd($conexao);
        
		?>
    
    </select>
</td></tr>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="data">Data</label></td>
</tr>
<tr>
    <td class="tdbl tdbr"><input type="text" class="requerido" name="data" id="data" maxlength="10" size="10" onkeypress="return MascaraData(this, event);" value="<? echo date('d/m/Y') ?>"></td>
</tr>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="hora">Hora</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep"><input type="text" class="requerido" name="hora" id="hora" maxlength="5" size="5" onkeypress="return MascaraHora(this, event);" value="<? echo date('H:i') ?>"></td>
</tr>

<tr>
    <td class="legenda tdbl tdbr"><label class="requerido" for="mensagem_pizzaria">Mensagem</label></td>
</tr>
<tr>
    <td class="tdbl tdbr sep"><textarea rows="10" cols="100" id="mensagem_pizzaria" name="mensagem_pizzaria"></textarea><br />Limite de 250 caracteres.</td>
</tr>

<tr>
    <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
        class="botao" type="submit" value="Enviar Mensagem"></td>
</tr>

</table>

<input type="hidden" name="acao" value="enviar"></form>

<? 
rodape(); 

?>