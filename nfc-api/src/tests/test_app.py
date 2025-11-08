"""正常系"""


def test_read_success(client, monkeypatch):
    # ダミー読み取り成功をモンキーパッチ
    def fake_read_nfc_card(timeout=5):
        return "FAKE_CARD_ID"

    monkeypatch.setattr("app.read_nfc_card", fake_read_nfc_card)

    response = client.get("/nfc/read")
    assert response.status_code == 200
    assert response.json["card_id"] == "FAKE_CARD_ID"


"""NFC読み取りリクエストが競合した場合"""


def test_reading_in_progress(client, monkeypatch):
    # 強制的に読み取り中フラグを立てる
    monkeypatch.setattr("app.is_reading", True)

    response = client.get("/nfc/read")
    assert response.status_code == 409
    assert (
        response.json["error"] == "Another reading is already in progress. Please wait."
    )


"""タイムアウトしてカードが読み取れない場合"""


def test_timeout_read(client, monkeypatch):
    def fake_read_nfc_card(timeout=5):
        return None

    monkeypatch.setattr("app.read_nfc_card", fake_read_nfc_card)

    response = client.get("/nfc/read")
    assert response.status_code == 408
    assert response.json["error"] == "No card detected within default time."
