<?php
/**
 * SystemGroupList
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemGroupList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        
        parent::setDatabase('permission');            // defines the database
        parent::setActiveRecord('SystemGroup');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        parent::addFilterField('id', '=', 'id'); // filterField, operator, formField
        parent::addFilterField('name', 'like', 'name'); // filterField, operator, formField

        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/titulo_cargo.html');
        
        $replaces = [];
        $replaces['title']  = 'Cargos';
        $replaces['botao'] = 'Criar Cargo';
        //$replaces['name']   = 'Someone famous';
        
        // replace the main section variables
        $this->html->enableSection('main', $replaces);

        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center', 50);
        $column_name = new TDataGridColumn('name', _t('Name'), 'left');
        $column_ativo = new TDataGridColumn('ativo', _t('Active'), 'left');

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_name);
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

        // creates the datagrid column actions
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        
        $order_name = new TAction(array($this, 'onReload'));
        $order_name->setParameter('order', 'name');
        $column_name->setAction($order_name);
                
        // create EDIT action
        $action_edit = new TDataGridAction(array('SystemGroupForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        
        // create CLONE action
        $action_clone = new TDataGridAction(array($this, 'onClone'));
        $action_clone->setButtonClass('btn btn-default');
        $action_clone->setLabel(_t('Clone'));
        $action_clone->setImage('far:clone green');
        $action_clone->setField('id');
        $this->datagrid->addAction($action_clone);

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
        $this->datagrid->enableSearch($input_search, 'id, name, ativo');

        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup('Lista de Cargos');       
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        $panel->addHeaderWidget($input_search);

        // Primeiro panel com o título da página
        $pagina = new TVBox;
        $pagina->style = 'width: 100%';
        $pagina->add($this->form);
        $pagina->add($this->html);
        

        //$this->html->addAction(_t('New'),  new TAction(array('SystemGroupForm', 'onEdit')), 'fa:plus green');
        //$pagina->addHeaderActionLink( 'Save as PDF', new TAction([$this, 'exportAsPDF'], ['register_state' => 'false']), 'far:file-pdf red' );

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        $container->add($pagina);
        $container->add($panel);
        parent::add($container);
    }
    
    //Ativar e desativar o registro
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('permission');
            $grupo = SystemGroup::find($param['id']);
            if ($grupo instanceof SystemGroup)
            {
                if ($grupo->ativo=='')
                {
                    $grupo->ativo = 'não';                    
                }
                else
                {
                    $grupo->ativo = $grupo->ativo == 'não' ? 'sim' : 'não';
                }                
                $grupo->store();
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

    /**
     * Clone group
     */
    public function onClone($param)
    {
        try
        {
            TTransaction::open('permission');
            $group = new SystemGroup($param['id']);
            $group->cloneGroup();
            TTransaction::close();
            
            $this->onReload();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
