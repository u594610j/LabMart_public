import errno
import fcntl
import os
import threading
import time

import nfc

_reader_lock = threading.Lock()  # プロセス内


class DeviceUnavailable(Exception):
    ...


class DeviceBusy(Exception):
    ...


class InterProcessLock:
    def __init__(self, path="/tmp/pasori.lock"):
        self.path = path
        self.fd = None

    def __enter__(self):
        self.fd = os.open(self.path, os.O_CREAT | os.O_RDWR, 0o666)
        fcntl.flock(self.fd, fcntl.LOCK_EX | fcntl.LOCK_NB)
        return self

    def __exit__(self, exc_type, exc, tb):
        try:
            if self.fd is not None:
                fcntl.flock(self.fd, fcntl.LOCK_UN)
        finally:
            if self.fd is not None:
                os.close(self.fd)
                self.fd = None


def _extract_id(tag) -> str | None:
    try:
        if hasattr(tag, "idm"):
            return tag.idm.hex().upper()
        if hasattr(tag, "identifier"):
            return tag.identifier.hex().upper()
        ident = getattr(tag, "identifier", b"")
        return ident.hex().upper() if ident else None
    except Exception:
        return None


def scanNfc(timeout: float = 5.0) -> str | None:
    """タグが見つかれば16進ID。見つからなければ None。占有/未接続は例外。"""
    start = time.monotonic()
    card_id = None

    # プロセス内排他（既存）
    if not _reader_lock.acquire(blocking=False):
        raise DeviceBusy("reader busy in process")
    try:
        # プロセス間排他を追加
        try:
            with InterProcessLock():
                with nfc.ContactlessFrontend("usb") as clf:

                    def on_connect(tag):
                        nonlocal card_id
                        card_id = _extract_id(tag)
                        return True

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
            # 他プロセスが保持中
            raise DeviceBusy("locked by other process")

        except OSError as e:
            if e.errno in (errno.EBUSY, 16):
                raise DeviceBusy(str(e))
            if e.errno in (errno.ENODEV, 19, errno.EACCES, 13):
                raise DeviceUnavailable(str(e))
            raise DeviceUnavailable(str(e))

        return card_id  # None ならタイムアウト扱い
    finally:
        _reader_lock.release()
