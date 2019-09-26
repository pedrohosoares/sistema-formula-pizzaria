<?
function buscar_endereco_json($cepDest) 
{
  //cod_sedex – Neste parâmetro é definido o tipo de frete que será calculado. 
  //Sendo as opções Sedex Convencional 40010, Sedex 10 40215 e E-Sedex 81019. 

  //file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
  //$datafile = fopen("http://www.internetsistemas.com.br/completa_cep_is.php?cep=".$cepDest,"r");
  $datafile = fopen("http://sistema.formulapizzaria.com.br/formula/production/current/site/cep/production/completa_cep_is.php?cep=".$cepDest,"r");
  //$datafile = fopen("http://18.214.220.91/formula/production/current/site/cep/production/completa_cep_barathrum.php?cep=".$cepDest,"r");
  //$datafile = fopen("http://www.uol.com.br/","r");
  $data = fread($datafile, 100000000); 

  $arr_json = array();

  $arr_infos = explode("<br>",utf8_encode($data));
  //$arr_json["oqpego"] = "";
  foreach($arr_infos as $infoa)
  {
      $info = explode(": ",$infoa);
      switch($info[0])
      {
          case 'OK':
              $arr_json["status"] = "OK";
          break;
          case "Endereco":
              $arr_json["endereco"] = $info[1];
          break;
          case "Bairro":
              $arr_json["bairro"] = $info[1];
          break;
          case "Cidade":
              $arr_json["cidade"] = $info[1];
          break;
          case "UF":
              $arr_json["estado"] = $info[1];
          break;
      }
  } 
  if($arr_json["status"]!="OK")
  {
      $arr_json["status"] = "ERRO";
  }    
  echo json_encode($arr_json);

}

buscar_endereco_json($_POST['cep']);
//buscar_endereco_json('12236063');
?>
