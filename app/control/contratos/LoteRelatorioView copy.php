<?php
/**
 * LoteVew Record selection
 * @author  <your name here>
 */
class LoteRelatorioView extends TPage
{
    protected $form;     // search form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('erphouse');            // defines the database
        $this->setActiveRecord('LoteView');   // defines the active record
        $this->setDefaultOrder('contrato_id', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('contrato_id', '=', 'contrato_id'); // filterField, operator, formField
        $this->addFilterField('cliente', '=', 'cliente'); // filterField, operator, formField
        $this->addFilterField('fornecedor', 'like', 'fornecedor'); // filterField, operator, formField
        $this->addFilterField('data_prog', '>=', 'data_ini'); // filterField, operator, formField
        $this->addFilterField('data_prog', '<=', 'data_fim'); // filterField, operator, formField
        $this->addFilterField('forma_pagamento', 'like', 'forma_pagamento'); // filterField, operator, formField
        $this->addFilterField('efetivacao', '=', 'efetivacao'); // filterField, operator, formField
        $this->addFilterField('total_efetivado', 'like', 'total_efetivado'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_ViewContratos');
        $this->form->setFormTitle('Relatório de Títulos Depositados');
        

        // create the form fields
        $contrato_id = new TEntry('contrato_id');
        $cliente = new TDBUniqueSearch('cliente', 'erphouse', 'Cliente', 'id', 'nome');
        $fornecedor = new TEntry('fornecedor');
        //$data_prog = new TEntry('data_prog');
        $forma_pagamento = new TDBCombo('forma_pagamento', 'erphouse', 'LoteView', 'forma_pagamento', 'forma_pagamento');
        $efetivacao = new TDBUniqueSearch('efetivacao', 'erphouse', 'Efetivacao', 'id', 'contrato_id');
        $total_efetivado = new TEntry('total_efetivado');

        $data_ini = new TDate('data_ini');
        $data_fim = new TDate('data_fim');

        $row = $this->form->addFields(  [ new TLabel('Contrato'), $contrato_id ],
                                        [ new TLabel('Cliente'), $cliente ]                                        
                                        );
        $row->layout = ['col-sm-2', 'col-sm-6'];

        $row = $this->form->addFields(  [ new TLabel('Forma Pagamento'), $forma_pagamento ],
                                        [ new TLabel('Fornecedor'), $fornecedor ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-6'];

        $row = $this->form->addFields(  [ new TLabel('Data Inicial'), $data_ini ],
                                        [ new TLabel('Data Final'), $data_fim ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-2'];

        // set sizes
        $contrato_id->setSize('100%');
        $cliente->setSize('100%');
        $fornecedor->setSize('100%');
        //$data_prog->setSize('100%');
        $forma_pagamento->setSize('100%');
        $efetivacao->setSize('100%');
        $total_efetivado->setSize('100%');
        $data_ini->setSize('100%');
        $data_fim->setSize('100%');

        $data_ini->setMask('dd/mm/yyyy');
        $data_ini->setDatabaseMask('yyyy-mm-dd');

        $data_fim->setMask('dd/mm/yyyy');
        $data_fim->setDatabaseMask('yyyy-mm-dd');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_contrato_id = new TDataGridColumn('contrato_id', 'Contrato', 'right');
        $column_cliente = new TDataGridColumn('cliente', 'Nome do Cliente', 'left');
        $column_fornecedor = new TDataGridColumn('fornecedor', 'Fornecedor', 'left');
        $column_data_prog = new TDataGridColumn('data_prog', 'Data Depósito', 'left');
        $column_forma_pagamento = new TDataGridColumn('forma_pagamento', 'Forma Pagamento', 'left');
        $column_efetivacao = new TDataGridColumn('efetivacao', 'Efetivacao', 'left');
        $column_total_efetivado = new TDataGridColumn('total_efetivado', 'Valor', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_contrato_id);
        $this->datagrid->addColumn($column_data_prog);
        $this->datagrid->addColumn($column_cliente);
        $this->datagrid->addColumn($column_total_efetivado);
        //$this->datagrid->addColumn($column_efetivacao);
        $this->datagrid->addColumn($column_fornecedor);        
        $this->datagrid->addColumn($column_forma_pagamento);

        $column_contrato_id->setTransformer([$this, 'formatRow'] );

        // Formatando as datas
        $column_data_prog->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });

        //Formatando a colula valor
        //$col_valor_parcela->enableTotal('sum', 'R$', 2, ',', '.');  
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ ' .number_format($value, 2, ',', '.');
            }
            return $value;
        };        
        $column_total_efetivado->setTransformer( $format_value );
        
        // creates the datagrid actions
        $action1 = new TDataGridAction([$this, 'onSelect'], ['contrato_id' => '{contrato_id}', 'register_state' => 'false']);
        //$action1->setUseButton(TRUE);
        $action1->setButtonClass('btn btn-default');
        
                
        // add the actions to the datagrid
        $this->datagrid->addAction($action1, 'Select', 'far:square fa-fw black');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        $panel->addHeaderActionLink( 'Show results', new TAction([$this, 'showResults']), 'far:check-circle' );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Save the object reference in session
     */
    public function onSelect($param)
    {
        // get the selected objects from session 
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        
        TTransaction::open('erphouse');
        $object = new LoteView($param['key']); // load the object
        if (isset($selected_objects[$object->contrato_id]))
        {
            unset($selected_objects[$object->contrato_id]);
        }
        else
        {
            $selected_objects[$object->contrato_id] = $object->toArray(); // add the object inside the array
        }
        TSession::setValue(__CLASS__.'_selected_objects', $selected_objects); // put the array back to the session
        TTransaction::close();
        
        // reload datagrids
        $this->onReload( func_get_arg(0) );
    }
    
    /**
     * Highlight the selected rows
     */
    public function formatRow($value, $object, $row)
    {
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        
        if ($selected_objects)
        {
            if (in_array( (int) $value, array_keys( $selected_objects ) ) )
           // var_dump($value);
            {
                $row->style = "background: #abdef9";
                
                $button = $row->find('i', ['class'=>'far fa-square fa-fw black'])[0];
                if ($button)
                {
                    $button->class = 'far fa-check-square fa-fw black';
                }
            }
        }
        
        return $value;
    }
    
    /**
     * Show selected records
     */
    public function showResults()
    {
        $datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $datagrid->width = '100%';
        $datagrid->addColumn( new TDataGridColumn('contrato_id',  'Contrato Id',  'right') );
        $datagrid->addColumn( new TDataGridColumn('cliente',  'Cliente',  'left') );
        $datagrid->addColumn( new TDataGridColumn('fornecedor',  'Fornecedor',  'left') );
        $datagrid->addColumn( new TDataGridColumn('data_prog',  'Data Prog',  'left') );
        $datagrid->addColumn( new TDataGridColumn('forma_pagamento',  'Forma Pagamento',  'left') );
        $datagrid->addColumn( new TDataGridColumn('efetivacao',  'Efetivacao',  'left') );
        $datagrid->addColumn( new TDataGridColumn('total_efetivado',  'Total Efetivado',  'right') );
        
        // create the datagrid model
        $datagrid->createModel();
        
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        ksort($selected_objects);
        if ($selected_objects)
        {
            $datagrid->clear();
            foreach ($selected_objects as $selected_object)
            {
                $datagrid->addItem( (object) $selected_object );
            }
        }
        
        $win = TWindow::create('Results', 0.6, 0.6);
        $win->add($datagrid);
        $win->show();
    }
}
