<?php
/**
 * Motivo SAC Active Record
 * @author  <Anderson Souza - (81) 99703-2438>
 */
class MotivoSac extends TRecord
{
    const TABLENAME = 'motivo_sac';
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
        parent::addAttribute('motivo');
        parent::addAttribute('produto_id');
        parent::addAttribute('ativo');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
    }
    
    public function get_pessoa()
    {
        return Pessoa::find($this->pessoa_id);
    }

    public function get_produto()
    {
        return Produto::find($this->produto_id);
    }
    
}
