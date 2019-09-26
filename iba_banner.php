
<?
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';


$SqlBusca = "SELECT t.cod_tamanhos FROM iba_banners b INNER JOIN iba_tamanhos t ON (b.cod_tamanhos = t.cod_tamanhos)";
//$arr_banners = array(1 =>array(0),2 =>array(0),3 =>array(0),4 =>array(0),5 =>array(0),6 =>array(0));
$resBusca = mysql_query($SqlBusca);
while($objBusca = mysql_fetch_object($resBusca))
{
	$arr_banners[$objBusca->cod_tamanhos][] = 0;
}
function buscar_banner($tamanho,&$arr_banners) {
  $retorno = '';
  
  $con = conectabd();

  $lista_cods = implode(',',$arr_banners[$tamanho]);
  $dias_semanas = array('exibicao_dom', 'exibicao_seg', 'exibicao_ter', 'exibicao_qua', 'exibicao_qui', 'exibicao_sex', 'exibicao_sab');
  $exibicao = $dias_semanas[date('w')]; 
  
  	$SqlBusca = "SELECT * FROM iba_banners b INNER JOIN iba_tamanhos t ON (b.cod_tamanhos = t.cod_tamanhos) WHERE t.cod_tamanhos = $tamanho AND $exibicao = 1 AND b.cod_banners not in($lista_cods) ORDER BY RAND()";
  	$resBusca = mysql_query($SqlBusca);
  	//echo "SQL:".$SqlBusca."<br/>";
  	$num_linhas  = mysql_num_rows($resBusca);
  	if($num_linhas==0)
  	{
  			$SqlBusca = "SELECT * FROM iba_banners b INNER JOIN iba_tamanhos t ON (b.cod_tamanhos = t.cod_tamanhos) WHERE t.cod_tamanhos = $tamanho AND $exibicao = 1 AND b.cod_banners ORDER BY RAND()";
  			$resBusca = mysql_query($SqlBusca);
		  	$num_linhas  = mysql_num_rows($resBusca);
	  		if($num_linhas==0)
	  		{
	  			$objBusca = "";
	  		}
	  		else
	  		{
	  				$objBusca = mysql_fetch_object($resBusca);
	  		}
  	}else
  	{
  		$objBusca = mysql_fetch_object($resBusca);
  	}

// Retirado o contador de view do banner para otimizar o MySQL  
/*
  if($objBusca->cod_banners > 0){
    $SqlUpdate = "UPDATE iba_banners SET visualizacoes = visualizacoes + 1 WHERE cod_banners =".$objBusca->cod_banners;
    mysql_query($SqlUpdate);
  }
*/
  $info = pathinfo('upload/banners/'.$objBusca->imagem);
  	if($objBusca->cod_banners !="")
  	{
  		$arr_banners[$tamanho][] = $objBusca->cod_banners;
  	}
  if($info['extension'] == 'swf'){
    
    $retorno .= '<object width="100%" height="'.$objBusca->altura.'">';
    $retorno .= '<param name="movie" value="upload/banners/'.$objBusca->imagem.'">';
    $retorno .= '<param name="wmode" value="transparent">';
    $retorno .= '<embed wmode="transparent" src="upload/banners/'.$objBusca->imagem.'" width="100%" height="'.$objBusca->altura.'" wmode="transparent">';
    $retorno .= '</embed>';
    $retorno .= '</object>';
  }
  else{

    if ($tamanho == 1)
    {
          //   if($objBusca->link != '')
          //   {    

          //       $print = '<div class="superbanner" style="background-image: url(upload/banners/'.$objBusca->imagem.');  cursor:pointer;  "';

          //         $print.= "' onclick='location.href=\"".$objBusca->link."\"'></div>";
          //         echo $print;
          //   }
          // else
          // {
                            echo '<div class="superbanner" style="background-image: url(upload/banners/'.$objBusca->imagem.'); ">';
                            echo '</div>';
          // }
          echo '<div class="banner_btn" ><h1 class="h1_banner">Peça a sua agora mesmo</h1><a class="btn btn-primary" href="pedidos">Pedir Pizza</a></div>';
    }
    else
    {
        echo '<a href="'.$objBusca->link.'"><img src="upload/banners/'.$objBusca->imagem.'"></a>';
    }

      
  }
   
  desconectabd ($con);
  
  return $retorno;

}
?>
