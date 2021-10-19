<?php
/**
 * ContratoList
 *
 * @version    1.0
 * @package    erphouse
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ContratoList extends TPage
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
        // $this->setCriteria($criteria) // define a standard filter

        

        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/operacao/titulo_contrato.html');

        $replaces = [];
        $replaces['title']  = 'Contratos';
        $replaces['botao'] = 'Criar Contrato';
                
        // replace the main section variables
        $this->html->enableSection('main', $replaces);
    
        
        // add the search form action
        /*
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['ContratoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        */

        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center',  '10%');
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
        
        /*
        $column_dt_fim->setTransformer( function($value, $object) {
            $today = new DateTime(date('Y-m-d'));
            $end   = new DateTime($value);
            $data = TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
            
            if ($object->ativo == 'sim' && !empty($value) && $today >= $end)
            {
                $div = new TElement('span');
                $div->class="label label-warning";
                $div->style="text-shadow:none; font-size:12px";
                $div->add($data);
                return $div;
            }
            
            return $data;
        });
        */
        
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
        
        $action1 = new TDataGridAction(['ContratoForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onTurnOnOff'], ['id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Activate/Deactivate'), 'fa:power-off orange');
        //$this->datagrid->addAction($action3 ,_t('Delete'), 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();

        // Busca do datagrid
        $input_search = new TEntry('input_search');
        $input_search->placeholder = _t('Search');
        $input_search->setSize('100%');
        
        // enable fuse search by column name
        $this->datagrid->enableSearch($input_search, 'id, fornecedor->nome_fantasia, cliente->nome, periodicidade->nome, dt_inicio, dt_fim, ativo');
        
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
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($pagina);
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
            TTransaction::open('erphouse');
            $contrato = Contrato::find($param['id']);
            
            if ($contrato instanceof Contrato)
            {
                $contrato->ativo = $contrato->ativo == 'sim' ? 'não' : 'sim';
                $contrato->store();
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
