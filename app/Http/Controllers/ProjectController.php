<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProjectRequest;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Discussion;
use App\Models\DiscussionMessage;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function create(Request $request): View
    {
        return view('project.create', [
            'user' => $request->user(),
        ]);
    }

    public function store(CreateProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->only(["name", "description"]));
        $user = ProjectUser::create([
            'user_id' => $request->user()->id,
            'project_id' => $project->id,
            'role' => 'admin'
        ]);

        return redirect()->route('dashboard');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $project = Project::find($id);

        $project->update($request->only(["name", "description"]));

        return redirect()->route('project.show', $id);
    }

    public function destroy(Request $request, $id): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $project = Project::find($id);
        $projectUser = ProjectUser::where('user_id', $user->id)->where('project_id', $id)->first();

        if ($projectUser->role != 'admin') {
            return response()->json(
                ['error' => 'Your role not admin']
            );
        }

        $projectDiscussions = Discussion::all()->where('project_id', $id);
        foreach ($projectDiscussions as $projectDiscussion) {
            $discussionMessages = DiscussionMessage::all()->where('discussion_id', $projectDiscussion->id);
            foreach ($discussionMessages as $discussionMessage) {
                $discussionMessage->delete();
            }
            $projectDiscussion->delete();
        }

        $projectUsers = ProjectUser::all()->where('project_id', $id);
        foreach ($projectUsers as $projectUser) {
            $projectUser->delete();
        }

        $project->delete();

        return redirect()->route('dashboard');
    }

    public function edit(Request $request, $id): View
    {
        $project = Project::find($id);

        return view('project.edit', compact('project'));
    }

    public function show(Request $request, $id): View|RedirectResponse
    {
        $user = ProjectUser::where('project_id', $id)->where('user_id', $request->user()->id)->first();

        if (!$user) {
            return redirect()->route('dashboard');
        }

        $project = Project::find($id);
        $projectUsers = ProjectUser::all()->where('project_id', $id);

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

        $projectTopics = Discussion::all()->where('project_id', $id);
        $discussions = [];
        foreach ($projectTopics as $discussion) {
            $discussionMessages = DiscussionMessage::all()->where('discussion_id', $discussion->id);
            $messages = [];
            foreach ($discussionMessages as $message) {
                $messageUser = User::find($message->user_id);
                $messages[] = [
                    'id' => $message->id,
                    'message' => $message->message,
                    'user' => [
                        'id' => $messageUser->id,
                        'name' => $messageUser->name,
                        'email' => $messageUser->email,
                    ],
                ];
            }
            $discussions[] = [
                'id' => $discussion->id,
                'title' => $discussion->title,
                'messages' => $messages,
            ];
        }
        $project->discussions = $discussions;

        $projectAttachments = Attachment::all()->where('project_id', $id);
        $attachments = [];
        foreach ($projectAttachments as $item) {
            $attachments[] = [
                'id' => $item->id,
                'file_name' => $item->file_name,
                'file_path' => Storage::url($item->file_path),
            ];
        }
        $project->attachments = $attachments;
        $authUser = ProjectUser::where('project_id', $id)->where('user_id', $request->user()->id)->first();

        return view('project.show', compact('project', 'authUser'));
    }

    public function invite(Request $request, $id): RedirectResponse
    {
        $project = Project::find($id);
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $projectUser = ProjectUser::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'role' => $request->admin ? 'admin' : 'user'
            ]);
        }

        return redirect()->route('project.show', $id);
    }

    public function leave(Request $request, $id): RedirectResponse
    {
        $project = Project::find($id);
        $user = $request->user_id;

        $projectUser = ProjectUser::where('user_id', $user->id)->where('project_id', $project->id)->first();
        $projectUser->delete();

        return redirect()->route('dashboard');
    }

    public function delete(Request $request, $id, $uid): JsonResponse|RedirectResponse
    {
        $user = ProjectUser::where('user_id', $request->user()->id)->first();

        if ($user->role != 'admin') {
            return response()->json(
                ['error' => 'Your role not admin'],
                403
            );
        }

        if ($request->user()->id == $uid) {
            return response()->json(
                ['error' => 'You can not delete yourself'],
                403
            );
        }

        $projectUser = ProjectUser::where('user_id', $uid)->where('project_id', $id)->first();
        $projectUser->delete();

        return redirect()->route('project.show', $id);
    }
}
