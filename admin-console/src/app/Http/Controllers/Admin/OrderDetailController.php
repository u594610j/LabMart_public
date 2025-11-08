<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class OrderDetailController extends BaseAdminController
{
    public function batchCancelOrPaid(Request $request)
    {
        $ids = $request->input('order_detail_ids', []);
        $action = $request->input('action_type','');

        if (empty($ids)) {
            return redirect()->back()->withErrors(['error' => '対象が選択されていません。']);
        }

        if ($action === 'cancel') {
            foreach ($ids as $id) {
                $detail = OrderDetail::find($id);
    
                if (! $detail || $detail->canceled || $detail->paid) {
                    continue; // 存在しない or すでにキャンセル済み or 支払い済みならスキップ
                }
    
                // 注文合計金額とユーザー累計金額を修正
                $order = $detail->order;
                $user = $order->user;
    
                $order->total_price -= $detail->subtotal;   // ← subtotalプロパティ活用
                $user->total_amount -= $detail->subtotal;
    
                $order->save();
                $user->save();
    
                // 在庫数を戻す
                if ($detail->item_id) {
                    $item = Item::find($detail->item_id);
                    if ($item) {
                        $item->stock_quantity += $detail->item_quantity;
                        $item->save();
                    }
                }
    
                // 最後にキャンセルマーク
                $detail->canceled = true;
                $detail->save();
            }
    
            return redirect()->route('admin.orders.index')->with('success', '選択した注文詳細をキャンセルしました。');    
        } else if ($action === 'pay') {
            foreach ($ids as $id) {
                $detail = OrderDetail::find($id);
    
                if (! $detail || $detail->canceled || $detail->paid) {
                    continue; // 存在しない or すでにキャンセル済み or 支払い済みならスキップ
                }
    
                // ユーザー累計金額を修正
                $order = $detail->order;
                $user = $order->user;

                $user->total_amount -= $detail->subtotal;
    
                $order->save();
                $user->save();
    
                // 最後に支払いマーク
                $detail->paid = true;
                $detail->save();
            }
    
            return redirect()->route('admin.orders.index')->with('success', '選択した注文詳細を支払いました。');  
        } else {
            return redirect()->route('admin.orders.index')->withErrors(['error' => '処理に失敗しました。']);

        }
    }
}
