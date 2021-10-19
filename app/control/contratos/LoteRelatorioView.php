<?php
/**
 * LoteViewReport Report
 * @author  <your name here>
 */
class LoteRelatorioView extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_LoteView_report');
        $this->form->setFormTitle('LoteView Report');
        

        // create the form fields
        $contrato_id = new TDBUniqueSearch('contrato_id', 'erphouse', 'Contrato', 'id', 'fornecedor_id');
        $cliente_id = new TDBUniqueSearch('cliente_id', 'erphouse', 'Cliente', 'id', 'nome');
        $cliente = new TDBUniqueSearch('cliente', 'erphouse', 'Cliente', 'id', 'nome');
        $fornecedor = new TEntry('fornecedor');
        $dt_inicio = new TEntry('dt_inicio');
        $dt_fim = new TEntry('dt_fim');
        $data_prog = new TEntry('data_prog');
        $forma_pagamento = new TEntry('forma_pagamento');
        $efetivacao = new TDBUniqueSearch('efetivacao', 'erphouse', 'Efetivacao', 'id', 'contrato_id');
        $ativo = new TEntry('ativo');
        $total_efetivado = new TEntry('total_efetivado');
        $output_type = new TRadioGroup('output_type');


        // add the fields
        $this->form->addFields( [ new TLabel('Contrato Id') ], [ $contrato_id ] );
        $this->form->addFields( [ new TLabel('Cliente Id') ], [ $cliente_id ] );
        $this->form->addFields( [ new TLabel('Cliente') ], [ $cliente ] );
        $this->form->addFields( [ new TLabel('Fornecedor') ], [ $fornecedor ] );
        $this->form->addFields( [ new TLabel('Dt Inicio') ], [ $dt_inicio ] );
        $this->form->addFields( [ new TLabel('Dt Fim') ], [ $dt_fim ] );
        $this->form->addFields( [ new TLabel('Data Prog') ], [ $data_prog ] );
        $this->form->addFields( [ new TLabel('Forma Pagamento') ], [ $forma_pagamento ] );
        $this->form->addFields( [ new TLabel('Efetivacao') ], [ $efetivacao ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );
        $this->form->addFields( [ new TLabel('Total Efetivado') ], [ $total_efetivado ] );
        $this->form->addFields( [ new TLabel('Output') ], [ $output_type ] );

        $output_type->addValidation('Output', new TRequiredValidator);


        // set sizes
        $contrato_id->setSize('100%');
        $cliente_id->setSize('100%');
        $cliente->setSize('100%');
        $fornecedor->setSize('100%');
        $dt_inicio->setSize('100%');
        $dt_fim->setSize('100%');
        $data_prog->setSize('100%');
        $forma_pagamento->setSize('100%');
        $efetivacao->setSize('100%');
        $ativo->setSize('100%');
        $total_efetivado->setSize('100%');
        $output_type->setSize('100%');


        
        $output_type->addItems(array('html'=>'HTML', 'pdf'=>'PDF', 'rtf'=>'RTF', 'xls' => 'XLS'));
        $output_type->setLayout('horizontal');
        $output_type->setUseButton();
        $output_type->setValue('pdf');
        $output_type->setSize(70);
        
        // add the action button
        $btn = $this->form->addAction(_t('Generate'), new TAction(array($this, 'onGenerate')), 'fa:cog');
        $btn->class = 'btn btn-sm btn-primary';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Generate the report
     */
    function onGenerate()
    {
        try
        {
            // open a transaction with database 'erphouse'
            TTransaction::open('erphouse');
            
            // get the form data into an active record
            $data = $this->form->getData();
            
            $this->form->validate();
            
            $repository = new TRepository('LoteView');
            $criteria   = new TCriteria;
            
            if ($data->contrato_id)
            {
                $criteria->add(new TFilter('contrato_id', '=', "{$data->contrato_id}"));
            }
            if ($data->cliente_id)
            {
                $criteria->add(new TFilter('cliente_id', '=', "{$data->cliente_id}"));
            }
            if ($data->cliente)
            {
                $criteria->add(new TFilter('cliente', '=', "{$data->cliente}"));
            }
            if ($data->fornecedor)
            {
                $criteria->add(new TFilter('fornecedor', 'like', "%{$data->fornecedor}%"));
            }
            if ($data->dt_inicio)
            {
                $criteria->add(new TFilter('dt_inicio', 'like', "%{$data->dt_inicio}%"));
            }
            if ($data->dt_fim)
            {
                $criteria->add(new TFilter('dt_fim', 'like', "%{$data->dt_fim}%"));
            }
            if ($data->data_prog)
            {
                $criteria->add(new TFilter('data_prog', 'like', "%{$data->data_prog}%"));
            }
            if ($data->forma_pagamento)
            {
                $criteria->add(new TFilter('forma_pagamento', 'like', "%{$data->forma_pagamento}%"));
            }
            if ($data->efetivacao)
            {
                $criteria->add(new TFilter('efetivacao', '=', "{$data->efetivacao}"));
            }
            if ($data->ativo)
            {
                $criteria->add(new TFilter('ativo', 'like', "%{$data->ativo}%"));
            }
            if ($data->total_efetivado)
            {
                $criteria->add(new TFilter('total_efetivado', 'like', "%{$data->total_efetivado}%"));
            }

           
            $objects = $repository->load($criteria, FALSE);
            $format  = $data->output_type;
            
            if ($objects)
            {
                $widths = array(100,100,100,100,50,50,50,100,50,100,100);
                
                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths);
                        break;
                    case 'xls':
                        $tr = new TTableWriterXLS($widths);
                        break;
                    case 'rtf':
                        $tr = new TTableWriterRTF($widths);
                        break;
                }
                
                // create the document styles
                $tr->addStyle('title', 'Arial', '10', 'B',   '#ffffff', '#A3A3A3');
                $tr->addStyle('datap', 'Arial', '10', '',    '#000000', '#EEEEEE');
                $tr->addStyle('datai', 'Arial', '10', '',    '#000000', '#ffffff');
                $tr->addStyle('header', 'Arial', '16', '',   '#ffffff', '#6B6B6B');
                $tr->addStyle('footer', 'Times', '10', 'I',  '#000000', '#A3A3A3');
                
                // add a header row
                $tr->addRow();
                $tr->addCell('LoteView', 'center', 'header', 11);
                
                // add titles row
                $tr->addRow();
                $tr->addCell('Contrato Id', 'right', 'title');
                $tr->addCell('Cliente Id', 'right', 'title');
                $tr->addCell('Cliente', 'left', 'title');
                $tr->addCell('Fornecedor', 'left', 'title');
                $tr->addCell('Dt Inicio', 'left', 'title');
                $tr->addCell('Dt Fim', 'left', 'title');
                $tr->addCell('Data Prog', 'left', 'title');
                $tr->addCell('Forma Pagamento', 'left', 'title');
                $tr->addCell('Efetivacao', 'left', 'title');
                $tr->addCell('Ativo', 'left', 'title');
                $tr->addCell('Total Efetivado', 'right', 'title');

                
                // controls the background filling
                $colour= FALSE;
                
                // data rows
                foreach ($objects as $object)
                {
                    $style = $colour ? 'datap' : 'datai';
                    $tr->addRow();
                    $tr->addCell($object->contrato_id, 'right', $style);
                    $tr->addCell($object->cliente_id, 'right', $style);
                    $tr->addCell($object->cliente, 'left', $style);
                    $tr->addCell($object->fornecedor, 'left', $style);
                    $tr->addCell($object->dt_inicio, 'left', $style);
                    $tr->addCell($object->dt_fim, 'left', $style);
                    $tr->addCell($object->data_prog, 'left', $style);
                    $tr->addCell($object->forma_pagamento, 'left', $style);
                    $tr->addCell($object->efetivacao, 'left', $style);
                    $tr->addCell($object->ativo, 'left', $style);
                    $tr->addCell($object->total_efetivado, 'right', $style);

                    
                    $colour = !$colour;
                }
                
                // footer row
                $tr->addRow();
                $tr->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 11);
                
                // stores the file
                if (!file_exists("app/output/LoteView.{$format}") OR is_writable("app/output/LoteView.{$format}"))
                {
                    $tr->save("app/output/LoteView.{$format}");
                }
                else
                {
                    throw new Exception(_t('Permission denied') . ': ' . "app/output/LoteView.{$format}");
                }
                
                // open the report file
                parent::openFile("app/output/LoteView.{$format}");
                
                // shows the success message
                new TMessage('info', 'Report generated. Please, enable popups.');
            }
            else
            {
                new TMessage('error', 'No records found');
            }
    
            // fill the form with the active record data
            $this->form->setData($data);
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}
