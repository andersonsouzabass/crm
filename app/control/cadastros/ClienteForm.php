<?php
/**
 * ClienteForm Master/Detail
 * @author  <your name here>
 */
class ClienteForm extends TPage
{
    protected $form; // form
    protected $fieldlist;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct($param);
        
        /*
        parent::setSize(0.68, null);
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();
        */

        // creates the form
        $this->form = new BootstrapFormBuilder('form_Cliente');
        $this->form->setFormTitle('<h3><b>Criar Cliente</b></h3>');
        
        // create the form fields
        $id = new TEntry('id');
            
        $nome = new TEntry('nome');
        $dt_nascimento = new TDate('dt_nascimento');
        $contato_resp = new TEntry('contato_resp');

        $cpf = new TEntry('cpf');
        $cnpj = new TEntry('cnpj');

        $tipo_doc = new TCombo('tipo_doc');
        $desc_doc = new TEntry('desc_doc');
        $doc = new TEntry('doc');

        $emissor = new TEntry('emissor');

        $filter = new TCriteria;
        $filter->add(new TFilter('id', '<', '0'));
        $estado = new TDBCombo('estado', 'erphouse', 'Estado', 'id', '{nome} ({uf})');
        $estado->enableSearch();

        $estado_civil = new TCombo('estado_civil');
        $sexo = new TCombo('sexo');
        
        $profissao = new TDBUniqueSearch('profissao', 'erphouse', 'Profissao', 'id', 'profissao');
        $classe_social = new TCombo('classe_social');
        
        $renda = new TEntry('renda');
        $observacao = new TText('observacao');
        
        $cnpj->setExitAction( new TAction( [$this, 'onExitCNPJ'] ) );
                
        $tipo_doc->addItems ([
            1 => 'RG',
            2 => 'CNH',
            3 => 'Outro'
            ]);
        
        $classe_social->addItems ([
            'Classe A' => 'Classe A',
            'Classe B' => 'Classe B',
            'Classe C' => 'Classe C',
            'Classe D' => 'Classe D'
            ]);
        
        $observacao->setSize('100%', 60);
        $sexo->addItems([
            'Masculino' => 'Masculino',
            'Feminino' => 'Feminino',
            'Homossexual' => 'Homossexual',
            'Travesti' => 'Travesti',
            'Mulher Transexual' => 'Mulher Transexual',
            'Homem Transexual' => 'Homem Transexual'
            ]);
        
        $estado_civil->addItems ([
            'Solteiro(a)' => 'Solteiro(a)',
            'Casado(a)' => 'Casado(a)',
            'Divorciado(a)' => 'Divorciado(a)',
            'Viúvo(a)' => 'Viúvo(a)',
            'Separado(a)' => 'Separado(a)'
            ]);
        
        // add the fields

        //adiciona campos ao formulário
        $row = $this->form->addFields(  [ new TLabel('Id'), $id ],
                                        [ new TLabel('Nome'), $nome ],
                                        [ new TLabel('Responsável pelo Cliente'), $contato_resp ],
                                        [ new TLabel('Nascimento'), $dt_nascimento ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-4', 'col-sm-4', 'col-sm-2'];

        $row = $this->form->addFields(  [ new TLabel('CPF'), $cpf ],
                                        [ new TLabel('CNPJ'), $cnpj ],
                                        [ new TLabel('Renda'), $renda ],
                                        [ new TLabel('Classe Social'), $classe_social ],
                                        [ new TLabel('Estado Civil'), $estado_civil ],
                                        [ new TLabel('Orientação'), $sexo ]                                                                                
                                        );
        $row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-2','col-sm-2', 'col-sm-2'];

        
        $row = $this->form->addFields(  [ new TLabel('Documento'), $tipo_doc ],
                                        [ new TLabel('Outro Documento'), $desc_doc ],
                                        [ new TLabel('Número Doc'), $doc ],
                                        [ new TLabel('Orgão Emissor'), $emissor ],
                                        [ new TLabel('UF'), $estado ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-4'];

         //Botão para adicionar novo produto
         $contrato_lateral = TButton::create('contrato_lateral', [$this, 'NovoContrato'], ['cliente_id' => '{id}'], 'Abrir Tela de Contrato', 'fa:plus-circle green');
         $contrato_lateral->getAction()->setParameter('static','1');

        $row = $this->form->addFields(  [ new TLabel('Profissão'), $profissao ],
                                        //[ new TLabel(''), $contrato_lateral ]
                                        );
        $row->layout = ['col-sm-8'];

        $row = $this->form->addFields(  [ new TLabel('Observação'), $observacao ]);
        $row->layout = ['col-sm-8'];
        
        
        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $nome->forceUpperCase();
        
        $contato_resp->setSize('100%');
                        
        $cpf->setMask('999.999.999-99', true);
        $cnpj->setMask('99.999.999/9999-99', true);
        
        $cpf->addValidation('doc', new TMinLengthValidator, array(14));
        $cpf->addValidation('doc', new TMaxLengthValidator, array(14));

        $cnpj->addValidation('doc', new TMinLengthValidator, array(18));
        $cnpj->addValidation('doc', new TMaxLengthValidator, array(18));
        
        $renda->setNumericMask(2,',','.', true);

        $tipo_doc->setChangeAction(new TAction(array($this, 'mudaEmissor')));
        self::mudaEmissor(array('tipo_doc'=>3));
        TEntry::disableField('form_Cliente', 'desc_doc');


        //$dt_nascimento->addValidation('Birthdate', new TRequiredValidator);
        /*
        
        $dt_nascimento->setDatabaseMask('yyyy-mm-dd');        
        */
        $dt_nascimento->setMask('dd/mm/yyyy');                              
        

        $cpf->setSize('100%');
        $cnpj->setSize('100%');

        $tipo_doc->setSize('100%');
        $desc_doc->setSize('100%');
        $doc->setSize('100%');
        $emissor->setSize('100%');
        $estado->setSize('100%');

        $nome->setSize('100%');

        $dt_nascimento->setSize('100%');
        $estado_civil->setSize('100%');
        $sexo->setSize('100%');

        $profissao->setSize('100%');
        $observacao->setSize('100%');
        $classe_social->setSize('100%');
        
        $id->setEditable(FALSE);        
        $nome->addValidation('Nome', new TRequiredValidator);
        //Fim do formulário


        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '88%';
        $this->fieldlist->enableSorting();

        // add field list to the form
        $detail_wrapper = new TElement('div');
        $detail_wrapper->add($this->fieldlist);

        $this->form->addContent( [ TElement::tag('h5', '<b>Contatos</b>', [ 'style'=>'background: whitesmoke; padding: 5px; border-radius: 5px; margin-top: 5px'] ) ] );
        $this->form->addContent( [ $detail_wrapper ] );

        $tipo = new TCombo('list_tipo[]');
        $contato = new TEntry('list_contato[]');
        $responsavel = new TEntry('list_responsavel[]');
        $principal = new TCombo('list_principal[]');
        $obs = new TEntry('list_obs[]');

        $tipo->addItems([
            'email' => 'E-mail',
            'fone_fixo' => 'Telefone Fixo',
            'celular' => 'Celular'
            ]);

        $principal->addItems([
            'sim' => 'Sim',
            'não' => 'Não'
            ]);

        $tipo->setSize('100%');
        $contato->setSize('100%');
        $responsavel->setSize('100%');
        $principal->setSize('100%');
        $obs->setSize('100%');

        $this->fieldlist->addField( '<b>Tipo</b>', $tipo);
        $this->fieldlist->addField( '<b>Contato</b>', $contato);
        $this->fieldlist->addField( '<b>Responsavel</b>', $responsavel);
        $this->fieldlist->addField( '<b>Principal</b>', $principal);
        $this->fieldlist->addField( '<b>Observação</b>', $obs);

        $this->form->addField($tipo);
        $this->form->addField($contato);
        $this->form->addField($responsavel);
        $this->form->addField($principal);
        $this->form->addField($obs);
        
        // create the form actions
        $this->form->addHeaderActionLink( _t('Close'),  new TAction(array('ClienteList','onReload')),  'fa:times red' );
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Limpar',  new TAction([$this, 'onClear']), 'fa:eraser red');
        $this->form->addActionLink('Cancelar', new TAction(array('ClienteList','onReload')),  'fa:times red' );
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    public function NovoContrato($param)
    {
        $chave = $param['id'];
        //var_dump($chave);

        //TSession::setValue('cliente_id', $param['id']);
        
        AdiantiCoreApplication::loadPage('ContratoForm_cliente', 'onEdit', ['cliente_id' => $chave], ['adianti_target_container' => 'adianti_right_panel', 'register_state' => 'false']);
    }
    /**
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            TTransaction::open('erphouse');
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new Cliente($key);
                $this->form->setData($object);
                if ($object->tipo_doc==3)
                {
                    TEntry::enableField('form_Cliente', 'desc_doc');  
                }
                
                $items  = Contato::where('cliente_id', '=', $key)->load();
                
                if ($items)
                {
                    $this->fieldlist->addHeader();
                    foreach($items  as $item )
                    {
                        $detail = new stdClass;
                        $detail->list_tipo = $item->tipo;
                        $detail->list_contato = $item->contato;
                        $detail->list_responsavel = $item->responsavel;
                        $detail->list_principal = $item->principal;
                        $detail->list_obs = $item->observacao;
                        $this->fieldlist->addDetail($detail);
                    }
                    
                    $this->fieldlist->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }
                
                TTransaction::close(); // close transaction
	    }
	    else
            {
                $this->onClear($param);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->fieldlist->addHeader();
        $this->fieldlist->addDetail( new stdClass );
        $this->fieldlist->addCloneAction();
    }
    
    /**
     * Save the Cliente and the Contato's
     */
    public static function onSave($param)
    {
        try
        {   
            TTransaction::open('erphouse');
            
            $id = (int) $param['id'];
            $master = new Cliente;
            $master->fromArray( $param);

            $master->cpf = str_replace(['.','-'], ['',''], $master->cpf);
            $master->cnpj = str_replace(['.','/','-'], ['','',''], $master->cnpj);

            $master->store(); // save master object
            
            // delete details
            Contato::where('cliente_id', '=', $master->id)->delete();
            
            if( !empty($param['list_tipo']) AND is_array($param['list_tipo']) )
            {
                foreach( $param['list_tipo'] as $row => $tipo)
                {
                    if (!empty($tipo))
                    {
                        $detail = new Contato;
                        $detail->cliente_id = $master->id;
                        $detail->tipo = $param['list_tipo'][$row];
                        $detail->contato = $param['list_contato'][$row];
                        $detail->responsavel = $param['list_responsavel'][$row];
                        $detail->principal = $param['list_principal'][$row];
                        $detail->observacao = $param['list_obs'][$row];
                        $detail->store();
                    }
                }
            }
            
            $data = new stdClass;
            $data->id = $master->id;
            TForm::sendData('form_Cliente', $data);
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    //Habilita o campo outro tipo de documento caso Outros seja selecionado
    public static function mudaEmissor($param)
    {
        
        //echo $param;
        if (!empty($param['tipo_doc'] == 3))
        {
            TEntry::enableField('form_Cliente', 'desc_doc');            
        }
        elseif (!empty($param['tipo_doc'] != 3))
        {   
            TEntry::clearField('form_Cliente', 'desc_doc');
            TEntry::disableField('form_Cliente', 'desc_doc');
        }
    }

     /**
     * Autocompleta outros campos a partir do CNPJ
     */
    public static function onExitCNPJ($param)
    {
        session_write_close();
        
        try
        {
            $cnpj = preg_replace('/[^0-9]/', '', $param['doc']);
            $url  = 'http://receitaws.com.br/v1/cnpj/'.$cnpj;
            
            $content = @file_get_contents($url);
            
            if ($content !== false)
            {
                $cnpj_data = json_decode($content);
                
                
                $data = new stdClass;
                if (is_object($cnpj_data) && $cnpj_data->status !== 'ERROR')
                {
                    $data->tipo = 'J';
                    $data->nome = $cnpj_data->nome;
                    $data->nome_fantasia = !empty($cnpj_data->fantasia) ? $cnpj_data->fantasia : $cnpj_data->nome;
                    
                    if (empty($param['cep']))
                    {
                        $data->cep = $cnpj_data->cep;
                        $data->numero = $cnpj_data->numero;
                    }
                    
                    if (empty($param['fone']))
                    {
                        $data->fone = $cnpj_data->telefone;
                    }
                    
                    if (empty($param['email']))
                    {
                        $data->email = $cnpj_data->email;
                    }
                    
                    TForm::sendData('form_Pessoa', $data, false, true);
                }
                else
                {
                    $data->nome = '';
                    $data->nome_fantasia = '';
                    $data->cep = '';
                    $data->numero = '';
                    $data->telefone = '';
                    $data->email = '';
                    TForm::sendData('form_Pessoa', $data, false, true);
                }
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

}
