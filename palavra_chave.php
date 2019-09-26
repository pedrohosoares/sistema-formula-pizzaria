<?
/**
 * Rotinas de busca e substituiчуo de palavras chave.
 *
 * @version 1.2
 * @package nucleo
 * 
 * LISTA DE MODIFICAЧеES:
 *
 * VERSУO    DATA         AUTOR         DESCRIЧУO 
 * ======    ==========   ===========   =============================================================
 * 
 * 1.2       09/06/2009   FELIPE        Renomeado as funчѕes para o novo padrуo de codificaчуo. 
 * 
 * 1.1       05/05/2009   FELIPE        Adicionado comentсrios.
 */

require_once 'bd.php';

/**
 * Busca a palavra chave no "banco de palavras" e retorna a descriчуo da mesma.
 *
 * @todo Adicionar um mecanismo que substitua uma marcaчуo passada para funчуo por valores (%usuario% por exemplo).
 * 
 * @param string $chave Chave de busca
 * @return Descriчуo / Texto. Caso nуo seja encontrado o prѓprio nome da chave serс retornado.
 */
function buscar_palavra_chave($chave) {
  require_once 'sys/lib/php/formulario.php';
  
  $objBusca = executar_busca_simples("SELECT texto FROM nuc_banco_palavras WHERE palavra = '".filtrar_caracteres_sql($chave)."' LIMIT 1");
  
  if($objBusca->texto)
    return $objBusca->texto;
  else
    return $chave;
}
?>