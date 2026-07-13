<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\SunatService;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;

class DespatchController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'destinatario' => 'required|array',
            'envio' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array',
        ]);

        $data = $request->all();

        $company = Company::where('user_id', auth('api')->id())
            ->where('ruc', $data['company']['ruc'])
            ->firstOrFail();

        $sunat = new SunatService();
        $despatch = $sunat->getDespatch($data);

        $api = $sunat->getseeApi($company);
        $result = $api->send($despatch);

        $ticket = $result->getTicket();
        $result = $api->getStatus($ticket);

        $response['xml'] = $api->getLastXml();
        $response['hash'] = base64_encode(sha1($response['xml'], true));
        $response['sunatResponse'] = $sunat->sunatResponse($result);

        return response()->json($response, 200);
    }

    public function xml(Request $request)
    {
        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'destinatario' => 'required|array',
            'envio' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array',
        ]);

        $data = $request->all();

        $company = Company::where('user_id', auth('api')->id())
            ->where('ruc', $data['company']['ruc'])
            ->firstOrFail();

        $sunat = new SunatService();
        $despatch = $sunat->getDespatch($data);
        $api = $sunat->getseeApi($company);

        $response['xml'] = $api->getXmlSigned($despatch);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

        return response()->json($response, 200);
    }

    public function pdf(Request $request)
    {
        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'destinatario' => 'required|array',
            'envio' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array',
        ]);

        $data = $request->all();

        $company = Company::where('user_id', auth('api')->id())
            ->where('ruc', $data['company']['ruc'])
            ->firstOrFail();

        $sunat = new SunatService();
        $despatch = $sunat->getDespatch($data);
        $api = $sunat->getseeApi($company);

        $response['xml'] = $api->getXmlSigned($despatch);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

        return response()->json($response, 200);
    }
}
