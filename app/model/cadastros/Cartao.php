<?php
/**
 * 
 */
class Cartao extends TRecord
{
    const TABLENAME = 'bd_cartao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cliente_id');
        parent::addAttribute('contrato_id');
        parent::addAttribute('numero');
        parent::addAttribute('validade');
        parent::addAttribute('ccv');
        parent::addAttribute('nome');
        parent::addAttribute('bandeira');
    }
}
