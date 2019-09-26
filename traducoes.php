<?php

function traduzir_tipo_entrega($str_tipo_entrega)
{
  switch ($str_tipo_entrega) 
  {
    case 'Balc�o':
      $str_traduzida = "Balc�o";
    break;
    case 'Balc�o a Distancia':
      $str_traduzida = "Balc�o a Distancia";
    break;
    case 'Entrega':
      $str_traduzida = "Entrega";
    break;
    case 'Mesa':
      $str_traduzida = "Mesa";
    break;
    default:
      $str_traduzida = $str_tipo_entrega;
    break;
  }
  return $str_traduzida;
}

function traduzir_origem_pedido($str_origem_pedido)
{
  switch ($str_origem_pedido) 
  {
    case 'TEL':
      $str_traduzida = "TEL";
    break;
    case 'NET':
      $str_traduzida = "NET";
    break;
    default:
      $str_traduzida = $str_origem_pedido;
    break;
  }
  return $str_traduzida;
}

function renomear_massa($massa)
{
  if ($massa == "Branca Org�nica Fufi")
  {
    $nova_massa = "Branca";
  }
  elseif ($massa == "Branca Org�nica Original")
  {
    $nova_massa = "Branca";
  }
  elseif ($massa == "Multigr�os Org�nica Fufi")
  {
    $nova_massa = "Multigr�os";
  }
  elseif ($massa == "Multigr�os Org�nica Original")
  {
    $nova_massa = "Multigr�os";
  }
  return $nova_massa;
}  


function lozalizar_espessura($massa)
{
  if ($massa == "Branca Org�nica Fufi")
  {
    $espessura = "Fufi";
  }
  elseif ($massa == "Branca Org�nica Original")
  {
    $espessura = "Original";
  }
  elseif ($massa == "Multigr�os Org�nica Fufi")
  {
    $espessura = "Fufi";
  }
  elseif ($massa == "Multigr�os Org�nica Original")
  {
    $espessura = "Original";
  }
  return $espessura;
}    

?>