<?php

/**
 * Script para atualiza��o do sistema SEI.
 *
 * Modificar o "short_open_tag" para "On" no php.ini
 *
 * PHP 5.3.3 (cli) (built: Jul  9 2015 17:39:00)
 * Copyright (c) 1997-2010 The PHP Group
 * Zend Engine v2.3.0, Copyright (c) 1998-2010 Zend Technologies
 *
 */

try {
    require_once dirname(__FILE__).'/../web/SEI.php';

    //Normaliza o formato de n�mero de vers�o considerando dois caracteres para cada item (3.0.15 -> 030015)
    $numVersaoAtual = explode('.', SEI_VERSAO);
    $numVersaoAtual = array_map(function($item){ return str_pad($item, 2, '0', STR_PAD_LEFT); }, $numVersaoAtual);
    $numVersaoAtual = intval(join($numVersaoAtual));

    //Normaliza o formato de n�mero de vers�o considerando dois caracteres para cada item (3.1.0 -> 030100)
    // A partir da vers�o 3.1.0 � que o SEI passa a dar suporte ao UsuarioScript/SenhaScript
    $numVersaoScript = explode('.', "3.1.0");
    $numVersaoScript = array_map(function($item){ return str_pad($item, 2, '0', STR_PAD_LEFT); }, $numVersaoScript);
    $numVersaoScript = intval(join($numVersaoScript));

    if ($numVersaoAtual >= $numVersaoScript) {
        BancoSEI::getInstance()->setBolScript(true);

        if (!ConfiguracaoSEI::getInstance()->isSetValor('BancoSEI','UsuarioScript')){
            throw new InfraException('Chave BancoSEI/UsuarioScript n�o encontrada.');
        }
      
        if (InfraString::isBolVazia(ConfiguracaoSEI::getInstance()->getValor('BancoSEI','UsuarioScript'))){
            throw new InfraException('Chave BancoSEI/UsuarioScript n�o possui valor.');
        }
      
        if (!ConfiguracaoSEI::getInstance()->isSetValor('BancoSEI','SenhaScript')){
            throw new InfraException('Chave BancoSEI/SenhaScript n�o encontrada.');
        }
      
        if (InfraString::isBolVazia(ConfiguracaoSEI::getInstance()->getValor('BancoSEI','SenhaScript'))){
            throw new InfraException('Chave BancoSEI/SenhaScript n�o possui valor.');
        }    
    }

    $objAtualizarRN = new PenAtualizarSeiRN();
    $objAtualizarRN->atualizarVersao();
    exit(0);
}
catch(InfraException $e){

    print $e->getStrDescricao().PHP_EOL;
}
catch(Exception $e) {

    print InfraException::inspecionar($e);

    try {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {

    }

    exit(1);
}

print PHP_EOL;

