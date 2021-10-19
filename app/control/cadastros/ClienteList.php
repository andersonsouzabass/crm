<?php
/**
 * Clientes Cadastrados
 */
class ClienteList extends TPage
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
        $this->setActiveRecord('Cliente');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);

        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('cliente', 'like', 'cliente'); // filterField, operator, formField        
        //$this->addFilterField('unidade_medida_id', '=', 'unidade_medida_id'); // filterField, operator, formField
        
        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/operacao/titulo_cliente.html');

        $replaces = [];
        $replaces['title']  = 'Clientes';
        $replaces['botao'] = 'Criar Cliente';
        //$replaces['name']   = 'Someone famous';
        
        // replace the main section variables
        $this->html->enableSection('main', $replaces);
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        

        
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);

        
        $action1 = new TDataGridAction(['ClienteForm', 'onEdit'], ['id'=>'{id}']);
        //$action2 = new TDataGridAction(['ContratoForm', 'onNovoContrato'], ['cliente_id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}', 'register_state' => 'false']);
        
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        //$this->datagrid->addAction($action2, 'Gerar Contrato',   'fas:newspaper fa-fw');

        
        // create the datagrid model
        $this->datagrid->createModel();

        // Busca do datagrid
        $input_search = new TEntry('input_search');
        $input_search->placeholder = _t('Search');
        $input_search->setSize('100%');
        
        // enable fuse search by column name
        $this->datagrid->enableSearch($input_search, 'id, nome');
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        $panel->addHeaderWidget($input_search);
        
        
        // Primeiro panel com o título da página
        $pagina = new TVBox;
        $pagina->style = 'width: 100%';
        $pagina->add($this->form);
        $pagina->add($this->html);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($pagina);
        $container->add($panel);
        
        parent::add($container);
    }

    

    
}