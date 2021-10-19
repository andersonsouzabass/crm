<?php
/**
 * Cargos Ativos
 * @author  <Anderson Souza>
 */
class Periodicidade extends TRecord
{
    const TABLENAME = 'periodicidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');    
        parent::addAttribute('repete');    
    }


}
