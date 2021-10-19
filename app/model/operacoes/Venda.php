<?php
/**
 * Tabela de Consulta da venda 
 * @author  Anderson Souza
 */
class Venda extends TRecord
{
    const TABLENAME = 'view_contratos';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('total');
        parent::addAttribute('contrato_id');
        parent::addAttribute('cliente_id');
        parent::addAttribute('ativo');
        parent::addAttribute('efetivacao');
        parent::addAttribute('dt_inicio');
        parent::addAttribute('dt_fim');
    }
}
?>