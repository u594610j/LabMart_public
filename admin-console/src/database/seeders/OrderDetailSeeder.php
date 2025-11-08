<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Database\Seeder;

class OrderDetailSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();
        $items = Item::all();

        foreach ($orders as $order) {
            $orderTotal = 0;

            foreach (range(1, rand(1, 3)) as $j) {
                $item = $items->random();
                $quantity = rand(1, 5);

                OrderDetail::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_price' => $item->price,
                    'item_quantity' => $quantity,
                    'item_category' => $item->category->name ?? null,
                    'paid' => false,
                ]);

                $orderTotal += $item->price * $quantity;
            }

            $order->update(['total_price' => $orderTotal]);
        }
    }
}
