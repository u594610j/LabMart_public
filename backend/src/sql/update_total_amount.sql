UPDATE users
SET total_amount = total_amount + %s
WHERE id = %s;