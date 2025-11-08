@extends('admin.layout')

@section('title', '管理者ダッシュボード')

@section('content')
    <h2>管理者ダッシュボード</h2>

    <p>ようこそ、{{ Auth::guard('admin')->user()->name }} さん！</p>

    {{-- 将来ここに売上グラフや履歴一覧を追加予定 --}}
@endsection