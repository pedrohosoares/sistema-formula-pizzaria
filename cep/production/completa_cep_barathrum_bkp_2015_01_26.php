<?
require_once("bd.php");

function buscar_endereco_json($cepDest) {

/*
  //cod_sedex – Neste parâmetro é definido o tipo de frete que será calculado. 
  //Sendo as opções Sedex Convencional 40010, Sedex 10 40215 e E-Sedex 81019. 

  // Codigo pra funcionar com gateway locaweb
  $cepDest = str_replace('-', '', str_replace ('.', '', $cepDest));
  $cepOrig = '12220770';
  $PesoTotal = '1';
  $ValorDeclarado = '1,00';
  $metodo = 'leitura';
  $cod_sedex = '40010';
  
  $filename = "http://comercio.locaweb.com.br/correios/calcula_sedex.asp?cod_sedex=".$cod_sedex."&cepOrig=".$cepOrig."&cepDest=".$cepDest."&pesoDeclarado=".$PesoTotal."&vlrDeclarado=".$ValorDeclarado."&metodo=".$metodo;

  ini_set ('allow_url_fopen', 1);
  $file = file($filename);
  ini_set ('allow_url_fopen', 0);
  echo $file[0];
  */


  $sql_cep = "SELECT e.nome endereco, b.nome bairro, c.nome cidade, e.id_uf estado FROM endereco e INNER JOIN cidade c ON (e.id_cidade = c.id_cidade) INNER JOIN bairro b ON (e.id_bairro = b.id_bairro) WHERE e.cep= '".$cepDest."'";
  $res_cep = mysql_query($sql_cep);
  $num_cep = mysql_num_rows($res_cep);
  $obj_cep = mysql_fetch_object($res_cep);

  $arrJson = array();
  if($num_cep == 0) 
  {
    // CEP não existe
    $arrJson['status'] = 'ERRO';
    $arrJson['mensagem'] = 'CEP NAO ENCONTRADO!';
  }
  else 
  {
    // CEP encontrado
    $arrJson['status'] = 'OK';
    $arrJson['mensagem'] = '';
    $arrJson['endereco'] = utf8_encode($obj_cep->endereco);
    $arrJson['bairro'] = utf8_encode($obj_cep->bairro);
    $arrJson['cidade'] = utf8_encode($obj_cep->cidade);
    $arrJson['estado'] = utf8_encode(strtoupper($obj_cep->estado));
  }
  /*
  $arrLinhas = explode( "<br>", $file[0]);
  
  for($l = 0; $l < count($arrLinhas); $l++) {
    $arrLinhas[$l] = preg_replace('/[a-zA-Z0-9_,\s]+:\s?/', '', $arrLinhas[$l]);
  }
  
  $arrJson = array();
  
  if(preg_match('/^OK$/', $arrLinhas[0])) {
    // O formato do CEP está OK!
    
    if($arrLinhas[count($arrLinhas) - 1] != '') {
      // CEP não existe
      
      $arrJson['status'] = 'ERRO';
      $arrJson['mensagem'] = $arrLinhas[count($arrLinhas) - 1];
    }
    else {
      // CEP encontrado
      $arrJson['status'] = 'OK';
      $arrJson['mensagem'] = '';
      $arrJson['endereco'] = utf8_encode($arrLinhas[1]);
      $arrJson['bairro'] = utf8_encode($arrLinhas[2]);
      $arrJson['cidade'] = utf8_encode($arrLinhas[3]);
      $arrJson['estado'] = utf8_encode(strtoupper($arrLinhas[4]));
    }
  }
  else {
    $arrJson['status'] = 'ERRO';
    $arrJson['mensagem'] = 'Formato de CEP inválido.';
  }
  echo '<pre>';
  print_r($arrJson);
  echo '</pre>';
  */
  
  echo json_encode($arrJson);

}

$conexao = conectar_bd();
buscar_endereco_json($_GET['cep']);
//buscar_endereco_json('12236063');
?>