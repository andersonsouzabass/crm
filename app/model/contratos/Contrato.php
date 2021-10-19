<?php
/**
 * Contrato Active Record
 * @author  <your-name-here>
 */
class Contrato extends TRecord
{
    const TABLENAME = 'contrato';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('fornecedor_id');
        parent::addAttribute('cliente_id');
        parent::addAttribute('tipo_contrato_id');
        parent::addAttribute('ativo');
        parent::addAttribute('dt_inicio');
        parent::addAttribute('melhor_dia');
        parent::addAttribute('dt_fim');
        parent::addAttribute('obs');
        parent::addAttribute('produto_id');
        parent::addAttribute('periodicidade_id');
        parent::addAttribute('system_user_id');
        parent::addAttribute('gera_viagem');
        parent::addAttribute('endereco_contrato_id');
        parent::addAttribute('forma_pagamento');
        parent::addAttribute('efetivacao');
        parent::addAttribute('status');
    }

    public function get_enderecoontrato()
    {
        return EnderecoContrato::find($this->endereco_contrato_id);
    }

    public function get_systemuser()
    {
        return System_User::find($this->system_user_id);
    }

    public function get_produto()
    {
        return Produto::find($this->produto_id);
    }

    public function get_periodicidade()
    {
        return Periodicidade::find($this->periodicidade_id);
    }
    
    public function get_cliente()
    {
        return Cliente::find($this->cliente_id);
    }

    public function get_tipo_contrato()
    {
        return TipoContrato::find($this->tipo_contrato_id);
    }

    public function get_view_fornecedor_usuario()
    {
        return ViewUsuarioFornecedor::find($this->view_fornecedor_usuario_id);
    }

    public function get_fornecedor()
    {
        return Pessoa::find($this->fornecedor_id);
    }
    
    public function get_total()
    {
        return ContratoItem::where('contrato_id', '=', $this->id)->sumBy('total');
    }
    
    public function get_ultima_fatura()
    {
        return Fatura::where('cliente_id','=',$this->cliente_id)->where('total','=', $this->get_total())->orderBy('dt_fatura', 'desc')->first()->dt_fatura;
    }

    public function getItemsContrato()
    {
        return parent::loadAggregate('Contrato', 'ContratoItem', 'contrato_id', 'contrato_item_id', $this->id);
    }
}
