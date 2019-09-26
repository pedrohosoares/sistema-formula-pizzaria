
<?php

/**
 * Ajax para tela para enviar email para teste de compatibilidade.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       15/08/2012   PEDRO H       Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

$acao = validaVarPost("acao");
$cod_informacoes = validaVarPost("cod_informacoes");

switch ($acao) {
	case 'detalhes':
        
	break;
}
?>
