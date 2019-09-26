<?
/**
 * Camada de apresentação de página.
 *
 * @version 1.0
 * @package iconteudo
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       14/06/2009   FELIPE        Criado.
 *
 */

require_once 'config.php';
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';

define('UPLOAD_DIR_CONTEUDO', 'upload/conteudos');

$comando_sistema = array ('REQUISICAO', 'PALAVRACHAVE', 'BANNER');

function processar_marcas ($conexao, $obj_buscar_pagina)
{
    global $comando_sistema;
    
    $conteudo = bd2texto($obj_buscar_pagina->codigo);
    
    // Retirando as possíveis chamadas php no modelo...
    $conteudo = str_replace('<?', '', $conteudo);
    $conteudo = str_replace('?>', '', $conteudo);
    
    // Mescla marcas com conteúdo
    $codigos = array ();
    
    preg_match_all('/<#([A-Za-z0-9\,\s\_\-\.\[\]]+)#>/', $conteudo, $codigos);
    
    if (count($codigos > 0))
    {
        foreach ($codigos[1] as $val_codigos)
        {
            $comando_argumentos = array ();
            
            if (preg_match('/([A-Za-z0-9\_]+)\[?([a-zA-Z0-9,\_\-\.]+)?\]?/', $val_codigos, $comando_argumentos))
            {
                $comando = strtoupper($comando_argumentos[1]);
                $argumentos = explode(',', $comando_argumentos[2]);
                
                if (!in_array($comando, $comando_sistema))
                {
                    $saida = '';
                    
                    if ($comando == 'TITULO')
                    {
                        $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'TITULO'", $conexao);
                        $obj_campo = executar_busca_simples(sprintf("SELECT * FROM ico_campos_paginas WHERE cod_paginas = %d AND cod_tipos_campos = %d AND numero = %d AND rascunho = %d LIMIT 1", $obj_buscar_pagina->cod_paginas, $obj_tipos_campos->cod_tipos_campos, $argumentos[0], ICO_RASCUNHO), $conexao);
                        $saida = bd2texto($obj_campo->conteudo);
                    }
                    else if ($comando == 'TEXTO')
                    {
                        $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'TEXTO'", $conexao);
                        $obj_campo = executar_busca_simples(sprintf("SELECT * FROM ico_campos_paginas WHERE cod_paginas = %d AND cod_tipos_campos = %d AND numero = %d AND rascunho = %d LIMIT 1", $obj_buscar_pagina->cod_paginas, $obj_tipos_campos->cod_tipos_campos, $argumentos[0], ICO_RASCUNHO), $conexao);
                        $saida = bd2texto($obj_campo->conteudo);
                    }
                    else if ($comando == 'LINK')
                    {
                        $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'LINK'", $conexao);
                        $obj_campo = executar_busca_simples(sprintf("SELECT * FROM ico_campos_paginas WHERE cod_paginas = %d AND cod_tipos_campos = %d AND numero = %d AND rascunho = %d LIMIT 1", $obj_buscar_pagina->cod_paginas, $obj_tipos_campos->cod_tipos_campos, $argumentos[0], ICO_RASCUNHO), $conexao);
                        
                        // Se não começar com http://, adicione!
                        //$endereco = (preg_match('/^http:\/\//', $obj_campo->conteudo)) ? $obj_campo->conteudo : 'http://' . $obj_campo->conteudo;
                        
                        if(ICO_RASCUNHO)
                        {
                            $endereco = 'rascunho/'.$obj_campo->conteudo;    
                        }
                        else
                        {
                            $endereco = $obj_campo->conteudo;
                        }
                        
                        $saida = '<a href="' . $endereco . '">' . $obj_campo->auxiliar . '</a>';
                    }
                    else if ($comando == 'IMAGEM')
                    {
                        $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'IMAGEM'", $conexao);
                        $obj_campo = executar_busca_simples(sprintf("SELECT * FROM ico_campos_paginas WHERE cod_paginas = %d AND cod_tipos_campos = %d AND numero = %d AND rascunho = %d LIMIT 1", $obj_buscar_pagina->cod_paginas, $obj_tipos_campos->cod_tipos_campos, $argumentos[0], ICO_RASCUNHO), $conexao);
                        if (is_file(UPLOAD_DIR_CONTEUDO . '/' . $obj_campo->arquivo))
                        {
                            $info = pathinfo(UPLOAD_DIR_CONTEUDO . '/' . $obj_campo->arquivo);
                            
                            if ($info['extension'] == 'swf')
                            {
                                //$saida = '<object width="' . $objBusca->largura . '" height="' . $objBusca->altura . '">';
                                $saida = '<object>';
                                $saida .= '<param name="movie" value="' . UPLOAD_DIR_CONTEUDO . '/' . $obj_campo->arquivo . '">';
                                //$saida .= '<embed src="' . UPLOAD_DIR_CONTEUDO . '/' . $obj_campo->arquivo . '" width="' . $objBusca->largura . '" height="' . $objBusca->altura . '" wmode="transparent">';
                                $saida .= '<embed src="' . UPLOAD_DIR_CONTEUDO . '/' . $obj_campo->arquivo . '" wmode="transparent">';
                                $saida .= '</embed>';
                                $saida .= '</object>';
                            }
                            else
                            {
                                $saida = '<img src="' . UPLOAD_DIR_CONTEUDO . '/' . $obj_campo->arquivo . '">';
                            }
                        }
                        else
                        {
                            $saida = '';
                        }
                    }
                    else if ($comando == 'TITULOPAGINA')
                    {
                        $saida = ICO_TITULO_PAGINA;
                    }
                    else
                    {
                        // Verifica se é repetição (biblioteca)
                        $chamada = strtoupper($comando);
                        $obj_busca_modelos = executar_busca_simples("SELECT * FROM ico_modelos m WHERE chamada = '$chamada' AND biblioteca = 1 LIMIT 1", $conexao);
                        
                        $saida = ($obj_busca_modelos->cod_modelos > 0) ? processar_marcas($conexao, $obj_busca_modelos) : '';
                    }
                    
                    $conteudo = str_replace('<#' . $val_codigos . '#>', $saida, $conteudo);
                }
            }
        }
    }
    
    return $conteudo;
}

function processar_marcas_sis (&$conteudo)
{
    // Convertendo a entidade &lt; para '<' e &gt; para '>'
    $conteudo = str_replace('&lt;', '<', $conteudo);
    $conteudo = str_replace('&gt;', '>', $conteudo);
    
    // Retirando as possíveis chamadas php no modelo...
    $conteudo = str_replace('<?', '', $conteudo);
    $conteudo = str_replace('?>', '', $conteudo);
    
    // Mescla marcas com conteúdo
    $codigos = array ();
    
    preg_match_all('/<#([A-Za-z0-9\_\-\.,\s\[\]]+)#>/', $conteudo, $codigos);
    
    if (count($codigos > 0))
    {
        foreach ($codigos[1] as $val_codigos)
        {
            $comando_argumentos = array ();
            
            if (preg_match('/([A-Za-z]+)\[?([a-zA-Z\_\-0-9,\?\=\&\.]+)?\]?/', $val_codigos, $comando_argumentos))
            {
                $comando = strtoupper($comando_argumentos[1]);
                $argumentos = explode(',', $comando_argumentos[2]);
                
                $saida = '';
                
                if ($comando == 'REQUISICAO')
                {
                    $saida = (is_file($argumentos[0])) ? "<? require '" . $argumentos[0] . "'; ?>" : 'Requisição: ' . $argumentos[0] . ' não foi encontrado.';
                }
                else if ($comando == 'PALAVRACHAVE')
                {
                    require_once 'palavra_chave.php';
                    $saida = buscar_palavra_chave($argumentos[0], $conexao);
                    
                    //$obj_busca = executar_busca_simples("SELECT texto FROM nuc_banco_palavras WHERE palavra = '" . $argumentos[0] . "' LIMIT 1", $conexao);
                    //$saida = ($obj_busca->texto) ? bd2texto($obj_busca->texto) : $chave;
                }
                else if ($comando == 'BANNER')
                {
                    if (is_file('iba_banner.php'))
                    {
                        require_once 'iba_banner.php';
                        
                        $saida = buscar_banner($argumentos[0]);
                    }
                    else
                    {
                        $saida = 'Módulo iBanner não instalado.';
                    }
                }
                
                $conteudo = str_replace('<#' . $val_codigos . '#>', $saida, $conteudo);
            }
        }
    }
}

function exibir_pagina ($conexao, $pagina = '', $home = false, $erro_404 = false)
{
    if ($home)
    {
        $sql_buscar_pagina_rascunho = (ICO_RASCUNHO) ? '' : 'AND p.publicado = 1';
        $obj_buscar_pagina = executar_busca_simples("SELECT *, p.chamada AS paginas_chamada, m.chamada AS modelos_chamadas FROM ico_paginas p INNER JOIN ico_modelos m ON (p.cod_modelos = m.cod_modelos) WHERE p.home = 1 AND p.habilitado = 1 $sql_buscar_pagina_rascunho LIMIT 1", $conexao);
        
        if ($obj_buscar_pagina->cod_paginas > 0)
        {
            define('ICO_TITULO_PAGINA', bd2texto($obj_buscar_pagina->titulo));
            
            $pagina = processar_marcas($conexao, $obj_buscar_pagina);
            processar_marcas_sis($pagina);
            
            eval("?>" . $pagina . "<?");
        }
        else
        {
            // Erro de arquivo de home não encontrado
            echo '<html>';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"></head>';
            echo '<center><h1 style="font-weight: bold; color: red;">Módulo de Conteúdos não Configurado</h1></center>';
            echo '<br>';
            echo '<center><p style="font-weight: bold; color: red;">A página HOME não foi definida no sistema.</p></center>';
            echo '</html>';
        }
    }
    else if ($erro_404)
    {
        $sql_buscar_pagina_rascunho = (ICO_RASCUNHO) ? '' : 'AND p.publicado = 1';
        $obj_buscar_pagina = executar_busca_simples("SELECT *, p.chamada AS paginas_chamada, m.chamada AS modelos_chamadas FROM ico_paginas p INNER JOIN ico_modelos m ON (p.cod_modelos = m.cod_modelos) WHERE p.erro_404 = 1 AND p.habilitado = 1 $sql_buscar_pagina_rascunho LIMIT 1", $conexao);
        
        // Executa chamada para 404
        if ($obj_buscar_pagina->cod_paginas > 0)
        {
            define('ICO_TITULO_PAGINA', bd2texto($obj_buscar_pagina->titulo));
            
            $pagina = processar_marcas($conexao, $obj_buscar_pagina);
            processar_marcas_sis($pagina);
            
            eval("?>" . $pagina . "<?");
        }
        else
        {
            // Erro de arquivo de home não encontrado
            echo '<html>';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"></head>';
            echo '<center><h1 style="font-weight: bold; color: red;">Módulo de Conteúdos não Configurado</h1></center>';
            echo '<br>';
            echo '<center><p style="font-weight: bold; color: red;">A página de ERRO 404 não foi definida no sistema.</p></center>';
            echo '</html>';
        }
    }
    else
    {
        if(count(explode("-",$pagina))>1)
        {
          $pagina = explode("-",$pagina);
          if($pagina[0]=='nossos_produtos_cardapio')
          {
            define('TIPO_CARDAPIO', $pagina[1]);
            $pagina = $pagina[0];
          }
        }
        elseif($pagina=='nossos_produtos_cardapio')
        {
          define('TIPO_CARDAPIO', 'pizzas');
        }

        $sql_buscar_pagina_rascunho = (ICO_RASCUNHO) ? '' : 'AND p.publicado = 1';
        $obj_buscar_pagina = executar_busca_simples("SELECT * FROM ico_paginas p INNER JOIN ico_modelos m ON (p.cod_modelos = m.cod_modelos) WHERE p.chamada = '$pagina' AND p.habilitado = 1 $sql_buscar_pagina_rascunho LIMIT 1", $conexao);
        
        if ($obj_buscar_pagina->cod_paginas > 0)
        {
            define('ICO_TITULO_PAGINA', bd2texto($obj_buscar_pagina->titulo));
            
            $pagina = processar_marcas($conexao, $obj_buscar_pagina);
            processar_marcas_sis($pagina);
            
            eval("?>" . $pagina . "<?");
        }
        else
        {
            // Chama 404
            exibir_pagina($conexao, '', false, true);
        }
    }
}

$pagina = trim(validar_var_get('pagina', '/[a-z_]+/'));
$pagina = str_replace('/', '', $pagina);
$rascunho = (validar_var_get('rascunho', '/[0-9]+/') == 1) ? true : false;

/*
if (preg_match('/([A-Za-z0-9_]+)\/?([A-Za-z0-9_]?)/', $pagina, $arr_niveis_paginas))
{
    if($arr_niveis_paginas[1] == 'rascunho')
    {
        $pagina = ($arr_niveis_paginas[2] == 'rascunho') ? '' : $arr_niveis_paginas[2];
        $rascunho = true;
    }
    else
    {
        $pagina = $arr_niveis_paginas[1];
        $rascunho = false;
    }
}
*/


define('ICO_PAGINA', $pagina);
define('ICO_RASCUNHO', $rascunho);

$conexao = conectar_bd();

if ($pagina == '')
{
    // Busca a página home
    exibir_pagina($conexao, '', true);
}
else
{
    // Busca a página específica
    exibir_pagina($conexao, $pagina);
}

// De acordo com o php.net (http://www.php.net/manual/pt_BR/function.mysql-close.php): Usar mysql_close() não é normalmente necessário, já que as conexões não persistentes são automaticamente fechadas ao final da execução do script. Veja também liberando recursos. 
//desconectar_bd($conexao);


?>
