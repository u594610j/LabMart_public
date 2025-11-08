# ベースイメージ
FROM node:22-slim

# 作業ディレクトリの作成（イメージ内）
WORKDIR /app

# package.json と package-lock.json を先にコピー（キャッシュ利用）
COPY nfc-react-app/package*.json ./nfc-react-app/

# 依存パッケージのインストール
WORKDIR /app/nfc-react-app
RUN npm install

# アプリのソースコードをコピー
COPY nfc-react-app/ .

# React アプリの起動
CMD ["npm", "start"]
