<?php
/**
 * Produtos Cadastrados
 */
class MotivoSacList extends TPage
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
        $this->setActiveRecord('MotivoSac');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);

        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('motivo', 'like', 'motivo'); // filterField, operator, formField        
        $this->addFilterField('produto_id', '=', 'produto_id'); // filterField, operator, formField
        
        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/titulo_perfil_motivo_sac.html');
    
        $replaces = [];
        $replaces['title']  = 'Motivo SAC';
        $replaces['botao'] = 'Criar Motivo SAC';
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
        $column_motivo = new TDataGridColumn('motivo', 'Motivo', 'left');
        $column_Produto = new TDataGridColumn('produto->produto', 'Produto', 'left');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'left');

        $column_ativo->enableAutoHide(500);
        $column_Produto->enableAutoHide(500);
        //$column_fornecedor_id->enableAutoHide(500);        
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_motivo);
        $this->datagrid->addColumn($column_Produto);       
        $this->datagrid->addColumn($column_ativo);

        $column_ativo->setTransformer( function($value, $object, $row) {
            $class = ($value=='não') ? 'danger' : 'success';
            $label = ($value=='não') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });
              
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_motivo->setAction(new TAction([$this, 'onReload']), ['order' => 'motivo']);

        /*       
        Criar botões de edição
        */

        //Editar Motivo
        $action2 = new TDataGridAction(['MotivoSacForm', 'onEdit'], ['id'=>'{id}']);
        $this->datagrid->addAction($action2, _t('Edit'),   'far:edit blue');

        /*
        $action3 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}', 'register_state' => 'false']);
        $this->datagrid->addAction($action3 ,_t('Delete'), 'far:trash-alt red');*/

        // create ONOFF action
        $action_onoff = new TDataGridAction(array($this, 'onTurnOnOff'));
        $action_onoff->setButtonClass('btn btn-default');
        $action_onoff->setLabel(_t('Activate/Deactivate'));
        $action_onoff->setImage('fa:power-off orange');
        $action_onoff->setField('id');
        $this->datagrid->addAction($action_onoff);  
        

        // create the datagrid model
        $this->datagrid->createModel();

        // Busca do datagrid
        $input_search = new TEntry('input_search');
        $input_search->placeholder = _t('Search');
        $input_search->setSize('100%');
        
        // enable fuse search by column name
        $this->datagrid->enableSearch($input_search, 'id, motivo, produto->produto, ativo');
    
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

    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('permission');
            $motivo = MotivoSac::find($param['id']);
            if ($motivo instanceof MotivoSac)
            {
                $motivo->ativo = $motivo->ativo == 'sim' ? 'não' : 'sim';
                $motivo->store();
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