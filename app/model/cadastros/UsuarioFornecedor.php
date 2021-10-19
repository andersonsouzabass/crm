<?php
/**
 * Atribuir Fornecedor ao Usuário
 *
 *
 * @author     Anderson Souza
 */
class UsuarioFornecedor extends TRecord
{
    const TABLENAME = 'usuario_fornecedor';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('system_user_id');
        parent::addAttribute('fornecedor_id');
    }
}
