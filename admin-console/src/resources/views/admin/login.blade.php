<!-- resources/views/admin/login.blade.php -->

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>管理者ログイン</title>
</head>
<body>
    <h2>管理者ログイン</h2>

    @if($errors->any())
        <div style="color: red;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <label>ユーザー名:</label>
        <input type="text" name="name" required>
        <br>

        <label>パスワード:</label>
        <input type="password" name="password" required>
        <br>

        <button type="submit">ログイン</button>
    </form>
</body>
</html>