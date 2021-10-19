<?php
/**
 * ContratoItem Active Record
 * @author  <your-name-here>
 */
class LoteView extends TRecord
{
    const TABLENAME = 'view_contratos';
    const PRIMARYKEY= 'contrato_id';
    const IDPOLICY =  'max'; // {max, serial}
    
    private $produto;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cliente_id');
        parent::addAttribute('cliente');
        parent::addAttribute('fornecedor');
        parent::addAttribute('dt_inicio');
        parent::addAttribute('dt_fim');
        parent::addAttribute('data_prog');
        parent::addAttribute('forma_pagamento');
        parent::addAttribute('ativo');
        parent::addAttribute('total_efetivado');
    }
}