FROM python:3.12-slim

# 必要パッケージインストール
RUN apt-get update && apt-get install -y \
    libpcsclite-dev \
    pcscd \
    libusb-1.0-0 libusb-1.0-0-dev usbutils \
    && rm -rf /var/lib/apt/lists/*

# 作業ディレクトリ
WORKDIR /app

# 必要なライブラリインストール
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Flaskアプリソースコードをコピー
COPY ./src .

# Flaskアプリ起動に必要な環境変数設定
ENV FLASK_APP=app.py

CMD ["flask", "run", "--host=0.0.0.0", "--port=5000"]