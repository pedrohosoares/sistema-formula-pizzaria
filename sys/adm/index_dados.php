<?php

/**
 * Resultados das Enquetes (ajax).
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
require_once '../lib/php/formulario.php';
//require_once '../lib/php/sessao.php';

$param = explode(',', validaVarGet('param'));

$tipo_grafico = $param[0];

$conexao = conectabd();

?>

<?php if($tipo_grafico == 1): ?>

<chart caption='Performance' subcaption='(12 semanas)' periodLength='4' highColor='99CC00' lowColor='CC0000' numberPrefix='R$' decimalSeparator=',' thousandSeparator='.'>
	<dataset>
		<set value='3400' />
		<set value='4400' />
		<set value='3400' />
		<set value='7600' />
		<set value='9400' />
		<set value='5800' />
		<set value='2300' />
		<set value='4600' />
		<set value='6500' />
		<set value='6400' />
		<set value='3400' />
		<set value='7600' />
	</dataset>
	<trendlines>
		<line startValue='7000' color='FFCC00'/>
	</trendlines>
</chart>

<?php endif; ?>

<?php desconectabd($conexao); ?>