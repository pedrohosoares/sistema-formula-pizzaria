<?

session_start();

require_once dirname(__FILE__) . '/../bd.php';

/**
 * Classe de manipulação de estoque.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       18/03/2010   FELIPE        Criado.
 *
 */

class Estoque
{
    /**
     * Variáveis de tipo de lançamento.
     * 
     * @todo Configurar na instalação
     */
    private $cod_estoque_tipo_lancamento_entrada_itens = 2;
    private $cod_estoque_tipo_lancamento_consumo = 3;
    private $id_sessao = 'estoque_entrada';
    
    /**
    * Define se será usado a sessão de entrada normal, ou de entrada para nfe
    *
    *
    */
    public function definir_id_sessao($id_sessao_novo)
    {
      if($id_sessao_novo=="estoque_entrada" || $id_sessao_novo=="estoque_entrada_nfe")
      {
        $this->id_sessao = $id_sessao_novo;
      }
      else
      {
        throw new Exception('Erro ao alterar indice da sessão, indice novo não permitido. Erro núm.: ' . mysql_errno());
      }
    }

    /**
     * Adiciona o registro no estoque de entrada.
     *
     * @param int $cod_ingredientes Código do ingrediente
     * @param int $cod_bebidas_ipi_conteudos Código da bebida e conteúdo
     * @param int $quantidade Quantidade
     * @param int $quantidade_embalagem Quantidade na embalagem. Caso não sei queira editar o valor deve ser 0.
     * @param float $preco_unitario Preço unitário
     * @param int $cod_estoque_entrada Caso a entrada não seja temporária, preencher o cód. de estoque entrada. Caso o valor seja 0, considera-se temp.
     */
    public function adicionar_entrada_item($cod_ingredientes, $cod_bebidas_ipi_conteudos, $quantidade, $quantidade_embalagem, $preco_unitario,$preco_total,$unidade, $divisor_comum, $cod_estoque_entrada = 0, $liberacao_minimo = 0)
    {
      if ($quantidade > 0)
      {
          if (($cod_ingredientes > 0) && ($cod_bebidas_ipi_conteudos == 0))
          {
              $tipo_entrada_estoque = 'INGREDIENTE';
          }
          elseif (($cod_ingredientes == 0) && ($cod_bebidas_ipi_conteudos > 0))
          {
              $tipo_entrada_estoque = 'BEBIDA';
          }
          
          $temp = ($cod_estoque_entrada == 0) ? true : false;
          
          if ($temp)
          {
              if ($tipo_entrada_estoque == 'INGREDIENTE')
              {

                  
                  //if (!$ingrediente_existe)
                  //{
                      $ingrediente_indice = isset($_SESSION[$this->id_sessao]['ingredientes']) ? count($_SESSION[$this->id_sessao]['ingredientes']) : 0;
                      
                      $arr_ingrediente['cod_ingredientes'] = $cod_ingredientes;
                      $arr_ingrediente['quantidade'] = $quantidade;
                      $arr_ingrediente['quantidade_embalagem'] = $quantidade_embalagem;
                      $arr_ingrediente['preco'] = $preco_unitario;
                      $arr_ingrediente['preco_total'] = $preco_total;
                      $arr_ingrediente['unidade'] = $unidade;
                      $arr_ingrediente['divisor_comum'] = $divisor_comum;
                      $arr_ingrediente['liberacao_minimo'] = $liberacao_minimo;
                      
                      $_SESSION[$this->id_sessao]['ingredientes'][(int) $ingrediente_indice] = $arr_ingrediente;

                      return $ingrediente_indice;
                  //}
              }
              else
              {
                  if ($tipo_entrada_estoque == 'BEBIDA')
                  {
                      /*
                      $bebida_existe = false;
                      
                      for($p = 0; $p < count($_SESSION['estoque_entrada']['bebidas']); $p++)
                      {
                          if ($_SESSION['estoque_entrada']['bebidas'][$p]['cod_bebidas_ipi_conteudos'] == $cod_bebidas_ipi_conteudos)
                          {
                              $bebida_existe = true;
                              
                              $_SESSION['estoque_entrada']['bebidas'][$p]['quantidade'] += $quantidade;
                              $_SESSION['estoque_entrada']['bebidas'][$p]['preco'] = $preco_unitario;
                              
                              break;
                          }
                      }
                      
                      if (!$bebida_existe)
                      {
                      */
                          $bebida_indice = isset($_SESSION[$this->id_sessao]['bebidas']) ? count($_SESSION[$this->id_sessao]['bebidas']) : 0;
                          
                          $arr_bebida['cod_bebidas_ipi_conteudos'] = $cod_bebidas_ipi_conteudos;
                          $arr_bebida['quantidade'] = $quantidade;
                          $arr_bebida['quantidade_embalagem'] = $quantidade_embalagem;
                          $arr_bebida['preco'] = $preco_unitario;
                          $arr_bebida['liberacao_minimo'] = '0';
                          $_SESSION[$this->id_sessao]['bebidas'][(int) $bebida_indice] = $arr_bebida;

                          return $bebida_indice;
                      //}
                  }
              }
          }
          else
          {
              $conexao = conectabd();
              
              beginbd();
              
              
              $sql_inserir_estoque_entrada_itens = sprintf("INSERT INTO ipi_estoque_entrada_itens (cod_estoque_entrada, cod_bebidas_ipi_conteudos, cod_ingredientes, quantidade_entrada, preco_unitario_entrada,preco_total_entrada, tipo_entrada_estoque,entrada_fora_limites) VALUE ('%s', '%s', '%s', '%s', '%s', '%s', '%s',%d)", $cod_estoque_entrada, $cod_bebidas_ipi_conteudos, $cod_ingredientes, $quantidade, $preco_unitario,$preco_total, $tipo_entrada_estoque,$liberacao_minimo);
              $res_inserir_estoque_entrada_itens = mysql_query($sql_inserir_estoque_entrada_itens);
              
              if (mysql_errno() > 0)
              {
                  rollbackbd();
                  
                  throw new Exception('Erro ao inserir registro no banco de dados. Erro núm.: ' . mysql_errno());
              }
              else
              {
                  commitbd();
              }
          }
      }
    }
    

    /**
     * Adiciona o registro no estoque de entrada.
     *
     * @param int $cod_ingredientes Código do ingrediente
     * @param int $cod_bebidas_ipi_conteudos Código da bebida e conteúdo
     * @param int $quantidade_embalagem Quantidade na embalagem. Caso não sei queira editar o valor deve ser 0.
     * @param str $unidade_trib Unidade tributaria da nfe, ex.: KG,LT,CX,FD,BS...
     */
    public function adicionar_item_fornecedor($ingrediente_indice,$cod_fornecedores_item,$cod_ingredientes, $cod_bebidas_ipi_conteudos, $quantidade_embalagem, $unidade_trib,$cean_trib)
    {
        if (true)//$quantidade > 0
        {
            if (($cod_ingredientes > 0) && ($cod_bebidas_ipi_conteudos == 0))
            {
                $tipo_entrada_estoque = 'INGREDIENTE';
            }
            elseif (($cod_ingredientes == 0) && ($cod_bebidas_ipi_conteudos > 0))
            {
                $tipo_entrada_estoque = 'BEBIDA';
            }
            
            $temp = ($cod_estoque_entrada == 0) ? true : false;

            if ($tipo_entrada_estoque == 'INGREDIENTE')
            {

                
                //if (!$ingrediente_existe)
                //{$_SESSION[$this->id_sessao]['ingredientes'][(int) $ingrediente_indice] = $arr_ingrediente;


                   // $ingrediente_indice = isset($_SESSION[$this->id_sessao]['ingredientes_fornecedores']) ? count($_SESSION[$this->id_sessao]['ingredientes_fornecedores']) : 0;
                    
                    $arr_ingrediente['cod_ingredientes'] = $cod_ingredientes;
                    $arr_ingrediente['quantidade_embalagem'] = $quantidade_embalagem;
                    $arr_ingrediente['cod_fornecedores_item'] = $cod_fornecedores_item;
                    $arr_ingrediente['unidade_trib'] = $unidade_trib;
                    $arr_ingrediente['cean_trib'] =  $cean_trib;
                    
                    $_SESSION[$this->id_sessao]['ingredientes'][(int) $ingrediente_indice]['dados_fornecedor'] = $arr_ingrediente;
                //}
            }
            else
            {
                if ($tipo_entrada_estoque == 'BEBIDA')
                {
                    /*
                    $bebida_existe = false;
                    
                    for($p = 0; $p < count($_SESSION['estoque_entrada']['bebidas']); $p++)
                    {
                        if ($_SESSION['estoque_entrada']['bebidas'][$p]['cod_bebidas_ipi_conteudos'] == $cod_bebidas_ipi_conteudos)
                        {
                            $bebida_existe = true;
                            
                            $_SESSION['estoque_entrada']['bebidas'][$p]['quantidade'] += $quantidade;
                            $_SESSION['estoque_entrada']['bebidas'][$p]['preco'] = $preco_unitario;
                            
                            break;
                        }
                    }
                    
                    if (!$bebida_existe)
                    {
                    */
                        $bebida_indice = isset($_SESSION[$this->id_sessao]['bebidas']) ? count($_SESSION[$this->id_sessao]['bebidas']) : 0;
                        
                        $arr_bebida['cod_bebidas_ipi_conteudos'] = $cod_bebidas_ipi_conteudos;
                        $arr_bebida['quantidade'] = $quantidade;
                        $arr_bebida['quantidade_embalagem'] = $quantidade_embalagem;
                        $arr_bebida['preco'] = $preco_unitario;
                        $arr_bebida['liberacao_minimo'] = '0';
                        $_SESSION[$this->id_sessao]['bebidas'][(int) $bebida_indice] = $arr_bebida;
                    //}
                }
            }
        }
    }

    /**
     * Altera o estoque e a entrada de estoque apartir da consulta de nf
     *
     * @param int $cod_estoque_entrada_itens Código do item na entrada de estoque
     * @param int $quantidade Quantidade
     * @param int $quantidade_embalagem Quantidade na embalagem
     */
    public function alterar_nota_fiscal($cod_estoque_entrada_itens, $quantidade, $quantidade_embalagem)
    {
        $res_alteracao = true;
        $sql_atualizar_estoque = "UPDATE ipi_estoque SET quantidade = '".($quantidade*$quantidade_embalagem)."' WHERE cod_estoque_entrada_itens = '".$cod_estoque_entrada_itens."'";
        $res_alteracao &= mysql_query($sql_atualizar_estoque);

        $sql_atualizar_entrada_estoque = "UPDATE ipi_estoque_entrada_itens SET quantidade_entrada = '".$quantidade."', quantidade_embalagem_entrada = '".$quantidade_embalagem."' WHERE cod_estoque_entrada_itens = '".$cod_estoque_entrada_itens."'";
        $res_alteracao &= mysql_query($sql_atualizar_entrada_estoque);

        return $res_alteracao;
    }

    /**
     * Adiciona na lista de processamento do mapa de estoque o item em questão
     *
     * @param int $cod_estoque_entrada_itens Código do item na entrada de estoque
     * @param int $quantidade Quantidade
     * @param int $quantidade_embalagem Quantidade na embalagem
     */
    public function reprocessar_mapa_estoque($cod_estoque_entrada_itens)
    {
        $codigo_usuario = $_SESSION['usuario']['codigo'];

        $res_inserir_proc = true;
       /* 

        $sql_atualizar_entrada_estoque = "UPDATE ipi_estoque_entrada_itens SET quantidade_entrada = '".$quantidade."', quantidade_embalagem_entrada = '".$quantidade_embalagem."' WHERE cod_estoque_entrada_itens = '".$cod_estoque_entrada_itens."'";
        $res_alteracao &= mysql_query($sql_atualizar_entrada_estoque);*/

        $sql_buscar_data = "SELECT data_hora_lancamento FROM ipi_estoque WHERE cod_estoque_entrada_itens = '".$cod_estoque_entrada_itens."'";
        $res_buscar_data = mysql_query($sql_buscar_data);
        $obj_buscar_data = mysql_fetch_object($res_buscar_data);


        $data_inicial = date("Y-m-d",strtotime($obj_buscar_data->data_hora_lancamento));
        $data_final = date("Y-m-d",strtotime(date("Y-m-d").' -1 day'));
        $sql_inserir_proc = "INSERT INTO `ipi_processamento_estoque` (`cod_usuarios_processamento`, `data_hora_processo`, `cod_ingredientes_processar`, `cod_pizzarias_processar`, `data_inicial_processamento`, `data_final_processamento`, `situacao`) (SELECT '$codigo_usuario','".date("Y-m-d H:i:s")."',e.cod_ingredientes, e.cod_pizzarias, e.data_hora_lancamento, '".$data_final."', 'ADICIONADO' FROM ipi_estoque e where cod_estoque_entrada_itens = '".$cod_estoque_entrada_itens."')";
        $res_inserir_proc &= mysql_query($sql_inserir_proc);
        //echo "<br/>".$sql_inserir_proc."<br/>";
        $cod_processamento = mysql_insert_id();

        $data = date("Y-m-d",strtotime($data_inicial.' +1 day'));
        $sql_inserir_fila = "INSERT INTO `ipi_processamento_estoque_fila` (`cod_processamento`, `data_processamento`,`situacao`) VALUES ('$cod_processamento', '$data_inicial', 'NOVO')";
        while (strtotime($data) <= strtotime($data_final)) 
        {
         // echo "$data\n";
          $sql_inserir_fila .= ", ('$cod_processamento', '$data', 'NOVO')";
          $data = date ("Y-m-d", strtotime("+1 day", strtotime($data)));
        }
        $res_inserir_fila = mysql_query($sql_inserir_fila);
        

        return ($res_inserir_proc && $res_inserir_fila);
    }

    /**
     * Altera o registro no estoque de entrada.
     *
     * @param int $cod_ingredientes Código do ingrediente
     * @param int $cod_bebidas_ipi_conteudos Código da bebida e conteúdo
     * @param int $quantidade Quantidade
     * @param int $quantidade_embalagem Quantidade na embalagem
     * @param float $preco_unitario Preço unitário
     * @param int $cod_estoque_entrada Caso a entrada não seja temporária, preencher o cód. de estoque entrada. Caso o valor seja 0, considera-se temp.
     */
    public function alterar_entrada_item($cod_ingredientes, $cod_bebidas_ipi_conteudos, $quantidade, $quantidade_embalagem, $preco_unitario, $cod_estoque_entrada = 0)
    {
        if ($quantidade > 0)
        {
            if (($cod_ingredientes > 0) && ($cod_bebidas_ipi_conteudos == 0))
            {
                $tipo_entrada_estoque = 'INGREDIENTE';
            }
            elseif (($cod_ingredientes == 0) && ($cod_bebidas_ipi_conteudos > 0))
            {
                $tipo_entrada_estoque = 'BEBIDA';
            }
            
            $temp = ($cod_estoque_entrada == 0) ? true : false;
            
            if ($temp)
            {
                if ($tipo_entrada_estoque == 'INGREDIENTE')
                {
                    $ingrediente_existe = false;
                    
                    for($p = 0; $p < count($_SESSION[$this->id_sessao]['ingredientes']); $p++)
                    {
                        if ($_SESSION[$this->id_sessao]['ingredientes'][$p]['cod_ingredientes'] == $cod_ingredientes)
                        {
                            $ingrediente_existe = true;
                            
                            $_SESSION[$this->id_sessao]['ingredientes'][$p]['quantidade'] = $quantidade;
                            $_SESSION[$this->id_sessao]['ingredientes'][$p]['quantidade_embalagem'] = $quantidade_embalagem;
                            $_SESSION[$this->id_sessao]['ingredientes'][$p]['preco'] = $preco_unitario;
                            
                            break;
                        }
                    }
                    
                    if (!$ingrediente_existe)
                    {
                        $ingrediente_indice = isset($_SESSION[$this->id_sessao]['ingredientes']) ? count($_SESSION[$this->id_sessao]['ingredientes']) : 0;
                        
                        $arr_ingrediente['cod_ingredientes'] = $cod_ingredientes;
                        $arr_ingrediente['quantidade'] = $quantidade;
                        $arr_ingrediente['preco'] = $preco_unitario;
                        
                        $_SESSION[$this->id_sessao]['ingredientes'][(int) $ingrediente_indice] = $arr_ingrediente;
                    }
                }
                else
                {
                    if ($tipo_entrada_estoque == 'BEBIDA')
                    {
                        $bebida_existe = false;
                        
                        for($p = 0; $p < count($_SESSION[$this->id_sessao]['bebidas']); $p++)
                        {
                            if ($_SESSION[$this->id_sessao]['bebidas'][$p]['cod_bebidas_ipi_conteudos'] == $cod_bebidas_ipi_conteudos)
                            {
                                $bebida_existe = true;
                                
                                $_SESSION[$this->id_sessao]['bebidas'][$p]['quantidade'] = $quantidade;
                                $_SESSION[$this->id_sessao]['bebidas'][$p]['preco'] = $preco_unitario;
                                
                                break;
                            }
                        }
                        
                        if (!$bebida_existe)
                        {
                            $bebida_indice = isset($_SESSION[$this->id_sessao]['bebidas']) ? count($_SESSION[$this->id_sessao]['bebidas']) : 0;
                            
                            $arr_bebida['cod_bebidas_ipi_conteudos'] = $cod_bebidas_ipi_conteudos;
                            $arr_bebida['quantidade'] = $quantidade;
                            $arr_bebida['preco'] = $preco_unitario;
                            
                            $_SESSION[$this->id_sessao]['bebidas'][(int) $bebida_indice] = $arr_bebida;
                        }
                    }
                }
            }
            else
            {
                $conexao = conectabd();
                
                beginbd();
                
                if ($tipo_entrada_estoque == 'INGREDIENTE')
                {
                
                    $sql_atualizar_estoque_entrada_itens = sprintf("UPDATE ipi_estoque_entrada_itens SET quantidade_entrada = '%s', preco_unitario_entrada = '%s' WHERE cod_estoque_entrada = '$cod_estoque_entrada' AND cod_ingredientes = '$cod_ingredientes'", $quantidade, $preco_unitario);
                }
                elseif ($tipo_entrada_estoque == 'BEBIDA')
                {
                    $sql_atualizar_estoque_entrada_itens = sprintf("UPDATE ipi_estoque_entrada_itens SET quantidade_entrada = '%s', preco_unitario_entrada = '%s' WHERE cod_estoque_entrada = '$cod_estoque_entrada' AND cod_bebidas_ipi_conteudos = '$cod_bebidas_ipi_conteudos'", $quantidade, $preco_unitario);
                }
                
                $res_atualizar_estoque_entrada_itens = mysql_query($sql_atualizar_estoque_entrada_itens);
                
                if (mysql_errno() > 0)
                {
                    rollbackbd();
                    
                    throw new Exception('Erro ao atualizar registro no banco de dados. Erro núm.: ' . mysql_errno());
                }
                else
                {
                    commitbd();
                }
            }
        }
    }
    
    /**
     * Exclui o registro no estoque de entrada.
     *
     * @param int $cod_ingredientes Código do ingrediente
     * @param int $cod_bebidas_ipi_conteudos Código da bebida e conteúdo
     * @param int $cod_estoque_entrada Caso a entrada não seja temporária, preencher o cód. de estoque entrada. Caso o valor seja 0, considera-se temp.
     */
    public function excluir_entrada_item($cod_ingredientes, $cod_bebidas_ipi_conteudos, $cod_estoque_entrada = 0)
    {
        if (($cod_ingredientes > 0) && ($cod_bebidas_ipi_conteudos == 0))
        {
            $tipo_entrada_estoque = 'INGREDIENTE';
        }
        elseif (($cod_ingredientes == 0) && ($cod_bebidas_ipi_conteudos > 0))
        {
            $tipo_entrada_estoque = 'BEBIDA';
        }
        
        $temp = ($cod_estoque_entrada == 0) ? true : false;
        
        if ($temp)
        {
            if ($tipo_entrada_estoque == 'INGREDIENTE')
            {
                for($p = 0; $p < count($_SESSION[$this->id_sessao]['ingredientes']); $p++)
                {
                    if ($_SESSION[$this->id_sessao]['ingredientes'][$p]['cod_ingredientes'] == $cod_ingredientes)
                    {
                        unset($_SESSION[$this->id_sessao]['ingredientes'][$p]);
                        
                        if (count($_SESSION[$this->id_sessao]['ingredientes']) > 0)
                        {
                            $arr_novos_indices = range(0, (count($_SESSION[$this->id_sessao]['ingredientes']) - 1));
                            $_SESSION[$this->id_sessao]['ingredientes'] = array_combine($arr_novos_indices, $_SESSION[$this->id_sessao]['ingredientes']);
                        }
                        
                        break;
                    }
                }
            }
            else
            {
                if ($tipo_entrada_estoque == 'BEBIDA')
                {
                    for($p = 0; $p < count($_SESSION[$this->id_sessao]['bebidas']); $p++)
                    {
                        if ($_SESSION[$this->id_sessao]['bebidas'][$p]['cod_bebidas_ipi_conteudos'] == $cod_bebidas_ipi_conteudos)
                        {
                            unset($_SESSION[$this->id_sessao]['bebidas'][$p]);
                            
                            if (count($_SESSION[$this->id_sessao]['bebidas']) > 0)
                            {
                                $arr_novos_indices = range(0, (count($_SESSION[$this->id_sessao]['bebidas']) - 1));
                                $_SESSION[$this->id_sessao]['bebidas'] = array_combine($arr_novos_indices, $_SESSION[$this->id_sessao]['bebidas']);
                            }
                            
                            break;
                        }
                    }
                }
            }
        }
        else
        {
            $conexao = conectabd();
            
            beginbd();
            
            if ($tipo_entrada_estoque == 'INGREDIENTE')
            {
                
                $sql_excluir_estoque_entrada_itens = "DELETE FROM ipi_estoque_entrada_itens WHERE cod_estoque_entrada = '$cod_estoque_entrada' AND cod_ingredientes = '$cod_ingredientes' ";
            }
            elseif ($tipo_entrada_estoque == 'BEBIDA')
            {
                $sql_excluir_estoque_entrada_itens = "DELETE FROM ipi_estoque_entrada_itens WHERE cod_estoque_entrada = '$cod_estoque_entrada' AND cod_bebidas_ipi_conteudos = '$cod_bebidas_ipi_conteudos'";
            }
            
            $res_excluir_estoque_entrada_itens = mysql_query($sql_excluir_estoque_entrada_itens);
            
            if (mysql_errno() > 0)
            {
                rollbackbd();
                
                throw new Exception('Erro ao excluir registro no banco de dados. Erro núm.: ' . mysql_errno());
            }
            else
            {
                commitbd();
            }
        }
    }
    
    /**
     * Exclui o registro temporário no estoque de entrada.
     *
     * @param int $posicao Posição
     * @param boolean $ingrediente
     * @param boolean $bebida
     */
    public function excluir_entrada_temp_item($posicao, $ingrediente, $bebida)
    {
            
        if ($ingrediente == true)
        {
            unset($_SESSION[$this->id_sessao]['ingredientes'][$posicao]);
            
            if (count($_SESSION[$this->id_sessao]['ingredientes']) > 0)
            {
                $arr_novos_indices = range(0, (count($_SESSION[$this->id_sessao]['ingredientes']) - 1));
                $_SESSION[$this->id_sessao]['ingredientes'] = array_combine($arr_novos_indices, $_SESSION[$this->id_sessao]['ingredientes']);
            }
        }
        else if ($bebida == true)
        {
            unset($_SESSION[$this->id_sessao]['bebidas'][$posicao]);
                        
            if (count($_SESSION[$this->id_sessao]['bebidas']) > 0)
            {
                $arr_novos_indices = range(0, (count($_SESSION[$this->id_sessao]['bebidas']) - 1));
                $_SESSION[$this->id_sessao]['bebidas'] = array_combine($arr_novos_indices, $_SESSION[$this->id_sessao]['bebidas']);
            }
        }
    }


    /**
     * Limpa a entrada temporária da session.
     */
    public function limpar_entrada()
    {
        unset($_SESSION[$this->id_sessao]);
    }
    
    /**
     * Verifica se existe ingredientes temporários.
     * 
     * @param bool $temp Verificar nos temporários
     * 
     * @return Booleano se existe ou não ingredientes.
     */
    public function existe_ingredientes($temp = true)
    {
        return is_array($_SESSION[$this->id_sessao]['ingredientes']);
    }
    
    /**
     * Verifica se existe bebidas temporários.
     * 
     * @param bool $temp Verificar nos temporários
     * 
     * @return Booleano se existe ou não ingredientes.
     */
    public function existe_bebidas($temp = true)
    {
        return is_array($_SESSION[$this->id_sessao]['bebidas']);
    }
    
    /**
    * Grava os itens temporários de entrada no banco de dados e financeiro.
    *
    * @param int $cod_pizzarias Código da pizzaria
    * @param string $numero_nota_fiscal Número da nota fiscal
    * @param int $cod_titulos_subcategorias Código de subcategoria do título
    * @param int $cod_fornecedores Código do fornecedor
    * @param int $num_parcelas Número total de parcelas
    * @param array $vencimento Data de vencimento
    * @param array $emissao Data de Emissão
    * @param array $valor Valor
    * @param array $mes_ref Mes e Ano de referência
    * @param string $obs Descrição do título de pagamento
    */

    public function gravar_entrada_itens_temporarios($cod_pizzarias, $numero_nota_fiscal, $cod_titulos_subcategorias, $cod_fornecedores, $num_parcelas, $vencimento,$data_emissao, $valor, $mes_ref,$ipi,$icms,$outras,$desconto_total, $obs = '')
    {
        $conexao = conectabd();
        
        beginbd();
        $desconto_total = moeda2bd($desconto_total);
        $valor_total = moeda2bd($valor[0]) + $desconto_total - moeda2bd($ipi) - moeda2bd($icms) - moeda2bd($outras) ;

        $sql_edicao = sprintf("INSERT INTO ipi_estoque_entrada (cod_fornecedores, cod_usuarios, cod_pizzarias, data_hota_entrada_estoque) VALUES ('%s', '%s', '%s', NOW())", $cod_fornecedores, $_SESSION['usuario']['codigo'], $cod_pizzarias);
        //echo "<br>sql_edicao: ".$sql_edicao;
        if (mysql_query($sql_edicao))
        {
          $codigo = mysql_insert_id();
          
          $res_edicao_itens = true;
          $c_item = 0;
          $total_items = count($this->listar_entrada_bebidas_temporarios()) + count($this->listar_entrada_ingredientes_temporarios());
          $total_desconto_dado = 0;
          /*echo "<pre>";
            print_r($valor);
            echo "</pre>";*/

          foreach ( $this->listar_entrada_ingredientes_temporarios() as $ingredientes )
          {
            
            $c_item ++;


            $quantidade_lancamento = $ingredientes['quantidade'];
            $cod_ingredientes = $ingredientes['cod_ingredientes'];
            $entrada_fora_limites = $ingredientes['liberacao_minimo'];
            //echo "<Br/><Br/><Br/>princ -".$ingredientes['preco']."- quanti lanc -$quantidade_lancamento- fora limite -$entrada_fora_limites-   vl total -($valor_total)-   vl -".$valor[0]."-    desc -$desconto_total-";
            $porc_desconto = ($quantidade_lancamento * $ingredientes['preco']) / ($valor_total);
            $valor_desconto = ($porc_desconto*$desconto_total)/$quantidade_lancamento;
            $total_desconto_dado += ($porc_desconto*$desconto_total);
            //echo "<Br/> porc_desconto=".$porc_desconto;
            //echo "<Br/> valor_desconto=".$valor_desconto;
            //echo "<Br/> total_desconto_dado=".$total_desconto_dado;
            
            if($c_item==$total_items && $total_desconto_dado !=$desconto_total)
            {
              $valor_desconto += $desconto_total - $total_desconto_dado;
            }

            $preco_ing_descontado = $ingredientes['preco'] - ($valor_desconto);
            //echo "<Br/> valor_desconto=".$valor_desconto;
            //echo "<Br/> preco_ing_descontado=".$preco_ing_descontado;

            $embalagem_lancamento = $ingredientes['quantidade_embalagem']*$ingredientes['divisor_comum'];
            
            $sql_edicao_itens = sprintf("INSERT INTO ipi_estoque_entrada_itens (cod_estoque_entrada, cod_ingredientes, cod_bebidas_ipi_conteudos, tipo_entrada_estoque, quantidade_entrada, quantidade_embalagem_entrada, preco_unitario_entrada,preco_unitario_sem_desconto,preco_total_entrada,valor_desconto,entrada_fora_limites) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',%d)", $codigo,  $cod_ingredientes, 0, 'INGREDIENTE',$quantidade_lancamento, $embalagem_lancamento,$preco_ing_descontado,$ingredientes['preco'],$ingredientes['preco_total'],$valor_desconto,$entrada_fora_limites);
            $res_edicao_itens &= mysql_query($sql_edicao_itens);

            $cod_estoque_entrada_itens = mysql_insert_id();
            
            if(is_array($ingredientes['dados_fornecedor']))
            {
              //if($ingredientes['dados_fornecedor']['unidade_trib']!="KG")
              {
                //$ingredientes['dados_fornecedor']['cod_ingredientes'] = $cod_ingredientes;
                //$qtd_embalagem = $ingredientes['dados_fornecedor']['quantidade_embalagem'];
                $cod_fornecedores_item = $ingredientes['dados_fornecedor']['cod_fornecedores_item'];
                $unidade_trib = $ingredientes['dados_fornecedor']['unidade_trib'];
                $cean_trib = $ingredientes['dados_fornecedor']['cean_trib'];

                if($cod_fornecedores!="" && $cod_fornecedores_item !="" && $unidade_trib!="")
                {
                  $sql_itens_forn = "DELETE FROM ipi_ingredientes_ipi_fornecedores WHERE cod_fornecedores = '".$cod_fornecedores."' and cod_item_fornecedor = '".$cod_fornecedores_item."' and unidade_trib ='".$unidade_trib."'";
                  $res_itens_forn = mysql_query($sql_itens_forn);

                  $sql_itens_forn = sprintf("INSERT INTO ipi_ingredientes_ipi_fornecedores (cod_fornecedores,cod_ingredientes,cod_item_fornecedor,quantidade_embalagem,unidade_trib,cean_trib) VALUES (%d, %d, %d, %d,'%s',%d)",$cod_fornecedores,$cod_ingredientes,$cod_fornecedores_item,$embalagem_lancamento ,$unidade_trib,$cean_trib);
                  $res_itens_forn = mysql_query($sql_itens_forn);
                }
              }
            }

            //echo $sql_edicao_itens;

            
            $quantidade_total = $quantidade_lancamento * $embalagem_lancamento;
            
            $res_edicao_itens &= $this->lancar_estoque_entrada_itens($quantidade_total, $cod_ingredientes, 0, 'INGREDIENTE', $cod_pizzarias, $cod_estoque_entrada_itens);
          }
            
            foreach ( $this->listar_entrada_bebidas_temporarios() as $bebidas )
            {
              $c_item ++;

              $quantidade_lancamento =  $bebidas['quantidade'];
              $porc_desconto = ($bebidas['quantidade']*$bebidas['preco']) / $valor_total;
              // $porc_desconto = round($porc_desconto , 2);
              $valor_desconto = ($porc_desconto*$desconto_total)/$quantidade_lancamento;
              //$valor_desconto = round($valor_desconto , 2);
              $total_desconto_dado += ($porc_desconto*$desconto_total);

              if($c_item==$total_items && $total_desconto_dado !=$desconto_total)
              {
                  $valor_desconto += $desconto_total - $total_desconto_dado;
              }
              $preco_ing_descontado = $bebidas['preco'] - ($valor_desconto);

              $sql_edicao_itens = sprintf("INSERT INTO ipi_estoque_entrada_itens (cod_estoque_entrada, cod_ingredientes, cod_bebidas_ipi_conteudos, tipo_entrada_estoque, quantidade_entrada, quantidade_embalagem_entrada, preco_unitario_entrada,preco_unitario_sem_desconto,valor_desconto) VALUES ('%s',  '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $codigo, 0, $bebidas['cod_bebidas_ipi_conteudos'], 'BEBIDA', $bebidas['quantidade'], $bebidas['quantidade_embalagem'], $preco_ing_descontado, $bebidas['preco'], $valor_desconto);
              $res_edicao_itens &= mysql_query($sql_edicao_itens);
              //echo "<br>sql_edicao_itens: ".$sql_edicao_itens;
              $cod_estoque_entrada_itens = mysql_insert_id();

              $quantidade_lancamento = $bebidas['quantidade'] * $bebidas['quantidade_embalagem'];
              $res_edicao_itens &= $this->lancar_estoque_entrada_itens($quantidade_lancamento, 0, $bebidas['cod_bebidas_ipi_conteudos'], 'BEBIDA', $cod_pizzarias, $cod_estoque_entrada_itens);
            }
            
            // Inserindo no financeiro
            if ($res_edicao_itens)
            {
              require_once dirname(__FILE__) . '/financeiro.php';
              
              $data_ref = $mes_ref;
              
              for($i = 0; $i < count($data_ref); $i++)
              {
                $arr_ref = explode('/', $data_ref[$i]);
                
                $mes_ref[$i] = (int) $arr_ref[0];
                $ano_ref[$i] = (int) $arr_ref[1];
              }

              if ($obs == '')
              {
                $obs = "Ref. entrada estoque $codigo";
              }

              $financeiro = new Financeiro();
              $financeiro->lancar_titulo_entrada_estoque($cod_titulos_subcategorias, $cod_pizzarias, $cod_fornecedores, $codigo, $numero_nota_fiscal, $obs, $vencimento,$data_emissao, array(), $valor, $juros, array(), array(), array(), array(), array(), $mes_ref, $ano_ref, 'ABERTO',$ipi,$icms,$outras,$desconto_total);
            }
        }
        
        if (mysql_errno() > 0)
        {
          rollbackbd();
          
          throw new Exception('Erro ao gravar registro temporário no banco de dados. Erro núm.: ' . mysql_errno());
        }
        else
        {
          commitbd();
          
          $this->limpar_entrada();
          
          return true;
        }
    }
    
    /**
     * Lista os ingredientes temporários.
     *
     * @return Array com todos os ingredientes temporários.
     */
    public function listar_entrada_ingredientes_temporarios()
    {
        if(is_array($_SESSION[$this->id_sessao]['ingredientes']))
        {
            return $_SESSION[$this->id_sessao]['ingredientes'];
        }
        else
        {
            return array();
        }
    }
    
    /**
     * Lista os bebidas temporários.
     *
     * @return Array com todos as bebidas temporários.
     */
    public function listar_entrada_bebidas_temporarios()
    {
        if(is_array($_SESSION[$this->id_sessao]['bebidas']))
        {
            return $_SESSION[$this->id_sessao]['bebidas'];
        }
        else
        {
            return array();
        }
    }


    
    /**
     * Método para arredondar o $valor e adaptar ele ao número de casas decimais informado.
     *
     * @param int $valor Valor a ser arredondado e adaptado.
     * @param int $casas Número de casa que o valor terá após ser arredondado.
     * @param str $separador Especifica qual o separador decimal do $valor para adaptação correta.
     * 
     * @return $valor arredondado e adaptado.
     */
    public function float2bd($valor, $casas, $separador = '.')
    {
      if($separador == '.')
      {
        $valor = str_replace(',', '', $valor);
      }
      elseif($separador == ',')
      {
        $valor = str_replace(',', '.', str_replace('.', '', $valor));
      }
      return str_replace(',', '', number_format($valor, $casas));
    }


    
    /**
     * Método genérico de insert no banco na tabela de estoques.
     *
     * @param int $quantidade Quantidade de ingrediente/bebidas a serem inseridas. Para débito adicionar com valor negativo.
     * @param int $cod_ingredientes Código do ingrediente
     * @param int $cod_bebidas_ipi_conteudos Código da bebida conteúdo
     * @param string $tipo_estoque Tipo de estoque - BEBIDA/INGREDIENTE
     * @param int $cod_pizzarias Código de pizzaria
     * @param int $cod_estoque_tipo_lancamento Tipo de lançamento
     * @param int $cod_pedidos Código do pedido se houver
     * @param int $cod_colaboradores_prejuizo Código do colaborador que causou o prejuizo se houver
     * @param int $cod_estoque_entrada_itens Caso seja entrada de estoque informar o código
     * @param int $cod_pedidos_pizzas Chave que liga com a tabela ipi_pedidos_pizzas, usada para saber a pizza do adicional ou borda
     * @param int $cod_pedidos_fracoes Chave para saber de qual fração o ingrediente pertence
     * @param int $cod_pedidos_ingredientes Chave para achar o ingrediente adicional de qual pizza
     * @param int $cod_pedidos_bebidas Linkar qual bebida é qual
     * @param int $cod_pedidos_bordas Chave para saber qual borda é de qual pizza 
     * @return true para sucesso.
     */
    public function lancar_estoque($quantidade, $cod_ingredientes, $cod_bebidas_ipi_conteudos, $tipo_estoque, $cod_pizzarias, $cod_estoque_tipo_lancamento, $cod_pedidos = 0, $cod_colaboradores_prejuizo = 0, $cod_estoque_entrada_itens = 0, $obs = '',$cod_pedidos_pizzas = 0, $cod_pedidos_fracoes = 0, $cod_pedidos_ingredientes = 0, $cod_pedidos_bebidas = 0, $cod_pedidos_bordas = 0)
    {
        $conexao = conectabd();
        
        beginbd();
        //echo $cod_ingredientes.'= '.$quantidade.'<br />';
        $data_hora_lancamento = " NOW()";
        if($cod_pedidos>0)
        {
            $sql_buscar_dados_pedido = "select * from ipi_pedidos where cod_pedidos = '".$cod_pedidos."'";
            $res_buscar_dados_pedido = mysql_query($sql_buscar_dados_pedido);
            $obj_buscar_dados_pedido = mysql_fetch_object($res_buscar_dados_pedido);

            $data_hora_lancamento = "'".$obj_buscar_dados_pedido->data_hora_pedido."'";
        }   

        $sql_edicao = sprintf("INSERT INTO ipi_estoque (quantidade, cod_bebidas_ipi_conteudos, cod_ingredientes, tipo_estoque, data_hora_lancamento, cod_pizzarias, cod_usuarios, cod_colaboradores_prejuizo, cod_estoque_entrada_itens, cod_estoque_tipo_lancamento, cod_pedidos, obs_estoque, cod_pedidos_ingredientes, cod_pedidos_bebidas, cod_pedidos_bordas, cod_pedidos_pizzas, cod_pedidos_fracoes) VALUES ('%s', '%s', '%s', '%s', $data_hora_lancamento, '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, %d, %d, %d)", $quantidade, $cod_bebidas_ipi_conteudos, $cod_ingredientes, $tipo_estoque, $cod_pizzarias, $_SESSION['usuario']['codigo'], $cod_colaboradores_prejuizo, $cod_estoque_entrada_itens, $cod_estoque_tipo_lancamento, $cod_pedidos, $obs,$cod_pedidos_ingredientes,$cod_pedidos_bebidas,$cod_pedidos_bordas,$cod_pedidos_pizzas,$cod_pedidos_fracoes);
        $res_edicao = mysql_query($sql_edicao);
        //echo "<br>sql_edicao: ".$sql_edicao;
        if (mysql_errno() > 0)
        {
            rollbackbd();
            
            throw new Exception('Erro ao gravar registro de estoque no banco de dados. Erro núm.: ' . mysql_errno());
        }
        else
        {
            commitbd();
            
            return true;
        }
    }

    
    /**
     * Lança uma entrada de estoque (itens - Nota fiscal de entrada)
     *
     * @param int $quantidade Quantidade a ser dado entrada
     * @param int $cod_ingredientes Ingrediente
     * @param int $cod_bebidas_ipi_conteudos Bebida
     * @param string $tipo_estoque Tipo de estoque (INGREDIENTE/BEBIDA)
     * @param int $cod_pizzarias Pizzaria
     * @param int $cod_estoque_entrada_itens Código de entrada de itens
     * 
     * @return true para sucesso
     */
    public function lancar_estoque_entrada_itens($quantidade, $cod_ingredientes, $cod_bebidas_ipi_conteudos, $tipo_estoque, $cod_pizzarias, $cod_estoque_entrada_itens)
    {
        return $this->lancar_estoque($quantidade, $cod_ingredientes, $cod_bebidas_ipi_conteudos, $tipo_estoque, $cod_pizzarias, $this->cod_estoque_tipo_lancamento_entrada_itens, 0, 0, $cod_estoque_entrada_itens);
    }
    
    /**
     * Lança uma saída de estoque diretamente de um pedido
     *
     * @param int $cod_pedidos Código do pedido
     * 
     * @return true para sucesso
     */
    public function lancar_estoque_consumo_pedido($cod_pedidos)
    {
        $res_lancar_consumo = true;
        
        // Buscando os ingredientes (Consumo) das pizzas        
        $sql_buscar_ingredientes_pizza = "SELECT pp.quant_fracao, quantidade_estoque_ingrediente, iiip.cod_ingredientes, p.cod_pizzarias, pp.cod_pedidos, iin.cod_ingredientes_baixa,pf.cod_pedidos_fracoes, pp.cod_pedidos_pizzas FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_ingredientes_ipi_pizzas iiip ON (pf.cod_pizzas = iiip.cod_pizzas) INNER JOIN ipi_ingredientes_estoque ie ON (iiip.cod_ingredientes = ie.cod_ingredientes AND pp.cod_tamanhos = ie.cod_tamanhos AND pf.cod_pizzas = ie.cod_pizzas) INNER JOIN ipi_ingredientes iin ON (iiip.cod_ingredientes = iin.cod_ingredientes and iin.consumo = 1) WHERE p.cod_pedidos = '$cod_pedidos'";
        $res_buscar_ingredientes_pizza = mysql_query($sql_buscar_ingredientes_pizza);

        /*, $obs = '',$cod_pedidos_pizzas = 0, $cod_pedidos_fracoes = 0, $cod_pedidos_ingredientes = 0, $cod_pedidos_bebidas = 0, $cod_pedidos_bordas = 0
        , 0, 0, '',0,0, $obj_buscar_ingredientes_adicional_pizza->cod_pedidos_ingredientes, 0, 0);  */
        while ( $obj_buscar_ingredientes_pizza = mysql_fetch_object($res_buscar_ingredientes_pizza) )
        {
          if($obj_buscar_ingredientes_pizza->cod_ingredientes_baixa)
          {
            $quantidade_estoque = (($obj_buscar_ingredientes_pizza->quantidade_estoque_ingrediente * -1)/$obj_buscar_ingredientes_pizza->quant_fracao); 

            /*$quantidade_estoque = (($obj_buscar_ingredientes_pizza->quantidade_estoque_ingrediente * (-1 -($obj_buscar_ingredientes_pizza->indice_perda/100))/$obj_buscar_ingredientes_pizza->quant_fracao); */

            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd($quantidade_estoque, 3), $obj_buscar_ingredientes_pizza->cod_ingredientes_baixa, 0, 'INGREDIENTE', $obj_buscar_ingredientes_pizza->cod_pizzarias , $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_ingredientes_pizza->cod_pedidos,0,0,'',$obj_buscar_ingredientes_pizza->cod_pedidos_pizzas,$obj_buscar_ingredientes_pizza->cod_pedidos_fracoes);   
          }
          else
          {

            $quantidade_estoque = (($obj_buscar_ingredientes_pizza->quantidade_estoque_ingrediente * -1)/$obj_buscar_ingredientes_pizza->quant_fracao); 
            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd($quantidade_estoque, 3), $obj_buscar_ingredientes_pizza->cod_ingredientes, 0, 'INGREDIENTE', $obj_buscar_ingredientes_pizza->cod_pizzarias , $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_ingredientes_pizza->cod_pedidos,0,0,'',$obj_buscar_ingredientes_pizza->cod_pedidos_pizzas,$obj_buscar_ingredientes_pizza->cod_pedidos_fracoes);   
          }    
        }

        // Buscando os ingredientes (N Consumo) das pizzas        
        $sql_buscar_ingredientes_pizza = "SELECT pp.quant_fracao, quantidade_estoque_ingrediente, iiip.cod_ingredientes, p.cod_pizzarias, pp.cod_pedidos, iin.cod_ingredientes_baixa,pf.cod_pedidos_fracoes, pp.cod_pedidos_pizzas FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_ingredientes_ipi_pizzas iiip ON (pf.cod_pizzas = iiip.cod_pizzas) INNER JOIN ipi_ingredientes_estoque ie ON (iiip.cod_ingredientes = ie.cod_ingredientes AND pp.cod_tamanhos = ie.cod_tamanhos AND pf.cod_pizzas = ie.cod_pizzas) INNER JOIN ipi_pedidos_ingredientes ipi on (ipi.cod_ingredientes = iiip.cod_ingredientes and ipi.cod_pedidos = p.cod_pedidos and pf.cod_pedidos_fracoes = ipi.cod_pedidos_fracoes) INNER JOIN ipi_ingredientes iin ON (iiip.cod_ingredientes = iin.cod_ingredientes and iin.consumo = 0) WHERE p.cod_pedidos = '$cod_pedidos'";
        $res_buscar_ingredientes_pizza = mysql_query($sql_buscar_ingredientes_pizza);

        /*, $obs = '',$cod_pedidos_pizzas = 0, $cod_pedidos_fracoes = 0, $cod_pedidos_ingredientes = 0, $cod_pedidos_bebidas = 0, $cod_pedidos_bordas = 0
        , 0, 0, '',0,0, $obj_buscar_ingredientes_adicional_pizza->cod_pedidos_ingredientes, 0, 0);  */
        while ( $obj_buscar_ingredientes_pizza = mysql_fetch_object($res_buscar_ingredientes_pizza) )
        {
          if($obj_buscar_ingredientes_pizza->cod_ingredientes_baixa)
          {
            $quantidade_estoque = (($obj_buscar_ingredientes_pizza->quantidade_estoque_ingrediente * -1)/$obj_buscar_ingredientes_pizza->quant_fracao); 

            /*$quantidade_estoque = (($obj_buscar_ingredientes_pizza->quantidade_estoque_ingrediente * (-1 -($obj_buscar_ingredientes_pizza->indice_perda/100))/$obj_buscar_ingredientes_pizza->quant_fracao); */

            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd($quantidade_estoque, 3), $obj_buscar_ingredientes_pizza->cod_ingredientes_baixa, 0, 'INGREDIENTE', $obj_buscar_ingredientes_pizza->cod_pizzarias , $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_ingredientes_pizza->cod_pedidos,0,0,'',$obj_buscar_ingredientes_pizza->cod_pedidos_pizzas,$obj_buscar_ingredientes_pizza->cod_pedidos_fracoes);   
          }
          else
          {

            $quantidade_estoque = (($obj_buscar_ingredientes_pizza->quantidade_estoque_ingrediente * -1)/$obj_buscar_ingredientes_pizza->quant_fracao); 
            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd($quantidade_estoque, 3), $obj_buscar_ingredientes_pizza->cod_ingredientes, 0, 'INGREDIENTE', $obj_buscar_ingredientes_pizza->cod_pizzarias , $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_ingredientes_pizza->cod_pedidos,0,0,'',$obj_buscar_ingredientes_pizza->cod_pedidos_pizzas,$obj_buscar_ingredientes_pizza->cod_pedidos_fracoes);   
          }    
        }
        //die();
        
        // Ingredientes adicionais Adicionais
        $sql_buscar_ingredientes_adicional_pizza = "SELECT iiit.quantidade_estoque_extra, pp.quant_fracao, iiit.cod_ingredientes as cod_ingredientes_adicional, p.cod_pizzarias, pp.cod_pedidos, iin.cod_ingredientes_baixa,pi.cod_pedidos_ingredientes,pf.cod_pedidos_fracoes,pp.cod_pedidos_pizzas FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos = pf.cod_pedidos AND pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) INNER JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos = pi.cod_pedidos AND pf.cod_pedidos_pizzas = pi.cod_pedidos_pizzas AND pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) LEFT JOIN ipi_ingredientes_ipi_tamanhos iiit ON (iiit.cod_tamanhos = pp.cod_tamanhos AND pi.cod_ingredientes = iiit.cod_ingredientes AND p.cod_pizzarias = iiit.cod_pizzarias) LEFT JOIN ipi_ingredientes iin ON (iiit.cod_ingredientes = iin.cod_ingredientes) WHERE p.cod_pedidos = '$cod_pedidos' AND ingrediente_padrao = 0";
        $res_buscar_ingredientes_adicional_pizza = mysql_query($sql_buscar_ingredientes_adicional_pizza);
        //echo $sql_buscar_ingredientes_adicional_pizza;
       
        while ( $obj_buscar_ingredientes_adicional_pizza = mysql_fetch_object($res_buscar_ingredientes_adicional_pizza) )
        {
          if($obj_buscar_ingredientes_adicional_pizza->cod_ingredientes_baixa)
          {
            $quantidade_estoque = (($obj_buscar_ingredientes_adicional_pizza->quantidade_estoque_extra * -1)/$obj_buscar_ingredientes_adicional_pizza->quant_fracao);
            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd($quantidade_estoque, 3), $obj_buscar_ingredientes_adicional_pizza->cod_ingredientes_baixa, 0, 'INGREDIENTE', $obj_buscar_ingredientes_adicional_pizza->cod_pizzarias, $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_ingredientes_adicional_pizza->cod_pedidos, 0, 0, '',$obj_buscar_ingredientes_adicional_pizza->cod_pedidos_pizzas, $obj_buscar_ingredientes_adicional_pizza->cod_pedidos_fracoes, $obj_buscar_ingredientes_adicional_pizza->cod_pedidos_ingredientes, 0, 0);  
          }
          else
          {
            $quantidade_estoque = (($obj_buscar_ingredientes_adicional_pizza->quantidade_estoque_extra * -1)/$obj_buscar_ingredientes_adicional_pizza->quant_fracao);
            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd($quantidade_estoque, 3), $obj_buscar_ingredientes_adicional_pizza->cod_ingredientes_adicional, 0, 'INGREDIENTE', $obj_buscar_ingredientes_adicional_pizza->cod_pizzarias, $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_ingredientes_adicional_pizza->cod_pedidos, 0, 0, '',$obj_buscar_ingredientes_adicional_pizza->cod_pedidos_pizzas, $obj_buscar_ingredientes_adicional_pizza->cod_pedidos_fracoes, $obj_buscar_ingredientes_adicional_pizza->cod_pedidos_ingredientes, 0, 0);  
          }     
        }
        
        // Buscando os ingredientes de borda
        $sql_buscar_ingredientes_borda = "SELECT * FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_bordas pb ON (pp.cod_pedidos = pb.cod_pedidos AND pp.cod_pedidos_pizzas = pb.cod_pedidos_pizzas) INNER JOIN ipi_tamanhos_ipi_bordas tb ON (pp.cod_tamanhos = tb.cod_tamanhos AND pb.cod_bordas = tb.cod_bordas AND p.cod_pizzarias = tb.cod_pizzarias) INNER JOIN ipi_bordas b ON (pb.cod_bordas = b.cod_bordas) LEFT JOIN ipi_tamanhos_ipi_bordas_estoque itibe ON (pp.cod_tamanhos = itibe.cod_tamanhos AND b.cod_bordas = itibe.cod_bordas) LEFT JOIN ipi_ingredientes iin ON (b.cod_ingredientes = iin.cod_ingredientes) WHERE p.cod_pedidos = '$cod_pedidos'";
        $res_buscar_ingredientes_borda = mysql_query($sql_buscar_ingredientes_borda);
        //echo $sql_buscar_ingredientes_borda;

        while ( $obj_buscar_ingredientes_borda = mysql_fetch_object($res_buscar_ingredientes_borda) )
        {
          if($obj_buscar_ingredientes_borda->cod_ingredientes_baixa)
          {
            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd(($obj_buscar_ingredientes_borda->quantidade_estoque * -1), 3), $obj_buscar_ingredientes_borda->cod_ingredientes_baixa, 0, 'INGREDIENTE', $obj_buscar_ingredientes_borda->cod_pizzarias, $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_ingredientes_borda->cod_pedidos, 0, 0, '',$obj_buscar_ingredientes_borda->cod_pedidos_pizzas, 0, 0, 0, $obj_buscar_ingredientes_borda->cod_pedidos_bordas);
          }
          else
          {
            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd(($obj_buscar_ingredientes_borda->quantidade_estoque * -1), 3), $obj_buscar_ingredientes_borda->cod_ingredientes, 0, 'INGREDIENTE', $obj_buscar_ingredientes_borda->cod_pizzarias, $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_ingredientes_borda->cod_pedidos, 0, 0, '',$obj_buscar_ingredientes_borda->cod_pedidos_pizzas, 0, 0, 0, $obj_buscar_ingredientes_borda->cod_pedidos_bordas);
          }
        }
        
        // Buscando adicionais (gergelim)
        $sql_buscar_ingredientes_adicionais = "SELECT * FROM ipi_pedidos p INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos_adicionais pa ON (pp.cod_pedidos = pa.cod_pedidos AND pp.cod_pedidos_pizzas = pa.cod_pedidos_pizzas) INNER JOIN ipi_tamanhos_ipi_adicionais ta ON (pa.cod_adicionais = ta.cod_adicionais AND pp.cod_tamanhos = ta.cod_tamanhos AND p.cod_pizzarias = ta.cod_pizzarias) INNER JOIN ipi_adicionais a ON (pa.cod_adicionais = a.cod_adicionais) LEFT JOIN ipi_tamanhos_ipi_adicionais_estoque itiae ON (itiae.cod_tamanhos = pp.cod_tamanhos AND itiae.cod_adicionais = a.cod_adicionais) LEFT JOIN ipi_ingredientes iin ON (a.cod_ingredientes = iin.cod_ingredientes) WHERE p.cod_pedidos = '$cod_pedidos'";
        
        $res_buscar_ingredientes_adicionais = mysql_query($sql_buscar_ingredientes_adicionais);
        
        while ( $obj_buscar_ingredientes_adicionais = mysql_fetch_object($res_buscar_ingredientes_adicionais) )
        {
          if($obj_buscar_ingredientes_adicionais->cod_ingredientes_baixa)
          {
            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd(($obj_buscar_ingredientes_adicionais->quantidade_estoque * -1), 3), $obj_buscar_ingredientes_adicionais->cod_ingredientes_baixa, 0, 'INGREDIENTE', $obj_buscar_ingredientes_adicionais->cod_pizzarias, $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_ingredientes_adicionais->cod_pedidos, 0, 0, '', $obj_buscar_ingredientes_borda->cod_pedidos_pizzas, 0, 0, 0, 0);
          }
          else
          {
            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd(($obj_buscar_ingredientes_adicionais->quantidade_estoque * -1), 3), $obj_buscar_ingredientes_adicionais->cod_ingredientes, 0, 'INGREDIENTE', $obj_buscar_ingredientes_adicionais->cod_pizzarias, $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_ingredientes_adicionais->cod_pedidos, 0, 0, '', $obj_buscar_ingredientes_borda->cod_pedidos_pizzas, 0, 0, 0, 0);
          }
        }
        
        // Buscando bebidas
        $sql_buscar_bebidas = "SELECT * FROM ipi_pedidos p INNER JOIN ipi_pedidos_bebidas pb ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) WHERE p.cod_pedidos = '$cod_pedidos'";
        $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
        
        while ( $obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas) )
        {
            $res_lancar_consumo &= $this->lancar_estoque($this->float2bd(($obj_buscar_bebidas->quantidade * -1), 3), 0, $obj_buscar_bebidas->cod_bebidas_ipi_conteudos, 'BEBIDA', $obj_buscar_bebidas->cod_pizzarias, $this->cod_estoque_tipo_lancamento_consumo, $obj_buscar_bebidas->cod_pedidos, 0, 0, '', 0,  0, 0, $obj_buscar_bebidas->cod_pedidos_bebidas);
        }
        
        return $res_lancar_consumo;
    }
}

?>
