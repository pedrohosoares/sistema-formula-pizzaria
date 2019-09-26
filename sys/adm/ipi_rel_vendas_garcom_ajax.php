<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/pedido.php';


$acao = validaVarPost('acao');
switch($acao) 
{
  case 'carregar_colaboradores':
    $cod_pizzarias = validaVarPost('cod_pizzarias');
    $cod_colaboradores = validaVarPost('cod_colaboradores');


    if($cod_pizzarias !="")
    {
      $con = conectar_bd();

      $sql_colaboradores = "SELECT c.cod_colaboradores, c.nome FROM ipi_colaboradores c WHERE c.cod_pizzarias = '$cod_pizzarias' AND situacao = 'ATIVO'";
      $res_colaboradores = mysql_query($sql_colaboradores);
      echo '<option value="">Todos</option>';
      while($obj_colaboradores = mysql_fetch_object($res_colaboradores))
      {
        echo utf8_encode('<option value="'.$obj_colaboradores->cod_colaboradores.'" ');
        if ($obj_colaboradores->cod_colaboradores == $cod_colaboradores)
        {
          echo 'selected="selected"';
        }
        echo utf8_encode('>'.$obj_colaboradores->nome.'</option>');

      }

      desconectar_bd($con);
    }
  break;
}

?>