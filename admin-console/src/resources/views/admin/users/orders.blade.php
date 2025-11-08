@extends('admin.layout')

@section('title', 'ユーザー注文履歴')

@section('content')
    <h2>{{ $user->name }} さんの注文履歴</h2>

    @forelse ($orders as $order)
        @php
            // この注文の明細が全部キャンセル済みか判定
            $allCanceled = $order->orderDetails->every(fn($detail) => $detail->canceled);
            # この注文の未キャンセルの明細が全部支払い済みか判定
            $allPaidOrCanceled = $order->orderDetails->every(fn($detail) => $detail->paid || $detail->canceled);
        @endphp

        <div style="border: 1px solid #ccc; border-radius: 8px; padding: 20px; margin-bottom: 30px; background-color: {{ $allPaidOrCanceled ? '#f0f0f0' : 'white' }};">
            <div style="margin-bottom: 10px;">
                <strong>注文ID:</strong> {{ $order->id }}<br>
                <strong>注文日時:</strong> {{ $order->ordered_at->format('Y-m-d H:i') }}<br>
                <strong>合計金額:</strong> {{ number_format($order->total_price) }} 円
            </div>

            @if ($allCanceled)
                <div style="color: red; font-weight: bold; margin-bottom: 10px;">
                    この注文はすべてキャンセル済みです
                </div>
            @elseif ($allPaidOrCanceled)
                <div style="color: green; font-weight: bold; margin-bottom: 10px;">
                    この注文はすべて支払い済みです
                </div>  
            @endif

            <table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse;">
                <thead style="background-color: #f0f0f0;">
                    <tr>
                        <th>商品名</th>
                        <th>単価</th>
                        <th>数量</th>
                        <th>小計</th>
                        <th>支払い状況</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderDetails as $detail)
                        <tr @if ($detail->canceled) style="background-color: #f8f8f8;" @endif>
                            <td>
                                @if ($allPaidOrCanceled)
                                    {{ $detail->item_name }}
                                @else
                                    @if ($detail->canceled)
                                        <span style="text-decoration: line-through; color: gray;">
                                            {{ $detail->item_name }}
                                        </span>
                                        <span style="background-color: #f8d7da; color: #721c24; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; margin-left: 5px;">
                                            キャンセル済み
                                        </span>
                                    @elseif ($detail->paid)
                                        <span style="text-decoration: line-through; color: gray;">
                                            {{ $detail->item_name }}
                                        </span>
                                        <span style="background-color: #d7f8d9; color: #007b00; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; margin-left: 5px;">
                                            支払い済み
                                        </span>                              
                                    @else
                                        {{ $detail->item_name }}
                                    @endif
                                @endif
                            </td>
                            <td style="text-align: right;">{{ number_format($detail->item_price) }} 円</td>
                            <td style="text-align: center;">{{ $detail->item_quantity }} 個</td>
                            <td style="text-align: right;">{{ number_format($detail->item_price * $detail->item_quantity) }} 円</td>
                            <td style="text-align: center;">
                                @if ($detail->paid)
                                    <span style="color: green;">支払い済み</span>
                                @elseif ($detail->canceled)
                                    <span style="color: red;">キャンセル済み</span>
                                @else
                                    <span style="color: red;">未払い</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    @empty
        <p style="text-align: center; margin-top: 50px;">注文履歴はありません。</p>
    @endforelse

    <div style="margin-top: 30px; text-align: center;">
        <a href="{{ route('admin.users.index') }}" style="text-decoration: underline; color: #007bff;">← ユーザー一覧に戻る</a>
    </div>
@endsection