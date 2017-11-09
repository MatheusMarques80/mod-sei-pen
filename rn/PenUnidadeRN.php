<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Description of PenUnidadeEnvioRN
 *
 * @author Join Tecnologia (Thiago Farias)
 */
class PenUnidadeRN extends InfraRN {
    
    /**
     * Inicializa o obj do banco da Infra
     * @return obj
     */
    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }
    
    /**
     * M�todo para buscar apenas as unidades que j� est�o em uso
     * @param PenUnidadeDTO $objFiltroDTO
     * @return arrayDTO
     */
    public function getIdUnidadeEmUso(PenUnidadeDTO $objFiltroDTO){
        $objDTO = new PenUnidadeDTO();
        $objDTO->setDistinct(true);
        $objDTO->retNumIdUnidade();
        
        if($objFiltroDTO->isSetNumIdUnidade()) {
            $objDTO->setNumIdUnidade($objFiltroDTO->getNumIdUnidade(), InfraDTO::$OPER_DIFERENTE);
        }

        $arrObjDTO = $this->listar($objDTO);
        
        $arrIdUnidade = array();
        
        if(!empty($arrObjDTO)) {
            $arrIdUnidade = InfraArray::converterArrInfraDTO($arrObjDTO, 'IdUnidade');
        }
        return $arrIdUnidade;
    }
    
    /**
     * M�todo utilizado para listagem de dados.
     * @param UnidadeDTO $objUnidadeDTO
     * @return array
     * @throws InfraException
     */
    protected function listarConectado(UnidadeDTO $objUnidadeDTO) {
        try {
            //Valida Permissao
            SessaoSEI::getInstance()->validarAuditarPermissao('pen_map_unidade_listar',__METHOD__,$objUnidadeDTO);

            $objUnidadeBD = new UnidadeBD($this->getObjInfraIBanco());
            $ret = $objUnidadeBD->listar($objUnidadeDTO);
            
            return $ret;
        }catch(Exception $e){
            throw new InfraException('Erro listando Unidades.',$e);
        }
    }
    
    /**
     * M�todo utilizado para altera��o de dados.
     * @param UnidadeDTO $objDTO
     * @return array
     * @throws InfraException
     */
    protected function alterarConectado(PenUnidadeDTO $objDTO){
        try {
            $objBD = new GenericoBD($this->inicializarObjInfraIBanco());
            return $objBD->alterar($objDTO);
        } 
        catch (Exception $e) {
            throw new InfraException('Erro excluindo E-mail do Sistema.', $e);
        }
    }
    
    /**
     * M�todo utilizado para cadastro de dados.
     * @param UnidadeDTO $objDTO
     * @return array
     * @throws InfraException
     */
    protected function cadastrarConectado(PenUnidadeDTO $objDTO){
        try {
            $objBD = new GenericoBD($this->inicializarObjInfraIBanco());
            return $objBD->cadastrar($objDTO);
        } 
        catch (Exception $e) {
            throw new InfraException('Erro excluindo E-mail do Sistema.', $e);
        }
    }
    
    /**
     * M�todo utilizado para exclus�o de dados.
     * @param UnidadeDTO $objDTO
     * @return array
     * @throws InfraException
     */
    protected function excluirConectado(PenUnidadeDTO $objDTO){
        try {
            $objBD = new GenericoBD($this->inicializarObjInfraIBanco());
            return $objBD->excluir($objDTO);
        } 
        catch (Exception $e) {
            throw new InfraException('Erro excluindo E-mail do Sistema.', $e);
        }
    }
}
