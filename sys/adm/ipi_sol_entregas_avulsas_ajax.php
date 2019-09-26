<?php
require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');

switch ($acao)
{
    case 'carregar_entregadores':

        $cod_pizzarias = validaVarPost('cod_pizzarias');

        $con = conectabd();
        echo '<option value=""></option>';
				$sql_buscar_entregadores = "SELECT e.* FROM ipi_entregadores e WHERE e.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND e.cod_pizzarias = '".$cod_pizzarias."' ORDER BY e.numero_cadastro, e.nome";
        //echo "<option>".$sql_buscar_entregadores."</option>";
				$res_buscar_entregadores = mysql_query($sql_buscar_entregadores);
				
				while($obj_buscar_entregadores = mysql_fetch_object($res_buscar_entregadores))
				{
				    echo utf8_encode('<option value="' . $obj_buscar_entregadores->cod_entregadores . '">' . bd2texto($obj_buscar_entregadores->numero_cadastro) . ' - ' . bd2texto($obj_buscar_entregadores->nome) . '</option>');
				}
				desconectabd($con);

    break;
}
?>
