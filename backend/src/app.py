from flask import Flask, jsonify, request
from flask_cors import CORS
from GetHistory import getHistory
from GetItems import getItems
from GetName import getName
from ProcessOrder import processOrder
from ScanNfc import DeviceBusy, DeviceUnavailable, scanNfc

app = Flask(__name__)
CORS(app)


@app.route("/nfc/read", methods=["GET"])
def getHexId():
    try:
        card_id = scanNfc()
        return jsonify({"card_id": card_id}), 200
    except DeviceBusy as e:
        return jsonify({"error": "device_busy", "detail": str(e)}), 503
    except DeviceUnavailable as e:
        return jsonify({"error": "device_unavailable", "detail": str(e)}), 503
    except Exception as e:
        return jsonify({"error": "internal", "detail": repr(e)}), 500


@app.route("/nfc/name", methods=["GET"])
def name():
    try:
        nfc_id = request.args.get("nfc_id")
        user = getName(nfc_id)

        if user:
            return jsonify([user]), 200  # ← React 側と合わせて配列で返す
        else:
            return jsonify([]), 404  # ← 空配列にしておくとReact側でusers[0]がundefinedになるのを防げる

    except Exception as e:
        return jsonify({"error": str(e)}), 500


# returnするリストは{id, name, price, category, stock_quantity}
@app.route("/items", methods=["GET"])
def items():
    try:
        available_items = getItems()
        return jsonify(available_items), 200
    except Exception as e:
        return jsonify({"error": str(e)}), 500


# front-end 購入ボタン押下時
@app.route("/purchases", methods=["POST"])
def order():
    data = request.get_json()
    if data is None:
        return {"error": "Invalid JSON"}, 400
    user_id = data.get("user_id")
    ordered_at = data.get("ordered_at")
    items = data.get("items")

    result = processOrder(user_id, ordered_at, items)
    if result == "complete":
        return jsonify({"message": "complete"}), 200
    else:
        return jsonify({"error": "failed"}), 500


# history
@app.route("/history", methods=["GET"])
def history():
    try:
        userId = request.args.get("user_id")
        if userId is None:
            return {"error": "Invalid user_id"}, 400
        result = getHistory(userId)
        return jsonify(result), 200
    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8000, debug=True)
