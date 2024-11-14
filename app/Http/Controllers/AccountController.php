<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordEmail;
use App\Models\Category;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Redirect;

use Intervention\Image\ImageManager;

use Intervention\Image\Drivers\Gd\Driver;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\SavedJob;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Mail;

class AccountController extends Controller


{
    //this method will show user register page
    public function registration() {
        return view('front.account.registration');
    }

    // This method will save a user
    public function processRegistration(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required',
        ]);

        if ($validator->passes()) {

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success','You have registerd successfully.');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // This method will show user login page
    public function login() {
        return view('front.account.login');
    }

    // This method will authenticate user
    public function authenticate(Request $request) {

        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->passes()) {

            //Auth::attempt([...]): Phương thức attempt kiểm tra xem thông tin đăng nhập do người dùng cung cấp (email và password) có khớp với bất kỳ bản ghi nào trong bảng users của cơ sở dữ liệu hay không.
           if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
               return redirect()->route('account.profile');
           } else {
               return redirect()->route('account.login')
               ->with('error','Invalid email or password')
               ->withInput($request->only('email'));

           }
        
        } else {
           return redirect()->route('account.login')
           ->withErrors($validator) //giúp hiển thị các thông báo lỗi để người dùng biết và sửa lại dữ liệu đầu vào.
           ->withInput($request->only('email'));
           //withInput($request->only('email')): Chuyển dữ liệu của trường 'email' từ request hiện tại đến view mà người dùng sẽ được chuyển hướng tới. Điều này giúp giữ lại email mà người dùng đã nhập vào form đăng nhập, để nếu có lỗi, email đó sẽ vẫn hiển thị, không cần nhập lại.


        }
    }

    public function profile() {

        $id=Auth::user()->id; //Phương thức user() trả về người dùng hiện đang đăng nhập vào hệ thống.
        $user = User::where('id',$id)->first();
    
        return view('front.account.profile',[
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request) {
        $id = Auth::user()->id;

        $validator = Validator::make($request->all(),[
            'name' => 'required|min:3|max:20',
            'email' => 'required|email|unique:users,email'
        ]);

        if ($validator->passes()) {
            $user = User::find(Auth::user()->id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->designation = $request->designation;
            $user->save();
     
            session()->flash('success','Profile updated successfully.');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function logout() {
        Auth::logout(); //Phương thức logout() đăng xuất người dùng hiện đang đăng nhập, hủy phiên đăng nhập của họ, và xóa tất cả các thông tin xác thực khỏi session.
        return redirect()->route('account.login');
    }

    public function updateProfilePic(Request $request) {
        //dd($request->all());

        $id = Auth::user()->id;

        $validator = Validator::make($request->all(),[
            'image' => 'required|image'
        ]);

        if ($validator->passes()) {

            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = $id.'-'.time().'.'.$ext;
            $image->move(public_path('/profile_pic/'), $imageName);

            $sourcePath = public_path('/profile_pic/'.$imageName);
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($sourcePath);

            // crop the best fitting 5:3 (600x360) ratio and resize to 600x360 pixel
            $image->cover(150, 150);
            $image->toPng()->save(public_path('/profile_pic/thumb/'.$imageName));
        
            // delete the old image
            // thay doi anh sau khi update
            File::delete(public_path('/profile_pic/thumb/'.Auth::user()->image));
            File::delete(public_path('/profile_pic/'.Auth::user()->image));

            User::where('id',$id)->update(['image' => $imageName]);

            session()->flash('success','Profile picture updated successfully.');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function createJob(){

       $categories = Category::orderBy('name','ASC')->where('status',1)->get();

       $jobTypes = JobType::orderBy('name','ASC')->where('status',1)->get();

        return view('front.account.job.create',[
            'categories' => $categories,
            'jobTypes' => $jobTypes
        ]);
    }

    public function saveJob(Request $request) {

        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:75',          

        ];

        $validator = Validator::make($request->all(),$rules);

        if ($validator->passes()) {

            $job = new Job();
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id  = $request->jobType;
            $job->user_id = Auth::user()->id;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsibility = $request->responsibility;
            $job->qualifications = $request->qualifications;
            $job->keywords = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->website;
            $job->save();

            session()->flash('success','Job added successfully.');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function myJobs() {
        $jobs = Job::where('user_id',Auth::user()->id)->with('jobType', 'applications')->orderBy('created_at', 'DESC')->paginate(10); // in 10 thong tin
    
       return view('front.account.job.my-jobs',[
              'jobs' => $jobs
       ]);
    }

    public function editJob(Request $request, $id) {
        
        $categories = Category::orderBy('name','ASC')->where('status',1)->get();
        $jobTypes = JobType::orderBy('name','ASC')->where('status',1)->get();

        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $id
        ])->first();

        if($job == null ){
            abort(404);
        }

        return view('front.account.job.edit',[
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'job' => $job
        ]);
    }

    public function updateJob(Request $request, $id) {

        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:75',          

        ];

        $validator = Validator::make($request->all(),$rules);

        if ($validator->passes()) {

            $job = Job::find($id);
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id  = $request->jobType;
            $job->user_id = Auth::user()->id;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsibility = $request->responsibility;
            $job->qualifications = $request->qualifications;
            $job->keywords = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->website;
            $job->save();

            session()->flash('success','Job updated successfully.');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function deleteJob(Request $request) {
        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $request->jobId
        ])->first();

        if($job == null) {
            session()->flash('error','Job not found.'); 
            return response()->json([
                'status' => true,
            ]);
        }

        Job::where('id',$request->jobId)->delete();
        session()->flash('success','Job deletes successfully.'); 
            return response()->json([
                'status' => true,
            ]);
    }

    public function myJobApplications(){
        $jobApplications = JobApplication::where('user_id',Auth::user()->id)
        ->with('job', 'job.jobType','job.category')
        ->orderBy('created_at', 'DESC')
        ->paginate(10);
        
        return view('front.account.job.my-job-applications',[
            'jobApplications' => $jobApplications

        ]);
    }

    public function removeJobs(Request $request){
        $jobApplication = JobApplication::where([
            'id' => $request->id,
            'user_id' => Auth::user()->id
        ])->first();

        if($jobApplication == null) {
            session()->flash('error','Job application not found.');
            return response()->json([
                'status' => false,
            ]);
        }

        JobApplication::find($request->id)->delete();

        session()->flash('success','Job application removed successfully.');
        return response()->json([
            'status' => true,
        ]);

    }

    public function savedJobs(){
        // $jobApplications = JobApplication::where('user_id',Auth::user()->id)
        // ->with('job', 'job.jobType','job.category')
        // ->paginate(10);

        $savedJobs = SavedJob::where([
            'user_id' => Auth::user()->id
        ])->with('job', 'job.jobType','job.category')
        ->orderBy('created_at', 'DESC')->paginate(10);
        
        return view('front.account.job.saved-jobs',[
            'savedJobs' => $savedJobs

        ]);
    }

    public function removeSavedJobs(Request $request){
        $savedJob = SavedJob::where([
            'id' => $request->id,
            'user_id' => Auth::user()->id
        ])->first();

        if($savedJob == null) {
            session()->flash('error','Job not found.');
            return response()->json([
                'status' => false,
            ]);
        }

        SavedJob::find($request->id)->delete();

        session()->flash('success','Job removed successfully.');
        return response()->json([
            'status' => true,
        ]);

    }

    public function updatePassword(Request $request){
        $validator = Validator::make($request->all(),[
            'old_password' => 'required',
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

        if (Hash::check($request->old_password, Auth::user()->password) == false){
            session()->flash('error','Your old password is incorrect.');
            return response()->json([
                'status' => true                
            ]);
        }


        $user = User::find(Auth::user()->id);
        $user->password = Hash::make($request->new_password);  
        $user->save();

        session()->flash('success','Password updated successfully.');
        return response()->json([
            'status' => true                
        ]);

    }

    public function forgotPassword() {
        return view('front.account.forgot-password');
    }

    public function processForgotPassword(Request $request) {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.forgotPassword')->withInput()->withErrors($validator);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->where('email',$request->email)->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now()
        ]);

        // Send Email here
        $user = User::where('email',$request->email)->first();
        $mailData =  [
            'token' => $token,
            'user' => $user,
            'subject' => 'You have requested to change your password.'
        ];

        Mail::to($request->email)->send(new ResetPasswordEmail($mailData));

        return redirect()->route('account.forgotPassword')->with('success','Reset password email has been sent to your inbox.');
        
    }

    public function resetPassword($tokenString) {
        $token = DB::table('password_reset_tokens')->where('token',$tokenString)->first();

        if ($token == null) {
            return redirect()->route('account.forgotPassword')->with('error','Invalid token.');
        }

        return view('front.account.reset-password',[
            'tokenString' => $tokenString
        ]);
    }

    public function processResetPassword(Request $request) {

        $token = DB::table('password_reset_tokens')->where('token',$request->token)->first();

        if ($token == null) {
            return redirect()->route('account.forgotPassword')->with('error','Invalid token.');
        }
        
        $validator = Validator::make($request->all(),[
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.resetPassword',$request->token)->withErrors($validator);
        }

        User::where('email',$token->email)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('account.login')->with('success','You have successfully changed your password.');

    }
}