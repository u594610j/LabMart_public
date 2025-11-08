@extends('admin.layout')

@section('title', '商品編集')

@section('content')
    <h2>商品編集</h2>

    {{-- バリデーションエラー --}}
    @if ($errors->any())
        <div style="color: red; margin-bottom: 20px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.items.update', $item->id) }}">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 15px;">
            <label>商品名：</label><br>
            <input type="text" name="name" value="{{ old('name', $item->name) }}" required>
        </div>

        <div style="margin-bottom: 15px;">
            <label>価格：</label><br>
            <input type="number" name="price" value="{{ old('price', $item->price) }}" min="0" required> 円
        </div>

        <div style="margin-bottom: 15px;">
            <label>在庫数：</label><br>
            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $item->stock_quantity) }}" min="0" required> 個
        </div>

        <div style="margin-bottom: 15px;">
            <label>カテゴリ：</label><br>
            <select name="category_id" required>
                <option value="">-- 選択してください --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit">更新する</button>
            <a href="{{ route('admin.items.index') }}" style="margin-left: 20px;">← キャンセル</a>
        </div>
    </form>
@endsection