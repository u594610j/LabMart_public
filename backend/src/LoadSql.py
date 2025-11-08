import os


def load_sql(filename):
    sql_path = os.path.join(os.path.dirname(__file__), "sql", filename)
    with open(sql_path, "r", encoding="utf-8") as f:
        return f.read()
