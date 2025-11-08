@extends('admin.layout')

@section('title', 'ユーザー編集')

@section('content')
    <h2>ユーザー編集</h2>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <div>
            <label>氏名：</label><br>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
        </div>

        <div>
            <label>学年：</label><br>
            <select name="grade" required>
                <option value="">--選択してください--</option>
                @foreach (['B4', 'M1', 'M2', 'D1', 'D2', 'D3', 'その他'] as $grade)
                    <option value="{{ $grade }}" {{ old('grade', $user->grade) == $grade ? 'selected' : '' }}>
                        {{ $grade }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- カードIDは表示しない（固定なので編集不可） --}}

        <div style="margin-top: 20px;">
            <button type="submit">更新する</button>
        </div>
    </form>

    <div style="margin-top: 20px;">
        <a href="{{ route('admin.users.index') }}">← ユーザ一覧に戻る</a>
    </div>
@endsection