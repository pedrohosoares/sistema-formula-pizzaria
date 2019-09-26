<?php
ini_set('display_errors', 'Off');
date_default_timezone_set('America/Sao_Paulo');
include 'classes/conectamysql.class.php';
include 'classes/nfe.class.php';
include 'classes/pedidos.class.php';
$pedidos = new Pedidos();
$value_codigo_pedido = $value;
$pedidos->cod_pedidos = $value_codigo_pedido;
$pedidos->num_pedido = $value_codigo_pedido;
$pedidos->getIpiPedidos();
//if(!isset($pedidos->IpiPedidos[0]['valor'])){exit;}
$valorPedido = $pedidos->IpiPedidos[0]['valor'];
$valorEntrega = $pedidos->IpiPedidos[0]['valor_entrega'];
$valorTotal = $pedidos->IpiPedidos[0]['valor_total'];
$pedidos->cod_clientes = $pedidos->IpiPedidos[0]['cod_clientes'];
$pedidos->cod_pizzarias = $pedidos->IpiPedidos[0]['cod_pizzarias'];
$pedidos->campos = "cnpj";
$pedidos->getIpiPizzarias();
$cnpj = $pedidos->IpiPizzarias[0]['cnpj'];
//Seta campo para bebidas e pizzarias
$pedidos->campos = "*";//"cod_pedidos_bebidas,cod_bebidas_ipi_conteudos,preco,quantidade";
$pedidos->getIpiPedidosBebidas();
//Bebidas
$pedidos->campos = "*";
//Numero do pedido
$num = 0;
//Array que ficara a descricao dos itens da nota
$itens = array();
foreach ($pedidos->IpiPedidosBebidas as $key => $value) {
	$num = $num+1;
	$precoBebida = $value['preco'];
	$quantidadeBebida = $value['quantidade'];
	$precoTotal = number_format($precoBebida*$quantidadeBebida,2);
	//Seta id bebida
	$pedidos->cod_bebidas = $value['cod_pedidos_bebidas'];
	//Seta campos de busca
	$pedidos->campos = "b.ncm,b.cest,b.cst_icms,b.cod_bebidas,pb.cod_bebidas_ipi_conteudos,pb.quantidade,b.bebida,c.conteudo,pb.preco as preco_bebida";
	//Pega nome da bebiad
	$pedidos->cod_bebidas = $value['cod_bebidas_ipi_conteudos'];
	$pedidos->getIpiBebidas();
	$nomeBebida = $pedidos->IpiBebidas[0]['bebida'];
	$codBebida = $pedidos->IpiBebidas[0]['cod_bebidas'];
	$ncm = $pedidos->IpiBebidas[0]['ncm'];
	$cest = $pedidos->IpiBebidas[0]['cest'];
	$icms_situacao = $pedidos->IpiBebidas[0]['cst_icms'];
	if(substr($cest, 0,1) == '3'){
		$cest="0".$cest;
	}
	$itens[] = array(
		"numero_item"=>$num,
		"codigo_ncm"=> $ncm,
		"quantidade_comercial"=> $num,
		"quantidade_tributavel"=> $num,
		"cfop"=> "5405",
		"cest"=>$cest,
		"valor_unitario_tributavel"=> $precoTotal,
		"valor_unitario_comercial"=> $precoTotal,
		"valor_desconto"=>'0.00',
		"descricao"=> $nomeBebida,//'NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL'
		"codigo_produto"=> $codBebida,
		"icms_origem"=> '0',
		"icms_situacao_tributaria"=>$icms_situacao,
		"unidade_comercial"=> 'L',
		"unidade_tributavel"=> 'L',
		"valor_bruto"=> number_format($precoTotal*$num,2),
		"valor_total_tributos"=>'0.00'
	);
}

$pedidos->campos = "cod_pedidos_pizzas,cod_tamanhos";
//Tamanhos
$pedidos->getIpiPedidosPizzas();
$pedidosPizzas = $pedidos->IpiPedidosPizzas;



$pedidos->cod_tamanhos = $pedidosPizzas[0]['cod_tamanhos'];
$pedidos->campos = "tamanho";
$pedidos->getIpiTamanhos();
$tamanhoPizza = $pedidos->IpiTamanhos;
$descricaoPizza = $tamanhoPizza[0]['tamanho'];



$pedidos->campos = "*";
$pedidos->getIpiPedidosFracoes();
$fracoes = $pedidos->IpiPedidosFracoes;
$numeroFracoes = count($fracoes);
foreach ($fracoes as $key => $value) {
	$numeroFracao = $value['fracao'];
	$obsFracao = $value['obs_fracao'];
	$precoFracao = $value['preco'];
	$precoTotalFracao = $precoFracao*$numeroFracao;
	$pedidos->cod_pizzas = $value['cod_pizzas'];
	//RECUPERA PIZZAS
	$pedidos->campos = "*";
	$pedidos->getIpiPizzas();
	$pizzas = $pedidos->IpiPizzas;
	foreach($pizzas as $key_pizza => $value_pizza){
		$cest = $value_pizza['cest'];
		$aliquota = $value_pizza['aliq_icms'];
		$ncm = $value_pizza['ncm'];
		$pedidos->cod_pizzas = $value_pizza['cod_pizzas'];
		$pizzaSabor = $value_pizza['pizza'];
		$cod_tipo_pizza = $value_pizza['cod_tipo_pizza'];
		$sabor_pizza = $value_pizza['tipo'];//Sabor pode ser doce ou salgado
		$pedidos->cod_tipo_pizza = $cod_tipo_pizza;//Mostra se é promocao ou outro
		$descricaoPizza .= ' Sabor: '.$pizzaSabor.' - '.$sabor_pizza;
	}
	$pedidos->campos = "preco";
	$pedidos->getIpiPizzasIpiTamanhos();
	$tamanhosPizza = $pedidos->IpiPizzasIpiTamanhos;
	$precoPizza = $tamanhosPizza[0]['preco'];
}

$pedidos->getIpiPedidosBordas();
$pedidos->IpiPedidosBordas;
$valorBorda = 0;
//Percorre VALOR BORDA
foreach($pedidos->IpiPedidosBordas as $key=>$value){
	$valorBorda+=$value['preco'];
}
$precoTotalTributos = 0;
$precoTotalFracao = (number_format(($precoTotalFracao+$valorBorda),2)-0.01);
$precoTributos = number_format($precoTotalFracao*0.18,2);
$precoTotalTributos +=$precoTributos;
$num = count($itens)+1;
$itens[] = array(
	//csfn 102
	//origem 0
	//pis e confins 49
	"numero_item"=>$num,
	"codigo_ncm"=> $ncm,
	"quantidade_comercial"=> 1,
	"quantidade_tributavel"=> 1,
	"cfop"=> "5102",
	"cest"=>$cest,
	"valor_unitario_tributavel"=> $precoTotalFracao,
	"valor_unitario_comercial"=> $precoTotalFracao,
	"valor_desconto"=>'0.00',
	"descricao"=>$descricaoPizza,'NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL'
	"codigo_produto"=> $cod_tipo_pizza,
	"icms_origem"=> '0',
	"icms_situacao_tributaria"=>'102',
	"unidade_comercial"=> 'UN',
	"unidade_tributavel"=> 'UN',
	"valor_bruto"=> $precoTotalFracao,
	"valor_total_tributos"=>number_format(($precoTotalFracao*1)*0.18,2)
);
$pedidos->campos = "*";
$pedidos->getIpiClientes();
//Fecha conexão
$pedidos->closeConection();
//GERA NFCE
//Data de emissão
$data = date('Y-m-d').'T'.date('H:i:s');
$nf = new Nfe();
$nf->num_pedido = $pedidos->num_pedido;
$nf->ref = $nf->geraRef();
$nf->itens = $itens;
$nf->natureza_operacao = "PRESTAÇÂO DE SERVIÇOS";
$nf->data_emissao = $data;
$nf->tipo_documento = '1';
$nf->presenca_comprador = ($pedidos->IpiPedidos[0]['tipo_entrega'] == 'Balcão')?'4':'1';   
$nf->consumidor_final = "1";
$nf->finalidade_emissao = "1";
$nf->cnpj_emitente = $cnpj;
$nf->nome_destinatario = $pedidos->IpiClientes[0]['nome'];
$nf->cpf_destinatario = $pedidos->IpiClientes[0]['cpf'];
$nf->informacoes_adicionais_contribuinte = "";//ex Retirada por conta do destinatário
$nf->valor_produtos = $valorTotal;
$nf->valor_desconto = $pedidos->IpiPedidos[0]['desconto'];
$nf->valor_total = $valorTotal;
$nf->forma_pagamento = "0";
$nf->icms_valor_total = $precoTotalTributos;
$nf->modalidade_frete = "0";
$nf->metodo_pagamento = '99';//$nf->converteIdPagamentoParaIdNotaPagamento($forma_pg[0]);
$nf->valor_pagamento = $valorTotal;
if($nf->cnpjDeSaoPaulo()){
	$resultNota = $nf->nfce();
	if($resultNota){
		/*
		#Chama Arquivo Para impressão de nota
		$data = array(                                                                                
			'cod_pedido' => $pedidos->num_pedido,                                                                      
			'ref' => $nf->nota['id_interno'],
			'link_cupom' => 'https://api.focusnfe.com.br'.$nf->nota['caminho_danfe'],                                                                       
			'link_nota' => 'https://api.focusnfe.com.br'.$nf->nota['qrcode_url'],  
			'link_xml' =>'https://api.focusnfe.com.br'.$nf->nota['caminho_xml_nota_fiscal'],
			'chave_nfe' =>$nf->nota['chave_nfe']                                                                    
		);                                                                   
		$ch = curl_init('http://sistema.formulapizzaria.com.br/classes/emitenfe.php');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                              
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                                                  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);    
		if(!empty($result)){
			$nf->arquivo_nota_pdf = $result;
			$nf->updatePDF();
		}                                                                 
		curl_close($ch);
		*/   
	}
}
