<?php
/**
 * Contem todas as rotinas de mensagem na tela
 */

function mensagemErro($titulo, $texto = '') {
  echo '<script type="text/javascript" src="../lib/js/mensagem.js"></script>';
  echo "<script>mensagemErro('$titulo', '$texto');</script>";
}

function mensagemOk($titulo, $texto = '') {
  echo '<script type="text/javascript" src="../lib/js/mensagem.js"></script>';
  echo "<script>mensagemOk('$titulo', '$texto');</script>";
}


/**
 * Exibe uma caixa central de erro.
 *
 * @param string $titulo Título da caixa
 * @param string $texto Texto de exibição
 */
function exibir_mensagem_erro ($titulo, $texto = '')
{
    echo '<script type="text/javascript" src="../lib/js/mensagem.js"></script>';
    echo "<script>exibir_mensagem_erro('$titulo', '$texto');</script>";
}

/**
 * Exibe uma caixa no canto superior direito de OK.
 *
 * @param string $titulo Título da caixa
 * @param string $texto Texto de exibição
 */
function exibir_mensagem_ok ($titulo, $texto = '')
{
    echo '<script type="text/javascript" src="../lib/js/mensagem.js"></script>';
    echo "<script>exibir_mensagem_ok('$titulo', '$texto');</script>";
}
?>
