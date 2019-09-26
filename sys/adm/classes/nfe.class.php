<?php
/*
CLASSE FEITA PARA GERAÇÃO, CONSULTA E EXCLUSÃO DA NOTA FISCAL
É CHAMADA NOS ARQUIVOS
ipi_caixa.php na linha 1761
*/
class Nfe{

  //DADOS DO SERVIDOR
  public $server = "http://producao.acrasnfe.acras.com.br";
  //Homologação serve
  //public $server = "http://homologacao.acrasnfe.acras.com.br";
  public $link = "https://api.focusnfe.com.br";
  //Homologação token
  //public $token = "tOJUiZsTjUJdeNolMVi3G9sGUtq9sTzF";
  //Producao Token
  public $token = "hXxP9olkLB7nq362pTz8hl1BeJXYCqg8";
  public $password = "";
  //Homologação login
  //public $login = "tOJUiZsTjUJdeNolMVi3G9sGUtq9sTzF";
  //Producao Login
  public $login = "hXxP9olkLB7nq362pTz8hl1BeJXYCqg8";
  public $ref;
  public $nota;
  //Pega numero do pedido em ipi_caixa.php na linha 1785
  public $num_pedido;
  public $sessoes;
  public $sessoesUsuario;
  public $caminho = "notas_fiscais";
  public $pasta;
  public $justificativa;



  //DADOS DA NOTA
  public $natureza_operacao;
  public $forma_pagamento;
  public $metodo_pagamento;
  public $data_emissao;
  public $tipo_documento;
  public $finalidade_emissao;
  public $cnpj_emitente;
  public $inscricao_estadual_emitente;
  public $nome_destinatario;
  public $cnpj_destinatario;
  public $inscricao_estadual_destinatario;
  public $logradouro_destinatario;
  public $numero_destinatario;
  public $bairro_destinatario;
  public $municipio_destinatario;
  public $uf_destinatario;
  public $pais_destinatario;
  public $cep_destinatario;
  public $icms_base_calculo;
  public $icms_valor_total;
  public $icms_base_calculo_st;
  public $icms_valor_total_st;
  public $icms_modalidade_base_calculo;
  public $icms_valor;
  public $valor_frete;
  public $valor_seguro;
  public $valor_total;
  public $valor_produtos;
  public $valor_ipi;
  public $modalidade_frete;
  public $informacoes_adicionais_contribuinte;
  public $nome_transportador;
  public $cnpj_transportador;
  public $endereco_transportador;
  public $municipio_transportador;
  public $uf_transportador;
  public $inscricao_estadual_transportador;
  public $itens = array();
  public $nome_emitente;
  public $indicador_inscricao_estadual_destinatario;
  public $data_entrada_saida;
  public $logradouro_emitente;
  public $nome_fantasia_emitente;
  public $numero_emitente;
  public $bairro_emitente;
  public $municipio_emitente;
  public $uf_emitente;
  public $cep_emitente;
  public $cpf_destinatario;
  public $telefone_destinatario;




  //ITENS
  public $numero_item;
  public $codigo_produto;
  public $descricao;
  public $cfop;
  public $unidade_comercial;
  public $quantidade_comercial;
  public $valor_unitario_comercial;
  public $valor_unitario_tributavel;
  public $unidade_tributavel;
  public $codigo_ncm;
  public $quantidade_tributavel;
  public $valor_bruto;
  public $icms_situacao_tributaria;
  public $icms_origem;
  public $pis_situacao_tributaria;
  public $cofins_situacao_tributaria;
  public $ipi_situacao_tributaria;
  public $ipi_codigo_enquadramento_legal;

  #VOLUME
  public $quantidade;
  public $especie;
  public $peso_bruto;
  public $peso_liquido;

  #DUPLICATAS
  public $valor;
  #FIM NFE

   #INICIO NFSE E NFCE
  public $incentivador_cultural;
  public $optante_simples_nacional;
  public $status;

    #PRESTADOR
  public $cnpj;
  public $inscricao_municipal;

    #SERVIÇO
  public $aliquota;
  public $base_calculo;
  public $discriminacao;
  public $iss_retido;
  public $item_lista_servico;
  public $valor_iss;
  public $valor_liquido;
  public $valor_servicos;

    #TOMADOR
  public $cpf;
  public $razao_social;
  public $email;

  #ENDERECO TOMADOR
  public $bairro;
  public $cep;
  public $codigo_municipio;
  public $logradouro;
  public $numero;
  public $uf;
  #FIM NFSE E NFCE


  #RECUPERA PEDIDO BANCO DE DADOS
  public $id_pedido;
  public $link_nota_fiscal;
  public $arquivo_nota_xml;
  public $arquivo_nota_pdf;


  public $descricaoPedido = "";


  public function debugThis(){
    echo "<pre style='background:#FFF;'>";
    var_dump($this);
    echo "</pre>";
  }
  public function debug($e){
    echo "<pre style='background:#FFF;'>";
    var_dump($e);
    echo "</pre>";
  }

  public function doublePointer($valor){
    $valor = str_replace(',', '.', $valor);
    return $valor;
  }

  public function substituiEspacos($dado){
    return preg_replace('/\ /', '-', $dado);
  }

  public static function geraRef(){
    /*
    $numbers = range(1, 10);
    shuffle($numbers);
    $numbers = implode('', $numbers);
    */
    return md5(uniqid(rand(), true));
  }
  public function geraData(){
    $numbers = date('YmdHis');
    return $numbers;
  }

  public function getCnpjEmailEmpresa(){
    $bd = new Conexao();
    $bd->query = "SELECT cnpj FROM ipi_pizzarias WHERE cod_pizzarias='".$this->sessoesUsuario['cod_pizzarias'][0]."'";
    $bd->run();
    $resultado = mysqli_fetch_object($bd->return);
    $bd->close();
    return $resultado;
  }

  
  public function nfe(){
    $nfe = array (
      "natureza_operacao" => $this->natureza_operacao,
      "data_emissao" => $this->data_emissao,
      "data_entrada_saida" => $this->data_entrada_saida,
      "tipo_documento" => $this->tipo_documento,
      "finalidade_emissao" => $this->finalidade_emissao,
      "cnpj_emitente" => $this->cnpj_emitente,
      "nome_emitente" => $this->nome_emitente,
      "nome_fantasia_emitente" => $this->nome_fantasia_emitente,
      "logradouro_emitente" => $this->logradouro_emitente,
      "numero_emitente" => $this->numero_emitente,
      "bairro_emitente" => $this->bairro_emitente,
      "municipio_emitente" => $this->municipio_emitente,
      "uf_emitente" => $this->uf_emitente,
      "cep_emitente" => $this->cep_emitente,
      "inscricao_estadual_emitente" => $this->inscricao_estadual_emitente,
      "nome_destinatario" => $this->nome_destinatario,
      "cpf_destinatario" => $this->cpf_destinatario,
      "telefone_destinatario" => $this->telefone_destinatario,
      "logradouro_destinatario" => "Rua S\u00e3o Janu\u00e1rio",
      "numero_destinatario" => "99",
      "bairro_destinatario" => "Crespo",
      "municipio_destinatario" => "Manaus",
      "uf_destinatario" => "AM",
      "pais_destinatario" => "Brasil",
      "cep_destinatario" => "69073178",
      "valor_frete" => "0.0",
      "valor_seguro" => "0",
      "valor_total" => "47.23",
      "valor_produtos" => "47.23",
      "modalidade_frete" => "0",
      "items" => array(
        array(
          "numero_item" => "1",
          "codigo_produto" => "1232",
          "descricao" => "Cartu00f5es de Visita",
          "cfop" => "6923",
          "unidade_comercial" => "un",
          "quantidade_comercial" => "100",
          "valor_unitario_comercial" => "0.4723",
          "valor_unitario_tributavel" => "0.4723",
          "unidade_tributavel" => "un",
          "codigo_ncm" => "49111090",
          "quantidade_tributavel" => "100",
          "valor_bruto" => "47.23",
          "icms_situacao_tributaria" => "400",
          "icms_origem" => "0",
          "pis_situacao_tributaria" => "07",
          "cofins_situacao_tributaria" => "07"
        )
      ),
    );
    $this->ref = $this->geraRef();
    // Inicia o processo de envio das informações usando o cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->server."/nfe2/autorizar.json?ref=" . $this->ref . "&token=" . $this->token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($nfe));
    $body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      //as três linhas abaixo imprimem as informações retornadas pela API, aqui o seu sistema deverá
      //interpretar e lidar com o retorno
    //print($http_code."\n");
    //print($body."\n\n");
    curl_close($ch);
  }

  public function enviaNotaEmail(){
    $email = array (
      "emails" => array(
        $this->email
      )
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->server."/v2/nfce/" . $this->ref . "/email");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($email));
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$this->login:$this->password");
    $body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
  }

  public function consulta(){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->server."/v2/nfce/" . $this->ref);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array());
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$this->login:$this->password");
    $body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $body;
  }

  public function cancelar(){
    //Verifica se existe uma nota
    $c = new Conexao();
    $c->query = "SELECT arquivo_json FROM ipi_pedidos WHERE ref_nota_fiscal='".$this->ref."'";
    $c->run();
    if(mysqli_num_rows($c->return) >0){
    // Inicia o processo de envio das informações usando o cURL.
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->server . "/v2/nfce/" . $this->ref);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('justificativa'=>$this->justificativa)));
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($ch, CURLOPT_USERPWD, "$this->login:$this->password");
      $body = curl_exec($ch);
      $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $res = json_decode($body,true);
      $res['status_number'] = $result;
      $res = json_encode($res,true);
      $c->query = "UPDATE ipi_pedidos SET arquivo_json='".$res."' WHERE ref_nota_fiscal='".$this->ref."'";
      $c->run();
      $c->close();
      return $res;
    }
    return false;
  }

  public function nfse(){
    $nfse = array (
      "data_emissao" => $this->data_emissao,
      "incentivador_cultural" => $this->incentivador_cultural,
      "natureza_operacao" => $this->natureza_operacao,
      "optante_simples_nacional" => $this->optante_simples_nacional,
      "status" => $this->status,
      "prestador" => array (
        "cnpj" => $this->cnpj,
        "inscricao_municipal" => $this->inscricao_municipal,
        "codigo_municipio" => $this->codigo_municipio
      ),
      "servico" => array (
        "aliquota" => $this->aliquota,
        "base_calculo" => $this->base_calculo,
        "discriminacao" => $this->discriminacao,
        "iss_retido" => $this->iss_retido,
        "item_lista_servico" => $this->item_lista_servico,
        "valor_iss" => $this->valor_iss,
        "valor_liquido" => $this->valor_liquido,
        "valor_servicos" => $this->valor_servicos
      ),
      "tomador" => array (
        "cpf" => $this->cpf,
        "razao_social" => $this->razao_social,
        "endereco" => array (
          "bairro" => $this->bairro,
          "cep" => $this->cep,
          "codigo_municipio" => $this->codigo_municipio,
          "logradouro" => $this->logradouro,
          "numero" => $this->numero,
          "uf" => $this->uf
        ),
      ),
    );
    $ch = curl_init();
    $this->ref = $this->geraRef();
    curl_setopt($ch, CURLOPT_URL, $this->server . "/nfse" . "?ref=" . $this->ref . "&token=" . $this->token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST,           1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $nfse);
    $http_code = curl_exec($ch);
    $result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //as três linhas abaixo imprimem as informações retornadas pela API, aqui o seu sistema deverá
    //interpretar e lidar com o retorno
    curl_close($ch);  
  }

  public function verificaCpfVazio(){
    if($this->cpf_destinatario === '00000000000' or empty($this->cpf_destinatario) or !isset($this->cpf_destinatario) or $this->cpf_destinatario === '000.000.000-00'){
      $this->cpf_destinatario = '';
      $this->nome_destinatario = '';
    }
  }

  public function cnpjDeSaoPaulo(){
    if($this->cnpj_emitente == '07.399.894/0001-83' or $this->cnpj_emitente == "07399894000183"){
      return true;
    }
    return false;
  }

  public function nfce(){
    $this->verificaCpfVazio();
    /*
    $nfce = array (
      'cnpj_emitente' => $this->cnpj_emitente,//cnpj do emissor
      'data_emissao' => $this->data_emissao,//data tem que ser DATE('Y-m-d').'T'.date('H:i:s')
      'natureza_operacao' => $this->natureza_operacao,//DESCRIÇÃO DA OPERAÇÃO
      'tipo_documento' => $this->tipo_documento,//obrigatorio valor 1
      'presenca_comprador' => $this->presenca_comprador,//1 oresebcuak e 4 entrega domicilio
      'consumidor_final' => $this->consumidor_final,//obrigatorio valor 1
      'finalidade_emissao' => $this->finalidade_emissao,//obrigatorio 1
      'nome_destinatario' => $this->nome_destinatario,//nome cliente
      'cpf_destinatario' => $this->cpf_destinatario,//cpf cliente opcional
      'informacoes_adicionais_contribuinte' => $this->descricaoPedido,//add observacoes
      'valor_produtos' => $this->valor_produtos,//valor da soma dos produtos
      'valor_desconto' => (is_null($this->valor_desconto))?'0.00':$this->valor_desconto,//valor de desconto
      //'valor_total' => $this->valor_total,//valor total da soma dos produtos
      'icms_valor_total' => $this->icms_valor_total,
      'modalidade_frete' => $this->modalidade_frete,
      'items' => $this->itens,
      'formas_pagamento' => array (
        array (
          'forma_pagamento' => $this->metodo_pagamento,//Forma do recebimento. Valores possíveis:01: Dinheiro.02: Cheque.03: Cartão de Crédito.04: Cartão de Débito.05: Crédito Loja.10: Vale Alimentação.11: Vale Refeição.12: Vale Presente.13: Vale Combustível.99: Outros
          'valor_pagamento' => $this->valor_pagamento,//valor pago
        ),
      ),
    );
    */
    
    $nfce = array (
     "cnpj_emitente" => $this->cnpj_emitente,
         'nome_destinatario' => $this->nome_destinatario,//nome cliente
         'cpf_destinatario' => $this->cpf_destinatario,//cpf cliente opcional
         "data_emissao" => $this->data_emissao,
         "indicador_inscricao_estadual_destinatario" => "9",
         "modalidade_frete" => "9",//0-porconta do emitente 1- conta do destinatario 2 - conta de terceiros 9 - sem frete
         "local_destino" => "1",//1 – Operação interna; 2 – Operação interestadual; 3 – Operação com exterior
         "presenca_comprador" => "1",
         "natureza_operacao" => $this->natureza_operacao,
         "itens" => $this->itens,
         "formas_pagamento" => array(
          array(
           "forma_pagamento" => $this->metodo_pagamento,
           "valor_pagamento" => $this->valor_pagamento
         )
        ),
       );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->server . "/v2/nfce?token=".$this->token."&ref=" .  $this->ref);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($nfce));
    $body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $arr_body = json_decode($body, true);
    curl_close($ch);
    $arr_body['id_interno'] = $this->ref;
    $arr_body['status_number'] = $http_code;
    $this->nota = $arr_body;
    //Se a emissão da nota for um sucesso
    //Salva ID da requisicao da nota e json da mesma
    $this->cadastraLinkNotaFiscal();
    if($this->nota['status'] == 'autorizado' and $this->nota['mensagem_sefaz'] == 'Autorizado o uso da NF-e'){
     $this->enviaNotaEmail();
       //$this->criaPastaNotaFiscal();
     $this->downloadXml();
     return true;
   }else{
     return false;
   }
 }

 public function cadastraLinkNotaFiscal(){
   $nota = json_encode($this->nota,true);
   $c = new Conexao();
   $c->query = "UPDATE ipi_pedidos SET ref_nota_fiscal='".$this->ref."',arquivo_json='".$nota."' WHERE cod_pedidos='".$this->num_pedido."'";
   $c->run();
   $c->close();
 }

 public function criaPastaNotaFiscal(){
  $ano = date('Y');
  $mes = date('m');
  $dia = date('d');
  if(!file_exists($this->caminho)){
    mkdir($this->caminho);
  }
  if(!file_exists($this->caminho.'/'.$this->cnpj_emitente)){
    mkdir($this->caminho.'/'.$this->cnpj_emitente);
  }
  if(!file_exists($this->caminho.'/'.$this->cnpj_emitente.'/'.$ano)){
    mkdir($this->caminho.'/'.$this->cnpj_emitente.'/'.$ano);
  }
  if(!file_exists($this->caminho.'/'.$this->cnpj_emitente.'/'.$ano.'/'.$mes)){
    mkdir($this->caminho.'/'.$this->cnpj_emitente.'/'.$ano.'/'.$mes);
    $this->pasta = $this->caminho.'/'.$this->cnpj_emitente.'/'.$ano.'/'.$mes;
  }
  if(!file_exists($this->caminho.'/'.$this->cnpj_emitente.'/'.$ano.'/'.$mes.'/'.$dia)){
    mkdir($this->caminho.'/'.$this->cnpj_emitente.'/'.$ano.'/'.$mes.'/'.$dia);
    $this->pasta = $this->caminho.'/'.$this->cnpj_emitente.'/'.$ano.'/'.$mes.'/'.$dia;
  }else{
    $this->pasta = $this->caminho.'/'.$this->cnpj_emitente.'/'.$ano.'/'.$mes.'/'.$dia;
  }
}

public function downloadXml(){
  @file_put_contents($this->pasta."nota_ref_".$this->ref.".xml", fopen($this->link.$this->nota['caminho_xml_nota_fiscal'], 'r'));
}
public function updatePDF(){
  $this->query = "UPDATE ipi_pedidos SET arquivo_nota_pdf='".$this->$arquivo_nota_pdf."' WHERE cod_pedidos='".$this->id_pedido."'";
  $this->run();
}

public function pegaPedido(){
  $this->query = "UPDATE ipi_pedidos SET link_nota_fiscal='".$this->link_nota_fiscal."',arquivo_nota_xml='".$this->arquivo_nota_xml."' WHERE cod_pedidos='".$this->id_pedido."'";
  $this->run();
}


public function formaPagamento($meioPagamento){
    /*
    Forma do recebimento. 
    Valores possíveis:
    01: Dinheiro.
    02: Cheque.
    03: Cartão de Crédito.
    04: Cartão de Débito.
    05: Crédito Loja.
    10: Vale Alimentação.
    11: Vale Refeição.
    12: Vale Presente.
    13: Vale Combustível.
    99: Outros
    */
    if($meioPagamento == 03 or $meioPagamento == 3){

    }
  }

  public function converteIdPagamentoParaIdNotaPagamento($numero){


    /*
    NFE ONLINE Forma do recebimento. Valores possíveis:
      01: Dinheiro.
      02: Cheque.
      03: Cartão de Crédito.
      04: Cartão de Débito.
      05: Crédito Loja.
      10: Vale Alimentação.
      11: Vale Refeição.
      12: Vale Presente.
      13: Vale Combustível.
      99: Outros
    */


      $pagametosCadastradosSistema = array(
        '8'=>'Alelo Refeição',
        '10'=>'American',
        '1'=>'Dinheiro',
        '5'=>'Elo Crédito',
        '3'=>'Elo Débito',
        '2'=>'Master Crédito',
        '4'=>'Master Débito',
        '11'=>'Pag. Online Ifood',
        '6'=>'Visa Crédito',
        '7'=>'Visa Débito'
      );

      switch ($numero) {
        case '8':
        $numero = 10;
        break;
        case '10':
        $numero = 03;
        break;
        case '1':
        $numero = 01;
        break; 
        case '5':
        $numero = 03;
        break;
        case '3':
        $numero = 04;
        break;
        case '2':
        $numero = 03;
        break;
        case '4':
        $numero = 04;
        break;
        case '11':
        $numero = 99;
        break;
        case '6':
        $numero = 03;
        break;
        case '7':
        $numero = 04;
        break;
      }
      return $numero;
    }
  }