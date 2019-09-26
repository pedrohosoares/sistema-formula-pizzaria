<?php

/**
 * ipi_pedido_minimo.php: Cadastro de Pedido Minimo
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Definição do Pedido Mínimo de Compra');

$acao = validaVarPost('acao');

$tabela = 'ipi_configuracoes';
$chave_primaria = 'chave';

switch($acao) {
  case 'editar':
    $valor_minimo = validaVarPost('valor_minimo');
    
    $con = conectabd();
      $SqlEdicao = sprintf("UPDATE $tabela SET valor = '%s' WHERE chave='VALOR_MINIMO'", moeda2bd($valor_minimo));
        //echo $SqlEdicao;
      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    
    desconectabd($con);
  break;
}

?>



  
  
  <!-- Tab Incluir -->
    <? 
    $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = 'VALOR_MINIMO'");
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="valor_minimo">Valor Mínimo de Compra</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="valor_minimo" id="valor_minimo" maxlength="20" size="25" value="<? echo bd2moeda("0".$objBusca->valor) ?>" onkeypress="return ValidarNumeros(event)"></td></tr>
    
    <tr><td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Alterar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    
    </form>
  <!-- Tab Incluir -->
    

<? rodape(); ?>