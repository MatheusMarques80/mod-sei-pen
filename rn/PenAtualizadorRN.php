<?php
/**
 * Atualizador abstrato para sistema do SEI para instalar/atualizar o m�dulo PEN
 * 
 * @autor Join Tecnologia
 */
abstract class PenAtualizadorRN extends InfraRN  {
    
    const VER_NONE = '0.0.0';// Modulo n�o instalado
    const VER_001 = '0.0.1';
    const VER_002 = '0.0.2';
    const VER_003 = '0.0.3';
    const VER_004 = '0.0.4';
    const VER_005 = '0.0.5';
    const VER_006 = '0.0.6';
    const VER_007 = '0.0.7';
   // const VER_008 = '0.0.8';
    
    protected $sei_versao;
    
    /**
     * @var string Vers�o m�nima requirida pelo sistema para instala��o do PEN
     */
    protected $versaoMinRequirida;
 
    /**
     * @var InfraIBanco Inst�ncia da classe de persist�ncia com o banco de dados
     */
    protected $objBanco;

    /**
     * @var InfraMetaBD Inst�ncia do metadata do banco de dados
     */
    protected $objMeta;

    /**
     * @var InfraDebug Inst�ncia do debuger
     */
    protected $objDebug;

    /**
     * @var integer Tempo de execu��o do script
     */
    protected $numSeg = 0;

    /**
     * @var array Argumentos passados por linha de comando ao script
     */
    protected $arrArgs = array();
    
    /**
     * Inicia a conex�o com o banco de dados
     */
    protected function inicializarObjMetaBanco(){
        if(empty($this->objMeta)) {
            $this->objMeta = new PenMetaBD($this->inicializarObjInfraIBanco());
        }
        return $this->objMeta;
    }
    
    /**
     * Adiciona uma mensagem ao output para o usu�rio
     * 
     * @return null
     */
    protected function logar($strMsg) {
        $this->objDebug->gravar($strMsg);
    }
    
    /**
     * Inicia o script criando um contator interno do tempo de execu��o
     * 
     * @return null
     */
    protected function inicializar($strTitulo) {

        $this->numSeg = InfraUtil::verificarTempoProcessamento();

        $this->logar($strTitulo);
    }

    /**
     * Finaliza o script informando o tempo de execu��o.
     * 
     * @return null
     */
    protected function finalizar() {

        $this->logar('TEMPO TOTAL DE EXECUCAO: ' . InfraUtil::verificarTempoProcessamento($this->numSeg) . ' s');
 
        $this->objDebug->setBolLigado(false);
        $this->objDebug->setBolDebugInfra(false);
        $this->objDebug->setBolEcho(false);
        
        print PHP_EOL;
        die();
    }
    
    /**
     * M�todo criado em fun��o de um bug com a InfraRN na linha 69, onde usamos
     * uma inst�ncia do banco do SIP e a vers�o esta no banco SEI, essa verifica��o
     * e lan�amento de uma excess�o pelos bancos terem nome diferentes tava o 
     * atualizado
     * 
     * @todo Migrar para classe PenMetaBD
     * @return null
     */
    protected function setVersao($strRegexVersao, $objInfraBanco = null){
        
       InfraDebug::getInstance()->gravarInfra(sprintf('[%s->%s]', get_class($this), __FUNCTION__)); 
        
      
       if($this->getVersao($objInfraBanco)) {
           
           $sql = sprintf("UPDATE infra_parametro SET valor = '%s' WHERE nome = '%s'", $strRegexVersao, $this->sei_versao); 
       }
       else {
           
          $sql = sprintf("INSERT INTO infra_parametro(nome, valor) VALUES('%s', '%s')", $this->sei_versao, $strRegexVersao); 
       }
       
       if(empty($objInfraBanco)) {
          
            $objInfraBanco = $this->inicializarObjInfraIBanco();
        }
        
        $objInfraBanco->executarSql($sql);
        
        return $strRegexVersao;
    }
    
    /**
     * Retorna a vers�o atual do modulo, se j� foi instalado
     * 
     * @todo Migrar para classe PenMetaBD
     * @param InfraBanco $objInfraBanco Conex�o com o banco SEI ou SIP
     * @return string
     */
    protected function getVersao($objInfraBanco = null){
        
        InfraDebug::getInstance()->gravarInfra(sprintf('[%s->%s]', get_class($this), __FUNCTION__)); 

        $sql = sprintf("SELECT valor FROM infra_parametro WHERE nome = '%s'", $this->sei_versao);

        if(empty($objInfraBanco)) {
          
            $objInfraBanco = $this->inicializarObjInfraIBanco();
        }
        
        $arrResultado = $objInfraBanco->consultarSql($sql);

        if(empty($arrResultado)) {
            return null;
        }
        
        $arrLinha = current($arrResultado);
        
        return $arrLinha['valor'];
    }
    
    /**
     * Verifica se o n�mero da vers�o � valido
     * 
     * @param string $strVersao Vers�o a ser instalada
     * @return bool
     */
    protected function isVersaoValida($strVersao = self::VER_NONE){
        
	if(empty($strVersao)) {
            return false;
        }

        // Remove os caracteres n�o n�mericos
        $strVersao = preg_replace('/\D+/', '', $strVersao);
        
        // Tem que no m�nimo 3 digitos
        if (strlen($strVersao) < 3) {
            return false;
        }

        return is_numeric($strVersao) ? true : false;
    }
    
    /**
     * Verifica se um param�tro existe, caso sim retorna o seu valor, sen�o
     * retorna o default especificado.
     * 
     * @param string $strChave Nome do param�tro
     * @param string $strParam String a ser formatada com o valor do param�tro
     * @param string $strParamDefault String que retorna caso o valor do 
     * param�tro n�o exista
     * @param bool $bolAlgumFiltroUsado Ponteiro de controle para verificar se 
     * pelo menos um param�tro foi encontrado
     * @return string
     */
    private function getStrArg($strChave = '', $strParam = '', $strParamDefault = '', &$bolAlgumFiltroUsado){
        
        if(array_key_exists($strChave, $this->arrArgs)) { 
            $bolAlgumFiltroUsado = true;
            return sprintf($strParam, str_pad($this->arrArgs[$strChave], 3, '0', STR_PAD_LEFT));
        }
        return $strParamDefault;
    }
    
    /**
     * Retorna a �ltima vers�o disponivel. Verifica as constantes que iniciam
     * com VER_
     */
    private function getUltimaVersao(){
        
        $objReflection = new ReflectionClass(__CLASS__);
        $arrVersao = array_flip(preg_grep('/^VER\_/', array_flip($objReflection->getConstants())));
        sort($arrVersao);
        return array_pop($arrVersao);
    }
    
    /**
     * Encontra os m�todos com nota��o para instalar a vers�o selecionada
     * 
     * @return string N�mero da vers�o
     */
    protected function executarControlado(){
        
        $this->inicializarObjMetaBanco()
            ->isDriverSuportado()
            ->isDriverPermissao()
            ->isVersaoSuportada(SEI_VERSAO, $this->versaoMinRequirida);
        
        $arrMetodo = array();
        
        // Retorna a �ltima vers�o disponibilizada pelo script. Sempre tenta atualizar
        // para vers�o mais antiga
        $strVersaoInstalar = $this->getUltimaVersao();
        
        //throw new InfraException($strVersaoInstalar);
        $objInfraBanco = $this->inicializarObjInfraIBanco();
        // Vers�o atual
        $strPenVersao = $this->getVersao($objInfraBanco);
        if(!$this->isVersaoValida($strPenVersao)) {
            // N�o instalado
            $strPenVersao = $this->setVersao(self::VER_NONE, $objInfraBanco); 
        }

        $numPenVersao = substr($strPenVersao, -1);
        $numVersaoInstalar = intval(substr($strVersaoInstalar, -1));
        
        $bolAlgumFiltroUsado = false;
        $strRegexRelease = $this->getStrArg('release', '(R%s)', '(R[0-9]{1,3})?', $bolAlgumFiltroUsado);
        $strRegexSprint = $this->getStrArg('sprint', '(S%s)', '(S[0-9]{1,3})?', $bolAlgumFiltroUsado);
        $strRegexItem = $this->getStrArg('user-story', '(US%s)', '(US|IW[0-9]{1,3})?', $bolAlgumFiltroUsado);
        $strRegexItem = $this->getStrArg('item-worker', '(IW%s)', $strRegexItem, $bolAlgumFiltroUsado);
        
        // Instalar todas atualiza��es
        if($bolAlgumFiltroUsado === false) {

            $strRegexVersao = sprintf('[%d-%d]', ($numPenVersao + 1), $numVersaoInstalar); 
        }
        // Instalar somente a solicitada
        else {
            // Caso algum param�tro seja adicionado n�o deve passar para pr�xima vers�o
            $strVersaoInstalar = $strPenVersao;
            $strRegexVersao = intval(substr($strPenVersao, -1) + 1);
        }
        
        // instalarV[0-9]{1,2}[0-9](R[0-9]{1,3})?(S[0-9]{1,3})?(US|IW[0-9]{1,4})?
        $strRegex = sprintf('/^instalarV[0-9][0-9]%s%s%s%s/i',
            $strRegexVersao,
            $strRegexRelease,
            $strRegexSprint,
            $strRegexItem
        );

        // Tenta encontrar m�todos que iniciem com instalar
        $arrMetodo  = (array)preg_grep ($strRegex, get_class_methods($this)); 
        
        if(empty($arrMetodo)) {
            
            throw new InfraException(sprintf('NENHUMA ATUALIZACAO FOI ENCONTRADA SUPERIOR A VERSAO %s DO MODULO PEN', $strPenVersao));
        }
        else {
            
            foreach($arrMetodo as $strMetodo) {

                $this->{$strMetodo}();
            } 
        }
        $this->setVersao($strVersaoInstalar, $objInfraBanco);
        
        return $strVersaoInstalar;
    }
    
    /**
     * M�todo que inicia o processo
     */
    public function atualizarVersao() {
        
        $this->inicializar('INICIANDO ATUALIZACAO DO MODULO PEN NO SEI VERSAO '.SEI_VERSAO);
        
        try {

            $strRegexVersao = $this->executar();
            $this->logar('ATUALIZADA VERSAO: '.$strRegexVersao);  
        }
        catch(InfraException $e) {
            
            $this->logar('Erro: '.$e->getStrDescricao());
        }
        catch (\Exception $e) {
            
            $this->logar('Erro: '.$e->getMessage());
        }
        
        $this->finalizar();
    }
    
    /**
     * Construtor
     * 
     * @param array $arrArgs Argumentos enviados pelo script
     */
    public function __construct($arrArgs = array()) {
                
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');
        ob_implicit_flush();
        
        $this->arrArgs = $arrArgs;
        
        $this->inicializarObjMetaBanco();
        
        $this->objDebug = InfraDebug::getInstance();
        $this->objDebug->setBolLigado(true);
        $this->objDebug->setBolDebugInfra(true);
        $this->objDebug->setBolEcho(true);
        $this->objDebug->limpar();
    }
}