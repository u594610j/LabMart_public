import os

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


# TODO
# 現在購入可能なアイテムをreturn
# {id, name, price, category, stock}


def getItems():
    connection = None
    cursor = None
    try:
        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)
        sql = load_sql("get_available_items.sql")
        cursor.execute(sql)
        result = cursor.fetchall()
        return result if result else []

    except Error as e:
        print(f"DB Error: {e}")
        return None
    finally:
        if cursor:
            cursor.close()
        if connection and connection.is_connected():
            connection.close()
