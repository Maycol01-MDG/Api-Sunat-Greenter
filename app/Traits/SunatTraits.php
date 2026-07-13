<?php

namespace App\Traits;


use Luecano\NumeroALetras\NumeroALetras;

trait SunatTraits{
     public function setTotales(&$data)
    {
        $details = collect($data['details']);

        $data['mtoOperGravadas'] = $details
            ->where('tipAfeIgv', 10)
            ->sum('mtoValorVenta');
        $data['mtoOperExoneradas'] = $details
            ->where('tipAfeIgv', 20)
            ->sum('mtoValorVenta');
        $data['mtoOperInafectas'] = $details
            ->where('tipAfeIgv', 30)
            ->sum('mtoValorVenta');
        $data['mtoOperExportacion'] = $details
            ->where('tipAfeIgv', 40)
            ->sum('mtoValorVenta');
        $data['mtoOperGratuitas'] = $details
            ->whereNotIn('tipAfeIgv', [10, 20, 30, 40])
            ->sum('mtoValorVenta');

        $data['mtoIGV'] = $details
            ->whereIn('tipAfeIgv', [10, 20, 30, 40])
            ->sum('igv');
        $data['mtoIGVGratuitas'] = $details
            ->whereNotIn('tipAfeIgv', [10, 20, 30, 40])
            ->sum('igv');
         //impuesto a la bolsa plastica
        $data['icbper']=$details->sum('icbper');
        $data['totalImpuestos'] = $data['mtoIGV']+ $data['icbper'];
        $valorVentaTotal = $details
            ->whereIn('tipAfeIgv', [10, 20, 30, 40])
            ->sum('mtoValorVenta');

        // Greenter solo necesita esta clave
        $data['valorVenta'] = $valorVentaTotal;

        $data['subTotal'] = $data['valorVenta'] + $data['mtoIGV'];
        $data['mtoImpVenta'] = floor($data['subTotal'] * 10) / 10;
        $data['redondeo'] = $data['mtoImpVenta'] - $data['subTotal'];
    } 
    public function setLegends(&$data){
        $formater = new NumeroALetras();

        $data['legends'] = [
            [
                'code'=> '1000',
                'value' => $formater->toInvoice($data['mtoImpVenta'], 2, 'SOLES')
            ]
        ];



    }

}