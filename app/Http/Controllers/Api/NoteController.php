<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Services\SunatService;
use App\Traits\SunatTraits;
use Greenter\Report\XmlUtils;

class NoteController extends Controller
{
    use SunatTraits;

    public function send(Request $request)
    {

        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'client' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array',

        ]);

        $data = $request->all();

        $company = Company::where('user_id', auth('api')->id())
            ->where('ruc', $data['company']['ruc'])
            ->firstOrFail();

        $this->setTotales($data);
        $this->setLegends($data);


        $sunat = new SunatService();
        $see = $sunat->getSee($company);
        $note = $sunat->getNote($data);


        $result = $see->send($note);
        $response['xml'] = $see->getFactory()->getLastXml();
        $response['hash'] = base64_encode(
            sha1($response['xml'], true)
        );
        $response['sunatResponse'] = $sunat->sunatResponse($result);

        return response()->json($response, 200);
    }
    public function xml(Request $request)
    {

        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'client' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array',

        ]);

        $data = $request->all();

        $company = Company::where('user_id', auth('api')->id())
            ->where('ruc', $data['company']['ruc'])
            ->firstOrFail();

        $this->setTotales($data);
        $this->setLegends($data);


        $sunat = new SunatService();
        $see = $sunat->getSee($company);
        $note = $sunat->getNote($data);


        $response['xml'] = $see->getXmlSigned($note);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

        return response()->json($response, 200);
    }

      public function pdf(Request $request)
    {

        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'client' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array',

        ]);
        $data = $request->all();
        $company = Company::where('user_id', auth('api')->id())
            ->where('ruc', $data['company']['ruc'])
            ->firstOrFail();

        $this->setTotales($data);
        $this->setLegends($data);

        $sunat = new SunatService();
        $note = $sunat->getInvoice($data);
       // $sunat->generatePdfReport($note); // para descarga el pdf
        return $sunat->getHtmlReport($note);
    }
    
}
