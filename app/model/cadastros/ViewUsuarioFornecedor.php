<?php

class ViewUsuarioFornecedor extends TRecord
{
    const TABLENAME = 'view_fornecedor_usuario';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);       
        parent::addAttribute('nome');
        parent::addAttribute('system_user_id');
    }
}
