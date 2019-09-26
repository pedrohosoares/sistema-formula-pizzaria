<?php
class Conexao{
	public $host;
	public $password;
	public $user;
	public $db;
	public static $instance;
	public $query;
	public $return;
	
	public function debug($d){
		echo "<pre style='background:#FFF;'>";
		var_dump($d);
		echo "</pre>";
	}

	public function __construct(){
		//PRODUCAO
		$this->host = '18.214.220.91';
		$this->password = '46302113';
		$this->user = 'pedrosoares';
		$this->db = "formula_pr";
		
		/*
		HOMOLOGAÇÃO
		$this->host = 'localhost';
		$this->password = 'root';
		$this->user = 'root';
		$this->db = "teste_formula_pizzaria";
		*/
		$this->conecta();
	}

	public function __destruct(){
		
	}

	public function conecta(){
		self::$instance = mysqli_connect($this->host,$this->user,$this->password,$this->db) or die('Falha na conexão');
	}

	public function run(){
		$this->return = self::$instance->query($this->query);
	}

	public function close(){
		mysqli_close(self::$instance);
	}

}