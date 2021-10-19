<?php
/**
 * Tela de registro de novo produto e edição
 *
 * 
 * @author     Anderson Souza
 */
class ProdutoFormView extends TPage
{
    protected $form; // form
    protected $detail_list_contratos;
    protected $detail_list_contas;
    
    /**
     * Page constructor
     */
    public function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_ProdutoView');
        $this->form->setFormTitle('Visualizar Produto');
        $this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
        
        $dropdown = new TDropDown('Opções', 'fa:th');
        //$dropdown->addAction(
        $dropdown->addAction( 'Imprimir', new TAction([$this, 'onPrint'], ['key'=>$param['key'], 'static' => '1']), 'far:file-pdf red');
        $dropdown->addAction( 'Editar', new TAction(['ProdutoForm', 'onEdit'],['key'=>$param['key']]), 'far:edit blue');
        $dropdown->addAction( 'Fechar', new TAction([$this, 'onClose']), 'fa:times red');
        
        $this->form->addHeaderWidget($dropdown);
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%'; 
        // $container->add(new TXMLBreadCrumb('menu.xml', 'PessoaList'));
        $container->add($this->form);

        parent::add($container);
    }
    
    /**
     * onEdit
     */
    public function onEdit($param)
    {
        try
        {
            TTransaction::open('erphouse');
            $master_object = new Produto($param['key']);
            
            $label_id = new TLabel('Id:', '#333333', '12px', '');
            //$label_Fornecedor = new TLabel('Fornecedor:', '#333333', '12px', '');
            $label_produto = new TLabel('Produto:', '#333333', '12px', '');
            $label_tipo = new TLabel('Tipo de Produto:', '#333333', '12px', '');
            $label_valor = new TLabel('Valor:', '#333333', '12px', '');
            $label_desconto = new TLabel('Desconto:', '#333333', '12px', '');
            $label_unidade = new TLabel('UN:', '#333333', '12px', '');
            $label_created_at = new TLabel('Criado em:', '#333333', '12px', '');
            $label_updated_at = new TLabel('Alterado em:', '#333333', '12px', '');
            
            $text_id  = new TTextDisplay($master_object->id, '#333333', '12px', '');
            $text_tipo  = new TTextDisplay($master_object->valor_fixo, '#333333', '12px', '');
            $text_produto  = new TTextDisplay($master_object->produto, '#333333', '12px', '');
            
            $text_created_at  = new TTextDisplay(TDateTime::convertToMask($master_object->created_at, 'yyyy-mm-dd hh:ii:ss', 'dd/mm/yyyy hh:ii:ss'), '#333333', '12px', '');
            $text_updated_at  = new TTextDisplay(TDateTime::convertToMask($master_object->updated_at, 'yyyy-mm-dd hh:ii:ss', 'dd/mm/yyyy hh:ii:ss'), '#333333', '12px', '');
            
            if($text_tipo='v')
            {
                $text_tipo='Venda';                
            }
            elseif($text_tipo='d')
            {
                $text_tipo='Doação';    
            }

            $this->form->addFields([$label_id],[$text_id]);
            $this->form->addFields([$label_tipo],[$text_tipo]);
            $this->form->addFields([$label_produto],[$text_produto]);
            $this->form->addFields([$label_created_at],[$text_created_at]);
            $this->form->addFields([$label_updated_at],[$text_updated_at]);
                        
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
   
    /**
     * Imprime a view
     */
    public function onPrint($param)
    {
        try
        {
            $this->onEdit($param);
            
            // string with HTML contents
            $html = clone $this->form;
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/pessoa.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Export', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file.'?rndval='.uniqid();
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Gera etiqueta
     */
    public function onGeraEtiqueta($param)
    {
        try
        {
            $this->onEdit($param);
            
            TTransaction::open('erphouse');
            $pessoa = new Pessoa($param['key']);
            
            $replaces = $pessoa->toArray();
            $replaces['cidade'] = $pessoa->cidade;
            $replaces['estado'] = $pessoa->cidade->estado;
            
            // string with HTML contents
            $html = new THtmlRenderer('app/resources/mail-label.html');
            $html->enableSection('main', $replaces);
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/etiqueta.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Export', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file.'?rndval='.uniqid();
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
