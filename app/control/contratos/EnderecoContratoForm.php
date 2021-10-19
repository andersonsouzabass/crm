<?php
/**
 * EnderecoContratoForm Master/Detail
 * @author  <your name here>
 */
class EnderecoContratoForm extends TWindow
{
    protected $form; // form
    protected $detail_list;    
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();

        parent::setSize(0.7, null);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_EnderecoContrato');
        $this->form->setFormTitle('Criar Endereços');
        
        // master fields
        $id = new TEntry('id');
        $fornecedor_id = new TDBCombo('fornecedor_id', 'erphouse', 'Pessoa', 'id', 'nome');
        $cliente_id = new TDBCombo('cliente_id', 'erphouse', 'Cliente', 'id', 'nome');

        // detail fields
        $detail_uniqid = new THidden('detail_uniqid');
        $detail_id = new THidden('detail_id');
        $detail_cep = new TEntry('detail_cep');
        $detail_logradouro = new TEntry('detail_logradouro');
        $detail_numero = new TEntry('detail_numero');
        $detail_complemento = new TEntry('detail_complemento');
        $detail_bairro = new TEntry('detail_bairro');
        $detail_cidade = new TEntry('detail_cidade');
        $detail_estado = new TEntry('detail_estado');
        $detail_referencia = new TText('detail_referencia');
        $detail_tipo = new TEntry('detail_tipo');


        //Configurações dos campos
        // master fields
        $id->setSize('100%');
        $fornecedor_id->setSize('100%');
        $cliente_id->setSize('100%');

        // detail fields
        $detail_uniqid->setSize('100%');
        $detail_id->setSize('100%');
        $detail_cep->setSize('100%');
        $detail_logradouro->setSize('100%');
        $detail_numero->setSize('100%');
        $detail_complemento->setSize('100%');
        $detail_bairro->setSize('100%');
        $detail_cidade->setSize('100%');
        $detail_estado->setSize('100%');
        $detail_referencia->setSize('100%');
        $detail_tipo->setSize('100%');

        $detail_cep->setExitAction( new TAction([ $this, 'onExitCEP']) );
        $detail_cep->setMask('99.999-999');


        TEntry::disableField('form_EnderecoContrato', 'detail_logradouro');
        TEntry::disableField('form_EnderecoContrato', 'detail_bairro');
        TEntry::disableField('form_EnderecoContrato', 'detail_cidade');
        TEntry::disableField('form_EnderecoContrato', 'detail_estado');


        if (!empty($id))
        {
            $id->setEditable(FALSE);
            $fornecedor_id->setEditable(FALSE);
            $cliente_id->setEditable(FALSE);
        }
        
        // master fields

        $row = $this->form->addFields(  [ new TLabel('Contrato'), $id ],
                                        [ new TLabel('Cliente'), $cliente_id ],
                                        [ new TLabel('Fornecedor'), $fornecedor_id ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-5', 'col-sm-5'];
        
        // detail fields
        //$this->form->addContent( ['<h4>Adicionar Endereços</h4><hr>'] );
        //$this->form->addContent( [''] );
        $this->form->addFields( [$detail_uniqid] );
        $this->form->addFields( [$detail_id] );
        
        $row = $this->form->addFields(  [ new TLabel('Cep'), $detail_cep ],
                                        [ new TLabel('Logradouro'), $detail_logradouro ],
                                        [ new TLabel('Numero'), $detail_numero ],
                                        [ new TLabel('Comp.'), $detail_complemento ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-2', 'col-sm-4'];

        $row = $this->form->addFields(  [ new TLabel('Bairro'), $detail_bairro ],
                                        [ new TLabel('Cidade'), $detail_cidade ],
                                        [ new TLabel('Estado'), $detail_estado ]
                                        );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];

        $row = $this->form->addFields(  [ new TLabel('Referência'), $detail_referencia ]
                                        );
        $row->layout = ['col-sm-12'];

        //Botão para inserir os dados dos endereços no datagrid
        $add = TButton::create('add', [$this, 'onDetailAdd'], 'Adicionar Endereço', 'fa:plus-circle green');
        $add->getAction()->setParameter('static','1');
        
        $row = $this->form->addFields(  [ new TLabel('Tipo'), $detail_tipo ],
                                        [ new TLabel(' '), $add ]
                                        );
        $row->layout = ['col-sm-8', 'col-sm-1'];


        
        $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->detail_list->setId('EnderecoContrato_list');
        $this->detail_list->generateHiddenFields();
        $this->detail_list->style = "min-width: 700px; width:100%;margin-bottom: 10px";
        
        // items
        $this->detail_list->addColumn( new TDataGridColumn('uniqid', 'Uniqid', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('id', 'Id', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('cep', 'Cep', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('logradouro', 'Logradouro', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('numero', 'Numero', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('complemento', 'Complemento', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('bairro', 'Bairro', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('cidade', 'Cidade', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('estado', 'Estado', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('referencia', 'Referencia', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('tipo', 'Tipo', 'left', 100) );

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
        
        $btn = $this->form->addHeaderAction( _t('Close'), new TAction([$this, 'VoltarEndereco'], ['static'=>'1']),  'fa:times red' );
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave'], ['static'=>'1']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Limpar', new TAction([$this, 'onClear']), 'fa:eraser red');
        $btn = $this->form->addAction('Cancelar', new TAction([$this, 'VoltarEndereco'], ['static'=>'1']),  'fa:times red' );
        
        
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
            $grid_data['cep'] = $data->detail_cep;
            $grid_data['logradouro'] = $data->detail_logradouro;
            $grid_data['numero'] = $data->detail_numero;
            $grid_data['complemento'] = $data->detail_complemento;
            $grid_data['bairro'] = $data->detail_bairro;
            $grid_data['cidade'] = $data->detail_cidade;
            $grid_data['estado'] = $data->detail_estado;
            $grid_data['referencia'] = $data->detail_referencia;
            $grid_data['tipo'] = $data->detail_tipo;
            
            // insert row dynamically
            $row = $this->detail_list->addItem( (object) $grid_data );
            $row->id = $uniqid;
            
            TDataGrid::replaceRowById('EnderecoContrato_list', $uniqid, $row);
            
            // clear detail form fields
            $data->detail_uniqid = '';
            $data->detail_id = '';
            $data->detail_cep = '';
            $data->detail_logradouro = '';
            $data->detail_numero = '';
            $data->detail_complemento = '';
            $data->detail_bairro = '';
            $data->detail_cidade = '';
            $data->detail_estado = '';
            $data->detail_referencia = '';
            $data->detail_tipo = '';
            
            // send data, do not fire change/exit events
            TForm::sendData( 'form_EnderecoContrato', $data, false, false );
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
        $data->detail_cep = $param['cep'];
        $data->detail_logradouro = $param['logradouro'];
        $data->detail_numero = $param['numero'];
        $data->detail_complemento = $param['complemento'];
        $data->detail_bairro = $param['bairro'];
        $data->detail_cidade = $param['cidade'];
        $data->detail_estado = $param['estado'];
        $data->detail_referencia = $param['referencia'];
        $data->detail_tipo = $param['tipo'];
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_EnderecoContrato', $data, false, false );
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
        $data->detail_cep = '';
        $data->detail_logradouro = '';
        $data->detail_numero = '';
        $data->detail_complemento = '';
        $data->detail_bairro = '';
        $data->detail_cidade = '';
        $data->detail_estado = '';
        $data->detail_referencia = '';
        $data->detail_tipo = '';
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_EnderecoContrato', $data, false, false );
        
        // remove row
        TDataGrid::removeRowById('EnderecoContrato_list', $param['uniqid']);
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
                $idForm = $key;
                $object = new Contrato($key);
                $items  = EnderecoContrato::where('contrato_id', '=', $key)->load();
                
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
            
            $master = new Contrato;
            $master->fromArray( (array) $data);
            $master->store();
            
            EnderecoContrato::where('contrato_id', '=', $master->id)->delete();
            
            if( $param['EnderecoContrato_list_cep'] )
            {
                foreach( $param['EnderecoContrato_list_cep'] as $key => $item_id )
                {
                    $detail = new EnderecoContrato;
                    $detail->cep  = $param['EnderecoContrato_list_cep'][$key];
                    $detail->logradouro  = $param['EnderecoContrato_list_logradouro'][$key];
                    $detail->numero  = $param['EnderecoContrato_list_numero'][$key];
                    $detail->complemento  = $param['EnderecoContrato_list_complemento'][$key];
                    $detail->bairro  = $param['EnderecoContrato_list_bairro'][$key];
                    $detail->cidade  = $param['EnderecoContrato_list_cidade'][$key];
                    $detail->estado  = $param['EnderecoContrato_list_estado'][$key];
                    $detail->referencia  = $param['EnderecoContrato_list_referencia'][$key];
                    $detail->tipo  = $param['EnderecoContrato_list_tipo'][$key];
                    $detail->cliente_id = $master->cliente_id;
                    $detail->contrato_id = $master->id;
                    $detail->store();
                }
            }
            TTransaction::close(); // close the transaction
            
            TForm::sendData('form_EnderecoContrato', (object) ['id' => $master->id]);
            
            new TMessage('info', 'Endereços Atualizados com Sucesso!');

        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback();
        }
    }

    public static function onExitCEP($param)
    {
        session_write_close();
        
        try
        {
            $cep = preg_replace('/[^0-9]/', '', $param['detail_cep']);
            $url = 'https://viacep.com.br/ws/'.$cep.'/json/unicode/';
            
            $content = @file_get_contents($url);
            
            if ($content !== false)
            {
                $cep_data = json_decode($content);
                
                $data = new stdClass;
                if (is_object($cep_data) && empty($cep_data->erro))
                {
                    /*
                    TTransaction::open('erphouse');
                    $estado = Estado::where('uf', '=', $cep_data->uf)->first();
                    $cidade = Cidade::where('codigo_ibge', '=', $cep_data->ibge)->first();
                    TTransaction::close();
                    */
                    
                    $data->detail_logradouro  = $cep_data->logradouro;
                    $data->detail_complemento = $cep_data->complemento;
                    $data->detail_bairro      = $cep_data->bairro;
                    $data->detail_estado   = $cep_data->uf;
                    $data->detail_cidade   = $cep_data->localidade;
                    
                    TForm::sendData('form_EnderecoContrato', $data, false, true);
                }
                else
                {
                    $data->detail_logradouro  = '';
                    $data->detail_complemento  = '';
                    $data->detail_bairro  = '';
                    $data->detail_estado  = '';
                    $data->detail_cidade  = '';
                    
                    TEntry::enableField('form_EnderecoContrato', 'detail_logradouro');
                    TEntry::enableField('form_EnderecoContrato', 'detail_bairro');
                    TEntry::enableField('form_EnderecoContrato', 'detail_cidade');
                    TEntry::enableField('form_EnderecoContrato', 'detail_estado');

                    TForm::sendData('form_EnderecoContrato', $data, false, true);
                }
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    
    public function VoltarEndereco($param)
    {
        try
        {
            // open a transaction with database
            TTransaction::open('erphouse');
            
            $data = $this->form->getData();
            $this->form->validate();
            
            $master = new Contrato;
            $master->fromArray( (array) $data);
            $master->store();
            
            EnderecoContrato::where('contrato_id', '=', $master->id)->delete();
            
            if( $param['EnderecoContrato_list_cep'] )
            {
                foreach( $param['EnderecoContrato_list_cep'] as $key => $item_id )
                {
                    $detail = new EnderecoContrato;
                    $detail->cep  = $param['EnderecoContrato_list_cep'][$key];
                    $detail->logradouro  = $param['EnderecoContrato_list_logradouro'][$key];
                    $detail->numero  = $param['EnderecoContrato_list_numero'][$key];
                    $detail->complemento  = $param['EnderecoContrato_list_complemento'][$key];
                    $detail->bairro  = $param['EnderecoContrato_list_bairro'][$key];
                    $detail->cidade  = $param['EnderecoContrato_list_cidade'][$key];
                    $detail->estado  = $param['EnderecoContrato_list_estado'][$key];
                    $detail->referencia  = $param['EnderecoContrato_list_referencia'][$key];
                    $detail->tipo  = $param['EnderecoContrato_list_tipo'][$key];
                    $detail->cliente_id = $master->cliente_id;
                    $detail->contrato_id = $master->id;
                    $detail->store();
                }
            }
            TTransaction::close(); // close the transaction
            
            TForm::sendData('form_EnderecoContrato', (object) ['id' => $master->id]);
            
            //new TMessage('info', 'Endereços Atualizados com Sucesso!');

            $script = "__adianti_load_page('engine.php?class=ContratoForm&method=onEdit&id={$master->id}&key={$master->id}');";
            TScript::create($script);

        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback();
        }
    }
    
}
