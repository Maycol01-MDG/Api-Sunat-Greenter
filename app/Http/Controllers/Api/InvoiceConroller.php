<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Services\SunatService;

class InvoiceConroller extends Controller
{
    public function send(Request $request)
    {
        $data = $request->all();

        $company = Company::where('user_id', auth('api')->id())
            ->where('ruc', $data['company']['ruc'])
            ->firstOrFail();

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

        $data['mtoOperGratuitas']= $details
        ->whereNotIn('tipAfeIgv', 10,20,30,40)
            ->sum('mtoValorVenta');

        return $data;

        $sunat = new SunatService();

        $see = $sunat->getSee($company);

        $invoice = $sunat->getInvoice($data);

        $result = $see->send($invoice);

        $response['xml'] = $see->getFactory()->getLastXml();
        $response['hash'] = base64_encode(
            sha1($response['xml'], true)
        );
        $response['sunatResponse'] = $sunat->sunatResponse($result);


        return response()->json($response, 200);
    }
}
