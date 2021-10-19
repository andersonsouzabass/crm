<?php
/**
 * ParcelaForm Master/Detail
 * @author  <your name here>
 */
class ParcelaForm extends TPage
{
    protected $form; // form
    protected $detail_list;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');

        // creates the form
        $this->form = new BootstrapFormBuilder('form_Contrato_Parcela');
        $this->form->setFormTitle('Dividir Depósito');
        
        // master fields
        $id = new TEntry('id');
        $efetivacao = new TDate('efetivacao');
        $valor =  new TNumeric('Valida_valor', 2, ',', '.');
        $parcela = new TEntry('parcela');

        $efetivacao->setMask('dd/mm/yyyy');
        $efetivacao->setDatabaseMask('yyyy-mm-dd');

        $id->setSize('100%');
        $efetivacao->setSize('100%');
        $valor->setSize('100%');
        $parcela->setSize('100%');

        $parcela->setMaxLength(2);

        // detail fields
        $detail_uniqid = new THidden('detail_uniqid');
        $detail_id = new THidden('detail_id');
        $detail_data = new TDate('detail_data');
        $detail_valor_parcela = new TNumeric('detail_valor_parcela', 2, ',', '.');

        $detail_data->setSize('100%');
        $detail_valor_parcela->setSize('100%');

        $detail_data->setMask('dd/mm/yyyy');
        $detail_data->setDatabaseMask('yyyy-mm-dd');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        $row = $this->form->addFields(  [ new TLabel('Contrato'), $id ],
                                        [ new TLabel('Efetivacao'), $efetivacao ]                                        
                                        );
        $row->layout = ['col-sm-4', 'col-sm-4'];

        $row = $this->form->addFields(  [ new TLabel('Valor Total'), $valor ],
                                        [ new TLabel('Dividido em'), $parcela ]                                        
                                        );
        $row->layout = ['col-sm-4', 'col-sm-4'];
        
        // detail fields
        $this->form->addContent( ['<h4>Tabela de Parcelas</h4><hr>'] );
        $this->form->addFields( [$detail_uniqid] );
        $this->form->addFields( [$detail_id] );
        
        $row = $this->form->addFields(  [ new TLabel('Data'), $detail_data ],
                                        [ new TLabel('Parcela'), $detail_valor_parcela ]                                        
                                        );
        $row->layout = ['col-sm-4', 'col-sm-4'];

        $add = TButton::create('add', [$this, 'onDetailAdd'], 'Ajustar Parcela', 'fa:plus-circle green');
        $add->getAction()->setParameter('static','1');
        
        $row = $this->form->addFields(  [ $add ]
                                        );
        $row->layout = ['col-sm-1'];
        
        $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->detail_list->setId('Parcela_list');
        $this->detail_list->generateHiddenFields();
        $this->detail_list->style = "min-width: 400px; width:100%;margin-bottom: 10px";
        

        $column_dt_parcela = new TDataGridColumn('data', 'Data', 'left', 30);
        $column_dt_parcela->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });

        $col_valor_parcela = new TDataGridColumn('valor_parcela', 'Valor', 'left');
        // items
        $this->detail_list->addColumn( new TDataGridColumn('uniqid', 'Uniqid', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('id', 'Id', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( $column_dt_parcela );
        $this->detail_list->addColumn( $col_valor_parcela );

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

        //Formatando a colula valor
        $col_valor_parcela->enableTotal('sum', 'R$', 2, ',', '.');  
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ ' .number_format($value, 2, ',', '.');
            }
            return $value;
        };        
        $col_valor_parcela->setTransformer( $format_value );
        
        $this->form->addAction( 'Registrar',  new TAction([$this, 'onSave'], ['static'=>'1']), 'fa:save green');
        //$this->form->addAction( 'Clear', new TAction([$this, 'onClear']), 'fa:eraser red');
        $this->form->addAction( 'Fechar', new TAction([$this, 'onClose']), 'fa:times red');
        
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
            
            $uniqid = !empty($data->detail_uniqid) ? $data->detail_uniqid : uniqid();
            
            $grid_data = [];
            $grid_data['uniqid'] = $uniqid;
            $grid_data['id'] = $data->detail_id;
            $grid_data['data'] = $data->detail_data;
            $grid_data['valor_parcela'] = $data->detail_valor_parcela;
            
            $vParcelas = new stdClass;
            $vParcelas->valor = (float) str_replace(['.'], [','],$param['Valida_valor']); 
            
            var_dump($param['Valida_valor']);
            var_dump($param['efetivacao']);

            // insert row dynamically
            $row = $this->detail_list->addItem( (object) $grid_data );
            $row->id = $uniqid;
            
            TDataGrid::replaceRowById('Parcela_list', $uniqid, $row);
            
            // clear detail form fields
            $data->detail_uniqid = '';
            $data->detail_id = '';
            $data->detail_data = '';
            $data->detail_valor_parcela = '';
            
            // send data, do not fire change/exit events
            TForm::sendData( 'form_Contrato_Parcela', $data, false, false );
            TForm::sendData( 'form_Contrato_Parcela', $vParcelas, false, false );
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

        $data_fn = new DateTime($param['data']);
        $dt_result_fin = $data_fn->format('d/m/Y');

        $data->detail_data =  $dt_result_fin;
        $data->detail_valor_parcela = $param['valor_parcela'];

        $data->valor = $param['valor'];
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Contrato_Parcela', $data, false, false );
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
        $data->detail_data = '';
        $data->detail_valor_parcela = '';
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Contrato_Parcela', $data, false, false );
        
        // remove row
        TDataGrid::removeRowById('Parcela_list', $param['uniqid']);
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
                
                $object = new Contrato($key);
                /*
                $items  = ContratoItem::where('contrato_id', '=', $key)->load();
                
                foreach( $items as $item )
                {
                    $item->uniqid = uniqid();
                    $row = $this->detail_list->addItem( $item );
                    $row->id = $item->uniqid;
                }
                */
                $this->form->setData($object);

                //$$this->form->valor = $object->
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
            
            ContratoItem::where('contrato_id', '=', $master->id)->delete();
            
            if( $param['Parcela_list_data'] )
            {
                foreach( $param['Parcela_list_data'] as $key => $item_id )
                {
                    $detail = new ContratoItem;
                    $detail->data  = $param['Parcela_list_data'][$key];
                    $detail->valor_parcela  = $param['Parcela_list_valor_parcela'][$key];
                    $detail->contrato_id = $master->id;
                    $detail->store();
                }
            }
            TTransaction::close(); // close the transaction
            
            TForm::sendData('form_Contrato_Parcela', (object) ['id' => $master->id]);
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback();
        }
    }
    
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
