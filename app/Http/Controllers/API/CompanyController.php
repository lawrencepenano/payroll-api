<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Company;
use App\Models\CompanyAuditTrail;
use App\Models\CompanyStatusAssignment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Storage;
use Response;
use Validator;
use Carbon\Carbon;
use App\Http\Resources\CompanyResourceCollection as CompanyResource;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->query('q');

        $id = $request->user()->id;
        $user  = User::find($id);
        $company = $user->company;

        $company = Company::
        when(!empty($search), function ($q) use ($search) {
            return $q->where('company_name', 'LIKE', '%' . $search . '%');
        })
        /* To get the Company of Current User */
        ->where('id',$company->id)
         /* Sorting */
         ->when($request->query('sortField') &&  $request->query('sortOrder'), function ($q) use ($request) {
            return $q->orderBy($request->query('sortField'), $request->query('sortOrder'));
        })
        /* Pagination */
        ->paginate($request->query('sizePerPage'));

        return (new CompanyResource($company))->response()->setStatusCode(200);  
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
         /* Get Company */
         $company  = Company::find($id);

         if(!isset($company)){
             return Response::json(['status' => 'fail', 'data' => "Company is not existing" ], 419);
         }
        
         /* Get Company Logo URL*/
         $company->company_logo = asset(Storage::disk('public')->url($company->company_logo));
         
         /* Get Access Status */
         $company->access_status;

         $response = $company;
 
         return Response::json(['status' => 'success', 'data' => $company], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
       //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /* To select the current users cpmpany */
        $company = Company::find($id);

        /* get parameter values */
        $params = $request->post();

        // do image upload
        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $name = time() . $file->getClientOriginalName();
            $filePath = 'company_logo/' . $name;
            Storage::disk('public')->put($filePath, file_get_contents($file));
            $params['company_logo'] = $filePath;
        }else{
            $params['company_logo'] = $company->company_logo;
        }

         /* Validattion of field */
         $validator = Validator::make($request->all(), [
            'nature_of_business' => 'required',
            'address_1' => 'required',  
            'email' => 'required',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'error', 'data' => [$validator->errors()]], 400);
        }
        
        /* Get Updater */
        $updated_by = $request->user()->id;

        /* Save Audit Trails */
        $company_audit_trail = new CompanyAuditTrail;
        $company_audit_trail->company_id = $company->id;
        $company_audit_trail->company_logo = $company->company_logo;
        $company_audit_trail->company_name = $company->company_name;
        $company_audit_trail->nature_of_business = $company->nature_of_business;
        $company_audit_trail->address_1 = $company->address_1;
        $company_audit_trail->address_2 = $company->address_2;
        $company_audit_trail->rdo = $company->rdo;
        $company_audit_trail->zip_code = $company->zip_code;
        $company_audit_trail->email =  $company->email;
        $company_audit_trail->phone = $company->phone;
        $company_audit_trail->fax = $company->fax;
        $company_audit_trail->tin_no = $company->tin_no;
        $company_audit_trail->sss_no = $company->sss_no;
        $company_audit_trail->hdmf_no = $company->hdmf_no;
        $company_audit_trail->working_hours = $company->working_hours;
        $company_audit_trail->working_hours_schedule_type = $company->working_hours_schedule_type;
        $company_audit_trail->no_of_shifts = $company->no_of_shifts;
        $company_audit_trail->created_by = $company->created_by;
        $company_audit_trail->updated_by = $updated_by;
        $company_audit_trail->action = "update";
        $company_audit_trail->date_and_time = Carbon::now();
        /* removing time stamps before saving */
        $company_audit_trail->timestamps = false;
        $company_audit_trail->save();
 
        /* Set ID to $oarams in order to attach to its setup */
        $params['id'] = $id;
        /* set updated_by */
        $params['updated_by'] = $updated_by;
        /* update current record in the main table */

        $company->company_logo = $params['company_logo'];
        $company->company_name = $params['company_name'];
        $company->nature_of_business = $params['nature_of_business'];
        $company->address_1 = $params['address_1'];
        $company->address_2 = $params['address_2']?$params['address_2']:"";
        $company->zip_code = $params['zip_code']?$params['zip_code']:"";
        $company->rdo = $params['rdo']?$params['rdo']:"";
        $company->email = $params['email'];
        $company->phone = $params['phone'];
        $company->fax =  $params['fax']?$params['fax']:"";
        $company->tin_no = $params['tin_no']?$params['tin_no']:"";
        $company->sss_no = $params['sss_no']?$params['sss_no']:"";
        $company->hdmf_no = $params['hdmf_no']?$params['hdmf_no']:" ";
        $company->working_hours = $params['working_hours']?$params['working_hours']:"";
        $company->working_hours_schedule_type =  $params['working_hours_schedule_type']?$params['working_hours_schedule_type']:"";
        $company->no_of_shifts =  $params['no_of_shifts']?$params['no_of_shifts']:"";
        $company->updated_by = $params['updated_by']?$params['updated_by']:"";
        $company->save();

        return Response::json(['status' => 'success', 'data' => $company], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
         /* Get Details if existings */
         $comapny = Company::find($id);

         /* Check if account exist */
         if (!$comapny) {
             return response([
                 'message' => ['Company is not existing.']
             ], 404);
         }
         
         /* Find Current Access Status */
         $company_access_status = CompanyStatusAssignment::where('company_id',$comapny->id)->first();
 
         /* Deactive Account */
         if ($company_access_status->status_id == 1){
             $new_status = 2;
         }
 
         /* Activate Account */
         if ($company_access_status->status_id == 2){
             $new_status = 1;
         }
 
         $company_access_status->update(['status_id'=>$new_status]);
 
         return Response::json(['status' => 'success', 'data' => $company_access_status], 200);
    }
}
