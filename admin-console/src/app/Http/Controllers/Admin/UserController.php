<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends BaseAdminController
{
    /*
     * ユーザ一覧表示
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }
        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }

        $users = $query->orderBy('id')->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    /*
     * ユーザ更新フォーム表示
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    /*
     * ユーザ更新処理
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'required|string|max:10',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'ユーザー情報を更新しました。');
    }

    /*
     * ユーザ新規登録フォーム表示
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /*
     * ユーザ登録処理
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'grade' => 'required|string|max:50',
            'card_id' => 'required|string|max:16|unique:users,card_id',
            'total_amount' => 'nullable|integer|min:0',
        ], [
            'name.required' => '氏名を入力してください。',
            'name.unique' => 'この氏名はすでに登録されています。',
            'grade.required' => '学年を選択してください。',
            'card_id.required' => 'NFCカードIDを読み取ってください。',
            'card_id.unique' => 'このカードIDはすでに登録済みです。',
        ]);

        // 空なら0にする
        $validated['total_amount'] = $validated['total_amount'] ?? 0;

        \App\Models\User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'ユーザーを登録しました。');
    }

    /*
     * ユーザ購入履歴表示
     */
    public function showOrders(User $user)
    {
        $orders = $user->orders()->with('orderDetails')->orderBy('ordered_at', 'desc')->get();

        return view('admin.users.orders', compact('user', 'orders'));
    }
}
