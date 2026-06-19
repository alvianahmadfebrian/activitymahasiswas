<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AiGenerateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\DriveController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthController::class, 'registerPage'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::get('/drive', [DriveController::class, 'index'])->name('drive.index');
    Route::post('/drive/upload', [DriveController::class, 'upload'])->name('drive.upload');
    Route::post('/drive/folder', [DriveController::class, 'createFolder'])->name('drive.folder');
    Route::delete('/drive/{id}', [DriveController::class, 'destroy'])->name('drive.destroy');

    Route::get('/discussions', [DiscussionController::class, 'index'])->name('discussions.index');
    Route::post('/discussions', [DiscussionController::class, 'storeRoom'])->name('discussions.store');
    Route::post('/discussions/private', [DiscussionController::class, 'startPrivateChat'])->name('discussions.private');

    Route::post('/discussions/{id}/call/start', [DiscussionController::class, 'startCall'])->name('discussions.call.start');
    Route::get('/discussions/{id}/call/check', [DiscussionController::class, 'checkCall'])->name('discussions.call.check');

    Route::get('/discussions/{id}', [DiscussionController::class, 'show'])->name('discussions.show');
    Route::post('/discussions/{id}/messages', [DiscussionController::class, 'sendMessage'])->name('discussions.message');

    Route::post('/discussions/{id}/leave', [DiscussionController::class, 'leaveGroup'])
    ->name('discussions.leave');


    Route::get('discussions/{id}/poll', [DiscussionController::class, 'pollMessages'])->name('discussions.poll');

Route::post('/discussions/{id}/kick/{userId}', [DiscussionController::class, 'kickMember'])
    ->name('discussions.kick');

Route::delete('/discussions/{id}', [DiscussionController::class, 'deleteGroup'])
    ->name('discussions.delete');

    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');

    Route::get('/chatbot', [AiChatController::class, 'index'])->name('chatbot.index');
    Route::get('/chatbot/new', [AiChatController::class, 'newChat'])->name('chatbot.new');
    Route::get('/chatbot/download/{messageId}', [AiChatController::class, 'download'])->name('chatbot.download');
    Route::post('/chatbot/send', [AiChatController::class, 'send'])->name('chatbot.send');
    Route::post('/chatbot/ask', [AiChatController::class, 'ask'])->name('chatbot.ask');
    Route::delete('/chatbot/{sessionId}', [AiChatController::class, 'destroy'])->name('chatbot.destroy');
    Route::get('/chatbot/{sessionId}', [AiChatController::class, 'show'])->name('chatbot.show');

    Route::post('/ai/generate/pdf', [AiGenerateController::class, 'pdf'])->name('ai.generate.pdf');
    Route::post('/ai/generate/word', [AiGenerateController::class, 'word'])->name('ai.generate.word');
    Route::post('/ai/generate/image', [AiGenerateController::class, 'image'])->name('ai.generate.image');
});
