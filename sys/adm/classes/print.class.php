<?php
//Documentação https://www.printnode.com/en/docs/api/curl
require "conectamysql.class.php";
class Impressao extends Conexao{
	public $url = "https://api.printnode.com";
	public $description = "teste-impressao";
	public $key = "3c78edc8c3be91ba0cb3411ffe5fd046ceb18386";

	public function envia(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url."/whoami");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->key);
		$body = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		echo "<pre>";
		var_dump($body);
		echo "</pre>";
	}
}
$print = new Impressao();
$print->envia();

