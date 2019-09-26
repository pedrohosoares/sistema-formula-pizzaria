<?php
/**
 * nuc_usuarios.php: Cadastro de Usuários
 * 
 * Índice: cod_usuarios
 * Tabela: nuc_usuarios
 */
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Usuários');

$acao = validaVarPost('acao');

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
//    $SqlDel1 = "DELETE FROM ipi_pizzarias_nuc_usuarios WHERE cod_usuarios IN ($indicesSql)";
//    $SqlDel2 = "DELETE FROM nuc_usuarios WHERE cod_usuarios IN ($indicesSql)";

  	$SqlDel1 = "UPDATE nuc_usuarios SET situacao = 'EXCLUIDO' WHERE cod_usuarios IN ($indicesSql)";
    $ResDel1 = mysql_query($SqlDel1);
//    $ResDel2 = mysql_query($SqlDel2);
    
    if ($ResDel1)
    {
      mensagemOk('Os usuários selecionados foram excluídos com sucesso!');

      $ip = $_SERVER["REMOTE_ADDR"];
      $SqlUpdateLog = sprintf('INSERT INTO nuc_usuarios_log (cod_usuarios_alterado,cod_usuarios_alterou,alteracao,ip_alteracao,data_hora_alteracao) (SELECT cod_usuarios,"%s","%s","%s",NOW() FROM nuc_usuarios where cod_usuarios in ('.$indicesSql.'))',$_SESSION['usuario']['codigo'],"Excluiu usuario do sys",$ip);
      $ResUpdateLog = mysql_query($SqlUpdateLog);
    }
    else
      mensagemErro('Erro ao excluir os usuários', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $cod_usuarios = validaVarPost('cod_usuarios');
    $cadastro_usuario      = addslashes(validaVarPost('cadastro_usuario'));
    $cadastro_senha        = addslashes(validaVarPost('cadastro_senha'));
    $nome         = addslashes(validaVarPost('nome'));
    $email        = addslashes(validaVarPost('email'));
    $cod_perfis   = validaVarPost('cod_perfis');
    $cod_pizzarias= validaVarPost('cod_pizzarias');
    $situacao     = validaVarPost('situacao');
    
    
    // Trava anti-usuário...
    if($cadastro_usuario == 'admin')
      $cod_perfis = 1;
    
    $con = conectabd();
    
    if ($cod_usuarios <= 0) 
    {

      $sql_verificar_usuarios = "SELECT * FROM nuc_usuarios WHERE usuario = '$cadastro_usuario'";
    	$res_verificar_usuarios = mysql_query($sql_verificar_usuarios);	
    	$num_verificar_usuarios = mysql_num_rows($res_verificar_usuarios);	

      if ($num_verificar_usuarios==0)
      {
          $sql_edicao = sprintf("INSERT INTO nuc_usuarios (usuario, senha, nome, email, cod_perfis, situacao) VALUES ('%s', MD5('%s'), '%s', '%s', %d, '%s')", $cadastro_usuario, $cadastro_senha, $nome, $email, $cod_perfis, $situacao);
          $res_edicao = mysql_query($sql_edicao);	
          $codigo_cadastro = mysql_insert_id();

          $ip = $_SERVER["REMOTE_ADDR"];
          $SqlUpdateLog = sprintf('INSERT INTO nuc_usuarios_log (cod_usuarios_alterado,cod_usuarios_alterou,alteracao,ip_alteracao,data_hora_alteracao) values("%s","%s","%s","%s",NOW())',$codigo_cadastro,$_SESSION['usuario']['codigo'],"Cadastrou usuario no sys",$ip);
          $ResUpdateLog = mysql_query($SqlUpdateLog);
}
      else
      {
          mensagemErro('Erro ao adicionar o usuário', 'Usuário ('.$cadastro_usuario.') já cadastrado no sistema!');
          $msg_erro = 1;
      }
    }
    else 
    {

      if($cadastro_senha!="")
      {
        $sql_edicao = sprintf("UPDATE nuc_usuarios SET usuario = '%s',senha = MD5('%s'), nome = '%s', email = '%s', cod_perfis = %d, situacao = '%s' WHERE cod_usuarios = %d", $cadastro_usuario,$cadastro_senha, $nome, $email, $cod_perfis, $situacao, $cod_usuarios);
        $res_edicao = mysql_query($sql_edicao);

        $codigo_cadastro = $cod_usuarios;
      }

      else
      {
        $sql_edicao = sprintf("UPDATE nuc_usuarios SET usuario = '%s', nome = '%s', email = '%s', cod_perfis = %d, situacao = '%s' WHERE cod_usuarios = %d", $cadastro_usuario, $nome, $email, $cod_perfis, $situacao, $cod_usuarios);
        $res_edicao = mysql_query($sql_edicao);

        $codigo_cadastro = $cod_usuarios;
      } 

      if (count($cod_pizzarias)>0)
        {
          $sql_del_cadastros_grupos = "DELETE FROM ipi_pizzarias_nuc_usuarios WHERE cod_usuarios=".$codigo_cadastro;
            $res_del_cadastros_grupos = mysql_query($sql_del_cadastros_grupos);
        }
    	
      $ip = $_SERVER["REMOTE_ADDR"];
      $SqlUpdateLog = sprintf('INSERT INTO nuc_usuarios_log (cod_usuarios_alterado,cod_usuarios_alterou,alteracao,ip_alteracao,data_hora_alteracao) values("%s","%s","%s","%s",NOW())',$codigo_cadastro,$_SESSION['usuario']['codigo'],"Alterou usuario no sys",$ip);
      $ResUpdateLog = mysql_query($SqlUpdateLog);
    }
	
	if ($codigo_cadastro>0)
  {
    	if (count($cod_pizzarias)>0)
    	{
			    for ($x=0; $x<count($cod_pizzarias); $x++)
			    {
				    $sql_edicao_cadastros = sprintf("INSERT INTO ipi_pizzarias_nuc_usuarios (cod_usuarios, cod_pizzarias) VALUES ('%s', '%s')", $codigo_cadastro, $cod_pizzarias[$x]);
			        $res_edicao_cadastros = mysql_query($sql_edicao_cadastros);
			    }
    	}	
	}
	

  if ($msg_erro != 1)
  {
    if( ($res_edicao_cadastros) || ($res_edicao) )
    {
      	mensagemOk('Usuário adicionado com êxito!');
    }	
    else
    {
        mensagemErro('Erro ao adicionar o usuário', 'Por favor, verifique se o usuário já não se encontra cadastrado.');
    }
  }	

  desconectabd($con);
  break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

<script>

function verificaCheckbox(form) {
  var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
   
  if(cInput > 0) {
    if (confirm('Deseja excluir os registros selecionados?')) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja excluir.');
     
    return false;
  }
}

function editar(cod) {
  var form = new Element('form', {
    'action': '<? echo $_SERVER['PHP_SELF'] ?>',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': 'cod_usuarios',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.cod_usuarios.value > 0) {
    <? if ($acao == '') echo 'tabs.irpara(1);'; ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.cod_usuarios.value = '';
      document.frmIncluir.cadastro_usuario.value = '';
      document.frmIncluir.cadastro_senha.value = '';
      document.frmIncluir.nome.value = '';
      document.frmIncluir.email.value = '';
      document.frmIncluir.cod_perfis.value = '';
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
      
      document.frmIncluir.cadastro_senha.disabled = '';
      document.getElementById('lbl_cadastro_senha').addClass('requerido');
      document.frmIncluir.cadastro_senha.addClass('requerido');
    }
  });
});

</script>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a href="javascript:;">Editar</a></li>
       <li><a href="javascript:;">Incluir</a></li>
    </ul>
  </div>
    
  <!-- Tab Editar -->
  <div class="painelTab">
    <table><tr>
  
    <!-- Conteúdo -->
    <td class="conteudo">
    
      <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">
    
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
          <tr>
            <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
              <td align="center">Usuário</td>
              <td align="center">Nome</td>
              <td align="center">Perfil de Acesso</td>
              <td align="center">Último Login</td>
              <td align="center">Situação</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          $SqlBuscaUsuarios = "SELECT * FROM nuc_usuarios u LEFT JOIN nuc_perfis g ON (u.cod_perfis = g.cod_perfis) WHERE u.situacao!='EXCLUIDO' ORDER BY u.usuario";
          $resBuscaUsuarios = mysql_query($SqlBuscaUsuarios);
          
          while ($objBuscaUsuarios = mysql_fetch_object($resBuscaUsuarios)) {
            echo '<tr>';
            
            if($objBuscaUsuarios->usuario == 'admin')
              echo '<td align="center"><input type="checkbox" disabled="disabled" class="marcar excluir" name="excluir[]" value="'.$objBuscaUsuarios->cod_usuarios.'"></td>';
            else
              echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaUsuarios->cod_usuarios.'"></td>';
            
            
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaUsuarios->cod_usuarios.')">'.stripslashes($objBuscaUsuarios->usuario).'</a></td>';
            echo '<td align="center">'.stripslashes($objBuscaUsuarios->nome).'</td>';
            
            if($objBuscaUsuarios->perfil != '')
              echo '<td align="center">'.stripslashes($objBuscaUsuarios->perfil).'</td>';
            else
              echo '<td align="center">Sem Perfil</td>';
            
            if ($objBuscaUsuarios->ultimo_login != '')
              echo '<td align="center">'.bd2datahora($objBuscaUsuarios->ultimo_login).'</td>';
            else
              echo '<td align="center">Nunca Logou</td>';

            echo '<td align="center">'.$objBuscaUsuarios->situacao.'</td>';
              
            echo '</tr>';
          }
          
          desconectabd($con);
          
          ?>
          
          </tbody>
        </table>
      
        <input type="hidden" name="acao" value="excluir">
      </form>
    
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="nuc_perfis.php">Perfis de Acesso</a></li>
          <li><a href="nuc_paginas.php">Páginas e Menus</a></li>
        </ul>
      </div>
    </td>
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Editar -->
  
  
  
  <!-- Tab Incluir -->
  <div class="painelTab">
    <? 
      $cod_usuarios = validaVarPost('cod_usuarios', '/[0-9]+/');
      
      if($cod_usuarios > 0) {
        $objBusca = executaBuscaSimples("SELECT * FROM nuc_usuarios WHERE cod_usuarios = ".$cod_usuarios);
      } 
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr>
		<td class="legenda tdbl tdbt tdbr sep" colspan="2"><label class="requerido" for="cod_pizzarias">Pizzarias</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr" colspan="2">
        <?
    $conexao = conectabd();
    
    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao in ('ATIVO', 'TESTE') ORDER BY nome";
    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
    $num_buscar_pizzarias = mysql_num_rows($res_buscar_pizzarias);
    $metade = $num_buscar_pizzarias/2;
    $e = 0;
    echo '<table align="center" border="0"><tr><td>';
    while ($obj_buscar_pizzarias[$e] = mysql_fetch_object($res_buscar_pizzarias))
    {
        if ($objBusca)
        {
        	$arr_pizzarias = array();   
            
            if ($cod_usuarios)
            {
                $sql_buscar_cadastros = "SELECT * FROM ipi_pizzarias_nuc_usuarios WHERE cod_usuarios=$cod_usuarios";
                $res_buscar_cadastros = mysql_query($sql_buscar_cadastros);
                $num_buscar_cadastros = mysql_num_rows($res_buscar_cadastros);
                
                for ($a = 0; $a < $num_buscar_cadastros; $a++)
                {
                    $obj_buscar_cadastros[$a] = mysql_fetch_object($res_buscar_cadastros);
                    $arr_pizzarias[] = $obj_buscar_cadastros[$a]->cod_pizzarias;
                }
            }
        }
        
        echo '<br /><input type="checkbox" name="cod_pizzarias[]" class="noborder" align="absbottom" id="cod_pizzarias[]" value=' . $obj_buscar_pizzarias[$e]->cod_pizzarias . ' ';
        if (count($arr_pizzarias) > 0)
        {
        	if (in_array($obj_buscar_pizzarias[$e]->cod_pizzarias, $arr_pizzarias))
	        {
	        	echo 'checked="checked"';
	        }
        }
        echo '>' . $obj_buscar_pizzarias[$e]->nome.'  ';
        
        if ( $metade == ($e+1) )
        {
			echo "</td><td>";        	
        }
        
        $e +=1;
    }
    echo "</tr>";
    echo '</table>';
    desconectabd($conexao);
    ?>
		</td>
	</tr>

<?
/*
ATENÇÃO: Fiz uma alteração no nome dos campos do formulário abaixo USUARIO e SENHA, para CADASTRO_USUARIO e CADASTRO_SENHA, 
pagina causava erro no nuc_login.php, pois todas as telas do sys passam pelo nuc_login.php, só que quando você posta o formulário, 
ele tem os mesmos campos necessário pelo nuc_login para fazer o login e causava um erro, destruindo a sessão.
*/
?>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="cadastro_usuario">Usuário</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="cadastro_usuario" id="cadastro_usuario" maxlength="45" size="30" value="<? echo stripslashes($objBusca->usuario) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="cadastro_senha" id="lbl_cadastro_senha">Senha</label></td></tr>
    
    <? //if($objBusca->cod_usuarios > 0): ?>
<!--         <tr><td class="sep tdbl tdbr"><input type="text" name="cadastro_senha" id="cadastro_senha" maxlength="45" size="30" disabled="disabled"></td></tr> -->
    <? //else: ?>
        <tr><td class="sep tdbl tdbr"><input type="password" name="cadastro_senha" id="cadastro_senha" maxlength="45" size="30"></td></tr>
    <? //endif; ?>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="nome">Nome Completo</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="nome" id="nome" maxlength="45" size="30" value="<? echo stripslashes($objBusca->nome) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="nome">E-mail</label></td></tr>
    <tr><td class="sep tdbl tdbr"><input type="text" name="email" id="email" maxlength="45" size="30" value="<? echo stripslashes($objBusca->email) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="cod_perfis">Perfil de Acesso</label></td></tr>
    <tr>
      <td class="sep tdbl tdbr">
        <select name="cod_perfis" id="cod_perfis" class="requerido">
          <option></option>
          <?
            $con = conectabd();
            
            $SqlBuscaPerfis = 'SELECT * FROM nuc_perfis WHERE cod_perfis '.($_SESSION['usuario']['codigo']==1 ? "" : "NOT IN (1) ").' ORDER BY perfil';
            $resBuscaPerfis = mysql_query($SqlBuscaPerfis);
            
            while ($objBuscaPerfis = mysql_fetch_object($resBuscaPerfis)) {
              echo '<option value="'.$objBuscaPerfis->cod_perfis.'" ';
              
              if($objBuscaPerfis->cod_perfis == $objBusca->cod_perfis)
                echo 'SELECTED';
              
              echo '>'.$objBuscaPerfis->perfil.'</option>';
            }
            
            desconectabd($con);
          ?>
        </select>
      </td>
    </tr>


    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="situacao">Situação</label></td></tr>
    <tr>
      <td class="sep tdbl tdbr">
        <select class="requerido" name="situacao" id="situacao">
            <option value=""></option>
            <option value="ATIVO" <? if($objBusca->situacao == 'ATIVO') echo 'selected'; ?>> Ativo </option>
            <option value="INATIVO" <? if($objBusca->situacao == 'INATIVO') echo 'selected'; ?>> Inativo </option>
        </select>
      </td>
    </tr>


    
    <tr><td colspan="2" align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="cod_usuarios" value="<? echo $objBusca->cod_usuarios ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
