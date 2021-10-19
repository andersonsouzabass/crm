<?php
/**
 * GrupoForm
 *
 * @version    1.0
 * @package    erphouse
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class GrupoForm extends TWindow
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
     
        parent::setSize( 0.6, null);
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();
        
        //parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['GrupoList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase('erphouse');              // defines the database
        $this->setActiveRecord('Grupo');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Grupo');
        $this->form->setFormTitle('Criar Perfil de Fornecedor');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
       
        $nome->addValidation('Nome', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');

        $id->setEditable(FALSE);
        
        // create the form actions
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save' );
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink('Limpar', new TAction(array($this, 'onEdit')),  'fa:eraser red' );
        $this->form->addActionLink('Cancelar', new TAction(array('GrupoList','onReload')),  'fa:times red' );
        $this->form->addHeaderActionLink( 'Fechar',  new TAction(['GrupoList', 'onReload']), 'fa:times red' );

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
