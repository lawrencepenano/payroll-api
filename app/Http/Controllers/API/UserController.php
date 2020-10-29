<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\UserName;
use App\Models\UserCompanyAssignment;
use App\Models\UserRoleAssignment;
use App\Models\UserModuleAssignment;
use App\Models\UserNameAssignment;
use App\Models\UserAccessStatusAssignment;
use Hash;
use Validator;
use Response;
use Log;
use DB;
use App\Http\Resources\UserResourceCollection as UserResource;

class UserController extends Controller
{
    function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
            /* Get Details if existings */
            $user = User::join('user_name_assignments as pvot', 'users.id', 'pvot.user_id')
                    ->join('user_names', 'pvot.user_name_id', 'user_names.id')
                    ->orWhere('users.email',$email)
                    ->orWhere('user_names.user_name',$email)
                    ->select('users.*')
                    ->first();
                    
            /* Check if exists and same password */
            if (!$user || !Hash::check($password, $user->password)) {
                return Response::json(['status' => 'fail', 'data' => ['These credentials do not match our records.'] ], 419);
            }

            /* Check if Inactive */
            $inactive = UserAccessStatusAssignment::where('user_id',$user->id)
                    ->where('status_id',2)
                    ->first();
                        
            if($inactive){
                return Response::json(['status' => 'fail', 'data' => ['You account is currently deactivated. Please contact the administrator.']], 419);
            }

            /* Generate Token */
            $token = $user->createToken('my-app-token')->plainTextToken;
            
            /* Get Assigned Company */
            $user->company;

            /* Get Assigned User Name */
            $user->assigned_user_name;

            /* Get Assigned Role */
            $user->assigned_role;

            /* Get Assigned Modules */
            $user->assigned_modules;

            /* Destructure Resposonse*/
            $response = [
                'user' => $user,
                'token' => $token
            ];

            return Response::json(["status"=>"Success","data"=>$response]);
    }

    function register(Request $request)
    {
        $details = $request->post();

        /* Validattion of field */
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'email' => 'required',  
            'phone' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return Response::json(['status' => 'error', 'data' => [$validator->errors()]], 400);
        }

        /* Validate duplicate emails */
        if (User::where('email', $details['email'])->first()) {
            return Response::json(['status' => 'error', 'data' => ['Email is already used.']], 400);
        }

        /* To encrypt the password */
        $details['password'] = Hash::make($details['password']);
        
        /* Create a user */
        $user = new User;
        $user->name = $details['last_name'] . ', ' . $details['first_name'];
        $user->email = $details['email'];
        $user->password = $details['password'];
        $user->save();

        /* Create a company */
        $company = new Company;
        /* to get default values */
        $defaults = file_get_contents(base_path()."\config\default.json");
        $company_defaults = json_decode($defaults)->company;
        $company->company_logo = $company_defaults->company_logo;
        $company->company_name = $details['company_name'];
        $company->nature_of_business = $company_defaults->nature_of_business;
        $company->address1 = $company_defaults->address1;
        $company->address2 = $company_defaults->address2;
        $company->rdo = $company_defaults->rdo;
        $company->zip_code = $company_defaults->zip_code;
        $company->email =  $details['email'];
        $company->phone = $details['phone'];
        $company->fax = $company_defaults->fax;
        $company->tin_no = $company_defaults->tin_no;
        $company->sss_no = $company_defaults->sss_no;
        $company->hdmf_no = $company_defaults->hdmf_no;
        $company->working_hours = $company_defaults->working_hours;
        $company->working_hours_schedule_type = $company_defaults->working_hours_schedule_type;
        $company->no_of_shifts = $company_defaults->no_of_shifts;
        $company->created_by = $user->id;
        $company->updated_by = $company_defaults->updated_by;
        $company->save();

        /* Assign company */
        $user_company = new UserCompanyAssignment;
        $user_company->user_id = $user->id;
        $user_company->company_id = $company->id;
        $user_company->save();

         /* Assign role */
         $user_role = new UserRoleAssignment;
         $user_role->user_id = $user->id;
         $user_role->role_id = 1; // 1 = super admin
         $user_role->save();

         /* Assign modules */
         $defaults = file_get_contents(base_path()."\config\default.json");
         $modules = json_decode($defaults)->super_admin;
         foreach($modules as $key => $module){
            $user_module = new UserModuleAssignment;
            $user_module->user_id = $user->id;
            $user_module->module_id = $module;
            $user_module->save();
         }

        /* Generate User Name */
        $available = false;
        while(!$available){
            $generated_user_name = $details['first_name'] . rand(1,10000) . '/Admin';
            $check_if_availability = UserName::where('user_name',$generated_user_name)->first();
            if(!isset($check_if_available)){
                $available = true;
            }
        }

        /* Save User Name */
        $user_name = new UserName;
        $user_name->user_name = $generated_user_name;
        $user_name->save();

        /* Assign User Name */
        $user_role = new UserNameAssignment;
        $user_role->user_id = $user->id;
        $user_role->user_name_id = $user_name->id; 
        $user_role->save();

        /* Assign Access Status */
        $user_access_status = new UserAccessStatusAssignment;
        $user_access_status->user_id = $user->id;
        $user_access_status->status_id = 1; 
        $user_access_status->save();
 
        /* Get Assigned Company */
        $user->company;

        /* Get Assigned User Name */
        $user->assigned_user_name;

        /* Get Assigned Role */
        $user->assigned_role;

        /* Get Assigned Modules */
        $user->assigned_modules;

        /* Generate Token */        
        $token = $user->createToken('my-app-token')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];
        return Response::json(['status' => 'Success', 'data' => $response ], 200);
    }


    function index(Request $request)
    {
        $search = $request->query('q');

        $users = User::
        when(!empty($search), function ($q) use ($search) {
            return $q->orWhere('users.name', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $search . '%')
                    ->orWhere('user_names.user_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('roles.name', 'LIKE', '%' . $search . '%');
        })
        ->join('user_name_assignments as pvot1', 'users.id', 'pvot1.user_id')
        ->join('user_names', 'pvot1.user_name_id', 'user_names.id')
        ->join('user_role_assignments as pvot2', 'users.id', 'pvot2.user_id')
        ->join('roles', 'pvot2.role_id', 'roles.id')
        ->join('user_access_status_assignments as pvot3', 'users.id', 'pvot3.user_id')
        ->join('access_statuses', 'pvot3.status_id', 'access_statuses.id')
        ->select('users.*', 'user_names.user_name', 'roles.name as role', 'access_statuses.status', 'access_statuses.id as status_id' , 'pvot3.status_id')
         /* Sorting */
         ->when($request->query('sortField') &&  $request->query('sortOrder'), function ($q) use ($request) {
            return $q->orderBy($request->query('sortField'), $request->query('sortOrder'));
        })
        /* Pagination */
        ->paginate($request->query('sizePerPage'));

        return (new UserResource($users))->response()->setStatusCode(200);  
    }

    function show($id)
    {
        /* Get User */
        $user  = User::find($id);

        if(!isset($user)){
            return Response::json(['status' => 'fail', 'data' => "User is not existing" ], 419);
        }

        /* Get Assigned Company */
        $user->company;

        /* Get Assigned User Name */
        $user->assigned_user_name;

        /* Get Assigned Role */
        $user->assigned_role;

        /* Get Assigned Modules */
        $user->assigned_modules;

        $response = $user;

        return Response::json(['status' => 'success', 'data' => $response], 200);
    }

    function auth(Request $request){

        $id = $request->user()->currentAccessToken()->tokenable_id;

        $user  = User::find($id);

        if(!isset($user)){
            return Response::json(['status' => 'fail', 'data' => "User is not existing" ], 419);
        }

        /* Get Assigned Company */
        $user->company;

        /* Get Assigned User Name */
        $user->assigned_user_name;

        /* Get Assigned Role */
        $user->assigned_role;

        /* Get Assigned Modules */
        $user->assigned_modules;

        $response = $user;

        return Response::json(['status' => 'success', 'data' => $response], 200);
    }

    function update(Request $request, $id)
    {
        $user = User::find($id);
        // $request->all();

        /* Check if account exist */
        if (!$user) {
            return response([
                'message' => ['Account is not existing.']
            ], 404);
        }

        /* Get and update roles ls if existings */
        $user_role = UserRoleAssignment::where('user_id',$id);
        if(!$user_role){
            $user_role = new UserRoleAssignment;
            $user_role->user_id = $id;
            $user_role->role_id = $request->role_id; 
            $user_role->save();
        }else{
            $user_role->update(['role_id'=>$request->role_id]);
        }

        /* Delete and insert new assigned modules*/
        $delete_user_modules = UserModuleAssignment::where('user_id',$id)->delete();
        $modules = json_decode($request->modules);
        foreach($modules as $key => $module){
           $user_module = new UserModuleAssignment;
           $user_module->user_id = $id;
           $user_module->module_id = $module;
           $user_module->save();
        }

        return Response::json(['status' => 'success', 'data' => 'Successfully updated the data'], 200);
    }

    function destroy($id)
    {
        /* Get Details if existings */
        $user = User::find($id);

        /* Check if account exist */
        if (!$user) {
            return response([
                'message' => ['Account is not existing.']
            ], 404);
        }
        
        /* Find Current Access Status */
        $user_access_status = UserAccessStatusAssignment::where('user_id',$user->id)->first();

        /* Deactive Account */
        if ($user_access_status->status_id == 1){
            $new_status = 2;
        }

        /* Activate Account */
        if ($user_access_status->status_id == 2){
            $new_status = 1;
        }

        $user_access_status->update(['status_id'=>$new_status]);

        return Response::json(['status' => 'success', 'data' => $user_access_status], 200);
    }


    function reset_password($id){
        $user = User::find($id);

        /* Check if account exist */
        if (!$user) {
            return response([
                'message' => ['Account is not existing.']
            ], 404);
        }

        /* "password" is the default password */
        $default_passowrd = Hash::make('password');
        
        /* To reset the password to default */
        $user->password = $default_passowrd;

        return Response::json(['status' => 'success', 'data' => 'You have successfully reset the password'], 200);
    }

}
