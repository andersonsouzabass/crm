<?php
/**
 * PessoaForm
 *
 * 
 * @author     Anderson Souza
 */
class MotivoSacForm extends TWindow
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::setSize(0.6, null);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_MotivoSac');
        $this->form->setFormTitle('Criar Motivo de SAC');
        $this->form->setProperty('style', 'margin:0;border:0');
        $this->form->setClientValidation(true);

        // create the form fields
        $id = new TEntry('id');

        $produto = new TDBUniqueSearch('produto_id', 'erphouse', 'Produto', 'id', 'produto');
        $produto->setMinLength(0);
        
        $motivo = new TEntry('motivo');
        $ativo = new TCombo('ativo');       
        $ativo->addItems( ['sim' => 'Ativo', 'não' => 'Inativo' ] );
        
        
        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ]);
        $this->form->addFields( [ new TLabel('Produto') ], [ $produto ]);
        $this->form->addFields( [ new TLabel('Motivo') ], [ $motivo ], [ new TLabel('Ativo') ], [ $ativo ]);
                
        // set sizes
        $id->setSize('17%');

        $produto->setSize('100%');
        
        $motivo->setSize('100%');
        $ativo->setSize('100%');     

        //Validações
        $id->setEditable(FALSE);
        $produto->addValidation('Produto', new TRequiredValidator);
        $motivo->addValidation('Motivo', new TRequiredValidator);
        $ativo->addValidation('Ativo', new TRequiredValidator);
        //$desconto->addValidation('Desconto', new TRequiredValidator);
        //$unidade_medida_id->addValidation('UN', new TRequiredValidator);

        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save' );
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink('Limpar', new TAction(array($this, 'onEdit')),  'fa:eraser red' );
        $this->form->addActionLink('Cancelar', new TAction(array('MotivoSacList','onReload')),  'fa:times red' );
        $this->form->addHeaderActionLink( 'Fechar',  new TAction(['MotivoSacList', 'onReload']), 'fa:times red' );

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('erphouse'); // open a transaction
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new MotivoSac;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];
                TTransaction::open('erphouse');
                $object = new MotivoSac($key);
                
                $object->motivo_sac_id = MotivoSac::where('produto_id', '=', $object->id)->getIndexedArray('motivo_sac_id');
                
                $this->form->setData($object);
                
                /*           
                // force fire events
                $data = new stdClass;
                $data->unidade_medida_id = $object->unidade_medida->id;
                $data->unidade_medida_id = $object->unidade_medida_id;
                TForm::sendData('form_MotivoSac', $data);
                */
                TTransaction::close();
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
        /**
     * Closes window
     */
    public static function onClose()
    {   
        parent::closeWindow();        
    }
}
