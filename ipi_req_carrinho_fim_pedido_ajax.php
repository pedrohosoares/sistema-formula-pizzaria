<?
//session_start();
require_once 'sys/lib/php/formulario.php';
require_once 'bd.php';
require_once 'classe/log.php';
	
$acao = validar_var_post('acao');


if ($acao=="salvar_apelido")
{
	
	$conexao = conectar_bd ();
	$cod_pedidos = validar_var_post('cod');
	$apelido = utf8_decode(validar_var_post('apelido'));
	
	$sqlApelido = "UPDATE ipi_pedidos SET apelido='".$apelido."' WHERE cod_pedidos=".$cod_pedidos;
	$resApelido = mysql_query( $sqlApelido );

	desconectar_bd ( $conexao );
}

if($acao=="log_share_fb")
{
  $cod_pedidos = validar_var_post('cod');
  $local = (validar_var_post('local') != 'n' ? '_'.validar_var_post('local') : '');
  $log = new Log();
  $log->log_email(false, 'COMPARTILHAR_FB'.$local, 1, '', $cod_pedidos, 0, 0, 0);
}

if($acao == "pedidos_log")
{  
  require_once 'ipi_req_carrinho_classe.php';
  $carrinho = new ipi_carrinho();

  $cod_pedidos = validaVarPost('cod_pedidos');
  $nome = validaVarPost('nome');
  $versao = validaVarPost('versao');
  $idioma = validaVarPost('idioma');
  $plataforma = validaVarPost('plat');

  $carrinho->pedidos_log($cod_pedidos, $nome, $versao, $plataforma, $idioma);
}

?>
