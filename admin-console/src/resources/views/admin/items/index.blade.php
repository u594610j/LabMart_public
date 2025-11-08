@extends('admin.layout')

@section('title', '商品一覧')

@section('content')
    <h2>商品一覧</h2>

    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.items.create') }}">➕ 新規商品登録</a>
    </div>
    {{-- 成功メッセージ --}}
    @if (session('success'))
        <div id="success-message" style="color: green; margin-bottom: 20px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- フィルタフォーム --}}
    <form method="GET" action="{{ route('admin.items.index') }}" style="margin-bottom: 20px;">
        <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
            <div style="display: flex; flex-direction: column;">
                <label for="name" style="margin-bottom: 5px;">商品名</label>
                <input type="text" name="name" id="name" value="{{ request('name') }}" placeholder="例：りんごジュース" style="padding: 5px; width: 200px;">
            </div>
            <div style="display: flex; flex-direction: column;">
                <label for="category_id" style="margin-bottom: 5px;">カテゴリ</label>
                <select name="category_id" id="category_id" style="padding: 5px; width: 150px;">
                    <option value="">-- 全カテゴリ --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="padding: 6px 12px; background-color: #3490dc; color: white; border: none; border-radius: 4px;">
                    🔍 検索
                </button>
                <a href="{{ route('admin.items.index') }}" style="padding: 6px 12px; background-color: #6c757d; color: white; border-radius: 4px; text-decoration: none;">
                    リセット
                </a>
            </div>
        </div>
    </form>

    {{-- ページネーションリンク --}}
    <div style="margin-top: 20px;">
        {{ $items->appends(request()->query())->links() }}
    </div>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>商品名</th>
                <th>価格</th>
                <th>カテゴリ</th>
                <th>在庫数</th>
                <th>編集</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ number_format($item->price) }}円</td>
                <td>{{ optional($item->category)->name ?? '（未分類）' }}</td>
                <td>
                    @if ($item->stock_quantity == 0)
                        <span style="color: red; font-weight: bold;">SOLD OUT</span>
                    @else
                        {{ $item->stock_quantity }}
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.items.edit', $item->id) }}">✏️ 編集</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">商品が登録されていません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                setTimeout(function () {
                    successMessage.remove();
                }, 3000);
            }
        });
    </script>
@endsection