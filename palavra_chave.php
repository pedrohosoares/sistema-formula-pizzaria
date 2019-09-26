<?
/**
 * Rotinas de busca e substitui��o de palavras chave.
 *
 * @version 1.2
 * @package nucleo
 * 
 * LISTA DE MODIFICA��ES:
 *
 * VERS�O    DATA         AUTOR         DESCRI��O 
 * ======    ==========   ===========   =============================================================
 * 
 * 1.2       09/06/2009   FELIPE        Renomeado as fun��es para o novo padr�o de codifica��o. 
 * 
 * 1.1       05/05/2009   FELIPE        Adicionado coment�rios.
 */

require_once 'bd.php';

/**
 * Busca a palavra chave no "banco de palavras" e retorna a descri��o da mesma.
 *
 * @todo Adicionar um mecanismo que substitua uma marca��o passada para fun��o por valores (%usuario% por exemplo).
 * 
 * @param string $chave Chave de busca
 * @return Descri��o / Texto. Caso n�o seja encontrado o pr�prio nome da chave ser� retornado.
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