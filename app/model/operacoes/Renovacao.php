<?php
/**
 * Renovação de Doação Active Record
 * @author  Anderson Lopes Souza (81) 997032438
 */
class Renovacao extends TRecord
{
    const TABLENAME = 'venda';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CACHECONTROL = 'TAPCache';
    
    const CREATEDAT = 'created_at';
    
    private $clientes;
    private $pessoas;
    private $fornecedores;
            
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        
        parent::addAttribute('quantidade');
        parent::addAttribute('valor_unitario');
        parent::addAttribute('desconto');
        parent::addAttribute('valor_total');

        parent::addAttribute('venda_id');
        parent::addAttribute('atualiza');
        parent::addAttribute('status');
        parent::addAttribute('data_contato');

        parent::addAttribute('system_user_id');
            
    }
}