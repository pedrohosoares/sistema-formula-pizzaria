<?
header("access-control-allow-origin: *");

function buscar_endereco_json($cepDest, $content_type)
{
  //cod_sedex – Neste parâmetro é definido o tipo de frete que será calculado.
  //Sendo as opções Sedex Convencional 40010, Sedex 10 40215 e E-Sedex 81019.

  $cepDest = str_replace('-', '', str_replace ('.', '', $cepDest));
  $cepOrig = '12220770';
  $PesoTotal = '1';
  $ValorDeclarado = '1,00';
  $metodo = 'leitura';
  $cod_sedex = '40010';

  //$filename = "http://comercio.locaweb.com.br/correios/calcula_sedex.asp?cod_sedex=".$cod_sedex."&cepOrig=".$cepOrig."&cepDest=".$cepDest.
"&pesoDeclarado=".$PesoTotal."&vlrDeclarado=".$ValorDeclarado."&metodo=".$metodo;
  //$filename = "http://www.osmuzzarellas.com.br/completa_cep_locaweb.php?cep=".$cepDest;
  //$filename = "http://barathrum.internetsistemas.com.br/cep/production/completa_cep_barathrum.php?cep=".$cepDest;
  $filename = "http://sistema.formulapizzaria.com.br/formula/production/current/site/cep/production/completa_cep_barathrum.php?cep=".$cepDest;

  ini_set ('allow_url_fopen', 1);
  $file = file($filename);
  ini_set ('allow_url_fopen', 0);

  if ($content_type == 'json')
  {
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

    //echo '<pre>';
    //print_r($arrJson);
    //echo '</pre>';

    echo json_encode($arrJson);
  }
  else
  {
    echo $file[0];
  }
}

$content_type = ($_GET['format']) ? $_GET['format'] : 'html';

buscar_endereco_json($_GET['cep'], $content_type);
?>
