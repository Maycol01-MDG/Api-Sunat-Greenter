<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::where('user_id', auth('api')->user()->id)->get();
        return response()->json($companies, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'razon_social' => 'required|string|max:255',
            'ruc' => [
                'required',
                'string',
                'regex:/^(10|20)\d{9}$/',
                new \App\Rules\UniqueRucRule(),
            ],
            'direccion' => 'required|string|max:255',
            'logo' => 'nullable|image',
            'sol_user' => 'nullable|string|max:255',
            'sol_pass' => 'nullable|string|max:255',
            'cert' => 'required|file|mimes:pem,txt',
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
            'production' => 'nullable|boolean',

        ]);


        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos');
        }

        //para que sesuva a nutro servidor el archivo .pem y se guarde en la carpeta storage/app/certs
        $data['cert_path'] = $request->file('cert')->store('certs');
        $data['user_id'] = JWTAuth::user()->id;

        $company = Company::create($data);

        return response()->json([
            'message' => 'Empresa creada exitosamente',
            'company' => $company
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($company)
    {
        //usar el ruc para buscar la empresa
        $company = Company::where('ruc', $company)
            ->where('user_id', auth('api')->user()->id)
            ->firstOrFail();

        return response()->json($company, 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $company)
    {
        //usar el ruc para buscar la empresa
        $company = Company::where('ruc', $company)
            ->where('user_id', auth('api')->user()->id)
            ->firstOrFail();
       

        $data = $request->validate([
            'razon_social' => 'nullable|string|max:255',
            'ruc' => [
                'nullable',
                'string',
                'regex:/^(10|20)\d{9}$/',
                new \App\Rules\UniqueRucRule($company->id),
            ],
            'direccion' => 'nullable|string|min:5|max:500',
            'logo' => 'nullable|image',
            'sol_user' => 'nullable|string|max:255',
            'sol_pass' => 'nullable|string|max:255',
            'cert' => 'nullable|file|mimes:pem,txt',
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
            'production' => 'nullable|boolean',

        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos');
        }

        if ($request->hasFile('cert')) {
            $data['cert_path'] = $request->file('cert')->store('certs');
        }

        $company->update($data);

        return response()->json([
            'message' => 'Empresa actualizada exitosamente',
            'company' => $company
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($company)
    {
        $company = Company::where('ruc', $company)
            ->where('user_id', auth('api')->user()->id)
            ->firstOrFail();

        $company->delete();

        return response()->json([
            'message' => 'Empresa eliminada exitosamente'
        ], 200);
    
    }
}
