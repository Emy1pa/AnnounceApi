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
     /**
     * @OA\Post(
     *     path="/api/announcement",
     *     tags={"announcements"},
     *     summary="Create a new announcement",
     *     operationId="store",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     description="Announcement title",
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     description="Announcement type",
     *                 ),
     *                 @OA\Property(
     *                     property="date",
     *                     type="string",
     *                     format="date",
     *                     description="Announcement date",
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Announcement description",
     *                 ),
     *                 @OA\Property(
     *                     property="location",
     *                     type="string",
     *                     description="Announcement location",
     *                 ),
     *                 @OA\Property(
     *                     property="required_skills",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     description="Announcement required skills",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement created successfully",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *     )
     * )
     */

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

 /**
     * @OA\Get(
     *     path="/api/announcements",
     *     tags={"announcements"},
     *     summary="Get all announcements",
     *     operationId="index",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     )
     * )
     */
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
                'status' => true,
                'announcements' => $announcements
            ], 200);
        } else {
            return response()->json([
                'status' => false,
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
/**
 * @OA\Get(
 *     path="/api/announcements/{id}",
 *     tags={"announcements"},
 *     summary="Get a specific announcement",
 *     operationId="show",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the announcement",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Announcement not found",
 *     )
 * )
 */

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
         /**
 * @OA\Get(
 *     path="/api/announcements/{id}/edit",
 *     tags={"announcements"},
 *     summary="Edit a specific announcement",
 *     operationId="edit",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the announcement",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Announcement not found",
 *     )
 * )
 */
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
        
        /**
 * @OA\Put(
 *     path="/api/announcements/{id}/edit",
 *     tags={"announcements"},
 *     summary="Update a specific announcement",
 *     operationId="update",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the announcement",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Announcement updated successfully",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Announcement not found",
 *     )
 * )
 */

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
        /**
            * @OA\Delete(
            *     path="/api/announcements/{id}/delete",
            *     tags={"announcements"},
            *     summary="Delete a specific announcement",
            *     operationId="destroy",
            *     @OA\Parameter(
            *         name="id",
            *         in="path",
            *         description="ID of the announcement",
            *         required=true,
            *         @OA\Schema(
            *             type="integer"
            *         )
            *     ),
            *     @OA\Response(
            *         response=200,
            *         description="Announcement deleted successfully",
            *     ),
            *     @OA\Response(
            *         response=404,
            *         description="Announcement not found",
            *     )
            * )
            */
           
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

/**
 * @OA\Post(
 *     path="/api/announcements/{id}/apply",
 *     tags={"announcements"},
 *     summary="Apply for a specific announcement",
 *     operationId="apply",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the announcement",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="message",
 *                     type="string",
 *                     description="Application message"
 *                 ),
 *                 @OA\Property(
 *                     property="required_skills",
 *                     type="array",
 *                     @OA\Items(type="string"),
 *                     description="Required skills"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Application submitted successfully",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Announcement not found",
 *     )
 * )
 */
/**
 * @OA\Put(
 *     path="/api/applications/{applicationId}/accept",
 *     tags={"applications"},
 *     summary="Accept a specific application",
 *     operationId="acceptApplication",
 *     @OA\Parameter(
 *         name="applicationId",
 *         in="path",
 *         description="ID of the application",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Application accepted successfully",
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden: Only organizers can accept applications",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Application not found",
 *     )
 * )
 */
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
        /**
 * @OA\Put(
 *     path="/api/applications/{applicationId}/reject",
 *     tags={"applications"},
 *     summary="Reject a specific application",
 *     operationId="rejectApplication",
 *     @OA\Parameter(
 *         name="applicationId",
 *         in="path",
 *         description="ID of the application",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Application rejected successfully",
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden: Only organizers can reject applications",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Application not found",
 *     )
 * )
 */

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
        /**
 * @OA\Get(
 *     path="/api/user/applications",
 *     tags={"applications"},
 *     summary="Get applications for the authenticated user",
 *     operationId="userApplications",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden: Only volunteers can access their applications",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No applications found for the authenticated user",
 *     )
 * )
 */
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
