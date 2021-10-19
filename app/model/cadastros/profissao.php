<?php
/**
 * Tabela de registro de unidades de medida dos produtos
 * @author  <Anderson Souza>
 */
class Profissao extends TRecord
{
    const TABLENAME = 'profissao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('profissao');
        parent::addAttribute('ativo');
    }


}
