<?php
//Loga um evento
function grava_log($mensagem, $nivel = 'INFO', $mysql_con = '') {
  if(!$mysql_con)
    $con = conectabd();
  
  $SqlLog = sprintf("INSERT INTO log (data_hora, nivel, mensagem, cod_usuarios, cod_grupos) VALUES (NOW(), '%s', '%s', %d, %d)", 
                    $nivel, $mensagem, $_SESSION['usuario']['codigo'], $_SESSION['usuario']['perfil']);
  
  mysql_query($SqlLog);                  
  
  if(!$mysql_con)
    desconectabd($con);
}
?>