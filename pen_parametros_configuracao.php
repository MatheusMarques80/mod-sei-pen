<?
/**
 * Join Tecnologia
 */
try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    define('PEN_RECURSO_ATUAL', 'pen_parametros_configuracao');
    define('PEN_PAGINA_TITULO', 'Par�metros de Configura��o do M�dulo de Tramita��es PEN');

    $objPagina = PaginaSEI::getInstance();
    $objBanco = BancoSEI::getInstance();
    $objSessao = SessaoSEI::getInstance();

    $o = new PenRelHipoteseLegalEnvioRN();
    $os = new PenRelHipoteseLegalRecebidoRN();

    $objSessao->validarPermissao('pen_parametros_configuracao');

    $objPenParametroDTO = new PenParametroDTO();
    $objPenParametroDTO->retTodos();
    $objPenParametroDTO->setNumSequencia(null, InfraDTO::$OPER_DIFERENTE);
    $objPenParametroDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objPenParametroRN = new PenParametroRN();
    $retParametros = $objPenParametroRN->listar($objPenParametroDTO);

    /* Busca os dados para montar dropdown ( TIPO DE PROCESSO EXTERNO ) */
    $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
    $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
    $objTipoProcedimentoDTO->retStrNome();
    $objTipoProcedimentoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objTipoProcedimentoRN = new TipoProcedimentoRN();
    $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);

    /* Busca os dados para montar dropdown ( UNIDADE GERADORA DOCUMENTO RECEBIDO ) */
    $objUnidadeDTO = new UnidadeDTO();
    $objUnidadeDTO->retNumIdUnidade();
    $objUnidadeDTO->retStrSigla();
    $objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objUnidadeRN = new UnidadeRN();
    $arrObjUnidade = $objUnidadeRN->listarRN0127($objUnidadeDTO);

    if ($objPenParametroDTO===null){
        throw new InfraException("Registros n�o encontrados.");
    }

    switch ($_GET['acao']) {
        case 'pen_parametros_configuracao_salvar':
            try {
                $objPenParametroRN = new PenParametroRN();

                if (!empty(count($_POST['parametro']))) {
                    foreach ($_POST['parametro'] as $nome => $valor) {
                        $objPenParametroDTO = new PenParametroDTO();
                        $objPenParametroDTO->setStrNome($nome);
                        $objPenParametroDTO->retStrNome();

                        if($objPenParametroRN->contar($objPenParametroDTO) > 0) {
                            $objPenParametroDTO->setStrValor(trim($valor));
                            $objPenParametroRN->alterar($objPenParametroDTO);
                        }
                    }
                }

            } catch (Exception $e) {
                $objPagina->processarExcecao($e);
            }
            header('Location: ' . $objSessao->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;

        case 'pen_parametros_configuracao':
            $strTitulo = 'Par�metros de Configura��o do M�dulo de Tramita��es PEN';
            break;

        default:
            throw new InfraException("A��o '" . $_GET['acao'] . "' n�o reconhecida.");
    }

} catch (Exception $e) {
    $objPagina->processarExcecao($e);
}

//Monta os bot�es do topo
if ($objSessao->verificarPermissao('pen_parametros_configuracao_alterar')) {
    $arrComandos[] = '<button type="submit" id="btnSalvar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
}
$arrComandos[] = '<button type="button" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . $objPagina->formatarXHTML($objSessao->assinarLink('controlador.php?acao=pen_parametros_configuracao&acao_origem=' . $_GET['acao'])) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

$objPagina->montarDocType();
$objPagina->abrirHtml();
$objPagina->abrirHead();
$objPagina->montarMeta();
$objPagina->montarTitle($objPagina->getStrNomeSistema() . ' - ' . $strTitulo);
$objPagina->montarStyle();
$objPagina->abrirStyle();
?>
<?
$objPagina->fecharStyle();
$objPagina->montarJavaScript();
$objPagina->abrirJavaScript();
?>

function inicializar(){
    if ('<?= $_GET['acao'] ?>'=='pen_parametros_configuracao_selecionar'){
        infraReceberSelecao();
        document.getElementById('btnFecharSelecao').focus();
    }else{
        document.getElementById('btnFechar').focus();
    }
    infraEfeitoImagens();
    infraEfeitoTabelas();
}

<?
$objPagina->fecharJavaScript();
$objPagina->fecharHead();
$objPagina->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<style>
    .input-field {
        width: 35%;
        margin-bottom: 15px;
        margin-top: 2px;
    }

</style>

<form id="frmInfraParametroCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=$objSessao->assinarLink('controlador.php?acao='.$_GET['acao'].'_salvar&acao_origem='.$_GET['acao'])?>">
    <?
    $objPagina->montarBarraComandosSuperior($arrComandos);
    foreach ($retParametros as $parametro) {

        //Esse par�metro n�o aparece, por j� existencia de uma tela s� para altera��o do pr�prio.
        if ($parametro->getStrNome() != 'HIPOTESE_LEGAL_PADRAO') {
            //Constr�i o label
            ?> <label id="lbl<?= PaginaSEI::tratarHTML($parametro->getStrNome()); ?>" for="txt<?= PaginaSEI::tratarHTML($parametro->getStrNome()); ?>" accesskey="N" class="infraLabelObrigatorio"><?=  PaginaSEI::tratarHTML($parametro->getStrDescricao()); ?>:</label><br> <?php
        }

        //Constr�i o campo de valor
        switch ($parametro->getStrNome()) {

            //Esse par�metro n�o aparece, por j� existencia de uma tela s� para altera��o do pr�prio.
            case 'HIPOTESE_LEGAL_PADRAO':
                echo '';
                break;

            case 'PEN_SENHA_CERTIFICADO_DIGITAL':
                echo '<input type="password" id="PARAMETRO_'.$parametro->getStrNome().'" name="parametro['.$parametro->getStrNome().']" class="infraText input-field" value="'.$objPagina->tratarHTML($parametro->getStrValor()).'" onkeypress="return infraMascaraTexto(this,event);" tabindex="'.$objPagina->getProxTabDados().'" maxlength="100" />';
                break;

            case 'PEN_ENVIA_EMAIL_NOTIFICACAO_RECEBIMENTO':
                echo '<select id="PARAMETRO_PEN_ENVIA_EMAIL_NOTIFICACAO_RECEBIMENTO" name="parametro[PEN_ENVIA_EMAIL_NOTIFICACAO_RECEBIMENTO]" class="infraText input-field" >';
                echo '    <option value="S" ' . ($parametro->getStrValor() == 'S' ? 'selected="selected"' : '') . '>Sim</option>';
                echo '    <option value="N" ' . ($parametro->getStrValor() == 'N' ? 'selected="selected"' : '') . '>N�o</option>';
                echo '<select>';
                break;

            case 'PEN_TIPO_PROCESSO_EXTERNO':
                echo '<select name="parametro[PEN_TIPO_PROCESSO_EXTERNO]" class="infraText input-field" >';
                foreach ($arrObjTipoProcedimentoDTO as $procedimento) {
                    echo '<option ' . ($parametro->getStrValor() == $procedimento->getNumIdTipoProcedimento() ? 'selected="selected"' : '') . ' value="'.$procedimento->getNumIdTipoProcedimento().'">'.$procedimento->getStrNome().'</option>';
                }
                echo '<select>';
                break;

            case 'PEN_UNIDADE_GERADORA_DOCUMENTO_RECEBIDO':
                echo '<select name="parametro[PEN_UNIDADE_GERADORA_DOCUMENTO_RECEBIDO]" class="infraText input-field" >';
                foreach ($arrObjUnidade as $unidade) {
                    echo '<option ' . ($parametro->getStrValor() == $unidade->getNumIdUnidade() ? 'selected="selected"' : '') . ' value="'.$unidade->getNumIdUnidade().'">'.$unidade->getStrSigla().'</option>';
                }
                echo '<select>';
                break;


            case 'PEN_ID_REPOSITORIO_ORIGEM':
                try {
                    $objExpedirProcedimentosRN = new ExpedirProcedimentoRN();
                    $repositorios = $objExpedirProcedimentosRN->listarRepositoriosDeEstruturas();
                    $idRepositorioSelecionado = (!is_null($parametro->getStrValor())) ? $parametro->getStrValor() : '';
                    $strItensSelRepositorioEstruturas = InfraINT::montarSelectArray('', 'Selecione', $idRepositorioSelecionado, $repositorios);
                    echo '<select id="parametro[PEN_ID_REPOSITORIO_ORIGEM]" name="parametro[PEN_ID_REPOSITORIO_ORIGEM]" class="infraSelect input-field">';
                            echo $strItensSelRepositorioEstruturas;
                    echo '</select>';
                } catch (Exception $e) {
                    // Caso ocorra alguma falha na obten��o de dados dos servi�os do PEN, apresenta estilo de campo padr�o
                    echo '<input type="text" id="PARAMETRO_'.$parametro->getStrNome().'" name="parametro['.$parametro->getStrNome().']" class="infraText input-field" value="'.$objPagina->tratarHTML($parametro->getStrValor()).'" onkeypress="return infraMascaraTexto(this,event);" tabindex="'.$objPagina->getProxTabDados().'" maxlength="100" />';
                }

                break;


            default:
                echo '<input type="text" id="PARAMETRO_'.$parametro->getStrNome().'" name="parametro['.$parametro->getStrNome().']" class="infraText input-field" value="'.$objPagina->tratarHTML($parametro->getStrValor()).'" onkeypress="return infraMascaraTexto(this,event);" tabindex="'.$objPagina->getProxTabDados().'" maxlength="100" />';
                break;
        }
        echo '<br>';
    }
    ?>
</form>

<?
$objPagina->fecharBody();
$objPagina->fecharHtml();
?>
