<?php
/**
 * ContratoItem Active Record
 * @author  <your-name-here>
 */
class ContratoItem extends TRecord
{
    const TABLENAME = 'contrato_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    private $produto;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('produto_id');
        parent::addAttribute('contrato_id');
        parent::addAttribute('venda_id');
        parent::addAttribute('valor');
        parent::addAttribute('quantidade');
        parent::addAttribute('total');
        parent::addAttribute('data_contrato');
        parent::addAttribute('data_venda');
        parent::addAttribute('status');
    }

     /**
     * Method set_product
     * Sample of usage: $sale_item->product = $object;
     * @param $object Instance of Product
     */
    /*
    public function set_produto(Produto $object)
    {
        $this->produto = $object;
        $this->produto_id = $object->id;
    }
    
    /**
     * Method get_product
     * Sample of usage: $sale_item->product->attribute;
     * @returns Product instance
     */
    /*
    public function get_produto()
    {
        // loads the associated object
        if (empty($this->produto))
            $this->produto = new Product($this->produto_id);
    
        // returns the associated object
        return $this->produto;
    }
    */

    


}
