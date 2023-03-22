<?php


use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DiscussionsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/project/create', [ProjectController::class, 'create'])->name('project.create');

    Route::post('/project/store', [ProjectController::class, 'store'])->name('project.store');

    Route::get('/project/{id}', [ProjectController::class, 'show'])->name('project.show');
    Route::get('/project/{id}/edit', [ProjectController::class, 'edit'])->name('project.edit');
    Route::patch('/project/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/project/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::post('/project/{id}/invite', [ProjectController::class, 'invite'])->name('project.invite');
    Route::post('/project/{id}/leave', [ProjectController::class, 'leave'])->name('project.leave');
    Route::post('/project/{id}/user/{uid}', [ProjectController::class, 'delete'])->name('project.delete');

    Route::post('/project/{id}/topic/store', [DiscussionsController::class, 'store'])->name('discussion.store');
    Route::get('/topic/{id}/edit', [DiscussionsController::class, 'edit'])->name('discussion.edit');
    Route::patch('/topic/{id}', [DiscussionsController::class, 'update'])->name('discussion.update');
    Route::delete('/topic/{id}', [DiscussionsController::class, 'destroy'])->name('discussion.destroy');

    Route::post('/topic/{id}/message', [DiscussionsController::class, 'message'])->name('topic.message');
    Route::get('/topic/{id}/message/{mid}/edit', [DiscussionsController::class, 'editMessage'])->name('topic.message.edit');
    Route::patch('/topic/{id}/message/{mid}', [DiscussionsController::class, 'updateMessage'])->name('topic.message.update');
    Route::delete('/topic/{id}/message/{mid}/delete', [DiscussionsController::class, 'deleteMessage'])->name('topic.message.delete');

    Route::post('/project/{id}/attachment', [AttachmentController::class, 'store'])->name('attachment.store');
    Route::delete('/project/{id}/attachment/{fid}', [AttachmentController::class, 'destroy'])->name('attachment.destroy');
});
