<?php

class PENIntegracao extends SeiIntegracao {

    private static $strDiretorio;

    public function getNome() {
        return 'M�dulo de Integra��o com o Barramento PEN';
    }

    public function getVersao() {
        return '1.0.0';
    }

    public function getInstituicao() {
        return 'TRF4 - Tribunal Regional Federal da 4� Regi�o';
    }

    public function montarBotaoProcesso(ProcedimentoAPI $objSeiIntegracaoDTO) {
  
        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProcedimentoDTO->setDblIdProcedimento($objSeiIntegracaoDTO->getIdProcedimento());
        $objProcedimentoDTO->retTodos();
        
        $objProcedimentoRN = new ProcedimentoRN();
        $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);
        
        $objSessaoSEI = SessaoSEI::getInstance();
        $objPaginaSEI = PaginaSEI::getInstance();
        $strAcoesProcedimento = "";

        $dblIdProcedimento = $objProcedimentoDTO->getDblIdProcedimento();
        $numIdUsuario = SessaoSEI::getInstance()->getNumIdUsuario();
        $numIdUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();

        //Verifica se o processo encontra-se aberto na unidade atual
        $objAtividadeRN = new AtividadeRN();
        $objPesquisaPendenciaDTO = new PesquisaPendenciaDTO();
        $objPesquisaPendenciaDTO->setDblIdProtocolo($dblIdProcedimento);
        $objPesquisaPendenciaDTO->setNumIdUsuario($numIdUsuario);
        $objPesquisaPendenciaDTO->setNumIdUnidade($numIdUnidadeAtual);
        $objPesquisaPendenciaDTO->setStrSinMontandoArvore('N');
        $arrObjProcedimentoDTO = $objAtividadeRN->listarPendenciasRN0754($objPesquisaPendenciaDTO);
        $bolFlagAberto = count($arrObjProcedimentoDTO) == 1;


        //Verifica��o da Restri��o de Acesso � Funcionalidade
        $bolAcaoExpedirProcesso = $objSessaoSEI->verificarPermissao('pen_procedimento_expedir');

        // ExpedirProcedimentoRN::__construct() criar a inst�ncia do ProcessoEletronicoRN
        // e este pode lan�ar exce��es caso alguma configura��o dele n�o estaja correta
        // invalidando demais a��es na tela do Controle de Processo, ent�o ecapsulamos
        // no try/catch para prevenir o erro em tela adicionamos no log
       // try {

            $objExpedirProcedimentoRN = new ExpedirProcedimentoRN();
            $objProcedimentoDTO = $objExpedirProcedimentoRN->consultarProcedimento($dblIdProcedimento);

         /*   $bolProcessoEstadoNormal = !in_array($objProcedimentoDTO->getStrStaEstadoProtocolo(), array(
                        ProtocoloRN::$TE_PROCEDIMENTO_SOBRESTADO,
                        ProtocoloRN::$TE_EM_PROCESSAMENTO,
                        ProtocoloRn::$TE_BLOQUEADO
            ));*/

            //TODO: N�o apresentar
            //$bolFlagAberto && $bolAcaoProcedimentoEnviar && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo()!=ProtocoloRN::$NA_SIGILOSO
          //  if ($bolFlagAberto && $bolAcaoExpedirProcesso && $bolProcessoEstadoNormal && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() != ProtocoloRN::$NA_SIGILOSO) {
                $numTabBotao = $objPaginaSEI->getProxTabBarraComandosSuperior();
                $strAcoesProcedimento .= '<a id="validar_expedir_processo" href="' . $objPaginaSEI->formatarXHTML($objSessaoSEI->assinarLink('controlador.php?acao=pen_procedimento_expedir&acao_origem=procedimento_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&arvore=1')) . '" tabindex="' . $numTabBotao . '" class="botaoSEI"><img class="infraCorBarraSistema" src="' . $this->getDiretorioImagens() . '/pen_expedir_procedimento.gif" alt="Expedir Processo" title="Expedir Processo" /></a>';
            //}

       /*     if ($objProcedimentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_EM_PROCESSAMENTO) {

                $objProcessoEletronicoRN = new ProcessoEletronicoRN();

                if ($objProcessoEletronicoRN->isDisponivelCancelarTramite($objProcedimentoDTO->getStrProtocoloProcedimentoFormatado())) {
                    $strAcoesProcedimento .= '<a href="' . $objPaginaSEI->formatarXHTML($objSessaoSEI->assinarLink('controlador.php?acao=pen_procedimento_cancelar_expedir&acao_origem=procedimento_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&arvore=1')) . '" tabindex="' . $numTabBotao . '" class="botaoSEI">';
                    $strAcoesProcedimento .= '<img class="infraCorBarraSistema" src="' . $this->getDiretorioImagens() . '/sei_desanexar_processo.gif" alt="Cancelar Expedi��o" title="Cancelar Expedi��o" />';
                    $strAcoesProcedimento .= '</a>';
                }
            }
            $objProcedimentoAndamentoDTO = new ProcedimentoAndamentoDTO();
            $objProcedimentoAndamentoDTO->setDblIdProcedimento($dblIdProcedimento);

            $objGenericoBD = new GenericoBD(BancoSEI::getInstance());

            if ($objGenericoBD->contar($objProcedimentoAndamentoDTO) > 0) {

                $strAcoesProcedimento .= '<a href="' . $objSessaoSEI->assinarLink('controlador.php?acao=pen_procedimento_estado&acao_origem=procedimento_visualizar&acao_retorno=arvore_visualizar&id_procedimento=' . $dblIdProcedimento . '&arvore=1') . '" tabindex="' . $numTabBotao . '" class="botaoSEI">';
                $strAcoesProcedimento .= '<img class="infraCorBarraSistema" src="' . $this->getDiretorioImagens() . '/pen_consultar_recibos.png" alt="Consultar Recibos" title="Consultar Recibos"/>';
                $strAcoesProcedimento .= '</a>';
            }
        
     /*   } catch (InfraException $e) {
            LogSEI::getInstance()->gravar($e->getStrDescricao());
        } catch (Exception $e) {
            LogSEI::getInstance()->gravar($e->getMessage());
        }*/

        return array($strAcoesProcedimento);
    }

    
    public function montarIconeControleProcessos($arrObjProcedimentoAPI = array()) {
        
        $arrStrIcone = array();
        $arrDblIdProcedimento = array();

        foreach ($arrObjProcedimentoAPI as $ObjProcedimentoAPI) {
            $arrDblIdProcedimento[] = $ObjProcedimentoAPI->getIdProcedimento();
        }

        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProcedimentoDTO->setDblIdProcedimento($arrDblIdProcedimento, InfraDTO::$OPER_IN);
        $objProcedimentoDTO->retDblIdProcedimento();
        $objProcedimentoDTO->retStrStaEstadoProtocolo();

        $objProcedimentoBD = new ProcedimentoBD(BancoSEI::getInstance());
        $arrObjProcedimentoDTO = $objProcedimentoBD->listar($objProcedimentoDTO);

        if (!empty($arrObjProcedimentoDTO)) {

            foreach ($arrObjProcedimentoDTO as $objProcedimentoDTO) {

                $dblIdProcedimento = $objProcedimentoDTO->getDblIdProcedimento();

                if ($objProcedimentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO) {
                    $arrStrIcone[$dblIdProcedimento] = array('<img src="' . $this->getDiretorioImagens() . '/pen_em_processamento.png" title="Em Tramita��o Externa" />');
                } else {
                    $objPenProtocoloDTO = new PenProtocoloDTO();
                    $objPenProtocoloDTO->setDblIdProtocolo($dblIdProcedimento);
                    $objPenProtocoloDTO->retStrSinObteveRecusa();
                    $objPenProtocoloDTO->setNumMaxRegistrosRetorno(1);

                    $objProtocoloBD = new ProtocoloBD(BancoSEI::getInstance());
                    $objPenProtocoloDTO = $objProtocoloBD->consultar($objPenProtocoloDTO);

                    if (!empty($objPenProtocoloDTO) && $objPenProtocoloDTO->getStrSinObteveRecusa() == 'S') {

                        $arrStrIcone[$dblIdProcedimento] = array('<img src="' . $this->getDiretorioImagens() . '/pen_tramite_recusado.png" title="Um tr�mite para esse processo foi recusado" />');
                    }
                }
            }
        }

        return $arrStrIcone;
    }

    public function montarIconeAcompanhamentoEspecial($arrObjProcedimentoDTO) {
        
    }

    public function getDiretorioImagens() {
        return static::getDiretorio() . '/imagens';
    }

    public function montarMensagemSituacaoProcedimento(ProcedimentoDTO $objProcedimentoDTO) {
        if ($objProcedimentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_EM_PROCESSAMENTO || $objProcedimentoDTO->getStrStaEstadoProtocolo() == ProtocoloRN::$TE_BLOQUEADO) {
            $objAtividadeDTO = new AtividadeDTO();
            $objAtividadeDTO->setDblIdProtocolo($objProcedimentoDTO->getDblIdProcedimento());
            $objAtividadeDTO->retNumIdAtividade();

            $objAtividadeRN = new AtividadeRN();
            $arrAtividadeDTO = (array) $objAtividadeRN->listarRN0036($objAtividadeDTO);

            if (empty($arrAtividadeDTO)) {

                throw new InfraException('N�o foi possivel localizar as atividades executadas nesse procedimento');
            }

            $objFiltroAtributoAndamentoDTO = new AtributoAndamentoDTO();
            $objFiltroAtributoAndamentoDTO->setStrNome('UNIDADE_DESTINO');
            $objFiltroAtributoAndamentoDTO->retStrValor();
            $objFiltroAtributoAndamentoDTO->setOrdNumIdAtributoAndamento(InfraDTO::$TIPO_ORDENACAO_DESC);

            $objAtributoAndamentoRN = new AtributoAndamentoRN();
            $objAtributoAndamentoFinal = null;

            foreach ($arrAtividadeDTO as $objAtividadeDTO) {

                $objFiltroAtributoAndamentoDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());
                $objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objFiltroAtributoAndamentoDTO);

                if (!empty($objAtributoAndamentoDTO)) {
                    $objAtributoAndamentoFinal = $objAtributoAndamentoDTO;
                }
            }
            $objAtributoAndamentoDTO = $objAtributoAndamentoFinal;

            //@TODOJOIN: Retirar esse array_pop(array_pop) pois a vers�o 5.6 n�o permite realizar esse tipo de aninhamento.
            $strUnidadeDestino = array_pop(array_pop(PaginaSEI::getInstance()->getArrOptionsSelect($objAtributoAndamentoDTO->getStrValor())));

            return "<br/>" . sprintf('Processo em tr�mite externo para "%s".', $strUnidadeDestino);
        }
    }

    public static function getDiretorio() {

//        if (empty(static::$strDiretorio)) {
//
//            $arrModulos = ConfiguracaoSEI::getInstance()->getValor('SEI', 'Modulos');
//
//            $strModuloPath = realpath($arrModulos['PENIntegracao']);
//            
//
//            
//            static::$strDiretorio = str_replace(realpath(__DIR__ . '/../..'), '', $strModuloPath);
//            static::$strDiretorio = preg_replace('/^\//', '', static::$strDiretorio);
//        }
//
//        return static::$strDiretorio;
        return "modulos/pen";
    }

    public function processarControlador($strAcao) {
        switch ($strAcao) {
            case 'pen_procedimento_expedir':
                require_once dirname(__FILE__) . '/pen_procedimento_expedir.php';
                return true;
            //TODO: Alterar nome do recurso para pen_procedimento_expedir_unidade_sel
            case 'pen_unidade_sel_expedir_procedimento':
                require_once dirname(__FILE__) . '/pen_unidade_sel_expedir_procedimento.php';
                return true;

            case 'pen_procedimento_processo_anexado':
                require_once dirname(__FILE__) . '/pen_procedimento_processo_anexado.php';
                return true;

            case 'pen_procedimento_cancelar_expedir':
                require_once dirname(__FILE__) . '/pen_procedimento_cancelar_expedir.php';
                return true;

            case 'pen_procedimento_expedido_listar':
                require_once dirname(__FILE__) . '/pen_procedimento_expedido_listar.php';
                return true;

            case 'pen_map_tipo_doc_enviado_listar':
            case 'pen_map_tipo_doc_enviado_excluir':
            case 'pen_map_tipo_doc_enviado_desativar':
            case 'pen_map_tipo_doc_enviado_ativar':
                require_once dirname(__FILE__) . '/pen_map_tipo_doc_enviado_listar.php';
                return true;

            case 'pen_map_tipo_doc_enviado_cadastrar':
            case 'pen_map_tipo_doc_enviado_visualizar':
                require_once dirname(__FILE__) . '/pen_map_tipo_doc_enviado_cadastrar.php';
                return true;

            case 'pen_map_tipo_doc_recebido_listar':
            case 'pen_map_tipo_doc_recebido_excluir':
                require_once dirname(__FILE__) . '/pen_map_tipo_doc_recebido_listar.php';
                return true;

            case 'pen_map_tipo_doc_recebido_cadastrar':
            case 'pen_map_tipo_doc_recebido_visualizar':
                require_once dirname(__FILE__) . '/pen_map_tipo_doc_recebido_cadastrar.php';
                return true;

            case 'apensados_selecionar_expedir_procedimento':
                require_once dirname(__FILE__) . '/apensados_selecionar_expedir_procedimento.php';
                return true;

            case 'pen_procedimento_estado':
                require_once dirname(__FILE__) . '/pen_procedimento_estado.php';
                return true;
        }

        return false;
    }

    public function processarControladorAjax($strAcao) {
        $xml = null;

        switch ($_GET['acao_ajax']) {

            case 'pen_unidade_auto_completar_expedir_procedimento':
                $arrObjEstruturaDTO = (array) ProcessoEletronicoINT::autoCompletarEstruturas($_POST['id_repositorio'], $_POST['palavras_pesquisa']);

                if (count($arrObjEstruturaDTO) > 0) {
                    $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjEstruturaDTO, 'NumeroDeIdentificacaoDaEstrutura', 'Nome');
                } else {
                    throw new InfraException("Unidade n�o Encontrada.", $e);
                }
                break;

            case 'pen_apensados_auto_completar_expedir_procedimento':
                //TODO: Validar par�metros passados via ajax     
                $dblIdProcedimentoAtual = $_POST['id_procedimento_atual'];
                $numIdUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
                $arrObjProcedimentoDTO = ProcessoEletronicoINT::autoCompletarProcessosApensados($dblIdProcedimentoAtual, $numIdUnidadeAtual, $_POST['palavras_pesquisa']);
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjProcedimentoDTO, 'IdProtocolo', 'ProtocoloFormatadoProtocolo');
                break;

            case 'pen_procedimento_expedir_validar':
                require_once dirname(__FILE__) . '/pen_procedimento_expedir_validar.php';
                break;
        }

        return $xml;
    }

}
