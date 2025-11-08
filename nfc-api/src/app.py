import errno
import fcntl
import os
import threading
import time

import nfc
from flask import Flask, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# ① プロセス内の二重実行を防ぐロック
_inproc_lock = threading.Lock()


# ② プロセス間ロック（/tmp/pasori.lock）
class InterProcessLock:
    def __init__(self, path="/tmp/pasori.lock"):
        self.path = path
        self.fd = None

    def __enter__(self):
        self.fd = os.open(self.path, os.O_CREAT | os.O_RDWR, 0o666)
        fcntl.flock(self.fd, fcntl.LOCK_EX | fcntl.LOCK_NB)  # 取れなければ BlockingIOError
        return self

    def __exit__(self, exc_type, exc, tb):
        try:
            if self.fd is not None:
                fcntl.flock(self.fd, fcntl.LOCK_UN)
        finally:
            if self.fd is not None:
                os.close(self.fd)
                self.fd = None


def _extract_id(tag):
    try:
        if hasattr(tag, "idm"):  # FeliCa
            return tag.idm.hex().upper()
        if hasattr(tag, "identifier"):  # Type A/MIFARE
            return tag.identifier.hex().upper()
        ident = getattr(tag, "identifier", b"")
        return ident.hex().upper() if ident else None
    except Exception:
        return None


def read_nfc_card(timeout=5):
    """同期読み取り。必ず timeout 以内に戻り、ハンドルはスコープで解放される。"""
    start = time.monotonic()
    card_id = None

    # まずは同一プロセス内の多重起動を防止
    if not _inproc_lock.acquire(blocking=False):
        return {"error": "reading_in_progress"}

    try:
        # 他プロセスと排他
        try:
            with InterProcessLock():
                # ここから先でデバイスを開く（失敗しても with で必ず閉じる）
                with nfc.ContactlessFrontend("usb") as clf:

                    def on_connect(tag):
                        nonlocal card_id
                        card_id = _extract_id(tag)
                        return True  # 一枚で終了

                    clf.connect(
                        rdwr={
                            "on-connect": on_connect,
                            "targets": ["212F", "424F", "106A"],
                            "beep-on": False,
                        },
                        terminate=lambda: (
                            (time.monotonic() - start) >= timeout
                            or (card_id is not None)
                        ),
                    )
        except BlockingIOError:
            # 他プロセスが使用中（flock が取れない）
            return {"error": "device_busy", "detail": "locked_by_other_process"}

        except OSError as e:
            # 代表的な errno をHTTP層が扱えるようにマップ
            if e.errno in (errno.EBUSY, 16):
                return {"error": "device_busy", "detail": str(e)}
            if e.errno in (errno.ENODEV, 19, errno.EACCES, 13):
                return {"error": "device_unavailable", "detail": str(e)}
            # その他は一律 device_unavailable として返す
            return {"error": "device_unavailable", "detail": str(e)}

        # 正常系：読めた or タイムアウト
        return {"card_id": card_id}

    finally:
        _inproc_lock.release()


@app.route("/nfc/read", methods=["GET"])
def read_nfc():
    result = read_nfc_card(timeout=5)

    if result.get("error") == "reading_in_progress":
        return (
            jsonify({"error": "Another reading is already in progress. Please wait."}),
            409,
        )

    if result.get("error") == "device_busy":
        return jsonify({"error": "device_busy", "detail": result.get("detail")}), 503

    if result.get("error") == "device_unavailable":
        return (
            jsonify({"error": "device_unavailable", "detail": result.get("detail")}),
            503,
        )

    if result.get("card_id"):
        return jsonify({"card_id": result["card_id"]})

    # 読めなかった（タイムアウト）
    return jsonify({"error": "No card detected within default time."}), 408


@app.route("/", methods=["GET"])
def home():
    return "NFC API Server is running!"
