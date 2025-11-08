@extends('admin.layout')

@section('title', '注文履歴')

@section('content')
    <h2>注文履歴一覧</h2>

    {{-- フィルタフォーム --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" style="margin-bottom: 20px;">
        <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
            <div style="display: flex; flex-direction: column;">
                <label for="user_name" style="margin-bottom: 5px;">ユーザー名</label>
                <input type="text" name="user_name" id="user_name" value="{{ request('user_name') }}" placeholder="例：田中" style="padding: 5px; width: 200px;">
            </div>
            <div style="display: flex; flex-direction: column;">
                <label for="grade" style="margin-bottom: 5px;">学年</label>
                <select name="grade" id="grade" style="padding: 5px; width: 120px;">
                    <option value="">-- 全学年 --</option>
                    @foreach (['B4', 'M1', 'M2', 'D1', 'D2', 'D3', 'その他'] as $g)
                        <option value="{{ $g }}" {{ request('grade') === $g ? 'selected' : '' }}>
                            {{ $g }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="padding: 6px 12px; background-color: #3490dc; color: white; border: none; border-radius: 4px;">
                    🔍 検索
                </button>
                <a href="{{ route('admin.orders.index') }}" style="padding: 6px 12px; background-color: #6c757d; color: white; border-radius: 4px; text-decoration: none;">
                    リセット
                </a>
            </div>
        </div>
    </form>

    <div style="margin: 20px 0;">
        {{ $orders->appends(request()->query())->links() }}
    </div>

    @forelse ($orders as $order)
        @php
            # キャンセル注文かどうか
            $allCanceled = $order->orderDetails->every(fn($detail) => $detail->canceled);
            # この注文の未キャンセルの明細が全部支払い済みか判定
            $allPaidOrCanceled = $order->orderDetails->every(fn($detail) => $detail->paid || $detail->canceled);
        @endphp

        <div style="border: 1px solid #ccc; border-radius: 8px; padding: 20px; margin-bottom: 40px; background-color: {{ ($allPaidOrCanceled) ? '#f0f0f0' : 'white' }};">
            <div style="margin-bottom: 15px;">
                <strong>注文ID:</strong> {{ $order->id }}<br>
                <strong>注文者:</strong> {{ $order->user->name }} ({{ $order->user->grade }})<br>
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

            <form method="POST" action="{{ route('admin.order_details.batch_cancel_or_paid') }}">
                @csrf
                @method('PUT')

                @if (!$allPaidOrCanceled)
                    <div style="margin-bottom: 10px;">
                        <button type="button" id="select-all-{{ $order->id }}" style="background-color: #007bff; color: white; padding: 5px 10px; border-radius: 5px;">
                            全選択
                        </button>
                    </div>
                @endif

                <table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse;">
                    <thead style="background-color: #f0f0f0;">
                        <tr>
                            <th></th>
                            <th>商品名</th>
                            <th>カテゴリ</th>
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
                                    @if (!$detail->canceled && !$detail->paid)
                                        <input type="checkbox" class="pay-cancel-checkbox-{{ $order->id }}" name="order_detail_ids[]" value="{{ $detail->id }}">
                                    @endif
                                </td>
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
                                <td>{{ $detail->item_category ?? '-' }}</td>
                                <td style="text-align: right;">{{ number_format($detail->item_price) }} 円</td>
                                <td style="text-align: center;">{{ $detail->item_quantity }}</td>
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

                @if (!$allPaidOrCanceled)
                    <div id="cancel-button-wrapper-{{ $order->id }}" style="margin-top: 20px; text-align: right; display: none;">
                        <button type="submit" name="action_type" value="cancel" style="background-color: #e3342f; color: white; padding: 8px 16px; border-radius: 5px;">
                            選択した商品をキャンセル
                        </button>
                    </div>
                    <div id="pay-button-wrapper-{{ $order->id }}" style="margin-top: 20px; text-align: right; display: none;">
                        <button type="submit" name="action_type" value="pay" style="background-color: #007bff; color: white; padding: 8px 16px; border-radius: 5px;">
                            選択した商品を支払う
                        </button>
                    </div>
                @endif
            </form>
        </div>
    @empty
        <p style="text-align: center; margin-top: 50px;">注文データがありません。</p>
    @endforelse

    {{-- 一括選択＆ボタン切り替え --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach ($orders as $order)
                const checkboxes{{ $order->id }} = document.querySelectorAll('.pay-cancel-checkbox-{{ $order->id }}');
                const cancelButton{{ $order->id }} = document.getElementById('cancel-button-wrapper-{{ $order->id }}');
                const payButton{{ $order->id }} = document.getElementById('pay-button-wrapper-{{ $order->id }}');
                const selectAllButton{{ $order->id }} = document.getElementById('select-all-{{ $order->id }}');

                function toggleButton{{ $order->id }}() {
                    const anyChecked = Array.from(checkboxes{{ $order->id }}).some(cb => cb.checked);
                    cancelButton{{ $order->id }}.style.display = anyChecked ? 'block' : 'none';
                    payButton{{ $order->id }}.style.display = anyChecked ? 'block' : 'none';
                }

                checkboxes{{ $order->id }}.forEach(cb => {
                    cb.addEventListener('change', toggleButton{{ $order->id }});
                });

                if (selectAllButton{{ $order->id }}) {
                    selectAllButton{{ $order->id }}.addEventListener('click', function () {
                        const allChecked = Array.from(checkboxes{{ $order->id }}).every(cb => cb.checked);
                        checkboxes{{ $order->id }}.forEach(cb => cb.checked = !allChecked);
                        toggleButton{{ $order->id }}();
                    });
                }
            @endforeach
        });
    </script>
@endsection