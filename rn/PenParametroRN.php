<?php

require_once dirname(__FILE__).'/../../../SEI.php';

/**
 * Regra de neg�cio para o par�metros do m�dulo PEN
 *
 * @author Join Tecnologia
 */
class PenParametroRN extends InfraRN {

    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }
    
    protected function contarControlado(PenParametroDTO $objDTO){
        
        try {

            $objBD = new PenParametroBD($this->inicializarObjInfraIBanco());

            return $objBD->contar($objDTO);
        } 
        catch (Exception $e) {
            throw new InfraException('Erro ao contar par�metro.', $e);
        }
        
    }

    protected function consultarControlado(PenParametroDTO $objDTO){
               
        try {

            SessaoSEI::getInstance()->validarAuditarPermissao('pen_map_hipotese_legal_padrao_cadastrar', __METHOD__, $objDTO);

            $objBD = new PenParametroBD($this->inicializarObjInfraIBanco());
            
            return $objBD->consultar($objDTO);
        } 
        catch (Exception $e) {
            throw new InfraException('Erro ao listar par�metro.', $e);
        }
    }
    
    protected function listarControlado(PenParametroDTO $objDTO){
               
        try {

            SessaoSEI::getInstance()->validarAuditarPermissao('pen_map_hipotese_legal_padrao_cadastrar', __METHOD__, $objDTO);

            $objBD = new PenParametroBD($this->inicializarObjInfraIBanco());
            
            return $objBD->listar($objDTO);
        } 
        catch (Exception $e) {
            throw new InfraException('Erro ao listar par�metro.', $e);
        }
    }
    
    protected function cadastrarControlado(PenParametroDTO $objDTO){
               
        try {

            SessaoSEI::getInstance()->validarAuditarPermissao('pen_map_hipotese_legal_padrao_cadastrar', __METHOD__, $objDTO);

            $objBD = new PenParametroBD($this->inicializarObjInfraIBanco());
            
            return $objBD->cadastrar($objDTO);
        } 
        catch (Exception $e) {
            throw new InfraException('Erro ao cadastrar par�metro.', $e);
        }
    }
    
    protected function alterarControlado(PenParametroDTO $objDTO){
               
        try {

            SessaoSEI::getInstance()->validarAuditarPermissao('pen_map_hipotese_legal_padrao_cadastrar', __METHOD__, $objDTO);

            $objBD = new PenParametroBD($this->inicializarObjInfraIBanco());
            
            return $objBD->alterar($objDTO);
        } 
        catch (Exception $e) {
            throw new InfraException('Erro ao alterar par�metro.', $e);
        }
    }
    
    protected function excluirControlado(PenParametroDTO $objDTO){
               
        try {

            SessaoSEI::getInstance()->validarAuditarPermissao('pen_map_hipotese_legal_padrao_cadastrar', __METHOD__, $objDTO);

            $objBD = new PenParametroBD($this->inicializarObjInfraIBanco());
            
            return $objBD->excluir($objDTO);
        } 
        catch (Exception $e) {
            throw new InfraException('Erro ao excluir par�metro.', $e);
        }
    }
    
    protected function desativarControlado(PenParametroDTO $objDTO){
        
        try {


        } 
        catch (Exception $e) {
            throw new InfraException('Erro ao desativar par�metro.', $e);
        }
    }
    
    protected function reativarControlado(PenParametroDTO $objDTO){
        
        try {


        } 
        catch (Exception $e) {
            throw new InfraException('Erro ao reativar par�metro.', $e);
        }
    }
    
    public function setValor($strNome, $strValor){
        
        try {

            $objBD = new PenParametroBD($this->inicializarObjInfraIBanco());
            
            return $objBD->setValor($strNome, $strValor);
        } 
        catch (Exception $e) {
            throw new InfraException('Erro ao reativar par�metro.', $e);
        }
    }
    
    public function isSetValor($strNome){
        
        return $objBD->isSetValor($strNome); 
    }
    
    /**
     * Resgata o valor do par�metro
     * @param string $strNome
     */
    public function getParametro($strNome) {
        $objPenParametroDTO = new PenParametroDTO();
        $objPenParametroDTO->setStrNome($strNome);
        $objPenParametroDTO->retStrValor();

        if($this->contar($objPenParametroDTO) > 0) {
            $objPenParametroDTO = $this->consultarControlado($objPenParametroDTO);
            return $objPenParametroDTO->getStrValor();
        }
    }
}
