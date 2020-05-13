<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class PenRelTipoDocMapRecebidoRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    public function listarEmUso($dblCodigoEspecie = 0)
    {    
        $arrNumCodigoEspecie = array();
        $objInfraIBanco = $this->inicializarObjInfraIBanco();  
                        
        $objDTO = new PenRelTipoDocMapRecebidoDTO();  
        $objDTO->retNumCodigoEspecie();
        $objDTO->setDistinct(true);
        //$objDTO->setOrdNumCodigoEspecie(InfraDTO::$TIPO_ORDENACAO_ASC);
        $objDTO->setBolExclusaoLogica(false);

        $objGenericoBD = new GenericoBD($objInfraIBanco);
        $arrObjPenRelTipoDocMapRecebidoDTO = $objGenericoBD->listar($objDTO);

        if(!empty($arrObjPenRelTipoDocMapRecebidoDTO)) {
            foreach($arrObjPenRelTipoDocMapRecebidoDTO as $objDTO) {
                $arrNumCodigoEspecie[] = $objDTO->getNumCodigoEspecie();
            }
        }

        if($dblCodigoEspecie > 0) {
            // Tira da lista de ignorados o que foi selecionado, em caso de edi��o
            $numIndice = array_search($dblCodigoEspecie, $arrNumCodigoEspecie);
            if($numIndice !== false) {
                unset($arrNumCodigoEspecie[$numIndice]);
            }
        }
        
        return $arrNumCodigoEspecie;
    }
    
    public function cadastrarControlado(PenRelTipoDocMapRecebidoDTO $objParamDTO){
          
        $objDTO = new PenRelTipoDocMapRecebidoDTO();
        $objDTO->setNumCodigoEspecie($objParamDTO->getNumCodigoEspecie());
        $objDTO->retTodos();
        
        $objBD = new GenericoBD($this->inicializarObjInfraIBanco());
        $objDTO = $objBD->consultar($objDTO);
        
        if(empty($objDTO)) {
            
            $objDTO = new PenRelTipoDocMapRecebidoDTO();
            $objDTO->setNumIdSerie($objParamDTO->getNumIdSerie());
            $objDTO->setNumCodigoEspecie($objParamDTO->getNumCodigoEspecie());
            $objDTO->setStrPadrao('S');
            $objBD->cadastrar($objDTO);  
        }
        else {
            
            $objDTO->setNumIdSerie($objParamDTO->getNumIdSerie()); 
            $objBD->alterar($objDTO);
        }
    }

    /**
     * Remove uma esp�cie documental da base de dados do SEI baseado em um c�digo de esp�cie do Barramento
     *
     * @param int $parNumIdEspecieDocumentla
     * @return void
     */
    protected function excluirPorEspecieDocumentalControlado($parNumIdEspecieDocumental)
    {        
        try {
            $objPenRelTipoDocMapRecebidoBD = new PenRelTipoDocMapRecebidoBD($this->getObjInfraIBanco());
            $objPenRelTipoDocMapRecebidoDTO = new PenRelTipoDocMapRecebidoDTO();
            $objPenRelTipoDocMapRecebidoDTO->setNumCodigoEspecie($parNumIdEspecieDocumental);
            $objPenRelTipoDocMapRecebidoDTO->retDblIdMap();
            
            foreach ($objPenRelTipoDocMapRecebidoBD->listar($objPenRelTipoDocMapRecebidoDTO) as $objDTO) {
                $objPenRelTipoDocMapRecebidoBD->excluir($objDTO);
            }
                                    
          }catch(Exception $e){
            throw new InfraException('Erro removendo Mapeamento de Tipos de Documento para recebimento pelo c�digo de esp�cie.',$e);
          }
    }


    protected function contarConectado(PenRelTipoDocMapRecebidoDTO $parObjPenRelTipoDocMapRecebidoDTO)
    {
        try {
          $objPenRelTipoDocMapRecebidoBD = new PenRelTipoDocMapRecebidoBD($this->getObjInfraIBanco());
          return $objPenRelTipoDocMapRecebidoBD->contar($parObjPenRelTipoDocMapRecebidoDTO);
        }catch(Exception $e){
          throw new InfraException('Erro contando Mapeamento de Tipos de Documento para Recebimento.',$e);
        }
    }    


    /**
     * Registra o mapeamento de esp�cies documentais para RECEBIMENTO com os Tipos de Documentos similares do SEI
     * 
     * A an�lise de simularidade utiliza o algor�tmo para calcular a dist�ncia entre os dois nomes
     * Mais informa��es sobre o algor�tmo podem ser encontradas no link abaixo:
     * https://www.php.net/manual/pt_BR/function.similar-text.php
     *
     * @return void
     */
    protected function mapearEspeciesDocumentaisRecebimentoControlado()
    {
        $objTipoDocMapRN = new TipoDocMapRN();
        $objPenRelTipoDocMapRecebidoRN = new PenRelTipoDocMapRecebidoRN();

        //Persentual de similaridade m�nimo aceito para que a esp�cie documental possa ser automaticamente mapeada
        $numPercentualSimilaridadeValido = 85;

        $arrTiposDocumentos = $objTipoDocMapRN->listarParesSerie(null, true);

        // Obter todas as esp�cies documentais do Barramento de Servi�os do PEN
        // Antes separa as esp�cies com nomes separados por '/' em itens diferentes
        $arrEspeciesDocumentais = array();
        $arrEspecies = $objTipoDocMapRN->listarParesEspecie($objPenRelTipoDocMapRecebidoRN->listarEmUso(null));
        foreach ($arrEspecies as $numCodigo => $strItem) {
            foreach (preg_split('/\//', $strItem) as $strNomeEspecie) {
                $arrEspeciesDocumentais[] = array("codigo" => $numCodigo, "nome" => $strNomeEspecie);
            }            
        }        

        foreach ($arrEspeciesDocumentais as $objEspecieDocumental) {
            $numIdEspecieDocumental = $objEspecieDocumental["codigo"];
            $strNomeEspecieDocumental = $objEspecieDocumental["nome"];
            $numMelhorSimilaridade = null;
            $numIdTipDocumentoSimilar = null;
            
            foreach ($arrTiposDocumentos as $numIdTipoDocumento => $strNomeTipoDocumento) {
                $numSimilaridade = 0;
                $numTamNomeTipoDoc = strlen($strNomeTipoDocumento);
                $numTamNomeEspecie = strlen($strNomeEspecieDocumental);                
                $numPosEspacoAdicional = strpos($strNomeTipoDocumento, ' ', min($numTamNomeEspecie, $numTamNomeTipoDoc));

                if($numPosEspacoAdicional){
                    // Avalia��o com tamanho reduzido, caso seja um termo composto
                    $numTamanhoReducao = max($numTamNomeEspecie, $numPosEspacoAdicional);
                    $strNomeTipoDocReduzido = substr($strNomeTipoDocumento, 0, $numTamanhoReducao);
                    similar_text(strtolower($strNomeEspecieDocumental), strtolower($strNomeTipoDocReduzido), $numSimilaridadeReduzido);
                    $numSimilaridade = $numSimilaridadeReduzido;
                } else {
                    // Avalia��o de termo em tamanho normal
                    similar_text(strtolower($strNomeEspecieDocumental), strtolower($strNomeTipoDocumento), $numSimilaridadeNormal);
                    $numSimilaridade = $numSimilaridadeNormal;
                }

                if($numMelhorSimilaridade < $numSimilaridade && $numSimilaridade > $numPercentualSimilaridadeValido) {
                    $numMelhorSimilaridade = $numSimilaridade;
                    $numIdTipDocumentoSimilar = $numIdTipoDocumento;
                }

            }

            if(isset($numMelhorSimilaridade)){
                // Realiza o mapeamento do tipo de documento com a esp�cie documental similar
                $objPenRelTipoDocMapRecebidoDTO = new PenRelTipoDocMapRecebidoDTO();
                $objPenRelTipoDocMapRecebidoDTO->setNumCodigoEspecie($numIdEspecieDocumental);
                if($objPenRelTipoDocMapRecebidoRN->contar($objPenRelTipoDocMapRecebidoDTO) == 0){
                    $objPenRelTipoDocMapRecebidoDTO->setNumIdSerie($numIdTipDocumentoSimilar);
                    $objPenRelTipoDocMapRecebidoRN->cadastrar($objPenRelTipoDocMapRecebidoDTO);
                }                
            }            
        }
    }
}
