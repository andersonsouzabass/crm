<?php
/**
 * 
 */
class EfetivacaoList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('erphouse');            // defines the database
        $this->setActiveRecord('Contrato');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(10);

        //Renova apenas os contratos que estão aguardando contato
        $criteria = new TCriteria;
        $criteria->add(new TFilter('status', '=', 'Aguardando Pagamento') );        
        $this->setCriteria($criteria); 

        //$this->addFilterField('vinculo_id', '=', 3); // filterField, operator, formField
        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_cedido_para_list');
        $this->form->setFormTitle('Validação - Lista de Contratos para Efetivação');
        
        // create the form fields
        $id = new TEntry('id');
       
        
        $row = $this->form->addFields(  [ new TLabel('Buscar Contrato'), $id ]                                      
                                        );
        $row->layout = ['col-sm-3'];


        // set sizes
        $id->setSize('100%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink('Cadastrar Pessoa', new TAction(['CessaoParaForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Contrato', 'center',  '10%');
        $column_fornecedor_id = new TDataGridColumn('fornecedor->nome_fantasia', 'Fornecedor', 'left');
        $column_cliente_id = new TDataGridColumn('cliente->nome', 'Cliente', 'left');
        $column_tipo_contrato_id = new TDataGridColumn('produto->produto', 'Produto', 'left');
        $column_periodicidade_id = new TDataGridColumn('periodicidade->nome', 'Periodicidade', 'left');
        $column_dt_inicio = new TDataGridColumn('dt_inicio', 'Inicio', 'left');
        //$column_dt_fim = new TDataGridColumn('dt_fim', 'Dt Fim', 'left');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'left');

        $column_ativo->setTransformer( function ($value) {
            if ($value == 'sim')
            {
                $div = new TElement('span');
                $div->class="label label-success";
                $div->style="text-shadow:none; font-size:12px";
                $div->add('Sim');
                return $div;
            }
            else
            {
                $div = new TElement('span');
                $div->class="label label-danger";
                $div->style="text-shadow:none; font-size:12px";
                $div->add('Não');
                return $div;
            }
        });
        
        $column_id->setTransformer( function ($value, $object, $row) {
            if ($object->ativo == 'não')
            {
                $row->style= 'color: silver';
            }
            
            return $value;
        });
        
        $column_dt_inicio->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_fornecedor_id);
        $this->datagrid->addColumn($column_cliente_id);
        //$this->datagrid->addColumn($column_tipo_contrato_id); //Produto do contrato
        $this->datagrid->addColumn($column_periodicidade_id);
        $this->datagrid->addColumn($column_dt_inicio);
        //$this->datagrid->addColumn($column_dt_fim);
        $this->datagrid->addColumn($column_ativo);

        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_cliente_id->setAction(new TAction([$this, 'onReload']), ['order' => 'cliente->nome_fantasia']);
        
        $column_tipo_contrato_id->enableAutoHide(500);
        $column_periodicidade_id->enableAutoHide(500);
        $column_dt_inicio->enableAutoHide(500);
        //$column_dt_fim->enableAutoHide(500);
        $column_ativo->enableAutoHide(500);

        
        $action1 = new TDataGridAction(['EfetivacaoForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onTurnOnOff'], ['id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        //$this->datagrid->addAction($action2 ,_t('Activate/Deactivate'), 'fa:power-off orange');
        //$this->datagrid->addAction($action3 ,_t('Delete'), 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        /*
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        */
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';        
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Turn on/off an user
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('gratifica');
            $srv = Servidor::find($param['id']);
            
            if ($srv instanceof Servidor)
            {
                $srv->ativo = $srv->ativo == 'sim' ? 'não' : 'sim';
                $srv->store();
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
