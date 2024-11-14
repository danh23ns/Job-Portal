<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index()
    {
        $applications = JobApplication::orderBy('created_at', 'DESC')
        ->with('job', 'user', 'employer')
        ->paginate(10);

        return view('admin.job-applications.list', [
            'applications' => $applications
        ]);
    }

    public function destroy(Request $request)
    {
        $id = $request->id;
        $JobApplication = JobApplication::find($id);

        if($JobApplication == null){
            session()->flash('error', 'Job Application not found');
            return response()->json([             
                'status' => 'error'
            ]);
        }

        $JobApplication->delete();
        session()->flash('success', 'Job Application deleted successfully');
        return response()->json([
            'status' => 'success'
        ]);

    }
}
