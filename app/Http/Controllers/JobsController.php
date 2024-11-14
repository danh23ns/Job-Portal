<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\jobType;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobNotificationEmail;
use App\Models\savedJob;
use App\Models\User;


class JobsController extends Controller
{
    public function index(Request $request)
    {
       $categories = Category::where('status', 1)->get();
       $jobTypes = jobType::where('status', 1)->get();

       $jobs = Job::where('status', 1);

       //search by using keyword
       if(!empty($request->keyword)){
           $jobs = $jobs->where(function($query) use($request) { 

                $query->orwhere('title', 'LIKE', '%'.request()->keyword.'%');
                $query->orwhere('keywords', 'LIKE', '%'.request()->keyword.'%');
                      
           });
       }

       //search by using location

         if(!empty($request->location)){
              $jobs = $jobs->where('location', 'LIKE', '%'.request()->location.'%');
         }

         //search by using category

            if(!empty($request->category)){
                $jobs = $jobs->where('category_id', request()->category);
            }

        //search by using job type
            $jobTypeArray = [];
            if(!empty($request->jobType)){
                //1,2,3
                $jobTypeArray = explode(',', request()->jobType);
                $jobs = $jobs->whereIn('job_type_id', $jobTypeArray);
            }

        //search by using experience

            if(!empty($request->experience)){
                $jobs = $jobs->where('experience', request()->experience);
            }

            $jobs = $jobs->with(['jobType', 'category']);
            if($request->sort == '0') {
                $jobs = $jobs->orderBy('created_at','ASC');
            } else {
                $jobs = $jobs->orderBy('created_at','DESC');
            }
     

       $jobs = $jobs->paginate(9);

        return view('front.jobs',[
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'jobs' => $jobs,
            'jobTypeArray' => $jobTypeArray
        ]);
    }

    // this method will show the job detail page
    public function detail($id)
    {
        $job = Job::where(['id' => $id, 'status' => 1])
            ->with(['jobType', 'category'])
            ->first();

        if ($job == null) {
            abort(404);
        }


        $count = 0;
       //check if the job is already saved
        if(Auth::user()){
            $count = savedJob::where([
                'user_id' => Auth::user()->id,
                'job_id' => $id
            ])->count();
        }

        // fetch applications for this job
        $applications = JobApplication::where('job_id', $id)
        ->with('user')
        ->get();
        

        return view('front.jobsDetail' ,[
            'count' => $count,
            'job' => $job,
            'applications' => $applications
        ]);
    }

    public function applyJob(Request $request)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'status' => 'false',
                'message' => 'You need to be logged in to apply for jobs'
            ]);
        }
    
        $id = $request->id;
    
        // Check if the job exists
        $job = Job::find($id);
    
        if (!$job) {
            $message = 'Job not found';
            session()->flash('error', $message);
            return response()->json([
                'status' => 'false',
                'message' => $message
            ]);
        }
    
        // Prevent user from applying to their own job
        $employer_id = $job->user_id;
    
        if ($employer_id == Auth::user()->id) {
            $message = 'You cannot apply for your own job';
            session()->flash('error', $message);
            return response()->json([
                'status' => 'false',
                'message' => $message
            ]);
        }

        //you cannot apply for the same job twice
        $jobApplicationCount = JobApplication::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id
        ])->count();

        if($jobApplicationCount>0){
            $message = 'You have already applied for this job';
            session()->flash('error', $message);
            return response()->json([
                'status' => 'false',
                'message' => $message
            ]);
        }
    
        // Save the job application
        $application = new JobApplication();
        $application->job_id = $id;
        $application->user_id = Auth::user()->id;
        $application->employer_id = $employer_id;
        $application->applied_at = now();
        $application->save();

        // Send email notification to the employer
        $employer = User::find($employer_id);
        // in the mail data, we will pass the employer, user and job
        $mailData = [
            'employer' => $employer,
            'user' => Auth::user(),
            'job' => $job

        ];

        Mail::to($employer->email)->send(new JobNotificationEmail($mailData));
    
        $message = 'You have applied for the job successfully';
        session()->flash('success', $message);
    
        return response()->json([
            'status' => 'true',
            'message' => $message
        ]);
    }
    
    public function saveJob(Request $request){
        $id = $request->id;

        $job = Job::find($id);

        if($job == null){
            session()->flash('erro', 'Job not found');
            return response()->json([
                'status' => 'false'
            ]);
        }

        //check if the job is already saved
        $count = savedJob::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id
        ])->count();
        
        //you canot save the same job twice
        if($count > 0){
            session()->flash('error', 'Job already saved');
            return response()->json([
                'status' => 'false'
            ]);
        }

        $savedJob = new savedJob();
        $savedJob->job_id = $id;
        $savedJob->user_id = Auth::user()->id;
        $savedJob->save();

        session()->flash('success', 'Job saved successfully');

        return response()->json([
            'status' => 'true'
        ]);
    }

}
