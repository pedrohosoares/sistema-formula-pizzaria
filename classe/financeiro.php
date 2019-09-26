<?

require_once dirname(__FILE__) . '/../bd.php';

/**
 * Classe de financeiro.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       29/03/2010   FELIPE        Criado.
 *
 */
class Financeiro
{
    /**
     * Lança um título com as parcelas.
     *
     * @param int $cod_titulos_subcategorias
     * @param int $cod_pizzarias
     * @param int $cod_entregadores
     * @param int $cod_clientes
     * @param int $cod_fornecedores
     * @param int $cod_colaboradores
     * @param int $cod_pedidos
     * @param int $cod_estoque_entrada
     * @param string $numero_nota_fiscal
     * @param string $descricao
     * @param string $tipo_titulo (PAGAR/RECEBER)
     * @param string|array $data_vencimento
     * @param string|array $data_pagamento
     * @param float|array $valor
     * @param float|array $juros
     * @param string|array $forma_pagamento
     * @param string|array $cheque_numero
     * @param string|array $cheque_favorecido
     * @param string|array $documento_numero
     * @param string|array $obs
     * @param int|array $mes_ref
     * @param int|array $ano_ref
     * @param string|array $situacao_parcela
     * 
     * @return true para sucesso
     */
    private function lancar_titulo($cod_titulos_subcategorias, $cod_pizzarias, $cod_entregadores = 0, $cod_clientes = 0, $cod_fornecedores = 0, $cod_colaboradores = 0, $cod_pedidos = 0, $cod_estoque_entrada = 0, $numero_nota_fiscal, $descricao, $tipo_titulo, $data_vencimento,$data_emissao, $data_pagamento, $valor, $juros, $forma_pagamento, $cheque_numero, $cheque_favorecido, $documento_numero, $obs, $mes_ref, $ano_ref, $situacao_parcela,$ipi=0,$icms=0,$outras=0)
    {


        $conexao = conectabd();
        
        beginbd();

        $tipo_cedente_sacado = '';
        
        if (($cod_entregadores > 0) && ($cod_clientes == 0) && ($cod_fornecedores == 0) && ($cod_colaboradores == 0))
        {
            $tipo_cedente_sacado = 'ENTREGADOR'; 
        }
        else if (($cod_entregadores == 0) && ($cod_clientes > 0) && ($cod_fornecedores == 0) && ($cod_colaboradores == 0))
        {
            $tipo_cedente_sacado = 'CLIENTE'; 
        }
        else if (($cod_entregadores == 0) && ($cod_clientes == 0) && ($cod_fornecedores > 0) && ($cod_colaboradores == 0))
        {
            $tipo_cedente_sacado = 'FORNECEDOR'; 
        }
        else if (($cod_entregadores == 0) && ($cod_clientes == 0) && ($cod_fornecedores == 0) && ($cod_colaboradores > 0))
        {
            $tipo_cedente_sacado = 'COLABORADOR'; 
        }

        $sql_inserir_titulo = sprintf("INSERT INTO ipi_titulos (cod_titulos_subcategorias, cod_pizzarias, cod_entregadores, cod_clientes, cod_fornecedores, cod_colaboradores, cod_pedidos, cod_estoque_entrada, numero_nota_fiscal, total_parcelas, tipo_titulo, descricao, tipo_cedente_sacado,total_ipi,total_icms,outras_despesas,data_hora_criacao) VALUE ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',NOW())", 
                                $cod_titulos_subcategorias, $cod_pizzarias, $cod_entregadores, $cod_clientes, $cod_fornecedores, $cod_colaboradores, $cod_pedidos, $cod_estoque_entrada, $numero_nota_fiscal, count($data_vencimento), $tipo_titulo, $descricao, $tipo_cedente_sacado,$ipi,$icms,$outras);



        $res_inserir_titulo = mysql_query($sql_inserir_titulo);
        
        $cod_titulos = mysql_insert_id();
        
        if($res_inserir_titulo)
        {
            if(is_array($data_vencimento))
            {
                if(count($data_vencimento) > 0)
                {
                    for($i = 0; $i < count($data_vencimento); $i++)
                    {
                        $valor_sql = moeda2bd($valor[$i]);
                        $juros_sql = moeda2bd($juros[$i]);
                        $valor_total_sql = moeda2bd($valor[$i]) + moeda2bd($juros[$i]);

                        if($tipo_titulo == 'PAGAR')
                        {
                            $valor_sql *= -1;
                            $juros_sql *= -1;
                            $valor_total_sql *= -1;
                        }

                        $sql_inserir_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos, data_vencimento, data_pagamento,data_emissao, valor, juros, valor_total, forma_pagamento, cheque_numero, cheque_favorecido, documento_numero, obs, mes_ref, ano_ref, situacao, numero_parcela, data_hora_criacao) VALUE ('%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',NOW())", 
                                                $cod_titulos, data2bd($data_vencimento[$i]), data2bd($data_pagamento[$i]),data2bd($data_emissao[$i]), $valor_sql, $juros_sql, $valor_total_sql, $forma_pagamento[$i], $cheque_numero[$i], $cheque_favorecido[$i], $documento_numero[$i], $obs[$i], $mes_ref[$i], $ano_ref[$i], $situacao_parcela, ($i + 1));

                        $res_inserir_parcelas = mysql_query($sql_inserir_parcelas);
                    }
                }
            }
        }
        
        if (mysql_errno() > 0)
        {
            rollbackbd();
            
            throw new Exception('Erro ao inserir titulo no banco de dados. Erro núm.: ' . mysql_errno());
        }
        else
        {
            commitbd();
            
            return true;
        }
    }
    
    /**
     * Lança um título de estoque de entrada.
     *
     * @param int $cod_titulos_subcategorias
     * @param int $cod_pizzarias
     * @param int $cod_fornecedores
     * @param int $cod_estoque_entrada
     * @param string $numero_nota_fiscal
     * @param string $descricao
     * @param string|array $data_vencimento
     * @param string|array $emissao Data de Emissão
     * @param string|array $data_pagamento
     * @param float|array $valor
     * @param float|array $juros
     * @param string|array $forma_pagamento
     * @param string|array $cheque_numero
     * @param string|array $cheque_favorecido
     * @param string|array $documento_numero
     * @param string|array $obs
     * @param int|array $mes_ref
     * @param int|array $ano_ref
     * @param string $situacao_parcela
     */
    public function lancar_titulo_entrada_estoque($cod_titulos_subcategorias, $cod_pizzarias, $cod_fornecedores, $cod_estoque_entrada, $numero_nota_fiscal, $descricao, $data_vencimento,$data_emissao, $data_pagamento, $valor, $juros, $forma_pagamento, $cheque_numero, $cheque_favorecido, $documento_numero, $obs, $mes_ref, $ano_ref, $situacao_parcela,$ipi,$icms,$outras)
    {
        return $this->lancar_titulo($cod_titulos_subcategorias, $cod_pizzarias, 0, 0, $cod_fornecedores, 0, 0, $cod_estoque_entrada, $numero_nota_fiscal, $descricao, 'PAGAR', $data_vencimento,$data_emissao, $data_pagamento, $valor, $juros, $forma_pagamento, $cheque_numero, $cheque_favorecido, $documento_numero, $obs, $mes_ref, $ano_ref, $situacao_parcela,$ipi,$icms,$outras);
    }
}

?>
