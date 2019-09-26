<?
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

$acao = ValidaVarPost('acao');
$cabecalho = ValidaVarPost('cabecalho');

cabecalho($cabecalho);

$quant_pagina = 7;
$data_atual = date('Y-m-d');

$codigo = ValidaVarPost('codigo');

$cod_secoes = validaVarPost('cod_secoes'); // DEFINE QUAL NOTICIA VAI SER

$conexao = conectabd();

$nova_pagina = (ValidaVarPost('nova_pagina', '/[0-9]+/')) ? ValidaVarPost('nova_pagina', '/[0-9]+/') : 0;

$sql_buscar_registros = "SELECT * FROM iti_textos t INNER JOIN iti_secoes s ON(t.cod_secoes=s.cod_secoes) WHERE t.situacao='ATIVO' AND data_inicio_exibicao<='".$data_atual."' AND t.cod_secoes=$cod_secoes ";
$res_buscar_registros = mysql_query($sql_buscar_registros);
$num_buscar_registros = mysql_num_rows($res_buscar_registros);

$sql_buscar_registros .= ' ORDER BY t.data_texto DESC LIMIT ' . ($quant_pagina * $nova_pagina) . ', ' . $quant_pagina;
$res_buscar_registros = mysql_query($sql_buscar_registros);
$linhas_buscar_registros = mysql_num_rows($res_buscar_registros);

?>

<script>
function editar(codigo)
{   
    document.passar_codigo_noticia.codigo.value = codigo;
    document.passar_codigo_noticia.submit();   
}
</script>
<div
<?
echo '<div id="conteudo" class="caixa" style="width: 600px; ">';
if ($linhas_buscar_registros>0)
{
    if ($acao=="")
    {
        echo "<table border='0' width='100%' cellspacing='10' cellspading='10'>";        
        
        for ($n=0; $n<$linhas_buscar_registros; $n++)
        {
            $obj_busca_dicas_noticias = mysql_fetch_object ($res_buscar_registros);
            
            $texto = strlen($obj_busca_dicas_noticias->texto);
            

            if ( strlen($obj_busca_dicas_noticias->texto)>1)
            {
                echo "<tr>";
                
                if ($obj_busca_dicas_noticias->imagem_gde!='')
                {
                    echo "<td>";
                    echo "</td><td>";
                }
                else
                {
                    echo "<td colspan='2'>";      
                }
                
                if ($obj_busca_dicas_noticias->exibir_data_hora==1)
                {
                    echo "<h3>".nl2br(bd2texto($obj_busca_dicas_noticias->titulo))." - <b>".bd2data($obj_busca_dicas_noticias->data_texto)."</b></h3>";
                }
                else
                {
                    echo "<h3>".nl2br(bd2texto($obj_busca_dicas_noticias->titulo))."</h3>";      
                }
                echo nl2br(substr(bd2texto($obj_busca_dicas_noticias->texto), 0, 180));
                if (strlen($obj_busca_dicas_noticias->texto)>180)
                {
                    echo '...&nbsp;&nbsp;&nbsp;<i><b>(&nbsp;<a href="#" style="text-decoration: underline;" onclick="editar(' . $obj_busca_dicas_noticias->cod_textos . ')"> LEIA MAIS</a>&nbsp;&nbsp;)</b></i>'."</td>";
                }
                else     
                {
                    echo '<b>( <i><a href="#" onclick="editar(' . $obj_busca_dicas_noticias->cod_textos . ')"> LEIA MAIS</a></i>&nbsp;&nbsp;)</b>'."</td>";  
                }
                echo "</tr>";
                echo '<tr><td colspan="2"><hr size="1" width="100%" noshade="noshade" /></td></tr>';
            }
        }
        echo "</table>";    
        if ((($quant_pagina * $nova_pagina) == $num_buscar_registros) && ($nova_pagina != 0) && ($acao == 'excluir'))
        {
            $nova_pagina--;
        }
        
        echo '<center>';
        
        $numpag = ceil(((int) $num_buscar_registros) / ((int) $quant_pagina));
        
        for ($b = 0; $b < $numpag; $b++)
        {
            echo "\n".'<form name="frmPaginacao' . $b . '" method="post">';
            echo "\n".'<input type="hidden" name="nova_pagina" value="' . $b . '">';
            echo "\n </form>\n";
        }
        
        if ($nova_pagina != 0)
        {
            echo '<a href="#" onclick="javascript:document.frmPaginacao' . ($nova_pagina - 1) . '.submit();" style="margin-right: 5px;">&laquo;&nbsp;Anterior</a>';
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
            
            if ($nova_pagina != $b)
            {
                echo '<a href="#" onclick="javascript:document.frmPaginacao' . $b . '.submit();">' . ($b + 1) . '</a>';
            }    
            else
            {
                echo '<span><b>' . ($b + 1) . '</b></span>';
            }    
        }
        
        if (($quant_pagina == $linhas_buscar_registros) && ((($quant_pagina * $nova_pagina) + $quant_pagina) != $num_buscar_registros))
        {
            echo '<a href="#" onclick="javascript:document.frmPaginacao' . ($nova_pagina + 1) . '.submit();" style="margin-left: 5px;">Próxima&nbsp;&raquo;</a>';
        }
        else
        {
            echo '<span style="margin-left: 5px;">Próxima&nbsp;&raquo;</span>';
        }    
        echo '</center>';
        ?>
        
        <form action="<? echo $PHP_SELF ?>" name="passar_codigo_noticia" method="post">
            <input type="hidden" name="codigo" value="" >
            <input type="hidden" name="cod_secoes" value="<? echo $cod_secoes; ?>" >
            <input type="hidden" name="cabecalho" value="<? echo $cabecalho; ?>" >
            <input type="hidden" name="acao" value="exibir">
        </form>
        <?
    }
    else
    {
        $sql_busca_noticias = "SELECT * FROM iti_textos WHERE cod_textos=".$codigo;
        $res_busca_noticias = mysql_query ($sql_busca_noticias);
        $num_busca_noticias = mysql_num_rows ($res_busca_noticias);
        $obj_busca_noticias = mysql_fetch_object ($res_busca_noticias);

        echo "<h1><center>".nl2br(bd2texto($obj_busca_noticias->titulo))."</center></h1>";
        
        if ($obj_busca_noticias->imagem_gde)
        {
            if ($obj_busca_noticias->orientacao_imagem=="DIREITA")
            {
                echo "<div style='float: right; margin: 10px;'><img src='../../upload/imagens_iti/".$obj_busca_noticias->imagem_gde."' width='200'></div>";
            }
            else
            {
                echo "<div style='float: left; margin: 10px;'><img src='../../upload/imagens_iti/".$obj_busca_noticias->imagem_gde."' width='200'></div>";
            }
        }    
        echo '<br />'.nl2br(bd2texto($obj_busca_noticias->texto));
        
        if ($obj_busca_noticias->link!='')
        {
        	echo '<br /><a href="http://'.str_replace('http://', '', $obj_busca_noticias->link).'" target="_blank">'.$obj_busca_noticias->link.'</a>';
        }
        
    }
}
else
{
    echo "Nenhum artigo cadastrado.";  
}
desconectabd($conexao);
echo '</div>';

rodape();
?>
