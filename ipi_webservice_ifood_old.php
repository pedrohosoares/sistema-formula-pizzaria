<?php
// require_once '../../config.php';
require_once 'ipi_req_carrinho_classe.php';
require_once 'sys/lib/php/formulario.php';
require_once ("sys/lib/php/nusoap/nusoap.php");

$usuario_webservice = WEBSERVICE_USUARIO;
$senha_webservice = WEBSERVICE_SENHA;

function testar_conexao($usuario, $senha)
{
    global  $usuario_webservice,$senha_webservice;
    if($usuario == $usuario_webservice && $senha == $senha_webservice)
    {
      $detalhes = "<testes>";
      $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='3' corte='0'>TESTE DE SISTEMA</linha>";
      $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>CONEXAO: OK</linha>";
      $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>DATA E HORA: " . date('d/m/Y H:i:s') . "</linha>";
      $detalhes .= "</testes>";
    }
    else
    {
      $detalhes = "<erro>";
      $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticacao</linha>";
      $detalhes .="</erro>";
    }

    return $detalhes;
}
function gravar_pedido_ifood($xml){

  $detalhes = "";
  
  //$xml_string = "teste.xml";
  $xml_pedido = simplexml_load_string($xml);


  if ($xml_pedido){

    $cliente = array();
    $pais = array();
    $filhos = array();


    foreach ($xml_pedido->children() as $pedido) {
      if (!empty($pedido->codPedido)){

        $email =  strtolower($pedido->email);

        $nome = addslashes(utf8_decode($pedido->nome));
        $tipo_logradouro = addslashes($pedido->tipoLogradouro);
        $endereco = addslashes(mb_strtolower(utf8_decode($pedido->logradouro)));
        $numero = $pedido->logradouroNum;
        $bairro = addslashes(utf8_decode($pedido->bairro));
        $cep = $pedido->cep;
        $cidade = addslashes(utf8_decode($pedido->cidade));
        $estado = $pedido->estado;
        $complemento =addslashes(utf8_decode($pedido->complemento));
        $referencia = addslashes(utf8_decode($pedido->referencia));
        $telefone = $pedido->telefones->telefone->numero;
        $ddd = $pedido->telefones->telefone->ddd;

        $obs_pedido = addslashes(utf8_decode($pedido->obsPedido));
        $forma_pg = utf8_decode($pedido->pagamentos->pagamento->codFormaPagto);

        $txt_valor_formas = abs(floatval($pedido->vlrPratos));
        $tipo_entrega ='Entrega';
        $desconto = floatval($pedido->vlrDesconto);
        //$horario_agendamento = validaVarPost('horario_agendamento'); // ''
        $troco = abs(floatval($pedido->vlrTroco));
        $frete = abs(floatval($pedido->vlrTaxa));

        $data_hora_pedido = $pedido->dataPedidoComanda;
        $pizzaria = $pedido->nomeFornecedor;

      }


      $contador_pais = 0;
      $contador_filhos = 0;

      if (count($pedido->item)){
        for($n = 0; $n < count($pedido->item); $n++){

          if ($pedido->item[$n]->codPai==""){

            //se for pizza
            if (strpos($pedido->item[$n]->descricaoCardapio, 'PIZZA') !== false    || strpos($pedido->item[$n]->descricaoCardapio, 'Fatias')!==false) {

              $aux = explode("T",$pedido->item[$n]->codProdutoPdv);             
              $aux = explode("F", $aux[1]);
              $cod_tamanhos = $aux[0];

              ##codProdutoPdv veio erra
              if ($cod_tamanhos==""){
                $detalhes = "<erro>Erro ao processar o pedido</erro>";
              }
             

    
              $pais[$contador_pais]['cod_tamanhos'] = $cod_tamanhos;
              $pais[$contador_pais]['tipo_produto']='PIZZA';
              $pais[$contador_pais]['qtde_pizzas']= floatval($pedido->item[$n]->quantidade);
              $pais[$contador_pais]['combo']=1;

      
            }

            //se for calzone
            elseif (strpos($pedido->item[$n]->descricaoCardapio, 'CALZONE') !== false || strpos($pedido->item[$n]->descricaoCardapio, 'Calzone')!==false) {

              $aux = explode("C",$pedido->item[$n]->codProdutoPdv);
              $codigo = $aux[1];

              $pais[$contador_pais]['codigo'] = $codigo;
              $pais[$contador_pais]['cod_tamanhos'] = 1;
              $pais[$contador_pais]['cod_bordas'] = 'N';
              $pais[$contador_pais]['preco']=floatval($pedido->item[$n]->vlrUnitBruto[0]);
              $pais[$contador_pais]['tipo_produto']='CALZONE';
              $pais[$contador_pais]['qtde_pizzas']= floatval($pedido->item[$n]->quantidade);
              //continue;
            }

            //se for bebida
            else{
              $aux = str_replace("BET", "",$pedido->item[$n]->codProdutoPdv);
              $cod_conteudos = $aux;

              $pais[$contador_pais]['cod_conteudos'] = $cod_conteudos;
              $pais[$contador_pais]['tipo_produto'] = 'BEBIDA';
              $pais[$contador_pais]['combo']=1;
            }

            $code = rand(100,10000);
            $pais[$contador_pais]['codFilho'] = $code;
            $pais[$contador_pais]['produto'] = strval(utf8_decode($pedido->item[$n]->descricaoCardapio[0]));
            $pais[$contador_pais]['qtde']=intval($pedido->item[$n+1]->quantidade);
            $pais[$contador_pais]['obs_fracao'] = strval(utf8_decode($pedido->item[$n]->obsItem));

            $contador_pais++;

          }
          else
          {

            if (strpos($pedido->item[$n]->descricaoCardapio, 'BORDA') !== false || strpos($pedido->item[$n]->descricaoCardapio, 'BORDA') !== false) {

              $cod_bordas = explode("BO",$pedido->item[$n]->codProdutoPdv);
              $cod_bordas = $cod_bordas[1];

              $filhos[$contador_filhos]['codigo']= $cod_bordas;
            }
            else{
              $filhos[$contador_filhos]['codigo']=intval($pedido->item[$n]->codProdutoPdv);
            }


            $filhos[$contador_filhos]['codPai']=$code;

            $filhos[$contador_filhos]['descricao']=strval(utf8_decode($pedido->item[$n]->descricaoCardapio[0]));
            $filhos[$contador_filhos]['preco'] = floatval($pedido->item[$n]->vlrUnitBruto[0]);

            $contador_filhos++;


          }

        }


      }
    }
    $con = conectabd();
    $pizzaria_aux = explode(' ', $pizzaria);

    $cod_pizzarias = "";
    foreach ($pizzaria_aux as $value) {
      $sql_pizzaria = "select cod_pizzarias, nome from ipi_pizzarias where nome like '%".utf8_decode($value)."%'";
      //echo $sql_pizzaria;
      $res_pizzaria = mysql_query($sql_pizzaria);
      if (mysql_num_rows($res_pizzaria)>0){
        $obj_pizzaria = mysql_fetch_object($res_pizzaria);
        $cod_pizzarias = $obj_pizzaria->cod_pizzarias;

      }
    }
    
    if ($cod_pizzarias==""){
      //default
      $cod_pizzarias = 1;
    }

//echo "<br>".$cod_pizzarias;

    $indice_pizzas=0;
    $indice_bebidas=0;
    $pizzas = array();
    $bebidas = array();

    for ($i=0; $i < count($pais) ; $i++) {
      if (count($filhos)>0){
        for ($j=0; $j < count($filhos); $j++) {
          if ($pais[$i]['codFilho']==$filhos[$j]['codPai'] ) {

            if ($pais[$i]['tipo_produto']=='PIZZA'){

              $pizzas[$pais[$i]['codFilho']]['produto']=$pais[$i]['produto'];
              $pizzas[$pais[$i]['codFilho']]['obs_fracao']=$pais[$i]['obs_fracao'];

              $pizzas[$pais[$i]['codFilho']]['qtde_fracao']=$indice_pizzas;

              $pizzas[$pais[$i]['codFilho']]['qtde_pizzas']=$pais[$i]['qtde_pizzas'];
              $pizzas[$pais[$i]['codFilho']]['cod_tamanhos']=$pais[$i]['cod_tamanhos'];
              //$pizzas[$pais[$i]['codFilho']]['combo']=1;

              if ($indice_pizzas==0){
                $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['cod_bordas']=$filhos[$j]['codigo'];
                $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['preco_borda']=$filhos[$j]['preco'];
                // $valor_pizza+=$pizzas[$pais[$i]['codFilho']][$indice_pizzas]['preco_borda'];

              }
              else{

                $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['cod_pizzas']=$filhos[$j]['codigo'];
                $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['num_fracao']=$indice_pizzas;
                $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['preco']=$filhos[$j]['preco'];
                $valor_pizza+=$pizzas[$pais[$i]['codFilho']][$indice_pizzas]['preco'];
                
              }

              $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['produto']=$filhos[$j]['descricao'];

              $pizzas[$pais[$i]['codFilho']]['valor']=$valor_pizza;
              $indice_pizzas++;


            }

            else{
              $bebidas[$pais[$i]['codFilho']]['produto']=$pais[$i]['produto'];
              $bebidas[$pais[$i]['codFilho']]['valor']+=$filhos[$j]['preco'];
              //$bebidas[$pais[$i]['codFilho']]['combo']=1;
              $bebidas[$pais[$i]['codFilho']][$indice_bebidas]['cod_bebidas']=$filhos[$j]['codigo'];

              $bebidas[$pais[$i]['codFilho']][$indice_bebidas]['cod_conteudos']=$pais[$i]['cod_conteudos'];

              $bebidas[$pais[$i]['codFilho']][$indice_bebidas]['produto']=$filhos[$j]['descricao'];
              $bebidas[$pais[$i]['codFilho']][$indice_bebidas]['preco']=$filhos[$j]['preco'];

              $bebidas[$pais[$i]['codFilho']]['qtde']=$pais[$i]['qtde'];
              $indice_bebidas++;
            }

          }
          elseif($pais[$i]['tipo_produto']=='CALZONE'){
              //indice 0 é usado para borda, e calzone não possui
              $indice_pizzas = 1;
              $pizzas[$pais[$i]['codFilho']]['produto']=$pais[$i]['produto'];

              $pizzas[$pais[$i]['codFilho']]['qtde_fracao']=1;

              $pizzas[$pais[$i]['codFilho']]['obs_fracao']=$pais[$i]['obs_fracao'];
              $pizzas[$pais[$i]['codFilho']]['cod_tamanhos']=$pais[$i]['cod_tamanhos'];
              $pizzas[$pais[$i]['codFilho']]['qtde_pizzas']=$pais[$i]['qtde_pizzas']; 
              $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['cod_bordas']= "N";
              $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['preco_borda']=0;
              $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['preco']=$pais[$i]['preco'];
              $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['num_fracao']=1;
              $pizzas[$pais[$i]['codFilho']][$indice_pizzas]['cod_pizzas']=$pais[$i]['codigo'];

              $indice_pizzas =0;
            }
          else{
            $indice_pizzas=0;
            $valor_pizza=0;
          }
        }
      }
      else{
              $pizzas[$pais[$i]['codFilho']]['produto']=$pais[$i]['produto'];
              $pizzas[$pais[$i]['codFilho']]['qtde_fracao']=1;
              $pizzas[$pais[$i]['codFilho']]['qtde_pizzas']=$pais[$i]['qtde_pizzas'];
              $pizzas[$pais[$i]['codFilho']]['cod_tamanhos']=$pais[$i]['cod_tamanhos'];

              $pizzas[$pais[$i]['codFilho']][1]['cod_bordas']= "N";
              $pizzas[$pais[$i]['codFilho']][1]['preco_borda']=0;
              $pizzas[$pais[$i]['codFilho']][1]['preco']=$pais[$i]['preco'];
              $pizzas[$pais[$i]['codFilho']][1]['num_fracao']=1;
              $pizzas[$pais[$i]['codFilho']][1]['cod_pizzas']=$pais[$i]['codigo'];
      }
    }


    //echo 'Pizzas: '.count($pizzas) .'<br/>Bebidas: '. count($bebidas);
    //echo '<pre>';

    ##pra usar como indice e pegar a fracao
    for ($i=0; $i < count($pais) ; $i++) {
      if ($pais[$i]['tipo_produto']=="PIZZA" || $pais[$i]['tipo_produto']=="CALZONE"){
        $pizza_aux[] =$pais[$i]['codFilho'];
      }
      else{
        $bebida_aux[] =$pais[$i]['codFilho'];
      }
    }
    
     //echo '<pre>';
    // print_r($pizza_aux);
    // print_r($bebida_aux);
    //print_r($bebidas);
      //print_r($pizzas);
      //print_r($pais);
     //print_r($filhos);
     //print_r($pedido);
    //print_r($xml_pedido);
//echo $detalhes;
    //die();

    $carrinho = new ipi_carrinho();

    $nascimento = '00/00/0000';
    $data_hora_pedido = str_replace("/", "-", $data_hora_pedido);
        $data_hora_pedido =date('Y-m-d H:i:s', strtotime($data_hora_pedido));

        $telefone_1 = '('.$ddd.')'.' '.$telefone;

        $cep = str_split($cep);
        $cep = $cep[0].$cep[1].$cep[2].$cep[3].$cep[4].'-'.$cep[5].$cep[6].$cep[7];
    $conexao = conectabd();
    $sql_verificar_email = "SELECT cod_clientes FROM ipi_clientes WHERE email='".$email."'";
    $res_verificar_email = mysql_query($sql_verificar_email);
    // echo $telefone_1.'asdas';
    // die($celular);

    #Novo cliente
    if (mysql_num_rows($res_verificar_email)==0){

      #cadastro de cliente
      $sql_novo_cliente = sprintf("INSERT INTO ipi_clientes (cod_onde_conheceu, nome, email, cpf, nascimento, celular, cod_clientes_indicador, indicador_recebeu_pontos, origem_cliente,observacao,data_hora_cadastro, sexo) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%d', 0, 'NET', '%s','%s', '%s')",
                            1, $nome, $email, $cpf, data2bd($nascimento), $celular, 0,'', date("Y-m-d H:i:s"), $sexo);
      // echo $sql_novo_cliente;
                $res_novo_cliente = mysql_query($sql_novo_cliente);
                $cod_clientes = mysql_insert_id();

                #cadastro de endereço
                $sql_endereco_cliente = sprintf("INSERT INTO ipi_enderecos (apelido, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2, cod_clientes, referencia_endereco,referencia_cliente) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s')",
                                'Endereço Padrão', ($endereco), ($numero), ($complemento), ($edificio), ($bairro), ($cidade), ($estado), ($cep), ($telefone_1), ($telefone_2), $cod_clientes,($referencia),($ref_cliente));
                $res_endereco_cliente = mysql_query($sql_endereco_cliente);
                $codigo_novo_endereco = mysql_insert_id();
                // echo "<br>1: ".$sql_endereco_cliente;

                //echo '<h2>Novo cliente cadastrado</h2>';

    }
    ##cliente com cadastro
    else{
      $obj_cliente = mysql_fetch_object($res_verificar_email);
      $cod_clientes = $obj_cliente->cod_clientes;
                #procurar se já possui endereço
                $sql_verificar_endereco = "SELECT * FROM ipi_enderecos e INNER JOIN ipi_clientes c ON c.cod_clientes = e.cod_clientes WHERE c.cod_clientes = $obj_cliente->cod_clientes AND e.endereco = '$endereco'";

                $res_verificar_endereco = mysql_query($sql_verificar_endereco);

                ## sem endereço
                if (mysql_num_rows($res_verificar_endereco)==0){

                    $sql_endereco_cliente = sprintf("INSERT INTO ipi_enderecos (apelido, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2, cod_clientes, referencia_endereco,referencia_cliente) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, '%s', '%s')",utf8_decode('Endereço Padrão'), ($endereco), ($numero), ($complemento), ($edificio), ($bairro), ($cidade), ($estado), ($cep), ($telefone_1), ($telefone_2), $obj_cliente->cod_clientes,($referencia),($ref_cliente));

                  $res_endereco_cliente = mysql_query($sql_endereco_cliente);
                  $codigo_novo_endereco = mysql_insert_id();
                  //echo "<h2>Cliente já existe e cadastrado novo endereço! </h2>";
                }

                ##com endereço
                else{
                          $obj_verificar_endereco = mysql_fetch_object($res_verificar_endereco);

                $sql_endereco_cliente = sprintf("UPDATE ipi_enderecos SET endereco = '%s', numero = '%s', complemento = '%s', edificio = '%s', bairro = '%s', cidade = '%s', estado = '%s', cep = '%s', telefone_1 = '%s', telefone_2 = '%s', cod_clientes = '%s', referencia_endereco = '%s' , referencia_cliente = '%s'  WHERE cod_enderecos = '%s'",
                                $endereco, $numero, $complemento, ($edificio), $bairro, $cidade, $estado, $cep, $telefone_1, $telefone_2,  $obj_cliente->cod_clientes, $referencia,($ref_cliente),  $obj_verificar_endereco->cod_enderecos);
        // echo $sql_endereco_cliente;
                $res_endereco_cliente = mysql_query($sql_endereco_cliente);
                //echo "<h2>Cliente já existe e endereço atualizado! </h2>";
                }


    }

    #######################################################

    //echo "$forma_pg<br>";
    // echo 'cod_pizzarias: '.$cod_pizzarias;
    // echo '<br/>obs_pedido: '.$obs_pedido;
    // echo '<br/>forma_pg: '.$forma_pg;
    $pagamentos_offline = array('CHE', 'RAM', 'DNREST', 'REC', 'RHIP', 'RDREST', 'VSREST', 'RED','MEREST', 'VIREST', 'DIN', 'VVREST', 'RSODEX', 'TRE', 'VALECA', 'VR_SMA');
    $pagamentos_online = array('AM', 'DNR', 'ELO', 'MC', 'VIS');

    if (in_array($forma_pg, $pagamentos_offline)){
          #dinheiro 
          if ($forma_pg=="DIN" || $forma_pg=="CHE"){
            
            $forma_pg = "IDINHEIRO";
          }

          #cartão de crédito
          elseif($forma_pg=="RAM" || $forma_pg=="DNREST" || $forma_pg=="REC" || $forma_pg=="RHIP" || $forma_pg=="RDREST" || $forma_pg=="VSREST"){
            
            $forma_pg = "ICREDITO";

          }

          #vales
          elseif($forma_pg=="VVREST" || $forma_pg=="RSODEX" || $forma_pg=="TRE" || $forma_pg=="VALECA" || $forma_pg=="VR_SMA"){

            $forma_pg = "IVALE";
            
          }

          #cartão de débito
          else{
            
            $forma_pg = "IDEBITO";

          }

    }
    elseif (in_array($forma_pg, $pagamentos_online)){
          $forma_pg = "IONLINE";
    }
    else{
      ##Sem código de pagamento válido
      $detalhes = "<erro>Erro ao processar o pedido</erro>";
    }

    
    $sql_verificar_forma_pg = "SELECT cod_formas_pg, forma_pg, cod_formas_pg_ifood FROM ipi_formas_pg WHERE cod_formas_pg_ifood = '$forma_pg'";

    $res_verificar_forma_pg = mysql_query($sql_verificar_forma_pg);

    if (mysql_num_rows($res_verificar_forma_pg)>0){
      $obj_forma_pg = mysql_fetch_object($res_verificar_forma_pg);
      $forma_pg = $obj_forma_pg->cod_formas_pg;
    }
    //echo $sql_verificar_forma_pg."<br>";
    //echo "<br>$forma_pg";
    //die();
    // echo $sql_verificar_forma_pg;
    // echo '<br/>forma_pg: '.$forma_pg;
    // echo '<br/>valor_formas: '. $txt_valor_formas;
    // echo '<br/>tipo_entrega: '.$tipo_entrega;
    // echo '<br/>desconto: '.$desconto;
    // //$horario_agendamento = validaVarPost('horario_agendamento'); // ''
    // echo '<br/>troco: '.$troco;
    // echo '<br/>frete: '.$frete;
    // echo '<br/>desconto: '.$desconto;
    // echo '<br/>cliente: '.$cod_clientes;

    $cliente['cod_clientes'] = $cod_clientes;
    $cliente['nome'] = $nome;
    $cliente['email'] = $email;
    $cliente['endereco'] = $tipo_logradouro.' '.$endereco;
    $cliente['numero'] = $numero;
    $cliente['bairro'] = $bairro;
    $cliente['cep'] = $cep;
    $cliente['cidade'] = $cidade;
    $cliente['estado'] = $estado;
    $cliente['complemento'] =$complemento;
    $cliente['referencia'] = $referencia;
    $cliente['telefone_1'] = $telefone_1;



        // echo '<br>s'.$cep;
        // die();



    if ($detalhes=="")
    {
        $num_pedido = $carrinho->finalizar_pedido_ifood($cliente, $pizzas, $pizza_aux, $bebidas, $bebida_aux, $cod_pizzarias, $obs_pedido, $cpf_nota_fiscal, $forma_pg, $txt_valor_formas, $tipo_entrega, $horario_agendamento, $troco, $desconto,$frete,$comissao_frete, $data_hora_pedido);
    //     $caixa->apagar_pedido();
    //     echo "<script>alert('Pedido: ".sprintf("%08d", $num_pedido)." efetuado com sucesso!')</script>";
            if ($num_pedido>0){
            $detalhes ="<detalhes>Pedido Processado</detalhes>";
          }
          else{
            $detalhes = "<erro>Erro ao processar o pedido</erro>";

          }
      }
     else{
          $detalhes = "<erro>Erro ao processar o pedido</erro>";
     }


       //die($detalhes);

  }
  ##Se não carregar o xml
  else{
    $detalhes = "<erro>Erro ao processar o pedido</erro>";

  }

    

  return $detalhes;





}

function dados_software($cod_pizzarias, $usuario, $senha)
{
    global  $usuario_webservice,$senha_webservice;
    if($usuario == $usuario_webservice && $senha == $senha_webservice)
    {
      $con_web = conectabd();

      $sql_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias = '$cod_pizzarias'";
      $res_pizzarias = mysql_query($sql_pizzarias);
      $obj_pizzarias = mysql_fetch_object($res_pizzarias);

      desconectabd($con_web);

      $detalhes = "<detalhes>";
      $detalhes .= "<detalhe chave='cliente' valor='" . NOME_SITE . "'/>";
      $detalhes .= "<detalhe chave='site' valor='" . HOST . "'/>";
      $detalhes .= "<detalhe chave='estabelecimento' valor='" . bd2texto($obj_pizzarias->nome) . "'/>";

      $detalhes .= "<detalhe chave='url_ftp' valor='ftp.internetsistemas.com.br' />";
      $detalhes .= "<detalhe chave='login_ftp' valor='formula' />";
      $detalhes .= "<detalhe chave='senha_ftp' valor='vmTAAHqV' />";

      // tempo de espera entre o proximo comando de impressão
      $detalhes .= "<detalhe chave='tempo_espera_impressao' valor='10'/>";

      // intervalo de consulta no webservice
      $detalhes .= "<detalhe chave='tempo_verificacao' valor='10'/>";

      $detalhes .= "<detalhe chave='ultima_versao' valor='1.0.0.0'/>";
      $detalhes .= "<detalhe chave='pacote_instalacao' valor='http://www.internetsistemas.com.br/download/ipizza.zip'/>";

      // Conta de SMTP utilizada para envio log
      $detalhes .= "<detalhe chave='smtp' valor='smtp.osmuzzarellas.com.br'/>";
      $detalhes .= "<detalhe chave='smtp_porta' valor='25'/>";
      $detalhes .= "<detalhe chave='smtp_usuario' valor='suporte@osmuzzarellas.com.br'/>";
      $detalhes .= "<detalhe chave='smtp_senha' valor='Sup@Muz98'/>";
      $detalhes .= "<detalhe chave='email_detino_arquivo_log' valor='contato@internetsistemas.com.br'/>";

      $detalhes .= "</detalhes>";
    }
    else
    {
      $detalhes = "<erro>";
      $detalhes .= "<linha formatacao='g' centralizado='1' quadrado='0' quebralinha='1' corte='0'>Erro de autenticacao</linha>";
      $detalhes .="</erro>";
    }

    return $detalhes;
}




$namespace = "urn:ipizza";

$server = new soap_server();

$server->configureWSDL("IPizza Impressao");
$server->wsdl->schemaTargetNamespace = $namespace;

$server->register('testar_conexao', array('usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Testa a conexão com o servidor.');
$server->register('gravar_pedido_ifood', array('xml_string' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Grava o pedido ifood');
$server->register('dados_software', array('cod_pizzarias' => 'xsd:int', 'usuario' => 'xsd:string', 'senha' => 'xsd:string'), array('return' => 'xsd:string'), $namespace, false, 'rpc', 'encoded', 'Retorna os dados de exibicao do software.');

$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

$server->service($POST_DATA);
//gravar_pedido_ifood('');

?>
