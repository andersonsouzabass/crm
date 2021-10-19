<?php
/**
 * Cadastro de clientes
 *
 *
 * @author     Anderson Souza
 * 
 */
class ClienteFormTemp extends TWindow
{
    protected $form; // form
    protected $contato_list;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::setSize(0.68, null);
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Cliente');
        $this->form->setFormTitle('Criar Cliente');
        $this->form->setProperty('style', 'margin:0;border:0');
        $this->form->setClientValidation(true);
        

        // create the form fields
        $id = new TEntry('id');
        
        $nome = new TEntry('nome');
        $dt_nascimento = new TDate('dt_nascimento');

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
        
        //$profissao->enableSearch();
        //$classe_social->enableSearch();        
       
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
                                        [ new TLabel('Data de Nascimento'), $dt_nascimento ],
                                        [ new TLabel('Orientação'), $sexo ]
                                        );
        $row->layout = ['col-sm-2', 'col-sm-3', 'col-sm-2', 'col-sm-2'];

        $row = $this->form->addFields(  [ new TLabel('CPF'), $cpf ],
                                        [ new TLabel('CNPJ'), $cnpj ],
                                        [ new TLabel('Renda'), $renda ],
                                        [ new TLabel('Classe Social'), $classe_social ]                                        
                                        );
        $row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-3'];

        
        $row = $this->form->addFields(  [ new TLabel('Documento'), $tipo_doc ],
                                        [ new TLabel('Outro Documento'), $desc_doc ],
                                        [ new TLabel('Número Doc'), $doc ],
                                        [ new TLabel('Orgão Emissor'), $emissor ],
                                        );
        $row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-3'];

        $row = $this->form->addFields(  [ new TLabel('Profissão'), $profissao ],
                                        [ new TLabel('Estado Civil'), $estado_civil ]                                        
                                        );
        $row->layout = ['col-sm-6', 'col-sm-3'];

        $row = $this->form->addFields(  [ new TLabel('Observação'), $observacao ]);
        $row->layout = ['col-sm-9'];
       
        
        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
                      
        $cpf->setMask('999.999.999-99', true);
        $cnpj->setMask('99.999.999/9999-99', true);
        
        $cpf->addValidation('doc', new TMinLengthValidator, array(14));
        $cpf->addValidation('doc', new TMaxLengthValidator, array(14));

        $cnpj->addValidation('doc', new TMinLengthValidator, array(18));
        $cnpj->addValidation('doc', new TMaxLengthValidator, array(18));
        
        $renda->setNumericMask(2,',','.', true);

        //$dt_nascimento->addValidation('Birthdate', new TRequiredValidator);
        $dt_nascimento->setMask('dd/mm/yyyy');
        $dt_nascimento->setDatabaseMask('yyyy-mm-dd');        
      
        $tipo_doc->setChangeAction(new TAction(array($this, 'mudaEmissor')));
        self::mudaEmissor(array('tipo_doc'=>3));

        $desc_doc->setEditable(FALSE);
        
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

        // tabela de adição de contatos
        $tipo_contato   = new TCombo('tipo_contato[]');
        $contato        = new TEntry('contato[]');
        $responsavel    = new TEntry('responsavel[]');
        $status_contato = new TCombo('status_contato[]');

        $tipo_contato->setSize('100%');
        $contato->setSize('100%');
        $responsavel->setSize('100%');
        $status_contato->setSize('100%');
        
        $tipo_contato->addItems([
            'email' => 'E-mail',
            'fone_fixo' => 'Telefone Fixo',
            'celular' => 'Celular'
            ]);

        $status_contato->addItems([
            'sim' => 'Sim',
            'não' => 'Não'
            ]);
        
        $this->form->addField($tipo_contato);
        $this->form->addField($contato);
        $this->form->addField($responsavel);
        $this->form->addField($status_contato);

        $this->contato_list = new TFieldList;
        $this->contato_list->addField( '<b>Tipo</b>', $tipo_contato,     ['width' => '15%']);
        $this->contato_list->addField( '<b>Contato</b>',   $contato,  ['width' => '30%']);
        $this->contato_list->addField( '<b>Responsável</b>',   $responsavel,  ['width' => '25%']);
        $this->contato_list->addField( '<b>Principal</b>',  $status_contato, ['width' => '20%']);
        $this->contato_list-> width = '77%';
        $this->contato_list->enableSorting();

        $this->contato_list->addHeader('Lista de Contatos');
        $this->contato_list->addDetail( new stdClass );        
        $this->contato_list->addCloneAction();
        
        $this->form->addFields( [new TFormSeparator('Contatos') ] );
        $this->form->addFields( [$this->contato_list] );
        //Fim de contatos

        
        // create the form actions
        $this->form->addHeaderActionLink( _t('Close'),  new TAction(array('ClienteList','onReload')),  'fa:times red' );
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Limpar',  new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addActionLink('Cancelar', new TAction(array('ClienteList','onReload')),  'fa:times red' );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);        
        parent::add($container);
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
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {

        try
        {
            TTransaction::open('erphouse'); // open a transaction
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Cliente;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data            
            $object->store(); // save the object
           
            if( !empty($param['tipo_contato']) AND is_array($param['tipo_contato']) )
            {
                foreach( $param['tipo_contato'] as $row => $tipo_contato)
                {
                    if ($tipo_contato)
                    {
                        $contato = new Contato;
                        $contato->tipo  = $param['tipo_contato'][$row];
                        $contato->contato = $param['contato'][$row];
                        $contato->responsavel = $param['responsavel'][$row];
                        $contato->principal = $param['status_contato'][$row];
                        $contato->cliente_id = $object->id;
                        
                        $contato->store();                        
                        //$object->addContato($contato);
                    }
                }
            }
            
            // stores the object in the database
            $object->store();
            $data = new stdClass;           
            $data->id = $object->id;  
            TForm::sendData('form_Cliente', $data);
            //$this->form->setData($data); // fill form data

            TTransaction::close(); // close the transaction
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];
                TTransaction::open('erphouse');
                $object = new Cliente($key);
                
                // load the contacts (composition)
                $contacts = $object->getContatos();
                
                if ($contacts)
                {
                    $this->contacts->addHeader();
                    foreach ($contacts as $contact)
                    {
                        $contact_detail = new stdClass;
                        $contact_detail->tipo  = $contact->tipo;
                        $contact_detail->contato = $contact->contato;
                        $contact_detail->responsavel = $contact->responsavel;
                        $contact_detail->principal = $contact->principal;
                        
                        $this->contacts->addDetail($contact_detail);
                    }
                    
                    $this->contacts->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }

                
                $this->form->setData($object);
                
               
                
                TTransaction::close();
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Action to be executed when the user changes the state
     * @param $param Action parameters
     */
    
    
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
    
    
    
    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
