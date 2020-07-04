<?php

class Cenario001Test extends CenarioBaseTestCase
{
    public function test_tramitar_processo_contendo_documento_gerado()
    {
        // Configura��o do dados para teste do cen�rio
        $remetente = $this->definirContextoTeste(CONTEXTO_ORGAO_A);
        $destinatario = $this->definirContextoTeste(CONTEXTO_ORGAO_B);
        $processoTeste = $this->gerarDadosProcessoTeste($remetente);
        $documentoTeste = $this->gerarDadosDocumentoInternoTeste($remetente);

        $orgaosDiferentes = $remetente['ORGAO'] != $destinatario['ORGAO'];

        // 1 - Acessar sistema do REMETENTE do processo
        $this->acessarSistema($remetente['URL'], $remetente['SIGLA_UNIDADE'], $remetente['LOGIN'], $remetente['SENHA']);

        // 2 - Cadastrar novo processo de teste
        $strProtocoloTeste = $this->cadastrarProcesso($processoTeste);

        // 3 - Incluir Documentos no Processo
        $this->cadastrarDocumentoInterno($documentoTeste);

        // 4 - Assinar documento interno criado anteriormente
        $this->assinarDocumento($remetente['ORGAO'], $remetente['CARGO_ASSINATURA'], $remetente['SENHA']);

        // 5 - Tr�mitar Externamento processo para �rg�o/unidade destinat�ria
        $this->tramitarProcessoExternamente($strProtocoloTeste, $destinatario['REP_ESTRUTURAS'], $destinatario['NOME_UNIDADE'],
            $destinatario['SIGLA_UNIDADE_HIERARQUIA'], false, function($testCase) {
            $testCase->frame('ifrEnvioProcesso');
            $testCase->assertContains('Tr�mite externo do processo finalizado com sucesso!', $testCase->byCssSelector('body')->text());
            $btnFechar = $testCase->byXPath("//input[@id='btnFechar']");
            $btnFechar->click();
            $testCase->frame(null);
            return true;
        });

        // 6 - Verificar se situa��o atual do processo est� como bloqueado
        $this->waitUntil(function($testCase) use (&$orgaosDiferentes) {
            sleep(5);
            $testCase->refresh();
            $paginaProcesso = new PaginaProcesso($testCase);
            $testCase->assertNotContains("Processo em tr�mite externo para ", $paginaProcesso->informacao());
            $testCase->assertFalse($paginaProcesso->processoAberto());
            $testCase->assertEquals($orgaosDiferentes, $paginaProcesso->processoBloqueado());
            return true;
        }, PEN_WAIT_TIMEOUT);

        // 7 - Validar se recibo de tr�mite foi armazenado para o processo (envio e conclus�o)
        $this->validarRecibosTramite(sprintf("Tr�mite externo do Processo %s para %s", $strProtocoloTeste, $destinatario['NOME_UNIDADE']), true, true);

        // 8 - Validar hist�rico de tr�mite do processo
        $this->validarHistoricoTramite($destinatario['NOME_UNIDADE'], true, true);

        // 9 - Verificar se processo est� na lista de Processos Tramitados Externamente
        $this->validarProcessosTramitados($strProtocoloTeste, $orgaosDiferentes);

        // 10 - Acessar sistema de REMETENTE do processo
        $this->paginaBase->sairSistema();
        $this->acessarSistema($destinatario['URL'], $destinatario['SIGLA_UNIDADE'], $destinatario['LOGIN'], $destinatario['SENHA']);

        // 11 - Abrir protocolo na tela de controle de processos
        $this->abrirProcesso($strProtocoloTeste);
        $listaDocumentos = $this->paginaProcesso->listarDocumentos();

        // 12 - Validar dados  do processo
        $processoTeste['OBSERVACOES'] = $orgaosDiferentes ? 'Tipo de processo no �rg�o de origem: ' . $processoTeste['TIPO_PROCESSO'] : null;
        $this->validarDadosProcesso($processoTeste['DESCRICAO'], $processoTeste['RESTRICAO'], $processoTeste['OBSERVACOES'], array($processoTeste['INTERESSADOS']));

        // 13 - Verificar recibos de tr�mite
        $this->validarRecibosTramite("Recebimento do Processo $strProtocoloTeste", false, true);

        // 14 - Validar dados do documento
        $this->assertTrue(count($listaDocumentos) == 1);
        $this->validarDadosDocumento($listaDocumentos[0], $documentoTeste, $destinatario);
    }


    public function test_tramitar_processo_contendo_documento_gerado_mesmo_orgao()
    {
        // Configura��o do dados para teste do cen�rio
        $remetente = $this->definirContextoTeste(CONTEXTO_ORGAO_A);
        $processoTeste = $this->gerarDadosProcessoTeste($remetente);
        $documentoTeste = $this->gerarDadosDocumentoInternoTeste($remetente);

        //Configura��o da unidade destinat�rio como outra unidade do mesmo �rg�o
        $destinatario = $this->definirContextoTeste(CONTEXTO_ORGAO_A);
        $destinatario['SIGLA_UNIDADE'] = $remetente['SIGLA_UNIDADE_SECUNDARIA'];
        $destinatario['NOME_UNIDADE'] = $remetente['NOME_UNIDADE_SECUNDARIA'];
        $destinatario['SIGLA_UNIDADE_HIERARQUIA'] = $remetente['SIGLA_UNIDADE_SECUNDARIA_HIERARQUIA'];

        $this->realizarTramiteExternoComValidacaoNoRemetente($processoTeste, $documentoTeste, $remetente, $destinatario);
        $this->paginaBase->sairSistema();
        $this->realizarValidacaoRecebimentoProcessoNoDestinatario($processoTeste, $documentoTeste, $destinatario);
    }
}
