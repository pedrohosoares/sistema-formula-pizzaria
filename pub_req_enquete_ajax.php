<?
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';

$acao = validaVarPost('acao');
$resposta = validaVarPost('cod_resposta');
$perg = validaVarPost('cod_pergunta');
$con = conectabd();
switch($acao)
{
  case 'verificar_filhas':
    $sql_verifica_filha = 'select perg.*,resp.* from ipi_enquete_perguntas perg inner join ipi_enquete_respostas_permitidas perm on perm.cod_enquete_perguntas = perg.cod_enquete_perguntas inner join ipi_enquete_respostas resp on resp.cod_enquete_respostas = perm.cod_enquete_respostas where perg.cod_enquete_perguntas_pai = '.$perg;//  $resposta
    
    //echo $sql_carrega_filha;
    $res_verifica_filha = mysql_query($sql_verifica_filha);
    if(mysql_num_rows($res_verifica_filha)>0)
    {
       $arrJson['status'] = 'sim';
    }
    else
    {
       $arrJson['status'] = 'nao';
    }
    echo json_encode($arrJson);
  break;
  case 'chamar_filhas':
    $sql_carrega_filha = 'select perg.* from ipi_enquete_perguntas perg inner join ipi_enquete_respostas_permitidas perm on perm.cod_enquete_perguntas = perg.cod_enquete_perguntas inner join ipi_enquete_respostas resp on resp.cod_enquete_respostas = perm.cod_enquete_respostas where resp.cod_enquete_respostas='.$resposta;//  $resposta
    $res_carrega_filha = mysql_query($sql_carrega_filha);
    $qtd_linhas = mysql_num_rows($res_carrega_filha);
    if($qtd_linhas>0)
    {
      echo '<table style="left-margin:30px">';
      while($obj_carrega_filha = mysql_fetch_object($res_carrega_filha)) 
      {
          echo '<tr><td id="pergunta_'.$obj_carrega_filha->cod_enquete_perguntas.'"><b>'.bd2texto($obj_carrega_filha->pergunta).'</b></td></tr>';
          
          echo '<input type="hidden" name="pergunta[]" value="'.$obj_carrega_filha->cod_enquete_perguntas.'">';
          
          $SqlBuscaRespostas = "SELECT * FROM ipi_enquete_respostas WHERE cod_enquete_perguntas = ".$obj_carrega_filha->cod_enquete_perguntas." ORDER BY  cod_enquete_respostas";
          $resBuscaRespostas = mysql_query($SqlBuscaRespostas);
          
          while($objBuscaRespostas = mysql_fetch_object($resBuscaRespostas)) {
            echo '<tr><td><input type="radio" style="background: none; border: none;" name="resposta_'.$obj_carrega_filha->cod_enquete_perguntas.'" class="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" value="'.$objBuscaRespostas->cod_enquete_respostas.'" onClick="verificarfilhas(this.value,'.$obj_carrega_filha->cod_enquete_perguntas.')" >&nbsp;<span id="legenda_'.$objBuscaRespostas->cod_enquete_respostas.'">'.bd2texto($objBuscaRespostas->resposta.'</span>');
            
            if (($objBuscaRespostas->justifica) && (!$objBuscaRespostas->justifica_opcional)) {
              echo '&nbsp;<input type="text" id="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" name="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" class="resposta_'.$obj_carrega_filha->cod_enquete_perguntas.'" size="45" maxsize="1000">&nbsp;<small>(justifique)</small>';
            }
            else if (($objBuscaRespostas->justifica) && ($objBuscaRespostas->justifica_opcional)) {
              echo '&nbsp;<input type="text" id="justifica_opcional_'.$objBuscaRespostas->cod_enquete_respostas.'" name="justifica_'.$objBuscaRespostas->cod_enquete_respostas.'" class="resposta_'.$obj_carrega_filha->cod_enquete_perguntas.'" size="45" maxsize="1000">&nbsp;<small>(comente)</small>';
            }
            
            echo '</td></tr>';
          }
          
          echo '<tr><td><div id="div_'.$obj_carrega_filha->cod_enquete_perguntas.'" name="div_'.$obj_carrega_filha->cod_enquete_perguntas.'" ></div></td></tr>';
          echo '<tr><td>&nbsp;</td></tr>';
         
        }
      
      echo '</table>';  
    }//echo '<tr><td>a</td></tr>';
  break; 
}
?>
