<?php

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';


cabecalho('Arquivos de Configura��es');

echo '<label>Regra de pre�o na divis�o da pizza:</label> '. (defined('REGRA_PRECO_DIVISAO_PIZZA') ?  REGRA_PRECO_DIVISAO_PIZZA : "N�o configurado").'<br/><br/>'; 

echo '<label>Enviar Enquete?</label> ' .(defined('ENQUETE_ENVIAR') ? ENQUETE_ENVIAR  : "N�o configurado").'<br/><br/>';

echo '<label>Nome do produto da enquete:</label> ' .(defined('ENQUETE_NOME_PRODUTO') ? ENQUETE_NOME_PRODUTO  : "N�o configurado").'<br/><br/>';

echo '<label>Email principal:</label> ' .(defined('EMAIL_PRINCIPAL') ? EMAIL_PRINCIPAL  : "N�o configurado").'<br/><br/>';

echo '<label>Email do fale conosco:</label> ' .(defined('EMAIL_FALECONOSCO') ? EMAIL_FALECONOSCO  : "N�o configurado").'<br/><br/>';

echo '<label>Usuario webservice:</label> ' .(defined('WEBSERVICE_USUARIO') ? WEBSERVICE_USUARIO  : "N�o configurado").'<br/><br/>';

echo '<label>Senha webservice:</label> ' .(defined('WEBSERVICE_SENHA') ? WEBSERVICE_SENHA  : "N�o configurado").'<br/><br/>';

echo '<label>Id do facebook app:</label> ' .(defined('FACEBOOK_APP_ID') ? FACEBOOK_APP_ID  : "N�o configurado").'<br/><br/>';

echo '<label>Senha do facebook app:</label> ' .(defined('FACEBOOK_SECRET') ? FACEBOOK_SECRET : "N�o configurado").'<br/><br/>';

 rodape(); ?>