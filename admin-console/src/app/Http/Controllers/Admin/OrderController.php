<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends BaseAdminController
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderDetails']);

        // ユーザー名でフィルタ
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->user_name.'%');
            });
        }

        // 学年でフィルタ
        if ($request->filled('grade')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('grade', $request->grade);
            });
        }

        $orders = $query->orderByDesc('ordered_at')->paginate(5);

        return view('admin.orders.index', compact('orders'));
    }
}
