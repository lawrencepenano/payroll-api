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
use Hash;
use Validator;
use Response;
use Log;
use DB;

class UserController extends Controller
{
    function login(Request $request)
    {
            $user = User::join('user_name_assignments as pvot', 'users.id', 'pvot.user_id')
                        ->join('user_names', 'pvot.user_name_id', 'user_names.id')
                        ->orWhere('users.email',$request->user_name)
                        ->orWhere('user_names.user_name',$request->user_name)
                        ->select('users.*', 'user_names.user_name')
                        ->first();


            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'message' => ['These credentials do not match our records.']
                ], 404);
            }
        
            $token = $user->createToken('my-app-token')->plainTextToken;
        
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
            return Response::json(['status' => 'fail', 'data' => [$validator->errors()]], 400);
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

        
        $token = $user->createToken('my-app-token')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return Response::json(["status"=>"Success","data"=>$response]);
    }


    function index(Request $request)
    {
        $q = $request->query('q');

        $users = User::when(!empty($search), function ($q) use ($search) {
            return $q->where('name', 'LIKE', '%' . $search . '%');
        })
         /* Sorting */
         ->when($request->query('sortField') &&  $request->query('sortOrder'), function ($q) use ($request) {
            return $q->orderBy($request->query('sortField'), $request->query('sortOrder'));
        })
        /* Pagination */
        ->paginate($request->query('sizePerPage'));

        return (new UserResource($users))
        ->response()->setStatusCode(200);  

    }

}
