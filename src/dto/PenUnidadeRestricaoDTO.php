<?php

require_once DIR_SEI_WEB . '/SEI.php';

/**
 * Cria uma tabela de rela��o 1 para 1 para unidade com o intuito de adicionar
 * novos campos de configura��o para cada unidade utilizado somente pelo m�dulo
 * PEN
 * 
 * Crio a classe com extendida de UnidadeDTO em fun��o dos m�todos de UnidadeRN,
 * que for�a o hinting para UnidadeDTO, ent�o n�o gera erro usar PenUnidadeDTO
 * com o UnidadeBD e UnidadeRN
 * 
 *
 * @see http://php.net/manual/pt_BR/language.oop5.typehinting.php
 */
class PenUnidadeRestricaoDTO extends UnidadeDTO
{

  public function getStrNomeTabela()
  {
    return 'md_pen_unidade_restricao';
  }

  public function getStrNomeSequenciaNativa()
  {
    return 'md_pen_seq_unidade_restricao';
  }

  public function montar()
  {
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidade', 'id_unidade');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidadeRH', 'id_unidade_rh');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidadeRestricao', 'id_unidade_restricao');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'NomeUnidadeRestricao', 'nome_unidade_restricao');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidadeRHRestricao', 'id_unidade_rh_restricao');
    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'NomeUnidadeRHRestricao', 'nome_unidade_rh_restricao');
    $this->configurarPK('Id', InfraDTO::$TIPO_PK_INFORMADO);

    $this->configurarFK('IdUnidade', 'unidade', 'id_unidade');
  }
}