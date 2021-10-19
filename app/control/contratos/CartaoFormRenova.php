<?php
/**
 */
class CartaoForm extends TWindow
{
    protected $form; // form
    protected $detail_list;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        parent::setSize(0.6, null);
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Cliente');
        $this->form->setFormTitle('Cliente');
        
        // master fields
        
        $id = new TEntry('id');
        $nome = new TEntry('nome');

        // detail fields
        $detail_uniqid = new THidden('detail_uniqid');
        $detail_id = new THidden('detail_id');
        $detail_numero = new TEntry('detail_numero');
        $detail_validade = new TEntry('detail_validade');
        $detail_ccv = new TEntry('detail_ccv');
        $detail_nome = new TEntry('detail_nome');
        $detail_bandeira = new TEntry('detail_bandeira');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        // master fields

        // master fields
        $row = $this->form->addFields(  [ new TLabel('Código'), $id ],
                                        [ new TLabel('Nome'), $nome ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-6']; 

        
        // detail fields
        $this->form->addContent( ['<h4>Details</h4><hr>'] );
        $this->form->addFields( [$detail_uniqid] );
        $this->form->addFields( [$detail_id] );
        
        $row = $this->form->addFields(  [ new TLabel('Titular do Cartão'), $detail_nome ],
                                        );
        $row->layout = ['col-sm-6']; 

        $row = $this->form->addFields(  [ new TLabel('Número do Cartão'), $detail_numero ]
                                        );
        $row->layout = ['col-sm-6']; 

        $row = $this->form->addFields(  [ new TLabel('Validade'), $detail_validade ],
                                        [ new TLabel('Cód. Segurança'), $detail_ccv ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm2'];

        $add = TButton::create('add', [$this, 'onDetailAdd'], 'Adicionar Cartão', 'fa:plus-circle green');
        $add->getAction()->setParameter('static','1');

        $row = $this->form->addFields(  [ new TLabel(''), $add ]
                                        );
        $row->layout = ['col-sm-1'];

        $id->setSize('100%');
        $nome->setSize('100%');
        $detail_uniqid->setSize('100%');
        $detail_id->setSize('100%');
        $detail_numero->setSize('100%');
        $detail_validade->setSize('100%');
        $detail_ccv->setSize('100%');
        $detail_nome->setSize('100%');
        //$detail_bandeira->setSize('100%');

        $detail_numero->setMask('9999-9999-9999-9999', true);
        $detail_validade->setMask('99/99', true);
        $detail_ccv->setMask('999', true);

        $nome->forceUpperCase();
        $detail_nome->forceUpperCase();
        
        $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->detail_list->setId('Cartao_list');
        $this->detail_list->generateHiddenFields();
        $this->detail_list->style = "min-width: 700px; width:100%;margin-bottom: 10px";
        
        // items
        $this->detail_list->addColumn( new TDataGridColumn('uniqid', 'Uniqid', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('id', 'Id', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('nome', 'Nome', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('numero', 'Numero', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('validade', 'Validade', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('ccv', 'Ccv', 'left', 100) );
        //$this->detail_list->addColumn( new TDataGridColumn('bandeira', 'Bandeira', 'left', 100) );

        // detail actions
        $action1 = new TDataGridAction([$this, 'onDetailEdit'] );
        $action1->setFields( ['uniqid', '*'] );
        
        $action2 = new TDataGridAction([$this, 'onDetailDelete']);
        $action2->setField('uniqid');
        
        // add the actions to the datagrid
        $this->detail_list->addAction($action1, _t('Edit'), 'fa:edit blue');
        $this->detail_list->addAction($action2, _t('Delete'), 'far:trash-alt red');
        
        $this->detail_list->createModel();
        
        $panel = new TPanelGroup;
        $panel->add($this->detail_list);
        $panel->getBody()->style = 'overflow-x:auto';
        $this->form->addContent( [$panel] );
        
        //Botoões do form
        $this->form->addHeaderActionLink( _t('Close'),  new TAction([$this, 'onClose']),  'fa:times red' );
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave'], ['static'=>'1']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Limpar',  new TAction([$this, 'onClear']), 'fa:eraser red');
        $this->form->addActionLink('Cancelar', new TAction([$this, 'onClose']),  'fa:times red' );
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    
    /**
     * Clear form
     * @param $param URL parameters
     */
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Add detail item
     * @param $param URL parameters
     */
    public function onDetailAdd( $param )
    {
        try
        {
            $this->form->validate();
            $data = $this->form->getData();
            
            /** validation sample
            if (empty($data->fieldX))
            {
                throw new Exception('The field fieldX is required');
            }
            **/
            
            $uniqid = !empty($data->detail_uniqid) ? $data->detail_uniqid : uniqid();
            
            $grid_data = [];
            $grid_data['uniqid'] = $uniqid;
            $grid_data['id'] = $data->detail_id;
            $grid_data['numero'] = $data->detail_numero;
            $grid_data['validade'] = $data->detail_validade;
            $grid_data['ccv'] = $data->detail_ccv;
            $grid_data['nome'] = $data->detail_nome;
            //$grid_data['bandeira'] = $data->detail_bandeira;
            
            // insert row dynamically
            $row = $this->detail_list->addItem( (object) $grid_data );
            $row->id = $uniqid;
            
            TDataGrid::replaceRowById('Cartao_list', $uniqid, $row);
            
            // clear detail form fields
            $data->detail_uniqid = '';
            $data->detail_id = '';
            $data->detail_numero = '';
            $data->detail_validade = '';
            $data->detail_ccv = '';
            $data->detail_nome = '';
            //$data->detail_bandeira = '';
            
            // send data, do not fire change/exit events
            TForm::sendData( 'form_Cliente', $data, false, false );
        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Edit detail item
     * @param $param URL parameters
     */
    public static function onDetailEdit( $param )
    {
        $data = new stdClass;
        $data->detail_uniqid = $param['uniqid'];
        $data->detail_id = $param['id'];
        $data->detail_numero = $param['numero'];
        $data->detail_validade = $param['validade'];
        $data->detail_ccv = $param['ccv'];
        $data->detail_nome = $param['nome'];
        //$data->detail_bandeira = $param['bandeira'];
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Cliente', $data, false, false );
    }
    
    /**
     * Delete detail item
     * @param $param URL parameters
     */
    public static function onDetailDelete( $param )
    {
        // clear detail form fields
        $data = new stdClass;
        $data->detail_uniqid = '';
        $data->detail_id = '';
        $data->detail_numero = '';
        $data->detail_validade = '';
        $data->detail_ccv = '';
        $data->detail_nome = '';
        //$data->detail_bandeira = '';
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Cliente', $data, false, false );
        
        // remove row
        TDataGrid::removeRowById('Cartao_list', $param['uniqid']);
    }
    
    /**
     * Load Master/Detail data from database to form
     */
    public function onEdit($param)
    {
        try
        {
            TTransaction::open('erphouse');
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new Cliente($key);
                $items  = Cartao::where('cliente_id', '=', $key)->load();
                
                foreach( $items as $item )
                {
                    $item->uniqid = uniqid();
                    $row = $this->detail_list->addItem( $item );
                    $row->id = $item->uniqid;
                }
                $this->form->setData($object);
                TTransaction::close();
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Save the Master/Detail data from form to database
     */
    public function onSave($param)
    {
        try
        {
            // open a transaction with database
            TTransaction::open('erphouse');
            
            $data = $this->form->getData();
            $this->form->validate();
            
            $master = new Cliente;
            $master->fromArray( (array) $data);
            $master->store();
            
            Cartao::where('cliente_id', '=', $master->id)->delete();
            
            if( $param['Cartao_list_numero'] )
            {
                foreach( $param['Cartao_list_numero'] as $key => $item_id )
                {
                    $detail = new Cartao;
                    $detail->numero  = $param['Cartao_list_numero'][$key];
                    $detail->validade  = $param['Cartao_list_validade'][$key];
                    $detail->ccv  = $param['Cartao_list_ccv'][$key];
                    $detail->nome  = $param['Cartao_list_nome'][$key];
                    //$detail->bandeira  = $param['Cartao_list_bandeira'][$key];
                    $detail->cliente_id = $master->id;
                    $detail->store();
                }
            }
            TTransaction::close(); // close the transaction
            
            TForm::sendData('form_Cliente', (object) ['id' => $master->id]);
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback();
        }
    }

    public static function onClose()
    {
        parent::closeWindow();
    }

}
