<?php

/**
 * Corretor de codigo para email mkt.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       04/01/2013   FILIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once'../../classe/corretor_html.php';

cabecalho('Gerador de código email marketing');

$acao = validaVarPost('acao');

switch ($acao) {
	case 'corrigir':
		//$codigo_antes = validaVarPost("codigo_antes");
		$diretorio = validaVarPost("diretorio");
		$codigo_antes = $_POST['codigo_antes'];
		$a = new HtmlFixer();
		$a->setar_diretorio_imagems($diretorio);
		$codigo_corrigido = $a->getFixedHtml($codigo_antes);
		$codigo_corrigido = "<table".$a->chars_entre("<table","</table>",$codigo_corrigido)."</table>";
	break;
	
}
//$tabela = 'ipi_pizzarias_estatisticas';
//$chave_primaria = 'cod_pizzarias_estatisticas';
?>
<style type="text/css">
	#conteudo
	{
		text-align: center;
	}
</style>
<form name="formCorrigir" method="post">

	<label for="diretorio">Diretório das imagems</label><br/>

	<input type="text" name="diretorio" id="diretorio" size="50" value="<? echo ($diretorio=="" ? "http://" : $diretorio) ; ?>" /><br/><br/>

	<label for="codigo_antes">Código para ser corrigido </label><br/>
	<textarea id="codigo_antes" name="codigo_antes" rows="15" cols="80"><? echo $codigo_antes ?></textarea><br/><br/>
	<input type="submit" value="Corrigir Código"/>

	<br/><br/>
	<label for="codigo_corrigido">Código Corrigido</label><br/>
	<textarea id="codigo_corrigido" rows="15" cols="80"><? echo $codigo_corrigido; ?></textarea>

	<input type="hidden" name="acao" value="corrigir"/>
</form>
<?php rodape(); ?>