SELECT id AS user_id, name AS user_name, card_id AS card_id
FROM users 
WHERE REPLACE(UPPER(card_id), '-', '') = %s
LIMIT 1;
