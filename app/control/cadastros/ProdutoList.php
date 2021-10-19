<?php
/**
 * Produtos Cadastrados
 */
class ProdutoList extends TPage
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
        $this->setActiveRecord('Produto');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);

        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('produto', 'like', 'produto'); // filterField, operator, formField        
        $this->addFilterField('unidade_medida_id', '=', 'unidade_medida_id'); // filterField, operator, formField
        
        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/titulo_produto.html');

        $replaces = [];
        $replaces['title']  = 'Produtos';
        $replaces['botao'] = 'Criar Produto';
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
        $column_produto = new TDataGridColumn('produto', 'Produto', 'left');
        $column_valor = new TDataGridColumn('valor', 'Preço de Venda', 'left');
        $column_desconto = new TDataGridColumn('desconto', 'Desconto', 'left');
        $column_renova = new TDataGridColumn('renova', 'Renova', 'left');
        $column_fornecedor_id = new TDataGridColumn('pessoa->nome', 'Fornecedor', 'left');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'left');
        
        //$column_valor = 'R$' .number_format($column_valor, 2, ',', '.');

        $column_valor->enableAutoHide(500);
        $column_desconto->enableAutoHide(500);
        $column_fornecedor_id->enableAutoHide(500);        
        $column_ativo->enableAutoHide(500);        

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_produto);
        $this->datagrid->addColumn($column_fornecedor_id);
        $this->datagrid->addColumn($column_renova);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_desconto);        
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

        $column_renova->setTransformer( function($value, $object, $row) {
            $class = ($value=='não') ? 'danger' : 'success';
            $label = ($value=='não') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });


         // declara uma função de transformação
        $formatar_moeda = function($value)
        {            
            if (is_numeric($value))
            {                
                return 'R$ '.number_format($value, 2, ',', '.');            
            }          
            return $value;      
        }; 

        //Formatações das colunas do datagrid
        $column_valor->setTransformer( $formatar_moeda );
        //------------------------------------------------------

        // declara uma função de transformação
        $formatar_percent = function($value)
        {            
            if (is_numeric($value))
            {                
                return number_format($value, 2, ',', '.') ."%";            
            }          
            return $value;      
        }; 

        //Formatações das colunas do datagrid
        $column_desconto->setTransformer( $formatar_percent );
        //------------------------------------------------------

        
        
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_produto->setAction(new TAction([$this, 'onReload']), ['order' => 'produto']);

        
        $action1 = new TDataGridAction(['ProdutoForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction(['ProdutoForm', 'onEdit'], ['id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}', 'register_state' => 'false']);
        
        $this->datagrid->addAction($action1, _t('View'),   'fa:search gray');
        $this->datagrid->addAction($action2, _t('Edit'),   'far:edit blue');
        
        // create ONOFF action
        $action_onoff = new TDataGridAction(array($this, 'onTurnOnOff'));
        $action_onoff->setButtonClass('btn btn-default');
        $action_onoff->setLabel(_t('Activate/Deactivate'));
        $action_onoff->setImage('fa:power-off orange');
        $action_onoff->setField('id');
        $this->datagrid->addAction($action_onoff);
        //Fim dos botões
        
        // create the datagrid model
        $this->datagrid->createModel();

        // Busca do datagrid
        $input_search = new TEntry('input_search');
        $input_search->placeholder = _t('Search');
        $input_search->setSize('100%');
        
        // enable fuse search by column name
        $this->datagrid->enableSearch($input_search, 'id, produto, pessoa->nome, renova, ativo');
        
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
            $prod = Produto::find($param['id']);
            if ($prod instanceof Produto)
            {
                $prod->ativo = $prod->ativo == 'sim' ? 'não' : 'sim';
                $prod->store();
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