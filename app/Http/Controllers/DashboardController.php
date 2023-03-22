<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Discussion;
use App\Models\DiscussionMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $projects = $request->user()->projectUser;

        foreach ($projects as $project) {
            $projectUsers = ProjectUser::all()->where('project_id', $project->project_id);

            $users = [];
            foreach ($projectUsers as $projectUser) {
                $user = User::find($projectUser->user_id);
                $users[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $projectUser->role,
                ];
            }
            $project->users = $users;
            $project->meta = Project::find($project->project_id);

            $projectTopics = Discussion::all()->where('project_id', $project->project_id);
            if ($projectTopics) {
                $discussionsCount = 0;
                foreach ($projectTopics as $projectTopic) {
                    $messages = DiscussionMessage::all()->where('discussion_id', $projectTopic->id);
                    $discussionsCount += count($messages);
                }
                $project->discussionsCount = $discussionsCount;
            } else {
                $project->discussionsCount = 0;
            }
        }

        return view('dashboard', [
            'projects' => $projects,
        ]);
    }

    public function welcome(Request $request): View|RedirectResponse
    {
        if (!$request->user()) {
            return view('welcome');
        } else {
            return redirect()->route('dashboard');
        }
    }
}
