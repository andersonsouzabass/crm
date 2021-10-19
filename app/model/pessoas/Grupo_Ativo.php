<?php
/**
 * Grupo Active Record
 * @author  <your-name-here>
 */
class Grupo_Ativo extends TRecord
{
    const TABLENAME = 'perfil_fornecedor_ativo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('ativo');
    }


}
