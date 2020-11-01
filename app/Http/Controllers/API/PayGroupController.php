<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\PayGroup;
use App\Models\PayGroupAuditTrail;
use Response;
use Validator;
use Carbon\Carbon;
use App\Http\Resources\PayGroupResourceCollection as PayGroupResource;
class PayGroupController extends Controller
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

        $pay_group = PayGroup::
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

        return (new PayGroupResource($pay_group))->response()->setStatusCode(200);  
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
         if (PayGroup::where('code', $details['code'])->where('company_id',$company_id)->first()) {
            return Response::json(['status' => 'error', 'data' => ['Code is already used.']], 400);
        }

        /* Create a Record */
        $pay_group = new PayGroup;
        $pay_group->company_id = $company_id;
        $pay_group->code = $details['code'];
        $pay_group->description = $details['description'];
        $pay_group->remarks = $details['remarks'];
        $pay_group->save();

        /* Store Record Audit Trail*/
        $pay_group_audit_trail = new PayGroupAuditTrail;
        $pay_group_audit_trail->pay_group_id = $pay_group->id;
        $pay_group_audit_trail->code = $pay_group->code;
        $pay_group_audit_trail->description = $pay_group->description;
        $pay_group_audit_trail->remarks = $pay_group->remarks;
        $pay_group_audit_trail->updated_by = $user->id;
        $pay_group_audit_trail->action = 'create';
        $pay_group_audit_trail->date_and_time = Carbon::now();
        /* disable time stamps */
        $pay_group_audit_trail->timestamps = false;
        $pay_group_audit_trail->save();
        
        return Response::json(['status' => 'Success', 'data' => $pay_group], 200);
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
         $pay_group  = PayGroup::find($id);

         /* Check if Existing */
         if(!$pay_group){
             return Response::json(['status' => 'fail', 'data' => ["Record is not existing"] ], 404);
         }

         /* Check if company is same */
         if(!$pay_group->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Record belongs to another Company"] ], 403);
         }

         $pay_group->audit;

         return Response::json(['status' => 'success', 'data' => $pay_group], 200);
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
        $pay_group  = PayGroup::find($id);

        /* Check for if Existing */
        if(!$pay_group){
            return Response::json(['status' => 'fail', 'data' => ["Pay Group is not existing"] ], 404);
        }
  
        /* Validate duplicate emails */
        $duplicate =  PayGroup::where('code', $details['code'])->where('company_id',$company_id)->where('id','<>',$id)->first();
        if ($duplicate) {
            return Response::json(['status' => 'fail', 'data' => ["Code is already used"] ], 403);
        }

        /* Check if company is same */
        if(!$pay_group->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Pay Group belongs to another company"] ], 403);
        }

         /* Get Updater */
         $updated_by = $request->user()->id;

         /* Save Audit Trails */
         $pay_group_audit_trail = new PayGroupAuditTrail;
         $pay_group_audit_trail->pay_group_id = $pay_group->id;
         $pay_group_audit_trail->code = $pay_group->code;
         $pay_group_audit_trail->description = $pay_group->description;
         $pay_group_audit_trail->remarks = $pay_group->remarks;
         $pay_group_audit_trail->updated_by = $updated_by;
         $pay_group_audit_trail->action = 'update';
         $pay_group_audit_trail->date_and_time = Carbon::now();
         /* disable time stamps */
         $pay_group_audit_trail->timestamps = false;
         $pay_group_audit_trail->save();

        /* update record */
        $pay_group->update($details);

        /* Get Audit Details */
        $pay_group->audit;

        return Response::json(['status' => 'Success', 'data' => $pay_group ], 200);
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
         $pay_group  = PayGroup::find($id);

         /* Check if Existing */
         if(!$pay_group){
             return Response::json(['status' => 'fail', 'data' => ["Pay Group is not existing"] ], 404);
         }

         /* Check if company is same */
         if(!$pay_group->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Pay Group belongs to another Company"] ], 403);
         }

        /* Get Updater */
        $updated_by = $request->user()->id;

        /* Save Audit Trails */
        $pay_group_audit_trail = new PayGroupAuditTrail;
        $pay_group_audit_trail->pay_group_id = $pay_group->id;
        $pay_group_audit_trail->code = $pay_group->code;
        $pay_group_audit_trail->description = $pay_group->description;
        $pay_group_audit_trail->remarks = $pay_group->remarks;
        $pay_group_audit_trail->updated_by = $updated_by;
        $pay_group_audit_trail->action = 'delete';
        $pay_group_audit_trail->date_and_time = Carbon::now();
        /* disable time stamps */
        $pay_group_audit_trail->timestamps = false;
        $pay_group_audit_trail->save();

        $pay_group->delete();

        return Response::json(['status' => 'Success', 'data' => $pay_group ], 200);
    }
}
