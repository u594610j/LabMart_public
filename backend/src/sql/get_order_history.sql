SELECT
    orders.ordered_at,
    order_details.item_name,
    order_details.item_quantity,
    order_details.paid
FROM orders, order_details
WHERE orders.user_id = %s AND orders.id = order_details.order_id AND order_details.canceled = 0;
