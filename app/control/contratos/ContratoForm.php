<?php
/**
 * ContratoFormFinal Master/Detail
 * @author  Anderson Souza
 */
class ContratoForm extends TPage
{
    protected $form; // form
    protected $detail_list;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        //parent::__construct($param);
        /*
        parent::setSize(0.4, null);
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();
        */

        // creates the form
        $this->form = new BootstrapFormBuilder('form_Contrato');
        $this->form->setFormTitle('Contrato');

        // master fields
        $id = new TEntry('id');

        //critério para listar apenas os fornecedores setados para o usuário logado
            TTransaction::open('permission');
            $user = TSession::getValue('userid');
            $id_logado = $user;
            TTransaction::close();
        //fim de pesquisa de id do usuário logado

        $fornecedor_id = new TDBCombo('fornecedor_id', 'erphouse', 'ViewUsuarioFornecedor', 'id', 'nome', null, TCriteria::create( ['system_user_id' => $id_logado] ));
        $fornecedor_id->setChangeAction(new TAction(array($this, 'onListaProduto')));

        $cliente_id = new TDBUniqueSearch('cliente_id', 'erphouse', 'Cliente', 'id', 'nome');
        $produto_id = new TDBCombo('produto_id', 'erphouse', 'Produto', 'id', 'produto'); //Adicionar aqui um parâmetro para filtrar apenas os produtos do fornecedor selcionado
        $periodicidade_id = new TDBCombo('periodicidade_id', 'erphouse', 'Periodicidade', 'id', 'nome');
        $dt_inicio = new TDate('dt_inicio');
        $obs = new TText('obs');
        $melhor_dia = new TEntry('melhor_dia');
        $efetivacao = new TDate('efetivacao');
        $forma_pagamento = new TCombo('forma_pagamento');
        $forma_pagamento->addItems( [
            'dinheiro' => 'Dinheiro',
            'cartao de credito' => 'Crédito',
            'cartao de debito' => 'Débito',
            'boleto' => 'Boleto',
            'cheque' => 'Cheque para o dia',
            'cheque-pre' => 'Cheque Pré-datado',
            'deposito' => 'Depósito para o dia',
            'deposito-pre' => 'Depósito Programado'
            ]);

        // detail fields
        $detail_uniqid = new THidden('detail_uniqid');
        $detail_id = new THidden('detail_id');
        $detail_produto = new TCombo('detail_produto');        
        $detail_produto_id =new THidden('detail_produto_id');
        $detail_valor = new TEntry('detail_valor');

        //Fields Endereços de entrega
        $gera_viagem = new TRadioGroup('gera_viagem');
        $gera_viagem->addItems( ['sim' => 'Sim', 'não' => 'Não'] );
        $gera_viagem->setLayout('horizontal');
        $gera_viagem->setUseButton();
        $gera_viagem->setSize('100%');
        $gera_viagem->setChangeAction(new TAction(array($this, 'onGeraViagem')));
        //TRadioGroup::disableField('form_Contrato', 'gera_viagem');
        
        // default value
        //$gera_viagem->setValue('não');
        
        // fire change event
        //self::onGeraViagem( ['gera_viagem' => 'não'] );

        $id_ss = TSession::getValue('id');        
        $endereco_contrato_id = new TDBCombo('endereco_contrato_id', 'erphouse', 'EnderecoContrato', 'id', 'tipo', null, TCriteria::create( ['contrato_id' => $id_ss] ));
        $endereco_contrato_id->setSize('100%');
        $endereco_contrato_id->setChangeAction(new TAction(array($this, 'InformaEndereco')));

        //Links        
        $icone         = '<i class="fa fa-plus-circle green red"></i>';
        $btStyle       = 'margin:-3px;font-size:1em;border:none;background:none;';
        
        //Botão Cores
        $botaendereco = new TButton("botaendereco");
        $botacartao = new TButton("botacartao");
        //TButton::disableField('form_Contrato', 'botaendereco');

        $local = new TText('local');
        $local->setSize('100%');
        $local->setValue('CONFIRME O LOCAL DE COLETA!');
        //$local->setEditable(FALSE);

        //Configurações
        $fornecedor_id->setSize('100%');
        $cliente_id->setSize('100%');
        $produto_id->setSize('100%');
        $periodicidade_id->setSize('100%');
        $dt_inicio->setSize('100%');
        $obs->setSize('100%');
        $melhor_dia->setSize('100%');
        $forma_pagamento->setSize('100%');

        // detail fields
        $detail_uniqid->setSize('100%');
        $detail_id->setSize('100%');
        $detail_produto_id->setSize('100%');
        $detail_produto->setSize('100%');
        $detail_valor->setSize('100%');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        $dt_inicio->setEditable(FALSE);
        $dt_inicio->setMask('dd/mm/yyyy');
        $dt_inicio->setDatabaseMask('yyyy-mm-dd');

        $efetivacao->setMask('dd/mm/yyyy');
        $efetivacao->setDatabaseMask('yyyy-mm-dd');

        $detail_valor = new TNumeric('detail_valor', 2, ',', '.');
        
        // master fields
        $row = $this->form->addFields(  [ new TLabel('Contrato'), $id ],
                                        [ new TLabel('Cliente'), $cliente_id ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-10']; 

        $row = $this->form->addFields(  [ new TLabel('Periodicidade'), $periodicidade_id ],
                                        [ new TLabel('Data'), $dt_inicio ],
                                        [ new TLabel('Melhor Dia'), $melhor_dia ],
                                        [ new TLabel('Efetivação'), $efetivacao ]
                                        );
        $row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];

        $row = $this->form->addFields(  [ new TLabel('Fornecedor'), $fornecedor_id ]
                                        );
        $row->layout = ['col-sm-12']; 

        //[ new TLabel('Selecione endereço'), $tipo_endereco ]
        $row = $this->form->addFields(  [ $botacartao, $forma_pagamento ],
                                        [ new TLabel('Gera Viagem'), $gera_viagem ],
                                        [ $botaendereco, $endereco_contrato_id ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-8'];
       
        $row = $this->form->addFields(  [ new TLabel('Observação'), $obs ],
                                        [ new TLabel('Endereço'), $local ]
                                        );
        $row->layout = ['col-sm-6', 'col-sm-6']; 

        //parâmetros do botão adicionar endereços
        $botaendereco->setAction(new TAction(array($this, 'AdicionarEndereco')),"Selecione um Endereço {$icone}");
        $botaendereco->style = $btStyle;
        $botaendereco->addStyleClass('label');

        //parâmetros do botão adicionar cartao
        $botacartao->setAction(new TAction(array($this, 'AdicionarCartao')),"Pagamento {$icone}");
        $botacartao->style = $btStyle;
        $botacartao->addStyleClass('label');
        
        //$this->form->addFields( [new TLabel('Produto Id')], [$produto_id] );
        
        //campos ocultos
        $this->form->addFields( [$detail_uniqid] );
        $this->form->addFields( [$detail_id] );
                            
        //Botão para adicionar novo produto
        $add = TButton::create('add', [$this, 'onDetailAdd'], 'Adicionar', 'fa:plus-circle green');
        $add->getAction()->setParameter('static','1');

        //Botão para abrir janela lateral de histórico
        $hist = TButton::create('hist', [$this, 'onDetailAdd'], 'Exibir Histórico', 'fa:plus green');
        $hist->getAction()->setParameter('static','1');

        $row = $this->form->addFields(  [ new TLabel('Produto'), $detail_produto ],
                                        [ new TLabel('Valor'), $detail_valor ],
                                        [ new TLabel(''), $add ],
                                        [ new TLabel(''), $hist ]
                                        );
        $row->layout = ['col-sm-6', 'col-sm-2', 'col-sm-1', 'col-sm-1']; 
        
        //Inicio do datagrid de produtos adicionados
        $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->detail_list->setId('ContratoItem_list');
        $this->detail_list->generateHiddenFields();
        $this->detail_list->style = "min-width: 400px; width:100%;margin-bottom: 5px";
        

        //atributos itens

        $col_uniq = new TDataGridColumn('uniqid', 'Uniqid', 'center');
        $col_id = new TDataGridColumn('id', 'Id', 'center');
        $col_produto_id = new TDataGridColumn('produto_id', 'Id', 'center');
        $col_produto = new TDataGridColumn('produto', 'Produto', 'left', '80%');
        $col_valor = new TDataGridColumn('valor', 'Valor', 'left', '20%');

        // items
        $this->detail_list->addColumn( $col_uniq )->setVisibility(false);
        $this->detail_list->addColumn( $col_id )->setVisibility(false);
        $this->detail_list->addColumn( $col_produto_id )->setVisibility(false);
        $this->detail_list->addColumn( $col_produto );
        $this->detail_list->addColumn( $col_valor );

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

        //Formatar a coluna dos valores
        $col_valor->enableTotal('sum', 'R$', 2, ',', '.');  
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ ' .number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $col_valor->setTransformer( $format_value );
        
        //Botoões do form
        $this->form->addHeaderActionLink( _t('Close'),  new TAction(array('ContratoList','onReload')),  'fa:times red' );
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave'], ['static'=>'1']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Limpar',  new TAction([$this, 'onClear']), 'fa:eraser red');
        $this->form->addActionLink('Cancelar', new TAction(array('ContratoList','onReload')),  'fa:times red' );

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

            $grid_data['produto_id'] = $data->detail_produto;

                TTransaction::open('erphouse');
                // load customer, returns FALSE if not found
                $customer = Produto::find($data->detail_produto);
                if ($customer instanceof Produto)
                {
                    $data->detail_produto = $customer->produto;
                }            
                TTransaction::close();
            
            $grid_data['uniqid'] = $uniqid;
            $grid_data['id'] = $data->detail_id;
            $grid_data['produto'] = $data->detail_produto;
            $grid_data['valor'] = $data->detail_valor;
            
            //$grid_data['valor'] = number_format( $data->detail_valor, 2, ',', '.');            
            
            // insert row dynamically
            $row = $this->detail_list->addItem( (object) $grid_data );
            $row->id = $uniqid;
            
            TDataGrid::replaceRowById('ContratoItem_list', $uniqid, $row);
            
            // clear detail form fields
            $data->detail_uniqid = '';
            $data->detail_id = '';
            $data->detail_produto_id = '';
            $data->detail_valor = '';
            
            $data->dt_inicio = TDate::convertToMask($data->dt_inicio, 'yyyy-mm-dd', 'dd/mm/yyyy');
            $data->efetivacao = TDate::convertToMask($data->efetivacao, 'yyyy-mm-dd', 'dd/mm/yyyy');

            // send data, do not fire change/exit events
            TForm::sendData( 'form_Contrato', $data, false, false );

            TRadioGroup::enableField('form_Contrato', 'gera_viagem'); //Libera o botão para que possa ser adicionado um endereço
            TButton::enableField('form_Contrato', 'botaendereco');

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
        
        $data->detail_valor = $param['valor'];
        //$data->detail_valor = number_format($data->detail_valor, 2, ',', '.');

        $criteria = TCriteria::create( ['id' => $param['produto_id'] ] );
                
        // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
        TDBCombo::reloadFromModel('form_Contrato', 'detail_produto', 'erphouse', 'Produto', 'id', 'produto', null, $criteria, TRUE);
        $data->detail_produto = $param['produto_id'];
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Contrato', $data, false, false );
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
        $data->detail_produto_id = '';
        $data->detail_valor = '';
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Contrato', $data, false, false );
        
        // remove row
        TDataGrid::removeRowById('ContratoItem_list', $param['uniqid']);
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
                $items  = ContratoItem::where('contrato_id', '=', $key)->load();
                
                foreach( $items as $item )
                {
                        //TTransaction::open('erphouse');
                            // load customer, returns FALSE if not found
                            $customer = Produto::find($item->produto_id);
                            if ($customer instanceof Produto)
                            {
                                $item->produto = $customer->produto;                                
                            }            
                        
                    
                    //$item->valor = number_format($item->valor, 2, ',', '.');
                    //$item->valor = number_format($item->valor, 2, ',', '.');
                    $item->uniqid = uniqid();
                    $row = $this->detail_list->addItem( $item );
                    $row->id = $item->uniqid;
                }

                $vEnderecoColeta = EnderecoContrato::find($object->endereco_contrato_id);
                if ($vEnderecoColeta instanceof EnderecoContrato)
                {
                    $object->local = $vEnderecoColeta->logradouro . ', ' . $vEnderecoColeta->numero . ' - ' . $vEnderecoColeta->complemento . "\n" .
                                    $vEnderecoColeta->cidade . '/' . $vEnderecoColeta->estado . "\n" . 'REFERÊNCIA: ' . $vEnderecoColeta->referencia;                                
                }   
                /*
                $customer->logradouro . ', ' . $customer->numero . ' - ' . $customer->complemento . "\n" .
                $customer->cidade . '/' . $customer->estado . "\n" . 'REFERÊNCIA: ' . $customer->referencia;
                */
                
                self::onListaProduto($object->fornecedor_id);
                self::ListaEnderecos($key);

                $criteria = new TCriteria;
                $criteria->add( TCriteria::create( ['pessoa_id' => $object->fornecedor_id ] ));
                $criteria->add( TCriteria::create( ['ativo' => 'sim' ] ));
                
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_Contrato', 'detail_produto', 'erphouse', 'Produto', 'id', 'produto', null, $criteria, TRUE);

                $this->form->setData($object);

                TRadioGroup::enableField('form_Contrato', 'gera_viagem'); //Libera o botão para que possa ser adicionado um endereço
                TButton::enableField('form_Contrato', 'botaendereco');
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
            if($master->dt_inicio == null)
            {
                $master->dt_inicio = date('Y-m-d');
            }
            $master->system_user_id = TSession::getValue('userid');
            $master->ativo = 'sim';
            $master->status = 'Aguardando Pagamento';
            $master->store();
            
            //Adiciona os itens do contrato
            ContratoItem::where('contrato_id', '=', $master->id)->delete();
            
            if( $param['ContratoItem_list_produto_id'] )
            {
                foreach( $param['ContratoItem_list_produto_id'] as $key => $item_id )
                {
                    $detail = new ContratoItem;
                    $detail->produto_id  = $param['ContratoItem_list_produto_id'][$key];
                    $detail->valor  = (float) str_replace([','], ['.'], $param['ContratoItem_list_valor'][$key]);
                    $detail->contrato_id = $master->id;
                    $detail->quantidade = 1;
                    $detail->total = (float) str_replace([','], ['.'], $param['ContratoItem_list_valor'][$key]);
                    $detail->data_contrato = date('Y-m-d');                    
                    $detail->status = 'Aguardando Pagamento';
                    $detail->store();
                }

            } //Fim do registro dos ítens do contrato e venda

            
            TTransaction::close(); // close the transaction
            
            TForm::sendData('form_Contrato', (object) ['id' => $master->id]);

            TRadioGroup::enableField('form_Contrato', 'gera_viagem'); //Libera o botão para que possa ser adicionado um endereço
            TButton::enableField('form_Contrato', 'botaendereco');
            
            new TMessage('info', 'Contrato registrado com sucesso!');
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback();
        }
    }

    public static function onGeraViagem($param)
    {
        if ($param['gera_viagem'] == 'sim')
        {
            TCombo::enableField('form_Contrato', 'endereco_contrato_id');
            //$id_ct = TSession::getValue('id');
            //TDBCombo::reloadFromModel('form_Contrato', 'endereco_contrato_id', 'erphouse', 'EnderecoContrato', 'id', 'tipo', null, $id_ct, TRUE);
        }
        else
        {
            TCombo::disableField('form_Contrato', 'endereco_contrato_id');
            //TCombo::clearField('form_Contrato', 'endereco_contrato_id');
        }
    }

    public static function onListaProduto($param)
    {
        try
        {
            TTransaction::open('erphouse');
            if (!empty($param['fornecedor_id']))
            {
                $criteria = new TCriteria;
                $criteria->add( TCriteria::create( ['pessoa_id' => $param['fornecedor_id'] ] ));
                $criteria->add( TCriteria::create( ['ativo' => 'sim' ] ));
                
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_Contrato', 'detail_produto', 'erphouse', 'Produto', 'id', 'produto', null, $criteria, TRUE);
            }
            else
            {
                TDBCombo::clearField('form_Contrato', 'detail_produto');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    public function AdicionarEndereco($param)
    {
        $data = $this->form->getData();
        $this->form->validate();
        $data->id = $param['id'];
        
        TSession::setValue('form_Contrato',$data);   
             
        $script = "__adianti_load_page('engine.php?class=EnderecoContratoForm&method=onEdit&id={$data->id}&key={$data->id}');";
        TScript::create($script);
    }

    public function AdicionarCartao($param)
    {
        $data = $this->form->getData();
        $this->form->validate();
        $data->id = $param['cliente_id'];
        
        TSession::setValue('form_Contrato',$data);   
             
        $script = "__adianti_load_page('engine.php?class=CartaoForm&method=onEdit&id={$data->id}&key={$data->id}');";
        TScript::create($script);
    }

    public static function ListaEnderecos($param)
    {
        try
        {
            TTransaction::open('erphouse');
            if (!empty($param))
            {
                $criteria = TCriteria::create( ['contrato_id' => $param ] );
                
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_Contrato', 'endereco_contrato_id', 'erphouse', 'EnderecoContrato', 'id', 'tipo', null, $criteria, TRUE);
            }
            else
            {
                TDBCombo::clearField('form_Contrato', 'endereco_contrato_id');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public static function InformaEndereco($param)
    {
        
        if(isset($param['endereco_contrato_id']))
        {
            try
            {
                TTransaction::open('erphouse'); // open transaction
                
                // query criteria
                $criteria = new TCriteria; 
                $criteria->add(new TFilter('id', '=', $param['endereco_contrato_id']));             
                
                // load using repository
                $repository = new TRepository('EnderecoContrato'); 
                $customers = $repository->load($criteria); 
                
                foreach ($customers as $customer) 
                { 
                    $data = new stdClass;
                    $data->local  = $customer->logradouro . ', ' . $customer->numero . ' - ' . $customer->complemento . "\n" .
                                    $customer->cidade . '/' . $customer->estado . "\n" . 'REFERÊNCIA: ' . $customer->referencia; 
                }
                
                TTransaction::close(); // close transaction
                if($param['endereco_contrato_id'] != null)
                {
                    TForm::sendData( 'form_Contrato', $data, false, false );
                }
                else
                {   
                    $data = new stdClass;
                    $data->local = 'SELECIONE UM ENDEREÇO DE COLETA!';
                    TForm::sendData( 'form_Contrato', $data, false, false );
                }
            }
            catch (Exception $e)
            {
                new TMessage('error', $e->getMessage());
            }
        }
            
    }

}
