<?php
/**
 * SystemUserForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemUserForm extends TWindow
{
    protected $form; // form
    protected $program_list;
    protected $fornecedor_list;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();

        parent::setSize( 0.7, 0.99);
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_System_user');
        $this->form->setFormTitle( 'Criar Usuário' );
        
        // create the form fields
        $id            = new TEntry('id');
        $name          = new TEntry('name');
        $login         = new TEntry('login');
        $password      = new TPassword('password');
        $repassword    = new TPassword('repassword');
        $email         = new TEntry('email');
        //$unit_id       = new TDBCombo('system_unit_id','permission','SystemUnit','id','name');
        $groups        = new TDBCheckGroup('groups','permission','CargoAtivo','id','name');
        $frontpage_id  = new TDBUniqueSearch('frontpage_id', 'permission', 'SystemProgram', 'id', 'name', 'name');
        $units         = new TDBCheckGroup('units','permission','SystemUnit','id','name');
        $telefone = new TEntry('telefone');
        
        $units->setLayout('horizontal');
        if ($units->getLabels())
        {
            foreach ($units->getLabels() as $label)
            {
                $label->setSize(200);
            }
        }
        
        $groups->setLayout('horizontal');
        if ($groups->getLabels())
        {
            foreach ($groups->getLabels() as $label)
            {
                $label->setSize(200);
            }
        }
        
        // define the sizes
        $id->setSize('100%');
        $name->setSize('100%');

        $login->setSize('100%');
        $email->setSize('100%');
        
        $password->setSize('100%');
        
        $repassword->setSize('100%');
        
        //$unit_id->setSize('100%');
        $frontpage_id->setSize('100%');
        $frontpage_id->setMinLength(1);

        $telefone->setSize('100%');
        $telefone->setMask('(99) 99999-9999', true);
        $telefone->addValidation('Fone', new TMaxLengthValidator, array(15));
        
        // outros
        $id->setEditable(false);
        
        // validations
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $login->addValidation('Login', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        
        //Adicionando os campos no formulário
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel(_t('Name'))], [$name]);
        $this->form->addFields([new TLabel(_t('Email'))], [$email], [new TLabel(_t('Login'))], [$login] );
        $this->form->addFields( [new TLabel(_t('Password'))], [$password],  [new TLabel(_t('Password confirmation'))], [$repassword] );
        $this->form->addFields( [new TLabel(_t('Front page'))], [$frontpage_id], [new TLabel('Telefone')], [$telefone] );

        //Espaço para selecionar os cargos
        $this->form->addFields( [new TFormSeparator('Cargo')] );
        $this->form->addFields( [$groups] );


        //$this->form->addFields( [new TFormSeparator('  ')] );
        $this->fornecedor_list = new TCheckList('fornecedor_list');
        $this->fornecedor_list->setIdColumn('id');
        $this->fornecedor_list->addColumn('id',    'ID',    'center',  '10%');
        $col_name    = $this->fornecedor_list->addColumn('nome', _t('Name'),    'left',   '50%');
        $col_fone = $this->fornecedor_list->addColumn('fone', 'Fone',    'left',   '40%');
        $col_fone->enableAutoHide(500);
        $this->fornecedor_list->setHeight(150);
        $this->fornecedor_list->makeScrollable();
        
        $col_name->enableSearch(); 
        $search_name = $col_name->getInputSearch();
        $search_name->placeholder = _t('Search');
        $search_name->style = 'width:50%;margin-left: 4px; border-radius: 4px';
                
        $this->form->addFields( [new TFormSeparator('Atribuir Fornecedor')] );
        $this->form->addFields( [$this->fornecedor_list] );
        
        TTransaction::open('permission');
        $this->fornecedor_list->addItems( Pessoa::get() );
        TTransaction::close();
        
        //Botões de ação do formulário
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save' );
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink('Limpar', new TAction(array($this, 'onEdit')),  'fa:eraser red' );
        $this->form->addActionLink('Cancelar', new TAction(array('SystemUserList','onReload')),  'fa:times red' );

        $this->form->addHeaderActionLink( 'Fechar',  new TAction(['SystemUserList', 'onReload']), 'fa:times red' );

        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', 'SystemUserList'));
        $container->add($this->form);
        //$container->add($panel);
        

        // add the container to the page
        parent::add($container);
    }

    /**
     * Save user data
     */
    public function onSave($param)
    {
        try
        {
            // open a transaction with database 'permission'
            TTransaction::open('permission');
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData();
            $this->form->setData($data);
            
            $object = new SystemUser;
            $object->fromArray( (array) $data );
            
            $senha = $object->password;
            
            if( empty($object->login) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }
            
            if( empty($object->id) )
            {
                if (SystemUser::newFromLogin($object->login) instanceof SystemUser)
                {
                    throw new Exception(_t('An user with this login is already registered'));
                }
                
                if (SystemUser::newFromEmail($object->email) instanceof SystemUser)
                {
                    throw new Exception(_t('An user with this e-mail is already registered'));
                }
                
                if ( empty($object->password) )
                {
                    throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
                }
                
                $object->active = 'sim';
            }
            
            if( $object->password )
            {
                if( $object->password !== $param['repassword'] )
                    throw new Exception(_t('The passwords do not match'));
                
                $object->password = md5($object->password);
            }
            else
            {
                unset($object->password);
            }
            
            $object->store();
            $object->clearParts();
            
            if( !empty($data->groups) )
            {
                foreach( $data->groups as $group_id )
                {
                    $object->addSystemUserGroup( new SystemGroup($group_id) );
                }
            }

            /*
            
            if (!empty($data->program_list))
            {
                foreach ($data->program_list as $program_id)
                {
                    $object->addSystemUserProgram( new SystemProgram( $program_id ) );
                }
            } */

            /*Atribui os fornecedores marcados para o usuário
            /Chama a classe Pessoa (Fornecedor)
            /
            */
            if (!empty($data->fornecedor_list))
            {
                foreach ($data->fornecedor_list as $fornecedor_id)
                {
                    $object->addUsuarioFornecedor( new Pessoa( $fornecedor_id ) );
                }
            }
                        
            $data = new stdClass;
            $data->id = $object->id;
            TForm::sendData('form_System_user', $data);
            
            // close the transaction
            TTransaction::close();
            
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());

            //$this->form->setData( $this->form->getData() ); // keep form data

            TTransaction::rollback();

            
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database 'permission'
                TTransaction::open('permission');
                
                // instantiates object System_user
                $object = new SystemUser($key);
                
                unset($object->password);
                
                $groups = array();
                $units  = array();
                $fornecs = array();
                
                if( $groups_db = $object->getSystemUserGroups() )
                {
                    foreach( $groups_db as $group )
                    {
                        $groups[] = $group->id;
                    }
                }
                
                if( $units_db = $object->getSystemUserUnits() )
                {
                    foreach( $units_db as $unit )
                    {
                        $units[] = $unit->id;
                    }
                }
                
                $program_ids = array();
                foreach ($object->getSystemUserPrograms() as $program)
                {
                    $program_ids[] = $program->id;
                }

                $fornec_ids = array();
                foreach ($object->getUsuarioFornecedor() as $fornec)
                {
                    $fornec_ids[] = $fornec->id;
                }

                $object->fornecedor_list = $fornec_ids;
                $object->program_list = $program_ids;
                $object->groups = $groups;
                $object->units  = $units;
                
                // fill the form with the active record data
                $this->form->setData($object);
                                
                // close the transaction
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
