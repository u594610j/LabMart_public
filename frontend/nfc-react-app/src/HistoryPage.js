import React, { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

export default function HistoryPage() {
  const location = useLocation();
  const navigate = useNavigate();
  const user = location.state?.user;

  const [history, setHistory] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (user === undefined) return; // 初期ロード時の防御

    if (!user) {
      navigate('/'); // ログインしていない場合
      return;
    }

    fetch(`/backend/history?user_id=${user.id}`)
      .then(res => res.json())
      .then(data => {
        console.log("📦 購入履歴データ:", data);
        setHistory(data);
      })
      .catch(err => {
        console.error('履歴取得失敗:', err);
      })
      .finally(() => {
        setLoading(false);
      });
  }, [user, navigate]);

  return (
    <div style={{ padding: '2rem' }}>
      <button
        onClick={() => navigate('/')}
        style={{ marginBottom: '1rem' }}
      >
        ログインページに戻る
      </button>

      <h1>{user?.name ?? 'ユーザー'} さんの購入履歴</h1>

      {/* ローディング表示 */}
      {loading ? (
        <p>読み込み中...</p>
      ) : history.length === 0 ? (
        <p style={{ marginTop: '1rem', color: 'gray' }}>購入履歴がありません。</p>
      ) : (
        history.map((record, index) => (
          <div key={index} style={{ marginBottom: '2rem' }}>
            <h3 style={{ marginBottom: '0.5rem', color: '#333' }}>
              注文日時: {new Date(record.ordered_at).toLocaleString()}
            </h3>

            <table
              style={{
                width: '100%',
                borderCollapse: 'collapse',
                marginBottom: '1rem',
                border: '1px solid #ccc'
              }}
            >
              <thead style={{ backgroundColor: '#f0f0f0' }}>
                <tr>
                  <th style={thStyle}>商品名</th>
                  <th style={thStyle}>数量</th>
                  <th style={thStyle}>支払い状況</th>
                </tr>
              </thead>
              <tbody>
                {record.items.map((item, i) => (
                  <tr key={i} style={trStyle(item)}>
                    <td style={tdStyle}>
                      {item.canceled ? (
                        <>
                          <span style={{ textDecoration: 'line-through', color: 'gray' }}>
                            {item.item_name}
                          </span>
                          <span style={badgeStyle('#f8d7da', '#721c24')}>キャンセル済み</span>
                        </>
                      ) : (
                        item.item_name
                      )}
                    </td>
                    <td style={{ ...tdStyle, textAlign: 'center' }}>{item.item_quantity}</td>
                    <td style={{ ...tdStyle, textAlign: 'center' }}>
                      {item.canceled ? (
                        <span style={{ color: 'red' }}>キャンセル済み</span>
                      ) : item.paid ? (
                        <span style={{ color: 'green' }}>支払い済み</span>
                      ) : (
                        <span style={{ color: 'red' }}>未払い</span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ))
      )}
    </div>
  );
}

// === スタイル定義 ===
const thStyle = {
  border: '1px solid #ccc',
  padding: '8px',
  textAlign: 'center'
};

const tdStyle = {
  border: '1px solid #ccc',
  padding: '8px'
};

const trStyle = (item) => ({
  backgroundColor: item.canceled ? '#f8f8f8' : 'white'
});

const badgeStyle = (bg, color) => ({
  backgroundColor: bg,
  color,
  padding: '2px 6px',
  borderRadius: '4px',
  fontSize: '0.8em',
  marginLeft: '5px'
});
