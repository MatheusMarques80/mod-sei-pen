<?

require_once DIR_SEI_WEB.'/SEI.php';

class ComponenteDigitalBD extends InfraBD {

    public function __construct(InfraIBanco $objInfraIBanco){
        parent::__construct($objInfraIBanco);
    }

    /**
     * Lista componentes digitais de determinado tr�mite
     *
     * @param TramiteDTO $parObjTramiteDTO
     * @return void
     */
    public function listarComponentesDigitais(TramiteDTO $parObjTramiteDTO)
    {
        if(is_null($parObjTramiteDTO)){
            throw new InfraException('Par�metro [parObjTramiteDTO] n�o informado');
        }

        $objComponenteDigitalPesquisaDTO = new ComponenteDigitalDTO();
        $objComponenteDigitalPesquisaDTO->setOrdNumOrdemDocumento(InfraDTO::$TIPO_ORDENACAO_ASC);
        $objComponenteDigitalPesquisaDTO->setNumIdTramite($parObjTramiteDTO->getNumIdTramite());

        $objComponenteDigitalPesquisaDTO->retStrNumeroRegistro();
        $objComponenteDigitalPesquisaDTO->retDblIdProcedimento();
        $objComponenteDigitalPesquisaDTO->retDblIdDocumento();
        $objComponenteDigitalPesquisaDTO->retNumIdTramite();
        $objComponenteDigitalPesquisaDTO->retNumCodigoEspecie();
        $objComponenteDigitalPesquisaDTO->retStrNomeEspecieProdutor();
        $objComponenteDigitalPesquisaDTO->retStrHashConteudo();
        $objComponenteDigitalPesquisaDTO->retDblIdProcedimentoAnexado();
        $objComponenteDigitalPesquisaDTO->retStrProtocoloProcedimentoAnexado();
        $objComponenteDigitalPesquisaDTO->retNumOrdemDocumento();
        $objComponenteDigitalPesquisaDTO->retNumOrdemDocumentoReferenciado();
        $objComponenteDigitalPesquisaDTO->retNumOrdemDocumentoAnexado();
        $objComponenteDigitalPesquisaDTO->retNumOrdem();

        return $this->listar($objComponenteDigitalPesquisaDTO);



        $objComponenteDigitalDTO = new ComponenteDigitalDTO();
        $objComponenteDigitalDTO->retNumOrdem();
        $objComponenteDigitalDTO->retDblIdProcedimento();
        $objComponenteDigitalDTO->retDblIdDocumento();

        $objComponenteDigitalDTO->retStrHashConteudo();
        $objComponenteDigitalDTO->retNumOrdemDocumento();
        $objComponenteDigitalDTO->retNumOrdemDocumentoAnexado();
        $objComponenteDigitalDTO->retDblIdProcedimentoAnexado();
        $objComponenteDigitalDTO->retStrProtocoloProcedimentoAnexado();

        if(!isset($parDblIdProcedimentoAnexado)){
            $objComponenteDigitalDTO->setDblIdProcedimento($objProcedimentoDTO->getDblIdProcedimento());
            $objComponenteDigitalDTO->setOrdNumOrdemDocumento(InfraDTO::$TIPO_ORDENACAO_ASC);
            $strCampoOrdenacao = "OrdemDocumento";
        } else {
            // Avalia��o de componentes digitais espec�ficos para o processo anexado
            $objComponenteDigitalDTO->setStrNumeroRegistro($strNumeroRegistro);
            $objComponenteDigitalDTO->setDblIdProcedimentoAnexado($parDblIdProcedimentoAnexado);
            $objComponenteDigitalDTO->setOrdNumOrdemDocumentoAnexado(InfraDTO::$TIPO_ORDENACAO_ASC);
            $strCampoOrdenacao = "OrdemDocumento";
        }


    }

    /**
     * Verifica a exist�ncia de algum documento contendo outro referenciado no pr�prio processo
     *
     * @param TramiteDTO $parObjTramiteDTO
     * @return void
     */
    public function possuiComponentesComDocumentoReferenciado(TramiteDTO $parObjTramiteDTO)
    {
        if(is_null($parObjTramiteDTO)){
            throw new InfraException('Par�metro [parObjTramiteDTO] n�o informado');
        }

        $objComponenteDigitalPesquisaDTO = new ComponenteDigitalDTO();
        $objComponenteDigitalPesquisaDTO->setOrdNumOrdemDocumento(InfraDTO::$TIPO_ORDENACAO_ASC);
        $objComponenteDigitalPesquisaDTO->setNumIdTramite($parObjTramiteDTO->getNumIdTramite());
        $objComponenteDigitalPesquisaDTO->setNumOrdemDocumentoReferenciado(null, InfraDTO::$OPER_DIFERENTE);
        $objComponenteDigitalPesquisaDTO->retNumIdTramite();


        return $this->contar($objComponenteDigitalPesquisaDTO) > 0;
    }
}
?>
