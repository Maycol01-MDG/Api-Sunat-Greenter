<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Services\SunatService;
use Greenter\Xml\XmlUtils;

class InvoiceConroller extends Controller
{
    public function send(Request $request){
        $data = $request->all();
        
        $company = Company::where('user_id', auth('api')->id())
            ->where('ruc', $data['company']['ruc'])
            ->firstOrFail();

        $details = collect($data['details']);
        $mtoOperGravadas = $details -> sum('mtoValorVenta');
        return $mtoOperGravadas;
        
        

        $sunat = new SunatService();

        $see = $sunat->getSee($company);

        $invoice = $sunat->getInvoice( $data );

        $result = $see->send($invoice);

        $response['xml'] = $see->getFactory()->getLastXml();
        $response['hash'] = base64_encode(
            sha1($response['xml'], true)
        );
        $response['sunatResponse'] = $sunat->sunatResponse($result);
       

        return response()->json($response, 200);
    }
}
