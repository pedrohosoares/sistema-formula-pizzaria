<?
require_once 'bd.php';

// Cliente pediu para retirar a central de atendimento (cod_pizzarias = 4) da lista de lojas do site
$sql_buscar_franquias = 'select * from ipi_pizzarias where (situacao = "ATIVO" or situacao = "TESTE") AND cod_pizzarias NOT IN (4,5) order by situacao asc,cidade asc';

$res_buscar_franquias = mysql_query($sql_buscar_franquias);



$cont = 0;
while($obj_buscar_franquias = mysql_fetch_object($res_buscar_franquias))//'.$obj_buscar_franquias->foto_grande.'
{

  $novo = ( $obj_buscar_franquias->data_inauguracao ? ((strtotime($obj_buscar_franquias->data_inauguracao) + 60*60*24*30) >= strtotime(date('y-m-d'))) : false );


  $endereco_maps = urlencode($obj_buscar_franquias->lat.','.$obj_buscar_franquias->lon);
 
  echo '<div class="box loja">';

    echo '<img class="img_loja" src="'.($obj_buscar_franquias->foto_pequena ? 'upload/pizzarias/'.$obj_buscar_franquias->foto_pequena : 'img/pc/pizzaria_sem_foto.png').'" alt="'.$obj_buscar_franquias->nome.'"  />';
     


    echo '<h3>UNIDADE ' .$obj_buscar_franquias->cidade .' / '. $obj_buscar_franquias->estado. ' </h3>'. '<br/>';

    echo '<span>'.$obj_buscar_franquias->endereco . ', nº ' . $obj_buscar_franquias->numero . ', ' . $obj_buscar_franquias->bairro . '<br/>';

    echo 'Tel.: ' . $obj_buscar_franquias->telefone_1 . ($obj_buscar_franquias->telefone_2 ? ' / ' . $obj_buscar_franquias->telefone_2 : '');
    echo '</span>';

    if ($obj_buscar_franquias->horarios)
    {
      echo '<br/><br/><b>ATENDIMENTO:</b><br/>';
      echo nl2br($obj_buscar_franquias->horarios);
      
    } 

    // if ($obj_buscar_franquias->lat!=0)
    // {
    //   echo '<div align="right" style="font-weight:bold;font-style:italic;">';
    //   echo '<a href="https://maps.google.com/maps?q='.$endereco_maps.'" target="_blank" title="Localize-nos no Google Maps">Veja no Google Maps!</a>';
    //   echo '</div>'; 
    //   echo '<br/><br/>';
    // }
echo '</div>';

  $cont++;



  

}



?>

