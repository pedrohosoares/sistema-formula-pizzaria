<?php

/**
 * Resultados das Enquetes (ajax).
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR               DESCRIÇÃO 
 * ======    ==========   ==============      =============================================================
 *
 * 1.0       18/04/2012   FilipeGranato        Criado.
 */

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$cod_enquetes = 1; // Forçado para a enquete mail

$acao = validaVarPost('acao');
$var2 = validaVarPost('var2');
$var3 = validaVarPost('var3');

switch($acao)
{
    //var2 é o cod da pergunta
    case 'chamar_perguntas_pai':
    
    $conexao = conectabd();
    $sql_respostas = 'select resp.* from ipi_enquete_respostas resp inner join ipi_enquete_perguntas per on per.cod_enquete_perguntas = resp.cod_enquete_perguntas where per.cod_enquete_perguntas='.$var2;
   // echo $sql_respostas;
    $res_respostas = mysql_query($sql_respostas);
    
  
    echo '<table id="respostas" class = "sep listaEdicao">';
    while ($obj_respostas = mysql_fetch_object($res_respostas))
    {
      $sql_permitidas = 'select perm.* from ipi_enquete_respostas_permitidas perm inner join ipi_enquete_perguntas perg on perg.cod_enquete_perguntas = perm.cod_enquete_perguntas where perg.cod_enquete_perguntas = '.$var3.' and perm.cod_enquete_respostas = '.$obj_respostas->cod_enquete_respostas;
      $res_permitidas = mysql_query($sql_permitidas);
     
      echo '<tr><td><input type="checkbox" name="check_respostas[]" id="check_respostas[]" ';
      while($obj_permitidas = mysql_fetch_object($res_permitidas))
      {
        echo 'aaa'.$obj_permitidas->cod_enquete_respostas.'aaa ';
        if($obj_permitidas->cod_enquete_respostas==$obj_respostas->cod_enquete_respostas)
        {
          echo 'checked';
        }
      }
        

      echo ' value="'.$obj_respostas->cod_enquete_respostas.'" />'.utf8_encode($obj_respostas->resposta).'</td></tr>';
      
    }  
    echo '</table>';
    break;
    case 'enviar_resposta':
    
    
    break;
}

?>
