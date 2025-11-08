<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', '管理画面')</title>
</head>
<body>
    <header>
        <h1>LabMart 管理システム</h1>
        <nav>
            <a href="{{ route('admin.dashboard') }}">🏠 ダッシュボード</a> |
            <a href="{{ route('admin.items.index') }}">📦 商品一覧</a> |
            <a href="{{ route('admin.users.index') }}">👤 ユーザ一覧</a> |
            <a href="{{ route('admin.orders.index') }}">🛒 注文一覧</a> |
            <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">
                @csrf
                <button type="submit">🚪 ログアウト</button>
            </form>
        </nav>
        <hr>
    </header>

    <main style="padding: 20px;">
        @yield('content')
    </main>

    <footer>
        <hr>
        <p>&copy; 2025 LabMart 管理システム</p>
    </footer>
</body>
</html>