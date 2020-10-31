<?php

namespace App\Http\Controllers\API;

use App\Models\Module;
use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;

class GlobalParameterController extends Controller
{
    function getModules(Request $request)
    {
        $modules = Module::select("id as value", "name as label")->get();

        return Response::json(['status' => 'success', 'data' => $modules], 200);
    }


    function getRoles(Request $request)
    {

        $roles = Role::select("id as value", "name as label")->get();

        return Response::json(['status' => 'success', 'data' => $roles], 200);
    }


}
