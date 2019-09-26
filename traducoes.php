<?php

function traduzir_tipo_entrega($str_tipo_entrega)
{
  switch ($str_tipo_entrega) 
  {
    case 'Balcão':
      $str_traduzida = "Balcão";
    break;
    case 'Balcão a Distancia':
      $str_traduzida = "Balcão a Distancia";
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
  if ($massa == "Branca Orgânica Fufi")
  {
    $nova_massa = "Branca";
  }
  elseif ($massa == "Branca Orgânica Original")
  {
    $nova_massa = "Branca";
  }
  elseif ($massa == "Multigrãos Orgânica Fufi")
  {
    $nova_massa = "Multigrãos";
  }
  elseif ($massa == "Multigrãos Orgânica Original")
  {
    $nova_massa = "Multigrãos";
  }
  return $nova_massa;
}  


function lozalizar_espessura($massa)
{
  if ($massa == "Branca Orgânica Fufi")
  {
    $espessura = "Fufi";
  }
  elseif ($massa == "Branca Orgânica Original")
  {
    $espessura = "Original";
  }
  elseif ($massa == "Multigrãos Orgânica Fufi")
  {
    $espessura = "Fufi";
  }
  elseif ($massa == "Multigrãos Orgânica Original")
  {
    $espessura = "Original";
  }
  return $espessura;
}    

?>