<?php
declare(strict_type=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\TaskService;

class TaskController extends Controller
{
    public function getTasks(Request $request, TaskService $service)
    {
        // 1 HTTP リクエストの値を参照
        $isDone = $request->get('is_done');

        // 2 ビジネスロジックの実行
        $tasks = $service->findTasks($isDone);

        // 3 レスポンスを返す
        return response($tasks);
    }
}
