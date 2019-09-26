<?

require_once dirname(__FILE__) . '/../bd.php';

/**
 * Classe de Log
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       15/08/2012   Thiago        Criado.
 * 1.0       15/08/2012   Pedro         Classe log_email: criada e configurada.
 *
 */
class Log
{

    /**
     * Função que registra LOG geral do sistema.
     * o parametro palavra_chave deve seguir a seguinte tabela:
     * 
     * PEDIDO_INSERIDO, ERRO_LOGIN_PUBLICO,
     */
    public function log_email ($var_conexao, $palavra_chave, $enviado, $valor, $cod_pedidos = 0, $cod_usuarios = 0, $cod_clientes = 0, $cod_pizzarias = 0)
    {
        if (!$var_conexao)
            $conexao = conectabd();
        
        $sqlAux = "INSERT INTO ipi_log (data_hora, cod_usuarios, cod_pedidos, cod_clientes, cod_pizzarias, palavra_chave, email_enviado, valor) VALUES (NOW(), '$cod_usuarios', '$cod_pedidos', '$cod_clientes', '$cod_pizzarias', '$palavra_chave', '$enviado', '$valor')";
        $resAux = mysql_query($sqlAux);
        
        //echo "SQL: ".$sqlAux;
        

        if (!$var_conexao)
            desconectabd($conexao);
    }
    

}

?>
