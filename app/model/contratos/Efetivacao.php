<?php
/**
 * Pessoa Active Record
 * @author  <Anderson Souza - (81) 99703-2438>
 */
class Efetivacao extends TRecord
{
    const TABLENAME = 'validacao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    const CREATEDAT = 'created_at';
    //const UPDATEDAT = 'updated_at';
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('contrato_id');
        parent::addAttribute('valor');
        parent::addAttribute('valor_renovado');
        parent::addAttribute('data_efet');
        parent::addAttribute('data_prog');
        parent::addAttribute('forma_pagamento');        
    }
}