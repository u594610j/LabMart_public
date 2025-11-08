import os
from collections import defaultdict

from dotenv import load_dotenv
from LoadSql import load_sql
from mysql.connector import Error, connect

load_dotenv()


def get_db_connection():
    return connect(
        host=os.getenv("DB_HOST", "db"),
        port=int(os.getenv("DB_PORT", "3306")),
        user=os.getenv("DB_USERNAME"),
        password=os.getenv("DB_PASSWORD"),
        database=os.getenv("DB_DATABASE"),
        charset=os.getenv("DB_CHARSET", "utf8mb4"),
    )


"""{
・注文日時
{
・商品名
・購入数
・単価
・月時支払いフラグ
}
}"""


def getHistory(user_id):
    connection = None
    cursor = None
    try:
        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)
        sql = load_sql("get_order_history.sql")
        cursor.execute(sql, (user_id,))
        rows = cursor.fetchall()

        grouped = defaultdict(list)
        for row in rows:
            ordered_at = row["ordered_at"]
            item_info = {
                "item_name": row["item_name"],
                "item_quantity": row["item_quantity"],
                # "item_price": row["item_price"],
                "paid": bool(row["paid"]),
            }
            grouped[ordered_at].append(item_info)

        result = [
            {"ordered_at": ordered_at, "items": items}
            for ordered_at, items in grouped.items()
        ]

        if result:
            return result
        else:
            return []

    except Error as e:
        print(f"DB Error: {e}")
        return None
    except Exception as e:
        print(f"DB Error: {e}")
        return None
    finally:
        if cursor:
            cursor.close()
        if connection and connection.is_connected():
            connection.close()
