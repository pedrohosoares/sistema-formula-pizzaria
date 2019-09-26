<?php

/**
 * ipi_sol_impressao_relatorio.php: Impressão de Relatórios
 * 
 * Índice: cod_bebidas
 * Tabela: ipi_bebidas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Impressão de Relatórios');

$acao = validaVarPost('acao');

switch($acao) {
  case 'editar':
    $cod_caixa = validaVarPost('cod_caixa');
    
    $cod_entregadores = validaVarPost('cod_entregadores');
    $data_inicial_entregador = validaVarPost('data_inicial_entregador');
    $data_final_entregador = validaVarPost('data_final_entregador');
    $valor_diaria = validaVarPost("diaria");
    $con = conectabd();
    
    $res_inserir_caixa = true;
    if($cod_caixa > 0)
    {
	    $obj_buscar_caixa = executaBuscaSimples("SELECT * FROM ipi_caixa WHERE cod_caixa = '$cod_caixa'", $con);
	    
	    $sql_inserir_caixa = sprintf("INSERT ipi_impressao_relatorio (cod_caixa, cod_usuarios, cod_pizzarias, relatorio, situacao) VALUES ('%s', '%s', '%s', 'CAIXA', 'NOVO')", $cod_caixa, $_SESSION['usuario']['codigo'], $obj_buscar_caixa->cod_pizzarias);
	    $res_inserir_caixa = mysql_query($sql_inserir_caixa);
    }
    
    
    $res_inserir_entregador = true;
    if($cod_entregadores > 0)
    {
		$obj_buscar_entregador = executaBuscaSimples("SELECT * FROM ipi_entregadores WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND cod_entregadores = '$cod_entregadores'", $con);
		
		$sql_inserir_entregador = sprintf("INSERT ipi_impressao_relatorio (cod_usuarios, cod_pizzarias, cod_entregadores, relatorio, data_hora_inicial, data_hora_final,valor_extra,situacao) VALUES ('%s', '%s', '%s', 'ENTREGADOR', '%s 00:00:00', '%s 23:59:59','%s', 'NOVO')", $_SESSION['usuario']['codigo'], $obj_buscar_entregador->cod_pizzarias, $obj_buscar_entregador->cod_entregadores, data2bd($data_inicial_entregador), data2bd($data_final_entregador),$valor_diaria);
	    $res_inserir_entregador = mysql_query($sql_inserir_entregador);
    }
    
    if ($res_inserir_caixa && $res_inserir_entregador)
    {
        mensagemOk('A solicitação de impressão foi realizada com sucesso!');
    }
    else
    {
        mensagemErro('Erro ao solicitar a impressão.', 'Por favor, comunique a equipe de suporte.');
    }
    
    desconectabd($con);
  break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script language="javascript" src="../lib/js/calendario.js"></script>

<script>

window.addEvent('domready', function(){
	var tabs = new Tabs('tabs');
	
	// DatePick
  	new vlaDatePicker('data_inicial_entregador', {openWith: 'botao_data_inicial_entregador', prefillDate: false});
  	new vlaDatePicker('data_final_entregador', {openWith: 'botao_data_final_entregador', prefillDate: false});
});

</script>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a href="javascript:;">Pendentes</a></li>
       <li><a href="javascript:;">Nova Impressão</a></li>
    </ul>
  </div>
    
  <!-- Tab Editar -->
  <div class="painelTab">
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <td align="center" width="120">Relatório</td>
              <td align="center" width="120">Data Hora Inicial</td>
              <td align="center" width="120">Data Hora Final</td>
              <td align="center"><? echo ucfirst(TIPO_EMPRESA)?></td>
              <td align="center">Entregador</td>
              <td align="center" width="200">Solicitante</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaRegistros = "SELECT *, p.nome AS nome_pizzaria, e.nome AS nome_entregador FROM ipi_impressao_relatorio r INNER JOIN nuc_usuarios u ON (r.cod_usuarios = u.cod_usuarios) INNER JOIN ipi_pizzarias p ON (r.cod_pizzarias = p.cod_pizzarias) LEFT JOIN ipi_entregadores e ON (e.cod_entregadores = r.cod_entregadores) WHERE r.situacao = 'NOVO' AND r.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY cod_impressao_relatorio";
          $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            echo '<td align="center">'.bd2texto($objBuscaRegistros->relatorio).'</td>';
            
            if($objBuscaRegistros->relatorio == 'CAIXA')
            {
                $obj_buscar_caixa = executaBuscaSimples("SELECT * FROM ipi_caixa c INNER JOIN ipi_pizzarias p ON (c.cod_pizzarias = p.cod_pizzarias) WHERE c.cod_caixa = '" . $objBuscaRegistros->cod_caixa . "'", $con);
                
                echo '<td align="center">'.bd2datahora($obj_buscar_caixa->data_hora_abertura).'</td>';
                echo '<td align="center">'.bd2datahora($obj_buscar_caixa->data_hora_fechamento).'</td>';
            }
            else
            {
                echo '<td align="center">'.bd2datahora($objBuscaRegistros->data_hora_inicial).'</td>';
                echo '<td align="center">'.bd2datahora($objBuscaRegistros->data_hora_final).'</td>';
            }
            
            echo '<td align="center">'.bd2texto($objBuscaRegistros->nome_pizzaria).'</td>';
            echo '<td align="center">'.bd2texto($objBuscaRegistros->nome_entregador).'</td>';
            
            echo '<td align="center">'.bd2texto($objBuscaRegistros->usuario).'</td>';
            
            echo '</tr>';
          }
          
          desconectabd($con);
          
          ?>
          
          </tbody>
        </table>
  </div>
  <!-- Tab Editar -->
  
  <!-- Tab Incluir -->
  <div class="painelTab">
    <? 
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    
    if($codigo > 0) {
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    } 
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="tdbl tdbt tdbr sep"><label>Relatório de Caixa</label><br><hr color="#1A498F" size="1" noshade="noshade"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="cod_caixa">Caixa</label></td></tr>
    <tr><td class="tdbl tdbr sep">
		<select name="cod_caixa" style="width: 450px;">
			<option value=""></option>
			
			<?

			$con = conectabd();
			
			$sql_buscar_caixa = "SELECT c.*, p.nome FROM ipi_caixa c INNER JOIN ipi_pizzarias p ON (c.cod_pizzarias = p.cod_pizzarias) WHERE c.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY cod_caixa DESC";
			$res_buscar_caixa = mysql_query($sql_buscar_caixa);
			
			while($obj_buscar_caixa = mysql_fetch_object($res_buscar_caixa))
			{
			    echo '<option value="' . $obj_buscar_caixa->cod_caixa . '">' . 'Caixa: ' . $obj_buscar_caixa->cod_caixa . ' - ' . $obj_buscar_caixa->cod_pizzarias . ' - ' . bd2texto($obj_buscar_caixa->nome) . ' ( Abertura de caixa: ' . bd2datahora($obj_buscar_caixa->data_hora_abertura) . ' - Fechamento de caixa: ' . bd2datahora($obj_buscar_caixa->data_hora_fechamento) . ' | ' . $obj_buscar_caixa->situacao . ' )' . '</option>';
			}
			
			desconectabd($con);
			
			?>
		</select>
	</td></tr>
    
    <tr><td class="tdbl tdbr sep"><label>Relatório de Entregadores</label><br><hr color="#1A498F" size="1" noshade="noshade"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="cod_entregadores">Entregador</label></td></tr>
    <tr><td class="tdbl tdbr sep">
		<select name="cod_entregadores" style="width: 450px;">
			<option value=""></option>
			
			<?

			$con = conectabd();
			
			$sql_buscar_entregador = "SELECT e.*, p.nome AS nome_pizzaria FROM ipi_entregadores e INNER JOIN ipi_pizzarias p ON (e.cod_pizzarias = p.cod_pizzarias) WHERE e.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY p.nome, e.nome";
			$res_buscar_entregador = mysql_query($sql_buscar_entregador);
			
			while($obj_buscar_entregador = mysql_fetch_object($res_buscar_entregador))
			{
			    echo '<option value="' . $obj_buscar_entregador->cod_entregadores . '">' . bd2texto($obj_buscar_entregador->nome_pizzaria) . ' - ' . bd2texto($obj_buscar_entregador->nome) . '</option>';
			}
			
			desconectabd($con);
			
			?>
		</select>
	</td></tr>
	
		<tr><td class="legenda tdbl tdbr"><label for="diaria">Comissão / Diária / Desconto</label></td></tr>
    <tr><td class="tdbl tdbr">
		<input type="text" name="diaria" id="diaria" size="6" onkeypress="return formataMoeda(this, '.', ',', event)">
 
	</td></tr>
	
	<tr><td class="legenda tdbl tdbr"><label for="data_inicial_entregador">Data Inicial</label></td></tr>
    <tr><td class="tdbl tdbr">
		<input type="text" name="data_inicial_entregador" id="data_inicial_entregador" size="12" value="<? echo $data_inicial ?>" onkeypress="return MascaraData(this, event)">
    	&nbsp;
    	<a href="javascript:;" id="botao_data_inicial_entregador"><img src="../lib/img/principal/botao-data.gif"></a>
	</td></tr>
	
	<tr><td class="legenda tdbl tdbr"><label for="data_final_entregador">Data Final</label></td></tr>
    <tr><td class="tdbl tdbr sep">
		<input type="text" name="data_final_entregador" id="data_final_entregador" size="12" value="<? echo $data_final ?>" onkeypress="return MascaraData(this, event)">
    	&nbsp;
    	<a href="javascript:;" id="botao_data_final_entregador"><img src="../lib/img/principal/botao-data.gif"></a>
	</td></tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Solicitar Impressão"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
</div>

<? rodape(); ?>
