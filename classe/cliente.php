<?

require_once dirname(__FILE__) . '/../bd.php';
require_once dirname(__FILE__) .'/../sys/lib/php/formulario.php';
require_once dirname(__FILE__) . '/../ipi_req_carrinho_classe.php';

/**
 * Classe de Cliente
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       27/04/2012   Thiago        Criado.
 *
 */
class Cliente
{

    /**
    * Cadastro o cliente no banco de dados.
    *
    * @param string Colocar todos os dados do cliente.
    * 
    * @return 0 caso consiga e 1 ou 2 depedendendo e caso dê erro.
    */
    public function cadastrar($email,$senha,$nome,$cpf,$nascimento,$celular,$sexo,$promocoes,$onde_conheceu) 
    {   

      
      $SqlBuscaClientes = sprintf("SELECT COUNT(*) AS contagem FROM ipi_clientes WHERE email = '%s' OR cpf = '%s'",
                                  $email, $cpf);
      $res_busca_clientes = mysql_query($SqlBuscaClientes);          

      $objClientes = mysql_fetch_object($res_busca_clientes);
      
      if($objClientes->contagem == 0) {
        //$SqlInsert = sprintf("INSERT INTO ipi_clientes (nome, email, senha, cpf, celular, indicador_email, indicador_recebeu_pontos) VALUES ('%s', '%s', MD5('%s'), '%s', '%s', '%s', 0)",
        //                     $nome, $email, $senha, $cpf, $celular, texto2bd($indicador_email));
        
        // Buscando o cliente que indicou...
        $SqlBuscaIndicacoes = "SELECT * FROM ipi_indicacoes WHERE email = '$email' LIMIT 1";
        $resBuscaIndicacoes = mysql_query($SqlBuscaIndicacoes);
        $objBuscaIndicacoes = mysql_fetch_object($resBuscaIndicacoes);
        
        if($objBuscaIndicacoes->cod_clientes_indicador > 0)
          $cod_clientes_indicador = $objBuscaIndicacoes->cod_clientes_indicador;
        
        $SqlInsert = sprintf("INSERT INTO ipi_clientes (nome, email, senha, cpf, nascimento, celular, cod_clientes_indicador, indicador_recebeu_pontos, origem_cliente, data_hora_cadastro, sexo,cod_onde_conheceu) VALUES ('%s', '%s', MD5('%s'), '%s', '%s', '%s', '%d', 0, 'NET', '%s', '%s',%d)",
                             $nome, $email, $senha, $cpf, data2bd($nascimento), $celular, $cod_clientes_indicador, date("Y-m-d H:i:s"), $sexo,$onde_conheceu);
    //echo "aki: ".$SqlInsert;
        if(mysql_query($SqlInsert)) {
          $codigo = mysql_insert_id();
          
          // Cadastrando o email no e-mail marketing
          $SqlInsertEmail = sprintf("INSERT INTO ine_emails_cadastro (email, ativo, recebimentos, cod_ligacao) VALUES ('%s', '%s', 0, '%s')",
                                    $email, ($promocoes == 1) ? 1 : 0, $codigo);
          
          if(mysql_query($SqlInsertEmail)) 
          {
            $_SESSION['ipi_cliente']['codigo'] = $codigo;
            $_SESSION['ipi_cliente']['nome'] = bd2texto($nome);
            $_SESSION['ipi_cliente']['email'] = bd2texto($email);
            $_SESSION['ipi_cliente']['cpf'] = $cpf;
            $_SESSION['ipi_cliente']['ultimo_acesso'] = '';
            $_SESSION['ipi_cliente']['autenticado'] = true;
          }
          else {
            return -1;
            //echo "<script>alert('Houve um erro de sistema ao cadastrar.');</script>";
          }
        }
      }
      else {
        return -2;
        //echo "<script>alert('O e-mail/cpf digitado já está cadastrado, se você esqueceu sua senha, acesso a página \"Esqueci minha senha\"');</script>";
      }
    return $codigo;
  }


  public function cadastrar_endereco($codigo,$apelido,$endereco,$numero,$complemento,$edificio,$bairro,$cep,$cidade,$estado,$telefone_1,$telefone_2,$ponto_referencia)
  {

    $SqlInsertEnd = sprintf("INSERT INTO ipi_enderecos (apelido, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2, referencia_cliente, cod_clientes) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', %d)",
                            filtrar_caracteres_sql($apelido), filtrar_caracteres_sql($endereco), filtrar_caracteres_sql($numero), filtrar_caracteres_sql($complemento), filtrar_caracteres_sql($edificio), filtrar_caracteres_sql($bairro), filtrar_caracteres_sql($cidade), filtrar_caracteres_sql($estado), filtrar_caracteres_sql($cep), filtrar_caracteres_sql($telefone_1), filtrar_caracteres_sql($telefone_2), filtrar_caracteres_sql($ponto_referencia), $codigo);

    mysql_query($SqlInsertEnd);

  }

/*  public function autenticar()
  {


  }*/
  public function criar_sessao_login($cod_clientes)
  {
    $sql_procurar_cliente = "select * from ipi_clientes where cod_clientes = $cod_clientes";
    $res_procurar_cliente = mysql_query($sql_procurar_cliente);
    $obj_procurar_cliente = mysql_fetch_object($res_procurar_cliente);

    $_SESSION['ipi_cliente']['codigo'] = $obj_procurar_cliente->cod_clientes;
    $_SESSION['ipi_cliente']['nome'] = bd2texto($obj_procurar_cliente->nome);
    $_SESSION['ipi_cliente']['email'] = bd2texto($obj_procurar_cliente->email);
    $_SESSION['ipi_cliente']['cpf'] = $obj_procurar_cliente->cpf;

    //$objQuantidadePontos = executaBuscaSimples("SELECT SUM(pontos) AS soma_pontos FROM ipi_fidelidade_clientes WHERE cod_clientes = ".$obj_procura_email->cod_clientes." AND (data_validade > NOW() OR data_validade = '0000-00-00' OR data_validade IS NULL) ORDER BY data_hora_fidelidade DESC", $con);
    $SqlQuantidadePontos = "SELECT SUM(pontos) AS soma_pontos FROM ipi_fidelidade_clientes WHERE cod_clientes = ".$obj_procurar_cliente->cod_clientes." ORDER BY data_hora_fidelidade DESC";
    $resQuantidadePontos = mysql_query($SqlQuantidadePontos);
    $objQuantidadePontos = mysql_fetch_object($resQuantidadePontos);

    $soma = ($objQuantidadePontos->soma_pontos > 0) ? $objQuantidadePontos->soma_pontos : 0;
    $_SESSION['ipi_cliente']['pontos_fidelidade'] = $soma;
    
    if($obj_procurar_cliente->ultimo_acesso != '')
      $_SESSION['ipi_cliente']['ultimo_acesso'] = bd2datahora($obj_procurar_cliente->ultimo_acesso);
    else
      $_SESSION['ipi_cliente']['ultimo_acesso'] = '';
    
    $_SESSION['ipi_cliente']['autenticado'] = true;
    
    $SqlUpdateAcesso = 'UPDATE ipi_clientes SET ultimo_acesso = NOW() WHERE cod_clientes = '.$obj_procurar_cliente->cod_clientes;
    mysql_query($SqlUpdateAcesso);

  }

  /*
  NOTA=> A Data deve ser enviada no formato Y-m-d


  */
  public function vincular($cod_clientes,$cliente_id_na_rede,$acess_token,$email,$nome,$nascimento,$sexo,$foto)
  {

    $sql_inserir = "insert into ipi_clientes_redes_sociais(cod_clientes,nome_site,url_cliente_site,hash_acesso_cliente_site,rs_email,rs_nome,rs_nascimento,rs_foto,rs_sexo,data_vinculacao,status) values($cod_clientes,'Facebook','$cliente_id_na_rede','$acess_token','$email','$nome','$nascimento','foto','$sexo',NOW(),'ATIVO')";
    //echo "<br/>".$sql_inserir."</br>";
    $res_inserir = mysql_query($sql_inserir);
    if($res_inserir)
    {
      return true;

    }else
    {
      return false;
    }


    return true;

  }
    
  public function enviar_email_vinculacao($codigo,$facebook)
  {
    $sql_pegar_dados = "select * from ipi_clientes where cod_clientes = $codigo";
    $res_pegar_dados = mysql_query($sql_pegar_dados);
    $obj_pegar_dados = mysql_fetch_object($res_pegar_dados);


    // Envia um e-mail para o cliente informando da vinculação
    require_once 'ipi_email.php';
    $email_origem = EMAIL_PRINCIPAL;
    $email_destino = $obj_pegar_dados->email;

    $nomes = explode(" ", $obj_pegar_dados->nome);
    $assunto = "Fórmula Pizzaria - Vinculação com Facebook!";

    $texto = "<br /><br />Olá <strong>".$nomes[0]."</strong>,";
    $texto .= "<br/><br/>Vinculamos sua conta:";
    $texto .= "<br /><strong>Nome:</strong> ".$obj_pegar_dados->nome;
    $texto .= "<br /><strong>E-mail:</strong> ".$obj_pegar_dados->email;
    $texto .= "</br><br/>Com sua conta Facebook";
    $texto .= "<br /><strong>E-mail da conta:</strong> ".$facebook;
    $texto .= "<br /><br />Peça Já: <a href='https://www.formulapizzaria.com.br/pedidos'>www.formulapizzaria.com.br/pedidos</a>";
    $arr_aux = array();
    $arr_aux['cod_pedidos'] = 0;
    $arr_aux['cod_usuarios'] = 0;
    $arr_aux['cod_clientes'] = $codigo;
    $arr_aux['cod_pizzarias'] = 0;
    $arr_aux['tipo'] = 'VINCULAÇÃO';
    enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'neutro');
  }  

  public function enviar_email_cadastro_com_senha($codigo,$senha,$facebook = '')
  {
    $sql_pegar_dados = "select * from ipi_clientes where cod_clientes = $codigo";
    $res_pegar_dados = mysql_query($sql_pegar_dados);
    $obj_pegar_dados = mysql_fetch_object($res_pegar_dados);

    $sql_pegar_endereco = "select * from ipi_enderecos where apelido='Endereço Padrão' and cod_clientes=$codigo";
    $res_pegar_endereco = mysql_query($sql_pegar_endereco);
    $obj_pegar_endereco = mysql_fetch_object($res_pegar_endereco);

    //pesquisando a pizzaria pelo cep
    $cep_limpo = str_replace ( "-", "", str_replace('.', '', $obj_pegar_endereco->cep));

    $sql_cep = "SELECT COUNT(*) AS contagem FROM ipi_cep WHERE cep_inicial <= '$cep_limpo' AND cep_final >= '$cep_limpo'";
    //echo $sql_cep."<br/>";
    $res_cep = mysql_query($sql_cep);
    $ObjCep = mysql_fetch_object($res_cep);
    $contagem = $objCep->contagem; 

    $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= '$cep_limpo' AND cep_final >= '$cep_limpo' LIMIT 1";
    $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
    $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
    $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;

    $sql_buscar_pizzaria = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias = '$cod_pizzarias'";
    $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
    $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);


    // Envia um e-mail para o contato muzzarellas informando do cadastro
    require_once 'ipi_email.php';
    $email_origem = EMAIL_PRINCIPAL;
    $email_destino = EMAIL_PRINCIPAL;
    $assunto = "Fórmula Pizzaria - Novo Cadastro";

    $texto = "<br /><br />Novo cliente cadastrado no site:";
    $texto .= "<br /><strong>Nome:</strong> ".$obj_pegar_dados->nome;
    $texto .= "<br /><strong>E-mail:</strong> ".$obj_pegar_dados->email;
    $texto .= "<br /><strong>CPF:</strong> ".$obj_pegar_dados->cpf;
    if($facebook)
    {
      $texto .= "<br /><strong>Cliente cadastrou pelo Facebook</strong>";
    }
    $texto .= "<br /><br /><strong>Endereço:</strong> ".$obj_pegar_endereco->endereco.", ".$obj_pegar_endereco->numero."";
    $texto .= "<br /><strong>Complemento:</strong> ".$obj_pegar_endereco->complemento."";
    $texto .= "<br /><strong>Bairro:</strong> ".$obj_pegar_endereco->bairro."";
    $texto .= "<br /><strong>Cidade:</strong> ".$obj_pegar_endereco->cidade."";
    $texto .= "<br /><strong>Estado:</strong> ".$obj_pegar_endereco->estado."";
    $texto .= "<br /><strong>CEP:</strong> ".$obj_pegar_endereco->cep."";
    $texto .= "<br /><br /><br />Para mais detalhes acesse o sistema de administração: Cadastro / Clientes";
    

    $arr_aux = array();
    $arr_aux['cod_pedidos'] = 0;
    $arr_aux['cod_usuarios'] = 0;
    $arr_aux['cod_clientes'] = $codigo;
    $arr_aux['cod_pizzarias'] = 0;
    $arr_aux['tipo'] = 'CADASTRO_CLIENTE';

/*    if ($obj_buscar_pizzaria->emails_diretoria)
    {
     $email_destino .= ",".$obj_buscar_pizzaria->emails_diretoria;
      //$texto = "<br/><br/><br/>,".$obj_buscar_pizzaria->emails_diretoria;
    }*/

     $email_destino = EMAIL_PRINCIPAL;
      //$email_destino = 'filipegranato@internetsistemas.com.br';

    enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'neutro');

    $email_origem = EMAIL_PRINCIPAL;
    $email_destino = $obj_pegar_dados->email;
    //$email_destino = 'tesedsf.com';
    $nomes = explode(" ", $obj_pegar_dados->nome);
    $assunto = "Fórmula Pizzaria - Seu Cadastro!";

    $texto = "<br /><br />Olá <strong>".$nomes[0]."</strong>,";
    $texto .= "<br /><br />Seja bem-vindo a nossa pizzaria e bem vindo também ao sistema de pedido online";

    $texto .= "<br /><br /><strong>Seus dados de acesso ao sistema online:</strong>";
    $texto .= "<br /><strong>E-mail:</strong> ".$obj_pegar_dados->email;
    $texto .= "<br /><strong>Senha:</strong> $senha";
    if($facebook)
    {
     $texto .= "<br/><br /><strong>Você também pode acessar usando sua conta facebook:";
     $texto .= "<br /><strong>Email do facebook:</strong> $facebook";
    }

    $texto .= "<br /><br />Resumo do seu cadastro:";
    $texto .= "<br /><strong>Nome:</strong> ".$obj_pegar_dados->nome;
    $texto .= "<br /><strong>E-mail:</strong> ".$obj_pegar_dados->email;
    $texto .= "<br /><strong>CPF:</strong> ".$obj_pegar_dados->cpf;
    if($facebook=='')
    {
      $texto .= "<br /><br /><strong>Endereço:</strong> ".$obj_pegar_endereco->endereco.", ".$obj_pegar_endereco->numero;
      $texto .= "<br /><strong>Complemento:</strong> ".$obj_pegar_endereco->complemento;
      $texto .= "<br /><strong>Bairro:</strong> ".$obj_pegar_endereco->bairro;
      $texto .= "<br /><strong>Cidade:</strong> ".$obj_pegar_endereco->cidade;
      $texto .= "<br /><strong>Estado:</strong> ".$obj_pegar_endereco->estado;
      $texto .= "<br /><strong>CEP:</strong> ".$obj_pegar_endereco->cep;
    }

    //$texto .= "<br /><br />Conheça nosso sistema de fidelidade e ganhe pizzas <a href='https://www.osmuzzarellas.com.br/programa_de_fidelidade_muzza'>clique aqui</a>!";
    $texto .= "<br /><br />Acesse nosso site: <a href='#'>www.formulapizzaria.com.br</a>";

    enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'neutro');
    enviar_email($email_origem, EMAIL_PRINCIPAL, $assunto, $texto, $arr_aux, 'neutro');
    //enviar_email($email_origem, "filipegranato@internetsistemas.com.br", $assunto, $texto, $arr_aux, 'neutro');
    //echo "<br/>aki: ".$texto;
  }
}

?>
