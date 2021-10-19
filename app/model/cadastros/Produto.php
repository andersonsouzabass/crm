<?php
/**
 * Pessoa Active Record
 * @author  <Anderson Souza - (81) 99703-2438>
 */
class Produto extends TRecord
{
    const TABLENAME = 'produto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    const CREATEDAT = 'created_at';
    const UPDATEDAT = 'updated_at';
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('produto');
        parent::addAttribute('renova');
        parent::addAttribute('valor_fixo');
        parent::addAttribute('valor');
        parent::addAttribute('desconto');
        parent::addAttribute('unidade_medida_id');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
        parent::addAttribute('pessoa_id');
        parent::addAttribute('ativo');
    }
    
    public function get_pessoa()
    {
        return Pessoa::find($this->pessoa_id);
    }

    public function get_unidade_medida()
    {
        return Unidade_Medida::find($this->unidade_medida_id);
    }
    
}
