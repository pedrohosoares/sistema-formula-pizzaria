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


/*
define('BD_NOME', 'formula_pr');
define('BD_USUARIO', 'formula_pr');
define('BD_SENHA', 'kGVG2NmmjMAuKX/YOHH7c+r52bo=');
define('BD_HOST', 'localhost');
*/


define('BD_NOME', 'formula_pr');
define('BD_USUARIO', 'pedrosoares');
define('BD_SENHA', '46302113');
define('BD_HOST', '18.214.220.91');

/*
define('BD_NOME', 'formula_pizzaria_sistema');
define('BD_USUARIO', 'formula');
define('BD_SENHA', '46302113@Enzo2501');
define('BD_HOST', '34.95.159.212');
*/

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

define('CAMINHO_CUPOM_FISCAL','https://formulasys.encontresuafranquia.com.br/notas/');

define('CAMINHO_INDEX_IFOOD','https://formulasys.encontresuafranquia.com.br/index.php/?');

define('CAMINHO_DESPACHAR_IFOOD','https://formulasys.encontresuafranquia.com.br/ifood/dispatch.php?reference=');

define('CAMINHO_DESPACHA_PEDIDO_SISTEMA','https://formulasys.encontresuafranquia.com.br/despacha.php?');

//Antigo
define('CAMINHO_DESPACHO_DELIVERY_IFOOD','https://formulasys.encontresuafranquia.com.br/ifood/readyToDelivery.php?reference=');
//define('CAMINHO_DESPACHO_DELIVERY_IFOOD','http://formula-api-11.appspot.com/api/ifood/ready-to-delivery?reference=');

//Antigo
define('CAMINHO_DELIVERY_IFOOD','https://formulasys.encontresuafranquia.com.br/ifood/delivery.php?reference=');
//define('CAMINHO_DELIVERY_IFOOD','http://formula-api-11.appspot.com/api/ifood/dispatch?reference=');

define('CAMINHO_PEDIDO_COZINHA_IFOOD','http://formula-api-11.appspot.com/api/cupons/cupom-cozinha-ifood/');

define('CAMINHO_PEDIDO_IMPRESSO_IFOOD','http://formula-api-11.appspot.com/api/cupons/cupom-pedido-ifood/');

define('CAMINHO_PEDIDO_COZINHA_TEL','https://formulasys.encontresuafranquia.com.br/pedido_cozinha.php?cod_pedidos=');

define('CAMINHO_PEDIDO_IMPRESSO_TEL','https://formulasys.encontresuafranquia.com.br/pedido_impresso.php?cod_pedidos=');

define('CAMINHO_PEDIDO_CANCELADO','http://formula-api-11.appspot.com/api/cupons/cupom-cancelado/');

define('CAMINHO_ARQUIVOS_NOTAS','http://formulasys.encontresuafranquia.com.br/notas/');

define('API_FOCUS_NFE','https://api.focusnfe.com.br');

define('REGENERAR_NOTA_IFOOD','https://formulasys.encontresuafranquia.com.br/focusnfe/regerarNotaFiscalIfood.php?acao=gerar&chave=165117047d56ce2487aa718bd8d6c5b7&cod_pedidos=');

define('REGENERAR_NOTA','https://formulasys.encontresuafranquia.com.br/focusnfe/regerarNotaFiscal.php?acao=gerar&chave=165117047d56ce2487aa718bd8d6c5b7&cod_pedidos=');

define('CANCELAR_NOTA_AJAX_1','https://formulasys.encontresuafranquia.com.br/index.php?acao=cancelar_notas_fiscais&ref=');

define('DATA_HORA',date('Y-m-d H:i:s',strtotime("+ 3 hours")));
define('HORA',date('H:i:s',strtotime("+ 3 hours")));


define('CAMINHO_AJAX','https://formulasys.encontresuafranquia.com.br');

/*
* URLS DO SISTEMA
*/
define('URL_API','http://formula-api-11.appspot.com');
define('URL_PEDIDOS','ipi_rel_historico_pedidos_novo.php?p=');
define('URL_CLIENTES','ipi_clientes_franquia.php?cc=');
define('AJAX_HISTORICO_PEDIDOS','https://formula-api-11.appspot.com/api/sistema/ver-historico/');
define('URL_PEDIR_REIMPRESSAO','https://formula-api-11.appspot.com/api/sistema/cadastrareimprimir/');
define('URL_NFCE','https://api.focusnfe.com.br');


?>
