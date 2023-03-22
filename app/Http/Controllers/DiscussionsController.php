<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTopicRequest;
use App\Http\Requests\SendMessageRequest;
use App\Models\ProjectUser;
use App\Models\Discussion;
use App\Models\DiscussionMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscussionsController extends Controller
{
    public function store(CreateTopicRequest $request, $id): RedirectResponse
    {
        $user = ProjectUser::where('project_id', $id)->where('user_id', $request->user()->id)->first();

        if ($user->role !== 'admin') {
            return redirect()->route('project.show', $id);
        }

        $discussion = Discussion::create([
            'project_id' => $id,
            'user_id' => $request->user()->id,
            'name' => $request->name,
        ]);

        return redirect()->route('project.show', $id);
    }

    public function edit(Request $request, $id): View|RedirectResponse
    {
        $discussion = Discussion::find($id);
        $user = ProjectUser::where('project_id', $discussion->project_id)->where('user_id', $request->user()->id)->first();

        if ($user->role !== 'admin') {
            return redirect()->route('project.show', $id);
        }

        return view('discussion.edit', compact('discussion'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $discussion = Discussion::find($id);
        $user = ProjectUser::where('project_id', $discussion->project_id)->where('user_id', $request->user()->id)->first();

        if ($user->role !== 'admin') {
            return redirect()->route('project.show', $discussion->project_id);
        }

        $discussion->update($request->only(['name']));

        return redirect()->route('project.show', $discussion->project_id);
    }

    public function destroy(Request $request, $id): RedirectResponse
    {
        $discussion = Discussion::find($id);
        $user = ProjectUser::where('project_id', $discussion->project_id)->where('user_id', $request->user()->id)->first();

        if ($user->role !== 'admin') {
            return redirect()->route('project.show', $discussion->project_id);
        }

        $messages = DiscussionMessage::all()->where('discussion_id', $id);
        if ($messages) {
            foreach ($messages as $message) {
                $message->delete();
            }
        }
        $discussion->delete();

        return redirect()->route('project.show', $discussion->project_id);
    }

    public function message(SendMessageRequest $request, $id): RedirectResponse
    {
        $discussion = Discussion::find($id);

        $message = DiscussionMessage::create([
            'discussion_id' => $id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        return redirect()->route('project.show', [$discussion->project_id, '#discussion-' . $id]);
    }

    public function editMessage(Request $request, $id, $mid): View|RedirectResponse
    {
        $message = DiscussionMessage::where('id', $mid)->where('user_id', $request->user()->id)->first();
        $discussion = Discussion::find($id);

        if (!$message) {
            return redirect()->route('project.show', [$id, '#discussion-' . $discussion->id]);
        }

        $user = ProjectUser::where('project_id', $discussion->project_id)->where('user_id', $request->user()->id)->first();

        if ($user->role !== 'admin' || $message->user_id !== $request->user()->id) {
            return redirect()->route('project.show', [$discussion->project_id, '#discussion-' . $discussion->id]);
        }

        return view('discussion.message.edit', compact('discussion','message'));
    }

    public function updateMessage(Request $request, $id, $mid): RedirectResponse
    {
        $message = DiscussionMessage::where('id', $mid)->where('user_id', $request->user()->id)->first();
        $discussion = Discussion::find($id);

        if (!$message) {
            return redirect()->route('project.show', [$id, '#discussion-' . $discussion->id]);
        }

        $user = ProjectUser::where('project_id', $discussion->project_id)->where('user_id', $request->user()->id)->first();

        if ($user->role !== 'admin' || $message->user_id !== $request->user()->id) {
            return redirect()->route('project.show', [$discussion->project_id, '#discussion-' . $discussion->id]);
        }

        $message->update($request->only(['message']));

        return redirect()->route('project.show', [$discussion->project_id, '#discussion-' . $id]);
    }

    public function deleteMessage(Request $request, $id, $mid): RedirectResponse
    {
        $message = DiscussionMessage::where('id', $mid)->where('user_id', $request->user()->id)->first();

        if (!$message) {
            return redirect()->route('project.show', $id);
        }

        $discussion = Discussion::find($id);
        $user = ProjectUser::where('project_id', $discussion->project_id)->where('user_id', $request->user()->id)->first();

        if ($user->role !== 'admin' || $message->user_id !== $request->user()->id) {
            return redirect()->route('project.show', $discussion->project_id);
        }

        $message->delete();

        return redirect()->route('project.show', [$discussion->project_id, '#discussion-' . $id]);
    }
}
