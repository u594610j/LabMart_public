# COOP 購買システム データベース設計

## ER図
``` mermaid
erDiagram
    USERS {
        bigint id PK "ユーザーID"
        string name "ユーザー名"
        string grade "学年"
        string card_id UK "NFCカードID"
        int total_amount "累計購入金額（円）"
        timestamp created_at "登録日時"
        timestamp updated_at "更新日時"
    }

    ADMIN_USERS {
        bigint id PK "管理者ID"
        string name UK "管理者名"
        string email UK "メールアドレス"
        string password "パスワード"
        string remember_token "リメンバートークン"
        timestamp last_login_at "最終ログイン日時"
        timestamp created_at "登録日時"
        timestamp updated_at "更新日時"
        timestamp deleted_at "削除日時（論理削除）"
    }

    CATEGORIES {
        bigint id PK "カテゴリID"
        string name UK "カテゴリ名"
        timestamp created_at "登録日時"
        timestamp updated_at "更新日時"
    }

    ITEMS {
        bigint id PK "商品ID"
        string name UK "商品名"
        int price "価格"
        bigint category_id FK "カテゴリID"
        int stock_quantity "在庫数"
        timestamp created_at "登録日時"
        timestamp updated_at "更新日時"
    }

    ORDERS {
        bigint id PK "注文ID"
        bigint user_id FK "ユーザーID"
        datetime ordered_at "注文日時"
        int total_price "合計金額"
        timestamp created_at "登録日時"
        timestamp updated_at "更新日時"
    }

    ORDER_DETAILS {
        bigint id PK "注文詳細ID"
        bigint order_id FK "注文ID"
        bigint item_id FK "商品ID"
        string item_name "購入時の商品名"
        int item_price "購入時の単価"
        int item_quantity "購入数"
        string item_category "購入時のカテゴリ"
        boolean paid "月次支払いフラグ"
        timestamp created_at "登録日時"
        timestamp updated_at "更新日時"
    }

    USERS ||--o{ ORDERS : 注文する
    ORDERS ||--|{ ORDER_DETAILS : 含む
    ITEMS ||--o{ ORDER_DETAILS : 商品情報
    CATEGORIES ||--|{ ITEMS : カテゴリ分類
```
## テーブル定義

### users

| カラム名       | 型              | 制約                                          | 説明                       |
|----------------|------------------|-----------------------------------------------|----------------------------|
| id             | bigint unsigned  | PRIMARY KEY, AUTO_INCREMENT, NOT NULL         | ユーザーID                 |
| name           | varchar(255)     | NOT NULL                                      | 氏名                       |
| grade          | varchar(10)      | NOT NULL                                      | 学年 (B4, M1, M2, D1, D2, D3, その他) |
| card_id        | varchar(16)      | UNIQUE, NOT NULL                              | NFCカードID                |
| total_amount   | int              | NOT NULL, DEFAULT 0                           | 累継買い物額               |
| created_at     | timestamp        | DEFAULT CURRENT_TIMESTAMP                     | 登録日時                   |
| updated_at     | timestamp        | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | 更新日時          |

---

### admin_users

| カラム名       | 型              | 制約                                          | 説明                       |
|----------------|------------------|-----------------------------------------------|----------------------------|
| id             | bigint unsigned  | PRIMARY KEY, AUTO_INCREMENT, NOT NULL         | 管理者ID                   |
| name           | varchar(255)     | UNIQUE, NOT NULL                              | 管理者名                   |
| email          | varchar(255)     | UNIQUE, NOT NULL                              | 管理者メールアドレス       |
| password       | varchar(255)     | NOT NULL                                      | パスワード（ハッシュ）     |
| remember_token | varchar(100)     | NULL                                          | リメンバートークン         |
| last_login_at  | timestamp        | NULL                                          | 最終ログイン日時            |
| created_at     | timestamp        | DEFAULT CURRENT_TIMESTAMP                     | 登録日時                   |
| updated_at     | timestamp        | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | 更新日時          |
| deleted_at     | timestamp        | NULL                                          | 誰別削除日時               |

---

### categories

| カラム名       | 型              | 制約                                          | 説明                       |
|----------------|------------------|-----------------------------------------------|----------------------------|
| id             | bigint unsigned  | PRIMARY KEY, AUTO_INCREMENT, NOT NULL         | カテゴリID                 |
| name           | varchar(255)     | UNIQUE, NOT NULL                              | カテゴリ名                 |
| created_at     | timestamp        | DEFAULT CURRENT_TIMESTAMP                     | 登録日時                   |
| updated_at     | timestamp        | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | 更新日時          |

---

### items

| カラム名         | 型              | 制約                                          | 説明                       |
|------------------|------------------|-----------------------------------------------|----------------------------|
| id               | bigint unsigned  | PRIMARY KEY, AUTO_INCREMENT, NOT NULL         | 商品ID                     |
| name             | varchar(255)     | UNIQUE, NOT NULL                              | 商品名                     |
| price            | int              | NOT NULL                                      | 価格（税抜き）             |
| category_id      | bigint unsigned  | FOREIGN KEY → categories(id), NOT NULL        | カテゴリID                 |
| stock_quantity   | int              | NOT NULL                                      | 在庫数                     |
| created_at       | timestamp        | DEFAULT CURRENT_TIMESTAMP                     | 登録日時                   |
| updated_at       | timestamp        | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | 更新日時          |

---

### orders

| カラム名       | 型              | 制約                                          | 説明                       |
|----------------|------------------|-----------------------------------------------|----------------------------|
| id             | bigint unsigned  | PRIMARY KEY, AUTO_INCREMENT, NOT NULL         | 注文ID                     |
| user_id        | bigint unsigned  | FOREIGN KEY → users(id), NOT NULL             | 注文者ユーザーID           |
| ordered_at     | datetime          | NOT NULL                                      | 注文日時                   |
| total_price    | int              | NOT NULL, DEFAULT 0                           | 合計金額                   |
| created_at     | timestamp         | DEFAULT CURRENT_TIMESTAMP                     | 登録日時                   |
| updated_at     | timestamp         | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | 更新日時          |

---

### order_details

| カラム名         | 型              | 制約                                          | 説明                       |
|------------------|------------------|-----------------------------------------------|----------------------------|
| id               | bigint unsigned  | PRIMARY KEY, AUTO_INCREMENT, NOT NULL         | 注文詳細ID                 |
| order_id         | bigint unsigned  | FOREIGN KEY → orders(id), NOT NULL            | 注文ID                     |
| item_id          | bigint unsigned  | FOREIGN KEY → items(id), NOT NULL             | 商品ID                     |
| item_name        | varchar(255)     | NOT NULL                                      | 購入時の商品名（スナップショット） |
| item_price       | int              | NOT NULL                                      | 購入時の単価（スナップショット） |
| item_quantity    | int              | NOT NULL                                      | 購入数                     |
| item_category    | varchar(100)     | NULL                                          | 購入時のカテゴリ（スナップショット） |
| paid             | boolean          | NOT NULL, DEFAULT 0                           | 月次支払い済みフラグ       |
| canceled         | boolean          | NOT NULL, DEFAULT 0                           | キャンセルフラグ       |
| created_at       | timestamp         | DEFAULT CURRENT_TIMESTAMP                     | 登録日時                   |
| updated_at       | timestamp         | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | 更新日時          |