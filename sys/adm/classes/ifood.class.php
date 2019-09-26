<?php
require 'conectamysql.class.php';
class ifood extends Conexao{
	public $client_id;
	public $client_secret;
	public $grant_type;
	public $username;
	public $password;
	public $access_tokem;
	public $token_type;

	public function conectIfood(){
		$dados = "client_id=".$this->client_id."&client_secret=".$this->client_secret."&grant_type=".$this->grant_type."&username=".$this->username."&password=".$this->password;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://pos-api.ifood.com.br/oauth/token");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
		$body = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      	curl_close($ch);
		$json = json_decode($body,true);
		$this->access_tokem = $json['access_token'];
		$this->token_type = $json['token_type'];
	}
}
