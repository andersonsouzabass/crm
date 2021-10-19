<?php
/**
 * Atribuir Contato ao Cliente
 *
 *
 * @author     Anderson Souza
 */
class ContatoCliente extends TRecord
{
    const TABLENAME = 'contato_cliente';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('cliente_id');
        parent::addAttribute('contato_id');
    }
}
