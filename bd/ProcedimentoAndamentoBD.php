<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Persist�ncia de dados no banco de dados
 * 
 * @autor Join Tecnologia
 */
class ProcedimentoAndamentoBD extends InfraBD {

    public function __construct(InfraIBanco $objInfraIBanco) {
        parent::__construct($objInfraIBanco);
    }
}