<?php

namespace App\Services;

use App\Models\Company as ModelsCompany;
use Greenter\Model\Sale\Note;
use Illuminate\Support\Facades\Storage;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use DateTime;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Report\HtmlReport;
use Greenter\Report\PdfReport;
use Greenter\Report\Resolver\DefaultTemplateResolver;

class SunatService
{
    public function getSee($company)
    {
        $see = new See();
        $see->setCertificate(Storage::get($company->cert_path));
        $see->setService($company->production ?
            SunatEndpoints::FE_PRODUCCION :
            SunatEndpoints::FE_BETA);
        $see->setClaveSOL(
            $company->ruc,
            $company->sol_user,
            $company->sol_pass
        );
        return $see;
    }
    public function getInvoice($data)
    {
        // Venta
        return (new Invoice())
            ->setUblVersion($data['ublVersion'] ?? '2.1') // UBL Version 2.1
            ->setTipoOperacion($data['tipoOperacion']?? null) // Venta - Catalog. 51
            ->setTipoDoc($data['tipoDoc']?? null) // Factura - Catalog. 01 
            ->setSerie($data['serie']?? null)
            ->setCorrelativo($data['correlativo'] ?? null)
            ->setFechaEmision(new DateTime($data['fechaEmision'] ?? null))
            ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
            ->setTipoMoneda($data['tipoMoneda'] ?? null) // Sol - Catalog. 02
            ->setCompany($this->getCompany($data['company']))
            ->setClient($this->getClient($data['client']))
            
            //MtoOper
            ->setMtoOperExoneradas($data['mtoOperExoneradas'])
            ->setMtoOperGravadas($data['mtoOperGravadas'])
            ->setMtoOperInafectas($data['mtoOperInafectas'])
            ->setMtoOperExportacion($data['mtoOperExportacion'])
            ->setMtoOperGratuitas($data['mtoOperGratuitas'])

            //Impuestos
            ->setMtoIGV($data['mtoIGV'])
            ->setMtoOperGravadas($data['mtoOperGravadas'])
            ->setIcbper($data['icbper'])
            ->setMtoIGVGratuitas($data['mtoIGVGratuitas'])
            
            //totales
            ->setValorVenta($data['valorVenta'])
            ->setTotalImpuestos($data['totalImpuestos'])
            ->setSubTotal($data['subTotal'])
            ->setRedondeo($data['redondeo'])
            ->setMtoImpVenta($data['mtoImpVenta'])
            
           //Productos
            ->setDetails($this->getDetails($data['details']))

            //leyenda
            ->setLegends($this->getLegends($data['legends']));
    }
     public function getNote($data){
        return (new Note())
        
            ->setUblVersion($data['ublVersion'] ?? '2.1') 
            ->setTipoDoc($data['tipoDoc']?? null) // Factura - Catalog. 01 
            ->setSerie($data['serie']?? null)
            ->setCorrelativo($data['correlativo'] ?? null)
            ->setFechaEmision(new DateTime($data['fechaEmision'] ?? null))
            ->setTipDocAfectado($data['tipDocAfectado'] ?? null)
            ->setNumDocfectado($data['numDocfectado'] ?? null)
            ->setCodMotivo($data['codMotivo'] ?? null) //
            ->setDesMotivo($data['desMotivo']?? null )
            ->setTipoMoneda($data['tipoMoneda'] ?? null)
            ->setCompany($this->getCompany($data['company']))
            ->setClient($this->getClient($data['client']))
        
         //MtoOper
            ->setMtoOperExoneradas($data['mtoOperExoneradas'])
            ->setMtoOperGravadas($data['mtoOperGravadas'])
            ->setMtoOperInafectas($data['mtoOperInafectas'])
            ->setMtoOperExportacion($data['mtoOperExportacion'])
            ->setMtoOperGratuitas($data['mtoOperGratuitas'])

            //Impuestos
            ->setMtoIGV($data['mtoIGV'])
            ->setMtoOperGravadas($data['mtoOperGravadas'])
            ->setIcbper($data['icbper'])
            ->setMtoIGVGratuitas($data['mtoIGVGratuitas'])
            
            //totales
            ->setValorVenta($data['valorVenta'])
            ->setTotalImpuestos($data['totalImpuestos'])
            ->setSubTotal($data['subTotal'])
            ->setRedondeo($data['redondeo'])
            ->setMtoImpVenta($data['mtoImpVenta'])
            
           //Productos
            ->setDetails($this->getDetails($data['details']))

            //leyenda
            ->setLegends($this->getLegends($data['legends']));
    }


    public function getCompany($company)
    {
        return (new Company())
            ->setRuc($company['ruc'] ?? null)
            ->setRazonSocial($company['razonSocial'] ?? null)
            ->setNombreComercial($company['nombreComercial'] ?? null)
            ->setAddress($this->getAddress($company['address']) ?? null ?? null ?? null);
    }
    public function getClient($client)
    {
        // Cliente
        return (new Client())
            ->setTipoDoc($client['tipoDoc'] ?? null ?? null ?? null)
            ->setNumDoc($client['numDoc'] ?? null ?? null ?? null)
            ->setRznSocial($client['rznSocial'] ?? null ?? null ?? null);
    }
    public function getAddress($address)
    {
        // Emisor
        return (new Address())
            ->setUbigueo($address['ubigueo'] ?? null ?? null)
            ->setDepartamento($address['departamento'] ?? null ?? null)
            ->setProvincia($address['provincia'] ?? null ?? null)
            ->setDistrito($address['distrito'] ?? null ?? null)
            ->setUrbanizacion($address['urbanizacion'] ?? null ?? null)
            ->setDireccion($address['direccion'] ?? null ?? null)
            ->setCodLocal($address['codLocal'] ?? null ?? null); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.
    }
    public function getDetails($details)
    {
        $green_details = [];

        foreach ($details as $detail) {
            $green_details[] =  (new SaleDetail())
                ->setTipAfeIgv($detail['tipAfeIgv'] ?? null)
                ->setCodProducto($detail['codProducto'] ?? null) // Codigo de producto
                ->setUnidad($detail['unidad'] ?? null) // Unidad - Catalog. 03
                ->setCantidad($detail['cantidad'] ?? null)
                ->setMtoValorUnitario($detail['mtoValorUnitario'] ?? null)
                ->setDescripcion($detail['descripcion'] ?? null)
                ->setMtoBaseIgv($detail['mtoBaseIgv'] ?? null)
                ->setPorcentajeIgv($detail['porcentajeIgv'] ?? null)
                ->setIgv($detail['igv'])
                ->setFactorIcbper($detail['factorIcbper'] ?? null)
                ->setIcbper($detail['icbper'] ?? null)
                ->setTotalImpuestos($detail['totalImpuestos'] ?? null)
                ->setMtoValorVenta($detail['mtoValorVenta'] ?? null)
                ->setMtoPrecioUnitario($detail['mtoPrecioUnitario'] ?? null);
        }
        return $green_details;
    }
    public function getLegends($legends)
    {
        $green_legends = [];
        foreach ($legends as $legend){
            $green_legends[] = (new Legend() ?? null)
                ->setCode($legend['code'] ?? null) // Monto en letras - Catalog. 52
                ->setValue($legend['value'] ?? null);
            }
         return $green_legends;
    }
    public function sunatResponse($result)
    {
        $response['success'] = $result->isSuccess();

        // Verificamos que la conexión con SUNAT fue exitosa.
        if (! $response['success']) {
            // Mostrar error al conectarse a SUNAT.
            $response['error'] = [
                'code' => $result->getError()->getCode(),
                'message' => $result->getError()->getMessage()
            ];

            return $response;
        }

        $response['cdrZip'] = base64_encode($result->getCdrZip());
        $cdr = $result->getCdrResponse();

        $response['cdrResponse'] = [
            'code' => (int)$cdr->getCode(),
            'description' => $cdr->getDescription(),
            'notes' => $cdr->getNotes()
        ];

        return $response;

        
        
    }
    public function getHtmlReport($invoice){

        $report = new HtmlReport();
        
        $resolver = new DefaultTemplateResolver();
        $report->setTemplate($resolver->getTemplate($invoice));

        $ruc = $invoice->getCompany()->getRuc();
        $company = ModelsCompany::where('ruc',$ruc)
        ->where('user_id',auth('api')->id())
        ->first();

        $params = [
            'system' => [
                'logo' => Storage::get($company->logo_path), // Logo de Empresa
                'hash' => 'qqnr2dN4p/HmaEA/CJuVGo7dv5g=', // Valor Resumen 
            ],
            'user' => [
                'header'     => 'Telf: <b>(01) 123375</b>', // Texto que se ubica debajo de la dirección de empresa
                'extras'     => [
                    // Leyendas adicionales
                    ['name' => 'CONDICION DE PAGO', 'value' => 'Efectivo'     ],
                    ['name' => 'VENDEDOR'         , 'value' => 'GITHUB SELLER'],
                ],
                'footer' => '<p>Nro Resolucion: <b>3232323</b></p>'
            ]
        ];

        return $html = $report->render($invoice, $params);
    }
    public function generatePdfReport($invoice){
         $htmlReport  = new HtmlReport();
        
        $resolver = new DefaultTemplateResolver();
        $htmlReport ->setTemplate($resolver->getTemplate($invoice));

        $ruc = $invoice->getCompany()->getRuc();
        $company = ModelsCompany::where('ruc',$ruc)
        ->where('user_id',auth('api')->id())
        ->first();

        $report = new PdfReport($htmlReport);
        $report->setOptions( [
            'no-outline',
            'viewport-size' => '1280x1024',
            'page-width' => '21cm',
            'page-height' => '29.7cm',
        ]);
        $report->setBinPath(env('WKHTMLTOPDF_PATH'));

        $params = [
            'system' => [
                'logo' => Storage::get($company->logo_path), // Logo de Empresa
                'hash' => 'qqnr2dN4p/HmaEA/CJuVGo7dv5g=', // Valor Resumen 
            ],
            'user' => [
                'header'     => 'Telf: <b>(01) 123375</b>', // Texto que se ubica debajo de la dirección de empresa
                'extras'     => [
                    // Leyendas adicionales
                    ['name' => 'CONDICION DE PAGO', 'value' => 'Efectivo'     ],
                    ['name' => 'VENDEDOR'         , 'value' => 'GITHUB SELLER'],
                ],
                'footer' => '<p>Nro Resolucion: <b>3232323</b></p>'
            ]
        ];
        $pdf = $report->render($invoice, $params);

        if ($pdf === null) {
            $error = $report->getExporter()->getError();
            echo 'Error: '.$error;
            return;
        }
        Storage::put('invoice/'. $invoice->getName() . '.pdf', $pdf); 

    }

    
}
