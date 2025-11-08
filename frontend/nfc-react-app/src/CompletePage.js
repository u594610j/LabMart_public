import React, { useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';

export default function CompletePage() {
  const navigate = useNavigate();
  const location = useLocation();
  const purchasedItems = location.state?.selectedItems || [];

  useEffect(() => {
    const timer = setTimeout(() => {
      localStorage.removeItem('currentUser'); // 任意: セッション消すならここで
      navigate('/');
    }, 10000); // 10秒後にLoginへ自動遷移

    return () => clearTimeout(timer);
  }, [navigate]);

  useEffect(() => {
    if (!location.state) {
      navigate('/'); // 強制ログイン画面へ
    }
  }, [location, navigate]);
  
  return (
    <div style={{ padding: '2rem' }}>
      <h1>🎉 購入ありがとうございました！</h1>

      {purchasedItems.length === 0 ? (
        <p>購入情報がありません。</p>
      ) : (
        <ul style={{ marginTop: '2rem' }}>
          {purchasedItems.map(item => (
            <li key={item.item_id} style={{ marginBottom: '1rem' }}>
              {item.name} × {item.quantity}個
            </li>
          ))}
        </ul>
      )}

      <p style={{ marginTop: '2rem', color: 'gray' }}>
        10秒後にログイン画面へ戻ります...
      </p>
    </div>
  );
}
