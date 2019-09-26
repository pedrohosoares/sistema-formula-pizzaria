<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';

$bloco = validaVarPost('bloco', '/[0-9]+/');

switch ($bloco)
{
    default:
    case 1:
        
        cabecalho('Login de sistema');
        
        ?>

        <form name="frmLogin" method="post" onsubmit="return validaRequeridos(this)">
        
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">
        
            <tr>
                <td class="legenda tdbl tdbt tdbr"><label class="requerido"
                    for="usuario">Usuário</label></td>
            </tr>
            <tr>
                <td class="tdbl tdbr"><input class="requerido" type="text"
                    name="usuario" id="usuario" maxlength="45" size="30"></td>
            </tr>
        
            <tr>
                <td class="legenda tdbl tdbr"><label class="requerido" for="senha">Senha</label></td>
            </tr>
            <tr>
                <td class="sep tdbl tdbr"><input class="requerido" type="password"
                    name="senha" id="senha" maxlength="45" size="30"></td>
            </tr>
        
            <tr>
                <td align="center" class="tdbl tdbb tdbr"><input class="botao"
                    type="submit" name="login" value="Acessar"></td>
            </tr>
        
        </table>
        
        <input type="hidden" name="bloco" value="2"></form>
        
        <br>
        <br>
        <p align="right" style="background-color: #e8e8e8; padding: 5px;">Área restrita,
        somente usuários cadastrados.</p>
        
        <?
        
        rodape();
        
        break;
    case 2:
        
        $usuario = validaVarPost('usuario', '/[a-z]+/');
        $senha = validaVarPost('senha');
        if (($usuario) && ($senha))
        {
            $con = conectabd();
            
            $sql_buscar_usuario = "SELECT * FROM nuc_usuarios WHERE usuario = '$usuario' AND senha = MD5('$senha') AND situacao='ATIVO'";
            $res_buscar_usuario = mysql_query($sql_buscar_usuario);
            $num_buscar_usuario = mysql_num_rows($res_buscar_usuario);
            if ($num_buscar_usuario > 0)
            {
                $obj_buscar_usuario = mysql_fetch_object($res_buscar_usuario);
                
                $sql_buscar_dados_usuario = sprintf("SELECT * FROM nuc_usuarios u INNER JOIN ipi_pizzarias_nuc_usuarios p ON (u.cod_usuarios = p.cod_usuarios) WHERE u.cod_usuarios = '%s'", $obj_buscar_usuario->cod_usuarios);
                $res_buscar_dados_usuario = mysql_query($sql_buscar_dados_usuario);
                $num_buscar_dados_usuario = mysql_num_rows($res_buscar_dados_usuario);
                
                if($num_buscar_dados_usuario > 0)
                {
                    $obj_buscar_dados_usuario = mysql_fetch_object($res_buscar_dados_usuario);
                    
                    // Carregando as pizzarias de acesso
                    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias_nuc_usuarios WHERE cod_usuarios = '" . $obj_buscar_dados_usuario->cod_usuarios . "'";
                    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
                    $num_buscar_pizzarias = mysql_num_rows($res_buscar_pizzarias);
                    
                    if($num_buscar_pizzarias > 0)
                    {
                        unset($_SESSION['usuario']);
                        
                        $_SESSION['usuario']['cod_pizzarias'] = array();
                        while ($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
                        {
                            $_SESSION['usuario']['cod_pizzarias'][] = $obj_buscar_pizzarias->cod_pizzarias;
                        }
                        
                        $_SESSION['usuario']['codigo'] = $obj_buscar_dados_usuario->cod_usuarios;
                        $_SESSION['usuario']['perfil'] = $obj_buscar_dados_usuario->cod_perfis;
                        $_SESSION['usuario']['usuario'] = $obj_buscar_dados_usuario->usuario;
                        $_SESSION['usuario']['nome'] = $obj_buscar_dados_usuario->nome;
                        $_SESSION['usuario']['email'] = $obj_buscar_dados_usuario->email;
                        
                        if ($obj_buscar_dados_usuario->ultimo_login != '')
                        {
                            $_SESSION['usuario']['ultimo_acesso'] = bd2datahora($obj_buscar_dados_usuario->ultimo_login);
                        }
                        else
                        {
                            $_SESSION['usuario']['ultimo_acesso'] = NULL;
                        }
                        
                        $_SESSION['usuario']['autenticado'] = true;
                        
                        $sql_atualizar_data = sprintf('UPDATE nuc_usuarios SET ultimo_login = NOW() WHERE cod_usuarios = %d', $obj_buscar_dados_usuario->cod_usuarios);
                        $res_atualizar_data = mysql_query($sql_atualizar_data);
                        
                        $sql_buscar_paginas = sprintf('SELECT * FROM nuc_paginas p INNER JOIN nuc_paginas_nuc_perfis pg ON (p.cod_paginas = pg.cod_paginas) WHERE pg.cod_perfis = %d', $obj_buscar_dados_usuario->cod_perfis);
                        $res_buscar_paginas = mysql_query($sql_buscar_paginas);
                        
                        while ($obj_buscar_paginas = mysql_fetch_object($res_buscar_paginas))
                        {
                            if ($obj_buscar_paginas->arquivo != '')
                            {
                                if ($obj_buscar_paginas->arquivo != '')
                                {
                                    $_SESSION['usuario']['paginas'][] = $obj_buscar_paginas->arquivo;
                                }
                                
                                if ($obj_buscar_paginas->arquivo_aux1 != '')
                                {
                                    $_SESSION['usuario']['paginas'][] = $obj_buscar_paginas->arquivo_aux1;
                                }
                                
                                if ($obj_buscar_paginas->arquivo_aux2 != '')
                                {
                                    $_SESSION['usuario']['paginas'][] = $obj_buscar_paginas->arquivo_aux2;
                                }
                                
                                if ($obj_buscar_paginas->arquivo_aux3 != '')
                                {
                                    $_SESSION['usuario']['paginas'][] = $obj_buscar_paginas->arquivo_aux3;
                                }
                            }
                        }
                        header('Location: index.php');
                    }
                
                }
                else
                {

                    ?>

                    <center>
                    <div class="mensagemErro">
                    <h2>Não há pizzarias associadas no seu perfil</h2>
                    <p>Erro, não há pizzarias associadas em seu perfil de acesso. Por favor, contacte o administrador do site para inclusão das pizzarias.</p>
                    <br />
                    <a href="nuc_login.php">Tentar novamente</a></div>
                    </center>
                    
                    <?
                    
                }
                
                
                desconectabd($con);
                
            }
            else
            {
                cabecalho('Login de sistema');
                
                ?>

                <center>
                <div class="mensagemErro">
                <h2>Acesso negado</h2>
                <p>Por favor, verifique se o nome de usuário e senha estão corretos.</p>
                <br />
                <a href="nuc_login.php">Tentar novamente</a></div>
                </center>

                
                <?
                
                rodape();
            }
        }
        else
        {
            cabecalho('Login de sistema');
            ?>

            <center>
            <div class="mensagemErro">
            <h2>Acesso negado</h2>
            <p>Por favor, verifique se o nome de usuário e senha estão corretos.</p>
            <br />
            <a href="nuc_login.php">Tentar novamente</a></div>
            </center>
            
            <?
            rodape();
        }
        break;
}
?>
