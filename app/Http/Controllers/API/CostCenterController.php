<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\CostCenter;
use App\Models\CostCenterAuditTrail;
use Response;
use Validator;
use Carbon\Carbon;
use App\Http\Resources\CostCenterResourceCollection as CostCenterResource;

class CostCenterController extends Controller
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

        $cost_center = CostCenter::
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

        return (new CostCenterResource($cost_center))->response()->setStatusCode(200);  
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
         if (CostCenter::where('code', $details['code'])->where('company_id',$company_id)->first()) {
            return Response::json(['status' => 'error', 'data' => ['Code is already used.']], 400);
        }

        /* Create a Cost Center */
        $cost_center = new CostCenter;
        $cost_center->company_id = $company_id;
        $cost_center->code = $details['code'];
        $cost_center->description = $details['description'];
        $cost_center->remarks = $details['remarks'];
        $cost_center->save();
        
        return Response::json(['status' => 'Success', 'data' => $cost_center], 200);
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

         /* Get Cost Center */
         $cost_center  = CostCenter::find($id);

         /* Check if Existing */
         if(!$cost_center){
             return Response::json(['status' => 'fail', 'data' => ["Cost Center is not existing"] ], 404);
         }

         /* Check if company is same */
         if(!$cost_center->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Cost Center belongs to another Company"] ], 403);
         }

         $cost_center->audit;

         return Response::json(['status' => 'success', 'data' => $cost_center], 200);
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

        /* Get Cost Center */
        $cost_center  = CostCenter::find($id);

        /* Check for if Existing */
        if(!$cost_center){
            return Response::json(['status' => 'fail', 'data' => ["Cost Center is not existing"] ], 404);
        }
  
        /* Validate duplicate emails */
        $duplicate =  CostCenter::where('code', $details['code'])->where('company_id',$company_id)->where('id','<>',$id)->first();
        if ($duplicate) {
            return Response::json(['status' => 'fail', 'data' => ["Code is already used"] ], 403);
        }

        /* Check if company is same */
        if(!$cost_center->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Cost Center belongs to another company"] ], 403);
        }

         /* Get Updater */
         $updated_by = $request->user()->id;

         /* Save Audit Trails */
         $cost_center_audit_trail = new CostCenterAuditTrail;
         $cost_center_audit_trail->cost_center_id = $cost_center->id;
         $cost_center_audit_trail->code = $cost_center->code;
         $cost_center_audit_trail->description = $cost_center->description;
         $cost_center_audit_trail->remarks = $cost_center->remarks;
         $cost_center_audit_trail->updated_by = $updated_by;
         $cost_center_audit_trail->action = 'update';
         $cost_center_audit_trail->date_and_time = Carbon::now();
         /* disable time stamps */
         $cost_center_audit_trail->timestamps = false;
         $cost_center_audit_trail->save();

        /* update cost center */
        $cost_center->update($details);

        /* Get Audit Details */
        $cost_center->audit;

        return Response::json(['status' => 'Success', 'data' => $cost_center ], 200);
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

         /* Get Cost Center */
         $cost_center  = CostCenter::find($id);

         /* Check if Existing */
         if(!$cost_center){
             return Response::json(['status' => 'fail', 'data' => ["Cost Center is not existing"] ], 404);
         }

         /* Check if company is same */
         if(!$cost_center->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Cost Center belongs to another Company"] ], 403);
         }

        /* Get Updater */
        $updated_by = $request->user()->id;

        /* Save Audit Trails */
        $cost_center_audit_trail = new CostCenterAuditTrail;
        $cost_center_audit_trail->cost_center_id = $cost_center->id;
        $cost_center_audit_trail->code = $cost_center->code;
        $cost_center_audit_trail->description = $cost_center->description;
        $cost_center_audit_trail->remarks = $cost_center->remarks;
        $cost_center_audit_trail->updated_by = $updated_by;
        $cost_center_audit_trail->action = 'delete';
        $cost_center_audit_trail->date_and_time = Carbon::now();
        /* disable time stamps */
        $cost_center_audit_trail->timestamps = false;
        $cost_center_audit_trail->save();

        $cost_center->delete();

        return Response::json(['status' => 'Success', 'data' => $cost_center ], 200);
    }
}
