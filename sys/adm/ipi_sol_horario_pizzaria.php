<?php

/**
 * ipi_sol_horario_pizzaria.php: Cadastro de Pizzarias
 * 
 * Índice: cod_pizzarias
 * Tabela: ipi_pizzarias
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Alteração de Tempo de Entrega');

$acao = validaVarPost('acao');

$tabela = 'ipi_pizzarias';
$chave_primaria = 'cod_pizzarias';

$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');

switch ($acao)
{
    case 'editar' :
        $tempo_entrega = validaVarPost('tempo_entrega');
        $cod_pizzarias_horarios = validaVarPost('cod_pizzarias_horarios');

        $con = conectabd();
        
        $resEdicaoTempos = true;
        
        for($c = 0; $c < count($cod_pizzarias_horarios); $c++)
        {
            if($cod_pizzarias_horarios[$c] > 0)
            {
                // Buscando se é tempo máximo
                $sql_buscar_tempo = "SELECT * FROM ipi_pizzarias_horarios WHERE cod_pizzarias_horarios = " . $cod_pizzarias_horarios[$c];
                $res_buscar_tempo =  mysql_query($sql_buscar_tempo);
                $obj_buscar_tempo = mysql_fetch_object($res_buscar_tempo);
                
                if($obj_buscar_tempo->tempo_entrega != $tempo_entrega[$c])
                {
                    $sql_inserir_log = sprintf("INSERT INTO ipi_pizzarias_horarios_log (cod_pizzarias_horarios, cod_pizzarias, cod_usuarios, data_hora_alteracao, tempo_entrega) VALUES ('%s', '%s', '%s', NOW(), '%s')", $cod_pizzarias_horarios[$c], $obj_buscar_tempo->cod_pizzarias, $_SESSION['usuario']['codigo'], $tempo_entrega[$c]);
                    $res_inserir_log = mysql_query($sql_inserir_log);
                }
                
                if($obj_buscar_tempo->tempo_entrega_max < $tempo_entrega[$c])
                {
                    $SqlEdicaoTempos = sprintf("UPDATE ipi_pizzarias_horarios SET tempo_entrega_max = '%s' WHERE cod_pizzarias_horarios = " . $cod_pizzarias_horarios[$c], $tempo_entrega[$c]);
                    $resEdicaoTempos &= mysql_query($SqlEdicaoTempos);
                }
                
                $SqlEdicaoTempos = sprintf("UPDATE ipi_pizzarias_horarios SET tempo_entrega = '%s' WHERE cod_pizzarias_horarios = " . $cod_pizzarias_horarios[$c], $tempo_entrega[$c]);
                $resEdicaoTempos &= mysql_query($SqlEdicaoTempos);
            }
        }
        
        if ($resEdicaoTempos)
        {
            mensagemOk('Registro adicionado com êxito!');
        } 
        else
        {
            mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        
        desconectabd($con);
        break;
}

?>

<form name="frmIncluir" method="post">

<?

$con = conectabd();

?>

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

	<?
	$SqlBuscaRegistros = "SELECT * FROM $tabela WHERE $chave_primaria IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";
    $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
	
    $primeira_linha = true;
    
	while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)):
	
    $codigo = $objBuscaRegistros->$chave_primaria;

	?>

	<tr>
		<td class="tdbl tdbr <? echo ($primeira_linha) ? 'tdbt' : ''; ?> sep"><label><? echo ucfirst(TIPO_EMPRESA)?>: <? echo bd2texto($objBuscaRegistros->nome) ?></label><hr color="#1A498F" size="1" noshade="noshade"></td>
	</tr>

	<tr>
		<td class="tdbl tdbr sep">
		
		<table class="listaEdicao" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<td align="center"><label>Horário Inicial</label></td>
					<td align="center"><label>Horário Final</label></td>
					
					<? for($s = 0; $s < 7; $s++): ?>
					
					<td align="center" width="50"><label>Tempo de Entrega <font color="red"><? echo $dia_semana[$s] ?></font></label></td>
					
					<? endfor; ?>
				</tr>
			</thead>
			<tbody>
      
            <?
            
            $sqlBuscaHorarios = "SELECT DISTINCT horario_inicial_entrega, horario_final_entrega FROM ipi_pizzarias_horarios WHERE $chave_primaria = $codigo ORDER BY horario_inicial_entrega";
            $resBuscaHorarios = mysql_query($sqlBuscaHorarios);
            
            while ( $objBuscaHorarios = mysql_fetch_object($resBuscaHorarios))
            {
                echo '<tr>';
                echo '<td align="center">'.$objBuscaHorarios->horario_inicial_entrega.'</td>';
                echo '<td align="center">'.$objBuscaHorarios->horario_final_entrega.'</td>';
                
                for($s = 0; $s < 7; $s++)
                {
                    $str_buscar_horario_dia_semana = "SELECT * FROM ipi_pizzarias_horarios WHERE $chave_primaria = $codigo AND horario_inicial_entrega = '" . $objBuscaHorarios->horario_inicial_entrega . "' AND horario_final_entrega = '" . $objBuscaHorarios->horario_final_entrega . "' AND dia_semana = '$s' ORDER BY horario_inicial_entrega";
                    $res_buscar_horario_dia_semana = mysql_query($str_buscar_horario_dia_semana);
                    $obj_buscar_horario_dia_semana = mysql_fetch_object($res_buscar_horario_dia_semana);
                    
                    echo '<td align="center"><input type="text" name="tempo_entrega[]" maxsize="2" size="2" value="' . $obj_buscar_horario_dia_semana->tempo_entrega . '" onkeypress="return ApenasNumero(event);"></td>';
                    
                    echo '<input type="hidden" name="cod_pizzarias_horarios[]" value="' . $obj_buscar_horario_dia_semana->cod_pizzarias_horarios . '">';
                }
                
                echo '</tr>';
            }
            
            ?>
        
        	</tbody>
		</table>
		</td>
	</tr>

	<tr>
		<td align="center" class="tdbl tdbr"><br><br><br></td>
	</tr>

	<? 
	
	$primeira_linha = false;
	
	endwhile; 
	
	?>

	<tr>
		<td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
			class="botao" type="submit" value="Alterar"></td>
	</tr>

</table>

<input type="hidden" name="acao" value="editar">

</form>

<?

desconectabd($con);

rodape();
?>