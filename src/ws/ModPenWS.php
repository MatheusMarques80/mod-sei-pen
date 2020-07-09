<?

$dirSeiWeb = !defined("DIR_SEI_WEB") ? getenv("DIR_SEI_WEB") ?: __DIR__."/../../../../web" : DIR_SEI_WEB;
require_once $dirSeiWeb . '/SEI.php';

class ModPenWS extends InfraWS {

    public function getObjInfraLog(){
        return LogSEI::getInstance();
    }

    public function __call($func, $params)
    {
        try{
            SessaoSEI::getInstance(false);

            if ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR'] && $_SERVER['REMOTE_ADDR'] != '127.0.0.1'){
                throw new SoapFault('Server', "Acesso remoto a este servi�o n�o � permitido.");
            }

            if (!method_exists($this, $func.'Monitorado')) {
                throw new InfraException('Servi�o ['.get_class($this).'.'.$func.'] n�o encontrado.');
            }

            $ret = call_user_func_array(array($this, $func.'Monitorado'), $params);

            if ($debugWebServices==2) {
                LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug(),InfraLog::$DEBUG);
            }

            return $ret;

        }catch(Exception $e){
            $this->processarExcecao($e);
        }
    }


    /**
     * Servi�o web de inicializa��o dos workers do gearman respos�veis pelo processamento do recebimento de
     * processos do Barramento do PEN.
     *
     * Servi�o implementado via webservice para considerar o mesmo contexto de vari�veis e permiss�es do servidor web Apache
     * Aten��o: Servi�o somente pode ser chamado via localhost pela pr�pria aplica��o
     *
     * @param int $QtdeWorkers
     * @return void
     */
    protected function inicializarWorkersMonitorado($QtdeWorkers)
    {
        try{
            ProcessarPendenciasRN::inicializarWorkers($QtdeWorkers);
        }catch(Exception $e){
            throw new InfraException('Erro no servi�o de inicializa��o do workers de processamento de tarefas do Barramento PEN', $e);
        }
    }

    /**
     * Servi�o web de inicializa��o do monitoramento e processamento de tarefas do Barramento do PEN.
     * Quando o Gearman estiver configurado, todo o trabalho de processamento � delegado para ele
     *
     * Servi�o implementado via webservice para considerar o mesmo contexto de vari�veis e permiss�es do servidor web Apache
     * Aten��o: Servi�o somente pode ser chamado via localhost pela pr�pria aplica��o
     *
     * @param int $QtdeWorkers
     * @param bool $MonitoramentoAtivado
     * @param bool $SegundoPlano
     * @param bool $DebugAtivo
     * @return void
     */
    protected function inicializarMonitoramentoPendenciasMonitorado($QtdeWorkers, $MonitoramentoAtivado, $SegundoPlano, $DebugAtivo)
    {
        try{
            PendenciasTramiteRN::inicializarMonitoramentoPendencias(
                $QtdeWorkers,
                $MonitoramentoAtivado,
                $SegundoPlano,
                $DebugAtivo
            );
        }catch(Exception $e){
            throw new InfraException('Erro no servi�o de inicializa��o do monitoramento de pend�ncias', $e);
        }
    }
}

$servidorSoap = new BeSimple\SoapServer\SoapServer(
    "modpen.wsdl",
    array (
        'encoding'=>'ISO-8859-1',
        'soap_version' => SOAP_1_1)
    );

    $servidorSoap->setClass("ModPenWS");

    if ($_SERVER['REQUEST_METHOD']=='POST') {
        $servidorSoap->handle($HTTP_RAW_POST_DATA);
    }
