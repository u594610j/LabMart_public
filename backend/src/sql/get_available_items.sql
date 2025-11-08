SELECT 
  items.id AS item_id, 
  items.name, 
  items.price AS item_price, 
  items.stock_quantity, 
  categories.id AS category_id, 
  categories.name AS category_name
FROM items, categories
WHERE stock_quantity > 0 AND items.category_id = categories.id;
