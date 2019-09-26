<?php

function debug($d) 
{
	echo "<pre style='background:#FFF;'>".print_r($d)."</pre>";
}
echo "<pre style='background:#FFF;'>";
//RESGATA PEDIDOS
require_once 'pedidos.class.php';
$pedidos = new Pedidos();
$pedidos->cod_pedidos = '185226';
$pedidos->getIpiPedidos();
if(!isset($pedidos->IpiPedidos[0]['valor'])){exit;}
$valorPedido = $pedidos->IpiPedidos[0]['valor'];
$valorEntrega = $pedidos->IpiPedidos[0]['valor_entrega'];
$valorTotal = $pedidos->IpiPedidos[0]['valor_total'];
$pedidos->cod_clientes = $pedidos->IpiPedidos[0]['cod_clientes'];
$pedidos->cod_pizzarias = $pedidos->IpiPedidos[0]['cod_pizzarias'];
//Seta campo para bebidas
$pedidos->campos = "cod_pedidos_bebidas,cod_bebidas_ipi_conteudos,preco,quantidade";
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
	$precoTotal = $precoBebida*$quantidadeBebida;
	//Seta id bebida
	$pedidos->cod_bebidas = $value['cod_pedidos_bebidas'];
	//Seta campos de busca
	$pedidos->campos = "*";
	//Pega nome da bebiad
	$pedidos->cod_bebidas = $value['cod_bebidas_ipi_conteudos'];
	$pedidos->getIpiBebidas();
	$nomeBebida = $pedidos->IpiBebidas[0]['bebida'];
	$codBebida = $pedidos->IpiBebidas[0]['cod_bebidas'];
	$itens[] = array(
		"numero_item"=>$num,
		"codigo_ncm"=> '22.02.10.00',
		"quantidade_comercial"=> $num,
		"quantidade_tributavel"=> $num,
		"cfop"=> "5102",
		"valor_unitario_tributavel"=> $precoTotal,
		"valor_unitario_comercial"=> $precoTotal,
		"valor_desconto"=>'0.00',
		"descricao"=> $nomeBebida,
		"codigo_produto"=> $codBebida,
		"icms_origem"=> '0',
		"icms_situacao_tributaria"=>'500',
		"unidade_comercial"=> 'UN',
		"unidade_tributavel"=> 'UN',
		"valor_bruto"=> $precoTotal,
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
	$num = $num + 1;
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

$precoTotalFracao = $precoTotalFracao+$valorBorda;
$precoTributos = number_format($precoTotalFracao*0.18,2);

$itens[] = array(
		"numero_item"=>$num,
		"codigo_ncm"=> '1902.20.00',
		"quantidade_comercial"=> $num,
		"quantidade_tributavel"=> $num,
		"cfop"=> "5102",
		"valor_unitario_tributavel"=> $precoTotalFracao,
		"valor_unitario_comercial"=> $precoTotalFracao,
		"valor_desconto"=>'0.00',
		"descricao"=>$descricaoPizza,
		"codigo_produto"=> $cod_tipo_pizza,
		"icms_origem"=> '0',
		"icms_situacao_tributaria"=>'102',
		"unidade_comercial"=> 'UN',
		"unidade_tributavel"=> 'UN',
		"valor_bruto"=> $precoTotalFracao,
		"valor_total_tributos"=>$precoTributos
	);
/*
$pedidos->getIpiPedidosCombos();
$pedidos->IpiPedidosCombos;
$pedidos->getIpiPedidosFormasPg();
$pedidos->IpiPedidosFormasPg;
$pedidos->getIpiPedidosInfo();
$pedidos->IpiPedidosInfo;
$pedidos->getIpiClientes();
$pedidos->IpiClientes;
$pedidos->getIpiPizzarias();
$pedidos->IpiPizzarias;
$pedidos->getIpiPedidosIngredientes();
$pedidos->IpiPedidosIngredientes;
$pedidos->getIpiPedidosSituacoes();
$pedidos->IpiPedidosSituacoes;
$pedidos->getIpiPedidosTaxas();
$pedidos->IpiPedidosTaxas;
$pedidos->getIpiIngredientes();
$pedidos->IpiIngredientes;
*/
//ARRAY DE ITENS DA NFCE
/*
$itens[] = array(
	"numero_item"=>$num,
	"codigo_ncm"=> '1902.20.00',
	"quantidade_comercial"=> $num,
	"quantidade_tributavel"=> $num,
	"cfop"=> "5102",
	"valor_unitario_tributavel"=> $this->doublePointer($preco_pizza),
	"valor_unitario_comercial"=> $this->doublePointer($preco_pizza),
	"valor_desconto"=>'0.00',
	"descricao"=> $pizza.' '.$tipo,
	"codigo_produto"=> $cod_pizzas,
	"icms_origem"=> '0',
	"icms_situacao_tributaria"=>'102',
	"unidade_comercial"=> 'UN',
	"unidade_tributavel"=> 'UN',
	"valor_bruto"=> $this->doublePointer($preco_pizza),
	"valor_total_tributos"=>$this->doublePointer($tributo)
);
*/
//FIM ARRAY DE ITENS

//GERA NFCE
/*
$nf = new Nfe();
$nf->ref = $nf->geraRef();
$nf->sessoes = $_SESSION['ipi_caixa'];
$nf->sessoesUsuario = $_SESSION['usuario'];
$nf->natureza_operacao = "PRESTAÇÂO DE SERVIÇOS";
$nf->data_emissao = date('Y-m-d').'T'.date('H:i:s');
$nf->tipo_documento = '1';
$nf->presenca_comprador =  ($_SESSION['ipi_caixa']['entregac'] == 'Balcão')?'4':'1';   
$nf->consumidor_final = "1";
$nf->finalidade_emissao = "1";
$cnpj = $nf->getCnpjEmailEmpresa();
$nf->cnpj_emitente = $cnpj->cnpj;
$nf->nome_destinatario = $_SESSION['ipi_caixa']['cliente']['nome'];
$nf->cpf_destinatario = $cpf_nota_fiscal;
$nf->informacoes_adicionais_contribuinte = "";//ex Retirada por conta do destinatário
$nf->valor_produtos = $_SESSION['ipi_caixa']['total_pedido'];
$nf->valor_desconto = $_SESSION['ipi_caixa'][''];
$nf->valor_total = $_SESSION['ipi_caixa']['total_pedido'];
$nf->forma_pagamento = "0";
$nf->icms_valor_total = "0";
$nf->modalidade_frete = "9";
$nf->metodo_pagamento = $nf->converteIdPagamentoParaIdNotaPagamento($forma_pg[0]);
$nf->valor_pagamento = $_SESSION['ipi_caixa']['total_pedido'];
$nf->nfce();
$nf->num_pedido = $num_pedido;
*/
//FIM NFCE
echo "</pre>";