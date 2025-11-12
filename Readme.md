# LabMart
## 🏷️ 概要
**🔒 本リポジトリは、既存の開発プロジェクトを整理した「公開用バージョン」です。**  
**LabMart** は、研究室内での食品・飲料の在庫管理及び購入管理を効率化するためのWebベース購買管理システムです。  
管理者は商品の登録、在庫の更新、注文の支払い及びキャンセル管理を行い、ユーザはNFCタグを用いた簡単な認証で購入を記録できます。


## 👥 開発メンバー
本システムは研究室メンバー3名による共同開発プロジェクトです。  
私は **ユーザアプリの開発とシステム全体の統合** を担当しました。  
また、**Docker環境での本番デプロイおよび運用テスト**を行い、実運用可能な構成を確立しました。


## 🐳 Docker Compose による開発環境構築
 
本リポジトリでは、**Docker Compose** を用いてローカル開発環境を構築しています。バックエンド（Flask など）、フロントエンド（React）、および MySQL データベースが連携する構成です。

###  サービス構成
| サービス名     | 説明               | ポート              |
|----------------|--------------------|---------------------|
| `nginx`        | リバースプロキシ（外部公開80番） | `localhost:80`  |
| `admin-console`| Laravel (管理者アプリ/APIサーバ) | 内部9000番 (FastCGI) |
| `backend`      | Python/Flask (ユーザーAPIサーバ) | 内部8000番 |
| `frontend`     | Reactアプリ（ユーザーWebフロント） | 内部3000番 |
| `nfc-api`      | NFCリーダAPIサービス  | (外部非公開) |
| `db`           | MySQL 8.0 データベース | 内部3306番 |

### ⚙️ 使用技術
| 分類        | 使用技術                          |
| --------- | ----------------------------- |
| ユーザアプリフロントエンド   | React, JavaScript, HTML, CSS  |
| ユーザアプリバックエンド    | Flask (Python) |
| 管理者アプリ    | Laravel (PHP) |
| データベース    | MySQL 8.0                     |
| インフラ / 環境 | Docker, Docker Compose, Nginx |
| 認証デバイス    | PaSoRi RC-S380 (NFCリーダ)       |
| 開発補助ツール   | GitHub, VS Code      |

### DBテーブル構成（ER図）
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
        boolean canceled "キャンセルフラグ"
        timestamp created_at "登録日時"
        timestamp updated_at "更新日時"
    }

    USERS ||--o{ ORDERS : 注文する
    ORDERS ||--|{ ORDER_DETAILS : 含む
    ITEMS ||--o{ ORDER_DETAILS : 商品情報
    CATEGORIES ||--|{ ITEMS : カテゴリ分類
```


## 🧭 セットアップ方法
**0️⃣ 本アプリが動作する環境（推奨バージョン）**  
```
Docker Engine 19.03 以降 + Docker Compose CLI 1.25 以降
```
**1️⃣ リポジトリのクローン,コンテナの起動**  
```bash
git clone https://github.com/u594610j/LabMart_public.git
cd LabMart_public
docker compose up -d
```
**2️⃣ 起動確認**  
ブラウザで以下のURLにアクセスします。
```
ユーザ：'http://localhost'
管理者：'http://localhost/admin'
⚠️管理者ユーザ名：admin
⚠️管理者パスワード：admin
```