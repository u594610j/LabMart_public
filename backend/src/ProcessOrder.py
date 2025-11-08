import os
from datetime import datetime

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


def processOrder(user_id, ordered_at, items):
    connection = None
    cursor = None
    try:
        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)
        sql_get_item = load_sql("select_item_by_id.sql")

        db_items = []
        total_price = 0
        updated = []

        # itemsの形は、frontend側ではitems:{id, quantity}となっている
        # ↓ items['id']をもとにdb_items:{id, name, price, stock_quantity, category_id}を生成
        for item in items:
            cursor.execute(sql_get_item, (item["item_id"],))
            db_item = cursor.fetchone()

            if not db_item:
                raise ValueError(f"error(id: {item['item_id']} not exist)")

            if db_item["price"] < 0:
                raise ValueError(f"error(id: {item['item_id']} price < 0)")

            db_items.append(db_item)

        # itemsの順番とdb_itemsの順番が必ずしも一致するとしていいはず(↑のfor文の処理の構造上)
        # → zipを用いてitemsとdb_itemsをセットにする
        # itemsの要素数 != db_itemsの要素数ならばエラー
        # items[item_id] != db_items[id]ならばエラー
        if len(items) != len(db_items):
            raise ValueError("error: items and db_items length mismatch")

        for item, db_item in zip(items, db_items):
            if item["item_id"] != db_item["id"]:
                raise ValueError(
                    f"error: item_id mismatch(id:{item['item_id']}!=db:{db_item['id']})"
                )

            order_quantity = item["item_quantity"]
            stock_quantity = db_item["stock_quantity"]

            if order_quantity < 0:
                raise ValueError(
                    f"error(item_id: {item['item_id']} order quantity < 0)"
                )

            if stock_quantity - order_quantity < 0:
                raise ValueError(f"error(item_id: {item['item_id']} order > stock)")

            total_price += db_item["price"] * order_quantity

            updated.append(
                {
                    "item_id": item["item_id"],
                    "remaining_quantity": stock_quantity - order_quantity,
                }
            )

        sql_insert_order = load_sql("insert_into_orders.sql")
        # ordered_atを型変換
        ordered_at_converted = datetime.fromisoformat(
            ordered_at.replace("Z", "+00:00")
        ).strftime("%Y-%m-%d %H:%M:%S")
        cursor.execute(sql_insert_order, (user_id, ordered_at_converted, total_price))
        order_id = cursor.lastrowid

        sql_insert_detail = load_sql("insert_into_details.sql")
        for item, db_item in zip(items, db_items):
            cursor.execute(
                sql_insert_detail,
                (
                    order_id,
                    db_item["id"],
                    db_item["name"],
                    db_item["price"],
                    item["item_quantity"],
                    db_item["category_id"],
                ),
            )

        sql_update_stock = load_sql("update_items.sql")
        for upd in updated:
            cursor.execute(
                sql_update_stock, (upd["remaining_quantity"], upd["item_id"])
            )

        sql_update_total_amount = load_sql("update_total_amount.sql")
        cursor.execute(sql_update_total_amount, (total_price, user_id))

        connection.commit()
        return "complete"

    except Error as e:
        print("DB Error:", e)
        if connection:
            connection.rollback()
        return None
    except Exception as e:
        print("DB Error:", e)
        if connection:
            connection.rollback()
        return None

    finally:
        if cursor:
            cursor.close()
        if connection and connection.is_connected():
            connection.close()
