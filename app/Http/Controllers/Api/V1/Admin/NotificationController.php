<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PaginationRequest;
use App\Http\Resources\V1\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(PaginationRequest $request)
    {
        return NotificationResource::collection($request->user()->notifications()->when($request->boolean('unread'), fn ($q) => $q->whereNull('read_at'))->orderByDesc('id')->paginate($request->integer('per_page', 12))->withQueryString());
    }

    public function update(Request $request, string $notification)
    {
        $notice = $request->user()->notifications()->findOrFail($notification);
        $notice->markAsRead();

        return new NotificationResource($notice);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->noContent();
    }
}
