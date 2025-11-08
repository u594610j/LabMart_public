@extends('admin.layout')

@section('title', '商品登録')

@section('content')
    <h2>新規商品登録</h2>
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
    <form method="POST" action="{{ route('admin.items.store') }}">
        @csrf

        <div>
            <label>商品名：</label><br>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>

        <div>
            <label>価格：</label><br>
            <input type="number" name="price" value="{{ old('price') }}" min="0" step="1" required> 円
        </div>

        <div>
            <label>在庫数：</label><br>
            <input type="number" name="stock_quantity" value="{{ old('stock_quantity') }}" min="1" step="1" required> 個
        </div>

        <div>
            <label>カテゴリ：</label><br>
            <select name="category_id" required>
                <option value="">-- 選択してください --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-top:20px;">
            <button type="submit">登録する</button>
        </div>
    </form>

    <div style="margin-top:20px;">
        <a href="{{ route('admin.items.index') }}">← 商品一覧に戻る</a>
    </div>
@endsection