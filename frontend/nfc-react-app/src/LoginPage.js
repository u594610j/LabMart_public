import { useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';

export default function LoginPage() {
  const navigate = useNavigate();
  const busy = useRef(false); // 多重実行防止
  const hasNotifiedError = useRef(false);
  const [errorBannerVisible, setErrorBannerVisible] = useState(false);
  const currentRequest = useRef(null);

  useEffect(() => {
    let cancelled = false;

    const poll = async () => {
      if (busy.current) return;
      busy.current = true;

      const controller = new AbortController();
      currentRequest.current = controller;

      try {
        // ① Laravelプロキシ経由で読取（nfc-api直叩きしない）
        const res = await fetch('/nfc/read', { signal: controller.signal });

        if (res.status === 503) {
          // ハード未接続/権限不足/競合 → ユーザに見える形で一度だけ通知
          console.error('NFCデバイスに接続できません（503）。USB接続/占有/権限を確認してください。');
          if (!hasNotifiedError.current) {
            if (!cancelled) {
              setErrorBannerVisible(true);
            }
            hasNotifiedError.current = true;
          }
          return; // 連続で叩き続けない
        }

        // 408/409 は想定内 → 何もせず次周期へ
        if (res.status === 408 || res.status === 409) {
          busy.current = false;
          return;
        }
        if (!res.ok) {
          console.warn('read error:', res.status);
          busy.current = false;
          return;
        }

        const data = await res.json();
        const nfcId = data?.card_id;
        if (!nfcId) {
          busy.current = false;
          return;
        }

        console.log('🎴 NFC ID:', nfcId);

        if (!cancelled) {
          setErrorBannerVisible(false);
        }
        hasNotifiedError.current = false;

        // ② 認証API
        const auth = await fetch(`/backend/nfc/name?nfc_id=${encodeURIComponent(nfcId)}`, {
          cache: 'no-store',
          signal: controller.signal
        });

        if (!auth.ok) {
          console.warn('auth error:', auth.status);
          busy.current = false;
          return;
        }

        const payload = await auth.json();
        const user = Array.isArray(payload) ? payload[0] : payload;

        if (user?.user_id) {
          localStorage.setItem(
            'currentUser',
            JSON.stringify({ id: user.user_id, name: user.user_name })
          );
          navigate('/purchase', {
            state: { user: { id: user.user_id, name: user.user_name } }
          });
        } else {
          console.warn('❌ 認証失敗');
        }
      } catch (e) {
        if (e.name !== 'AbortError') {
          console.error('エラー:', e);
        }
      } finally {
        if (currentRequest.current === controller) {
          currentRequest.current = null;
        }
        busy.current = false;
      }
    };

    const interval = setInterval(poll, 6000); // サーバのtimeout(例:5s)より長めに
    poll(); // 初回もすぐ実行

    return () => {
      cancelled = true;
      clearInterval(interval);
      currentRequest.current?.abort();
    };
  }, [navigate]);

  return (
    <div className="min-h-screen flex flex-col bg-[#f5efe9] text-[#4a362b]">
      <header className="bg-white shadow-sm">
        <div className="max-w-4xl mx-auto px-6 py-6">
          <h1 className="text-3xl font-bold text-[#6E4A3C]">LabMart 購入システム</h1>
        </div>
        <div className="h-px bg-[#e8ded6]" />
      </header>

      {errorBannerVisible && (
        <div className="bg-[#d14f4f] text-white text-center py-3 px-4 font-semibold flex flex-col gap-2" role="alert">
          <span>
            NFCデバイスに接続できません。USB接続・占有状態・権限を確認してください。
          </span>
          <button
            type="button"
            className="self-center text-sm underline"
            onClick={() => {
              setErrorBannerVisible(false);
              hasNotifiedError.current = false;
            }}
          >
            通知を閉じる
          </button>
        </div>
      )}

      <main className="flex-1 flex items-center justify-center px-4 py-12">
        <div className="w-full max-w-md bg-white rounded-2xl shadow-lg px-10 py-12 text-center">
          <h3 className="text-xl font-semibold text-[#7a5b4f] mb-8">
            購買システム ログイン
          </h3>
          <p className="text-lg font-semibold text-[#6E4A3C]">
            学生証をカードリーダーにタッチしてください
          </p>
        </div>
      </main>

      <hr></hr>

      <footer className="bg-white">
        <div className="h-px bg-[#e8ded6]" />
        <div className="w-24 h-px bg-[#cbb8ac] mx-auto mt-4" />
        <p className="text-center text-sm text-[#917d71] py-4">© 2025 LabMart 管理システム</p>
      </footer>

      <div className="flex flex-col items-center gap-2 mb-4 px-4">
        <button
          type="button"
          className="px-4 py-2 rounded-lg bg-[#6E4A3C] text-white shadow-md hover:bg-[#5c3e32] transition-colors"
          onClick={() =>
            navigate('/purchase', {
              state: { user: { id: '1', name: 'test_user' } }
            })
          }
        >
          公開用test_userログイン
        </button>
        <p className="text-xs text-[#917d71] text-center">
          NFCリーダーが使えない場合のデモログインです。エラー通知が出ている際もこちらで動作確認できます。
        </p>
      </div>
    </div>
  );
}
