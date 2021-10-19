<?php
/**
 * Cliente Active Record
 * @author  Anderson Lopes Souza (81) 997032438
 */
class Cliente extends TRecord
{
    const TABLENAME = 'cliente';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    const CACHECONTROL = 'TAPCache';
    
    const CREATEDAT = 'created_at';
    const UPDATEDAT = 'updated_at';

    private $contatos;
            
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        
        parent::addAttribute('cpf');
        parent::addAttribute('cnpj');

        parent::addAttribute('tipo_doc');
        parent::addAttribute('desc_doc');
        parent::addAttribute('doc');  
        parent::addAttribute('emissor');  
        parent::addAttribute('estado');        
        
        parent::addAttribute('renda');
        parent::addAttribute('classe_social');
        parent::addAttribute('profissao');
        parent::addAttribute('dt_nascimento');
        parent::addAttribute('sexo');
        parent::addAttribute('estado_civil');
        parent::addAttribute('observacao');
        parent::addAttribute('contato_resp');
    }
    
    

    public function addContato(Contato $object)
    {     
        $this->contatos[] = $object;   
    }
   
    
    /**
     * Returns the customer sales
     */
    public function getDoacoes()
    {
        return Venda::getVendaGeral($this->id);
    }
    
    

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        //$this->skills = array();
        $this->contatos = array();
    }
    
    /**
     * Method addContact
     * Add a Contact to the Customer
     * @param $object Instance of Contact
     */
    public function addContact(Contato $object)
    {
        $this->contatos[] = $object;
    }
    
    /**
     * Method getContacts
     * Return the Customer' Contact's
     * @return Collection of Contact
     */
    public function getContacts()
    {
        return $this->contatos;
    }

    public function getContatos()
    {
        return Contato::find($this->cliente_id);
        //return $this->contatos;
    }


    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
        $this->contatos = parent::loadComposite('Contato', 'cliente_id', $id);
    
        // load the object itself
        return parent::load($id);
    }

    /**
     * Store the object and its aggregates
     */
    public function store()
    {
        // store the object itself
        parent::store();    
        parent::saveComposite('Contato', 'cliente_id', $this->id, $this->contatos);
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        parent::deleteComposite('Contato', 'cliente_id', $id);
    
        // delete the object itself
        parent::delete($id);
    }

}
