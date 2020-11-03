<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\WHSStandard;
use App\Models\WHSStandardAuditTrail;
use App\Models\TotalWorkDaysPerYear;
use App\Models\TotalWorkMonthsPerYear;
use Response;
use Validator;
use Carbon\Carbon;
use App\Http\Resources\WHSStandardResourceCollection as WHSStandardResource;

class WHSStandardController extends Controller
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

        $whs_standard = WHSStandard::
        /* To get the Company of Current User */
        where('company_id',$company_id)
         /* Sorting */
         ->when($request->query('sortField') &&  $request->query('sortOrder'), function ($q) use ($request) {
            return $q->orderBy($request->query('sortField'), $request->query('sortOrder'));
        })
        /* Pagination */
        ->paginate($request->query('sizePerPage'));

        $whs_standard->company;
        $whs_standard->total_working_days_per_year;


        return (new WHSStandardResource($whs_standard))->response()->setStatusCode(200);  
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

        /* Store Record */
        $whs_standard = new WHSStandard;
        $whs_standard->company_id = $company_id;
        $whs_standard->type = 1; // 1 = Standard
        $whs_standard->wd_per_year = $details['wd_per_year'];
        $whs_standard->wh_per_day = $details['wh_per_day'];
        $whs_standard->wm_per_year = $details['wm_per_year'];
        $whs_standard->wh_start = $details['wh_start'];
        $whs_standard->wh_end = $details['wh_end'];
        $whs_standard->wh_end = $details['wh_end'];
        $whs_standard->break_hours = $details['break_hours'];
        $whs_standard->rd_monday = $details['rd_monday'];
        $whs_standard->rd_tuesday = $details['rd_tuesday'];
        $whs_standard->rd_wednesday = $details['rd_wednesday'];
        $whs_standard->rd_thursday = $details['rd_thursday'];
        $whs_standard->rd_friday = $details['rd_friday'];
        $whs_standard->rd_saturday = $details['rd_saturday'];
        $whs_standard->rd_sunday = $details['rd_sunday'];
        $whs_standard->save();

        /* Store Record Audit Trail*/
        $whs_standard_audit_trail = new WHSStandardAuditTrail;
        $whs_standard_audit_trail->whs_standard_id = $whs_standard->id;
        $whs_standard_audit_trail->type = 1; // 1 = Standard
        $whs_standard_audit_trail->wd_per_year = $details['wd_per_year'];
        $whs_standard_audit_trail->wh_per_day = $details['wh_per_day'];
        $whs_standard_audit_trail->wm_per_year = $details['wm_per_year'];
        $whs_standard_audit_trail->wh_start = $details['wh_start'];
        $whs_standard_audit_trail->wh_end = $details['wh_end'];
        $whs_standard_audit_trail->wh_end = $details['wh_end'];
        $whs_standard_audit_trail->break_hours = $details['break_hours'];
        $whs_standard_audit_trail->rd_monday = $details['rd_monday'];
        $whs_standard_audit_trail->rd_tuesday = $details['rd_tuesday'];
        $whs_standard_audit_trail->rd_wednesday = $details['rd_wednesday'];
        $whs_standard_audit_trail->rd_thursday = $details['rd_thursday'];
        $whs_standard_audit_trail->rd_friday = $details['rd_friday'];
        $whs_standard_audit_trail->rd_saturday = $details['rd_saturday'];
        $whs_standard_audit_trail->rd_sunday = $details['rd_sunday'];
        $whs_standard_audit_trail->updated_by = $user->id;
        $whs_standard_audit_trail->action = 'create';
        $whs_standard_audit_trail->date_and_time = Carbon::now();
        /* disable time stamps */
        $whs_standard_audit_trail->timestamps = false;
        $whs_standard_audit_trail->save();

        $whs_standard->company;

        return Response::json(['status' => 'Success', 'data' => $whs_standard], 200);
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
          $whs_standard  = WHSStandard::find($id);
 
          /* Check if Existing */
          if(!$whs_standard){
              return Response::json(['status' => 'fail', 'data' => ["Record is not existing"] ], 404);
          }
 
          /* Check if company is same */
          if(!$whs_standard->company->id == $company_id){
             return Response::json(['status' => 'fail', 'data' => ["This Record belongs to another Company"] ], 403);
          }
 
          $whs_standard->audit;
 
          return Response::json(['status' => 'success', 'data' => $whs_standard], 200);
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
        $whs_standard  = WHSStandard::find($id);

        /* Check for if Existing */
        if(!$whs_standard){
            return Response::json(['status' => 'fail', 'data' => ["Record is not existing"] ], 404);
        }

        /* Check if company is same */
        if(!$whs_standard->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Record belongs to another company"] ], 403);
        }

        /* Get Updater */
        $updated_by = $request->user()->id;

        /* Store Record Audit Trail*/
        $whs_standard_audit_trail = new WHSStandardAuditTrail;
        $whs_standard_audit_trail->whs_standard_id = $whs_standard->id;
        $whs_standard_audit_trail->type = 1; // 1 = Standard
        $whs_standard_audit_trail->wd_per_year = $whs_standard->wd_per_year;
        $whs_standard_audit_trail->wh_per_day = $whs_standard->wh_per_day;
        $whs_standard_audit_trail->wm_per_year = $whs_standard->wm_per_year;
        $whs_standard_audit_trail->wh_start = $whs_standard->wh_start;
        $whs_standard_audit_trail->wh_end = $whs_standard->wh_end;
        $whs_standard_audit_trail->wh_end = $whs_standard->wh_end;
        $whs_standard_audit_trail->break_hours = $whs_standard->break_hours;
        $whs_standard_audit_trail->rd_monday = $whs_standard->rd_monday;
        $whs_standard_audit_trail->rd_tuesday = $whs_standard->rd_tuesday;
        $whs_standard_audit_trail->rd_wednesday = $whs_standard->rd_wednesday;
        $whs_standard_audit_trail->rd_thursday = $whs_standard->rd_thursday;
        $whs_standard_audit_trail->rd_friday = $whs_standard->rd_friday;
        $whs_standard_audit_trail->rd_saturday = $whs_standard->rd_saturday;
        $whs_standard_audit_trail->rd_sunday = $whs_standard->rd_sunday;
        $whs_standard_audit_trail->updated_by = $user->id;
        $whs_standard_audit_trail->action = 'update';
        $whs_standard_audit_trail->date_and_time = Carbon::now();
        /* disable time stamps */
        $whs_standard_audit_trail->timestamps = false;
        $whs_standard_audit_trail->save();

        /* Get Audit Details */
        $whs_standard->audit;

        /* update cost center */
        $whs_standard->update($details);

        return Response::json(['status' => 'Success', 'data' => $whs_standard ], 200);


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
         $whs_standard  = WHSStandard::find($id);

         /* Check if Existing */
         if(!$whs_standard){
             return Response::json(['status' => 'fail', 'data' => ["Cost Center is not existing"] ], 404);
         }

         /* Check if company is same */
         if(!$whs_standard->company->id == $company_id){
            return Response::json(['status' => 'fail', 'data' => ["This Cost Center belongs to another Company"] ], 403);
         }

        /* Get Updater */
        $updated_by = $request->user()->id;

        /* Store Record Audit Trail*/
        $whs_standard_audit_trail = new WHSStandardAuditTrail;
        $whs_standard_audit_trail->whs_standard_id = $whs_standard->id;
        $whs_standard_audit_trail->type = 1; // 1 = Standard
        $whs_standard_audit_trail->wd_per_year = $whs_standard->wd_per_year;
        $whs_standard_audit_trail->wh_per_day = $whs_standard->wh_per_day;
        $whs_standard_audit_trail->wm_per_year = $whs_standard->wm_per_year;
        $whs_standard_audit_trail->wh_start = $whs_standard->wh_start;
        $whs_standard_audit_trail->wh_end = $whs_standard->wh_end;
        $whs_standard_audit_trail->wh_end = $whs_standard->wh_end;
        $whs_standard_audit_trail->break_hours = $whs_standard->break_hours;
        $whs_standard_audit_trail->rd_monday = $whs_standard->rd_monday;
        $whs_standard_audit_trail->rd_tuesday = $whs_standard->rd_tuesday;
        $whs_standard_audit_trail->rd_wednesday = $whs_standard->rd_wednesday;
        $whs_standard_audit_trail->rd_thursday = $whs_standard->rd_thursday;
        $whs_standard_audit_trail->rd_friday = $whs_standard->rd_friday;
        $whs_standard_audit_trail->rd_saturday = $whs_standard->rd_saturday;
        $whs_standard_audit_trail->rd_sunday = $whs_standard->rd_sunday;
        $whs_standard_audit_trail->updated_by = $user->id;
        $whs_standard_audit_trail->action = 'delete';
        $whs_standard_audit_trail->date_and_time = Carbon::now();
        /* disable time stamps */
        $whs_standard_audit_trail->timestamps = false;
        $whs_standard_audit_trail->save();

        $whs_standard->delete();

        return Response::json(['status' => 'Success', 'data' => $whs_standard ], 200);
    }
}
