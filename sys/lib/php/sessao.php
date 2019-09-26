<?
/**
 * Rotina de verifica��o de sess�o.
 */
session_cache_limiter('nocache');
$cache_limiter = session_cache_limiter();
session_cache_expire(180);
$cache_expire = session_cache_expire();

session_start();
// Adicionar aqui as p�ginas que devem ser ignoradas na verifica��o de sess�o.
$arrPaginasIgnoradas = array('nuc_login.php','ipi_exibe_pedidos_despachados.php','ipi_rel_notas_fiscais.php','ipi_cadastrar_mensagens.php');

if((!$_SESSION['usuario']['autenticado']) && (array_search(basename($_SERVER['PHP_SELF']), $arrPaginasIgnoradas) === false)) {
  header("Location: nuc_login.php");
  die();
}

if (array_search(basename($_SERVER['PHP_SELF']), $arrPaginasIgnoradas) === false) {
  if(is_array($_SESSION['usuario']['paginas'])) {
    if(array_search(basename($_SERVER['PHP_SELF']), $_SESSION['usuario']['paginas']) === false) {
      header("Location: index.php");
	  	die();
    }
  }
  else {
    header("Location: nuc_login.php");
  	die();
  }
}
?>
