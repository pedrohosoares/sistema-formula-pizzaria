<?
header("access-control-allow-origin: *");
header('Access-Control-Allow-Headers: DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,X-CSRF-TOKEN');

require_once("bd.php");

function buscar_endereco_json($cepDest) {
  $sql_cep = "SELECT e.nome endereco, b.nome bairro, c.nome cidade, e.id_uf estado FROM endereco e INNER JOIN cidade c ON (e.id_cidade = c.id_cidade) INNER JOIN bairro b ON (e.id_bairro = b.id_bairro) WHERE e.cep= '".$cepDest."'";
  $res_cep = mysql_query($sql_cep);
  $num_cep = mysql_num_rows($res_cep);
  $obj_cep = mysql_fetch_object($res_cep);
  $resposta = "";

  $arrJson = array();
  if($num_cep == 0) 
  {
    // CEP não existe
    $arrJson['status'] = 'ERROR';
    $arrJson['message'] = 'NOT FOUND';
  }
  else 
  {
    // CEP encontrado
    $arrJson['status'] = 'OK';
    $arrJson['address'] = utf8_encode($obj_cep->endereco);
    $arrJson['neighborhood'] = utf8_encode($obj_cep->bairro);
    $arrJson['city'] = utf8_encode($obj_cep->cidade);
    $arrJson['state'] = utf8_encode(strtoupper($obj_cep->estado));
    $arrJson['country'] = 'Brasil';
  }
 
  header('Content-type: application/json; charset=utf-8'); 
  echo json_encode($arrJson);
}

$conexao = conectar_bd();
buscar_endereco_json($_GET['cep']);
?>
