@extends('admin.layout')

@section('title', 'ユーザ一覧')

@section('content')
    <h2>ユーザ一覧</h2>

    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.users.create') }}">➕ 新規ユーザ登録</a>
    </div>

    {{-- 成功メッセージ --}}
    @if (session('success'))
        <div id="success-message" style="color: green; margin-bottom: 20px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- フィルタフォーム --}}
    <form method="GET" action="{{ route('admin.users.index') }}" style="margin-bottom: 20px;">
        <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
            <div style="display: flex; flex-direction: column;">
                <label for="name" style="margin-bottom: 5px;">氏名</label>
                <input type="text" name="name" id="name" value="{{ request('name') }}" placeholder="例：田中" style="padding: 5px; width: 200px;">
            </div>
            <div style="display: flex; flex-direction: column;">
                <label for="grade" style="margin-bottom: 5px;">学年</label>
                <select name="grade" id="grade" style="padding: 5px; width: 120px;">
                    <option value="">-- 全学年 --</option>
                    @foreach (['B4', 'M1', 'M2', 'D1', 'D2', 'D3', 'その他'] as $grade)
                        <option value="{{ $grade }}" {{ request('grade') == $grade ? 'selected' : '' }}>
                            {{ $grade }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="padding: 6px 12px; background-color: #3490dc; color: white; border: none; border-radius: 4px;">🔍 検索</button>
                <a href="{{ route('admin.users.index') }}" style="padding: 6px 12px; background-color: #6c757d; color: white; border-radius: 4px; text-decoration: none;">リセット</a>
            </div>
        </div>
    </form>
    php artisan make:mail AdminUserCreated
    {{-- ページネーションリンク --}}
    <div style="margin-top: 20px;">
        {{ $users->appends(request()->query())->links() }}
    </div>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>氏名</th>
                <th>学年</th>
                <th>未払金（円）</th>
                <th>登録日時</th>
                <th>注文履歴</th>
                <th>編集</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->grade }}</td>
                    <td>{{ number_format($user->total_amount) }}</td>
                    <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.users.orders', $user->id) }}">📜 注文履歴</a>
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user->id) }}">✏️ 編集</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">登録されているユーザがいません</td>
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
                }, 3000); // 3秒後に消える
            }
        });
    </script>
@endsection