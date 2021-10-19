<?php
/**
 * PessoaForm
 *
 * 
 * @author     Anderson Souza
 */
class ProdutoForm extends TWindow
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
        parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Produto');
        $this->form->setFormTitle('Criar Produto');
        $this->form->setFieldSizes('100%');        
        $this->form->setProperty('style', 'margin:0;border:0');
        $this->form->setClientValidation(true);

        // create the form fields
        $id = new TEntry('id');

        $fornecedor = new TDBUniqueSearch('pessoa_id', 'erphouse', 'Pessoa', 'id', 'nome_fantasia');
        $fornecedor->setMinLength(0);
        
        $produto = new TEntry('produto');
        $valor_fixo = new TCombo('valor_fixo');
        $valor = new TEntry('valor');
        $desconto = new TEntry('desconto');
        $renova = new TCombo('renova');
        $unidade_medida_id = new TDBUniqueSearch('unidade_medida_id', 'erphouse', 'Unidade_Medida', 'id', 'unidade_medida');
        $unidade_medida_id->setMinLength(0);
        $valor_fixo->addItems( ['D' => 'Doação', 'v' => 'Venda' ] );
        $renova->addItems( ['sim' => 'Sim', 'não' => 'Não' ] );
        
        // adiciona campos ao formulário

        

        $row = $this->form->addFields(  [ new TLabel('Id'), $id ],
                                        [ new TLabel('Produto'), $produto ],
                                        [ new TLabel('Tipo de Produto'), $valor_fixo ]
                                        
                                        );
        $row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-4'];                        
        
        $row = $this->form->addFields(  [ new TLabel('UN'), $unidade_medida_id ],
                                        [ new TLabel('Fornecedor'), $fornecedor ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-8'];

        $row = $this->form->addFields(  [ new TLabel('Valor'), $valor ],
                                        [ new TLabel('Desconto'), $desconto ],
                                        [ new TLabel('Renova'), $renova ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-4'];
        $row = $this->form->addFields(   );
        $row->layout = ['col-sm-6'];
        
        // set sizes
        $id->setSize('17%');

        $fornecedor->setSize('100%');
        $valor_fixo->setSize('100%');

        $valor->setSize('100%');
        $desconto->setSize('100%');        

        $unidade_medida_id->setSize('17%');
        
        //Validações
        $id->setEditable(FALSE);
        $produto->addValidation('Produto', new TRequiredValidator);
        $valor_fixo->addValidation('Tipo de Produto', new TRequiredValidator);
        
        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save' );
        $btn->class = 'btn btn-sm btn-primary';        
        $this->form->addActionLink('Limpar', new TAction(array($this, 'onEdit')),  'fa:eraser red' );
        $this->form->addActionLink('Cancelar', new TAction(array('ProdutoList','onReload')),  'fa:times red' );
        $this->form->addHeaderActionLink( 'Fechar',  new TAction(['ProdutoList', 'onReload']), 'fa:times red' );

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
            
            $object = new Produto;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->ativo = 'sim';
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
                $object = new Produto($key);
                
                $object->produto_id = Produto::where('pessoa_id', '=', $object->id)->getIndexedArray('produto_id');
                
                $this->form->setData($object);
                
                           
                // force fire events
                $data = new stdClass;
                //$data->unidade_medida_id = $object->unidade_medida->id;
                $data->unidade_medida_id = $object->unidade_medida_id;
                TForm::sendData('form_Produto', $data);
                
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
