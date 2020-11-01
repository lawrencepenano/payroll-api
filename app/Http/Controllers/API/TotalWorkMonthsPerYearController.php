<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\TotalWorkMonthsPerYear;
use App\Models\TotalWorkMonthsPerYearAuditTrail;
use Response;
use Validator;
use Carbon\Carbon;
use App\Http\Resources\TotalWorkMonthsPerYearResourceCollection as TotalWorkMonthsPerYearResource;

class TotalWorkMonthsPerYearController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->query('q');

        /* To get the company of the current user*/
        $id = $request->user()->id;
        $user  = User::find($id);
        $company_id = $user->company->id;

        /* Check if do have the total work months per year setup*/
        $total_work_months_per_year = TotalWorkMonthsPerYear::where('company_id',$company_id)->first();

        /* If do not have the setup yet. generate the default Totak Work Months Per Year Setup */
        if(!$total_work_months_per_year){
            $defaults = file_get_contents(base_path()."\config\default.json");
            $total_work_months_per_year_default = json_decode($defaults)->total_work_months_per_year;
            foreach($total_work_months_per_year_default as $code => $value){
                  /* Store Record */
                $new_total_work_months_per_year_setup = new TotalWorkMonthsPerYear;
                $new_total_work_months_per_year_setup->code = $value->code;
                $new_total_work_months_per_year_setup->company_id = $company_id;
                $new_total_work_months_per_year_setup->description = $value->description;
                $new_total_work_months_per_year_setup->remarks = $value->remarks;
                $new_total_work_months_per_year_setup->save();

                /* Store Record Audit Trail*/
                $total_work_months_per_year_audit_trail = new TotalWorkMonthsPerYearAuditTrail;
                $total_work_months_per_year_audit_trail->total_work_months_per_year_id = $new_total_work_months_per_year_setup->id;
                $total_work_months_per_year_audit_trail->code = $new_total_work_months_per_year_setup->code;
                $total_work_months_per_year_audit_trail->description = $new_total_work_months_per_year_setup->description;
                $total_work_months_per_year_audit_trail->remarks = $new_total_work_months_per_year_setup->remarks;
                $total_work_months_per_year_audit_trail->updated_by = $user->id;
                $total_work_months_per_year_audit_trail->action = 'create';
                $total_work_months_per_year_audit_trail->date_and_time = Carbon::now();

                /* disable time stamps */
                $total_work_months_per_year_audit_trail->timestamps = false;
                $total_work_months_per_year_audit_trail->save();
            }
        }

        $total_work_months_per_year = TotalWorkMonthsPerYear::
        when(!empty($search), function ($q) use ($search) {
            return $q->orWhere('code', 'LIKE', '%' . $search . '%')
                     ->orWhere('description', 'LIKE', '%' . $search . '%');
        })
        /* To get the Company of Current User */
        ->where('company_id',$company_id)
         /* Sorting */
         ->when($request->query('sortField') &&  $request->query('sortOrder'), function ($q) use ($request) {
            return $q->orderBy($request->query('sortField'), $request->query('sortOrder'));
        })
        /* Pagination */
        ->paginate($request->query('sizePerPage'));

        return (new TotalWorkMonthsPerYearResource($total_work_months_per_year))->response()->setStatusCode(200);  
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        /* To Get Reuest Details */
        $details = $request->post();

        /* Validattion of field */
        $validator = Validator::make($request->all(), [
            'code' => 'required',  
            'description' => 'required',
            'remarks' => 'required',
        ]);

        /* To get the company of the current user*/
        $id = $request->user()->id;
        $user  = User::find($id);
        $company_id = $user->company->id;

         /* Validate duplicate code */
         if (TotalWorkMonthsPerYear::where('code', $details['code'])->where('company_id',$company_id)->first()) {
            return Response::json(['status' => 'error', 'data' => ['Code is already used.']], 400);
        }

        /* Store Record */
        $total_work_months_per_year = new TotalWorkMonthsPerYear;
        $total_work_months_per_year->company_id = $company_id;
        $total_work_months_per_year->code = $details['code'];
        $total_work_months_per_year->description = $details['description'];
        $total_work_months_per_year->remarks = $details['remarks'];
        $total_work_months_per_year->save();

        /* Store Record Audit Trail*/
        $total_work_months_per_year_audit_trail = new TotalWorkMonthsPerYearAuditTrail;
        $total_work_months_per_year_audit_trail->total_work_months_per_year_id = $total_work_months_per_year->id;
        $total_work_months_per_year_audit_trail->code = $total_work_months_per_year->code;
        $total_work_months_per_year_audit_trail->description = $total_work_months_per_year->description;
        $total_work_months_per_year_audit_trail->remarks = $total_work_months_per_year->remarks;
        $total_work_months_per_year_audit_trail->updated_by = $user->id;
        $total_work_months_per_year_audit_trail->action = 'create';
        $total_work_months_per_year_audit_trail->date_and_time = Carbon::now();
        /* disable time stamps */
        $total_work_months_per_year_audit_trail->timestamps = false;
        $total_work_months_per_year_audit_trail->save();

        return Response::json(['status' => 'Success', 'data' => $total_work_months_per_year], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        /* To get the company of the current user*/
        $user_id = $request->user()->id;
        $user  = User::find($user_id);
        $company_id = $user->company;

         /* Get Record */
         $total_work_months_per_year  = TotalWorkMonthsPerYear::find($id);

         /* Check if Existing */
         if(!$total_work_months_per_year){
             return Response::json(['status' => 'fail', 'data' => ["Record is not existing"] ], 404);
         }

         /* Check if company is same */
         if(!$total_work_months_per_year->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Record belongs to another Company"] ], 403);
         }

         $total_work_months_per_year->audit;

         return Response::json(['status' => 'success', 'data' => $total_work_months_per_year], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /* Get Request Details */
        $details = $request->post();

        /* Get the company of the current user*/
        $user_id = $request->user()->id;
        $user  = User::find($user_id);
        $company_id = $user->company->id;

        /* Get Record */
        $total_work_months_per_year  = TotalWorkMonthsPerYear::find($id);

        /* Check for if Existing */
        if(!$total_work_months_per_year){
            return Response::json(['status' => 'fail', 'data' => ["Record is not existing"] ], 404);
        }
  
        /* Validate duplicate emails */
        $duplicate =  TotalWorkMonthsPerYear::where('code', $details['code'])->where('company_id',$company_id)->where('id','<>',$id)->first();
        if ($duplicate) {
            return Response::json(['status' => 'fail', 'data' => ["Code is already used"] ], 403);
        }

        /* Check if company is same */
        if(!$total_work_months_per_year->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Record belongs to another company"] ], 403);
        }

         /* Get Updater */
         $updated_by = $request->user()->id;

         /* Save Audit Trails */
         $total_work_months_per_year_audit_trail = new TotalWorkMonthsPerYearAuditTrail;
         $total_work_months_per_year_audit_trail->total_work_months_per_year_id = $total_work_months_per_year->id;
         $total_work_months_per_year_audit_trail->code = $total_work_months_per_year->code;
         $total_work_months_per_year_audit_trail->description = $total_work_months_per_year->description;
         $total_work_months_per_year_audit_trail->remarks = $total_work_months_per_year->remarks;
         $total_work_months_per_year_audit_trail->updated_by = $updated_by;
         $total_work_months_per_year_audit_trail->action = 'update';
         $total_work_months_per_year_audit_trail->date_and_time = Carbon::now();
         /* disable time stamps */
         $total_work_months_per_year_audit_trail->timestamps = false;
         $total_work_months_per_year_audit_trail->save();

        /* update cost center */
        $total_work_months_per_year->update($details);

        /* Get Audit Details */
        $total_work_months_per_year->audit;

        return Response::json(['status' => 'Success', 'data' => $total_work_months_per_year ], 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        /* Get the company of the current user*/
        $user_id = $request->user()->id;
        $user  = User::find($user_id);
        $company_id = $user->company->id;

         /* Get Record */
         $total_work_months_per_year  = TotalWorkMonthsPerYear::find($id);

         /* Check if Existing */
         if(!$total_work_months_per_year){
             return Response::json(['status' => 'fail', 'data' => ["Record is not existing"] ], 404);
         }

         /* Check if company is same */
         if(!$total_work_months_per_year->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Record belongs to another Company"] ], 403);
         }

        /* Get Updater */
        $updated_by = $request->user()->id;

        /* Save Audit Trails */
        $total_work_months_per_year_audit_trail = new TotalWorkMonthsPerYearAuditTrail;
        $total_work_months_per_year_audit_trail->total_work_months_per_year_id = $total_work_months_per_year->id;
        $total_work_months_per_year_audit_trail->code = $total_work_months_per_year->code;
        $total_work_months_per_year_audit_trail->description = $total_work_months_per_year->description;
        $total_work_months_per_year_audit_trail->remarks = $total_work_months_per_year->remarks;
        $total_work_months_per_year_audit_trail->updated_by = $updated_by;
        $total_work_months_per_year_audit_trail->action = 'delete';
        $total_work_months_per_year_audit_trail->date_and_time = Carbon::now();
        /* disable time stamps */
        $total_work_months_per_year_audit_trail->timestamps = false;
        $total_work_months_per_year_audit_trail->save();

        $total_work_months_per_year->delete();

        return Response::json(['status' => 'Success', 'data' => $total_work_months_per_year ], 200);
    }
}
