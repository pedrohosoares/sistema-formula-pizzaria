<?

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

function montar_caminho_plano_contas($cod_plano_contas, $arr_caminho)
{
    $sql_buscar_plano_conta_pai = "SELECT * FROM ipi_plano_contas WHERE cod_plano_contas = '$cod_plano_contas'";
    $res_buscar_plano_conta_pai = mysql_query($sql_buscar_plano_conta_pai);
    $obj_buscar_plano_conta_pai = mysql_fetch_object($res_buscar_plano_conta_pai);
    
    $arr_caminho[] = utf8_encode($obj_buscar_plano_conta_pai->conta_indice . ' - ' . $obj_buscar_plano_conta_pai->conta_nome);
    
    return ($obj_buscar_plano_conta_pai->cod_plano_contas_pai == 0) ? array_reverse($arr_caminho) : montar_caminho_plano_contas($obj_buscar_plano_conta_pai->cod_plano_contas_pai, $arr_caminho);
}

$conta_indice = validaVarPost('conta_indice');
$cod_plano_contas = validaVarPost('cod_plano_contas');

if($conta_indice[count($conta_indice)] == '.')
{
    $conta_indice = substr($conta_indice, 0, count($conta_indice) - 2); 
}

$conexao = conectabd();

if($cod_plano_contas > 0)
{
    $sql_buscar_plano_contas_edicao = "SELECT * FROM ipi_plano_contas WHERE cod_plano_contas = '$cod_plano_contas'";
    $res_buscar_plano_contas_edicao = mysql_query($sql_buscar_plano_contas_edicao);
    $obj_buscar_plano_contas_edicao = mysql_fetch_object($res_buscar_plano_contas_edicao);

    $sql_buscar_cod_plano_contas = "SELECT * FROM ipi_plano_contas WHERE cod_plano_contas = '$obj_buscar_plano_contas_edicao->cod_plano_contas_pai'";
    $res_buscar_cod_plano_contas = mysql_query($sql_buscar_cod_plano_contas);
    $obj_buscar_cod_plano_contas = mysql_fetch_object($res_buscar_cod_plano_contas);
}
else
{
    $sql_buscar_cod_plano_contas = "SELECT * FROM ipi_plano_contas WHERE conta_indice = '$conta_indice'";
    $res_buscar_cod_plano_contas = mysql_query($sql_buscar_cod_plano_contas);
    $obj_buscar_cod_plano_contas = mysql_fetch_object($res_buscar_cod_plano_contas);
}

if($obj_buscar_cod_plano_contas->cod_plano_contas > 0)
{
    $arr_caminho = montar_caminho_plano_contas($obj_buscar_cod_plano_contas->cod_plano_contas, array());

    for($i = 0; $i < count($arr_caminho); $i++)
    {
        $padding_left = 25 * $i;
        $bold = (count($arr_caminho) - 1 == $i) ? 'font-weight: bold;' : '';

        $html .= '<span style="padding-left: ' . $padding_left . 'px; '. $bold . '">' . $arr_caminho[$i] . '</span>';

        if($i < count($arr_caminho) - 1)
        {
            $html .= '<br/>';
        }
    }
}

desconectabd($conexao);

echo json_encode(array('resposta' => 'OK',
                       'html' => $html, 
                       'cod_plano_contas_pai' => $obj_buscar_cod_plano_contas->cod_plano_contas, 
                       'tipo_conta_pai' => $obj_buscar_cod_plano_contas->tipo_conta,
                       'quant' => count($arr_caminho)));
?>
