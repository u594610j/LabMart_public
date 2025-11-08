@extends('admin.layout')

@section('title', 'ユーザ登録')

@section('content')
    <h2>新規ユーザ登録</h2>
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
    {{-- 登録フォーム --}}
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        {{-- 氏名 --}}
        <div style="margin-bottom: 15px;">
            <label>氏名：</label><br>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>

        {{-- 学年 --}}
        <div style="margin-bottom: 15px;">
            <label>学年：</label><br>
            <select name="grade" required>
                <option value="">-- 選択してください --</option>
                <option value="B4" {{ old('grade') == 'B4' ? 'selected' : '' }}>B4（学部4年）</option>
                <option value="M1" {{ old('grade') == 'M1' ? 'selected' : '' }}>M1（修士1年）</option>
                <option value="M2" {{ old('grade') == 'M2' ? 'selected' : '' }}>M2（修士2年）</option>
                <option value="D1" {{ old('grade') == 'D1' ? 'selected' : '' }}>D1（博士1年）</option>
                <option value="D2" {{ old('grade') == 'D2' ? 'selected' : '' }}>D2（博士2年）</option>
                <option value="D3" {{ old('grade') == 'D3' ? 'selected' : '' }}>D3（博士3年）</option>
                <option value="その他" {{ old('grade') == 'その他' ? 'selected' : '' }}>その他</option>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label>累積購入金額（円）：</label><br>
            <input type="number" name="total_amount" value="{{ old('total_amount', 0) }}" min="0" step="1" required>
        </div>

        {{-- NFCカードID --}}
        <div style="margin-bottom: 15px;">
            <label>NFCカードID：</label><br>
            <input type="text" name="card_id" id="card_id" value="{{ old('card_id') }}" readonly required>

        
            <button type="button" id="read-nfc-button" onclick="readNFC()">📡 読み取り</button>
        
            <div id="nfc-status" style="margin-top: 10px; color: #555;"></div>
        </div>

        {{-- 登録ボタン --}}
        <div style="margin-top: 20px;">
            <button type="submit">登録する</button>
        </div>
    </form>

    <div style="margin-top: 20px;">
        <a href="{{ route('admin.users.index') }}">← ユーザ一覧に戻る</a>
    </div>

    {{-- NFC読み取り用スクリプト --}}
    <script>
    function readNFC() {
        const button = document.getElementById('read-nfc-button');
        const cardInput = document.getElementById('card_id');
        const statusText = document.getElementById('nfc-status');

        statusText.textContent = "読み取り中です...（NFCカードをかざしてください）";
        button.disabled = true;  

        fetch('{{ route('admin.nfc.proxyRead') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.card_id) {
                cardInput.value = data.card_id;
                statusText.textContent = "読み取り成功！";
            } else if (data.error) {
                statusText.textContent = "エラー: " + data.error;
            }
        })
        .catch(error => {
            console.error(error);
            statusText.textContent = "読み取りに失敗しました";
        })
        .finally(() => {
            button.disabled = false;

            setTimeout(() => {
                statusText.textContent = "";
            }, 1000);
        });}
    </script>
@endsection