<?php

class Pedidos extends Conexao{
	//CODIGOS DE REFERÊNCIA
	public $cod_pedidos;
	public $cod_clientes;
	public $cod_pizzarias;
	public $cod_ingredientes;
	public $cod_bebidas;
	public $cod_pizzas;
	public $cod_tipo_pizza;
	public $cod_tamanhos;

	//FIELDS DA CONSULTA
	public $campos = "*";

	//RESULTADOS DAS BUSCAS
	public $IpiPedidos;
	public $IpiBebidas;
	public $IpiPedidosBebidas;
	public $IpiPedidosBordas;
	public $IpiPedidosCombos;
	public $IpiPedidosFormasPg;
	public $IpiPedidosInfo;
	public $IpiClientes;
	public $IpiPedidosPizzas;
	public $IpiTipoPizza;
	public $IpiPizzas;
	public $IpiPedidosSituacoes;
	public $IpiPedidosTaxas;
	public $IpiPedidosIngredientes;
	public $IpiIngredientes;


	public $IpiTamanhos;
	public $IpiPizzasIpiTamanhos;

	public $cx;

	public function __construct(){
		$this->cx = new Conexao;
	}

	public function estruturaDados(){
		$dados = array();
		while($d = mysqli_fetch_assoc($this->cx->return)){
			$dados[] = $d;
		}
		return $dados;
	}
	public function getIpiPedidos(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiPedidos = $this->estruturaDados();
	}
	public function getIpiPedidosBebidas(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_bebidas WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiPedidosBebidas = $this->estruturaDados();
	}
	public function getIpiBebidas(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_bebidas_ipi_conteudos bc ON (pb.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) WHERE p.cod_pedidos = '".$this->cod_pedidos."'";
		//$this->cx->query = "SELECT $this->campos FROM ipi_bebidas WHERE cod_bebidas='".$this->cod_bebidas."'";
		$this->cx->run();
		$this->IpiBebidas = $this->estruturaDados();
	}
	public function getIpiPedidosBordas(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_bordas WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiPedidosBordas = $this->estruturaDados();
	}
	public function getIpiPedidosCombos(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_combos WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiPedidosCombos = $this->estruturaDados();
	}
	public function getIpiPedidosFormasPg(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_formas_pg WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiPedidosFormasPg = $this->estruturaDados();
	}
	public function getIpiPedidosFracoes(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_fracoes WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiPedidosFracoes = $this->estruturaDados();
	}
	public function getIpiPedidosInfo(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_info WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiPedidosInfo = $this->estruturaDados();
	}
	public function getIpiClientes(){
		$this->cx->query = "SELECT $this->campos FROM ipi_clientes WHERE cod_clientes='".$this->cod_clientes."'";
		$this->cx->run();
		$this->IpiClientes = $this->estruturaDados();
	}
	public function getIpiPizzarias(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pizzarias WHERE cod_pizzarias='".$this->cod_pizzarias."'";
		$this->cx->run();
		$this->IpiPizzarias = $this->estruturaDados();
	}
	public function getIpiPedidosIngredientes(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_ingredientes WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiIngredientes = $this->estruturaDados();
	}



	// PIZZAS
	public function getIpiPedidosPizzas(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_pizzas WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiPedidosPizzas = $this->estruturaDados();
	}
	public function getIpiTipoPizza(){
		$this->cx->query = "SELECT $this->campos FROM ipi_tipo_pizza WHERE cod_tipo_pizza='".$this->cod_tipo_pizza."'";
		$this->cx->run();
		$this->IpiTipoPizza = $this->estruturaDados();
	}
	public function getIpiPizzas(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pizzas WHERE cod_pizzas='".$this->cod_pizzas."'";
		$this->cx->run();
		$this->IpiPizzas = $this->estruturaDados();
	}


	public function getIpiPedidosSituacoes(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_situacoes WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiPedidosSituacoes = $this->estruturaDados();
	}
	public function getIpiPedidosTaxas(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pedidos_taxas WHERE cod_pedidos='".$this->cod_pedidos."'";
		$this->cx->run();
		$this->IpiPedidosTaxas = $this->estruturaDados();
	}
	public function getIpiIngredientes(){
		$this->cx->query = "SELECT $this->campos FROM ipi_ingredientes WHERE cod_ingredientes='".$this->cod_ingredientes."'";
		$this->cx->run();
		$this->IpiIngredientes = $this->estruturaDados();
	}


	//TAMANHOS
	public function getIpiTamanhos(){
		$this->cx->query = "SELECT $this->campos FROM ipi_tamanhos WHERE cod_tamanhos='".$this->cod_tamanhos."'";
		$this->cx->run();
		$this->IpiTamanhos = $this->estruturaDados();
	}
	public function getIpiPizzasIpiTamanhos(){
		$this->cx->query = "SELECT $this->campos FROM ipi_pizzas_ipi_tamanhos WHERE cod_pizzarias='".$this->cod_pizzarias."' AND cod_pizzas='".$this->cod_pizzas."' AND cod_tamanhos='".$this->cod_tamanhos."'";
		$this->cx->run();
		$this->IpiPizzasIpiTamanhos = $this->estruturaDados();
	}
	public function closeConection(){
		$this->cx->close();
	}
	//Clientes
	
}