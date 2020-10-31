<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\Department;
use App\Models\DepartmentAuditTrail;
use Response;
use Validator;
use Carbon\Carbon;
use App\Http\Resources\DepartmentResourceCollection as DepartmentResource;


class DepartmentController extends Controller
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

        $department = Department::
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

        return (new DepartmentResource($department))->response()->setStatusCode(200);  
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
          if (Department::where('code', $details['code'])->where('company_id',$company_id)->first()) {
             return Response::json(['status' => 'error', 'data' => ['Code is already used.']], 400);
         }
 
         /* Create a Department */
         $department = new Department;
         $department->company_id = $company_id;
         $department->code = $details['code'];
         $department->description = $details['description'];
         $department->remarks = $details['remarks'];
         $department->save();
         
         return Response::json(['status' => 'Success', 'data' => $department], 200);
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
         $company_id = $user->company->id;
 
          /* Get Department */
          $department  = Department::find($id);
 
          /* Check if Existing */
          if(!$department){
              return Response::json(['status' => 'fail', 'data' => ["Department is not existing"] ], 404);
          }
 
          /* Check if company is same */
          if(!$department->company->id == $company_id){
             return Response::json(['status' => 'fail', 'data' => ["This Department belongs to another Company"] ], 403);
          }
 
          $department->audit;
          return Response::json(['status' => 'success', 'data' => $department], 200);
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
 
         /* Get Department */
         $department  = Department::find($id);
 
         /* Check for if Existing */
         if(!$department){
             return Response::json(['status' => 'fail', 'data' => ["Department is not existing"] ], 404);
         }
   
         /* Validate duplicate emails */
         $duplicate =  Department::where('code', $details['code'])->where('company_id',$company_id)->where('id','<>',$id)->first();
         if ($duplicate) {
             return Response::json(['status' => 'fail', 'data' => ["Code is already used"] ], 403);
         }
 
         /* Check if company is same */
         if(!$department->company->id == $company_id){
             return Response::json(['status' => 'fail', 'data' => ["This Department belongs to another company"] ], 403);
         }
 
          /* Get Updater */
          $updated_by = $request->user()->id;
 
          /* Save Audit Trails */
          $department_audit_trail = new DepartmentAuditTrail;
          $department_audit_trail->department_id = $department->id;
          $department_audit_trail->code = $department->code;
          $department_audit_trail->description = $department->description;
          $department_audit_trail->remarks = $department->remarks;
          $department_audit_trail->updated_by = $updated_by;
          $department_audit_trail->action = 'update';
          $department_audit_trail->date_and_time = Carbon::now();
          /* disable time stamps */
          $department_audit_trail->timestamps = false;
          $department_audit_trail->save();
 
         /* update department */
         $department->update($details);
 
         /* Get Audit Details */
         $department->audit;
 
         return Response::json(['status' => 'Success', 'data' => $department ], 200);
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
 
          /* Get Department */
          $department  = Department::find($id);
 
          /* Check if Existing */
          if(!$department){
              return Response::json(['status' => 'fail', 'data' => ["Department is not existing"] ], 404);
          }
 
          /* Check if company is same */
          if(!$department->company->id == $company_id){
             return Response::json(['status' => 'fail', 'data' => ["This Department belongs to another Company"] ], 403);
          }
 
         /* Get Updater */
         $updated_by = $request->user()->id;
 
         /* Save Audit Trails */
         $department_audit_trail = new DepartmentAuditTrail;
         $department_audit_trail->department_id = $department->id;
         $department_audit_trail->code = $department->code;
         $department_audit_trail->description = $department->description;
         $department_audit_trail->remarks = $department->remarks;
         $department_audit_trail->updated_by = $updated_by;
         $department_audit_trail->action = 'delete';
         $department_audit_trail->date_and_time = Carbon::now();
         /* disable time stamps */
         $department_audit_trail->timestamps = false;
         $department_audit_trail->save();
 
         $department->delete();
 
         return Response::json(['status' => 'Success', 'data' => $department ], 200);
    }
}
