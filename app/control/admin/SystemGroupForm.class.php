<?php
/**
 * SystemGroupForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemGroupForm extends TWindow
{
    protected $form; // form
    protected $program_list;
    protected $user_list;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();

        
        parent::setSize( 0.7, null);
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();
        

        // creates the form
        $this->form = new BootstrapFormBuilder('form_System_group');
        $this->form->setFormTitle( 'Criar Cargo' );

        // create the form fields
        $id   = new TEntry('id');
        $name = new TEntry('name');
        
        
        // define the sizes
        $id->setSize('30%');
        $name->setSize('70%');
        

        // validations
        $name->addValidation('name', new TRequiredValidator);
        
        // outras propriedades
        $id->setEditable(false);
        
        $this->form->addFields( [new TLabel('ID')], [$id]);
        $this->form->addFields( [new TLabel(_t('Name'))], [$name]);
        // Fim do form

        //Início do datagrid com as astribuiçoes do cargo
        //$this->form->addFields( [new TFormSeparator('  ')] );
        $this->atribuicoes_list = new TCheckList('atribuicoes_list');
        $this->atribuicoes_list->setIdColumn('id');
        $this->atribuicoes_list->addColumn('id',    'ID',    'center',  '10%');
        $col_name    = $this->atribuicoes_list->addColumn('name', _t('Name'),    'left',   '90%');

        $this->atribuicoes_list->setHeight(150);
        $this->atribuicoes_list->makeScrollable();
        
        $col_name->enableSearch(); 
        $search_name = $col_name->getInputSearch();
        $search_name->placeholder = _t('Search');
        $search_name->style = 'width:50%;margin-left: 4px; border-radius: 4px';
        
        $this->form->addFields( [new TFormSeparator('Atribuições do Cargo')] );
        $this->form->addFields( [$this->atribuicoes_list] );
        
        TTransaction::open('permission');
        $this->atribuicoes_list->addItems( SystemProgram::get() );
        TTransaction::close();

        //Fim do datagrid


        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save' );
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink('Limpar', new TAction(array($this, 'onEdit')),  'fa:eraser red' );
        $this->form->addActionLink('Cancelar', new TAction(array('SystemGroupList','onReload')),  'fa:times red' );
        $this->form->addHeaderActionLink( 'Fechar',  new TAction(['SystemGroupList', 'onReload']), 'fa:times red' );

        $container = new TVBox;
        $container->style = 'width:100%';
        $container->add($this->form);

        // add the form to the page
        parent::add($container);
        
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave($param)
    {
        try
        {
            // open a transaction with database 'permission'
            TTransaction::open('permission');
            
            $data = $this->form->getData();
            $this->form->setData($data);
            
            // get the form data into an active record System_group
            $object = new SystemGroup;
            $object->fromArray( (array) $data );
            $object->store();
            $object->clearParts();
            
            if (!empty($data->atribuicoes_list))
            {
                foreach ($data->atribuicoes_list as $program_id)
                {
                    $object->addSystemProgram( new SystemProgram( $program_id ) );
                }
            }
            
            if (!empty($data->user_list))
            {
                foreach ($data->user_list as $user_id)
                {
                    $object->addSystemUser( new SystemUser( $user_id ) );
                }
            }
            
            $data = new stdClass;
            $data->id = $object->id;
            TForm::sendData('form_System_group', $data);
            
            TTransaction::close(); // close the transaction
            new TMessage('info', 'Cargo Criado'); // shows the success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database 'permission'
                TTransaction::open('permission');
                
                // instantiates object System_group
                $object = new SystemGroup($key);
                
                $program_ids = array();
                foreach ($object->getSystemPrograms() as $program)
                {
                    $program_ids[] = $program->id;
                }
                
                $object->atribuicoes_list = $program_ids;
                
                
                $user_ids = array();
                foreach ($object->getSystemUsers() as $user)
                {
                    $user_ids[] = $user->id;
                }
                
                $object->user_list = $user_ids;
                
                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
