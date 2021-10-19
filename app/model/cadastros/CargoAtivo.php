<?php
/**
 * Cargos Ativos
 * @author  <Anderson Souza>
 */
class CargoAtivo extends TRecord
{
    const TABLENAME = 'cargo_ativo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('name');
        parent::addAttribute('ativo');        
    }


}
