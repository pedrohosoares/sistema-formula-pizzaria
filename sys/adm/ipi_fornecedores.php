<?php

/**
 * Cadastro de fornecedores.
 *
 * @version 1.0
 * @package ipizza
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       08/01/2009   Elias         Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Fornecedores');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_fornecedores';
$tabela = 'ipi_fornecedores';
$campo_ordenacao = 'nome_fantasia';
$campo_filtro_padrao = 'nome_fantasia';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        $sql_verificar_subcategoria_fornecedores = "SELECT cod_fornecedores FROM `ipi_titulos_subcategorias_ipi_fornecedores` WHERE cod_fornecedores in ($indices_sql)";

        $res_verificar_subcategoria_fornecedores = mysql_query($sql_verificar_subcategoria_fornecedores);


        if (mysql_num_rows($res_verificar_subcategoria_fornecedores)>0)
        {
            while ($obj_verificar_subcategoria_fornecedores = mysql_fetch_object($res_verificar_subcategoria_fornecedores)) 
            {   
                        $sql_del_titulo_subcategoria_fornecedores = "DELETE FROM ipi_titulos_subcategorias_ipi_fornecedores WHERE cod_fornecedores = $obj_verificar_subcategoria_fornecedores->cod_fornecedores";
                        //echo $sql_del_titulo_subcategoria_fornecedores."<br/>";
                        if (mysql_query($sql_del_titulo_subcategoria_fornecedores))
                        {
                            $sql_del = "DELETE FROM $tabela WHERE $chave_primaria IN ($obj_verificar_subcategoria_fornecedores->cod_fornecedores)";
                            //echo $sql_del;
        
                            if (mysql_query($sql_del))
                            {
                               mensagemOK('Os registros selecionados foram excluídos com sucesso!');
                            }
                            else
                            {
                                mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
                            }
                        }                        
            }


        }
        // else
        // {
        //         $sql_del = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";

        //         if (mysql_query($sql_del))
        //         {
        //            mensagemOK('Os registros selecionados foram excluídos com sucesso!');
        //         }
        //         else
        //         {
        //             mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        //         }
        // }
        else
        {
            mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        }
        
        
        desconectabd($conexao);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
        $nome_fantasia = mb_strtoupper(texto2bd(validaVarPost('nome_fantasia')));
        $razao_social = mb_strtoupper(texto2bd(validaVarPost('razao_social')));
        $cnpj = texto2bd(validaVarPost('cnpj'));
        $cep = texto2bd(validaVarPost('cep'));
        $endereco= texto2bd(validaVarPost('endereco'));
        $numero = texto2bd(validaVarPost('numero'));
        $complemento = texto2bd(validaVarPost('complemento'));
        $bairro = texto2bd(validaVarPost('bairro'));
        $cidade = texto2bd(validaVarPost('cidade'));
        $estado = texto2bd(validaVarPost('estado'));
        $site = texto2bd(validaVarPost('site'));
        $email = texto2bd(validaVarPost('email'));
        $historico = texto2bd(validaVarPost('historico'));
        $situacao = texto2bd(validaVarPost('situacao'));
        $dias_pagamento = texto2bd(validaVarPost('dias_pagamento'));
        $cod_pizzarias = texto2bd(validaVarPost('cod_pizzarias'));
        // $cod_pizzarias = 0;
        $telefone = texto2bd(validaVarPost('telefone'));
        $cod_subcategorias = validaVarPost('cod_titulos_subcategorias');
        //echo "<pre>";print_r($_POST);echo "</pre>";die();
        $conexao = conectabd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (cod_pizzarias, nome_fantasia, razao_social, cnpj, endereco, numero, complemento, bairro, cidade, estado,cep, site, email, historico, situacao, telefone, dias_pagamento) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
                                $cod_pizzarias, $nome_fantasia, $razao_social, $cnpj, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $cep, $site, $email, $historico, $situacao, $telefone, $dias_pagamento);
            //echo "<br/>".$sql_edicao."<br/>";
            if (mysql_query($sql_edicao))
            {
              $codigo = mysql_insert_id();

               $sql_dropar_subs = "DELETE FROM ipi_titulos_subcategorias_ipi_fornecedores where cod_fornecedores = $codigo";
               //echo "<br/>".$sql_dropar_subs."<br/>";
               $res_dropar_subs = mysql_query($sql_dropar_subs);
               if($res_dropar_subs)
               {
                    $res_inserir_subs = TRUE;
                    foreach($cod_subcategorias as $cod_sub)
                    {
                        $sql_inserir_subs = sprintf("INSERT into ipi_titulos_subcategorias_ipi_fornecedores(cod_titulos_subcategorias,cod_fornecedores) values(%d,%d)",$cod_sub,$codigo);
                        $res_inserir_subs &= mysql_query($sql_inserir_subs);
                    }
                    if($res_inserir_subs)
                    {
                         mensagemOK('Registro adicionado com êxito!');
                    }else
                    {
                        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se as subcategorias foram selecionadas corretamente.');
                    }
               }else
               {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se as subcategorias foram selecionadas corretamented.');
               }
            }
            else
            {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET cod_pizzarias = '%s', nome_fantasia = '%s', razao_social = '%s', cnpj = '%s', endereco = '%s', numero = '%s', complemento = '%s',bairro = '%s', cidade = '%s', estado = '%s', cep = '%s', site = '%s', email = '%s', historico = '%s', situacao = '%s', telefone = '%s', dias_pagamento = '%s' WHERE $chave_primaria = $codigo", 
                                 $cod_pizzarias, $nome_fantasia, $razao_social, $cnpj, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $cep, $site, $email, $historico, $situacao, $telefone, $dias_pagamento);
    
            if (mysql_query($sql_edicao))
            {
               $sql_dropar_subs = "DELETE FROM ipi_titulos_subcategorias_ipi_fornecedores where cod_fornecedores = '$codigo'";
               $res_dropar_subs = mysql_query($sql_dropar_subs);
               if($res_dropar_subs)
               {
                    $res_inserir_subs = TRUE;
                    foreach($cod_subcategorias as $cod_sub)
                    {
                        $sql_inserir_subs = sprintf("INSERT into ipi_titulos_subcategorias_ipi_fornecedores(cod_titulos_subcategorias,cod_fornecedores) values(%d,%d)",$cod_sub,$codigo);
                       // echo $sql_inserir_subs."<br/>";
                        $res_inserir_subs &= mysql_query($sql_inserir_subs);
                    }

                    if($res_inserir_subs)
                    {
                         mensagemOK('Registro adicionado com êxito!');
                    }else
                    {
                        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se as subcategorias foram selecionadas corretamente.');
                    }
               }else
               {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se as subcategorias foram selecionadas corretamented.');
               }
            }
            else
            {
                mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        
        desconectabd($conexao);
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen"
    href="../lib/css/tabs_simples.css" />

<script>

function verificar_checkbox(form) 
{
    var cInput = 0;
    var checkBox = form.getElementsByTagName('input');

    for (var i = 0; i < checkBox.length; i++)
    {
        if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) 
        { 
            cInput++; 
        }
    }
   
    if(cInput > 0) 
    {
        if (confirm('Deseja excluir os registros selecionados?'))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
    else 
    {
        alert('Por favor, selecione os itens que deseja excluir.');
     
        return false;
    }
}

function editar(cod) 
{
    var form = new Element('form', 
    {
        'action': '<?
        echo $_SERVER['PHP_SELF']?>',
        'method': 'post'
    });
  
    var input = new Element('input', 
    {
        'type': 'hidden',
        'name': '<?
        echo $chave_primaria?>',
        'value': cod
    });
  
    input.inject(form);
    $(document.body).adopt(form);
  
    form.submit();
}

window.addEvent('domready', function()
{
    var tabs = new Tabs('tabs'); 
  
    if (document.frmIncluir.<?
    echo $chave_primaria?>.value > 0) 
    {
        <?
        if ($acao == '')
        {
            echo 'tabs.irpara(1);';
        }
        ?>
    
        document.frmIncluir.botao_submit.value = 'Alterar';
    }
    else 
    {
        document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  
    tabs.addEvent('change', function(indice)
    {
        if(indice == 1)
        {
            document.frmIncluir.<?
            echo $chave_primaria?>.value = '';
            document.frmIncluir.nome_fantasia.value = '';
            document.frmIncluir.razao_social.value = '';
            document.frmIncluir.cnpj.value = '';
            document.frmIncluir.endereco.value = '';
            document.frmIncluir.numero.value = '';
            document.frmIncluir.complemento.value = '';
            document.frmIncluir.cep.value = '';
            document.frmIncluir.bairro.value = '';
            document.frmIncluir.cidade.value = '';
            document.frmIncluir.estado.value = '';
            document.frmIncluir.site.value = '';
            document.frmIncluir.email.value = '';
            document.frmIncluir.historico.value = '';
            document.frmIncluir.cod_pizzarias.value = '';
            document.frmIncluir.telefone.value = '';
            document.frmIncluir.dias_pagamento.value = '';
            
            document.frmIncluir.botao_submit.value = 'Cadastrar';
        }
    });
});

function completar_endereco() 
{
    var cep = document.frmIncluir.cep.value;
    var url = 'cep=' + cep;
    
   
    if(cep != '') 
    {
        new Request.JSON({url: '../../ipi_completa_cep_ajax.php', onComplete: function(retorno) 
        {
            if(retorno.status == 'OK') 
            {
                document.frmIncluir.endereco.value = retorno.endereco;
                document.frmIncluir.bairro.value = retorno.bairro;
                document.frmIncluir.cidade.value = retorno.cidade;
                document.frmIncluir.estado.value = retorno.estado;
                
                document.frmIncluir.numero.focus();
            }
            else 
            {
                alert('Erro ao completar CEP: ' + retorno.mensagem);
            }
      }}).send(url);
    }
    else 
    {
        alert('Para completar o endereço o campo CEP deverá ter um valor válido.');
    }
}
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

<? if ($exibir_barra_lateral): ?>

<table>
    <tr>

        <!-- Conteúdo -->
        <td class="conteudo">
        

<? endif; ?>

        <?
        $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0;
        $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : $campo_filtro_padrao;
        $cod_pizzaria = validaVarPost('cod_pizzarias');
        $filtro = validaVarPost('filtro');
        ?>
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

            <tr>
                <td class="legenda tdbl tdbt" align="right"><select
                    name="opcoes">
                    <option value="<? echo $campo_filtro_padrao ?>"
                        <?
                        if ($opcoes == $campo_filtro_padrao)
                        {
                            echo 'selected';
                        }
                        ?>>Nome Fantasia</option>
                </select></td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text"
                    name="filtro" size="60" value="<?
                    echo $filtro?>"></td>
            </tr>
                        <tr>
                            <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
                            <td class="">&nbsp;</td>
                            <td class="tdbr ">
                                <select name="cod_pizzarias" id="cod_pizzarias">
                                    <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
                                    <?
                                    $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

                                    $con = conectabd();
                                    $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao!='INATIVO' AND cod_pizzarias in ($cod_pizzarias_usuario ) ORDER BY nome";//cod_pizzarias IN ($cod_pizzarias_usuario)
                                    $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
                                
                                    while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
                            {
                                echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
                                if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
                                {
                                    echo 'selected';
                                }
                                echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
                            }
                                    ?>
                                </select>
                            </td>
                        </tr>
            <tr>
                <td align="right" class="tdbl tdbb tdbr" colspan="3"><input
                    class="botaoAzul" type="submit" value="Buscar"></td>
            </tr>

        </table>

        <input type="hidden" name="acao" value="buscar"></form>

        <br>

        <?
        
        $conexao = conectabd();
        
        if($cod_pizzaria =="")
        {
            $sql_buscar_registros = "SELECT * FROM $tabela WHERE  $opcoes LIKE '%$filtro%' ";//cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND
        }
        else
        {
            $sql_buscar_registros = "SELECT * FROM $tabela WHERE cod_pizzarias = $cod_pizzaria AND $opcoes LIKE '%$filtro%' ";
        }
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY ' . $campo_ordenacao . ' LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        //echo $sql_buscar_registros;

        echo "<center><b>" . $num_buscar_registros . " registro(s) encontrado(s)</center></b><br>";
        
        if ((($quant_pagina * $pagina) == $num_buscar_registros) && ($pagina != 0) && ($acao == 'excluir'))
        {
            $pagina--;
        }
        
        echo '<center>';
        
        $numpag = ceil(((int) $num_buscar_registros) / ((int) $quant_pagina));
        
        for ($b = 0; $b < $numpag; $b++)
        {
            echo '<form name="frmPaginacao' . $b . '" method="post">';
            echo '<input type="hidden" name="pagina" value="' . $b . '">';
            echo '<input type="hidden" name="filtro" value="' . $filtro . '">';
            echo '<input type="hidden" name="opcoes" value="' . $opcoes . '">';
            
            echo '<input type="hidden" name="acao" value="buscar">';
            echo "</form>";
        }
        
        if ($pagina != 0)
        {
            echo '<a href="#" onclick="javascript:frmPaginacao' . ($pagina - 1) . '.submit();" style="margin-right: 5px;">&laquo;&nbsp;Anterior</a>';
        }
        else
        {
            echo '<span style="margin-right: 5px;">&laquo;&nbsp;Anterior</span>';
        }
        
        for ($b = 0; $b < $numpag; $b++)
        {
            if ($b != 0)
            {
                echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
            }
            
            if ($pagina != $b)
            {
                echo '<a href="#" onclick="javascript:frmPaginacao' . $b . '.submit();">' . ($b + 1) . '</a>';
            }
            else
            {
                echo '<span><b>' . ($b + 1) . '</b></span>';
            }
        }
        
        if (($quant_pagina == $linhas_buscar_registros) && ((($quant_pagina * $pagina) + $quant_pagina) != $num_buscar_registros))
        {
            echo '<a href="#" onclick="javascript:frmPaginacao' . ($pagina + 1) . '.submit();" style="margin-left: 5px;">Próxima&nbsp;&raquo;</a>';
        }
        else
        {
            echo '<span style="margin-left: 5px;">Próxima&nbsp;&raquo;</span>';
        }
        
        echo '</center>';
        
        ?>

        <br>

        <form name="frmExcluir" method="post"
            onsubmit="return verificar_checkbox(this)">

        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <tr>
                <td><input class="botaoAzul" type="submit"
                    value="Excluir Selecionados"></td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <td align="center" width="20"><input type="checkbox"
                        onclick="marcaTodos('marcar');"></td>
                    <td align="center">Nome</td>
                    <td align="center">Razão Social</td>
                    <td align="center">CNPJ</td>
                    <td align="center">Site</td>
                    <td align="center">E-Mail</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->nome_fantasia) . '</a></td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->razao_social) . '</a></td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->cnpj) . '</a></td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->site) . '</a></td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->email) . '</a></td>';
                
                echo '</tr>';
            }
            
            desconectabd($conexao);
            
            ?>
          
            </tbody>
        </table>

        <input type="hidden" name="acao" value="excluir"></form>

<?
if ($exibir_barra_lateral)
:
    ?>

        </td>
        <!-- Conteúdo -->

        <!-- Barra Lateral -->
        <td class="lateral">
        <div class="blocoNavegacao">
        <ul>
            <li><a href="#">Atalho 1</a></li>
            <li><a href="#">Atalho 2</a></li>
        </ul>
        </div>
        </td>
        <!-- Barra Lateral -->

    </tr>
</table>


<? endif;
?>

</div>

<!-- Tab Editar --> <!-- Tab Incluir -->
<div class="painelTab">

    <?
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    $subs = array();
    if ($codigo > 0)
    {
        $obj_editar = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
        $con = conectabd();
        $sql_buscar_subcategorias = "SELECT * FROM ipi_titulos_subcategorias_ipi_fornecedores WHERE cod_fornecedores = $codigo";
        $res_buscar_subcategorias = mysql_query($sql_buscar_subcategorias);
        while($obj_buscar_subcategorias = mysql_fetch_object($res_buscar_subcategorias))
        {
            $subs[] = $obj_buscar_subcategorias->cod_titulos_subcategorias;
        }
        desconectar_bd($con);

    }
    
    ?>
    
    <form name="frmIncluir" method="post"
    onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
        <tr>
        <td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_titulos_subcategorias">Subcategorias do fornecedor</label></td>
    </tr>   
    <tr>
        <td class="tdbl tdbr sep">
            <select name="cod_titulos_subcategorias[]" id="cod_titulos_subcategorias" class="requerido" multiple="multiple" size="10" style="height:auto">
              
                <?
                $con = conectabd();
                
                $sql_buscar_categorias = "SELECT * FROM ipi_titulos_categorias WHERE cod_titulos_categorias IN (SELECT cod_titulos_categorias FROM ipi_titulos_subcategorias WHERE tipo_titulo = 'PAGAR' AND tipo_cendente_sacado = 'FORNECEDOR') ORDER BY titulos_categoria";
                $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                
                while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                {
                    echo '<optgroup label="' . bd2texto($obj_buscar_categorias->titulos_categoria) . '">';
                    
                    $sql_buscar_subcategorias = "SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = '" . $obj_buscar_categorias->cod_titulos_categorias . "' AND tipo_titulo = 'PAGAR' AND tipo_cendente_sacado = 'FORNECEDOR' ORDER BY titulos_subcategorias";
                    $res_buscar_subcategorias = mysql_query($sql_buscar_subcategorias);
                    
                    while($obj_buscar_subcategorias = mysql_fetch_object($res_buscar_subcategorias))
                    {
                        echo '<option value="' . $obj_buscar_subcategorias->cod_titulos_subcategorias . '"';
                        if(in_array($obj_buscar_subcategorias->cod_titulos_subcategorias, $subs))
                        {
                            echo " SELECTED ";
                        }
                        echo '>' . bd2texto($obj_buscar_subcategorias->titulos_subcategorias) . '</option>';
                    }
                    
                    echo '</optgroup>';
                }
                
                
                
                ?>
            </select><br/>(para selecionar mais de uma categoria segure ctrl e clique)
        </td>
    </tr>
    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label
            for="cod_pizzarias">Pizzarias</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr" colspan="2"><select name="cod_pizzarias"
            id="cod_pizzarias">
            <option value=""></option>
        <?
       
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias ORDER BY nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
        while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias))
        {
            echo '<option value="' . $objBuscaPizzarias->cod_pizzarias . '" ';
            
            if ($objBuscaPizzarias->cod_pizzarias == $obj_editar->cod_pizzarias)
                echo 'selected';
            
            echo '>' . bd2texto($objBuscaPizzarias->nome) . '</option>';
        }
        
        desconectabd($con);
        ?>
      </select></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="nome_fantasia">Nome Fantasia</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr ">
        <input class="requerido" type="text" name="nome_fantasia" id="nome_fantasia" maxlength="45" size="45" value="<? echo bd2texto($obj_editar->nome_fantasia) ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="razao_social">Razão Social</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr ">
        <input type="text" name="razao_social" id="razao_social" maxlength="45" size="45" value="<? echo bd2texto($obj_editar->razao_social) ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl  tdbr"><label for="cnpj">CNPJ</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <input type="text" name="cnpj" id="cnpj" onkeypress="return MascaraCNPJ(this, event);" maxlength="45" size="45" value="<? echo $obj_editar->cnpj ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl  tdbr"><label for="telefone">Telefone</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <input type="text" name="telefone" id="telefone" onkeypress="return MascaraTelefone(this, event);" maxlength="45" size="45" value="<? echo $obj_editar->telefone ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="cep">CEP</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr ">
            <input type="text" name="cep" id="cep" onkeypress="return MascaraCEP(this, event);" size="12" value="<? echo $obj_editar->cep ?>">
            &nbsp;&nbsp;&nbsp;
            <input type="button" style="width: 150px;" class="botaoAzul" onclick="completar_endereco()" value="Completar Endereço"/>
        </td>
    </tr>
    
    
    <tr>
        <td class="legenda tdbl  tdbr"><label for="endereco">Endereço</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr ">
        <input type="text" name="endereco" id="endereco" maxlength="80" size="45" value="<? echo bd2texto($obj_editar->endereco) ?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl  tdbr">
            <label for="numero">Numero</label>
            <label style="padding-left: 78px;" for="complemento">Complemento</label>
        </td>
    </tr>
    <tr>  
       <td class="legenda tdbl  tdbr">   
          <input type="text" name="numero" id="numero" maxlength="10" size="16" value="<? echo $obj_editar->numero ?>">
          &nbsp;&nbsp;
          <input type="text" name="complemento" id="complemento" maxlength="20" size="20" value="<? echo bd2texto($obj_editar->complemento) ?>">
       </td>
     </tr>       
        
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="bairro">Bairro</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr ">
        <input type="text" name="bairro" id="bairro" maxlength="45" size="45" value="<? echo bd2texto($obj_editar->bairro) ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="cidade">Cidade</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr">
        <input type="text" name="cidade" id="cidade" maxlength="45" size="45" value="<? echo bd2texto($obj_editar->cidade) ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="estado">Estado</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <select name="estado">
                <option value=""/>
                <option value="AC" <? if($obj_editar->estado == 'AC') echo 'SELECTED' ?>>AC</option>
                <option value="AL" <? if($obj_editar->estado == 'AL') echo 'SELECTED' ?>>AL</option>
                <option value="AP" <? if($obj_editar->estado == 'AP') echo 'SELECTED' ?>>AP</option>
                <option value="AM" <? if($obj_editar->estado == 'AM') echo 'SELECTED' ?>>AM</option>
                <option value="BA" <? if($obj_editar->estado == 'BA') echo 'SELECTED' ?>>BA</option>
                <option value="CE" <? if($obj_editar->estado == 'CE') echo 'SELECTED' ?>>CE</option>
                <option value="DF" <? if($obj_editar->estado == 'DF') echo 'SELECTED' ?>>DF</option>
                <option value="ES" <? if($obj_editar->estado == 'ES') echo 'SELECTED' ?>>ES</option>
                <option value="GO" <? if($obj_editar->estado == 'GO') echo 'SELECTED' ?>>GO</option>
                <option value="MA" <? if($obj_editar->estado == 'MA') echo 'SELECTED' ?>>MA</option>
                <option value="MT" <? if($obj_editar->estado == 'MT') echo 'SELECTED' ?>>MT</option>
                <option value="MS" <? if($obj_editar->estado == 'MS') echo 'SELECTED' ?>>MS</option>
                <option value="MG" <? if($obj_editar->estado == 'MG') echo 'SELECTED' ?>>MG</option>
                <option value="PA" <? if($obj_editar->estado == 'PA') echo 'SELECTED' ?>>PA</option>
                <option value="PB" <? if($obj_editar->estado == 'PB') echo 'SELECTED' ?>>PB</option>
                <option value="PR" <? if($obj_editar->estado == 'PR') echo 'SELECTED' ?>>PR</option>
                <option value="PE" <? if($obj_editar->estado == 'PE') echo 'SELECTED' ?>>PE</option>
                <option value="PI" <? if($obj_editar->estado == 'PI') echo 'SELECTED' ?>>PI</option>
                <option value="RJ" <? if($obj_editar->estado == 'RJ') echo 'SELECTED' ?>>RJ</option>
                <option value="RN" <? if($obj_editar->estado == 'RN') echo 'SELECTED' ?>>RN</option>
                <option value="RS" <? if($obj_editar->estado == 'RS') echo 'SELECTED' ?>>RS</option>
                <option value="RO" <? if($obj_editar->estado == 'RO') echo 'SELECTED' ?>>RO</option>
                <option value="RR" <? if($obj_editar->estado == 'RR') echo 'SELECTED' ?>>RR</option>
                <option value="SC" <? if($obj_editar->estado == 'SC') echo 'SELECTED' ?>>SC</option>
                <option value="SP" <? if($obj_editar->estado == 'SP') echo 'SELECTED' ?>>SP</option>
                <option value="SE" <? if($obj_editar->estado == 'SE') echo 'SELECTED' ?>>SE</option>
                <option value="TO" <? if($obj_editar->estado == 'TO') echo 'SELECTED' ?>>TO</option>
            </select>
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="site">Site</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <input type="text" name="site" id="site" maxlength="45" size="45" value="<? echo bd2texto($obj_editar->site) ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="email">E-Mail</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <input type="text" name="email" id="email" maxlength="45" size="45" value="<? echo bd2texto($obj_editar->email) ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="dias_pagamento">Dias para Pagamento</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <input type="text" name="dias_pagamento" id="dias_pagamento" maxlength="10" size="10" value="<? echo bd2texto($obj_editar->dias_pagamento) ?>" onkeypress="return ApenasNumero(event);"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="historico">Histórico</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <textarea name="historico" id="historico" maxlength="250" rows="5" cols="48"><? echo bd2texto($obj_editar->historico) ?></textarea>
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="situacao">Situação</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <select class="requerido" name="situacao" id="situacao">
            <option value=""></option>
            <option value="ATIVO" <? if($obj_editar->situacao == 'ATIVO') echo 'selected'; ?>> Ativo </option>
            <option value="INATIVO" <? if($obj_editar->situacao == 'INATIVO') echo 'selected'; ?>> Inativo </option>
        </select>
        </td>
    </tr>

    <tr>
        <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
            class="botao" type="submit" value="Cadastrar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="editar"> <input type="hidden"
    name="<?
    echo $chave_primaria?>" value="<?
    echo $codigo?>"></form>

</div>
<!-- Tab Incluir --></div>

<? rodape(); ?>
