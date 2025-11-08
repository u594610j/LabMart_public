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
# 引数で16進数の値を受け取る
# usersテーブルのcard_idと照合
# {id, name} or None を返却


def getName(card_id: str):
    connection = None
    cursor = None
    try:
        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)
        sql = load_sql("get_username_by_cardid.sql")
        cursor.execute(sql, (card_id,))
        result = cursor.fetchone()

        if result:
            return {
                "user_id": result["user_id"],
                "user_name": result["user_name"],
                "nfc_id": result["card_id"],
            }
        else:
            return None

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
