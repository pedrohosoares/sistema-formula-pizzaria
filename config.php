<?php
define('VERSAO', '1.00.0');

define('NOME_SITE', 'Formula Pizzaria');
define('NOME_FANTASIA','Formula Pizzaria');

define('TIPO_PRODUTO','Pizza');
define('TIPO_PRODUTOS','Pizzas');

define('TIPO_EMPRESA','Pizzaria');
define('TIPO_EMPRESAS','Pizzarias');

define('REGRA_PRECO_DIVISAO_PIZZA', 'MAIOR'); // MAIOR ou IGUALMENTE

define('EMAIL_PRINCIPAL', 'contato@formulapizzaria.com.br');
define('EMAIL_FALECONOSCO', 'contato@formulapizzaria.com.br');

define('UPLOAD_DIR', '../../upload');

define('HOST', 'formulapizzaria.com.br');

define('URL_NOTICIAS', 'http://www.internetsistemas.com.br/interface_admin_noticias.php');

define('NUM_AFILIACAO_VISA', 'XXXXXX');

define('ENQUETE_ENVIAR', '0');
define('ENQUETE_NOME_PRODUTO', 'Borda Recheada');

define('PONTOS_FIDELIDADE', 'NENHUM_PEDIDO'); //TODOS_PEDIDOS ou NENHUM_PEDIDO

define('FACEBOOK_APP_ID', 'APP_ID');
define('FACEBOOK_SECRET', 'SECRET');

define('BD_NOME', 'formula_pr');
define('BD_USUARIO', 'formula_pr');
define('BD_SENHA', 'kGVG2NmmjMAuKX/YOHH7c+r52bo=');
define('BD_HOST', 'localhost');

define('PRODUTO_USA_TAMANHO', 'S');         // Se usar digite "S" = SIM ou senao usar "N" = Nao
define('PRODUTO_USA_QUANTOS_SABORES', 'S'); // Se usar digite "S" = SIM ou senao usar "N" = Nao
define('PRODUTO_USA_MASSA', 'N');           // Se usar digite "S" = SIM ou senao usar "N" = Nao
define('PRODUTO_USA_CORTE', 'S');           // Se usar digite "S" = SIM ou senao usar "N" = Nao
define('PRODUTO_USA_BORDA', 'S');           // Se usar digite "S" = SIM ou senao usar "N" = Nao
define('PRODUTO_USA_GERGELIM', 'N');        // Se usar digite "S" = SIM ou senao usar "N" = Nao

define('WEBSERVICE_USUARIO', 'formula');
define('WEBSERVICE_SENHA', 'T4mmMiYYtAdskH6UUrYL');

define('ESCREVER_LOG_HD', false);

define('COD_SUBCATEGORIAS_COMISSAO_CARTAO', '1,8');

define('IMPRIMIR_VIA_DESPACHO', 'N');

if (extension_loaded('newrelic')) { newrelic_set_appname(NOME_SITE); }

define("COD_PERFIS_FECHAMENTO_CAIXA", '2,5,9');

define('CODIGO_PIZZARIA_CENTRAL', '6');
define('HORARIO_CENTRAL_PIZZARIA', '17:00:00'); // TEMPO COMPLETO  EX.: 15:00:00

define("COD_PROMOCAO_FRETE_GRATIS", "3");

define('CAMINHO_DESPACHO_PEDIDOS_NOTA_FISCAL','http://sistema.formulapizzaria.com.br/formula/production/current/site/sys/adm/ipi_exibe_pedidos_despachados.php?');

define('CAMINHO_INDEX_IFOOD','https://formulasys.encontresuafranquia.com.br/index.php/?');

define('CAMINHO_DELIVERY_IFOOD','https://formulasys.encontresuafranquia.com.br/ifood/delivery.php?reference=');


?>
