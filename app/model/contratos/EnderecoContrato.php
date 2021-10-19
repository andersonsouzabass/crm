<?php
/**
 * 
 */
class EnderecoContrato extends TRecord
{
    const TABLENAME = 'endereco_contrato';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cliente_id');
        parent::addAttribute('contrato_id');
        parent::addAttribute('cep');
        parent::addAttribute('logradouro');
        parent::addAttribute('numero');
        parent::addAttribute('complemento');
        parent::addAttribute('bairro');
        parent::addAttribute('cidade');
        parent::addAttribute('estado');
        parent::addAttribute('referencia');
        parent::addAttribute('tipo');
    }

    


}
