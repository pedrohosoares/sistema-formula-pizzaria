<?
session_start();
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';



function arredondar_preco_ingrediente ($preco_ingrediente, $numero_divisoes)
{
    $preco_dividido = $preco_ingrediente / $numero_divisoes;
    $resto = ceil($preco_dividido) - $preco_dividido;
    if ($resto >= 0.50)
    {
        $preco = floor($preco_dividido) + 0.50;
    }
    else
        $preco = ceil($preco_dividido);
    return $preco;
}

class ipi_carrinho
{
    private $car_versao = "1.0";
    private $preco_troca_promocao = 6; //constante para definir o preço de troca da bebida kuat para a bebida coca
    private $pizzas_balcao = 2;  //FIX-ME (fazer funcionar) constante que define a quantidaade de pizzas necessarias para ganhar o refri gratis no balcao
    public $cod_ingredientes_promocao_16 = 12;//BACON 
    /**
     * Função que retorna a numeração da versão do carrinho.
     */
    public function versao ()
    {
        return $this->car_versao;
    }
    
    public function retornar_codigo_pizzaria ()
    {
        $concod = conectabd();

        if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
        {
          $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
        }
        else
        {
          $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
          $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
          $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
          $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
          $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
          $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
        }
        desconectabd($concod);

        return $cod_pizzarias;
    }
    /**
     * Função que registra LOG geral do sistema.
     * o parametro palavra_chave deve seguir a seguinte tabela:
     * 
     * PEDIDO_INSERIDO, ERRO_LOGIN_PUBLICO,
     */
    public function log ($var_conexao, $palavra_chave, $valor, $cod_pedidos = 0, $cod_usuarios = 0, $cod_clientes = 0)
    {
        if (!$var_conexao)
            $conexao = conectabd();
        
        $sqlAux = "INSERT INTO ipi_log (data_hora, cod_usuarios, cod_pedidos, cod_clientes, palavra_chave, valor) VALUES (NOW(), $cod_usuarios, $cod_pedidos, $cod_clientes, '$palavra_chave', '$valor' )";
        $resAux = mysql_query($sqlAux);
        
        //echo "SQL: ".$sqlAux;
        

        if (!$var_conexao)
            desconectabd($conexao);
    }
    
    public function gerar_id_sessao()
    {
        if ($_SESSION['ipi_carrinho']['id_sessao_atual']!="") 
        {
            $ret = $_SESSION['ipi_carrinho']['id_sessao_atual'] + 1;
        }
        else
        {
            $ret = 1;
        }
        
        $_SESSION['ipi_carrinho']['id_sessao_atual'] = $ret;
        
        return $ret;
    }        
    
    /**
     * Função que retorna a numeração da versão do carrinho.
     */
    public function existe_pedido ()
    {
        $ret = isset($_SESSION['ipi_carrinho']['pedido']) ? 1 : 0;
        return $ret;
    }
    
    /**
     * Função que exibe o total de pontos de fidelidade do cliente.
     */
    public function pontos_fidelidade ()
    {
        $pontos = isset($_SESSION['ipi_cliente']['pontos_fidelidade']) ? $_SESSION['ipi_cliente']['pontos_fidelidade'] : 0;
        
        return ($pontos - $_SESSION['ipi_carrinho']['fidelidade_pontos_gastos']);
    }
    
    /**
     * Função que cria uma pizza na sessão e retorna o o indice da pizza na sessão.
     */
    public function adicionar_pizza ($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $quant_fracao, $cod_opcoes_corte, $validar_cupom = '0', $numero_cupom = 0, $id_combo = '', $cod_combo = '',$cpp = '')
    {
        /*
        pedido              = ipi_req_carrinho_pedido.php               = Pedido Normal
        pedido_combo        = ipi_req_carrinho_pedido_combo.php         = Pedido Combo
        pedido_cupom        = ipi_req_carrinho_pedido_cupom.php         = Pedido usando Cupom
        pedido_promocional  = ipi_req_carrinho_pedido_promocional.php   = Pedido Promocional Abrir as Opções na Pizza de Segunda-Feira
        */

        $proximo_indice = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        $total_pedido = isset($_SESSION['ipi_carrinho']['total_pedido']) ? $_SESSION['ipi_carrinho']['total_pedido'] : 0;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_combo'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_combo'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_tamanhos'] = $cod_tamanhos;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_id_sessao'] = $this->gerar_id_sessao();
        
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_adicionais'] = $cod_adicionais;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_bordas'] = $cod_bordas;

        
        if (($_SESSION['ipi_carrinho']['combo']['qtde_bordas']>0)&&($cod_bordas != "N"))             
        {    
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_combo'] = '1';
            $_SESSION['ipi_carrinho']['combo']['qtde_bordas'] = $_SESSION['ipi_carrinho']['combo']['qtde_bordas'] - 1;
        }

        if ($cod_bordas != "N")
        {
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_id_sessao'] = $this->gerar_id_sessao();
        }
        if ($cod_adicionais != "N")
        {
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['adicional_id_sessao'] = $this->gerar_id_sessao();
        }
        
        /// ######### Pode ser que esteja em branco porque veio via repetir pedido, quando ainda não tinha opção de Tipo de Massa ou Opção de Corte, ESTUDAR O CASO
        /// ######### Existem 4 carrinhos (ipi_carrinho_xxx) pode ser que um deles esteja faltando algo
        // FIXME Descobrir pelo amor....porque isso que dah pau na impressão!!!!!!! prioridade máxima!!!!!!!
        if(($cod_tipo_massa <= 0) || ($cod_tipo_massa == ''))
        {
            $cod_tipo_massa = 2;
        }
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_tipo_massa'] = $cod_tipo_massa;


        /// ######### Pode ser que esteja em branco porque veio via repetir pedido, quando ainda não tinha opção de Tipo de Massa ou Opção de Corte, ESTUDAR O CASO
        /// ######### Existem 4 carrinhos (ipi_carrinho_xxx) pode ser que um deles esteja faltando algo
        // FIXME Descobrir pelo amor....porque isso que dah pau na impressão!!!!!!! prioridade máxima!!!!!!!
        if(($cod_opcoes_corte <= 0) || ($cod_opcoes_corte == ''))
        {
            $cod_opcoes_corte = 2;
        }
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_opcoes_corte'] = $cod_opcoes_corte;


        $cod_pizzarias = $this->retornar_codigo_pizzaria();
        
        if ($id_combo!='')
        {
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_combo'] = '1';
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['id_combo'] = $id_combo;
            if($cod_combo!='')
            {
              $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_combos'] = $cod_combo;
            }
            else
            {
              $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_combos'] = $_SESSION['ipi_carrinho']['combo']['cod_combos'];
            }
        }
        else
        {
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_combo'] = '0';
        }
    
        
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['quant_fracao'] = $quant_fracao;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_promocional'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_fidelidade'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['ind_ses_pizza_promo'] = 'N';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['ind_ses_bebida_promo'] = 'N';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['ind_ses_pizza_pai'] = $cpp;
        $conexao = conectabd();
        
        $cupom_valido = "0";
        
        if ($validar_cupom == "1")
        {
            $sqlCupom = "SELECT * FROM ipi_cupons  WHERE cupom = '" . $numero_cupom . "'";
            $resCupom = mysql_query($sqlCupom);
            $objCupom = mysql_fetch_object($resCupom);
            $cupom_valido = $objCupom->valido;
        }
        
        if ($cod_bordas != "N")
        {
            
            // PROMOCAO 3: Toda a terça-feira borda grátis
            // if(!isset($_SESSION['ipi_carrinho']['combo']))
            // {
            //     $sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '14' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
            //     $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
                    
            //     //  $obj_buscar_promocoes = mysql_fetch_object($res_buscar_promocoes);
            //     if(mysql_num_rows($res_buscar_promocoes)>0 && $cod_tamanhos==3)
            //     {
            //       $borda_promo = 1;
            //       $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_promo_cod'] = '14';
                    
            //     }
            //     else
            //     {
            //       if (date('w') == 2)
            //       {
            //         $sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '12' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
            //         $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
                        
            //         //  $obj_buscar_promocoes = mysql_fetch_object($res_buscar_promocoes);
            //         if(mysql_num_rows($res_buscar_promocoes)>0)
            //         {
            //           $borda_promo = 1;
            //           $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_promo_cod'] = '2';
                        
            //         }
            //       }
            //     }
            // }
            $sqlAux = "select preco from ipi_tamanhos_ipi_bordas where cod_bordas = $cod_bordas and cod_tamanhos = $cod_tamanhos and cod_pizzarias = $cod_pizzarias";
            //$sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_tamanhos=" . $cod_tamanhos . " AND cod_bordas=" . $cod_bordas;
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            if ($cupom_valido == "1")
            {
                $objAux->preco = 0;
                $_SESSION['ipi_carrinho']['pergunta_cupom'] = "Respondida";
                $_SESSION['ipi_carrinho']['cupom'] = $numero_cupom;
                $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_cupom']=1;
            }
            if($borda_promo == "1")
            {
                $objAux->preco = 0;
            }
            $total_pedido += $objAux->preco;
            $preco_pizza = $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'];
            $preco_pizza += $objAux->preco;
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'] = $preco_pizza;
        }
        
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_promocional'] = ($cupom_valido ? $cupom_valido : ($borda_promo ? $borda_promo : '') );
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_fidelidade'] = '0';
        
        if ($cod_adicionais != "N")
        {
            $sqlAux = "select preco from ipi_tamanhos_ipi_adicionais where cod_adicionais = $cod_adicionais and cod_tamanhos = $cod_tamanhos and cod_pizzarias = $cod_pizzarias";
            //echo $sqlAux."<br/>";
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);

            $preco_pizza = $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'];
            $preco_pizza += $objAux->preco;
            //echo "pp<br/><br/><br/>aa".$_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza']."<br/>";
            //echo $objAux->preco;
            //die();
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'] = $preco_pizza;

            $total_pedido += $objAux->preco;
        }
        
        $sqlAux = "SELECT * FROM ipi_fracoes f INNER JOIN ipi_tamanhos_ipi_fracoes tf ON (tf.cod_fracoes=f.cod_fracoes) WHERE tf.cod_tamanhos='" . $cod_tamanhos . "' AND f.fracoes='" . $quant_fracao. "' AND tf.cod_pizzarias = '".$cod_pizzarias."'";
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        $total_pedido += $objAux->preco;
        
        // PROMOCAO 1: Se for segunga-feira, na compra de uma pizza (cod_tamanhos = 3) ganhe uma quadradinha
        // Felipe:> Retirado para dar espaço para escolha da pizza vide página pedido_promocional 

        //if((date('w') == 1) && ($cod_tamanhos == 3) && ($numero_cupom == 0) && ($_SESSION['ipi_carrinho']['promocao']['promocao_1'] != '1')) {
     // PROMOÇÃO NA COMPRA DE PIZZA 4 FATIAS GANHA UM KUAT LATA \\
        if(($cod_tamanhos == 2) && ($numero_cupom == 0)) 
        {
            $sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '2' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
            $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);

            if (mysql_num_rows($res_buscar_promocoes)>0)
            {
                 if (!isset($_SESSION['ipi_carrinho']['promocao']['promocao_2']))
                {
                    $this->adicionar_bebida_promocional(37);
                }
            }
            
           
                            
                           
        }

        // PROMOÇÃO NA COMPRA DE PIZZA GIGANTE GANHA UM KUAT 2L \\
        if( (date('w') ==1 || date('w') ==2 || date('w') ==3 || date('w') ==4 ) && ($cod_tamanhos == 4) && ($numero_cupom == 0)) 
        {
             $sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '1' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
            $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);

            if (mysql_num_rows($res_buscar_promocoes)>0)
            {
                        if (!isset($_SESSION['ipi_carrinho']['promocao']['promocao_1']))
                        {
                            $this->adicionar_bebida_promocional(56);
                        }                
            }

                            
                           
        }
        
        
        // PROMOCAO 4: Se for balcão e quantidade de pizza >= 2, refri grátis
        //if(($_SESSION['ipi_carrinho']['buscar_balcao'] == 'Balcão') && ($_SESSION['ipi_carrinho']['promocao']['promocao_4'] != '1') && (count($_SESSION['ipi_carrinho']['pedido']) >= 2)) {
        //  // ATENCAO: Configurar o $cod_bebidas_ipi_conteudos de acordo com a bebida selecionada para promocao -> esta tem que ser guaraná kuat
        //  $this->adicionar_bebida_promocional(1);
        //  $_SESSION['ipi_carrinho']['promocao']['promocao_4'] = '1';
        //}
        

        //log($conexao);
        

        desconectabd($conexao);
        
        $_SESSION['ipi_carrinho']['total_pedido'] = $total_pedido;
        return $proximo_indice;
    }
    
    /**
     * Função que cria uma pizza na sessão e retorna o o indice da pizza na sessão.
     */
    public function adicionar_pizza_promocional ($cod_tamanhos, $cod_pizzas, $num_cupom = '')
    {
        $cod_adicionais = "N";
        $cod_bordas = "N";
        $quant_fracao = 1;
        $proximo_indice = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        $total_pedido = isset($_SESSION['ipi_carrinho']['total_pedido']) ? $_SESSION['ipi_carrinho']['total_pedido'] : 0;
        
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_combo'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_combo'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_tamanhos'] = $cod_tamanhos;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_id_sessao'] = $this->gerar_id_sessao();
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_adicionais'] = $cod_adicionais;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_bordas'] = $cod_bordas;

        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['quant_fracao'] = $quant_fracao;
        
        // FIXME Alterar isso para poder escolher no cupom!
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_tipo_massa'] = 2;

        // O corte será o padrão da pizza
        $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_opcoes_corte toc INNER JOIN ipi_opcoes_corte oc ON (toc.cod_opcoes_corte=oc.cod_opcoes_corte) WHERE toc.cod_tamanhos=" . $cod_tamanhos . " AND tamanho_padrao = 1 ORDER BY oc.opcao_corte";
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_opcoes_corte'] = $objAux->cod_opcoes_corte;
        
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['fracao'][0]['indice_ses_pizza'] = $proximo_indice;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['fracao'][0]['cod_pizzas'] = $cod_pizzas;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['fracao'][0]['num_fracao'] = 1;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_promocional'] = 1;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_fidelidade'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_promocional'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_fidelidade'] = '0';
        
        if($num_cupom != '')
            {
                $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_cupom']=1;
            $_SESSION['ipi_carrinho']['pergunta_cupom'] = "Respondida";
            $_SESSION['ipi_carrinho']['cupom'] = $num_cupom;
            }
        
        $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_pizzas WHERE cod_pizzas=" . $cod_pizzas;
        $resAux = mysql_query($sqlAux);
        $linAux = mysql_num_rows($resAux);
        
        if ($linAux > 0)
        {
            for ($a = 0; $a < $linAux; $a++)
            {
                $objAux = mysql_fetch_object($resAux);
                $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['fracao'][0]['ingredientes'][$a]['cod_ingredientes'] = $objAux->cod_ingredientes;
                $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['fracao'][0]['ingredientes'][$a]['ingrediente_padrao'] = true;
            }
        }
        
        $_SESSION['ipi_carrinho']['total_pedido'] = $total_pedido;
        return $proximo_indice;
    }
    
    public function adicionar_pizza_promocional_escolha ($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $quant_fracao, $cod_opcoes_corte, $codPizzaPai, $codCupom)
    {
        $proximo_indice = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        $total_pedido = isset($_SESSION['ipi_carrinho']['total_pedido']) ? $_SESSION['ipi_carrinho']['total_pedido'] : 0;
        
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_combo'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_combo'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_tamanhos'] = $cod_tamanhos;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_id_sessao'] = $this->gerar_id_sessao();
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_adicionais'] = $cod_adicionais;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_bordas'] = $cod_bordas;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['quant_fracao'] = $quant_fracao;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_opcoes_corte'] = $cod_opcoes_corte; // Ver anotações na função adicionar_pizza()

        $preco_pizza = $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'];
        
        if($codCupom != '')
            {
                $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_cupom']=1;
            $_SESSION['ipi_carrinho']['pergunta_cupom'] = "Respondida";
            }

        // Como o método é usado tanto para pizza promocional e pelo cupom, se houver cod_tipo_massa aceita-o, caso contrário, força para Massa Padrão.
        // OBS: Alterar isso para poder escolher no cupom!
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['cod_tipo_massa'] = ($cod_tipo_massa ? $cod_tipo_massa : 2);
        
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_promocional'] = '1';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_fidelidade'] = '0';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['ind_ses_pizza_promo'] = "N";
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['ind_ses_bebida_promo'] = 'N';
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['ind_ses_pizza_pai'] = "N";
        

        $cod_pizzarias = $this->retornar_codigo_pizzaria();
        $conexao = conectabd();

        // Setar o pai desta pizza promocional 
        if ($codPizzaPai)
        {
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['ind_ses_pizza_promo'] = "$codPizzaPai";

            $dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S\E1b');

            $sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '15' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
            $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);

            $sql_buscar_promocoes_indaiatuba = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '18' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
            $res_buscar_promocoes_indaiatuba = mysql_query($sql_buscar_promocoes_indaiatuba);
                    
            if(mysql_num_rows($res_buscar_promocoes)>0)
            {
              $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_promo_cod'] = "15";
            }
            elseif(mysql_num_rows($res_buscar_promocoes_indaiatuba)>0)
            {
                $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_promo_cod'] = "18";
            }
            elseif($dia_semana[date("w")]=='Seg')//trava para garantir q eh a promo de segunda
            {
              $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['pizza_promo_cod'] = "1";
            }
        }
        
        

        if ($cod_bordas != "N")
        {
            
            // PROMOCAO 3: Toda a terça-feira borda grátis
            if (date('w') == 2)
            {
                $sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '2' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
                $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
                    
                //  $obj_buscar_promocoes = mysql_fetch_object($res_buscar_promocoes);
                if(mysql_num_rows($res_buscar_promocoes)>0)
                {
                    $borda_promo = 1;
                    $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_promo_cod'] = '2';
                }
            }
            $sqlAux = "select preco from ipi_tamanhos_ipi_bordas where cod_bordas = $cod_bordas and cod_tamanhos = $cod_tamanhos and cod_pizzarias = $cod_pizzarias";
            //$sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_tamanhos=" . $cod_tamanhos . " AND cod_bordas=" . $cod_bordas;
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            if ($cupom_valido == "1")
            {
                $objAux->preco = 0;
                $_SESSION['ipi_carrinho']['pergunta_cupom'] = "Respondida";
                $_SESSION['ipi_carrinho']['cupom'] = $numero_cupom;
                $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_cupom']=1;
            }
            if($borda_promo == "1")
            {
                $objAux->preco = 0;
            }
            $total_pedido += $objAux->preco;
            $preco_pizza = $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'];
            $preco_pizza += $objAux->preco;
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'] = $preco_pizza;
        }
        
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_promocional'] = ($cupom_valido ? $cupom_valido : ($borda_promo ? $borda_promo : '') );
        
        /*if ($cod_bordas != "N")
        {
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_id_sessao'] = $this->gerar_id_sessao();
            

            $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_tamanhos=" . $cod_tamanhos . " AND cod_bordas=" . $cod_bordas."and cod_pizzarias = $cod_pizzarias";
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            $total_pedido += $objAux->preco;
            $preco_pizza +=$objAux->preco;
        }*/
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['borda_fidelidade'] = '0';
        
        if ($cod_adicionais != "N")
        {
            $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['adicional_id_sessao'] = $this->gerar_id_sessao();
            
            $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_adicionais WHERE cod_tamanhos=" . $cod_tamanhos . " AND cod_adicionais=" . $cod_adicionais." and cod_pizzarias = $cod_pizzarias";
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux);
            $total_pedido += $objAux->preco;
            $preco_pizza +=$objAux->preco;
        }
        
        $sqlAux = "SELECT * FROM ipi_fracoes f INNER JOIN ipi_tamanhos_ipi_fracoes tf ON (tf.cod_fracoes=f.cod_fracoes) WHERE tf.cod_tamanhos='" . $cod_tamanhos . "' AND f.fracoes='" . $quant_fracao. "' AND tf.cod_pizzarias = '".$cod_pizzarias."'";
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        $total_pedido += $objAux->preco;
        $preco_pizza +=$objAux->preco;
        
        desconectabd($conexao);
        
        if($_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"]==1)
        {
          $_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"] = 0;
          $_SESSION["ipi_carrinho"]['promocao']["promocao12_indice"] = (isset($_SESSION["ipi_carrinho"]['promocao']["promocao12_indice"]) ? $_SESSION["ipi_carrinho"]['promocao']["promocao12_indice"]+1 : 1);
          $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['promocao12_indice'] = $_SESSION["ipi_carrinho"]['promocao']["promocao12_indice"];

          $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
          if ($numero_pizzas > 0)
          {
            for ($a = 0; $a < $numero_pizzas; $a++)
            {
              if ($_SESSION['ipi_carrinho']['pedido'][$a]['promocao12_ativa']==1)//a variavel ativa, funciona como se fosse 'pendente', assim, depois que eu associar, eu removo-a para não deixala pendente
              {
                $indice_sessao_pai = $a;
                $_SESSION['ipi_carrinho']['pedido'][$indice_sessao_pai]['promocao12_indice'] = $_SESSION["ipi_carrinho"]['promocao']["promocao12_indice"];
                unset($_SESSION['ipi_carrinho']['pedido'][$indice_sessao_pai]['promocao12_ativa']);
                //break;
              }
            }
              
          }

            unset($_SESSION['ipi_carrinho']['promocao']['promocao12_cont']);
        }

        $_SESSION['ipi_carrinho']['total_pedido'] = $total_pedido;
        $_SESSION['ipi_carrinho']['pedido'][$proximo_indice]['preco_pizza'] =  $preco_pizza;
        return $proximo_indice;
    }
    
    
    public function remover_pizza_promocional($id_sessao_pizza_pai)
    {
        $id_sessao_exclusao = -1;
        
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        if ($numero_pizzas > 0)
        {
            for ($a = 0; $a < $numero_pizzas; $a++)
            {
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['ind_ses_pizza_promo']==$id_sessao_pizza_pai)
                {
                    $id_sessao_exclusao = $a;
                    break;
                }
            }
            
        }
        
        if ($id_sessao_exclusao!=-1)
        {
            unset($_SESSION['ipi_carrinho']['pedido'][$id_sessao_exclusao]);
            if (count($_SESSION['ipi_carrinho']['pedido'])>0)
            {
                $arr_novos_indices = range (0, (count($_SESSION['ipi_carrinho']['pedido']) - 1));
                $_SESSION['ipi_carrinho']['pedido'] = array_combine ($arr_novos_indices, $_SESSION['ipi_carrinho']['pedido']);
            }
        }

        $this->calcular_desconto_fidelidade();
        $_SESSION['ipi_carrinho']['total_pedido'] = $this->exibir_total();
    }

    public function remover_pizza_sugestao_vinculada($id_sessao_pizza_pai)
    {
        $id_sessao_exclusao = -1;
        
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        if ($numero_pizzas > 0)
        {
            for ($a = 0; $a < $numero_pizzas; $a++)
            {
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['ind_ses_pizza_pai']==$id_sessao_pizza_pai)
                {
                    $id_sessao_exclusao = $a;
                    break;
                }
            }
            
        }
        
        if ($id_sessao_exclusao!=-1)
        {
            unset($_SESSION['ipi_carrinho']['pedido'][$id_sessao_exclusao]);
            if (count($_SESSION['ipi_carrinho']['pedido'])>0)
            {
                $arr_novos_indices = range (0, (count($_SESSION['ipi_carrinho']['pedido']) - 1));
                $_SESSION['ipi_carrinho']['pedido'] = array_combine ($arr_novos_indices, $_SESSION['ipi_carrinho']['pedido']);
            }
        }

        $this->calcular_desconto_fidelidade();
        $_SESSION['ipi_carrinho']['total_pedido'] = $this->exibir_total();
    }
    
    public function remover_bebida_promocional($id_sessao_pizza_pai)
    {
        $id_sessao_exclusao = -1;
        
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
        if ($numero_pizzas > 0)
        {
            for ($a = 0; $a < $numero_pizzas; $a++)
            {
                if ($_SESSION['ipi_carrinho']['bebida'][$a]['ind_ses_pizza_promo']==$id_sessao_pizza_pai)
                {
                    $id_sessao_exclusao = $a;
                    break;
                }
            }
            
        }
        
        if ($id_sessao_exclusao!=-1)
        {
            unset($_SESSION['ipi_carrinho']['bebida'][$id_sessao_exclusao]);
            if (count($_SESSION['ipi_carrinho']['bebida'])>0)
            {
                $arr_novos_indices = range (0, (count($_SESSION['ipi_carrinho']['bebida']) - 1));
                $_SESSION['ipi_carrinho']['bebida'] = array_combine ($arr_novos_indices, $_SESSION['ipi_carrinho']['bebida']);
            }
        }

        $this->calcular_desconto_fidelidade();
        $_SESSION['ipi_carrinho']['total_pedido'] = $this->exibir_total();
    }
    
    
    public function remover_pizza ($cod_pizza_sessao)
    {
       
        $id_sessao_exclusao = 0;
        
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;

        if ($numero_pizzas > 0)
        {
            for ($a = 0; $a < $numero_pizzas; $a++)
            {
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_id_sessao']==$cod_pizza_sessao)
                {
                    $id_sessao_exclusao = $a;
                    break;
                }
            }
            
        }
                
        if ( ($_SESSION['ipi_carrinho']['pedido'][$id_sessao_exclusao]['pizza_cupom']=="1") || ($_SESSION['ipi_carrinho']['pedido'][$id_sessao_exclusao]['bebida_cupom']=="1") || ($_SESSION['ipi_carrinho']['pedido'][$id_sessao_exclusao]['borda_cupom']=="1") )
        {
            unset($_SESSION['ipi_carrinho']['cupom']);
            unset($_SESSION['ipi_carrinho']['pergunta_cupom']);
        }
            
                if($_SESSION["ipi_carrinho"]["pizza_promocional"] == "sim" && $_SESSION["ipi_carrinho"]["pizza_promocional_pai"]==$cod_pizza_sessao)
                {
                        unset($_SESSION["ipi_carrinho"]["pizza_promocional"]);
                        unset($_SESSION["ipi_carrinho"]["pizza_promocional_pai"]);
                }
        
        if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']))
        {
          $indice_promocao = $_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice'];

          for ($v = 0; $v < $numero_pizzas; $v++)//procura a pizza que esta 'V'inculada com ela
          {
            if($v!=$a)
            {
              if ($_SESSION['ipi_carrinho']['pedido'][$v]['promocao9_indice']==$indice_promocao)
              {
                $indice_sessao_vinculada = $v;
                break;
              }
            }
          }

          if(isset($_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]))
          {
            for ($p = 0; $p < $numero_pizzas; $p++)//procura a pizza que esta 'P'endente
            {
              if ($_SESSION['ipi_carrinho']['pedido'][$p]['promocao9_ativa']==1)
              {
                $indice_sessao_pendente = $p;
                break;
              }
            }

            unset($_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]);
            unset($_SESSION['ipi_carrinho']['pedido'][$p]['promocao9_ativa']);
            $_SESSION['ipi_carrinho']['pedido'][$v]['promocao9_indice'] = $indice_promocao;
            $_SESSION['ipi_carrinho']['pedido'][$p]['promocao9_indice'] = $indice_promocao;

          }
          else
          {
            $indice_sessao_condições = '';
            for ($c = 0; $c < $numero_pizzas; $c++)//Procura uma pizza nas "C"ondiçoes para vincular (fiquei sem ideia para a letra kkkk)
            {
              if((!isset($_SESSION['ipi_carrinho']['pedido'][$c]['promocao9_indice'])) && $_SESSION['ipi_carrinho']['pedido'][$c]['cod_tamanhos']==3)
              {
                $fracao_doce = $this->verificar_pizza_doce("fracao",$_SESSION['ipi_carrinho']['pedido'][$c]['fracao'][0]['cod_pizzas'],$_SESSION['ipi_carrinho']['pedido'][$c]['fracao'][1]['cod_pizzas'],$_SESSION['ipi_carrinho']['pedido'][$c]['fracao'][2]['cod_pizzas'],$_SESSION['ipi_carrinho']['pedido'][$c]['fracao'][3]['cod_pizzas']);


                if (!$fracao_doce)
                {
                  $indice_sessao_condições = $c;
                  break;
                }
              }
            }

            if($indice_sessao_condições!="")
            {
              $_SESSION['ipi_carrinho']['pedido'][$v]['promocao9_indice'] = $indice_promocao;
              $_SESSION['ipi_carrinho']['pedido'][$c]['promocao9_indice'] = $indice_promocao;
            }
            else
            {
              unset($_SESSION['ipi_carrinho']['pedido'][$v]['promocao9_indice']);
              $_SESSION['ipi_carrinho']['pedido'][$v]['promocao9_ativa'] = 1;
              $_SESSION["ipi_carrinho"]['promocao']["cod_promocao"] = 9;
            }
          }

        }

        if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao12_indice']))
        {
          $indice_promocao = $_SESSION['ipi_carrinho']['pedido'][$a]['promocao12_indice'];

          $excluida = '';

          if($_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos']==4)
          {
            $excluida = 'promocional';//a pizza excluida foi a promocional
            unset($_SESSION['ipi_carrinho']['pedido'][$v]['promocao12_indice']);
          }

          for ($v = 0; $v < $numero_pizzas; $v++)//procura a pizza que esta 'V'inculada com ela
          {
            if($v!=$a)
            {
              if ($_SESSION['ipi_carrinho']['pedido'][$v]['promocao12_indice']==$indice_promocao)
              {
                $indice_sessao_vinculada = $v;

                if($excluida=='promocional')//se excluir promocional, as pizzas ficam com a promo desativada (não servem para vinculo)
                {
                  unset($_SESSION['ipi_carrinho']['pedido'][$v]['promocao12_indice']);
                  $_SESSION['ipi_carrinho']['pedido'][$v]['promocao12_ativa'] = 1;
                  $_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"] = 1;
                }
                else
                {
                  if(isset($_SESSION['ipi_carrinho']['promocao']['promocao12_cont'])  && ($_SESSION['ipi_carrinho']['pedido'][$v]['cod_tamanhos']!=4))/*&& ($_SESSION['ipi_carrinho']['promocao']['promocao12_cont']==1)*/
                  {
                    unset($_SESSION['ipi_carrinho']['pedido'][$v]['promocao12_indice']);
                    //$_SESSION['ipi_carrinho']['promocao']['promocao12_cont'] = $_SESSION['ipi_carrinho']['promocao']['promocao12_cont'] +1;
                    //$_SESSION['ipi_carrinho']['promocao']['promocao12_id_2'] = $id_sessao_pai;
                    $_SESSION['ipi_carrinho']['pedido'][$v]['promocao12_ativa'] = 1;
                    $_SESSION['ipi_carrinho']['promocao']['promocao12_cont'] = $_SESSION['ipi_carrinho']['promocao']['promocao12_cont'] +1;
                    $_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"] = 1;
                  }
                  else
                  {
                    if($_SESSION['ipi_carrinho']['pedido'][$v]['cod_tamanhos']==4)
                    {
                      unset($_SESSION['ipi_carrinho']['pedido'][$v]);
                    }
                    else
                    {
                      unset($_SESSION['ipi_carrinho']['pedido'][$v]['promocao12_indice']);
                      $_SESSION['ipi_carrinho']['pedido'][$v]['promocao12_ativa'] = 1;
                      $_SESSION['ipi_carrinho']['promocao']['promocao12_cont'] = 1;
                    }
                  }


    /*              if($_SESSION["ipi_carrinho"]['promocao']["promocao12_ativa"]==1)
                  {
                    $acao = 'ir_promocao'; 
                    $promocao_cod = 12;
                    $pizza_pai = $id_sessao_pai;
                  }*/
                }
                
              }
            }
          }
        }

        if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao17_indice']))
        {
          $indice_promocao = $_SESSION['ipi_carrinho']['pedido'][$a]['promocao17_indice'];

          for ($v = 0; $v < $numero_pizzas; $v++)//procura a pizza que esta 'V'inculada com ela
          {
            if($v!=$a)
            {
              if ($_SESSION['ipi_carrinho']['pedido'][$v]['promocao17_indice']==$indice_promocao)
              {
                $indice_sessao_vinculada = $v;
                break;
              }
            }
          }

          unset($_SESSION['ipi_carrinho']['pedido'][$v]['promocao17_indice']);
          if(isset($_SESSION['ipi_carrinho']['pedido'][$v]['promocao17_doce']))
          {
            unset($_SESSION['ipi_carrinho']['pedido'][$v]['promocao17_doce']);
          }
          
          /*if(isset($_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]))
          {
            for ($p = 0; $p < $numero_pizzas; $p++)//procura a pizza que esta 'P'endente
            {
              if ($_SESSION['ipi_carrinho']['pedido'][$p]['promocao17_ativa']==1)
              {
                $indice_sessao_pendente = $p;
                break;
              }
            }

            unset($_SESSION["ipi_carrinho"]['promocao']["cod_promocao"]);
            unset($_SESSION['ipi_carrinho']['pedido'][$p]['promocao17_ativa']);
            $_SESSION['ipi_carrinho']['pedido'][$v]['promocao17_indice'] = $indice_promocao;
            $_SESSION['ipi_carrinho']['pedido'][$p]['promocao17_indice'] = $indice_promocao;

          }
          else
          {
            $indice_sessao_condições = '';
            for ($c = 0; $c < $numero_pizzas; $c++)//Procura uma pizza nas "C"ondiçoes para vincular (fiquei sem ideia para a letra kkkk)
            {
              if((!isset($_SESSION['ipi_carrinho']['pedido'][$c]['promocao17_indice'])) && $_SESSION['ipi_carrinho']['pedido'][$c]['cod_tamanhos']==3)
              {
                $fracao_doce = $this->verificar_pizza_doce("inteira",$_SESSION['ipi_carrinho']['pedido'][$c]['fracao'][0]['cod_pizzas'],$_SESSION['ipi_carrinho']['pedido'][$c]['fracao'][1]['cod_pizzas'],$_SESSION['ipi_carrinho']['pedido'][$c]['fracao'][2]['cod_pizzas'],$_SESSION['ipi_carrinho']['pedido'][$c]['fracao'][3]['cod_pizzas']);


                if ($fracao_doce)
                {
                  $indice_sessao_condições = $c;
                  break;
                }
              }
            }

            if($indice_sessao_condições!="")
            {
              $_SESSION['ipi_carrinho']['pedido'][$v]['promocao17_indice'] = $indice_promocao;
              $_SESSION['ipi_carrinho']['pedido'][$c]['promocao17_indice'] = $indice_promocao;
            }
            else
            {
              unset($_SESSION['ipi_carrinho']['pedido'][$v]['promocao17_indice']);
              $_SESSION['ipi_carrinho']['pedido'][$v]['promocao17_ativa'] = 1;
              $_SESSION["ipi_carrinho"]['promocao']["cod_promocao"] = 17;
            }
          }*/

        }

        unset($_SESSION['ipi_carrinho']['pedido'][$id_sessao_exclusao]);
        if (count($_SESSION['ipi_carrinho']['pedido'])>0)
        {
            $arr_novos_indices = range (0, (count($_SESSION['ipi_carrinho']['pedido']) - 1));
            $_SESSION['ipi_carrinho']['pedido'] = array_combine ($arr_novos_indices, $_SESSION['ipi_carrinho']['pedido']);
        }

        
        //echo "<br>ind2: ".$cod_pizza_sessao;
        $this->remover_pizza_sugestao_vinculada($cod_pizza_sessao);
        $this->remover_pizza_promocional($cod_pizza_sessao);
        $this->remover_bebida_promocional($cod_pizza_sessao);
        
        $this->verificar_promocao_quadrada_balcao();
        
        
        $this->calcular_desconto_fidelidade();
        $_SESSION['ipi_carrinho']['total_pedido'] = $this->exibir_total();
    
    }
    
        
    public function remover_bebida ($cod_bebida_sessao)
    {
        if($_SESSION['ipi_carrinho']['bebida'][$cod_bebida_sessao]['bebida_cupom'] == 1)
        {
            unset ($_SESSION['ipi_carrinho']['cupom']);
            unset($_SESSION['ipi_carrinho']['pergunta_cupom']);
        }
        
        unset($_SESSION['ipi_carrinho']['bebida'][$cod_bebida_sessao]);
        
        if (count($_SESSION['ipi_carrinho']['bebida']))
        {
            $carrinho_chaves = range(0, (count($_SESSION['ipi_carrinho']['bebida']) - 1));
            $_SESSION['ipi_carrinho']['bebida'] = array_combine($carrinho_chaves, $_SESSION['ipi_carrinho']['bebida']);
        }
        $this->calcular_desconto_fidelidade();
        $_SESSION['ipi_carrinho']['total_pedido'] = $this->exibir_total();
    }
    
    /**
     * Função que cria uma fração com o indice da pizza passado como paramentro.
     */
    public function adicionar_fracao ($indice_ses_pizza, $cod_pizzas, $num_fracao)
    {
        $total_pedido = isset($_SESSION['ipi_carrinho']['total_pedido']) ? $_SESSION['ipi_carrinho']['total_pedido'] : 0;
        
        $proximo_indice = isset($_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao']) ? count($_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao']) : 0;
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$proximo_indice]['indice_ses_pizza'] = $indice_ses_pizza;
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$proximo_indice]['cod_pizzas'] = $cod_pizzas;
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$proximo_indice]['num_fracao'] = $num_fracao;
        
        $conexao = conectabd();
        $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE pt.cod_tamanhos=" . $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['cod_tamanhos'] . " AND p.cod_pizzas=" . $cod_pizzas;
        //echo $sqlAux;
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        $total_pedido += ($objAux->preco / $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['quant_fracao']);
        $preco_pizza = $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['preco_pizza'];
        $preco_pizza += ($objAux->preco / $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['quant_fracao']);
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['preco_pizza'] = $preco_pizza;
        desconectabd($conexao);
        
        $_SESSION['ipi_carrinho']['total_pedido'] = $total_pedido;
        
        return $proximo_indice;
    }
    
    /**
     * Função que verifica se o pedido tem ou não duas pizzas quadradas
     */
    public function verificar_promocao_quadrada_balcao()
    {
        if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
        {
            if(count($_SESSION['ipi_carrinho']['pedido'])<2)
            {
                $ind_ses_bebida = $_SESSION['ipi_carrinho']['promocao']['promocao_4'];
                $this->remover_bebida($ind_ses_bebida); 
                unset($_SESSION['ipi_carrinho']['promocao']['promocao_4']);
            }
        }
    }
    
    /**
    * Função que verifica se a pizza toda é doce
    * Tipo ira receber fração, ou inteira
    * ou seja, verifica se tem fracao doce ou eh inteira doce
    */
    public function verificar_pizza_doce($tipo,$fracao1='0',$fracao2='0',$fracao3='0',$fracao4='0')
    {
      $con = conectabd();
      $lin = 0;
      $cont = 0;
      
      if($fracao1!='0')
      {
        $cont++;
        $sql_verifica = "select * from ipi_pizzas where cod_pizzas in(".$fracao1.") and tipo= 'Doce'";
        $res_verifica = mysql_query($sql_verifica);
        if(mysql_num_rows($res_verifica)>0)
        {
          $lin ++;
        }
      }
      
      if($fracao2!='0')
      {
        $cont++;
        $sql_verifica = "select * from ipi_pizzas where cod_pizzas in(".$fracao2.") and tipo= 'Doce'";
        $res_verifica = mysql_query($sql_verifica);
        if(mysql_num_rows($res_verifica)>0)
        {
          $lin ++;
        }
      }
      
      if($fracao3!='0')
      {
        $cont++;
        $sql_verifica = "select * from ipi_pizzas where cod_pizzas in(".$fracao3.") and tipo= 'Doce'";
        $res_verifica = mysql_query($sql_verifica);
        if(mysql_num_rows($res_verifica)>0)
        {
          $lin ++;
        }
      }
      
      if($fracao4!='0')
      {
        $cont++;
        $sql_verifica = "select * from ipi_pizzas where cod_pizzas in(".$fracao4.") and tipo= 'Doce'";
        $res_verifica = mysql_query($sql_verifica);
        if(mysql_num_rows($res_verifica)>0)
        {
          $lin ++;
        }
      }
      desconectar_bd($con);
      if($tipo=="fracao")
      {
        if($lin>=1)
        {
          return true;
        }
        else
        {
          return false;
        }
      }
      else
        //if($tipo=="inteira")
        {
          if($lin==$cont)
          {
            return true;
          }
          else
          {
            return false;
          }
        }
    }

    /**
     * Função que verifica se a pizza toda é doce e quadrada para dar o refrigerante
     */
    public function verificar_promocao_pizza_doce($indice_ses_pizza,$trocar,$fracao1,$fracao2,$fracao3,$fracao4,$trocar_cod)
    {
      $resposta = false;
      $inteira_doce = $this->verificar_pizza_doce("inteira",$fracao1,$fracao2,$fracao3,$fracao4);
      $cod_pizzarias = $this->retornar_codigo_pizzaria();
      $con = conectabd();
      $sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '7' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
      $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);

      $sql_buscar_promocoes_doce = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '3' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
      $res_buscar_promocoes_doce = mysql_query($sql_buscar_promocoes_doce);

      if((mysql_num_rows($res_buscar_promocoes)>0) || ($inteira_doce && mysql_num_rows($res_buscar_promocoes_doce)>0 ) )
      {
        $cod_promo = 19;
        if($inteira_doce)
        {
          $cod_promo =9;
        }
        if (($_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['cod_tamanhos'] == 3))
          {
            $resposta = true;
            if($trocar=='1')
            {
              $ind_ses_bebida = $this->adicionar_bebida_promocao_balcao($trocar_cod, $cod_promo); //1 cod_bebidas_ipi_conteudos da coca-cola de 2 litros e 9 é a promoção de pizza doce
              $_SESSION['ipi_carrinho']['bebida'][$ind_ses_bebida]['ind_ses_pizza_promo'] = $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['pizza_id_sessao'];
              $_SESSION['ipi_carrinho']['promocao']['promocao_3'] = '1';
            }
            else
            {
              // ATENCAO: Configurar o $cod_bebidas_ipi_conteudos de acordo com a bebida selecionada para promocao
              $ind_ses_bebida = $this->adicionar_bebida_promocao_balcao(5, $cod_promo); //Indice_ses_pizza, não é o id_sessao 
              
              $_SESSION['ipi_carrinho']['bebida'][$ind_ses_bebida]['ind_ses_pizza_promo'] = $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['pizza_id_sessao'];

              $_SESSION['ipi_carrinho']['promocao']['promocao_3'] = '1';
            }
          }
      }

            desconectar_bd($con);
        return $resposta;
    }
    
    /**
     * Função que adicionar um ingrediente a uma fracao da pizza pelo indice da fracao passado como parametro.
     */
    public function adicionar_ingrediente ($indice_ses_pizza, $indice_ses_fracao, $cod_ingredientes, $ingrediente_padrao, $ingrediente_troca='0', $cod_ingredientes_troca='')
    {
        $total_pedido = isset($_SESSION['ipi_carrinho']['total_pedido']) ? $_SESSION['ipi_carrinho']['total_pedido'] : 0;

        $preco_pizza = $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['preco_pizza'];

        $proximo_indice = isset($_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes']) ? count($_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes']) : 0;
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['cod_ingredientes'] = $cod_ingredientes;
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['cod_ingredientes_troca'] = $cod_ingredientes_troca;
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['ingrediente_padrao'] = $ingrediente_padrao;
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['ingrediente_troca'] = $ingrediente_troca;
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['ingrediente_id_sessao'] = $this->gerar_id_sessao();
        
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['ingrediente_promocional'] = 0;
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['ingrediente_fidelidade'] = 0;
        
        if ($ingrediente_padrao == false)
        {
            $cod_pizzarias = $this->retornar_codigo_pizzaria();
            $conexao = conectabd();
            $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_tamanhos=" . $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['cod_tamanhos'] . " AND it.cod_ingredientes=" . $cod_ingredientes;
            //echo $sqlAux;
            $resAux = mysql_query($sqlAux);
            $objAux = mysql_fetch_object($resAux); 

            $dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
            if($_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['cod_tamanhos']=='3' && $dia_semana[date("w")]=='Qua')
            {
              $sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '16' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
              $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
              //  $obj_buscar_promocoes = mysql_fetch_object($res_buscar_promocoes);
              if(mysql_num_rows($res_buscar_promocoes)>0 && $cod_ingredientes==$this->cod_ingredientes_promocao_16)
              {
                $objAux->preco_troca = 0;
                $objAux->preco = 0;
                $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['ingrediente_promo_cod'] = "16";
                $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['fracao'][$indice_ses_fracao]['ingredientes'][$proximo_indice]['ingrediente_promocional'] = 1;
              }
            }


            if ($ingrediente_troca==1)
            {
                $total_pedido += ($objAux->preco_troca / $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['quant_fracao']);
                $preco_pizza  += ($objAux->preco_troca / $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['quant_fracao']);
            }
            else
            {
                $total_pedido += ($objAux->preco / $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['quant_fracao']);
                $preco_pizza  += ($objAux->preco / $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['quant_fracao']);
            }
            desconectabd($conexao);
        }
        
        $_SESSION['ipi_carrinho']['total_pedido'] = $total_pedido;
        $_SESSION['ipi_carrinho']['pedido'][$indice_ses_pizza]['preco_pizza'] = $preco_pizza;

    }
    
    /**
     * Função que cria uma pizza na sessão e retorna o o indice da pizza na sessão.
     */
    public function adicionar_bebida ($cod_bebidas_ipi_conteudos, $quantidade, $id_combo = '', $cod_combos = '')
    {
        $total_pedido = isset($_SESSION['ipi_carrinho']['total_pedido']) ? $_SESSION['ipi_carrinho']['total_pedido'] : 0;
        
        $proximo_indice = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['cod_bebidas_ipi_conteudos'] = $cod_bebidas_ipi_conteudos;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['quantidade'] = $quantidade;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_promocional'] = '0';
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_fidelidade'] = '0';
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_id_sessao'] = $this->gerar_id_sessao();
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['cod_motivo_pro'] = '0';

        if ($id_combo!='')
        {
            $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_combo'] = '1';
            $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['id_combo'] = $id_combo;
            $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['cod_combos'] = $cod_combos;
        }
        else
        {
            $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_combo'] = '0';
        }
        
        $cod_pizzarias = $this->retornar_codigo_pizzaria();
        $conexao = conectabd();
        $sqlAux = "SELECT preco FROM ipi_conteudos_pizzarias WHERE cod_bebidas_ipi_conteudos='" . $cod_bebidas_ipi_conteudos."' AND cod_pizzarias = '".$cod_pizzarias."'";
        //echo $sqlAux;
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        $total_pedido += ($objAux->preco * $quantidade);
        /*echo '<br>sql '.$sqlAux.'<br>';
        echo '<br>total '.$total_pedido.'<br>';
        echo '<br>bebida '.$objAux->preco.'<br>';
        echo '<br>qtd '.$quantidade.'<br>';*/
        desconectabd($conexao);
        
        $_SESSION['ipi_carrinho']['total_pedido'] = $total_pedido;
    
    }
    
    /**
     * Função que cria uma bebida gratis na sessão e retorna o o indice da bebida na sessão.
     */
    public function adicionar_bebida_promocional ($cod_bebidas_ipi_conteudos, $num_cupom = '')
    {
        $quantidade = 1;
        $total_pedido = isset($_SESSION['ipi_carrinho']['total_pedido']) ? $_SESSION['ipi_carrinho']['total_pedido'] : 0;
        $proximo_indice = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['cod_bebidas_ipi_conteudos'] = $cod_bebidas_ipi_conteudos;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['quantidade'] = $quantidade;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_promocional'] = 1;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_fidelidade'] = '0';
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_id_sessao'] = $this->gerar_id_sessao();
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['cod_motivo_pro'] = '0';
        
        switch ($cod_bebidas_ipi_conteudos) {
            case '37':
                 $_SESSION['ipi_carrinho']['promocao']['promocao_2'] = '1';
                break;
            case '56':
                        $_SESSION['ipi_carrinho']['promocao']['promocao_1'] = '1';
                break;
            default:
                # code...
                break;
        }

    
        if($num_cupom != '') 
        {
                $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_cupom'] = 1;
            $_SESSION['ipi_carrinho']['cupom'] = $num_cupom;
            $_SESSION['ipi_carrinho']['pergunta_cupom'] = "Respondida";
        }
        
        $_SESSION['ipi_carrinho']['total_pedido'] = $total_pedido;
        return $proximo_indice;
    }
    
     /**              $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['cod_motivo_pro'] = $cod_motivo_pro;
     * Função que cria uma bebida com o preço de troca na sessão e retorna o o indice da bebida na sessão.
     */
    public function adicionar_bebida_promocao_balcao ($cod_bebidas_ipi_conteudos, $cod_motivo_pro = '')
    {
       
        $total_pedido = isset($_SESSION['ipi_carrinho']['total_pedido']) ? $_SESSION['ipi_carrinho']['total_pedido'] : 0;
        
        $proximo_indice = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['cod_bebidas_ipi_conteudos'] = $cod_bebidas_ipi_conteudos;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['quantidade'] = 1;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_promocional'] = '0';
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_fidelidade'] = '0';
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['cod_motivo_pro'] = $cod_motivo_pro;
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_id_sessao'] = $this->gerar_id_sessao();
        $_SESSION['ipi_carrinho']['bebida'][$proximo_indice]['bebida_combo'] = '0';
        
        if($cod_motivo_pro=="5")
        $total_pedido += $this->preco_troca_promocao;
        
        $_SESSION['ipi_carrinho']['total_pedido'] = $total_pedido;
        
        return $proximo_indice;
    }
    
    /**
     * Função que apaga o pedido (todas pizzas) da sessão antes de apagar faz o log.
     */
    public function apagar_pedido_logar($cod_pedidos)
    {
        $detalhes = "";
        $detalhes = var_export($_SESSION['ipi_carrinho'], true);
        $diretorio = "debug/debug_sessao/".$cod_pedidos.".xml";
        file_put_contents($diretorio, $detalhes, FILE_APPEND);
        unset($_SESSION['ipi_carrinho']);
    }
    
    /**
     * Função que apaga o pedido (todas pizzas) da sessão.
     */
    public function apagar_pedido()
    {
        unset($_SESSION['ipi_carrinho']);
    }    
    /**
     * Função que apaga o pedido (todas pizzas) da sessão.
     */
    public function existe_carrinho()
    {
        return (isset($_SESSION['ipi_carrinho']));
    }
    
    /**
     * Função que exibe o total do pedido.
     */
    public function exibir_total ()
    {
        $total_carrinho = 0;
        $arr_combo = array();
        
        $conexao = conectabd();

        if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
        {
          $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
        }
        else
        {
          $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
              $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
              $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
              $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
          $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
          $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
        }
        
        $arr_promo_carnaval = array();
        $arr_indice_menor = array();
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;

        if ($numero_pizzas > 0) //verificar preço da pizza para promoção de carnaval
        {
          for ($a = 0; $a < $numero_pizzas; $a++)
          {
            if (isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']))//a variavel ativa, funciona como se fosse 'pendente', assim, depois que eu associar, eu removo-a para não deixala pendente
            {
              $arr_promo_carnaval[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']][] = array('preco' => $_SESSION['ipi_carrinho']['pedido'][$a]['preco_pizza'],'ind_ses' => $a);
            }
          }

          foreach ($arr_promo_carnaval as $ind_pro => $arr_val) 
          {
/*            echo "<pre>";
            print_r($arr_val);

            echo "</pre>";
            echo "<br/>".$ind_pro." - ".$arr_val[0]['preco']."</br>";
            echo "<br/>".$ind_pro." - ".$arr_val[1]['preco']."</br>";*/

            if($arr_val[1]['preco']>$arr_val[0]['preco'])
            {
              $arr_indice_menor[$ind_pro] =  $arr_val[0]['ind_ses'];
            }
            else
            {
              $arr_indice_menor[$ind_pro] =  $arr_val[1]['ind_ses'];
            }

            
          }
            
        }

        if ($numero_pizzas > 0)
        {
            for ($a = 0; $a < $numero_pizzas; $a++)
            {
                $desconto = false;
                if (isset( $_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice'] ) )
                {
                    if(is_array($arr_promo_carnaval[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']]) && count($arr_promo_carnaval[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']]) > 1)
                    {

                      if($arr_indice_menor[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']] == $a)
                      {
                        $desconto = true;
                      }
                    }
                }

                if (isset( $_SESSION['ipi_carrinho']['pedido'][$a]['promocao17_indice'] ) )
                {
                    if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao17_doce']))
                    {
                      //if($arr_indice_menor[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']] == $a)
                      {
                        $desconto = true;
                      }
                    }
                }




                $preco_pizza = 0;
                $sqlAux = "SELECT * FROM ipi_fracoes f INNER JOIN ipi_tamanhos_ipi_fracoes tf ON (tf.cod_fracoes=f.cod_fracoes) WHERE tf.cod_tamanhos='" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'] . "' AND f.fracoes='" . $_SESSION['ipi_carrinho']['pedido'][$a]['quant_fracao']."' AND tf.cod_pizzarias = '".$cod_pizzarias."'";
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                $cod_tamanhos = $objAux->cod_tamanhos;
                $preco_divisao_fracao = $objAux->preco;
                $preco_pizza += $preco_divisao_fracao;
                
                //############################
                //echo "<br><br>Divisao: ".$preco_divisao_fracao;
                //echo "<br>Total: ".$total_carrinho;
                

                if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] != "N")
                {
                    $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] . " AND cod_tamanhos=" . $cod_tamanhos. " AND cod_pizzarias = ".$cod_pizzarias;
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    
                    if (($_SESSION['ipi_carrinho']['pedido'][$a]['borda_fidelidade'] != "1") && ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_promocional'] != "1") && ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_combo'] != "1") )
                    {
                        $preco_borda = $objAux->preco;
                    }
                    else
                    {
                        $preco_borda = 0;
                    }
                    
                    $preco_pizza += $preco_borda;
                    
                //############################
                //echo "<br><br>Borda: ".$preco_borda;
                //echo "<br>Total: ".$total_carrinho;
                }
                
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_tipo_massa'] != '')
                {
                    $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_tipo_massa WHERE cod_tipo_massa=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tipo_massa'] . " AND cod_tamanhos=" . $cod_tamanhos;
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    
                    $preco_massa = $objAux->preco;
                    
                    $preco_pizza += $preco_massa;
                    
                //############################
                //echo "<br><br>Massa: ".$preco_massa;
                //echo "<br>Total: ".$total_carrinho;
                }
                
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais'] != "N")
                {
                    $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais'] . " AND cod_tamanhos=" . $cod_tamanhos. " AND cod_pizzarias = ".$cod_pizzarias;
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    $preco_pizza += $objAux->preco;
                }
                //############################
                //echo "<br><br>Adic: ".$objAux->preco;
                //echo "<br>Total: ".$total_carrinho;
                

                $num_fracoes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao']);
                $preco_fracao_maior = 0;

                for ($b = 0; $b < $num_fracoes; $b++)
                {
                    $cod_pizzas = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    $num_fracao = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['num_fracao'];
                    $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos. " AND pt.cod_pizzarias = ".$cod_pizzarias;
                    //echo  "<br>sqlAux: ".$sqlAux;
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] == "1")
                    {
                        $preco_fracao = 0;
                    }
                    else
                    {
                        $preco_fracao = $objAux->preco;
                    }
                    
                    if($preco_fracao > $preco_fracao_maior)
                    {
                        $preco_fracao_maior = $preco_fracao;
                    }
                }

                for ($b = 0; $b < $num_fracoes; $b++)
                {    

                    
                    $cod_pizzas = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    $num_fracao = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['num_fracao'];
                    $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos . " AND pt.cod_pizzarias = ".$cod_pizzarias;
                   // echo "<br/>".$sqlAux."<br/>";
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    
                    if (($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] != "1"))
                    {
                          if(REGRA_PRECO_DIVISAO_PIZZA=="IGUALMENTE")
                          {
                            $preco_fracao = ($objAux->preco / $num_fracoes);
                          }
                          else if(REGRA_PRECO_DIVISAO_PIZZA=="MAIOR")
                          {
                            $preco_fracao = ($preco_fracao_maior / $num_fracoes);
                          }

                      

                        if($desconto)
                      $preco_fracao = $preco_fracao*0.5;

                      if (isset($_SESSION['ipi_carrinho']['desconto_balcao']))
                        {
                            if($_SESSION['ipi_carrinho']['desconto_balcao'] == 'sim')
                            $preco_fracao = $preco_fracao*0.7;
                        }
                    }
                    else
                    {
                        $preco_fracao = 0;
                        
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] == "1")
                        {
                            
                            if (!in_array($_SESSION['ipi_carrinho']['pedido'][$a]['id_combo'], $arr_combo)) 
                            {                       
                                $arr_combo[] = $_SESSION['ipi_carrinho']['pedido'][$a]['id_combo'];
                                $sqlAux = "SELECT preco FROM ipi_combos_pizzarias WHERE cod_pizzarias = '".$cod_pizzarias."' AND cod_combos='".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_combos']."'";
                                $resAux = mysql_query($sqlAux);
                                $objAux = mysql_fetch_object($resAux);
                                
                                $preco_fracao = $objAux->preco;
                            }
                        }                    
                    }



                    $preco_pizza += $preco_fracao;
                    
                    //############################
                    //echo "<br><br>Fraça: ".$preco_fracao;
                    //echo "<br>Total: ".$total_carrinho;
                    

                    $num_ingredientes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes']);
                    for ($c = 0; $c < $num_ingredientes; $c++)
                    {
                        $sqlAux = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) WHERE it.cod_tamanhos=" . $cod_tamanhos . " AND i.cod_ingredientes=" . $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes']. " AND it.cod_pizzarias = ".$cod_pizzarias;
                        $resAux = mysql_query($sqlAux);
                        $objAux = mysql_fetch_object($resAux);
                        
                        //echo $sqlAux.'<br>';
                        
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == false)
                        {
                            if($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_promocional']==1 && $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_promo_cod']==16)
                              {
                                $preco_ingrediente_extra = 0;
                                $preco_pizza += $preco_ingrediente_extra;
                              }
                            else
                            if ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_troca'] == false)
                            {
                                // Ingrediente EXTRA
                                $preco_ingrediente_extra = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                                $preco_pizza += $preco_ingrediente_extra;
                            }
                            elseif ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_troca'] == true)
                            {
                                // Ingrediente TROCA
                                //$sqlAuxTroca = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) WHERE it.cod_tamanhos=" . $cod_tamanhos . " AND i.cod_ingredientes=" . $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                                $sqlAuxTroca = "SELECT it.preco_troca, itroca.ingrediente FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) INNER JOIN ipi_ingredientes itroca ON (i.cod_ingredientes_troca=itroca.cod_ingredientes) WHERE i.cod_ingredientes = ".$_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes_troca']." AND it.cod_tamanhos=" . $cod_tamanhos. " AND it.cod_pizzarias = ".$cod_pizzarias;
                                $resAuxTroca = mysql_query($sqlAuxTroca);
                                $objAuxTroca = mysql_fetch_object($resAuxTroca);
                                
                                $preco_ingrediente_troca = arredondar_preco_ingrediente($objAuxTroca->preco_troca, $num_fracoes);
                                $preco_pizza += $preco_ingrediente_troca;
                            }
                        //############################
                        //echo "<br><br>Extra: ".$preco_ingrediente_extra;
                        //echo "<br>Total: ".$total_carrinho;

                        }
                    }

                    
                }
                $total_carrinho += $preco_pizza;
            }
        }
        
        $numero_bebidas = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
        if ($numero_bebidas > 0)
        {
            for ($a = 0; $a < $numero_bebidas; $a++)
            {
                $cod_bebidas_ipi_conteudos = $_SESSION['ipi_carrinho']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                //$sqlAux = "SELECT * FROM ipi_bebidas b INNER JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $cod_bebidas_ipi_conteudos;
                $sqlAux = "SELECT * FROM ipi_conteudos_pizzarias WHERE cod_bebidas_ipi_conteudos='" . $cod_bebidas_ipi_conteudos . "' AND  cod_pizzarias = '" . $cod_pizzarias . "'";
                
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                if (($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_fidelidade'] != "1") && ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_promocional'] != "1") && ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_combo'] != "1") && ($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] != "9") && ($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] != "19"))
                {
                    if(($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] != "5"))
                    {
                        $preco_bebida = $objAux->preco;
                    }
                   else
                    $preco_bebida = $this->preco_troca_promocao;
                }
                else
                {
                    $preco_bebida = 0;
                }
                $quantidade_bebida = $_SESSION['ipi_carrinho']['bebida'][$a]['quantidade'];
                $total_carrinho += ($preco_bebida * $quantidade_bebida);
            }
        }
        
        desconectabd($conexao);

        //Estamos colocando o valor total sem considerar o frete pois o metodo finalizar pedido irá somar o valor do frete
        //E os metodos para os cartoes de credito só necessitam do valor do return que este sim tem que ter o valor do frete
        $_SESSION['ipi_carrinho']['total_pedido'] = $total_carrinho;

         if (isset($_SESSION['ipi_carrinho']['pagamento']['valor_frete']))
        {
            $total_carrinho += $_SESSION['ipi_carrinho']['pagamento']['valor_frete'];
        }
        
        
        
        
        return (bd2moeda($total_carrinho));
    }
    


    

    /**
     * Calcular o desconto com fidelidade.
     */
    public function calcular_desconto_fidelidade ()
    {
        
        $conexao = conectabd();
        
        $fidelidade_desconto_total = 0;
        $fidelidade_pontos_gastos = 0;
        $fidelidade_pontos_atuais = $_SESSION['ipi_cliente']['pontos_fidelidade'];
        
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        if ($numero_pizzas > 0)
        {
            for ($a = 0; $a < $numero_pizzas; $a++)
            {
                
                $sqlAux = "SELECT * FROM ipi_fracoes f INNER JOIN ipi_tamanhos_ipi_fracoes tf ON (tf.cod_fracoes=f.cod_fracoes) WHERE tf.cod_tamanhos='" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'] . "' AND f.fracoes='" . $_SESSION['ipi_carrinho']['pedido'][$a]['quant_fracao']. "' AND tf.cod_pizzarias = '".$this->retornar_codigo_pizzaria()."'";
                $conexao = conectabd();
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                $cod_tamanhos = $objAux->cod_tamanhos;
                //echo $sqlAux.'<br />';
                
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] != "N")
                {
                    $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] . " AND cod_tamanhos=" . $cod_tamanhos;
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_fidelidade'] == "1")
                    {
                        $preco_borda = $objAux->preco;
                        $preco_fidelidade_borda = $objAux->pontos_fidelidade;
                    }
                    else
                    {
                        $preco_borda = 0;
                        $preco_fidelidade_borda = 0;
                    }
                    
                    if ($fidelidade_pontos_atuais > $preco_fidelidade_borda)
                    {
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_fidelidade'] == "1")
                        {
                            $fidelidade_desconto_total += $preco_borda;
                            $fidelidade_pontos_gastos += $preco_fidelidade_borda;
                            $fidelidade_pontos_atuais -= $preco_fidelidade_borda;
                        }
                    }
                //echo "<br>sql1: ".$sqlAux;
                //echo "<br>1a - fidelidade_desconto_total: ".$fidelidade_desconto_total;
                //echo "<br>1b - fidelidade_pontos_gastos: ".$fidelidade_pontos_gastos;
                //echo "<br>1c - fidelidade_pontos_atuais: ".$fidelidade_pontos_atuais;
                

                }
                
                $num_fracoes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao']);
                for ($b = 0; $b < $num_fracoes; $b++)
                {
                    
                    $cod_pizzas = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    $num_fracao = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['num_fracao'];
                    $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos;
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_fidelidade'] == "1")
                    {
                        $preco_fracao = ($objAux->preco / $num_fracoes);
                        $preco_fidelidade_fracao = ($objAux->pontos_fidelidade / $num_fracoes);
                    }
                    else
                    {
                        $preco_fracao = 0;
                        $preco_fidelidade_fracao = 0;
                    }
                    
                    if ($fidelidade_pontos_atuais > $preco_fidelidade_fracao)
                    {
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_fidelidade'] == "1")
                        {
                            $fidelidade_desconto_total += $preco_fracao;
                            $fidelidade_pontos_gastos += $preco_fidelidade_fracao;
                            $fidelidade_pontos_atuais -= $preco_fidelidade_fracao;
                        }
                    }
                    
                   /* echo "<br>sql2: ".$sqlAux;
                echo "<br>2a - fidelidade_desconto_total: ".$fidelidade_desconto_total;
                echo "<br>2b - fidelidade_pontos_gastos: ".$fidelidade_pontos_gastos;
                echo "<br>2c - fidelidade_pontos_atuais: ".$fidelidade_pontos_atuais;
                */
                

                }
            
            }
        
        }
        
        $numero_bebidas = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
        if ($numero_bebidas > 0)
        {
            
            for ($a = 0; $a < $numero_bebidas; $a++)
            {
                $cod_bebidas_ipi_conteudos = $_SESSION['ipi_carrinho']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                $sqlAux = "SELECT * FROM ipi_bebidas b INNER JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) INNER JOIN ipi_conteudos_pizzarias icp ON (icp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $cod_bebidas_ipi_conteudos;
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                
                if ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_fidelidade'] == "1")
                {
                    $preco_bebida = $objAux->preco;
                    $preco_fidelidade_bebida = $objAux->pontos_fidelidade;
                }
                else
                {
                    $preco_bebida = 0;
                    $preco_fidelidade_bebida = 0;
                }
                
                $quantidade_bebida = $_SESSION['ipi_carrinho']['bebida'][$a]['quantidade'];
                
                if ($fidelidade_pontos_atuais > ($preco_fidelidade_fracao * $quantidade_bebida))
                {
                    if ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_fidelidade'] == "1")
                    {
                        $fidelidade_desconto_total += ($preco_bebida * $quantidade_bebida);
                        $fidelidade_pontos_gastos += ($preco_fidelidade_bebida * $quantidade_bebida);
                        $fidelidade_pontos_atuais -= ($preco_fidelidade_bebida * $quantidade_bebida);
                    }
                }

                /*echo "<br>sql3: ".$sqlAux;
                echo "<br>3a - fidelidade_desconto_total: ".$fidelidade_desconto_total;
                echo "<br>3b - fidelidade_pontos_gastos: ".$fidelidade_pontos_gastos;
                echo "<br>3c - fidelidade_pontos_atuais: ".$fidelidade_pontos_atuais;
                */
            
            }
        }
        desconectabd($conexao);
        
        $_SESSION['ipi_carrinho']['fidelidade_desconto_total'] = $fidelidade_desconto_total;
        $_SESSION['ipi_carrinho']['fidelidade_pontos_gastos'] = $fidelidade_pontos_gastos;
        //die();
    
    }
    
    /**
     * Pagamento do Pedido com dinheiro
     */
    public function pagamento_dinheiro ($tipo, $troco, $cod_enderecos,$valor_frete,$comissao_frete,  $cpf_nota_paulista = '')
    {
        $_SESSION['ipi_carrinho']['pagamento']['tipo'] = $tipo;
        $_SESSION['ipi_carrinho']['pagamento']['troco'] = $troco;
        $_SESSION['ipi_carrinho']['pagamento']['cpf_nota_paulista'] = $cpf_nota_paulista;
        $_SESSION['ipi_carrinho']['pagamento']['cod_enderecos'] = $cod_enderecos;
        $_SESSION['ipi_carrinho']['pagamento']['valor_frete'] = (($valor_frete!="" || $valor_frete>0 ) ? $valor_frete : '0');
        $_SESSION['ipi_carrinho']['pagamento']['comissao_frete'] = (($comissao_frete!="" || $comissao_frete>0 ) ? $comissao_frete : '0');
    }
    
    /**
     * Pagamento do Pedido com cartão
     */
    public function pagamento_cartao ($tipo, $cod_enderecos,$valor_frete,$comissao_frete,  $num_pedido_temp, $cpf_nota_paulista = '')
    {
        $_SESSION['ipi_carrinho']['pagamento']['tipo'] = $tipo;
        $_SESSION['ipi_carrinho']['pagamento']['cpf_nota_paulista'] = $cpf_nota_paulista;
        $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] = $num_pedido_temp;
        $_SESSION['ipi_carrinho']['pagamento']['cod_enderecos'] = $cod_enderecos;
        $_SESSION['ipi_carrinho']['pagamento']['valor_frete'] = (($valor_frete!="" || $valor_frete>0 ) ? $valor_frete : '0');
        $_SESSION['ipi_carrinho']['pagamento']['comissao_frete'] = (($comissao_frete!="" || $comissao_frete>0 ) ? $comissao_frete : '0');
    }
    
    /**
     * Função que exibe o pedido completo.
     */
    public function exibir_pedido ()
    {
        //echo '<pre>';
        //print_r($_SESSION);
        //echo '</pre>';

        echo '<script type="text/javascript">';
        echo "function confirmar_excluir_pizza(frm, tipo)";
        echo "{";
        echo "  if (confirm('Deseja realmente excluir esta ' + tipo + ' do seu carrinho?'))";
        echo "  {";
        echo "    frm.submit();";
        echo "  }";
        echo "}";
        echo "</script>";

        $titulo_exibido = 0;

        if ($_SESSION['ipi_cliente']['autenticado'] == true)
        {
            echo '<div id="carrinho_pts_fidel" class="center">Você possui '.$this->pontos_fidelidade().' pontos de fidelidade</div><br/>';
           
            echo '<div class="clear"></div>';
        }
       
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        if ($numero_pizzas > 0)
        {
            if ($titulo_exibido == 0)
            {
                echo "<div class='center'>SEU PEDIDO</div>";
                $titulo_exibido = 1;
            }
            ?>
            <div style="border:1px solid black" class="comanda_espacamento cor_fundo_verde1"></div>
            <!--
            Versão antiga
            <a href="javascript:chama_promocao();"><img src="img/btn_fechar_pedido_carrinho.gif" border="0" alt="Clique aqui fechar seu pedido." /></a>
            -->
            <!--
            <a href="javascript:$('#frmFecharPedido').submit();"><img src="img/btn_fechar_pedido_carrinho.gif" border="0" alt="Clique aqui fechar seu pedido." /></a>
            <form id="frmFecharPedido" method="post" action="ipi_req_carrinho_acoes.php" style="margin: 0"><input type="hidden" name="acao" value="verificar_login"></form>
            -->
            <?
            $conexao = conectabd();
            $num_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
            for ($a = 0; $a < $num_pizzas; $a++)
            {

                echo "<div style='margin-top: 30px;'>";
                echo '<b>'.($a + 1) . 'ª Pizza</b>';
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] == "1")
                    echo " (GRÁTIS)";
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_fidelidade'] == "1")
                    echo " (FIDELIDADE)";
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] == "1")
                    echo " (COMBO)";
                echo "</div>";
                
                echo '<div class="fonte12 cor_branco cor_fundo_verde1 carrinho_comanda_corpo comanda_margem1">';
                echo '<form method="post" action="ipi_req_carrinho_acoes.php" name="frmExcluirPizza_' . $a . '">';
                echo '<input type="hidden" name="ind_ses" value="' . $_SESSION['ipi_carrinho']['pedido'][$a]['pizza_id_sessao'] . '" />';
                echo '<input type="hidden" name="acao" value="excluir_pizza" />';
                echo '</form>';

                
                if($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] == "1")
                {
                  echo "<div style='text-align: right'><a style='color:red;' href='javascript:if(confirm(\"Ao remover esta pizza,\n todo o combo sera removido\")){document.frmExcluirPizza_{$a}.submit();}'>Remover</a></div>";
                }
                else
                {
                  echo "<div style='text-align: right'><a style='color:red;' href='javascript:confirmar_excluir_pizza(document.frmExcluirPizza_{$a},\"pizza\");'>Remover</a></div>";
                }

                 echo '<div style="margin-left:10px;">';

                $sqlAux = "SELECT * FROM ipi_tamanhos WHERE cod_tamanhos=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                $tamanho_pizza = explode(" (", $objAux->tamanho);
                echo  $tamanho_pizza[0] ;
                //echo '<br><b>Quantidade de Sabores:</b> ' . $_SESSION['ipi_carrinho']['pedido'][$a]['quant_fracao'];
               
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] != "N")
                {
                    $sqlAux = "SELECT * FROM ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'];
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    echo '<br/>Borda: ' . $objAux->borda;
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_promocional'] == "1")
                        echo " (GRÁTIS)";
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_combo'] == "1")
                        echo " (COMBO)";
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_fidelidade'] == "1")
                        echo " (FIDELIDADE)";
                }
                else
                {
                    echo '<br />Sem borda recheada';
                }
                
                // if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais'] != "N")
                // {
                //     $sqlAux = "SELECT * FROM ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais'];
                //     $resAux = mysql_query($sqlAux);
                //     $objAux = mysql_fetch_object($resAux);
                //     echo '<br/><strong>Gergelim:</strong> ' . $objAux->adicional;
                // }
                // else
                // {
                //     echo '<br />Sem gergelim na borda';
                // }

                
                $sqlAux = "SELECT * FROM ipi_tipo_massa tm INNER JOIN ipi_tamanhos_ipi_tipo_massa tt ON (tm.cod_tipo_massa = tt.cod_tipo_massa) WHERE tm.cod_tipo_massa = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_tipo_massa']." AND tt.cod_tamanhos = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                echo '<br />Massa '.$objAux->tipo_massa;
                if($objAux->preco > 0)
                {
                    echo '&nbsp;('.bd2moeda($objAux->preco).')';   
                }


                $sqlAux = "SELECT * FROM ipi_opcoes_corte oc INNER JOIN ipi_tamanhos_ipi_opcoes_corte toc ON (oc.cod_opcoes_corte = toc.cod_opcoes_corte) WHERE oc.cod_opcoes_corte = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_opcoes_corte']." AND toc.cod_tamanhos = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                echo '<br />Corte '.$objAux->opcao_corte;
                if($objAux->preco > 0)
                {
                    echo '&nbsp;('.bd2moeda($objAux->preco).')';   
                }
                echo '</div>';
                echo '</div>';
                $num_fracoes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao']);
                for ($b = 0; $b < $num_fracoes; $b++)
                {
                    $sqlAux = "SELECT * FROM ipi_pizzas WHERE cod_pizzas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    echo "<div style='margin-top:10px;'>";
                    echo ($b + 1) . "º Sabor (<b>". $objAux->pizza."</b>)";
                    // echo "</div>";

                    // echo '<div style="margin-left:10px;">';
                   
                    $num_ingredientes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes']);
                    $ingredientes_padroes = array ();
                    $ingredientes_nao_padroes = array ();
                    $ind_aux_padrao = 0;
                    $ind_aux_nao_padrao = 0;
                    for ($c = 0; $c < $num_ingredientes; $c++)
                    {
                        $sqlAux = "SELECT * FROM ipi_ingredientes WHERE cod_ingredientes=" . $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                        $resAux = mysql_query($sqlAux);
                        $objAux = mysql_fetch_object($resAux);
                        
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == true)
                        {
                            $ingredientes_padroes[$ind_aux_padrao] = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                            $ind_aux_padrao++;
                        }
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == false)
                        {
                            $ingredientes_nao_padroes[$ind_aux_nao_padrao] = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                            $ind_aux_nao_padrao++;
                        }
                    }
                    
                    //if (count($ingredientes_padroes) > 0)
                    //{
                        $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (ip.cod_ingredientes=i.cod_ingredientes) WHERE ip.cod_pizzas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'] . " AND ip.cod_ingredientes NOT IN (" . implode(",", $ingredientes_padroes) . ") AND i.consumo != 1";
                        $resAux = mysql_query($sqlAux);
                        $linAux = mysql_num_rows($resAux);
                        //echo $sqlAux;
                        $cont_ing = 0;
                        if ($linAux > 0)
                        {
                            echo "<br />Retirar: ";
                            while ($objAux = mysql_fetch_object($resAux))
                            {
                                // $cont_ing++;
                                echo "<br />&nbsp;&nbsp;&nbsp;&nbsp; -" . $objAux->ingrediente;
                                // if ($cont_ing < $linAux)
                                // {
                                //     echo ", ";
                                // }
                            }
                        }
                    //}
                    
                    if (count($ingredientes_nao_padroes) > 0)
                    {
                        $sqlAux = "SELECT * FROM ipi_ingredientes i WHERE i.cod_ingredientes IN (" . implode(",", $ingredientes_nao_padroes) . ")";
                        $resAux = mysql_query($sqlAux);
                        $linAux = mysql_num_rows($resAux);
                        if ($linAux > 0)
                        {
                            echo "<br />Adicionais ";
                            while ($objAux = mysql_fetch_object($resAux))
                            {
                                echo "<br />&nbsp;&nbsp;&nbsp;&nbsp; -" . $objAux->ingrediente;
                            }
                        }
                    }
                    else
                    {
                        //echo "<br />Sem Adicionais";
                    }
                  echo '</div>';
                }
            }
            
            desconectabd($conexao);
        }
        else
        {
            echo '<div style="height:90px; font-weight: bold; text-align:center; font-size: 18px;">Nenhum Pedido em Andamento</div>';
            /*
            ?>
            <br />
            <br />
            <center><strong>Pizza Quadrada é mais pizza!</strong></center>
            <br />
            <br />
            <?
            */
        }
        
        $numero_bebidas = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
        if ($numero_bebidas > 0)
        {
            if ($titulo_exibido == 0)
                echo "<div class='comanda_titulo_geral fonte20 cor_amarelo1 cor_fundo_marrom1 centralizar'>SEU PEDIDO</div>";
            
            echo "<div class='fonte20 cor_branco cor_fundo_azul1 carrinho_comanda_titulo comanda_margem2'>BEBIDAS</div>";
            $conexao = conectabd();
            
            echo '<div class="fonte12 cor_branco cor_fundo_verde1 carrinho_comanda_corpo comanda_margem1">';
            for ($a = 0; $a < $numero_bebidas; $a++)
            {
                $sqlAux = "SELECT * FROM ipi_bebidas b INNER JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $_SESSION['ipi_carrinho']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                echo '<form method="post" action="ipi_req_carrinho_acoes.php" name="frmExcluirBebida_' . $a . '" style="margin: 0px">';
                echo '<input type="hidden" name="ind_ses" value="' . $a . '" />';
                echo '<input type="hidden" name="acao" value="excluir_bebida" />';
                echo '</form>';
                if ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_combo'] == "1")
                {
                  echo "<div style='text-align: right'><a style='color:red;' href='javascript:if(confirm(\"Ao remover esta bebida,\n todo o combo sera removido\")){document.frmExcluirBebida_{$a}.submit()};'>Remover</a></div>";
                }
                else
                {
                  echo "<div style='text-align: right'><a style='color:red;' href='javascript:;' onClick='confirmar_excluir_pizza(document.frmExcluirBebida_{$a},\"bebida\");'>Remover</a></div>";
                }

                echo $_SESSION['ipi_carrinho']['bebida'][$a]['quantidade'].' ';

                echo $objAux->bebida . " - " . $objAux->conteudo;

                if ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_promocional'] == "1")
                    echo " (GRÁTIS)";
                elseif ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_fidelidade'] == "1")
                    echo " (FIDELIDADE)";
                elseif ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_combo'] == "1")
                    echo " (COMBO)";
                elseif ($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] == "5") //no balcao refri gratis
                    echo ' &nbsp;('.bd2moeda($this->preco_troca_promocao).')';
                elseif ($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] == "9") //pizza doce refri gratis
                    echo " (GRÁTIS)";
                elseif ($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] == "19") //pizza doce refri gratis
                    echo " (GRÁTIS)";
            }
            echo "</div>";            
            desconectabd($conexao);
        }
        
        
        if (($numero_pizzas) || ($numero_bebidas))
        {
            echo "<div style='margin-top:30px'>Total do pedido: <span class='negrito'>R$ " . ($this->exibir_total()==""? '0,00' : $this->exibir_total()) . "</span></div>";
            ?>
            <script type="text/javascript">
            function chama_promocao() 
            {
            <?
            $dia_semana = date("N");
            if ($dia_semana!=1) // date("N") - Não aparecer a sugestão na segunda!!!
            {
                if (($_SESSION['ipi_carrinho']['sugestao'] == 0) || (!isset($_SESSION['ipi_carrinho']['sugestao'])))
                :
                ?>
                
                var iebody = (document.compatMode && document.compatMode != "BackCompat") ? document.documentElement : document.body;
                var dsoctop = document.all? iebody.scrollTop : pageYOffset;
              
                var divFundo = new Element('div', {
                  'styles': {
                    'position': 'absolute',
                    'top': 0,
                    'left': 0,
                    'height': document.documentElement.clientHeight + dsoctop,
                    'width':  document.documentElement.clientWidth,
                    'z-index': 99,
                    'background-color': '#000'
                  }
                });
              
                var divMsg = new Element('div', {
                  'styles': {
                    'position': 'absolute',
                    //'top': 50,
                    'left': (document.body.clientWidth - 400) / 2,
                    'background-color': '#ffffff',
                    'width' : 400,
                    'height': 380,
                    'padding': 20,
                    'z-index': 9999,
                    'overflow': 'hidden'
                  }
                });
                
                /*
                // Verifica se é IE6
                ie6 = Browser.Engine.trident4;
                
                if(ie6) {
                  var win = window;
                  var middle = win.getScrollTop() + (win.getHeight() / 2);
                  var top = Math.max(0, middle - (400 / 2));
                  
                  divMsg.setStyle('position', 'absolute');
                  divMsg.setStyle('top', top);
                }
                else {
                  divMsg.setStyle('position', 'fixed');
                }
                */
                
                var win = window;
                var middle = win.getScrollTop() + (win.getHeight() / 2);
                var top = Math.max(0, middle - (400 / 2));
                            
                divMsg.setStyle('position', 'absolute');
                divMsg.setStyle('top', top);
                              
                new Request.HTML({
                  url: 'ipi_req_carrinho_sugere_pizza.php',
                  update: divMsg
                }).send();
                
                divFundo.setStyle('opacity', 0.5);
              
                $(document.body).adopt(divMsg);
                $(document.body).adopt(divFundo);
              
              <?
              else
              :
                  echo 'document.frmFecharPedido.submit();';
              endif;
              }
              else
              {
                echo 'document.frmFecharPedido.submit();';
              }
              ?>
            }
            </script>


          <div id="carrinho_comanda_limpar">
            <a href="javascript:void(0);" onclick="javascript:if (confirm('Deseja realmente limpar o carrinho?'))document.frmLimparCarrinho.submit();" class="btn btn-secondary comanda_limpar">Limpar Carrinho</a>
            <form name="frmLimparCarrinho" method="post" action="ipi_req_carrinho_acoes.php" style="margin: 0">
            <input type="hidden" name="acao" value="limpar" />
            </form>
          </div>

          <?
             $pagina_url = str_replace('/', '', $_GET["pagina"]);
            if ($pagina_url == "algo_mais" || $pagina_url == 'pedidos')
           // if ($_GET["pagina"] == "algo_mais" || $_GET['pagina'] == 'pedidos')
           {

          ?>
          <div>
            
            <form name="frm_finalizar_pedido" method="post" action="ipi_req_carrinho_acoes.php">
            <!-- <a href='#' onclick='sugerir();' title='Finalizar meu pedido'> <img src='img/pc/btn_fechar_pedido.png' alt='Finalizar meu pedido' /></a> -->
            <a href="javascript:document.frm_finalizar_pedido.submit();" class="btn btn-secondary comanda_fechar">Finalizar Pedido</a>
            <input type='hidden' name="acao" value="verificar_login" />
            </form>
            <!-- <a href="javascript:sugerir();"><img src="img/pc/btn_fechar_pedido.png" border="0" alt="Clique aqui fechar seu pedido." /></a>
            <form id="frmFecharPedido" name="frmFecharPedido" method="post" action="ipi_req_carrinho_acoes.php" style="margin: 0"><input type="hidden" name="acao" value="verificar_login" /></form> -->        
            <!--
            <a href="javascript:document.frmFecharPedido.submit();"><img src="img/pc/btn_fechar_pedido.png" border="0" alt="Clique aqui fechar seu pedido." /></a>
            <form id="frmFecharPedido" name="frmFecharPedido" method="post" action="ipi_req_carrinho_acoes.php" style="margin: 0"><input type="hidden" name="acao" value="verificar_login"></form>
            -->
            
          </div>
            <?
           }
      }
    }



    /**
    * Função para calcular o preço da pizza normal
    */

    public function calcular_preco_pizza ($cod_pizzarias, $num_pizza, $desconto = false)
    {
      $preco_pizza = 0;
      $a = $num_pizza;
      $arr_combo = array();
      $sqlAux = "SELECT * FROM ipi_fracoes f INNER JOIN ipi_tamanhos_ipi_fracoes tf ON (tf.cod_fracoes=f.cod_fracoes) WHERE tf.cod_tamanhos='" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'] . "' AND f.fracoes='" . $_SESSION['ipi_carrinho']['pedido'][$a]['quant_fracao']. "' AND tf.cod_pizzarias = '".$cod_pizzarias."'";
      //echo $sqlAux;
      $resAux = mysql_query($sqlAux);
      $objAux = mysql_fetch_object($resAux);
      $cod_tamanhos = $objAux->cod_tamanhos;
      $preco_divisao_fracao = $objAux->preco;
      $preco_pizza += $preco_divisao_fracao;
      

      if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] != "N")
      {
          $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] . " AND cod_tamanhos=" . $cod_tamanhos. " AND cod_pizzarias = ".$cod_pizzarias;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          
          if (($_SESSION['ipi_carrinho']['pedido'][$a]['borda_fidelidade'] != "1") && ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_promocional'] != "1") && ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_combo'] != "1") )
          {
              $preco_borda = $objAux->preco;
          }
          else
          {
              $preco_borda = 0;
          }
          
          $preco_pizza += $preco_borda;
      }
      
      if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_tipo_massa'] != '')
      {
          $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_tipo_massa WHERE cod_tipo_massa=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tipo_massa'] . " AND cod_tamanhos=" . $cod_tamanhos;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          
          $preco_massa = $objAux->preco;
          
          $preco_pizza += $preco_massa;
      }
      
      if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais'] != "N")
      {
          $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais'] . " AND cod_tamanhos=" . $cod_tamanhos. " AND cod_pizzarias = ".$cod_pizzarias;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          $preco_pizza += $objAux->preco;
      }          

      $num_fracoes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao']);
      
      $preco_fracao_maior = 0;
      for ($b = 0; $b < $num_fracoes; $b++)
      {
          $cod_pizzas = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
          $num_fracao = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['num_fracao'];
          $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos. " AND pt.cod_pizzarias = ".$cod_pizzarias;
          //echo  "<br>sqlAux: ".$sqlAux;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          
          if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] == "1")
          {
              $preco_fracao = 0;
          }
          else
          {
              $preco_fracao = $objAux->preco;
          }
          
          if($preco_fracao > $preco_fracao_maior)
          {
              $preco_fracao_maior = $preco_fracao;
          }
      }

      for ($b = 0; $b < $num_fracoes; $b++)
      {
          
          $cod_pizzas = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
          $num_fracao = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['num_fracao'];
          $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos . " AND pt.cod_pizzarias = ".$cod_pizzarias;
          $resAux = mysql_query($sqlAux);
          $objAux = mysql_fetch_object($resAux);
          
          if (($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] != "1") && ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_fidelidade'] != "1") && ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] != "1"))
          {
            if(REGRA_PRECO_DIVISAO_PIZZA=="IGUALMENTE")
              {
                $preco_fracao = ($objAux->preco / $num_fracoes);
              }
              else if(REGRA_PRECO_DIVISAO_PIZZA=="MAIOR")
              {
                $preco_fracao = ($preco_fracao_maior / $num_fracoes);
              }
              // $preco_fracao = ($objAux->preco / $num_fracoes);
          }
          else
          {
              $preco_fracao = 0;
              
              if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] == "1")
              {
                  
                  if (!in_array($_SESSION['ipi_carrinho']['pedido'][$a]['id_combo'], $arr_combo)) 
                  {                       
                      $arr_combo[] = $_SESSION['ipi_carrinho']['pedido'][$a]['id_combo'];
                      $sqlAux = "SELECT preco FROM ipi_combos_pizzarias WHERE cod_combos = '".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_combos']."' AND cod_pizzarias = '".$cod_pizzarias."'";
                      $resAux = mysql_query($sqlAux);
                      $objAux = mysql_fetch_object($resAux);
                      
                      $preco_fracao = $objAux->preco;
                  }
              }                    
          }

          if($desconto)
            $preco_fracao = $preco_fracao*0.5;

          if($_SESSION['ipi_carrinho']['desconto_balcao'] == 'sim')
            $preco_fracao = $preco_fracao*0.7;

          $preco_pizza += $preco_fracao;
          

          $num_ingredientes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes']);
          for ($c = 0; $c < $num_ingredientes; $c++)
          {
              $sqlAux = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) WHERE it.cod_tamanhos='".$cod_tamanhos."' AND i.cod_ingredientes = '" . $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes']. "' AND it.cod_pizzarias = '".$cod_pizzarias."'";
              $resAux = mysql_query($sqlAux);
              $objAux = mysql_fetch_object($resAux);
              
              if ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == false)
              {
                  
                  if($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_promocional']==1 && $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_promo_cod']==16)
                  {
                    $preco_ingrediente_extra = 0;
                    $preco_pizza += $preco_ingrediente_extra;
                  }
                else
                  if ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_troca'] == false)
                  {
                      // Ingrediente EXTRA
                      $preco_ingrediente_extra = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                      $preco_pizza += $preco_ingrediente_extra;
                  }
                  elseif ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_troca'] == true)
                  {
                      // Ingrediente TROCA
                      $sqlAuxTroca = "SELECT it.preco_troca, itroca.ingrediente FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) INNER JOIN ipi_ingredientes itroca ON (i.cod_ingredientes_troca=itroca.cod_ingredientes) WHERE i.cod_ingredientes = ".$_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes_troca']." AND it.cod_tamanhos='".$cod_tamanhos."' AND it.cod_pizzarias = '".$cod_pizzarias."'";
                      $resAuxTroca = mysql_query($sqlAuxTroca);
                      $objAuxTroca = mysql_fetch_object($resAuxTroca);
                      
                      $preco_ingrediente_troca = arredondar_preco_ingrediente($objAuxTroca->preco_troca, $num_fracoes);
                      $preco_pizza += $preco_ingrediente_troca;
                  }
              }
          }
      }
      return $preco_pizza;
    }


    public function exibir_resumo_pedido()
    {
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        $arr_promo_carnaval = array();
        $arr_indice_menor = array();

        if ($numero_pizzas > 0) //verificar preço da pizza para promoção de carnaval
        {
          for ($a = 0; $a < $numero_pizzas; $a++)
          {
            if (isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']))//a variavel ativa, funciona como se fosse 'pendente', assim, depois que eu associar, eu removo-a para não deixala pendente
            {
              $arr_promo_carnaval[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']][] = array('preco' => $_SESSION['ipi_carrinho']['pedido'][$a]['preco_pizza'],'ind_ses' => $a);
            }
          }

          foreach ($arr_promo_carnaval as $ind_pro => $arr_val) 
          {
/*            echo "<pre>";
            print_r($arr_val);

            echo "</pre>";
            echo "<br/>".$ind_pro." - ".$arr_val[0]['preco']."</br>";
            echo "<br/>".$ind_pro." - ".$arr_val[1]['preco']."</br>";*/

            if($arr_val[1]['preco']>$arr_val[0]['preco'])
            {
              $arr_indice_menor[$ind_pro] =  $arr_val[0]['ind_ses'];
            }
            else
            {
              $arr_indice_menor[$ind_pro] =  $arr_val[1]['ind_ses'];
            }

            
          }
            
        }
                
        if ($numero_pizzas > 0)
        {
            $cod_pizzarias = $this->retornar_codigo_pizzaria(); 
            $conexao = conectabd();
            $num_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
            for ($a = 0; $a < $num_pizzas; $a++)
            {
                $desconto = false;
                if(is_array($arr_promo_carnaval[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']]) && count($arr_promo_carnaval[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']]) > 1)
                {

                  if($arr_indice_menor[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']] == $a)
                  {
                    $desconto = true;
                  }
                }


                if (isset( $_SESSION['ipi_carrinho']['pedido'][$a]['promocao17_indice'] ) )
                {
                    if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao17_doce']))
                    {
                      //if($arr_indice_menor[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']] == $a)
                      {
                        $desconto = true;
                      }
                    }
                }

                $preco_pizza = $this->calcular_preco_pizza($cod_pizzarias, $a,$desconto);

                echo '<div class="info_pizza">';
                echo '<div class="box_item">';
                echo '  <div class="box_item">';

                echo '<h3>'.($a + 1).'ª PIZZA';
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] == "1")
                    echo " (GRÁTIS)";
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_fidelidade'] == "1")
                    echo " (FIDELIDADE)";
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] == "1")
                    echo " (COMBO) <span class='fonte14'>Todos os adicionais serão cobrados a parte.</span>";

                echo '</h3>';

                $sqlAux = "SELECT * FROM ipi_tamanhos WHERE cod_tamanhos=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                echo '<div class="info_pizza"><ul class="lista_infos3_pedido">'; 
                $arr_aux_tamanhos = explode(')', $objAux->tamanho);
                echo '<li class="cor_cinza1">'.$arr_aux_tamanhos[0].')</li>';
                
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] != "N")
                {
                    $sqlAux = "SELECT ib.borda, itib.preco FROM ipi_bordas ib INNER JOIN ipi_tamanhos_ipi_bordas itib ON (ib.cod_bordas = itib.cod_bordas) WHERE ib.cod_bordas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas']. " AND cod_tamanhos = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    echo '<li class="cor_cinza1 maior">Borda de <span class="fonte12 cor_branco">' . $objAux->borda;
                    if($_SESSION['ipi_carrinho']['pedido'][$a]['borda_promocional'] == "1" || $_SESSION['ipi_carrinho']['pedido'][$a]['borda_combo'] == "1" || $_SESSION['ipi_carrinho']['pedido'][$a]['borda_fidelidade'] == "1")
                    {
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_promocional'] == "1" && $_SESSION['ipi_carrinho']['pedido'][$a]['borda_combo'] != "1")
                            echo " (GRÁTIS)</span>";
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_combo'] == "1")
                            echo " (COMBO)</span>";
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_fidelidade'] == "1")
                            echo " (FIDELIDADE)</span>";
                    }
                    else
                    {
                        echo " - R$ ".$objAux->preco."</span>";
                    }
                    echo '</li>';
                }
                else
                {
                    echo '<li class="cor_cinza1 maior">Sem borda recheada.</li>';
                }
                
                $sqlAux = "SELECT * FROM ipi_tipo_massa tm INNER JOIN ipi_tamanhos_ipi_tipo_massa tt ON (tm.cod_tipo_massa = tt.cod_tipo_massa) WHERE tm.cod_tipo_massa = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_tipo_massa']." AND tt.cod_tamanhos = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                echo '<li class="cor_cinza1"> Massa '.$objAux->tipo_massa;
                if($objAux->preco > 0)
                {
                    echo '&nbsp;('.bd2moeda($objAux->preco).')';   
                }
                echo '</li>';
                
                echo '</ul><ul class="lista_infos3_pedido">';

                $obj_preco_qnt_fracao = executar_busca_simples("SELECT * FROM ipi_fracoes f INNER JOIN ipi_tamanhos_ipi_fracoes tf ON (tf.cod_fracoes=f.cod_fracoes) WHERE tf.cod_tamanhos='" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'] . "' AND f.fracoes='" . $_SESSION['ipi_carrinho']['pedido'][$a]['quant_fracao']. "' AND tf.cod_pizzarias = '".$cod_pizzarias."'", $conexao);
                
                echo '<li class="cor_cinza1">' . $_SESSION['ipi_carrinho']['pedido'][$a]['quant_fracao'].' '.($_SESSION['ipi_carrinho']['pedido'][$a]['quant_fracao'] != 1 ? "Sabores" : "Sabor").' '.($obj_preco_qnt_fracao->preco > 0 ? " - <span class='fonte12 cor_branco'>R$ ".bd2moeda($obj_preco_qnt_fracao->preco)."</span>" : "").'</li>';

                if (defined('PRODUTO_USA_GERGELIM'))
                {
                    if (PRODUTO_USA_GERGELIM=='S')
                    {
                                if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais'] != "N")
                                {
                                    $sqlAux = "SELECT itia.preco FROM ipi_adicionais ia INNER JOIN ipi_tamanhos_ipi_adicionais itia ON (ia.cod_adicionais = itia.cod_adicionais) WHERE ia.cod_adicionais=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais']." AND itia.cod_tamanhos = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos']." AND itia.cod_pizzarias=".$cod_pizzarias;
                                    //echo $sqlAux;
                                    $resAux = mysql_query($sqlAux);
                                    $objAux = mysql_fetch_object($resAux);
                                    echo '<li class="cor_cinza1 maior">Com gergelim na borda - <span class="fonte12 cor_branco">R$ '.$objAux->preco.'</span></li>';
                                }
                                else
                                {
                                    echo '<li class="cor_cinza1 maior">Sem gergelim na borda.</li>';
                                }
                    }
                }



                $sqlAux = "SELECT * FROM ipi_opcoes_corte oc INNER JOIN ipi_tamanhos_ipi_opcoes_corte toc ON (oc.cod_opcoes_corte = toc.cod_opcoes_corte) WHERE oc.cod_opcoes_corte = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_opcoes_corte']." AND toc.cod_tamanhos = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                echo '<li class="cor_cinza1">'.$objAux->opcao_corte;
                if($objAux->preco > 0)
                {
                    echo '&nbsp;('.bd2moeda($objAux->preco).')';   
                }
                echo '</li>';
                echo '</ul><div class="clear">&nbsp;</div>';
                echo '</div></div>';



                $num_fracoes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao']);

                $preco_fracao_maior = 0;
                for ($b = 0; $b < $num_fracoes; $b++)
                {
                    $sqlAux = "SELECT * FROM ipi_pizzas ip INNER JOIN ipi_pizzas_ipi_tamanhos ipit ON (ip.cod_pizzas = ipit.cod_pizzas) WHERE ip.cod_pizzas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas']." AND ipit.cod_pizzarias = ".$cod_pizzarias." AND ipit.cod_tamanhos = ". $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                    //echo "<br/>".$sqlAux."</br>";
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                     if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] == "1")
                    {
                        $preco_fracao = 0;
                    }
                    else
                    {
                        $preco_fracao = $objAux->preco;
                    }
                    
                    if($preco_fracao > $preco_fracao_maior)
                    {
                        $preco_fracao_maior = $preco_fracao;
                    }
                }

                for ($b = 0; $b < $num_fracoes; $b++)
                {
                    $sqlAux = "SELECT * FROM ipi_pizzas ip INNER JOIN ipi_pizzas_ipi_tamanhos ipit ON (ip.cod_pizzas = ipit.cod_pizzas) WHERE ip.cod_pizzas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas']." AND ipit.cod_pizzarias = ".$cod_pizzarias." AND ipit.cod_tamanhos = ". $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                    //echo "<br/>".$sqlAux."</br>";
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    echo '  <div class="resumo_meio_laranja fonte12">';
                    echo '<h3 class=" fonte18 cor_marrom2">'.($b + 1) . "º Sabor: ". $objAux->pizza.'&nbsp;&nbsp;&nbsp;<span class="cor_branco fonte18">';
                                      
                    if($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] == "1" || $_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] == "1" || $_SESSION['ipi_carrinho']['pedido'][$a]['pizza_fidelidade'] == "1")
                    {
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] == "1" && $_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] != "1")
                            echo " (GRÁTIS)</span>";
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] == "1")
                            echo " (COMBO)</span>";
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_fidelidade'] == "1")
                            echo " (FIDELIDADE)</span>";
                    }
                    else
                    {

                        if(REGRA_PRECO_DIVISAO_PIZZA=="IGUALMENTE")
                          {
                            $preco_fracao = ($objAux->preco / $num_fracoes);
                          }
                          else if(REGRA_PRECO_DIVISAO_PIZZA=="MAIOR")
                          {
                            $preco_fracao = ($preco_fracao_maior / $num_fracoes);
                          }

                      if($desconto)
                        $preco_fracao = $preco_fracao*0.5;

                      if($_SESSION['ipi_carrinho']['desconto_balcao'] == 'sim')
                        $preco_fracao = $preco_fracao*0.7;

                        echo "R$ ".bd2moeda(round(($preco_fracao)*100)/100);

                      if($desconto)
                        echo "   (50% DESCONTO)";

                      if($_SESSION['ipi_carrinho']['desconto_balcao'] == 'sim')
                        echo "   (30% DESCONTO)";

                        echo "</span>";
                    }
                    echo '</h3>';

                    $num_ingredientes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes']);
                    $ingredientes_padroes = array ();
                    $ingredientes_nao_padroes = array ();
                    $ingredientes_troca_cods = array ();
                    $ind_aux_padrao = 0;
                    $ind_aux_nao_padrao = 0;
                    for ($c = 0; $c < $num_ingredientes; $c++)
                    {
                        $sqlAux = "SELECT * FROM ipi_ingredientes WHERE cod_ingredientes=" . $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                        $resAux = mysql_query($sqlAux);
                        $objAux = mysql_fetch_object($resAux);
                        
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == true)
                        {
                            $ingredientes_padroes[$ind_aux_padrao] = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                            $ind_aux_padrao++;
                        } 
                        elseif ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == false)
                            {
                                if($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_troca'])
                                {
                                    $ingredientes_troca_cods[$ind_aux_nao_padrao] = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes_troca'];
                                }
                                $ingredientes_nao_padroes[$ind_aux_nao_padrao] = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                                $ind_aux_nao_padrao++;

                            }
                    }
                    
                    //Ingredientes padrão da pizza
                    $sql_buscar_pedidos_ingredientes_pizza = "SELECT i.* FROM ipi_ingredientes_ipi_pizzas ipip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ipip.cod_ingredientes) WHERE ipip.cod_pizzas = ".$_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas']." AND i.ativo = 1 AND i.consumo = 0 AND i.cod_ingredientes not in (116) ORDER BY ingrediente";
                    $res_buscar_pedidos_ingredientes_pizza = mysql_query($sql_buscar_pedidos_ingredientes_pizza);
                    echo '<h1 class="cor_branco fonte16">INGREDIENTES:</h1>';
                    echo '<ul class="lista_infos4">';
                      while($obj_buscar_pedidos_ingredientes_pizza = mysql_fetch_object($res_buscar_pedidos_ingredientes_pizza)) 
                      {
                        echo '<li>';
                          if (in_array($obj_buscar_pedidos_ingredientes_pizza->cod_ingredientes, $ingredientes_padroes))
                          {
                            echo '<img src="img/check_ticado.png" alt="Ingrediente na pizza"/>';
                          }
                          else
                          {
                            echo '<img src="img/check_desticado.png" alt="Ingrediente retirado da pizza"/>';
                          }
                          
                        echo '<img src="upload/ingredientes/'.(($obj_buscar_pedidos_ingredientes_pizza->foto_pequena) ? $obj_buscar_pedidos_ingredientes_pizza->foto_pequena : "23_ing_p.png").'" alt="'.$obj_buscar_pedidos_ingredientes_pizza->ingrediente.'" width="48" />';

                          if (in_array($obj_buscar_pedidos_ingredientes_pizza->cod_ingredientes, $ingredientes_padroes))
                          {
                                echo '<span>'.$obj_buscar_pedidos_ingredientes_pizza->ingrediente.'</span>';
                          }
                          else
                          {
                                echo '<span><s>'.$obj_buscar_pedidos_ingredientes_pizza->ingrediente.'</s></span>';
                          }
                        echo '</li>';
                      }      
                    echo '</ul>';
                    echo '<div class="clear">&nbsp;</div>';
                    
                    echo '<h1 class="cor_branco fonte16">ADICIONAIS:</h1>';
                    echo '<ul class="lista_infos4">';
                    $sql_buscar_promocoes = "select * from ipi_promocoes_ipi_pizzarias where cod_promocoes= '16' and cod_pizzarias = '$cod_pizzarias' and situacao='ATIVO'";
                    $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);

                    if (count($ingredientes_nao_padroes) > 0)
                    {
                        $sqlAux = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos iiip ON (i.cod_ingredientes = iiip.cod_ingredientes) WHERE i.cod_ingredientes IN (" . implode(",", $ingredientes_nao_padroes) . ") AND iiip.cod_pizzarias = ".$cod_pizzarias." AND iiip.cod_tamanhos = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                        //echo "<br/>".$sqlAux."<br/>";
                        $resAux = mysql_query($sqlAux);
                        $linAux = mysql_num_rows($resAux);
                        if ($linAux > 0)
                        {
                            $z = 0;
                            while ($obj_buscar_pedidos_ingredientes_pizza = mysql_fetch_object($resAux))
                            {
                                echo '<li>';
                                echo '<img src="img/check_ticado.png" alt="Ingrediente na pizza"/>';
                                
                                echo '<img src="upload/ingredientes/'.(($obj_buscar_pedidos_ingredientes_pizza->foto_pequena) ? $obj_buscar_pedidos_ingredientes_pizza->foto_pequena : "23_ing_p.png").'" alt="'.$obj_buscar_pedidos_ingredientes_pizza->ingrediente.'" width="48" />';

                                if($ingredientes_nao_padroes[$z] == $obj_buscar_pedidos_ingredientes_pizza->cod_ingredientes && $ingredientes_troca_cods[$z]!="")
                                {
                                    $sql_selecionar_preco_correto = "SELECT preco_troca FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos iiip ON (i.cod_ingredientes = iiip.cod_ingredientes) WHERE i.cod_ingredientes = '" . $ingredientes_troca_cods[$z] . "' AND iiip.cod_pizzarias = ".$cod_pizzarias." AND iiip.cod_tamanhos = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                                    $res_selecionar_preco_correto = mysql_query($sql_selecionar_preco_correto);
                                    $obj_preco_correto = mysql_fetch_object($res_selecionar_preco_correto);

                                    $preco = bd2moeda(arredondar_preco_ingrediente($obj_preco_correto->preco_troca,$num_fracoes));
                                }
                                else
                                {
                                    $preco = bd2moeda(arredondar_preco_ingrediente($obj_buscar_pedidos_ingredientes_pizza->preco,$num_fracoes));
                                }

                                $dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
                                if($ingredientes_nao_padroes[$z]==$this->cod_ingredientes_promocao_16 && mysql_num_rows($res_buscar_promocoes)>0  && $dia_semana[date("w")]=='Qua' && $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos']==3)
                                {
                                  $preco = '0,00';
                                }

                                echo '<span>'.$obj_buscar_pedidos_ingredientes_pizza->ingrediente.'<br /><div class="cor_branco">R$ '.$preco.'</div></span>';
                                echo '</li>';
                                $z++;
                            }
                        }
                    }
                    else
                        echo "<li class='sem'>Sem adicionais</li>";
                    echo '</ul>';
                    echo '<div class="clear">&nbsp;</div>';
                  echo '</div>';
                }
                    if($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] != '1')
                    {
                      /*if($desconto)
                        $preco_pizza = $preco_pizza*0.5;*/

                        echo '<div class="resumo_meio_laranja"><div class="preco_pizza_resumo" > <h1 class="fonte22 cor_marrom2">Total desta pizza:</h1> <h1 class="fonte20 cor_branco">R$ '.($preco_pizza==0 ? '0,00' : bd2moeda($preco_pizza)).'</h1></div></div>'; 
                    }
                    else
                    {
                        echo '<div class="resumo_meio_laranja"><div class="preco_pizza_resumo" > <h1 class="fonte22 cor_marrom2">Total deste combo:</h1> <h1 class="fonte20 cor_branco">R$ '.bd2moeda($preco_pizza).'</h1></div></div>';
                    }

                echo '  <div class="resumo_rodape_laranja"></div>';
                echo '</div>';
                echo '</div>';
            }
            
            desconectabd($conexao);
        }

        $numero_bebidas = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
        if ($numero_bebidas > 0)
        {           

            echo "<div class='caixa_padrao_resumo'>";
            echo "  <div class='resumo_topo_azul'></div>";
            echo "  <div class='resumo_meio_azul fonte12'>";

            echo "<h4 class='cor_branco fonte18'>BEBIDA</h4>";
            $conexao = conectabd();
            echo '<ul class="lista_infos3_resumo">';
            echo '<li> <strong>Bebida:</strong> </li>';
            echo '<li class="centralizar"> <strong>Quantidade:</strong> </li>';
            echo '<li class="centralizar"> <strong>Valor Unitário:</strong> </li>';
            echo '</ul>';
            
            $total_bebidas = 0;
            for ($a = 0; $a < $numero_bebidas; $a++)
            {
                $sqlAux = "SELECT b.bebida, c.conteudo, cp.preco FROM ipi_bebidas b INNER JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) INNER JOIN ipi_conteudos_pizzarias cp ON (cp.cod_bebidas_ipi_conteudos=bc.cod_bebidas_ipi_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $_SESSION['ipi_carrinho']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                $resAux = mysql_query($sqlAux);
                $obj_buscar_pedidos_bebidas = mysql_fetch_object($resAux);
                //echo $sqlAux."<br>";

                echo '<ul class="lista_infos3_resumo">';
                echo '<li>'.$obj_buscar_pedidos_bebidas->bebida.' '.$obj_buscar_pedidos_bebidas->conteudo.'</li>';
                echo '<li class="centralizar">'.$_SESSION['ipi_carrinho']['bebida'][$a]['quantidade'].'</li>';
                
                $preco = true;
                if ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_promocional'] == "1")
                {
                    echo "<li class='centralizar'>GRÁTIS</li>";
                    $preco = false;
                }
                elseif ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_fidelidade'] == "1")
                {
                    echo "<li class='centralizar'>FIDELIDADE</li>";
                    $preco = false;
                }
                elseif ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_combo'] == "1")
                {
                    echo "<li class='centralizar'>COMBO</li>";
                    $preco = false;
                }
                elseif ($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] == "5") //no balcao refri gratis
                {
                    echo '<li class="centralizar">'.bd2moeda(($this->preco_troca_promocao > 0 ? $this->preco_troca_promocao : 0)).'</li>';
                    $total_bebidas += $_SESSION['ipi_carrinho']['bebida'][$a]['quantidade']*($this->preco_troca_promocao > 0 ? $this->preco_troca_promocao : 0);
                    $preco = false;                    
                }
                elseif ($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] == "9") //pizza doce refri gratis
                {
                    echo "<li class='centralizar'>GRÁTIS</li>";
                    $preco = false;                    
                }
                elseif ($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] == "19") //pizza doce refri gratis
                {
                    echo "<li class='centralizar'>GRÁTIS</li>";
                    $preco = false;                    
                }

                if($preco)
                {
                   echo '<li class="centralizar">R$'.bd2moeda($obj_buscar_pedidos_bebidas->preco).'</li>';     
                   $total_bebidas += $_SESSION['ipi_carrinho']['bebida'][$a]['quantidade']*$obj_buscar_pedidos_bebidas->preco;
                }
                echo '</ul>';
                echo '<div class="clear"> <br /> </div>';                
            }
            echo '<h1 class="fonte22 cor_marrom2">Total das bebidas:</h1> <h1 class="fonte20 cor_branco">R$ '.($total_bebidas==0 ? '0,00' : bd2moeda($total_bebidas)).'</h1>';
            
            desconectabd($conexao);

            echo "  </div>";
            echo "  <div class='resumo_rodape_azul'></div>";
            echo "</div>";

        }

      /*  
      echo "<div class='caixa_padrao_resumo'>";
      echo "  <div class='resumo_topo_laranja'></div>";
      echo "  <div class='resumo_meio_laranja fonte12'>";
      echo "  Total do pedido: <span class='negrito'>R$ " . $this->exibir_total() . "</span>";
      echo "  </div>";
      echo "  <div class='resumo_rodape_laranja'></div>";
      echo "</div>";

      echo "<br /><div class='fonte14 centralizar'>Pedidos com produtos promocionais ou com gastos em fidelidade não podem ser refeitos!</div><br />";
      */
    }

    
    
    /**
     * Função que exibe o pedido completo para descontar fidelidade.
     */
    public function exibir_pedido_fidelidade()
    {
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        $cod_pizzarias = $cod_pizzarias = $this->retornar_codigo_pizzaria(); ;
        if ($numero_pizzas > 0)
        {
            
            echo "<br/><br/><table class='tabela_usar_fidelidade' width='500'  align='center'>";
            echo '<thead>';
            echo "<tr>";
            
            echo "<td width='50' align='center' class='td_usar_fidelidade'>";
            echo "<strong>Gastar?</strong>";
            echo "</td>";
            
            echo "<td width='50' align='center' class='td_usar_fidelidade'>";
            echo "<strong>Pontos</strong>";
            echo "</td>";
            
            echo "<td width='500' class='td_usar_fidelidade'>";
            echo "<strong>Seu pedido de hoje</strong>";
            echo "</td>";
            
            echo "</tr>";
            
            echo '</thead>';
            echo '<tbody>';
            
            $conexao2 = conectabd();
            $num_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
            for ($a = 0; $a < $num_pizzas; $a++)
            {
                
                $num_fracoes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao']);
                $preco_pizza = 0;
                for ($b = 0; $b < $num_fracoes; $b++)
                {
                    
                    $cod_pizzas = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    $num_fracao = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['num_fracao'];
                    $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'] ." and pt.cod_pizzarias = '".$cod_pizzarias."'";
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    $preco_fracao = ceil($objAux->pontos_fidelidade / $num_fracoes) / 1000;
                    $preco_pizza += ($preco_fracao);
                }
                
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] != "1")
                {
                    echo "<tr><td colspan='3' class='pizza' align='center'>" . ($a + 1) . "ª Pizza</td></tr>";
                    
                    echo "<tr>";
                    
                    echo "<td align='center' class='botao' class='td_usar_fidelidade'>";
                    $selecionado = "";
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_fidelidade'] == "1")
                        $selecionado = " checked='checked' ";
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] != "1")
                    {    
                        echo "<input type='checkbox' name='cbPontos[]' value='" . ($preco_pizza * 1000) . ",PIZZA,$a' " . $selecionado . " style='border: 0; background: none' onClick='javascript:desconta_fidelidade(this);' />";
                    }
                    else
                    {
                        echo "Combo";
                    }
                    echo "</td>";
                    
                    echo "<td class='pontos' align='center' class='td_usar_fidelidade'>";
                    
                    echo $preco_pizza * 1000;
                    echo "</td>";
                    
                    echo "<td class='td_usar_fidelidade'>";
                }
                
                $sqlAux = "SELECT * FROM ipi_tamanhos WHERE cod_tamanhos=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                echo '<span><strong>Tamanho:</strong> ' . $objAux->tamanho;
                echo '<br><strong>Quantidade de Sabores:</strong> ' . $_SESSION['ipi_carrinho']['pedido'][$a]['quant_fracao'];
                echo "</span></td>";
                
                echo "</tr>";
                
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] != "N")
                {
                    
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_promocional'] != "1")
                    {
                        $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] . " AND cod_tamanhos=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos']." and cod_pizzarias = '".$cod_pizzarias."'";
                        $resAux = mysql_query($sqlAux);
                        $objAux = mysql_fetch_object($resAux);
                        echo "<tr>";
                        
                        echo "<td align='center' class='botao'>";
                        $selecionado = "";
                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_fidelidade'] == "1")
                            $selecionado = " checked='checked' ";
                        if($_SESSION['ipi_carrinho']['pedido'][$a]['borda_combo']=='1')
                        {
                            echo "Combo";
                        }
                        else
                        {
                            echo "<input type='checkbox' name='cbPontos[]' value='" . $objAux->pontos_fidelidade . ",BORDA," . $a . "' " . $selecionado . " style='border: 0; background: none' onClick='javascript:desconta_fidelidade(this);' />";
                         }
                        echo "</td>";
                        echo "<td align='center' class='pontos'>";
                        echo $objAux->pontos_fidelidade;
                        echo "</td>";
                        
                        echo "<td class='conteudo' >";
                        $sqlAux = "SELECT * FROM ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'];
                        $resAux = mysql_query($sqlAux);
                        $objAux = mysql_fetch_object($resAux);
                        echo '<strong>Borda:</strong> ' . $objAux->borda;
                        echo "</td>";
                        
                        echo "</tr>";
                    }
                }
            
            }
            
            echo '</tbody>';
            echo "</table>";
            
            desconectabd($conexao2);
        }
        
        $numero_bebidas = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
        if ($numero_bebidas > 0)
        {
            echo "<br/><br/><table width='500' border='0' align='center' class='tabela_usar_fidelidade'>";
            echo '<thead>';
            //echo "<tr><td colspan='3' align='center'>BEBIDAS</td></tr>";
            echo "<tr>";
            
            echo "<td width='50' align='center'>";
            echo "<strong>Gastar</strong>";
            echo "</td>";
            
            echo "<td width='50' align='center'>";
            echo "<strong>Pontos</strong>";
            echo "</td>";
            
            echo "<td width='500'>";
            echo "<strong>Seu pedido de hoje</strong>";
            echo "</td>";
            
            echo "</tr>";
            
            echo '</thead>';
            echo '<tbody>';
            
            $conexao2 = conectabd();
            if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
            {
              $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
            }
            else
            {
              $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
              $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
              $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
              $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
              $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
              $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
            }
            for ($a = 0; $a < $numero_bebidas; $a++)
            {
                if ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_promocional'] != "1" && $_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] !="9" && $_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] !="19" &&$_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] != "5" )
                {

                    $sqlAux = "SELECT * FROM ipi_bebidas b INNER JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) inner join ipi_conteudos_pizzarias cp on cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos WHERE cp.cod_pizzarias = ".$cod_pizzarias." and bc.cod_bebidas_ipi_conteudos=" . $_SESSION['ipi_carrinho']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    echo "<tr>";
                    
                    echo "<td align='center' class='botao'>";
                    $selecionado = "";
                    if ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_fidelidade'] == "1")
                        $selecionado = " checked='checked' ";
                    if ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_combo'] != "1")
                    {
                        echo "<input type='checkbox' name='cbPontos[]' value='" . ($objAux->pontos_fidelidade * $_SESSION['ipi_carrinho']['bebida'][$a]['quantidade']) . ",BEBIDA," . $a . "' " . $selecionado . " style='border: 0; background: none' onClick='javascript:desconta_fidelidade(this);' />";
                    }
                    else
                    {
                        echo "Combo";
                    }
                    echo "</td>";
                    
                    echo "<td align='center' class='pontos'>";
                    echo $objAux->pontos_fidelidade * $_SESSION['ipi_carrinho']['bebida'][$a]['quantidade'];
                    echo "</td>";
                    
                    echo "<td class='conteudo'>";
                    echo '<strong>Quantidade:</strong> ' . $_SESSION['ipi_carrinho']['bebida'][$a]['quantidade'];
                    echo '<br><strong>Sabor:</strong> ' . $objAux->bebida . " - " . $objAux->conteudo . "<br>";
                    echo "</td>";
                    
                    echo "</tr>";
                }
            }
            echo '</tbody>';
            echo "</table>";
            desconectabd($conexao2);
        }
    
    }
    
    /**
     * Função que exibe os endereços de entrega para escolha, retorna uma table com todos os endereços com radiobutton.
     */
    
    public function exibir_enderecos_entrega ($codigo_cliente)
    {
        $tabela_enderecos = '<table border="1" cellspacing="0" cellpadding="2" class="tabela" >';
        
        $conexao = conectabd();
        $sqlEnderecos = "SELECT * FROM ipi_enderecos e WHERE cod_clientes=" . $codigo_cliente;
        $resEnderecos = mysql_query($sqlEnderecos);
        $linEnderecos = mysql_num_rows($resEnderecos);
        $arr_promocoes_bebidas = array();
        $arr_promocoes_bordas = array();
        $arr_promocoes_pizzas = array();
        $promocaoFreteGratis = false;

        if ($linEnderecos > 0)
        {
            $tabela_enderecos .= '<thead>';
            $tabela_enderecos .= '<tr>';
            $tabela_enderecos .= '  <td>';
            $tabela_enderecos .= '<span class="fonte18 cor_marrom2 padding_15">Selecione</span>';
            $tabela_enderecos .= '  </td>';
            $tabela_enderecos .= '  <td >';
            $tabela_enderecos .= '<span class="fonte18 cor_marrom2 padding_15">Endereço</span>';
            $tabela_enderecos .= '  </td>';
            $tabela_enderecos .= '</tr>';
            $tabela_enderecos .= '</thead>';
            
            $tabela_enderecos .= '<tbody>';
            $num_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
            for ($p = 0; $p < $num_pizzas; $p++)
            {

                if ($_SESSION['ipi_carrinho']['pedido'][$p]['pizza_fidelidade'] == '1')
                {
                    $_SESSION['ipi_carrinho']['frete_gratis_fidelidade'] = true;
                    $fidelidade_com_frete_gratis = $_SESSION['ipi_carrinho']['frete_gratis_fidelidade'];
                }
                
                if($_SESSION['ipi_carrinho']['pedido'][$p]['pizza_promocional'] =="1")
                {
                    if($_SESSION['ipi_carrinho']['pedido'][$p]['pizza_promo_cod'] !="" && $_SESSION['ipi_carrinho']['pedido'][$p]['pizza_promo_cod'] >0)
                    {
                        $arr_promocoes_pizzas[] = $_SESSION['ipi_carrinho']['pedido'][$p]['pizza_promo_cod'] ;
                    }
                }

                if ($_SESSION['ipi_carrinho']['pedido'][$p]['cod_bordas'] != "N")
                {
                    if ($_SESSION['ipi_carrinho']['pedido'][$p]['borda_promocional'] == "1")
                    {
                        if($_SESSION['ipi_carrinho']['pedido'][$p]['borda_promo_cod']!="" && $_SESSION['ipi_carrinho']['pedido'][$p]['borda_promo_cod'] >0)
                        {
                            $arr_promocoes_bordas[] = $_SESSION['ipi_carrinho']['pedido'][$p]['borda_promo_cod'];
                        }
                    }
                }
            }

            $numero_bebidas = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
            if ($numero_bebidas > 0)
            {
                for ($a = 0; $a < $numero_bebidas; $a++)
                {
                    if($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro']>0 && $_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro']!="") 
                    {
                        $arr_promocoes_bebidas[] = $_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'];
                    }
                }
            }
            for ($a = 0; $a < $linEnderecos; $a++)
            {
                 
                $arr_promocoes_permitidas = array();
                $arr_promocoes_permitidas_cod = array();
                $objEnderecos = mysql_fetch_object($resEnderecos);
                
                $cep_limpo = str_replace("-", "", str_replace('.', '', $objEnderecos->cep));
                $sqlVerificarCEP = "SELECT COUNT(*) AS contagem FROM ipi_cep c INNER JOIN ipi_pizzarias p on p.cod_pizzarias = c.cod_pizzarias WHERE c.cep_inicial <= $cep_limpo AND c.cep_final >= $cep_limpo AND p.situacao !='INATIVO'";
                //echo "<br/>".$sqlVerificarCEP."</br>";
                $resVerificarCEP = mysql_query($sqlVerificarCEP);
                $objVerificarCEP = mysql_fetch_object($resVerificarCEP);
                if ($objVerificarCEP->contagem > 0)
                {

                    $sql_cod_pizzarias = "SELECT c.cod_pizzarias,tx.valor_frete as frete,min.valor_pedido_minimo FROM ipi_cep c inner join ipi_pizzarias p on p.cod_pizzarias = c.cod_pizzarias left join ipi_taxa_frete tx on c.cod_taxa_frete = tx.cod_taxa_frete  left join ipi_pedido_minimo min on min.cod_pedido_minimo = c.cod_pedido_minimo WHERE c.cep_inicial <= $cep_limpo AND c.cep_final >= $cep_limpo AND p.cod_pizzarias !='INATIVO'";
                    $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
                    $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);

                    $arr_dias_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab');
                    $dia_semana_hoje = $arr_dias_semana[date('w')]; 
                    require("pub_req_fuso_horario1.php");
                    $sql_funcionamento = "SELECT * FROM ipi_pizzarias_funcionamento WHERE cod_pizzarias = '".$obj_cod_pizzarias->cod_pizzarias."' AND dia_semana='".date('w')."' and CURTIME() < ADDTIME(horario_final, '00:05:01')";
                    //echo "<br/>1: ".$sql_funcionamento;
                    $res_funcionamento = mysql_query($sql_funcionamento);
                    $num_funcionamento = mysql_num_rows($res_funcionamento);

                    $sql_buscar_promocoes = "select p.promocao, p.cod_promocoes from ipi_promocoes_ipi_pizzarias pz inner join ipi_promocoes p on p.cod_promocoes = pz.cod_promocoes where cod_pizzarias ='".$obj_cod_pizzarias->cod_pizzarias."' and pz.situacao='ATIVO'";
                    $res_buscar_promocoes = mysql_query($sql_buscar_promocoes);
                   // echo "<br/>$sql_buscar_promocoes<br/>";
                    while($obj_buscar_promocoes = mysql_fetch_object($res_buscar_promocoes))
                    {
                        $arr_promocoes_permitidas[] = $obj_buscar_promocoes->promocao;
                        $arr_promocoes_permitidas_cod[] = $obj_buscar_promocoes->cod_promocoes;
                    }

                    // echo "<br/>".$obj_cod_pizzarias->cod_pizzarias."<pre>";
                    // print_r($arr_promocoes_bebidas);
                    // echo "</pre><br/><pre>";
                    // print_r($arr_promocoes_permitidas);
                    // echo "</pre><br/><br/><pre>";
                    // print_r($resultado);
                    // echo "</pre><br/>";

                    

                    if ($num_funcionamento>0)
                    {
                        if(defined("COD_PROMOCAO_FRETE_GRATIS")){
                            if (in_array(COD_PROMOCAO_FRETE_GRATIS, $arr_promocoes_permitidas_cod)){
                                $promocaoFreteGratis = true;
                                $_SESSION['ipi_carrinho']['PromocaoFreteGratis'] = true;
                            
                            }
                        }
                        // print_r($_SESSION['ipi_carrinho']['pagamento']);
                        // die('Promocao? '.$promocaoFreteGratis);
                        if(is_array($arr_promocoes_bebidas))
                        {
                            $resultado = array_diff($arr_promocoes_bebidas,$arr_promocoes_permitidas);//pegar a qtd de promocções de bebidas utilizadas sem vinculo com a pizzaria
                            $total_bebidas = count($resultado);
                        }
                        else
                        {
                            $total_bebidas= 0;
                        }
                        if($total_bebidas<=0)
                        {
                            
                            if(is_array($arr_promocoes_bordas))
                            {
                                $resultado = array_diff($arr_promocoes_bordas,$arr_promocoes_permitidas_cod);//pegar a qtd de promocções de bordas utilizadas sem vinculo com a pizzaria
                                $total_bordas = count($resultado);
                            }
                            else
                            {
                                $total_bordas= 0;
                            }

                            if($total_bordas<=0)
                            {
                                if(is_array($arr_promocoes_bordas))
                                {
                                    $resultado = array_diff($arr_promocoes_pizzas,$arr_promocoes_permitidas_cod);//pegar a qtd de promocções de bordas utilizadas sem vinculo com a pizzaria
                                    $total_pizzas = count($resultado);
                                }
                                else
                                {
                                    $total_pizzas= 0;
                                }

                                if($total_pizzas<=0)
                                {
                                    $tabela_enderecos .= '<tr>';
                                    if((isset($_SESSION['ipi_carrinho']['cupom']) && in_array($obj_cod_pizzarias->cod_pizzarias, $_SESSION['ipi_carrinho']['cupom_pizzarias'])) || !isset($_SESSION['ipi_carrinho']['cupom']))
                                    {
                                        if(($_SESSION['ipi_carrinho']['total_pedido']+$obj_cod_pizzarias->frete) >= $obj_cod_pizzarias->valor_pedido_minimo || $fidelidade_com_frete_gratis == true || $promocaoFreteGratis == true)
                                        {
                                            // die($fidelidade_com_frete_gratis);
                                            // if ($fidelidade_com_frete_gratis==true)
                                            // {
                                            //             $tabela_enderecos .= '  <td align="center" class="padding_15">';
                                            //             $tabela_enderecos .= '  <input name="cod_enderecos" id="cod_enderecos'.$objEnderecos->cod_enderecos.'" type="radio" value="' . $objEnderecos->cod_enderecos . '" '.($linEnderecos==1 ? 'checked="checked"' : "").' style="border: 0px; background: none;" onClick="javascript:carregar_pagamentos(\'Entregar\', this.value,\'0.00\');" />';
                                            // }
                                            // else
                                            // {
                                                        $tabela_enderecos .= '  <td align="center" class="padding_15">';
                                                        $tabela_enderecos .= '  <input name="cod_enderecos" id="cod_enderecos'.$objEnderecos->cod_enderecos.'" type="radio" value="' . $objEnderecos->cod_enderecos . '" '.($linEnderecos==1 ? 'checked="checked"' : "").' style="border: 0px; background: none;" onClick="javascript:carregar_pagamentos(\'Entregar\', this.value,\''.($fidelidade_com_frete_gratis==true || $promocaoFreteGratis == true? "0.00": $obj_cod_pizzarias->frete).'\');" />';
                                            // }
                                          
                                            if($linEnderecos == 1)
                                            {
                                                $tabela_enderecos .= '<input type="hidden" name="ativar_pagamento" id="ativar_pagamento" value="' . $objEnderecos->cod_enderecos . '" />';
                                                $tabela_enderecos .= '<input type="hidden" name="valor_frete_pagamento" id="valor_frete_pagamento" value="' . ($fidelidade_com_frete_gratis==true || $promocaoFreteGratis == true? "0.00": $obj_cod_pizzarias->frete) . '" />';
                                            }
                                            else
                                            {
                                                $tabela_enderecos .= '<input type="hidden" name="ativar_pagamento" id="ativar_pagamento" value="0" />';
                                                $tabela_enderecos .= '<input type="hidden" name="valor_frete_pagamento" id="valor_frete_pagamento" value="' . ($fidelidade_com_frete_gratis==true || $promocaoFreteGratis == true? "0.00": $obj_cod_pizzarias->frete) . '" />';
                                            }
                                            if($obj_cod_pizzarias->frete>0)
                                            {
                                                if ($fidelidade_com_frete_gratis==true )
                                                {
                                                    $tabela_enderecos .= "<br/>Sem Custo<br/>(Fidelidade)";
                                                }
                                                if ($promocaoFreteGratis == true) {
                                                    $tabela_enderecos .= "<br/>Sem Custo<br/>(Promoção Frete Grátis)";
                                                }
                                                else
                                                {
                                                    $tabela_enderecos .= "<br/>Custo da<br/>Entrega : R$".bd2moeda($obj_cod_pizzarias->frete);
                                                }
                                                
                                            }
                                             $tabela_enderecos .= '  </td>';
                                        }
                                        else
                                        {
                                            $tabela_enderecos .= '<tr>';
                                            $tabela_enderecos .= '  <td class="padding_15">';
                                            $tabela_enderecos .= '  Pedido minimo de<br />R$ '.bd2moeda($obj_cod_pizzarias->valor_pedido_minimo);
                                            $tabela_enderecos .= '  </td>';
                                        }
                                    }
                                    else
                                    {
                                        $tabela_enderecos .= '<tr>';
                                        $tabela_enderecos .= '  <td class="cor_amarelo1 fonte14 centralizar">';
                                        $tabela_enderecos .= '  A Pizzaria que antende este <br /> endereço não participa da <br/> promoção do cupom utilizado';// <br/> (Para continuar, remova-o)
                                        $tabela_enderecos .= '  </td>';
                                    }
                                }
                                else
                                {
                                    $tabela_enderecos .= '<tr>';
                                    $tabela_enderecos .= '  <td class="cor_amarelo1 fonte14 centralizar">';
                                    $tabela_enderecos .= '  A Pizzaria que antende este <br /> endereço não participa da <br/> promoção de pizza escolhida';// <br/> (Para continuar, remova-o)
                                    $tabela_enderecos .= '  </td>';
                                }
                            }
                            else
                            {
                                $tabela_enderecos .= '<tr>';
                                $tabela_enderecos .= '  <td class="cor_amarelo1 fonte14 centralizar">';
                                $tabela_enderecos .= '  A Pizzaria que antende este <br /> endereço não participa da <br/> promoção de borda escolhida';// <br/> (Para continuar, remova-o)
                                $tabela_enderecos .= '  </td>';
                            }
                        }
                        else
                        {
                            $tabela_enderecos .= '<tr>';
                            $tabela_enderecos .= '  <td class="cor_amarelo1 fonte14 centralizar">';
                            $tabela_enderecos .= '  A Pizzaria que antende este <br /> endereço não participa da <br/> promoção de bebida escolhida';// <br/> (Para continuar, remova-o)
                            $tabela_enderecos .= '  </td>';
                        }
                           
                            $tabela_enderecos .= '  <td class="padding_15 cor_branco">';
                            $tabela_enderecos .= '<label for="cod_enderecos'.$objEnderecos->cod_enderecos.'">';
                            $tabela_enderecos .= '<strong>' . $objEnderecos->apelido . '</strong>';
                            $tabela_enderecos .= '<br/>' . $objEnderecos->endereco . ', ' . $objEnderecos->numero . ($objEnderecos->complemento != "" ? ', '.$objEnderecos->complemento : '').($objEnderecos->edificio != "" ? '- '.$objEnderecos->edificio : '');
                            $tabela_enderecos .= '<br/>' . $objEnderecos->cidade . ' | ' . $objEnderecos->bairro . ' | ' . $objEnderecos->cep;
                            $tabela_enderecos .= '<br/>' . str_replace('(','',str_replace(')',' ',$objEnderecos->telefone_1));
                            $tabela_enderecos .= '</label>';
                            $tabela_enderecos .= '  </td>';
                            $tabela_enderecos .= '</tr>';
                       
                    }
                    else
                    {
                        $tabela_enderecos .= '<tr>';
                        $tabela_enderecos .= '  <td class="cor_amarelo1 fonte14">';
                        $tabela_enderecos .= '  Pizzaria<br />Fechada';
                        $tabela_enderecos .= '  </td>';
                        $tabela_enderecos .= '  <td class="padding_15 cor_branco">';
                        $tabela_enderecos .= '<strong>' . $objEnderecos->apelido . '</strong>';
                        $tabela_enderecos .= '<br/>' . $objEnderecos->endereco . ', ' . $objEnderecos->numero . ($objEnderecos->complemento != "" ? ', '.$objEnderecos->complemento : '').($objEnderecos->edificio != "" ? '- '.$objEnderecos->edificio : '');
                        $tabela_enderecos .= '<br/>' . $objEnderecos->cidade . ' | ' . $objEnderecos->bairro . ' | ' . $objEnderecos->cep;
                        $tabela_enderecos .= '<br/>' . str_replace('(','',str_replace(')',' ',$objEnderecos->telefone_1));
                        $tabela_enderecos .= '  </td>';
                        $tabela_enderecos .= '</tr>';
                    }
                }
                else
                {
                    $tabela_enderecos .= '<tr>';
                    $tabela_enderecos .= '  <td class="padding_15 cor_amarelo1 fonte14">';
                    $tabela_enderecos .= '  Não entregamos<br/>neste endereço';
                    $tabela_enderecos .= '  </td>';
                    $tabela_enderecos .= '  <td class="padding_15 cor_branco">';
                    $tabela_enderecos .= '<strong>' . $objEnderecos->apelido . '</strong>';
                    $tabela_enderecos .= '<br/>' . $objEnderecos->endereco . ', ' . $objEnderecos->numero . ($objEnderecos->complemento != "" ? ', '.$objEnderecos->complemento : '').($objEnderecos->edificio != "" ? '- '.$objEnderecos->edificio : '');
                    $tabela_enderecos .= '<br/>' . $objEnderecos->cidade . ' | ' . $objEnderecos->bairro . ' | ' . $objEnderecos->cep;
                    $tabela_enderecos .= '<br/>' . str_replace('(','',str_replace(')',' ',$objEnderecos->telefone_1));
                    $tabela_enderecos .= '  </td>';
                    $tabela_enderecos .= '</tr>';
                }
            }
            $tabela_enderecos .= '</tbody>';
        }

        desconectabd($conexao);
        $tabela_enderecos .= '</table>';
        return ($tabela_enderecos);
    }
    
    /**
     * Função que finaliza o pedido, transfere a SESSION para o BD e retorna o numero do pedido.
     */
    
    public function finalizar_pedido ()
    {
        $conexao = conectar_bd();

        //echo "<br />:::: ".$_SESSION['ipi_cliente']['codigo'];
        //echo "<br />:::: ".$_SESSION['ipi_carrinho']['buscar_balcao'];
        //$_SESSION['ipi_carrinho']['buscar_balcao']= "Balcão";  //################ retirar

        if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
        {
            $sqlCliente = "SELECT * FROM ipi_clientes c WHERE c.cod_clientes=" . $_SESSION['ipi_cliente']['codigo'];
            $resCliente = mysql_query($sqlCliente);
            $objCliente = mysql_fetch_object($resCliente);
            
            $codPizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
        }
        else
        {
            $sqlCliente = "SELECT * FROM ipi_clientes c INNER JOIN ipi_enderecos e ON (e.cod_clientes=c.cod_clientes) WHERE c.cod_clientes=" . $_SESSION['ipi_cliente']['codigo'] . " AND e.cod_enderecos=" . $_SESSION['ipi_carrinho']['pagamento']['cod_enderecos'];
            $resCliente = mysql_query($sqlCliente);
            $objCliente = mysql_fetch_object($resCliente);
            // echo "<br>:: ".$sqlCliente;

            
            $sqlPizzaria = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_cep c ON (p.cod_pizzarias=c.cod_pizzarias) WHERE c.cep_inicial<=" . str_replace(".", "", str_replace("-", "", $objCliente->cep)) . " AND c.cep_final>=" . str_replace(".", "", str_replace("-", "", $objCliente->cep)) . " GROUP BY p.cod_pizzarias";
            $resPizzaria = mysql_query($sqlPizzaria);
            $objPizzaria = mysql_fetch_object($resPizzaria);
            $codPizzarias = $objPizzaria->cod_pizzarias;
            // echo "<br>::: ".$sqlPizzaria;

        }
        $_SESSION['cod_pizzarias_atendeu'] = $codPizzarias; // <== usado na ultima pagina para segmentar por pizzaras as metas de conversão das publicidades (google, facebook, etc)

        /*
        // Cliente pediu Desvio forçado do aquarius pra adyana na segunda
        if((date('w')==1) && ($codPizzarias==14))
        {
          $codPizzarias = 1;
        }
        */
            $agendado = ($_SESSION['ipi_carrinho']['agendar'] == 'Sim') ? 1 : 0;

            //             require 'pub_req_fuso_horario1.php';
                     
            //              if (defined('HORARIO_INICIO_CENTRAL_PIZZARIA')){
            //                 $tempoInicio = HORARIO_INICIO_CENTRAL_PIZZARIA;
            //             }
            //             else{
            //                     $tempoInicio = '08:00:00'; //valor default
            //             }

            //             if (defined('HORARIO_FIM_CENTRAL_PIZZARIA')){
            //                 $tempoFim = HORARIO_FIM_CENTRAL_PIZZARIA;
            //             }
            //             else{
            //                     $tempoFim = '17:00:00'; //valor default
            //             }


            //             $dateTime = new DateTime($tempoInicio);
            //             $dateTime2 = new DateTime($tempoFim);
            //             $horarioAgendamentoAux = $_SESSION['ipi_carrinho']['horario'];
 
                        // if ($dateTime->diff(new DateTime)->format('%R') == '-' ) {
                        // if (($dateTime->diff(new DateTime)->format('%R') == '+') && ($dateTime2->diff(new DateTime)->format('%R')== '-')) {

                            // if($agendado==0){
                            //      if (defined('CODIGO_PIZZARIA_CENTRAL')){
                            //         $codPizzarias = CODIGO_PIZZARIA_CENTRAL;
                            //     }
                            //     else{
                            //         $codPizzarias = 1; //valor default
                            //     }
                            // }
                            // else{

                            //             $horarioAgendamento = DateTime::createFromFormat('H:i', $horarioAgendamentoAux);
                            //             if ($horarioAgendamento >$dateTime && $horarioAgendamento < $dateTime2)
                            //             {
                            //                 if (defined('CODIGO_PIZZARIA_CENTRAL')){
                            //                         $codPizzarias = CODIGO_PIZZARIA_CENTRAL;
                            //                     }
                            //                     else{
                            //                         $codPizzarias = 1; //valor default
                            //                     }
                            //             }
                            //     }



                        // }


        //echo "<br />codPizzarias: ".$codPizzarias;

        //$codPizzarias = 1; //################ retirar



        require ("pub_req_fuso_horario1.php");

        $impressao_fiscal = "0";
        if ($_SESSION['ipi_carrinho']['pagamento']['cpf_nota_paulista'] != '' || $_SESSION['ipi_carrinho']['pagamento']['tipo'] != "DINHEIRO")
        {
             $impressao_fiscal = "1";
        }
        
        if($impressao_fiscal == "0")
        {
            $sql_impressao = "SELECT impressao_automatica FROM ipi_pizzarias p WHERE cod_pizzarias = ".$codPizzarias;
            //echo "<br />sql_fiscal: ".$sql_impressao;
            $res_impressao = mysql_query($sql_impressao);
            $obj_impressao = mysql_fetch_object($res_impressao);
                $impressao_fiscal = $obj_impressao->impressao_automatica;
            //echo "<br />impressao_fiscal: ".$impressao_fiscal;
        }

        if ( ($_SESSION['ipi_carrinho']['pagamento']['cpf_nota_paulista'] == '') && ($impressao_fiscal=="1") )
        {
          // 000.000.000-00 - é para que queria cupom mais, não identificar o CPF
          $_SESSION['ipi_carrinho']['pagamento']['cpf_nota_paulista'] = "000.000.000-00";
        }
        
        $forma_pagamento = "Múltiplas";

        $cod_pedido_repetido = (isset($_SESSION['ipi_carrinho']['pedido_repetido']) ? $_SESSION['ipi_carrinho']['pedido_repetido'] : "0");

        if ($_SESSION['ipi_carrinho']['PromocaoFreteGratis']==true){
                        $_SESSION['ipi_carrinho']['pagamento']['valor_frete'] = 0;
                        $_SESSION['ipi_carrinho']['pagamento']['comissao_frete'] = 0;
        }

        // print_r($_SESSION['ipi_carrinho']['pagamento']);
        // die();
        $total_pedido = ($_SESSION['ipi_carrinho']['pagamento']['valor_frete']>0 && $_SESSION['ipi_carrinho']['pagamento']['valor_frete'] !="" ? $_SESSION['ipi_carrinho']['total_pedido'] + $_SESSION['ipi_carrinho']['pagamento']['valor_frete'] : $_SESSION['ipi_carrinho']['total_pedido']);

        $sqlInserirPedido = 'INSERT INTO ipi_pedidos (cod_clientes, cod_pizzarias, data_hora_pedido, valor, valor_entrega, valor_comissao_frete, valor_total, forma_pg, situacao, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2, tipo_entrega, horario_agendamento, agendado, pontos_fidelidade_total, data_hora_inicial, data_hora_final, impressao_fiscal, cod_pedidos_repetido) VALUES 
        ("' . $objCliente->cod_clientes . '", "' . $codPizzarias . '", "' . date("Y-m-d H:i:s") . '", "' . $_SESSION['ipi_carrinho']['total_pedido'] . '","'.$_SESSION['ipi_carrinho']['pagamento']['valor_frete'].'","'.$_SESSION['ipi_carrinho']['pagamento']['comissao_frete'].'", "' . $total_pedido . '", "' . $forma_pagamento . '", "NOVO", "' . filtrar_caracteres_sql($objCliente->endereco) . '", "' . filtrar_caracteres_sql($objCliente->numero) . '", "' . filtrar_caracteres_sql($objCliente->complemento) . '", "' . filtrar_caracteres_sql($objCliente->edificio) . '", "' . filtrar_caracteres_sql($objCliente->bairro) . '", "' . filtrar_caracteres_sql($objCliente->cidade) . '", "' . filtrar_caracteres_sql($objCliente->estado) . '", "' . filtrar_caracteres_sql($objCliente->cep) . '", "' . $objCliente->telefone_1 . '", "' . $objCliente->telefone_2 . '", "' . $_SESSION['ipi_carrinho']['buscar_balcao'] . '", "' . $_SESSION['ipi_carrinho']['horario'] . '", "'.$agendado.'", "' . $_SESSION['ipi_carrinho']['fidelidade_pontos_gastos'] . '", "' . $_SESSION['ipi_carrinho']['data_hora_inicial'] . '", "'.date("Y-m-d H:i:s").'", "'.$impressao_fiscal.'", "'.$cod_pedido_repetido.'")';
        //echo $sqlInserirPedido;
        $resInserirPedido = mysql_query($sqlInserirPedido);
        $cod_pedidos = mysql_insert_id();
        //echo "<br>sqlInserirPedido: ".$sqlInserirPedido;
        
        //$this->log($conexao, "PEDIDO_INSERIDO", "", $cod_pedidos, 0, $_SESSION['ipi_cliente']['codigo']);



        //NOVA TABELA DE MULTIPLAS FORMAS DE PAGAMENTO
        $sql_forma_pgto = "SELECT cod_formas_pg FROM ipi_formas_pg WHERE forma_pg = '".$_SESSION['ipi_carrinho']['pagamento']['tipo']."'";
        $res_forma_pgto = mysql_query($sql_forma_pgto);
        $num_forma_pgto = mysql_num_rows($res_forma_pgto);
        if ($num_forma_pgto>0)
        {
          $obj_forma_pgto = mysql_fetch_object($res_forma_pgto);
          $cod_formas_pg = $obj_forma_pgto->cod_formas_pg;
        }
        else // caso de erro, não ache a forma força dinheiro
        {
          $cod_formas_pg = 1;
        }

  
        $sql_forma_pgto = "INSERT INTO ipi_pedidos_formas_pg (cod_pedidos, cod_formas_pg, valor) VALUES ($cod_pedidos, '".$cod_formas_pg."', '" . ($total_pedido) . "')";
        $res_forma_pgto = mysql_query($sql_forma_pgto);
        //echo "<br>X: ".$sql_forma_pgto;
        
        
        if (($_SESSION['ipi_carrinho']['pagamento']['troco'] != "0") && ($_SESSION['ipi_carrinho']['pagamento']['troco'] != ""))
        {
            $sqlDetalhesPgto = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos, 'TROCO', '" . moeda2bd($_SESSION['ipi_carrinho']['pagamento']['troco']) . "')";
            $resDetalhesPgto = mysql_query($sqlDetalhesPgto);
        }
        
        if ($_SESSION['ipi_carrinho']['pagamento']['cpf_nota_paulista'] != '')
        {
            $sqlDetalhesPgto = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos, 'CPF_NOTA_PAULISTA', '" . $_SESSION['ipi_carrinho']['pagamento']['cpf_nota_paulista'] . "')";
            $resDetalhesPgto = mysql_query($sqlDetalhesPgto);
        }
        
        if ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "VISANET1")
        {
            
            $sqlDetPag = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'cod_pedido_operadora','" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "')";
            $resDetPag = mysql_query($sqlDetPag);
            
            $sqlAux = "SELECT * FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "'";
            $resAux = mysql_query($sqlAux);
            
            while ($objAux = mysql_fetch_object($resAux))
            {
                $sqlCupom = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'" . $objAux->chave . "','" . $objAux->valor . "')";
                $resCupom = mysql_query($sqlCupom);
            }
            
            $sqlAux = "DELETE FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "'";
            $resAux = mysql_query($sqlAux);
        
        }

        
        if ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "VISANET")
        {
            
            $sqlDetPag = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'cod_pedido_operadora','" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "')";
            $resDetPag = mysql_query($sqlDetPag);
            
            $sqlAux = "SELECT * FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "'";
            $resAux = mysql_query($sqlAux);
            $numAux = mysql_num_rows($resAux);
            
            while ($objAux = mysql_fetch_object($resAux))
            {
                $sqlCupom = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'" . $objAux->chave . "','" . $objAux->valor . "')";
                $resCupom = mysql_query($sqlCupom);
            }

            if ($numAux>4) //Trava para não apagar os dados temporários em caso de erro
            {
              $sqlAux = "DELETE FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "'";
              $resAux = mysql_query($sqlAux);
            }
        
        }


        if ( ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "VISANET-CIELO") || ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "MASTERCARDNET-CIELO") || ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "AMEXNET-CIELO") || ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "DINERSNET-CIELO") || ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "ELONET-CIELO") || ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "DISCOVERNET-CIELO") || ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "AURANET-CIELO") || ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "JCBNET-CIELO") )
        {
            
            $sqlDetPag = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'cod_pedido_operadora','" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "')";
            $resDetPag = mysql_query($sqlDetPag);
            
            $sqlAux = "SELECT * FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "'";
            $resAux = mysql_query($sqlAux);
            $numAux = mysql_num_rows($resAux);
            
            while ($objAux = mysql_fetch_object($resAux))
            {
                $sqlCupom = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'" . $objAux->chave . "','" . $objAux->valor . "')";
                $resCupom = mysql_query($sqlCupom);
            }

            if ($numAux>4) //Trava para não apagar os dados temporários em caso de erro
            {
              $sqlAux = "DELETE FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "'";
              $resAux = mysql_query($sqlAux);
            }
        
        }
        

        
        if ($_SESSION['ipi_carrinho']['pagamento']['tipo'] == "MASTERCARDNET")
        {
            
            $sqlDetPag = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'cod_pedido_operadora','" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "')";
            $resDetPag = mysql_query($sqlDetPag);
            
            $sqlAux = "SELECT * FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "'";
            $resAux = mysql_query($sqlAux);
            $numAux = mysql_num_rows($resAux);
            
            while ($objAux = mysql_fetch_object($resAux))
            {
                $sqlCupom = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'" . $objAux->chave . "','" . $objAux->valor . "')";
                $resCupom = mysql_query($sqlCupom);
            }

            if ($numAux>4) //Trava para não apagar os dados temporários em caso de erro
            {
              $sqlAux = "DELETE FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_carrinho']['pagamento']['cod_pedido_operadora'] . "'";
              $resAux = mysql_query($sqlAux);
            }        
        }
        
        $arr_promo_carnaval = array();
        $arr_indice_menor = array();
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;

        if ($numero_pizzas > 0) //verificar preço da pizza para promoção de carnaval
        {
          for ($a = 0; $a < $numero_pizzas; $a++)
          {
            if (isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']))//a variavel ativa, funciona como se fosse 'pendente', assim, depois que eu associar, eu removo-a para não deixala pendente
            {
              $arr_promo_carnaval[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']][] = array('preco' => $_SESSION['ipi_carrinho']['pedido'][$a]['preco_pizza'],'ind_ses' => $a);
            }
          }

          foreach ($arr_promo_carnaval as $ind_pro => $arr_val) 
          {
/*            echo "<pre>";
            print_r($arr_val);

            echo "</pre>";
            echo "<br/>".$ind_pro." - ".$arr_val[0]['preco']."</br>";
            echo "<br/>".$ind_pro." - ".$arr_val[1]['preco']."</br>";*/

            if($arr_val[1]['preco']>$arr_val[0]['preco'])
            {
              $arr_indice_menor[$ind_pro] =  $arr_val[0]['ind_ses'];
            }
            else
            {
              $arr_indice_menor[$ind_pro] =  $arr_val[1]['ind_ses'];
            }

            
          }
            
        }

        if ($numero_pizzas > 0)
        {
            for ($a = 0; $a < $numero_pizzas; $a++)
            {
                $desconto = false;
                $cod_motivo_promocoes = 0;
                if(is_array($arr_promo_carnaval[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']]) && count($arr_promo_carnaval[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']]) > 1)
                {

                  if($arr_indice_menor[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']] == $a)
                  {
                    $desconto = true;
                    $cod_motivo_promocoes = 20;
                  }
                }

                if (isset( $_SESSION['ipi_carrinho']['pedido'][$a]['promocao17_indice'] ) )
                {
                    if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao17_doce']))
                    {
                      //if($arr_indice_menor[$_SESSION['ipi_carrinho']['pedido'][$a]['promocao9_indice']] == $a)
                      {
                        $desconto = true;
                        $cod_motivo_promocoes = 29;
                      }
                    }
                }

                if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao12_indice']) && $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos']==4)
                {
                  $cod_motivo_promocoes = 23;
                }

                if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['promocao13_ativa']) && $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos']==4)
                {
                  $cod_motivo_promocoes = 25;
                }

                if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promo_cod']) && $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos']==4)
                {
                  if($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promo_cod']=="15")
                    $cod_motivo_promocoes = 27;
                }

                if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promo_cod']) && $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos']==4)
                {
                  if($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promo_cod']=="18")
                    $cod_motivo_promocoes = 30;
                }

                if($_SESSION['ipi_carrinho']['desconto_balcao'] == 'sim')
                {
                  $cod_motivo_promocoes = 21;
                }
                
                $sqlAux = "SELECT * FROM ipi_fracoes f INNER JOIN ipi_tamanhos_ipi_fracoes tf ON (tf.cod_fracoes=f.cod_fracoes) WHERE tf.cod_tamanhos='" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'] . "' AND f.fracoes='" . $_SESSION['ipi_carrinho']['pedido'][$a]['quant_fracao']. "' AND tf.cod_pizzarias = '".$codPizzarias."'";
                //echo $sqlAux;
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                $cod_tamanhos = $objAux->cod_tamanhos;
                
                $sqlAux2 = "SELECT * FROM ipi_tamanhos_ipi_tipo_massa WHERE cod_tamanhos=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'] . " AND cod_tipo_massa=" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tipo_massa'];
                $resAux2 = mysql_query($sqlAux2);
                $objAux2 = mysql_fetch_object($resAux2);
                
                $sqlAux3 = "SELECT * FROM ipi_combos_produtos WHERE cod_tamanhos='" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tamanhos'] . "' AND cod_combos = '" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_combos']."'";
                $resAux3 = mysql_query($sqlAux3);
                $objAux3 = mysql_fetch_object($resAux3);
                //echo "<br>3: ".$sqlAux3;

                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo']=="1")
                        {
                            if ( !is_array( $arr_id_combo_pedido_combo[ $_SESSION['ipi_carrinho']['pedido'][$a]['id_combo'] ] ) )
                            {
                                $sql_aux_combo = "SELECT * FROM ipi_combos c inner join ipi_combos_pizzarias cop on cop.cod_combos = c.cod_combos WHERE cop.cod_pizzarias = ".$codPizzarias." and c.cod_combos = ".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_combos'];
                    //echo "<br>sql_aux_combo: ".$sql_aux_combo;
                        $res_aux_combo = mysql_query($sql_aux_combo);
                        $obj_aux_combo = mysql_fetch_object($res_aux_combo);

                        // FIXME Não foi tratado para pagamentos de COMBOS com FIDELIDADE
                        $sql_combos_pedidos = "INSERT INTO ipi_pedidos_combos (cod_combos, cod_pedidos, pontos_fidelidade, preco, fidelidade, numero_combo) VALUES('".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_combos']."' , '".$cod_pedidos."', '', '".$obj_aux_combo->preco."', '0', '".$_SESSION['ipi_carrinho']['pedido'][$a]['id_combo']."')";
                    //echo "<br>sql_combos_pedidos: ".$sql_combos_pedidos;
                        $res_combos_pedidos = mysql_query($sql_combos_pedidos);
                        $cod_combos_pedidos = mysql_insert_id();

                                $arr_id_combo_pedido_combo[ $_SESSION['ipi_carrinho']['pedido'][$a]['id_combo'] ]['cod_pedidos_combos'] = $cod_combos_pedidos;

                            }
                        }
                
                $sqlInsPedPizzas = "INSERT INTO ipi_pedidos_pizzas (cod_pedidos_combos, cod_combos_produtos, cod_pedidos, cod_tamanhos, cod_tipo_massa, cod_opcoes_corte, quant_fracao, preco, preco_massa, promocional, fidelidade, combo,cod_motivo_promocoes) VALUES ('".$arr_id_combo_pedido_combo[$_SESSION['ipi_carrinho']['pedido'][$a]['id_combo']]['cod_pedidos_combos']."', '" . $objAux3->cod_combos_produtos . "', '" . $cod_pedidos . "', '" . $cod_tamanhos . "', '" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_tipo_massa'] . "', '" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_opcoes_corte'] . "','" . $_SESSION['ipi_carrinho']['pedido'][$a]['quant_fracao'] . "', '" . $objAux->preco . "', '" . $objAux2->preco . "','" . $_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] . "', '" . $_SESSION['ipi_carrinho']['pedido'][$a]['pizza_fidelidade'] . "', '" . $_SESSION['ipi_carrinho']['pedido'][$a]['pizza_combo'] . "','".$cod_motivo_promocoes."')";
                $resInsPedPizzas = mysql_query($sqlInsPedPizzas);
                $cod_pedidos_pizzas = mysql_insert_id();
                //echo "<Br>sqlInsPedPizzas: ".$sqlInsPedPizzas;
                
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] != "N")
                {
                    $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas='".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas']."' AND cod_tamanhos='".$cod_tamanhos."' AND cod_pizzarias='".$codPizzarias."'";
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    //echo "<Br>bordas: ".$sqlAux;

                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['borda_promocional'] == "1")
                    {
                        $preco_borda = 0;
                        
                        $sqlAux2 = "SELECT * FROM ipi_cupons WHERE cupom ='" . $_SESSION['ipi_carrinho']['cupom'] . "'";
                        $resAux2 = mysql_query($sqlAux2);
                        $objAux2 = mysql_fetch_object($resAux2);
                        
                        $sqlPedCupom = "INSERT INTO ipi_pedidos_ipi_cupons (cod_pedidos, cod_cupons) VALUES ('" . $cod_pedidos . "', '" . $objAux2->cod_cupons . "')";
                        $resPedCupom = mysql_query($sqlPedCupom);
                        
                        if ($objAux2->promocao == "0")
                        {
                            $sqlCupom = "UPDATE ipi_cupons SET valido=0 WHERE cod_cupons='" . $objAux2->cod_cupons . "'";
                            $resCupom = mysql_query($sqlCupom);
                        }
                    }
                    else
                        $preco_borda = $objAux->preco;
                    
                        $sqlAux4 = "SELECT * FROM ipi_combos_produtos WHERE tipo='BORDA' AND cod_combos = '" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_combos']."'";
                        $resAux4 = mysql_query($sqlAux4);
                        $objAux4 = mysql_fetch_object($resAux4);
                        //echo "<br>4: ".$sqlAux4;

                    $cod_motivo_promocoes_borda = 0;

                    if(isset($_SESSION['ipi_carrinho']['pedido'][$a]['borda_promo_cod']))
                    {
                      if($_SESSION['ipi_carrinho']['pedido'][$a]['borda_promo_cod']!="" && $_SESSION['ipi_carrinho']['pedido'][$a]['borda_promo_cod']>0)
                      {
                        if($_SESSION['ipi_carrinho']['pedido'][$a]['borda_promo_cod']==14)
                        {
                          $cod_motivo_promocoes_borda = 26;
                        }
                      }
                    }

                    $sqlBorda = "INSERT INTO ipi_pedidos_bordas (cod_pedidos, cod_pedidos_pizzas, cod_bordas, cod_pedidos_combos, cod_combos_produtos, preco, pontos_fidelidade, promocional, fidelidade, combo,cod_motivo_promocoes) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_bordas'] . "', '".$arr_id_combo_pedido_combo[$_SESSION['ipi_carrinho']['pedido'][$a]['id_combo']]['cod_pedidos_combos']."', '" . $objAux4->cod_combos_produtos . "' , '" . $preco_borda . "', '', '" . $_SESSION['ipi_carrinho']['pedido'][$a]['borda_promocional'] . "', '" . $_SESSION['ipi_carrinho']['pedido'][$a]['borda_fidelidade'] . "', '" . $_SESSION['ipi_carrinho']['pedido'][$a]['borda_combo'] . "','".$cod_motivo_promocoes_borda."')";
                    $resBorda = mysql_query($sqlBorda);
                    //echo "<br>sqlBorda: ".$sqlBorda;
                }
                
                if ($_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais'] != "N")
                {
                    $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_adicionais WHERE cod_adicionais='".$_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais']."' AND cod_tamanhos='" . $cod_tamanhos."' AND cod_pizzarias='".$codPizzarias."'";
                        //echo "<br>Adicionais: ".$sqlAux;
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    $sqlAdicional = "INSERT INTO ipi_pedidos_adicionais (cod_pedidos, cod_pedidos_pizzas, cod_adicionais, preco, pontos_fidelidade) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $_SESSION['ipi_carrinho']['pedido'][$a]['cod_adicionais'] . "', '" . $objAux->preco . "', '')";
                    $resAdicional = mysql_query($sqlAdicional);
                }
                
                $num_fracoes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao']);

                $preco_fracao_maior = 0;
                for ($b = 0; $b < $num_fracoes; $b++)
                {
                    $cod_pizzas = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    $num_fracao = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['num_fracao'];
                    $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos. " AND pt.cod_pizzarias = ".$cod_pizzarias;
                    //echo  "<br>sqlAux: ".$sqlAux;
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] == "1")
                    {
                        $preco_fracao = 0;
                    }
                    else
                    {
                        $preco_fracao = $objAux->preco;
                    }
                    
                    if($preco_fracao > $preco_fracao_maior)
                    {
                        $preco_fracao_maior = $preco_fracao;
                    }
                }

                for ($b = 0; $b < $num_fracoes; $b++)
                {
                    
                    $cod_pizzas = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    $num_fracao = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['num_fracao'];
                    $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas='" . $cod_pizzas . "' AND pt.cod_tamanhos='" . $cod_tamanhos."' AND pt.cod_pizzarias='".$codPizzarias."'";
                        //echo "<br>Pizzas Tamanhos: ".$sqlAux;

                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    
                    if ($_SESSION['ipi_carrinho']['pedido'][$a]['pizza_promocional'] == "1")
                    {
                        $preco_fracao = 0;
                        
                        $sqlAux2 = "SELECT * FROM ipi_cupons WHERE cupom ='" . $_SESSION['ipi_carrinho']['cupom'] . "'";
                        $resAux2 = mysql_query($sqlAux2);
                        $objAux2 = mysql_fetch_object($resAux2);
                        
                        $sqlPedCupom = "INSERT INTO ipi_pedidos_ipi_cupons (cod_pedidos, cod_cupons) VALUES ('" . $cod_pedidos . "', '" . $objAux2->cod_cupons . "')";
                        $resPedCupom = mysql_query($sqlPedCupom);
                        
                        if ($objAux2->promocao == "0")
                        {
                            $sqlCupom = "UPDATE ipi_cupons SET valido=0 WHERE cod_cupons='" . $objAux2->cod_cupons . "'";
                            $resCupom = mysql_query($sqlCupom);
                        }
                    }
                    else
                    {
                         if(REGRA_PRECO_DIVISAO_PIZZA=="IGUALMENTE")
                          {
                            $preco_fracao = ($objAux->preco / $num_fracoes);
                          }
                          else if(REGRA_PRECO_DIVISAO_PIZZA=="MAIOR")
                          {
                            $preco_fracao = ($preco_fracao_maior / $num_fracoes);
                          }
                        // $preco_fracao = ($objAux->preco / $num_fracoes);

                        if($desconto)
                        $preco_fracao = $preco_fracao*0.5;

                        if($_SESSION['ipi_carrinho']['desconto_balcao'] == 'sim')
                        $preco_fracao = $preco_fracao*0.7;
                    }
                    
                    $pizza_semana = 0;
                    $pizza_dia = 0;

                    if($objAux->pizza_semana==1)
                    {
                        $pizza_semana = 1;
                    }

                    if($objAux->pizza_dia==1)
                    {
                        $pizza_dia = 1;
                    }

                    $sqlPedFracoes = "INSERT INTO ipi_pedidos_fracoes (cod_pedidos, cod_pedidos_pizzas, cod_pizzas, fracao, preco, pontos_fidelidade_pizza,pizza_semana,pizza_dia) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pizzas . "', '" . $num_fracao . "', '" . $preco_fracao . "', '','".$pizza_semana."','".$pizza_dia."')";
                    $resPedFracoes = mysql_query($sqlPedFracoes);
                    $cod_pedidos_fracoes = mysql_insert_id();
                    
                    $sqlAux = "SELECT * FROM ipi_pizzas WHERE cod_pizzas=" . $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    
                    $num_ingredientes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes']);
                    for ($c = 0; $c < $num_ingredientes; $c++)
                    {
                        $cod_ingredientes = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                        $cod_ingredientes_troca = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes_troca'];
                        $ingrediente_padrao = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'];
                        $cod_ingrediente_trocado = ($ingrediente_padrao ? '0' : $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes_troca']);
                        $ingrediente_promocional = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_promocional'];

                        /*if ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_troca']==true)
                        {
                            $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_ingredientes='" . $cod_ingredientes_troca . "' AND it.cod_tamanhos='" . $cod_tamanhos."' AND it.cod_pizzarias='".$codPizzarias."'";
                            //echo "<br>Ingre Tam: ".$sqlAux;
                            $resAux = mysql_query($sqlAux);
                            $objAux = mysql_fetch_object($resAux);
                            $preco_ingrediente = arredondar_preco_ingrediente($objAux->preco_troca, $num_fracoes);
                                                
                            $sqlPedIngredientes = "INSERT INTO ipi_pedidos_ingredientes (cod_pedidos, cod_pedidos_pizzas, cod_pedidos_fracoes, cod_ingredientes, preco, pontos_fidelidade, ingrediente_padrao) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pedidos_fracoes . "', '" . $cod_ingredientes_troca . "', '" . $preco_ingrediente . "', '', '" . $ingrediente_padrao . "')";
                            $resPedIngredientes = mysql_query($sqlPedIngredientes);
                        }
                        else 
                        {
                            $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_ingredientes='" . $cod_ingredientes . "' AND it.cod_tamanhos='" . $cod_tamanhos."' AND it.cod_pizzarias='".$codPizzarias."'";
                            //echo "<br>Ingre Tam: ".$sqlAux;
                            $resAux = mysql_query($sqlAux);
                            $objAux = mysql_fetch_object($resAux);
                            $preco_ingrediente = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                                                
                            $sqlPedIngredientes = "INSERT INTO ipi_pedidos_ingredientes (cod_pedidos, cod_pedidos_pizzas, cod_pedidos_fracoes, cod_ingredientes, preco, pontos_fidelidade, ingrediente_padrao) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pedidos_fracoes . "', '" . $cod_ingredientes . "', '" . $preco_ingrediente . "', '', '" . $ingrediente_padrao . "')";
                            $resPedIngredientes = mysql_query($sqlPedIngredientes);
                        }*/


                        if ($_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_troca']==true)
                        {
                            $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_ingredientes='" . $cod_ingredientes_troca . "' AND it.cod_tamanhos='" . $cod_tamanhos."' AND it.cod_pizzarias='".$codPizzarias."'";
                            //echo "<br>Ingre Tam: ".$sqlAux;
                            $resAux = mysql_query($sqlAux);
                            $objAux = mysql_fetch_object($resAux);
                            $preco_ingrediente = arredondar_preco_ingrediente($objAux->preco_troca, $num_fracoes);
                        }
                        else 
                        {
                            $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_ingredientes='" . $cod_ingredientes . "' AND it.cod_tamanhos='" . $cod_tamanhos."' AND it.cod_pizzarias='".$codPizzarias."'";
                            //echo "<br>Ingre Tam: ".$sqlAux;
                            $resAux = mysql_query($sqlAux);
                            $objAux = mysql_fetch_object($resAux);
                            $preco_ingrediente = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                        }
                        
                        if($ingrediente_promocional==1)
                          $preco_ingrediente = 0;            

                        $sqlPedIngredientes = "INSERT INTO ipi_pedidos_ingredientes (cod_pedidos, cod_pedidos_pizzas, cod_pedidos_fracoes, cod_ingredientes, cod_ingrediente_trocado, preco, pontos_fidelidade, ingrediente_padrao,promocional) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pedidos_fracoes . "', '" . $cod_ingredientes . "', '" . $cod_ingrediente_trocado . "', '" . $preco_ingrediente . "', '', '" . $ingrediente_padrao . "','".$ingrediente_promocional."')";
                        $resPedIngredientes = mysql_query($sqlPedIngredientes);
                        //echo "<br>Ins Ingred: ".$sqlPedIngredientes;
                    }
                }
            
            }
        }
        
        $numero_bebidas = isset($_SESSION['ipi_carrinho']['bebida']) ? count($_SESSION['ipi_carrinho']['bebida']) : 0;
        if ($numero_bebidas > 0)
        {
            if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
            {
              $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
            }
            else
            {
              $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
              $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
              $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
              $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
              $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
              $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
            }
            for ($a = 0; $a < $numero_bebidas; $a++)
            {
                $cod_bebidas_ipi_conteudos = $_SESSION['ipi_carrinho']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                $sqlAux = "SELECT * FROM ipi_bebidas b INNER JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) inner join ipi_conteudos_pizzarias cp on cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos WHERE bc.cod_bebidas_ipi_conteudos='" . $cod_bebidas_ipi_conteudos."' AND cp.cod_pizzarias='".$cod_pizzarias."'";
                //echo "<br> Bebidas Conteudos: ".$sqlAux;

                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                if ($_SESSION['ipi_carrinho']['bebida'][$a]['bebida_promocional'] == "1")
                {
                    $preco_bebida = 0;
                    
                    $sqlAux2 = "SELECT * FROM ipi_cupons WHERE cupom ='" . $_SESSION['ipi_carrinho']['cupom'] . "'";
                    $resAux2 = mysql_query($sqlAux2);
                    $objAux2 = mysql_fetch_object($resAux2);
                    $numAux2 = mysql_num_rows($resAux2);
                    
                    if ($numAux2 > 0)
                    {
                        $sqlPedCupom = "INSERT INTO ipi_pedidos_ipi_cupons (cod_pedidos, cod_cupons) VALUES ('" . $cod_pedidos . "', '" . $objAux2->cod_cupons . "')";
                        $resPedCupom = mysql_query($sqlPedCupom);
                        
                        if ($objAux2->promocao == "0")
                        {
                            $sqlCupom = "UPDATE ipi_cupons SET valido=0 WHERE cod_cupons='" . $objAux2->cod_cupons . "'";
                            $resCupom = mysql_query($sqlCupom);
                        }
                    }
                
                }
                elseif($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] == "5") //codigos dos motivos em que a bebida é de graça (que no caso foi trocada por coca pagando um real)
                {
                    $preco_bebida = $this->preco_troca_promocao;
                
                }
                elseif($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] == "9")
                {
                  $preco_bebida = 0;
                }
                elseif($_SESSION['ipi_carrinho']['bebida'][$a]['cod_motivo_pro'] == "19")
                {
                  $preco_bebida = 0;
                }
                else
                    $preco_bebida = $objAux->preco;

                    
                        $sqlAux6 = "SELECT * FROM ipi_bebidas_ipi_conteudos WHERE cod_bebidas_ipi_conteudos = ".$_SESSION['ipi_carrinho']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                $resAux6 = mysql_query($sqlAux6);
                $objAux6 = mysql_fetch_object($resAux6);
                //echo "<br>Bebida Conteudo: ".$sqlAux6;

                $sqlAux5 = "SELECT * FROM ipi_combos_produtos cp WHERE cp.tipo='BEBIDA' AND cp.cod_conteudos='".$objAux6->cod_conteudos."' AND cp.cod_combos = '" . $_SESSION['ipi_carrinho']['bebida'][$a]['cod_combos']."'";
                $resAux5 = mysql_query($sqlAux5);
                $objAux5 = mysql_fetch_object($resAux5);
                //echo "<br>4: ".$sqlAux5;

                $quantidade_bebida = $_SESSION['ipi_carrinho']['bebida'][$a]['quantidade'];
                $sqlPedBebidas = "INSERT INTO ipi_pedidos_bebidas (cod_pedidos, cod_bebidas_ipi_conteudos, cod_combos_produtos, cod_pedidos_combos, preco, pontos_fidelidade, quantidade, promocional, fidelidade, combo) VALUES ('" . $cod_pedidos . "', '" . $cod_bebidas_ipi_conteudos . "', '" . $objAux5->cod_combos_produtos . "', '".$arr_id_combo_pedido_combo[$_SESSION['ipi_carrinho']['bebida'][$a]['id_combo']]['cod_pedidos_combos']."' , '" . $preco_bebida . "', '', '" . $quantidade_bebida . "', '" . $_SESSION['ipi_carrinho']['bebida'][$a]['bebida_promocional'] . "', '" . $_SESSION['ipi_carrinho']['bebida'][$a]['bebida_fidelidade'] . "', '" . $_SESSION['ipi_carrinho']['bebida'][$a]['bebida_combo'] . "')";
                $resPedBebidas = mysql_query($sqlPedBebidas);
                //echo "<br>sqlPedBebidas: ".$sqlPedBebidas;


            }
        }
        

        if ($cod_pedidos) // Verifica se não deu nenhum problema com geração do pedido antes de debitar a fidelidade
        {
            $fidelidade_descontar = isset($_SESSION['ipi_carrinho']['fidelidade_pontos_gastos']) ? $_SESSION['ipi_carrinho']['fidelidade_pontos_gastos'] : 0;
            $fidelidade_descontar = $fidelidade_descontar * (-1);
            
            if ($fidelidade_descontar != 0)
            {
                $sqlPedCupom = "INSERT INTO ipi_fidelidade_clientes (cod_pedidos, cod_clientes, data_hora_fidelidade, pontos) VALUES ('" . $cod_pedidos . "', '" . $_SESSION['ipi_cliente']['codigo'] . "', NOW(), " . $fidelidade_descontar . ")";
                $resPedCupom = mysql_query($sqlPedCupom);
            }
            
            $_SESSION[ipi_cliente][pontos_fidelidade] = $_SESSION[ipi_cliente][pontos_fidelidade] + $fidelidade_descontar;
        }
        desconectabd($conexao);
        
        return $cod_pedidos;
    }
    
    /**
     * Função que retorna se existe algum sabor de pizza doce no carrinho
     */
    public function existe_doce ()
    {
        
        $numero_pizzas = isset($_SESSION['ipi_carrinho']['pedido']) ? count($_SESSION['ipi_carrinho']['pedido']) : 0;
        if ($numero_pizzas > 0)
        {
            $existe_pizza_doce = false;
            for ($a = 0; $a < $numero_pizzas; $a++)
            {
                $num_fracoes = count($_SESSION['ipi_carrinho']['pedido'][$a]['fracao']);
                for ($b = 0; $b < $num_fracoes; $b++)
                {
                    $cod_pizzas = $_SESSION['ipi_carrinho']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    $sqlAux = "SELECT * FROM ipi_pizzas WHERE cod_pizzas=" . $cod_pizzas;
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    if ($objAux->tipo == "Doce")
                        $existe_pizza_doce = true;
                }
            }
        }
        
        return ($existe_pizza_doce);
    }
    
    /**
     * Função que exibe o todos os dados da Pizzaria. entrada é o código do pedido
     */
    public function dados_pizzaria ($codigo_pedido)
    {
        $conexao = conectabd();
        
        $sqlAux = "SELECT pi.* FROM ipi_pizzarias pi INNER JOIN ipi_pedidos pe ON (pe.cod_pizzarias=pi.cod_pizzarias) WHERE pe.cod_pedidos=" . $codigo_pedido;
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        
        $dados_pizzaria = "<strong>" . $objAux->nome . "</strong>";
        $dados_pizzaria .= "<br>" . $objAux->endereco . ", " . $objAux->numero;
        if ($objAux->complemento)
            $dados_pizzaria .= " - " . $objAux->complemento;
        $dados_pizzaria .= "<br>" . $objAux->bairro . " - " . $objAux->cidade . " - " . $objAux->estado;
        $dados_pizzaria .= "<br>Tel.: " . $objAux->telefone_1;
        if ($objAux->telefone_2)
            $dados_pizzaria .= " - " . $objAux->telefone_2;
        if ($objAux->telefone_3)
            $dados_pizzaria .= " - " . $objAux->telefone_3;
        if ($objAux->telefone_4)
            $dados_pizzaria .= " - " . $objAux->telefone_4;
        
        desconectabd($conexao);
        return $dados_pizzaria;
    }
    
    /**
     * Função que exibe o tempo de entrega. entrada é o código do pedido
     */
    public function tempo_entrega_pedido ($codigo_pedido)
    {
        $conexao = conectabd();
        
        $sqlAux = "SELECT * FROM ipi_pedidos WHERE cod_pedidos=" . $codigo_pedido;
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        //echo "<br>1: ".$sqlAux;

        if ($objAux->agendado)
        {
            $dados_pizzaria = "<b>Horáro de ".($objAux->tipo_entrega=="Entrega" ? "entrega" : "retirada" )."<br/>aproximado às " . $objAux->horario_agendamento . "</b> ";
        }
        else
        {
            $arr_aux = explode(' ', $objAux->data_hora_pedido);
            $arr_aux = explode(':', $arr_aux[1]);
            $sqlAuxHorario = "SELECT * FROM ipi_pizzarias_horarios WHERE cod_pizzarias = " . $objAux->cod_pizzarias . " AND horario_inicial_entrega <= TIME('" . $objAux->data_hora_pedido . "') AND horario_final_entrega >= TIME('" . $objAux->data_hora_pedido . "') AND dia_semana = '" . date('w', strtotime($objAux->data_hora_pedido)) . "'";
            $resAuxHorario = mysql_query($sqlAuxHorario);
            $objAuxHorario = mysql_fetch_object($resAuxHorario);
            
            //echo "<br>1: ".$sqlAuxHorario;
            $dados_pizzaria = "<b>Tempo de ".($objAux->tipo_entrega=="Entrega" ? "entrega" : "retirada" )." <br/>aproximado:</b> " . $objAuxHorario->tempo_entrega . ' minutos';
        }
        
        desconectabd($conexao);
        return $dados_pizzaria;
    }
    
/**
     * Função que exibe o tempo de entrega. entrada é o cep
     */
    public function tempo_entrega($cep)
    {
        $conexao = conectabd();

        $sqlPizzaria = "SELECT * FROM ipi_pizzarias p INNER JOIN ipi_cep c ON (p.cod_pizzarias=c.cod_pizzarias) WHERE c.cep_inicial<=" . str_replace(".", "", str_replace("-", "", $cep)) . " AND c.cep_final>=" . str_replace(".", "", str_replace("-", "", $cep)) . " GROUP BY p.cod_pizzarias";
        echo "<br><br>".$sqlPizzaria;
        $resPizzaria = mysql_query($sqlPizzaria);
        $objPizzaria = mysql_fetch_object($resPizzaria);
        $codPizzarias = $objPizzaria->cod_pizzarias;
        
        $sqlAuxHorario = "SELECT * FROM ipi_pizzarias_horarios WHERE cod_pizzarias = " . $codPizzarias . " AND horario_inicial_entrega <= TIME('" . date("Y-m-d H:i:s") . "') AND horario_final_entrega >= TIME('" . date("Y-m-d H:i:s") . "') dia_semana = '" . date('w') . "'";
        echo "<br><br>".$sqlAuxHorario;
        $resAuxHorario = mysql_query($sqlAuxHorario);
        $objAuxHorario = mysql_fetch_object($resAuxHorario);
        
        $dados_pizzaria = "<b>Tempo de entrega aproximado:</b> " . $objAuxHorario->tempo_entrega . ' minutos';
        
        desconectabd($conexao);
        return $dados_pizzaria;
    }
    
    /**
     * Função que exibe o tempo de entrega. entrada é o código da pizzaria
     */
    public function tempo_entrega_pizzaria ($codigo_pizzaria)
    {
        $conexao = conectabd();
        
        $sqlAux = "SELECT pi.* FROM ipi_pizzarias ip WHERE ip.cod_pizzarias=" . $codigo_pizzaria;
        $resAux = mysql_query($sqlAux);
        $objAux = mysql_fetch_object($resAux);
        
        $dados_pizzaria = "<b>Tempo de entrega aproximado:</b> " . $objAux->tempo_entrega;
        
        desconectabd($conexao);
        return $dados_pizzaria;
    }
    /**
     * Função que remove um combo e todas as bebidas e pizzas dele que ja foram pedidas, entrada é o id_combo, o indice
     */    
    public function excluir_combo ($id_combo)
    {
        $cont_bebidas = count($_SESSION['ipi_carrinho']['bebida']);
        $cont_pizzas = count($_SESSION['ipi_carrinho']['pedido']);
        for($b = 0;$b<$cont_bebidas;$b++)
        {
            if($_SESSION['ipi_carrinho']['bebida'][$b]['id_combo']==$id_combo)
            {
                unset($_SESSION['ipi_carrinho']['bebida'][$b]);
            }
        }

        for($p = 0;$p<$cont_pizzas;$p++)
        {
            if($_SESSION['ipi_carrinho']['pedido'][$p]['id_combo']==$id_combo)
            {
                unset($_SESSION['ipi_carrinho']['pedido'][$p]);
            }
        }

        if (count($_SESSION['ipi_carrinho']['pedido'])>0)
        {
            $arr_novos_indices = range (0, (count($_SESSION['ipi_carrinho']['pedido']) - 1));
            $_SESSION['ipi_carrinho']['pedido'] = array_combine ($arr_novos_indices, $_SESSION['ipi_carrinho']['pedido']);
        }
        
        if (count($_SESSION['ipi_carrinho']['bebida']))
        {
            $carrinho_chaves = range(0, (count($_SESSION['ipi_carrinho']['bebida']) - 1));
            $_SESSION['ipi_carrinho']['bebida'] = array_combine($carrinho_chaves, $_SESSION['ipi_carrinho']['bebida']);
        }
        unset($_SESSION['ipi_carrinho']['combo']);
    }

    /**
     * Função que exibe o pedido completo.
     */
    public function email_pedido ($codigo_pedido)
    {
        $con = conectabd();
        
        $objBuscaDetalhamento = executaBuscaSimples("SELECT * FROM ipi_pedidos WHERE cod_pedidos = $codigo_pedido", $con);
        
        $SqlBuscaPedidosPizzas = 'SELECT * FROM ipi_pedidos_pizzas p INNER JOIN ipi_tamanhos t ON (p.cod_tamanhos = t.cod_tamanhos) INNER JOIN ipi_tipo_massa m ON (p.cod_tipo_massa = m.cod_tipo_massa) WHERE p.cod_pedidos = ' . $objBuscaDetalhamento->cod_pedidos . ' ORDER BY cod_pedidos_pizzas';
        $resBuscaPedidosPizzas = mysql_query($SqlBuscaPedidosPizzas);
        //echo $SqlBuscaPedidosPizzas;
        
        $num_pizza = 1;
        while ($objBuscaPedidosPizzas = mysql_fetch_object($resBuscaPedidosPizzas))
        {
            $email_pedido .= '<h4><font face="arial narrow, arial" color="#EB891A" size="3">' . $num_pizza . '&ordf; Pizza</font>';

            if ($objBuscaPedidosPizzas->promocional)
                $email_pedido .= ' - GRÁTIS';
            else if ($objBuscaPedidosPizzas->fidelidade)
                $email_pedido .= ' - FIDELIDADE';
            else if ($objBuscaPedidosPizzas->combo)
                $email_pedido .= ' - COMBO';

            $email_pedido .= '</h4>' . "\r\n";
            
            $email_pedido .= '<hr noshade size="1" color="#EB891A">' . "\r\n";
            
            if ($objBuscaPedidosPizzas->preco > 0)
                $valorQuantFracao = '(R$' . bd2moeda($objBuscaPedidosPizzas->preco) . ')';
            else
                $valorQuantFracao = '';
            
            $email_pedido .= '<table>' . "\r\n";
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td><b>Tamanho da Pizza:</b></td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td><b>Quantidade de Sabores:</b></td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td><b>Recheio da Borda:</b></td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td>' . $objBuscaPedidosPizzas->tamanho . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>' . $objBuscaPedidosPizzas->quant_fracao . ' ' . $valorQuantFracao . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            
            $objBuscaPedidosBorda = executaBuscaSimples("SELECT * FROM ipi_pedidos_bordas p INNER JOIN ipi_bordas b ON (p.cod_bordas = b.cod_bordas) WHERE p.cod_pedidos = " . $objBuscaPedidosPizzas->cod_pedidos . " AND p.cod_pedidos_pizzas = " . $objBuscaPedidosPizzas->cod_pedidos_pizzas, $con);
            
            if ($objBuscaPedidosBorda->borda)
            {
                if ($objBuscaPedidosBorda->promocional)
                    $valorPedidosBorda = 'GRÁTIS';
                else if ($objBuscaPedidosBorda->fidelidade)
                    $valorPedidosBorda = 'FIDELIDADE';
                else if ($objBuscaPedidosBorda->combo)
                    $valorPedidosBorda = 'COMBO';
                else
                    $valorPedidosBorda = 'R$' . bd2moeda($objBuscaPedidosBorda->preco);
                
                $email_pedido .= '<td>' . $objBuscaPedidosBorda->borda . ' (' . $valorPedidosBorda . ')</td>' . "\r\n";
            }
            else
            {
                $email_pedido .= '<td>Não</td>' . "\r\n";
            }
            
            $email_pedido .= '</tr>' . "\r\n";
            
            $email_pedido .= '<tr><td colspan="5">&nbsp;</td></tr>' . "\r\n";
            
            $email_pedido .= '<tr>';
            // $email_pedido .= '<td><b>Borda salpicada com Gergelim:</b></td>' . "\r\n";
            // $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td colspan="3"><b>Tipo da Massa:</b></td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            $email_pedido .= '<tr>' . "\r\n";
            
            $objBuscaPedidosAdicional = executaBuscaSimples("SELECT * FROM ipi_pedidos_adicionais p INNER JOIN ipi_adicionais a ON (p.cod_adicionais = a.cod_adicionais) WHERE p.cod_pedidos = " . $objBuscaPedidosPizzas->cod_pedidos . " AND p.cod_pedidos_pizzas = " . $objBuscaPedidosPizzas->cod_pedidos_pizzas, $con);
            
            if ($objBuscaPedidosAdicional->adicional)
            {
                if ($objBuscaPedidosAdicional->promocional)
                    $valorPedidosAdicional = 'GRÁTIS';
                else if ($objBuscaPedidosAdicional->fidelidade)
                    $valorPedidosAdicional = 'FIDELIDADE';
                else if ($objBuscaPedidosAdicional->combo)
                    $valorPedidosAdicional = 'COMBO';
                else
                    $valorPedidosAdicional = 'R$' . bd2moeda($objBuscaPedidosAdicional->preco);
                
                // $email_pedido .= '<td>' . $objBuscaPedidosAdicional->adicional . ' (' . $valorPedidosAdicional . ')</td>' . "\r\n";
            }
            else
            {
                // $email_pedido .= '<td>Não</td>' . "\r\n";
            }
            
            // $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            
            $email_pedido .= '<td colspan="3">'.$objBuscaPedidosPizzas->tipo_massa;
            
            if($objBuscaPedidosPizzas->preco_massa > 0)
            {
                $email_pedido .= '&nbsp;(' . bd2moeda($objBuscaPedidosPizzas->preco_massa) . ')';   
            }
            
            echo '</td>' . "\r\n";
            
            $email_pedido .= '</tr>' . "\r\n";
            $email_pedido .= '</table>' . "\r\n";
            
            
            $SqlBuscaPedidosFracoes = "SELECT * FROM ipi_pedidos_fracoes fr INNER JOIN ipi_pizzas p ON (fr.cod_pizzas = p.cod_pizzas) WHERE fr.cod_pedidos = " . $objBuscaPedidosPizzas->cod_pedidos . " AND fr.cod_pedidos_pizzas = " . $objBuscaPedidosPizzas->cod_pedidos_pizzas . " ORDER BY fracao";
            $resBuscaPedidosFracoes = mysql_query($SqlBuscaPedidosFracoes);
            
            while ($objBuscaPedidosFracoes = mysql_fetch_object($resBuscaPedidosFracoes))
            {
                if ($objBuscaPedidosPizzas->promocional)
                    $valorPedidosFracoes = '(GRÁTIS)';
                else if ($objBuscaPedidosPizzas->fidelidade)
                    $valorPedidosFracoes = '(FIDELIDADE)';
                else if ($objBuscaPedidosPizzas->combo)
                    $valorPedidosFracoes = '(COMBO)';
                else
                    $valorPedidosFracoes = '(R$' . bd2moeda($objBuscaPedidosFracoes->preco) . ')';
                
                $email_pedido .= '<br><br><b>' . $objBuscaPedidosFracoes->fracao . '&ordm; Sabor:</b> <b>' . $objBuscaPedidosFracoes->pizza . ' ' . $valorPedidosFracoes . '</b>' . "\r\n";
                
                $email_pedido .= '<br><br><b>Ingredientes Retirados:</b>' . "\r\n";
                
                // Ingredientes da pizza
                //$SqlBuscaPedidosIngredientes = "SELECT * FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_ingredientes ig ON (pi.cod_ingredientes = ig.cod_ingredientes) WHERE pi.ingrediente_padrao = 1 AND pi.cod_pedidos_pizzas = ".$objBuscaPedidosFracoes->cod_pedidos_pizzas." AND pi.cod_pedidos = ".$objBuscaPedidosFracoes->cod_pedidos." AND pi.cod_pedidos_fracoes = ".$objBuscaPedidosFracoes->cod_pedidos_fracoes.' ORDER BY ingrediente';

                // Ingredientes retirados
                $SqlBuscaPedidosIngredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = " . $objBuscaDetalhamento->cod_pedidos . " AND pi.cod_pedidos_pizzas = " . $objBuscaPedidosPizzas->cod_pedidos_pizzas . " AND pi.cod_pedidos_fracoes = " . $objBuscaPedidosFracoes->cod_pedidos_fracoes . " AND pi.ingrediente_padrao = 1) AND i.consumo = 0 AND p.cod_pizzas = " . $objBuscaPedidosFracoes->cod_pizzas . ' ORDER BY ingrediente';
                
                $resBuscaPedidosIngredientes = mysql_query($SqlBuscaPedidosIngredientes);
                
                $email_pedido .= '<ol style="margin-bottom: 10px; margin-top: 10px;">' . "\r\n";
                
                while ($objBuscaPedidosIngredientes = mysql_fetch_object($resBuscaPedidosIngredientes))
                {
                    $email_pedido .= '<li>SEM ' . $objBuscaPedidosIngredientes->ingrediente . '</li>' . "\r\n";
                }
                
                $email_pedido .= '</ol>' . "\r\n";
                
                $email_pedido .= '<b>Ingredientes Adicionados:</b>' . "\r\n";
                
                $SqlBuscaPedidosExtra = "SELECT * FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_ingredientes ig ON (pi.cod_ingredientes = ig.cod_ingredientes) WHERE pi.ingrediente_padrao = 0 AND pi.cod_pedidos_pizzas = " . $objBuscaPedidosFracoes->cod_pedidos_pizzas . " AND pi.cod_pedidos = " . $objBuscaPedidosFracoes->cod_pedidos . " AND pi.cod_pedidos_fracoes = " . $objBuscaPedidosFracoes->cod_pedidos_fracoes . ' ORDER BY ingrediente';
                $resBuscaPedidosExtra = mysql_query($SqlBuscaPedidosExtra);
                
                $email_pedido .= '<ol style="margin-bottom: 10px; margin-top: 10px;">' . "\r\n";
                
                while ($objBuscaPedidosExtra = mysql_fetch_object($resBuscaPedidosExtra))
                {
                    $email_pedido .= '<li>' . $objBuscaPedidosExtra->ingrediente . ' (R$' . bd2moeda($objBuscaPedidosExtra->preco) . ')</li>' . "\r\n";
                }
                
                $email_pedido .= '</ol>' . "\r\n";
            }
            
            $num_pizza++;
        }
        
        $SqlBuscaPedidosBebidas = "SELECT *, p.preco AS pedidos_preco FROM ipi_pedidos_bebidas p INNER JOIN ipi_bebidas_ipi_conteudos bc ON (p.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) WHERE cod_pedidos = " . $objBuscaDetalhamento->cod_pedidos;
        $resBuscaPedidosBebidas = mysql_query($SqlBuscaPedidosBebidas);
        
        $email_pedido .= '<h4><font face="arial narrow, arial" color="#eb891a" size="3">Bebida</font></h4>' . "\r\n";
        $email_pedido .= '<hr noshade size="1" color="#EB891A">' . "\r\n";
        
        $email_pedido .= '<table>' . "\r\n";
        $email_pedido .= '<tr>' . "\r\n";
        $email_pedido .= '<td><b>Bebida:</b></td>' . "\r\n";
        $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
        $email_pedido .= '<td><b>Conteúdo:</b></td>' . "\r\n";
        $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
        $email_pedido .= '<td><b>Quantidade:</b></td>' . "\r\n";
        $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
        $email_pedido .= '<td><b>Valor Unit.:</b></td>' . "\r\n";
        $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
        $email_pedido .= '<td><b>Valor Total:</b></td>' . "\r\n";
        $email_pedido .= '</tr>' . "\r\n";
        
        while ($objBuscaPedidosBebidas = mysql_fetch_object($resBuscaPedidosBebidas))
        {
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td>' . $objBuscaPedidosBebidas->bebida . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>' . $objBuscaPedidosBebidas->conteudo . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>' . $objBuscaPedidosBebidas->quantidade . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            
            $valorTotalPedidosBebidas = 0;
            
            if ($objBuscaPedidosBebidas->promocional)
            {
                $valorPedidosBebidas = 'GRÁTIS';
                $valorTotalPedidosBebidas += 0;
            }
            else if ($objBuscaPedidosBebidas->fidelidade)
            {
                $valorPedidosBebidas = 'FIDELIDADE';
                $valorTotalPedidosBebidas += 0;
            }
            else if ($objBuscaPedidosBebidas->combo)
            {
                $valorPedidosBebidas = 'COMBO';
                $valorTotalPedidosBebidas += 0;
            }
            else
            {
                $valorPedidosBebidas = 'R$' . bd2moeda($objBuscaPedidosBebidas->pedidos_preco);
                $valorTotalPedidosBebidas += $objBuscaPedidosBebidas->pedidos_preco * $objBuscaPedidosBebidas->quantidade;
            }
            
            $email_pedido .= '<td>' . $valorPedidosBebidas . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>'.($valorTotalPedidosBebidas ? 'R$' . bd2moeda($valorTotalPedidosBebidas) : ''). '</td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            
            $email_pedido .= '<tr><td colspan="7"></td></tr>' . "\r\n";
        }
        
        $email_pedido .= '</table>' . "\r\n";
        
        $email_pedido .= '<br><br>' . "\r\n";
        
        $email_pedido .= '<h4><font face="arial narrow, arial" color="#eb891a" size="3">Pagamento</font></h4>' . "\r\n";
        $email_pedido .= '<hr noshade size="1" color="#EB891A">' . "\r\n";
        
        $email_pedido .= '<table>' . "\r\n";
        
        $email_pedido .= '<tr>' . "\r\n";
        $email_pedido .= '<td><b>Forma:</b></td>' . "\r\n";
        $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
        $email_pedido .= '<td><b>Valor:</b></td>' . "\r\n";
        $email_pedido .= '</tr>' . "\r\n";
        $email_pedido .= '<tr>' . "\r\n";
        $email_pedido .= '<td>' . bd2texto($objBuscaDetalhamento->forma_pg) . '</td>' . "\r\n";
        $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
        $email_pedido .= '<td>R$' . bd2moeda($objBuscaDetalhamento->valor_total) . '</td>' . "\r\n";
        $email_pedido .= '</tr>' . "\r\n";
        
        $email_pedido .= '</table>' . "\r\n";
        
        $email_pedido .= '<br><br>' . "\r\n";
        
        if ($objBuscaDetalhamento->tipo_entrega == 'Balcão')
        {
            $sql_buscar_endereco_pizzaria = "SELECT pi.* from ipi_pizzarias pi inner join ipi_pedidos p on p.cod_pizzarias = pi.cod_pizzarias where p.cod_pedidos = '".$objBuscaDetalhamento->cod_pedidos."'";
            $res_buscar_endereco_pizzaria = mysql_query($sql_buscar_endereco_pizzaria);
            $obj_buscar_endereco_pizzaria = mysql_fetch_object($res_buscar_endereco_pizzaria);

            $email_pedido .= '<h4><font face="arial narrow, arial" color="#eb891a" size="3">Retirar na '.ucfirst(TIPO_EMPRESA).'</font></h4>' . "\r\n";
            $email_pedido .= '<hr noshade size="1" color="#EB891A">' . "\r\n";
            
            $email_pedido .= '<table>' . "\r\n";
            
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td><b>Endereço:</b></td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td><b>Número:</b></td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td></td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td>' . bd2texto($obj_buscar_endereco_pizzaria->endereco) . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>' . bd2texto($obj_buscar_endereco_pizzaria->numero) . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td></td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            
            $email_pedido .= '<tr><td colspan="5">&nbsp;</td></tr>' . "\r\n";
            
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td><b>Bairro:</b></td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td><b>Cidade:</b></td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td><b>Estado:</b></td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td>' . bd2texto($obj_buscar_endereco_pizzaria->bairro) . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>' . bd2texto($obj_buscar_endereco_pizzaria->cidade) . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>' . $obj_buscar_endereco_pizzaria->estado . '</td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            
            $email_pedido .= '<tr><td colspan="5">&nbsp;</td></tr>' . "\r\n";
            
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td colspan="5"><b>CEP:</b></td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td colspan="5">' . $obj_buscar_endereco_pizzaria->cep . '</td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            
            $email_pedido .= '</table>' . "\r\n";
        }
        else
        {
            $email_pedido .= '<h4><font face="arial narrow, arial" color="#eb891a" size="3">Entrega</font></h4>' . "\r\n";
            $email_pedido .= '<hr noshade size="1" color="#EB891A">' . "\r\n";
            
            $email_pedido .= '<table>' . "\r\n";
            
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td><b>Endereço:</b></td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td><b>Número:</b></td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td><b>Complemento:</b></td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td>' . bd2texto($objBuscaDetalhamento->endereco) . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>' . bd2texto($objBuscaDetalhamento->numero) . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>' . bd2texto($objBuscaDetalhamento->complemento) . '</td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            
            $email_pedido .= '<tr><td colspan="5">&nbsp;</td></tr>' . "\r\n";
            
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td><b>Bairro:</b></td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td><b>Cidade:</b></td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td><b>Estado:</b></td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td>' . bd2texto($objBuscaDetalhamento->bairro) . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>' . bd2texto($objBuscaDetalhamento->cidade) . '</td>' . "\r\n";
            $email_pedido .= '<td width="50">&nbsp;</td>' . "\r\n";
            $email_pedido .= '<td>' . $objBuscaDetalhamento->estado . '</td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            
            $email_pedido .= '<tr><td colspan="5">&nbsp;</td></tr>' . "\r\n";
            
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td colspan="5"><b>CEP:</b></td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            $email_pedido .= '<tr>' . "\r\n";
            $email_pedido .= '<td colspan="5">' . $objBuscaDetalhamento->cep . '</td>' . "\r\n";
            $email_pedido .= '</tr>' . "\r\n";
            
            $email_pedido .= '</table>' . "\r\n";
        }
        
        // Buscando os pontos acumulados...
        //$objQuantidadePontos = executaBuscaSimples("SELECT SUM(pontos) AS soma_pontos FROM ipi_fidelidade_clientes WHERE cod_clientes = " . $objBuscaDetalhamento->cod_clientes . " AND (data_validade > NOW() OR data_validade = '0000-00-00' OR data_validade IS NULL) ORDER BY data_hora_fidelidade DESC", $con);
        $objQuantidadePontos = executaBuscaSimples("SELECT SUM(pontos) AS soma_pontos FROM ipi_fidelidade_clientes WHERE cod_clientes = " . $objBuscaDetalhamento->cod_clientes . " ORDER BY data_hora_fidelidade DESC", $con);
        
        desconectabd($con);
        
        //$email_pedido .= "Cupom da Promoção Férias tem que ter Pizza: IKBSUDCFL7 Guarde este código e receba sua Quadradinha de Confete Grátis, na compra de uma quadrada (35cm) no mês de Agosto e Setembro/2013.";

        $email_pedido .= "<br><br><b>Parabéns, você já tem " . $objQuantidadePontos->soma_pontos . " pontos e agora ganhou mais " . floor($objBuscaDetalhamento->valor_total) . " pontos de fidelidade*!</b>";
        
        $email_pedido .= "<br><br><br><small><b>* Caso seu pedido seja cancelado os pontos de fidelidade são estornados.</b></small>";
        $email_pedido .= "<br><small><b>** Seu pedido ficou arquivado em nosso sistema. Este e-mail é meramente informativo.</b></small>";
        
        return $email_pedido;
    
    }
    
    public function exibir_total_pedido ($codigo_pedido)
    {
        $conexao = conectabd();
        $sqlPizzas = "SELECT * FROM ipi_pedidos p WHERE p.cod_pedidos = '" . $codigo_pedido . "'";
        $resPizzas = mysql_query($sqlPizzas);
        $objPizzas = mysql_fetch_object($resPizzas);
        desconectabd($conexao);
        return (bd2moeda($objPizzas->valor_total));
    }

    public function repetir_pedido($cod_pedidos)
    {
      $conexao = conectabd();     

      //Data e Hora do Inicio do pedido
      $_SESSION['ipi_carrinho']['data_hora_inicial'] = date('Y-m-d H:i:s');

      //Pedido que foi repetido
      $_SESSION['ipi_carrinho']['pedido_repetido'] = $cod_pedidos;      

      if($_SESSION['ipi_cliente']['autenticado'] == true)
      {
        //echo "<br />2";
        $SqlEnderecos = 'SELECT * FROM ipi_enderecos WHERE cod_clientes="'.$_SESSION['ipi_cliente']['codigo'].'"';
        $resEnderecos = mysql_query ($SqlEnderecos);
        while($objEnderecos = mysql_fetch_object($resEnderecos))
        {
          //echo "<br />3: ".$SqlEnderecos;
          $cep_limpo = str_replace ( "-", "", str_replace('.', '', $objEnderecos->cep));
          $objCep = executaBuscaSimples("SELECT COUNT(*) AS contagem FROM ipi_cep c INNER JOIN ipi_pizzarias p on p.cod_pizzarias = c.cod_pizzarias WHERE p.situacao !='INATIVO' AND c.cep_inicial <= $cep_limpo AND c.cep_final >= $cep_limpo", $conexao);
          $contagem += $objCep->contagem; 
          //echo "<br />4: ".$cep_limpo;

          $sql_cod_pizzarias = "SELECT c.cod_pizzarias FROM ipi_cep c INNER JOIN ipi_pizzarias p on p.cod_pizzarias = c.cod_pizzarias WHERE c.cep_inicial <= $cep_limpo AND c.cep_final >= $cep_limpo AND p.situacao !='INATIVO'";
          //die($sql_cod_pizzarias);
          $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
          $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
          $num_cod_pizzarias = mysql_num_rows($res_cod_pizzarias);
          $arr_cod_pizzarias[]['cod_pizzarias'] = $obj_cod_pizzarias->cod_pizzarias;

          //echo "<br />5: ".$sql_cod_pizzarias;

          if ((!isset($_SESSION['ipi_carrinho']['cep_visitante']))&&($num_cod_pizzarias>0))
          {
            //echo "<br />6: ".$obj_cod_pizzarias->cod_pizzarias;
            $_SESSION['ipi_carrinho']['cep_visitante'] = $objEnderecos->cep;
            $_SESSION['ipi_carrinho']['cod_pizzarias'] = $obj_cod_pizzarias->cod_pizzarias;
          }
        }
      }      

      
      
      $bebidas_combo = false;
      $aux_combo = 0;
      if ($_SESSION['ipi_carrinho']['id_combo_atual'])
      {
        $id_combo = $_SESSION['ipi_carrinho']['id_combo_atual'] + 1;
        $_SESSION['ipi_carrinho']['id_combo_atual'] = $_SESSION['ipi_carrinho']['id_combo_atual'] + 1;
      }
      else
      {
        $id_combo = 1;
      } 
     // $id_combo = $_SESSION['ipi_carrinho']['id_combo_atual'];
      /***
      * Pesquisa as pizzas do pedido
      * @valores cod_pizzarias, cod_pedidos_pizzas
      ***/
      $sql_buscar_pizzas_pedidos = "SELECT ip.cod_pizzarias, ipp.cod_pedidos_pizzas, ip.tipo_entrega FROM ipi_pedidos ip LEFT JOIN ipi_pedidos_pizzas ipp ON (ipp.cod_pedidos = ip.cod_pedidos) WHERE ip.cod_pedidos = '".$cod_pedidos."'";
      //die($sql_buscar_pizzas_pedidos); 
      $res_buscar_pizzas_pedidos = mysql_query($sql_buscar_pizzas_pedidos);
      while($obj_buscar_pizzas_pedidos = mysql_fetch_object($res_buscar_pizzas_pedidos))
      {
        $cod_pizzarias = ($obj_buscar_pizzas_pedidos->cod_pizzarias ? $obj_buscar_pizzas_pedidos->cod_pizzarias : 1);
        $cod_pedidos_pizzas = $obj_buscar_pizzas_pedidos->cod_pedidos_pizzas;

        //echo '<pre>';
        //print_r($_SESSION);
        //echo '</pre>';

        $_SESSION['ipi_carrinho']['buscar_balcao'] = $obj_buscar_pizzas_pedidos->tipo_entrega;
        $_SESSION['ipi_carrinho']['agendar'] = "Não";
        $_SESSION['ipi_carrinho']['horario'] = "";
        $_SESSION['ipi_carrinho']['registrar_entrega'] = 'ok';

        if ((!isset($_SESSION['ipi_carrinho']['cod_pizzarias'])) || (!isset($_SESSION['ipi_carrinho']['cep_visitante'])))
        {
          $_SESSION['ipi_carrinho']['cod_pizzarias'] = $cod_pizzarias;
          $_SESSION['ipi_carrinho']['buscar_balcao'] = 'Balcão';          
        }

        /***
        * Pesquisa as informações da pizza
        * @valores cod_tamanhos, cod_adicionais, cod_bordas, cod_tipo_massa, cod_opcoes_corte, quant_fracao
        ***/
        $sql_buscar_infos_pedido = "SELECT ipp.cod_tamanhos, ipa.cod_adicionais, ipb.cod_bordas, ipp.cod_tipo_massa, ipp.cod_opcoes_corte, ipp.quant_fracao, ipc.cod_combos, ipp.combo as pizza_combo, ipb.combo as borda_combo FROM ipi_pedidos_pizzas ipp LEFT JOIN ipi_pedidos_adicionais ipa ON (ipa.cod_pedidos = ipp.cod_pedidos AND ipa.cod_pedidos_pizzas = ipp.cod_pedidos_pizzas) LEFT JOIN ipi_pedidos_bordas ipb ON (ipb.cod_pedidos = ipp.cod_pedidos AND ipb.cod_pedidos_pizzas = ipp.cod_pedidos_pizzas) LEFT JOIN ipi_pedidos_combos ipc ON (ipc.cod_pedidos = ipp.cod_pedidos and ipc.cod_pedidos_combos = ipp.cod_pedidos_combos) WHERE ipp.cod_pedidos = '".$cod_pedidos."' AND ipp.cod_pedidos_pizzas = '".$cod_pedidos_pizzas."' ORDER BY ipc.cod_combos";
        //echo '<br>'.$sql_buscar_infos_pedido.'<br>';
        //die($sql_buscar_infos_pedido);
        $res_buscar_infos_pedido = mysql_query($sql_buscar_infos_pedido);
        while($obj_buscar_infos_pedido = mysql_fetch_object($res_buscar_infos_pedido))
        {

          // FIXME: As informação faltantes serão substituidas valores forçados, procurar uma solução mais eficiente
          $cod_tamanhos = $obj_buscar_infos_pedido->cod_tamanhos;
          $quant_fracao = $obj_buscar_infos_pedido->quant_fracao;
          $cod_bordas = ($obj_buscar_infos_pedido->cod_bordas > 0 ? $obj_buscar_infos_pedido->cod_bordas : 'N');
          $cod_adicionais = ($obj_buscar_infos_pedido->cod_adicionais > 0 ? $obj_buscar_infos_pedido->cod_adicionais : 'N');
          $cod_tipo_massa = $obj_buscar_infos_pedido->cod_tipo_massa;
          $cod_opcoes_corte = $obj_buscar_infos_pedido->cod_opcoes_corte;
          $cod_combos = ($obj_buscar_infos_pedido->cod_combos ? $obj_buscar_infos_pedido->cod_combos : 0);
          $indice_pizza = 0;

          if(($obj_buscar_infos_pedido->pizza_combo == 0) && ($obj_buscar_infos_pedido->borda_combo == 0))
          {
            $cod_combos = 0;
          }

          if($aux_combo == 0)
          {
            $aux_combo = $cod_combos;
          }

          if($cod_combos > 0)
          {
            //echo $cod_combos.'<br/>';
            if ($id_combo > 0 && $aux_combo != $cod_combos)
            {
              $id_combo = $id_combo + 1;
            }

            if($bebidas_combo == false)
            {
              $sql_buscar_bebidas_combo = "SELECT ipb.cod_bebidas_ipi_conteudos, ipb.quantidade FROM ipi_pedidos_bebidas ipb LEFT JOIN ipi_pedidos_combos ipc ON (ipc.cod_pedidos = ipb.cod_pedidos and ipc.cod_pedidos_combos = ipb.cod_pedidos_combos) WHERE ipb.cod_pedidos = '".$cod_pedidos."' AND ipb.combo = '1'";//FIXME, isso ira adicionar todas as bebidas combo do pedido no primeiro combo, exemplo: um combo individual e um casal, se mandar repitir, ira adicionar o refri lata e o refri 2 litros no combo de id)primeiro, que sera o individual
              //echo $sql_buscar_bebidas_combo;
              ///die
              $res_buscar_bebidas_combo = mysql_query($sql_buscar_bebidas_combo);
              while($obj_buscar_bebidas_combo = mysql_fetch_object($res_buscar_bebidas_combo))
              {
                /***
                * adicionar_bebida
                * @params $cod_bebidas_ipi_conteudos, $quantidade, $id_combo = '', $cod_combos = ''
                ***/
                $this->adicionar_bebida($obj_buscar_bebidas_combo->cod_bebidas_ipi_conteudos, $obj_buscar_bebidas_combo->quantidade, $id_combo, $cod_combos);            
              }
              $conexao = conectabd();  

              $bebidas_combo = true;
            }

            /***
            * adicionar_pizza
            * @params $cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $quant_fracao, $cod_opcoes_corte, $validar_cupom = '0', $numero_cupom = 0, $id_combo = '', $cod_combo = ''
            ***/
            $indice_pizza = $this->adicionar_pizza($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $quant_fracao, $cod_opcoes_corte, '0', 0, $id_combo, $cod_combos);
            $conexao = conectabd();


          }
          else
          {
            /***
            * adicionar_pizza
            * @params $cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $quant_fracao, $cod_opcoes_corte, $validar_cupom = '0', $numero_cupom = 0, $id_combo = '', $cod_combo = ''
            ***/
            $indice_pizza = $this->adicionar_pizza($cod_tamanhos, $cod_adicionais, $cod_bordas, $cod_tipo_massa, $quant_fracao, $cod_opcoes_corte);
            $conexao = conectabd();
          }

          if($aux_combo != $cod_combos)
          {
            $aux_combo = $cod_combos;
          }

          /***
          * Pesquisa as pizzas do pedido
          * @valores cod_pedidos_fracoes, cod_pizzas, fracao
          ***/
          $sql_buscar_pizzas_pedido = "SELECT ipf.cod_pedidos_fracoes, ipf.cod_pizzas, ipf.fracao FROM ipi_pedidos_fracoes ipf WHERE ipf.cod_pedidos = '".$cod_pedidos."' AND ipf.cod_pedidos_pizzas = '".$cod_pedidos_pizzas."'";
          $res_buscar_pizzas_pedido = mysql_query($sql_buscar_pizzas_pedido);
          while($obj_buscar_pizzas_pedido = mysql_fetch_object($res_buscar_pizzas_pedido))
          {
            $cod_pedidos_fracoes = $obj_buscar_pizzas_pedido->cod_pedidos_fracoes;
            $cod_pizzas = $obj_buscar_pizzas_pedido->cod_pizzas;
            $num_fracao = $obj_buscar_pizzas_pedido->fracao;

            $indice_fracao = $this->adicionar_fracao($indice_pizza, $cod_pizzas, $num_fracao);
            $conexao = conectabd();


            /***
            * Pesquisa os ingredientes da pizza em questão, separando entre adicionais, padrão e troca
            * @valores cod_ingredientes, ingrediente_padrao
            ***/
            
            $arr_ingredientes_receita = array(); 
            $sql_buscar_receita = "SELECT iiip.cod_ingredientes FROM ipi_ingredientes_ipi_pizzas iiip WHERE iiip.cod_pizzas = '".$cod_pizzas."'";
            $res_buscar_receita = mysql_query($sql_buscar_receita);
            while($obj_buscar_receita = mysql_fetch_object($res_buscar_receita))
            {
              $arr_ingredientes_receita[] = $obj_buscar_receita->cod_ingredientes;
            }

            $arr_ingrediente_padrao = array();  
            $arr_ingrediente_adicional = array(); 
            $arr_ingrediente_removidos = array();  
            $sql_buscar_ingredientes_pedido = "SELECT ipi.cod_ingredientes, ipi.ingrediente_padrao FROM ipi_pedidos_ingredientes ipi WHERE ipi.cod_pedidos_fracoes = '".$cod_pedidos_fracoes."' ";
            $res_buscar_ingredientes_pedido = mysql_query($sql_buscar_ingredientes_pedido);
            while($obj_buscar_ingredientes_pedido = mysql_fetch_object($res_buscar_ingredientes_pedido))
            {
              $cod_ingredientes = $obj_buscar_ingredientes_pedido->cod_ingredientes;
              $ingrediente_padrao = ($obj_buscar_ingredientes_pedido->ingrediente_padrao ? true : false);
              if($ingrediente_padrao)
                $arr_ingrediente_padrao[] = $cod_ingredientes;
              else
                $arr_ingrediente_adicional[] = $cod_ingredientes;
            }

            foreach ($arr_ingredientes_receita as $ing) 
            {
              if(!in_array($ing, $arr_ingrediente_padrao))
              {
                $arr_ingrediente_removidos[] = $ing;
              }
            }

            /*echo '<pre>';
            print_r($arr_ingredientes_receita);
            print_r($arr_ingrediente_padrao);
            print_r($arr_ingrediente_adicional);
            echo '</pre>';*/

            $arr_aux_troca =  array();
            if(count($arr_ingrediente_adicional) > 0)
            {
              foreach ($arr_ingrediente_adicional as $ing) 
              {
                $aux_adicional = false;
                foreach ($arr_ingredientes_receita as $ingrediente) 
                {
                  if(!in_array($ingrediente, $arr_ingrediente_padrao))
                  {
                    $sql_verificar_troca = "SELECT ii.cod_ingredientes_troca FROM ipi_ingredientes ii WHERE ii.cod_ingredientes = '".$ingrediente."' AND ii.cod_ingredientes_troca = '".$ing."'";
                    $res_verificar_troca = mysql_query($sql_verificar_troca);
                    $num_verificar_troca = mysql_num_rows($res_verificar_troca);
                    if($num_verificar_troca)
                    {
                      /***
                      * adicionar_ingrediente
                      * @params $indice_ses_pizza, $indice_ses_fracao, $cod_ingredientes, $ingrediente_padrao, $ingrediente_troca='0', $cod_ingredientes_troca=''
                      ***/

                      /*echo '<br/>$ingrediente - troca: '.$ingrediente;
                      echo '<br/>$ing - troca: '.$ing;*/

                      $this->adicionar_ingrediente($indice_pizza, $indice_fracao, $ingrediente, true, true, $ing);
                      $arr_aux_troca[] = $ing;
                      $aux_adicional = true;
                    }
                  }
                }
                $conexao = conectabd();
                if($aux_adicional == false)
                {
                  /***
                  * adicionar_ingrediente
                  * @params $indice_ses_pizza, $indice_ses_fracao, $cod_ingredientes, $ingrediente_padrao, $ingrediente_troca='0', $cod_ingredientes_troca=''
                  ***/
                  //echo '<br/>$ing - add: '.$ing;
                  $this->adicionar_ingrediente($indice_pizza, $indice_fracao, $ing, false);
                  $conexao = conectabd();              
                }
              }
            }
              
            foreach ($arr_ingrediente_padrao as $ingrediente) 
            {
              if(!in_array($ingrediente, $arr_ingrediente_removidos))
              {
                //echo '<br/>$ingrediente - padrao/n troca/n removido : '.$ingrediente;
                $this->adicionar_ingrediente($indice_pizza, $indice_fracao, $ingrediente, true); 
              }
            }  
            $conexao = conectabd();        

          }
        }

      }
      if($id_combo!=$_SESSION['ipi_carrinho']['id_combo_atual'] && $aux_combo!=0)
      {
        $_SESSION['ipi_carrinho']['id_combo_atual'] = $id_combo;
      }

      /***
      * Pesquisa as bebidas do pedido
      * OBS:// Apenas se a bebida não pertence algum COMBO.
      * @valores cod_bebidas_ipi_conteudos, quantidade
      ***/
      $sql_buscar_bebidas_pedido = "SELECT ipb.cod_bebidas_ipi_conteudos, ipb.quantidade FROM ipi_pedidos_bebidas ipb WHERE ipb.cod_pedidos = '".$cod_pedidos."' AND ipb.combo = 0";
      $res_buscar_bebidas_pedido = mysql_query($sql_buscar_bebidas_pedido);
      while($obj_buscar_bebidas_pedido = mysql_fetch_object($res_buscar_bebidas_pedido))
      {
        $cod_bebidas_ipi_conteudos = $obj_buscar_bebidas_pedido->cod_bebidas_ipi_conteudos;
        $quantidade = $obj_buscar_bebidas_pedido->quantidade;

        /***
        * adicionar_bebida
        * @params $cod_bebidas_ipi_conteudos, $quantidade, $id_combo = '', $cod_combos = ''
        ***/
        $this->adicionar_bebida($cod_bebidas_ipi_conteudos, $quantidade);
        $conexao = conectabd();

      }

      desconectabd($conexao);

    }


    public function pedidos_log($cod_pedidos, $nome, $versao, $plataforma, $idioma, $info_extra = '')
    {
        $con = conectar_bd();

        $sql_inserir_dados = sprintf("INSERT INTO ipi_clientes_informacao(cod_pedidos, tipo_log, data_envio, nome_navegador, versao_navegador, idioma, nome_plataforma, user_agent, informacao_extra, sessao) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",$cod_pedidos, 'PEDIDOS_LOG', date("Y-m-d H:i:s"), $nome, $versao, $idioma, $plataforma, $_SERVER['HTTP_USER_AGENT'], $info_extra, $_SESSION['ipi_carrinho']);
        $res_inserir_dados = mysql_query($sql_inserir_dados);

        desconectar_bd($con);
    }

       public function finalizar_pedido_ifood ($cliente, $pizzas, $pizzas_aux, $bebidas, $bebidas_aux, $cod_pizzarias, $obs_pedido, $cpf_nota_fiscal, $forma_pg, $valor_formas, $tipo_entrega, $horario_agendamento, $troco, $desconto,$frete,$comissao_frete = 0, $data_hora_pedido){
        $conexao = conectabd();


        require("pub_req_fuso_horario1.php");


        $agendado = ($horario_agendamento != '' ? 1 : 0);

        $desconto = ($desconto=="" ? 0 : moeda2bd($desconto));

        $frete = ($frete==""? 0 : $frete);

        $comissao_frete = ($comissao_frete=="" ? 0 : $comissao_frete);

        $total_pedido = $valor_formas - $desconto + $frete;

        $total_desconto = $valor_formas - $desconto;

        $total_pedido_limpo = $valor_formas;

        $impressao_fiscal = "0";

        if($frete<=0)
        {
            $comissao_frete = 0;
        } 
        //else if($comissao_frete == 0 || $frete < $comissao_frete)  //CLIENTE PEDIU PARA LIBERAR A COMISSÃO MAIOR QUE O FRETE, VAI FUNCIONAR ASSIM POR 20 DIAS ATENTENDENDO UM CHAMADO DELE, ASSIM QUE PASSAR O PERIODO VOLTAR A TRAVA.
        else if($comissao_frete == 0)
        {
            $comissao_frete = $frete;
        }

        if ($cpf_nota_fiscal != '')
        {
           $impressao_fiscal = "1";
        }

        if($impressao_fiscal == "0")
        {
          $sql_impressao = "SELECT impressao_automatica FROM ipi_pizzarias p WHERE cod_pizzarias = ".$cod_pizzarias;
          //echo "<br />sql_fiscal: ".$sql_impressao;
          $res_impressao = mysql_query($sql_impressao);
          $obj_impressao = mysql_fetch_object($res_impressao);
              $impressao_fiscal = $obj_impressao->impressao_automatica;
        }
/*
        if($forma_pg !="DINHEIRO" && $forma_pg !="TICKET" && $forma_pg !="CHEQUE" )
        {
            $impressao_fiscal = "1";
        }
*/
        if ( ($cpf_nota_fiscal == '') && ($impressao_fiscal=="1") )
        {
          $cpf_nota_fiscal = "000.000.000-00";
        }

        $forma_pagamento = "Múltiplas";

        $sqlInserirPedido = "INSERT INTO ipi_pedidos (cod_clientes, cod_pizzarias, cod_usuarios_pedido, data_hora_pedido, valor, valor_entrega, valor_comissao_frete, valor_total, desconto, forma_pg, situacao, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2,referencia_endereco,referencia_cliente, tipo_entrega, horario_agendamento, agendado, pontos_fidelidade_total, obs_pedido, origem_pedido, data_hora_inicial, data_hora_final, impressao_fiscal, ifood) VALUES 
        ('" . $cliente['cod_clientes'] . "', '" . $cod_pizzarias . "', '" . $_SESSION['usuario']['codigo'] . "', '" . date("Y-m-d H:i:s") . "',  '" . $total_pedido_limpo . "', ".$frete.",'".$comissao_frete."', '" . $total_pedido . "', ".$desconto.", '" . $forma_pagamento . "', 'NOVO', '" . $cliente['endereco'] . "', '" . $cliente['numero'] . "', '" . $cliente['complemento'] . "', '" . $cliente['edificio'] . "', '" . $cliente['bairro'] . "', '" . $cliente['cidade'] . "', '" . $cliente['estado'] . "', '" . $cliente['cep'] . "', '" . $cliente['telefone_1'] . "', '" . $cliente['telefone_2'] . "','" . ($cliente['referencia']). "','" .( $cliente['ref_cliente']) ."', '" . $tipo_entrega . "', '" . $horario_agendamento . "', '$agendado', '" . $_SESSION['ipi_caixa']['fidelidade_pontos_gastos'] . "', '".texto2bd($obs_pedido)."', 'NET', '".$data_hora_pedido."', '".date("Y-m-d H:i:s")."', '".$impressao_fiscal."', 1)";
        //echo "<br>0: ".$sqlInserirPedido;
     
        $resInserirPedido = mysql_query($sqlInserirPedido);
        $cod_pedidos = mysql_insert_id();

        $this->log($conexao, "PEDIDO_TEL_INSERIDO", "", $cod_pedidos, 0, $_SESSION['ipi_caixa']['cod_clientes']);


        //IMPRIMIR EM IMPRESSORA COZINHA
        $cod_mesas = 1; //Forçado para esse código
        $cod_impressoras = 1; //Forçado para esse código
        $cod_caloboradores = 1; //Forçado para esse código

        $sql_abrir_mesa = "INSERT INTO ipi_mesas_pedidos (cod_pedidos, cod_mesas, cod_usuarios_abertura, data_hora_abertura, situacao_pedido_mesa) VALUES ('".$cod_pedidos."', ".$cod_mesas.", '".$_SESSION['usuario']['codigo']."', '".date("Y-m-d H:i:s")."', 'ABERTO')";
        //echo $sql_abrir_mesa;
        $res_abrir_mesa = mysql_query($sql_abrir_mesa);
        $cod_mesas_pedidos = mysql_insert_id();
        //fIM IMPRIMIR EM IMPRESSORA COZINHA


        //FORMAS DE PAGAMENTO
        if ( is_array($forma_pg) == true )
        {
          $cnt_formas = count($forma_pg);
          for ($a=0; $a<$cnt_formas; $a++)
          {
            $sql_forma_pgto = "INSERT INTO ipi_pedidos_formas_pg (cod_pedidos, cod_formas_pg, valor) VALUES ($cod_pedidos, '".$forma_pg[$a]."', '" . $valor_formas[$a] . "')";
            //echo "<br>$a: ".$sql_forma_pgto;
            $res_forma_pgto = mysql_query($sql_forma_pgto);
          }
        }
        else
        {
          $sql_forma_pgto = "INSERT INTO ipi_pedidos_formas_pg (cod_pedidos, cod_formas_pg, valor) VALUES ($cod_pedidos, '".$forma_pg."', '" . $total_pedido . "')";
         // echo "<br>X: ".$sql_forma_pgto;
          $res_forma_pgto = mysql_query($sql_forma_pgto);
        }
        
        if (($troco != "0") && ($troco != ""))
        {
            $troco = $troco + $total_pedido;
             
            $sqlDetalhesPgto = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos, 'TROCO', '" . ($troco) . "')";
            $resDetalhesPgto = mysql_query($sqlDetalhesPgto);
        }
        
        if ($cpf_nota_fiscal != '')
        {
            $sqlDetalhesPgto = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos, 'CPF_NOTA_PAULISTA', '" . $cpf_nota_fiscal . "')";
            $resDetalhesPgto = mysql_query($sqlDetalhesPgto);
        }
        
        /*
        if ($_SESSION['ipi_caixa']['pagamento']['tipo'] == "VISA")
        {
            $sqlDetPag = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'cod_pedido_operadora','" . $_SESSION['ipi_caixa']['pagamento']['cod_pedido_operadora'] . "')";
            $resDetPag = mysql_query($sqlDetPag);
            
            $sqlAux = "SELECT * FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_caixa']['pagamento']['cod_pedido_operadora'] . "'";
            $resAux = mysql_query($sqlAux);
            
            while ($objAux = mysql_fetch_object($resAux))
            {
                $sqlCupom = "INSERT INTO ipi_pedidos_detalhes_pg (cod_pedidos, chave, conteudo) VALUES ($cod_pedidos,'" . $objAux->chave . "','" . $objAux->valor . "')";
                $resCupom = mysql_query($sqlCupom);
            }
            
            $sqlAux = "DELETE FROM ipi_pedidos_pag_temp WHERE cod_pedido_operadora='" . $_SESSION['ipi_caixa']['pagamento']['cod_pedido_operadora'] . "'";
            $resAux = mysql_query($sqlAux);
        }
        */
        
        $numero_pizzas=count($pizzas);
    
        if ($numero_pizzas > 0)
        {
            for ($a = 0; $a < $numero_pizzas; $a++)
            {

                $qtde = $pizzas[$pizzas_aux[$a]]['qtde_pizzas'];
                if (empty($qtde)){
                    $qtde = 1;
                }
                for($cont=0;$cont<$qtde;$cont++):
                $cod_tamanhos = $pizzas[$pizzas_aux[$a]]['cod_tamanhos'];              
                //$cod_tamanho_forcado = 2; //$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos']
                $fracao = $pizzas[$pizzas_aux[$a]]['qtde_fracao']; //$_SESSION['ipi_caixa']['pedido'][$a]['quantidade_fracoes']
                $cod_tipo_massa = 1;//padrão $_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa']
                $cod_combos_forcado;//$_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']
                $cod_bordas = (isset($pizzas[$pizzas_aux[$a]][0]['cod_bordas']) ? $pizzas[$pizzas_aux[$a]][0]['cod_bordas']: 'N'); //$_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] != "N"
                $cod_corte = 2;//$_SESSION['ipi_caixa']['pedido'][$a]['cod_opcoes_corte']
                $preco_pizza = $pizzas[$pizzas_aux[$a]]['valor'];

                // echo '<h1>'.$fracao.'<br>';
                $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_fracoes fp WHERE fp.cod_tamanhos=" . $cod_tamanhos . " AND fp.cod_fracoes =" . $fracao . " AND fp.cod_pizzarias = ".$cod_pizzarias;
                //echo $sqlAux."<br>";
                //die($sqlAux);
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                $cod_tamanhos_aux = $objAux->cod_tamanhos;
               
                $sqlAux2 = "SELECT * FROM ipi_tamanhos_ipi_tipo_massa WHERE cod_tamanhos=" . $cod_tamanhos_aux . " AND cod_tipo_massa=" . $cod_tipo_massa;
                //die($sqlAux2);
                $resAux2 = mysql_query($sqlAux2);
                $objAux2 = mysql_fetch_object($resAux2);

                $sqlAux3 = "SELECT * FROM ipi_combos_produtos WHERE cod_tamanhos='" . $cod_tamanhos . "' AND cod_combos = '" . $cod_combos_forcado."'";
                $resAux3 = mysql_query($sqlAux3);
                $objAux3 = mysql_fetch_object($resAux3);

                
                // if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo']=="1")
                // {
                //     if ( !is_array( $arr_id_combo_pedido_combo[ $_SESSION['ipi_caixa']['pedido'][$a]['id_combo'] ] ) )
                //     {
                //       $sql_aux_combo = "SELECT * FROM ipi_combos c INNER JOIN ipi_combos_pizzarias cp ON (c.cod_combos = cp.cod_combos) WHERE c.cod_combos = '".$_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']."' AND c.situacao='ATIVO' AND cp.cod_pizzarias = '".$cod_pizzarias."'";
                //       $res_aux_combo = mysql_query($sql_aux_combo);
                //       $obj_aux_combo = mysql_fetch_object($res_aux_combo);

                //       // FIXME Não foi tratado para pagamentos de COMBOS com FIDELIDADE
                //       $sql_combos_pedidos = "INSERT INTO ipi_pedidos_combos (cod_combos, cod_pedidos, pontos_fidelidade, preco, fidelidade, numero_combo) VALUES('".$_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']."' , '".$cod_pedidos."', '', '".$obj_aux_combo->preco."', '0', '".$_SESSION['ipi_caixa']['pedido'][$a]['id_combo']."')";
                //       $res_combos_pedidos = mysql_query($sql_combos_pedidos);
                //       $cod_combos_pedidos = mysql_insert_id();

                //       $arr_id_combo_pedido_combo[ $_SESSION['ipi_caixa']['pedido'][$a]['id_combo'] ]['cod_pedidos_combos'] = $cod_combos_pedidos;
                //     }
                // }


                $sqlInsPedPizzas = "INSERT INTO ipi_pedidos_pizzas (cod_pedidos_combos, cod_combos_produtos, cod_pedidos, cod_tamanhos, cod_tipo_massa, cod_opcoes_corte, cod_motivo_promocoes, quant_fracao, preco, preco_massa, promocional, fidelidade, combo) VALUES ('".$arr_id_combo_pedido_combo[$_SESSION['ipi_caixa']['pedido'][$a]['id_combo']]['cod_pedidos_combos']."', '" . $objAux3->cod_combos_produtos . "', '" . $cod_pedidos . "', '" . $cod_tamanhos . "', '" . $cod_tipo_massa . "', '" . $cod_corte . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_motivo_promocoes_pizza'] . "','" . $fracao . "', '" . $preco_pizza . "', '" . $objAux2->preco . "','" . $_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] . "')";
                $resInsPedPizzas = mysql_query($sqlInsPedPizzas);
                $cod_pedidos_pizzas = mysql_insert_id();
                //echo "<br><h1>: ".$sqlInsPedPizzas;


                //ENVIAR PARA PRINTER DA PRINTER DA COZINHA 
                $tipo_impressao = 'PRODUTOS';
                $cod_pedidos_bebidas = 0;
                //$tipo_impressao = 'BEBIDAS';
                //$cod_pedidos_pizzas = 0;
                $sql_impressao = "INSERT INTO ipi_mesas_impressao (cod_pizzarias, cod_mesas_pedidos, cod_pedidos, cod_pedidos_pizzas, cod_pedidos_bebidas, tipo_impressao, cod_impressoras, situacao_impressao) VALUES ($cod_pizzarias, $cod_mesas_pedidos, $cod_pedidos, $cod_pedidos_pizzas, $cod_pedidos_bebidas, '".$tipo_impressao."', '$cod_impressoras', 'AGUARDANDO_IMPRESSAO')";
                $res_impressao = mysql_query($sql_impressao);        
                //ENVIAR PARA PRINTER DA PRINTER DA COZINHA 
              
                 if ($cod_bordas != "N")
                 {
                     $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_bordas WHERE cod_bordas=" . $cod_bordas. " AND cod_tamanhos=" . $cod_tamanhos. " AND cod_pizzarias = " . $cod_pizzarias;
                     $resAux = mysql_query($sqlAux);
                     
                     if (mysql_num_rows($resAux)>0){
                        $objAux = mysql_fetch_object($resAux);
                        $preco_borda = $objAux->preco;
                     }
                     else{
                        $preco_borda = $pizzas[$pizzas_aux[$a]][0]['preco_borda'];
                     }
                    //echo  "<br>sqlAux: ".$sqlAux;
                    // echo $preco_borda;
                    
                    }
                    else{
                        $preco_borda = 0;
                     }

                    $sqlAux4 = "SELECT * FROM ipi_combos_produtos WHERE tipo='BORDA' AND cod_combos = '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_combos']."'";
                    $resAux4 = mysql_query($sqlAux4);
                    $objAux4 = mysql_fetch_object($resAux4);
                    //echo "<br>4: ".$sqlAux4;

                    $sqlBorda = "INSERT INTO ipi_pedidos_bordas (cod_pedidos, cod_pedidos_pizzas, cod_bordas, cod_pedidos_combos, cod_motivo_promocoes, cod_combos_produtos, preco, pontos_fidelidade, promocional, fidelidade, combo) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_bordas . "', '".$arr_id_combo_pedido_combo[$_SESSION['ipi_caixa']['pedido'][$a]['id_combo']]['cod_pedidos_combos']."', '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_motivo_promocoes_borda'] . "', '" . $objAux4->cod_combos_produtos . "' , '" . $preco_borda . "', '', '" . $_SESSION['ipi_caixa']['pedido'][$a]['borda_promocional'] . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['borda_fidelidade'] . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['borda_combo'] . "')";
                    $resBorda = mysql_query($sqlBorda);
                    //echo "<Br>2: ".$sqlBorda;
                // }
                
                // if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] != "N")
                // {
                //     $sqlAux = "SELECT * FROM ipi_tamanhos_ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] . " AND cod_tamanhos=" . $cod_tamanhos . " AND cod_pizzarias = ".$cod_pizzarias;
                //     $resAux = mysql_query($sqlAux);
                //     $objAux = mysql_fetch_object($resAux);
                //     $sqlAdicional = "INSERT INTO ipi_pedidos_adicionais (cod_pedidos, cod_pedidos_pizzas, cod_adicionais, preco, pontos_fidelidade) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] . "', '" . $objAux->preco . "', '')";
                //     $resAdicional = mysql_query($sqlAdicional);
                // }
                
               
                $num_fracoes = $pizzas[$pizzas_aux[$a]]['qtde_fracao'];
                // $num_fracoes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao']);
                //echo "<br>num_fracoes: ".$num_fracoes;
                // $preco_fracao_maior = 0;
                // for ($b = 0; $b < $num_fracoes; $b++)
                // {
                //     ####FORÇADO####
                //     // $cod_pizzas = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                //     $cod_pizzas = $pizzas[$pizzas_aux[$a]][$b+1]['cod_pizzas'];

                   
                //     // $num_fracao = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['num_fracao'];
                //     $num_fracao = $pizzas[$pizzas_aux[$a]][$b]['num_fracao'];

                //     echo "<h1>$num_fracao";
                //     ####FORÇADO####
                //     $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos. " AND pt.cod_pizzarias = ".$cod_pizzarias;
                //     //echo  "<br>sqlAux: ".$sqlAux;
                //     $resAux = mysql_query($sqlAux);
                //     $objAux = mysql_fetch_object($resAux);
                    
                //     if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] == "1")
                //     {
                //         $preco_fracao = 0;
                //     }
                //     else
                //     {
                //         $preco_fracao = $objAux->preco;
                //     }

                //     // if($preco_fracao > $preco_fracao_maior)
                //     // {
                //     //     $preco_fracao_maior = $preco_fracao;
                //     // }
                // }

                for ($b = 1; $b <= $num_fracoes; $b++)
                {
                    $cod_pizzas = $pizzas[$pizzas_aux[$a]][$b]['cod_pizzas'];
                    if ($cod_pizzas==""){
                        $cod_pizzas=14;
                    }
                  
                   $num_fracao = $pizzas[$pizzas_aux[$a]][$b]['num_fracao'];
                   $preco_fracao = $pizzas[$pizzas_aux[$a]][$b]['preco'];
                   $obs_fracao = $pizzas[$pizzas_aux[$a]]['obs_fracao'];
                   //die($obs_fracao);
                    ####FORÇADO####
                   //  $sqlAux = "SELECT * FROM ipi_pizzas_ipi_tamanhos pt WHERE pt.cod_pizzas=" . $cod_pizzas . " AND pt.cod_tamanhos=" . $cod_tamanhos. " AND pt.cod_pizzarias = ".$cod_pizzarias;
                   //  //echo  "<br>sqlAux: ".$sqlAux;
                   // // die();
                   //  $resAux = mysql_query($sqlAux);
                   //  if ($resAux){
                   //      $objAux = mysql_fetch_object($resAux);
                   //  }
                    
    
                   
                    $sqlPedFracoes = "INSERT INTO ipi_pedidos_fracoes (cod_pedidos, cod_pedidos_pizzas, cod_pizzas, fracao, preco, pontos_fidelidade_pizza, obs_fracao) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pizzas . "', '" . $num_fracao . "', '" . $preco_fracao . "', '', '".$obs_fracao."')";
                    //echo "<h1>$sqlPedFracoes";
                    $resPedFracoes = mysql_query($sqlPedFracoes);
                    if($resPedFracoes){
                        $cod_pedidos_fracoes = mysql_insert_id();
                    }
                    
                    
                    // $sqlAux = "SELECT * FROM ipi_pizzas WHERE cod_pizzas=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                    // //echo "<br>".$sqlAux;
                    // $resAux = mysql_query($sqlAux);
                    // if ($resAux){
                    //     $objAux = mysql_fetch_object($resAux);
                    // }
                    
                    $sql_ingredientes = "SELECT * FROM ipi_ingredientes_ipi_pizzas WHERE cod_pizzas=" . $cod_pizzas;
                    $res_ingredientes = mysql_query($sql_ingredientes);
                    $num_ingredientes = mysql_num_rows($res_ingredientes);
        
                    if ($num_ingredientes > 0)
                    {
                        for ($x = 0; $x < $num_ingredientes; $x++)
                        {
                            $obj_ingredientes = mysql_fetch_object($res_ingredientes);
                            $preco_ingredientes = arredondar_preco_ingrediente($obj_ingredientes->preco_troca, $num_fracoes);
                             $sqlPedIngredientes = "INSERT INTO ipi_pedidos_ingredientes (cod_pedidos, cod_pedidos_pizzas, cod_pedidos_fracoes, cod_ingredientes, cod_ingrediente_trocado, preco, pontos_fidelidade, ingrediente_padrao) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pedidos_fracoes . "', '" .  $obj_ingredientes->cod_ingredientes . "', '" . $cod_ingrediente_trocado . "', '" . $preco_ingredientes . "', '', 1)";
                                 $resPedIngredientes = mysql_query($sqlPedIngredientes);
                             //echo $sqlPedIngredientes."<br>";
                        }
                    }
                    //die();
                    // $num_ingredientes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes']);
                    // for ($c = 0; $c < $num_ingredientes; $c++)
                    // {
                    //     $cod_ingredientes = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                    //     $ingrediente_padrao = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'];
                    //     $ingrediente_troca = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_troca'];
                    //     $cod_ingrediente_trocado = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes_troca'];

                    //     if($ingrediente_troca)
                    //     {

                    //       $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_ingredientes=" . $cod_ingrediente_trocado . " AND it.cod_tamanhos=" . $cod_tamanhos. " AND it.cod_pizzarias = ".$cod_pizzarias;
                    //       $resAux = mysql_query($sqlAux);
                    //       $objAux = mysql_fetch_object($resAux);
                    //       $preco_ingrediente = arredondar_preco_ingrediente($objAux->preco_troca, $num_fracoes);
                    //     }
                    //     else
                    //     {
                    //       $sqlAux = "SELECT * FROM ipi_ingredientes_ipi_tamanhos it WHERE it.cod_ingredientes=" . $cod_ingredientes . " AND it.cod_tamanhos=" . $cod_tamanhos. " AND it.cod_pizzarias = ".$cod_pizzarias;
                    //       $resAux = mysql_query($sqlAux);
                    //       $objAux = mysql_fetch_object($resAux);
                    //           if($_SESSION['ipi_caixa']['pedido'][$a]['adicionais_inteira'])
                    //           {
                    //             $preco_ingrediente = arredondar_preco_ingrediente_antigo($objAux->preco, $num_fracoes);
                    //            //echo "asdafed".$preco_ingrediente_extra."  ";
                    //           }
                    //           else
                    //           {
                    //             $preco_ingrediente = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                    //           }
                    //       //$preco_ingrediente = arredondar_preco_ingrediente($objAux->preco, $num_fracoes);
                    //     }
                        
                    //     $sqlPedIngredientes = "INSERT INTO ipi_pedidos_ingredientes (cod_pedidos, cod_pedidos_pizzas, cod_pedidos_fracoes, cod_ingredientes, cod_ingrediente_trocado, preco, pontos_fidelidade, ingrediente_padrao) VALUES ('" . $cod_pedidos . "', '" . $cod_pedidos_pizzas . "', '" . $cod_pedidos_fracoes . "', '" . $cod_ingredientes . "', '" . $cod_ingrediente_trocado . "', '" . $preco_ingrediente . "', '', '" . $ingrediente_padrao . "')";
                    //     $resPedIngredientes = mysql_query($sqlPedIngredientes);
                    
                    // }
                }
            endfor;
            }
        
        }


        // $numero_bebidas = isset($_SESSION['ipi_caixa']['bebida']) ? count($_SESSION['ipi_caixa']['bebida']) : 0;

       $numero_bebidas=count($bebidas);

        if ($numero_bebidas > 0)
        {
            
            for ($a = 0; $a < $numero_bebidas; $a++)
            {

                $bebidas_agrupadas = count($bebidas[$bebidas_aux[$a]])-3;
                if($bebidas_agrupadas>1){
                    for ($i=0; $i < $bebidas_agrupadas ; $i++) { 
                            $cod_conteudos = $bebidas[$bebidas_aux[$a]][$i]['cod_conteudos'];
                            $cod_bebidas = $bebidas[$bebidas_aux[$a]][$i]['cod_bebidas'];
                                                //echo $cod_bebidas."<br>";
                            $sqlAux = "SELECT cod_bebidas_ipi_conteudos FROM ipi_bebidas_ipi_conteudos  WHERE cod_conteudos=" . $cod_conteudos ." AND cod_bebidas = ".$cod_bebidas;
                            $resAux = mysql_query($sqlAux);
                            $objAux = mysql_fetch_object($resAux);
                            $cod_bebidas_ipi_conteudos = $objAux->cod_bebidas_ipi_conteudos;
                            $preco_bebida = $bebidas[$bebidas_aux[$a]][$i]['preco'];
                            $sqlAux6 = "SELECT * FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') WHERE bc.cod_bebidas_ipi_conteudos = ".$cod_bebidas_ipi_conteudos;
                                             
                            $resAux6 = mysql_query($sqlAux6);
                            $objAux6 = mysql_fetch_object($resAux6);

                            $sqlAux5 = "SELECT * FROM ipi_combos_produtos cp WHERE cp.tipo='BEBIDA' AND cp.cod_conteudos='".$objAux6->cod_conteudos."' AND cp.cod_combos = '" . $_SESSION['ipi_caixa']['bebida'][$a]['cod_combos']."'";
                            $resAux5 = mysql_query($sqlAux5);
                                                $objAux5 = mysql_fetch_object($resAux5);

                            $quantidade_bebida = $bebidas[$bebidas_aux[$a]]['qtde'];
                            $sqlPedBebidas = "INSERT INTO ipi_pedidos_bebidas (cod_pedidos, cod_bebidas_ipi_conteudos, cod_combos_produtos, cod_pedidos_combos, cod_motivo_promocoes, preco, pontos_fidelidade, quantidade, promocional, fidelidade, combo) VALUES ('" . $cod_pedidos . "', '" . $cod_bebidas_ipi_conteudos . "', '" . $objAux5->cod_combos_produtos . "', '".$arr_id_combo_pedido_combo[$_SESSION['ipi_caixa']['bebida'][$a]['id_combo']]['cod_pedidos_combos']."', '".$_SESSION['ipi_caixa']['bebida'][$a]['cod_motivo_promocoes_bebida']."', '" . $preco_bebida . "', '', '" . $quantidade_bebida . "', '" . $_SESSION['ipi_caixa']['bebida'][$a]['bebida_promocional'] . "', '" . $_SESSION['ipi_caixa']['bebida'][$a]['bebida_fidelidade'] . "', '" . $_SESSION['ipi_caixa']['bebida'][$a]['bebida_combo'] . "')";
                            $resPedBebidas = mysql_query($sqlPedBebidas);
                            $cod_pedidos_bebidas = mysql_insert_id();
                    }
                }
                else{               

                            $cod_conteudos = $bebidas[$bebidas_aux[$a]][$a]['cod_conteudos'];
                            $cod_bebidas = $bebidas[$bebidas_aux[$a]][$a]['cod_bebidas'];
                                            // echo($cod_bebidas_ipi_conteudos);
                            $sqlAux = "SELECT cod_bebidas_ipi_conteudos FROM ipi_bebidas_ipi_conteudos  WHERE cod_conteudos=" . $cod_conteudos ." AND cod_bebidas = ".$cod_bebidas;
                            $resAux = mysql_query($sqlAux);
                            $objAux = mysql_fetch_object($resAux);
                            $cod_bebidas_ipi_conteudos = $objAux->cod_bebidas_ipi_conteudos;
                                                            ####FORÇADO####
                                            //$cod_bebidas_ipi_conteudos = 27; //$_SESSION['ipi_caixa']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                                            ####FORÇADO####
                                            // $cod_bebidas_ipi_conteudos = $_SESSION['ipi_caixa']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                                            // $sqlAux = "SELECT * FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') INNER JOIN ipi_bebidas b ON (b.cod_bebidas=bc.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $cod_bebidas_ipi_conteudos;
                                            // $resAux = mysql_query($sqlAux);
                                            // $objAux = mysql_fetch_object($resAux);
                                            // if ($_SESSION['ipi_caixa']['bebida'][$a]['bebida_promocional'] == "1")
                                            // {
                                            //     $preco_bebida = 0;
                                                
                                            //     $sqlAux2 = "SELECT * FROM ipi_cupons WHERE cupom ='" . $_SESSION['ipi_caixa']['cupom'] . "'";
                                            //     $resAux2 = mysql_query($sqlAux2);
                                            //     $objAux2 = mysql_fetch_object($resAux2);
                                            //     $numAux2 = mysql_num_rows($resAux2);
                                                
                                            //     if ($numAux2 > 0)
                                            //     {
                                            //         $sqlPedCupom = "INSERT INTO ipi_pedidos_ipi_cupons (cod_pedidos, cod_cupons) VALUES ('" . $cod_pedidos . "', '" . $objAux2->cod_cupons . "')";
                                            //         $resPedCupom = mysql_query($sqlPedCupom);
                                                    
                                            //         if ($objAux2->promocao == "0")
                                            //         {
                                            //             $sqlCupom = "UPDATE ipi_cupons SET valido=0 WHERE cod_cupons='" . $objAux2->cod_cupons . "'";
                                            //             $resCupom = mysql_query($sqlCupom);
                                            //         }
                                            //     }
                                            // }
                                            // else
                                            // {
                                                // $preco_bebida = $objAux->preco;
                            $preco_bebida = $bebidas[$bebidas_aux[$a]]['valor'];
                                                // echo "<h1>$preco_bebida";
                                                // die();
                                            // }
                                                
                                          
                            $sqlAux6 = "SELECT * FROM ipi_conteudos_pizzarias cp INNER JOIN ipi_bebidas_ipi_conteudos bc ON (cp.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos AND cp.cod_pizzarias = '".$cod_pizzarias."') WHERE bc.cod_bebidas_ipi_conteudos = ".$cod_bebidas_ipi_conteudos;
                                         
                                          
                            $resAux6 = mysql_query($sqlAux6);
                            $objAux6 = mysql_fetch_object($resAux6);

                            $sqlAux5 = "SELECT * FROM ipi_combos_produtos cp WHERE cp.tipo='BEBIDA' AND cp.cod_conteudos='".$objAux6->cod_conteudos."' AND cp.cod_combos = '" . $_SESSION['ipi_caixa']['bebida'][$a]['cod_combos']."'";
                            $resAux5 = mysql_query($sqlAux5);
                            $objAux5 = mysql_fetch_object($resAux5);

                                           
                                            // $quantidade_bebida = 3; // $_SESSION['ipi_caixa']['bebida'][$a]['quantidade'];
                            $quantidade_bebida = $bebidas[$bebidas_aux[$a]]['qtde'];
                                             
                                          

                            $sqlPedBebidas = "INSERT INTO ipi_pedidos_bebidas (cod_pedidos, cod_bebidas_ipi_conteudos, cod_combos_produtos, cod_pedidos_combos, cod_motivo_promocoes, preco, pontos_fidelidade, quantidade, promocional, fidelidade, combo) VALUES ('" . $cod_pedidos . "', '" . $cod_bebidas_ipi_conteudos . "', '" . $objAux5->cod_combos_produtos . "', '".$arr_id_combo_pedido_combo[$_SESSION['ipi_caixa']['bebida'][$a]['id_combo']]['cod_pedidos_combos']."', '".$_SESSION['ipi_caixa']['bebida'][$a]['cod_motivo_promocoes_bebida']."', '" . $preco_bebida . "', '', '" . $quantidade_bebida . "', '" . $_SESSION['ipi_caixa']['bebida'][$a]['bebida_promocional'] . "', '" . $_SESSION['ipi_caixa']['bebida'][$a]['bebida_fidelidade'] . "', '" . $_SESSION['ipi_caixa']['bebida'][$a]['bebida_combo'] . "')";
                            $resPedBebidas = mysql_query($sqlPedBebidas);
                            $cod_pedidos_bebidas = mysql_insert_id();


                             //ENVIAR PARA PRINTER DA PRINTER DA COZINHA 
                            //$tipo_impressao = 'PRODUTOS';
                            //$cod_pedidos_bebidas = 0;
                            $tipo_impressao = 'BEBIDAS';
                            $cod_pedidos_pizzas = 0;
                            $sql_impressao = "INSERT INTO ipi_mesas_impressao (cod_pizzarias, cod_mesas_pedidos, cod_pedidos, cod_pedidos_pizzas, cod_pedidos_bebidas, tipo_impressao, cod_impressoras, situacao_impressao) VALUES ($cod_pizzarias, $cod_mesas_pedidos, $cod_pedidos, $cod_pedidos_pizzas, $cod_pedidos_bebidas, '".$tipo_impressao."', '$cod_impressoras', 'AGUARDANDO_IMPRESSAO')";
                            $res_impressao = mysql_query($sql_impressao);        
                            //ENVIAR PARA PRINTER DA PRINTER DA COZINHA 
                            //echo "<br>3: ".$sqlPedBebidas;
}
             

            }
        }


         //ORDEM DE IMPRESSÃO NA PRINTER DA COZINHA
        $sql_impressao = "INSERT INTO ipi_mesas_ordem_impressao (cod_pizzarias, cod_impressoras, cod_usuarios_impressao, cod_colaboradores_impressao, data_hora_impressao, tipo_impressao, situacao_ordem_impressao) VALUES ('".$cod_pizzarias."', '".$cod_impressoras."','".$_SESSION['usuario']['codigo']."', '".$cod_caloboradores."', '".date("Y-m-d H:i:s")."', 'IMPRIMIR_PRODUTOS', 'NOVO')";
        $res_impressao = mysql_query($sql_impressao);
        $cod_mesas_ordem_impressao = mysql_insert_id();
        $sql_imprimir = "UPDATE ipi_mesas_impressao mi SET cod_mesas_ordem_impressao = '".$cod_mesas_ordem_impressao."', situacao_impressao = 'ENVIADO_IMPRESSORA' WHERE mi.cod_pedidos = '".$cod_pedidos."' AND mi.cod_mesas_pedidos = '".$cod_mesas_pedidos."' AND mi.situacao_impressao = 'AGUARDANDO_IMPRESSAO' AND cod_impressoras = '".$cod_impressoras."'";
        $res_imprimir = mysql_query($sql_imprimir);
        //ORDEM DE IMPRESSÃO NA PRINTER DA COZINHA
        
        // $fidelidade_descontar = isset($_SESSION['ipi_caixa']['fidelidade_pontos_gastos']) ? $_SESSION['ipi_caixa']['fidelidade_pontos_gastos'] : 0;
        // $fidelidade_descontar = $fidelidade_descontar * (-1);
        
        // if ($fidelidade_descontar != 0)
        // {
        //     $sqlPedCupom = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, pontos) VALUES ('" . $_SESSION['ipi_cliente']['codigo'] . "', NOW(), " . $fidelidade_descontar . ")";
        //     $resPedCupom = mysql_query($sqlPedCupom);
        // }
        
        // $_SESSION[ipi_cliente][pontos_fidelidade] = $_SESSION[ipi_cliente][pontos_fidelidade] + $fidelidade_descontar;
        desconectabd($conexao);
       
        return $cod_pedidos;
        
    }
}
?>
