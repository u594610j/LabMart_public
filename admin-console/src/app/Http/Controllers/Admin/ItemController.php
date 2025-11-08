<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends BaseAdminController
{
    /*
     * 商品一覧表示
     */
    public function index(Request $request)
    {
        $query = Item::with('category');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $items = $query->orderBy('id')->paginate(10);
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('admin.items.index', compact('items', 'categories'));
    }

    /*
     * 商品更新フォーム表示
     */
    public function edit(Item $item)
    {
        $categories = Category::all();

        return view('admin.items.edit', compact('item', 'categories'));
    }

    /*
     * 商品更新処理
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:items,name,'.$item->id,
            'price' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $item->update($validated);

        return redirect()->route('admin.items.index')->with('success', '商品情報を更新しました。');
    }

    /*
     * 商品新規登録フォーム表示
     */
    public function create()
    {
        $categories = Category::all();

        return view('admin.items.create', compact('categories'));
    }

    /*
     * 商品登録処理
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:items,name',
            'price' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock_quantity' => 'required|integer|min:1',
        ], [
            'name.required' => '商品名を入力してください。',
            'name.unique' => 'この商品名はすでに登録されています。',
            'price.required' => '価格を入力してください。',
            'price.integer' => '価格は数字で入力してください。',
            'price.min' => '価格は0円以上で入力してください。',
            'category_id.required' => 'カテゴリを選択してください。',
            'category_id.exists' => '選択されたカテゴリが存在しません。',
            'stock_quantity.required' => '在庫数を入力してください。',
            'stock_quantity.integer' => '在庫数は整数で入力してください。',
            'stock_quantity.min' => '在庫数は1以上で入力してください。',
        ]);

        Item::create($validated);

        return redirect()->route('admin.items.index')
            ->with('success', '商品を登録しました！');
    }
}
