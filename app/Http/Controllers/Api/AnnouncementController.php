<?php

namespace App\Http\Controllers\Api;

use App\Models\Application;
use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api'); 
    }
    public function store(Request $request){
        
        $user = auth()->user();
        if (!$user || $user->role !== 'organizer') {
        return response()->json([
            'status' => false,
            'message' => 'Only organizers can create announcements'
        ], 403); 
    }
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'required_skills' => 'required|array'
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);

        } else {
            // // Check if the input is a string
            // if (is_string($request->required_skills)) {
            //     // Explode the string into an array
            //     $requiredSkills = explode(',', $request->required_skills);
            // } else {
            //     // If it's already an array, use it directly
              $requiredSkills = $request->required_skills;
            // }

             $requiredSkillsString = implode(',', $requiredSkills);


            $announcement = new Announcement();
            $announcement->title = $request->title;
            $announcement->type = $request->type;
            $announcement->date = $request->date;
            $announcement->description = $request->description;
            $announcement->location = $request->location;
            $announcement->required_skills = $requiredSkillsString;
            $announcement->organizer_id = auth()->id();
            $announcement->save();

            if($announcement){
                return response()->json([
                    'status' => 200,
                    'message' => 'announcement created successfully',
                    'announcement' => $announcement

                ],200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'something went wrong'
                ],500);
            }
      }
 }

 public function index(Request $request){
    $user = auth()->user();

    
    if ($user && $user->role === 'volunteer') {
        $query = Announcement::query();

        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        
        if ($request->has('location')) {
            $query->where('location', $request->location);
        }

        
        $announcements = $query->get();

        if ($announcements->count() > 0) {
            return response()->json([
                'status' => 200,
                'announcements' => $announcements
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No announcements found'
            ], 404);
        }
    } else {
        return response()->json([
            'status' => 403,
            'message' => 'Only volunteers can access all announcements'
        ], 403);
    }
}


          public function show($id){
            $announcement =  Announcement::find($id);
             if ($announcement){
                 return response()->json([
                     'status' => 200,
                     'message' => $announcement
                 ],200);
             } else {
                 return response()->json([
                     'status' => 404,
                     'message' => 'no announcement found'
                 ],404);
             }
         }
         public function edit($id){
            $announcement =  Announcement::find($id);
            if ($announcement){
                return response()->json([
                    'status' => 200,
                    'message' => $announcement
                ],200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'no annoucement found'
                ],404);
            }
        }

        public function update(Request $request, int $id){

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'date' => 'required|date',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
                'required_skills' => 'required|'
            ]);
            
            if($validator->fails()){
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->messages()
                ], 422);
            } else {
                $announcement = Announcement::find($id);
                
                if($announcement){
                    $announcement->update([
                        'title' => $request->title,
                        'type' => $request->type,
                        'date' => $request->date,
                        'description' => $request->description,
                        'location' => $request->location,
                        'required_skills' => $request->required_skills,
                    ]);
                    
                    return response()->json([
                        'status' => 200,
                        'message' => 'Announcement updated successfully'
                    ],200);
                }
                else {
                    return response()->json([
                        'status' => 404,
                        'message' => 'No such announcement found'
                    ],404);
                }
            }
        }        
        public function destroy($id){
            $announcement = Announcement::find($id);
            if ($announcement){
                $announcement->delete();
                return response()->json([
                    'status' => 200,
                    'message' => 'announcement deleted successfully'
                ],404);
            }
            else {
                return response()->json([
                    'status' => 404,
                    'message' => 'no such announcement found'
                ],404);
            }
    
        }


        // APPLY FOR AN ANNOUNCEMENT

        public function apply(Request $request, $id){
            $validator = Validator::make($request->all(),[
                'message' => 'required|string|max:255',
                'required_skills' => 'required|array'
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->messages()
                ], 422);
            }
            // if announcement exists
            $announcement = Announcement::find($id);
            if (!$announcement) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Announcement not found'
                ], 404);
            }
            $application = new Application();
            $application->announcement_id = $id;
            $application->volunteer_id = auth()->id();
            $application->message = $request->message;
            $application->required_skills = $announcement->required_skills;
            $application->save();
            return response()->json([
                'status' => 200,
                'message' => 'Application submitted successfully'
            ], 200);
        }

        public function acceptApplication($applicationId){
            $user = auth()->user();
                if (!$user || $user->role !== 'organizer') {
                return response()->json([
                    'status' => false,
                    'message' => 'Only organizers can accept applications'
            ], 403); 
            }
            $application = Application::findOrFail($applicationId);
            $application->update(['status' => 'accepted']);

            return response()->json([
                 'status' => 200,
                 'message' => 'Application accepted successfully'
            ], 200);
        }

        public function rejectApplication($applicationId) {
            $user = auth()->user();
            if (!$user || $user->role !== 'organizer') {
                return response()->json([
                    'status' => false,
                    'message' => 'Only organizers can reject applications'
                ], 403); 
            }
            $application = Application::findOrFail($applicationId);

    
            $application->update(['status' => 'rejected']);

            return response()->json([
                'status' => 200,
                'message' => 'Application rejected successfully'
            ], 200);
        }
        public function userApplications()
    {
        $user = auth()->user();
        
        if ($user && $user->role === 'volunteer') {
            $applications = Application::where('volunteer_id', $user->id)->get();
            
            if ($applications->count() > 0) {
                return response()->json([
                    'status' => 200,
                    'applications' => $applications
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'No applications found for the authenticated user'
                ], 404);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Only volunteers can access their applications'
            ], 403);
        }
    }

}
