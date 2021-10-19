<?php
/**
 * Tabela de registro de unidades de medida dos produtos
 * @author  <Anderson Souza>
 */
class Unidade_Medida extends TRecord
{
    const TABLENAME = 'unidade_medida';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('unidade_medida');
        parent::addAttribute('unidade_medida_desc');
    }


}
