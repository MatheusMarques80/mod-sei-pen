<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Controla o log de estados da expadi��o de um procedimento pelo modulo SEI
 *
 * @autor Join Tecnologia
 */
class ProcedimentoAndamentoRN extends InfraRN {

    protected $isSetOpts = false;
    protected $dblIdProcedimento;
    protected $dblIdTramite;
    protected $numTarefa;
    protected $strNumeroRegistro;

    /**
     * Inv�s de aproveitar o singleton do BancoSEI criamos uma nova inst�ncia para
     * n�o ser afetada pelo transation
     *
     * @return Infra[Driver]
     */
    protected function inicializarObjInfraIBanco(){
        return BancoSEI::getInstance();
    }

    public function setOpts($strNumeroRegistro, $dblIdTramite, $numTarefa, $dblIdProcedimento=null)
    {
        $this->strNumeroRegistro = $strNumeroRegistro;
        $this->dblIdTramite = $dblIdTramite;
        $this->dblIdProcedimento = $dblIdProcedimento;
        $this->numTarefa = $numTarefa;
        $this->isSetOpts = true;
    }

    /**
     * Adiciona um novo andamento � um procedimento que esta sendo expedido para outra unidade
     *
     * @param ProcedimentoAndamentoDTO $parProcedimentoAndamentoDTO
     */
    protected function cadastrarControlado($parProcedimentoAndamentoDTO)
    {
        if($this->isSetOpts === false) {
            throw new InfraException('Log do cadastro de procedimento n�o foi configurado');
        }

        $strMensagem = ($parProcedimentoAndamentoDTO->isSetStrMensagem()) ? $parProcedimentoAndamentoDTO->getStrMensagem() : 'N�o informado';
        $strSituacao = ($parProcedimentoAndamentoDTO->isSetStrSituacao()) ? $parProcedimentoAndamentoDTO->getStrSituacao() : 'N';

        $hash = md5($this->dblIdProcedimento . $strMensagem);
        $objProcedimentoAndamentoDTO = new ProcedimentoAndamentoDTO();
        $objProcedimentoAndamentoDTO->setStrSituacao($strSituacao);
        $objProcedimentoAndamentoDTO->setDthData(date('d/m/Y H:i:s'));
        $objProcedimentoAndamentoDTO->setDblIdProcedimento($this->dblIdProcedimento);
        $objProcedimentoAndamentoDTO->setStrNumeroRegistro($this->strNumeroRegistro);
        $objProcedimentoAndamentoDTO->setDblIdTramite($this->dblIdTramite);
        $objProcedimentoAndamentoDTO->setStrSituacao($strSituacao);
        $objProcedimentoAndamentoDTO->setStrMensagem($strMensagem);
        $objProcedimentoAndamentoDTO->setStrHash($hash);
        $objProcedimentoAndamentoDTO->setNumTarefa($this->numTarefa);

        $objProcedimentoAndamentoBD = new ProcedimentoAndamentoBD($this->getObjInfraIBanco());
        $objProcedimentoAndamentoBD->cadastrar($objProcedimentoAndamentoDTO);
    }
}
