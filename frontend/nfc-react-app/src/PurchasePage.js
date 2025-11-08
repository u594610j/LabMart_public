import React, { useEffect, useState, useRef, useCallback } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';

export default function PurchasePage() {
  const [items, setItems] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState('すべて');
  const [selectedItems, setSelectedItems] = useState([]);
  const navigate = useNavigate(); 
  const timerRef = useRef(null);
  const [confirming, setConfirming] = useState(false);

  const location = useLocation();
  const currentUser = location.state?.user || JSON.parse(localStorage.getItem('currentUser'));

  console.log('🧪 currentUser:', currentUser);
  useEffect(() => {
    if (!currentUser) {
      navigate('/');
    }
  }, [navigate, currentUser]);

  const handleLogout = useCallback(() => {
    localStorage.removeItem('currentUser');
    navigate('/');
  }, [navigate]);

  const resetTimer = useCallback(() => {
    if (timerRef.current) clearTimeout(timerRef.current);
    timerRef.current = setTimeout(() => {
      handleLogout();
    }, 2 * 60 * 1000);
  }, [handleLogout]);

  useEffect(() => {
    window.addEventListener('mousemove', resetTimer);
    window.addEventListener('keydown', resetTimer);
    resetTimer();

    return () => {
      window.removeEventListener('mousemove', resetTimer);
      window.removeEventListener('keydown', resetTimer);
      if (timerRef.current) clearTimeout(timerRef.current);
    };
  }, [resetTimer]);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await fetch('/backend/items');
        const data = await res.json();
        setItems(data);
      } catch (err) {
        console.error('商品データの取得に失敗しました:', err);
      }
    };

    fetchData();
  }, []);

  const handleSelect = (item) => {
    const exists = selectedItems.find(i => i.item_id === item.item_id);
    if (exists) return;

    setSelectedItems([...selectedItems, { ...item, quantity: 1 }]);
  };

  const handlePurchaseConfirm = async () => {
    try {
        const res = await fetch('/backend/purchases', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          user_id: currentUser.id,
          ordered_at: new Date().toISOString(),
          items: selectedItems.map(item => ({
            item_id: item.item_id,
            item_quantity: item.quantity
          })),
        }),
      });

      if (res.ok) {
        navigate('/complete', { state: { selectedItems }, replace: true });
      } else {
        alert('購入に失敗しました');
      }
    } catch (err) {
      console.error('POSTエラー:', err);
      alert('通信エラーが発生しました');
    }
  };

  const updateQuantity = (id, delta) => {
    setSelectedItems(prev =>
      prev.flatMap(item => {
        if (item.item_id !== id) return [item];

        const newQty = item.quantity + delta;

        if (newQty < 1) return [];
        if (newQty > item.stock_quantity) return [item];

        return [{ ...item, quantity: newQty }];
      })
    );
  };

  const totalPrice = selectedItems.reduce(
    (sum, item) => sum + item.item_price * item.quantity, 0
  );

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '1rem' }}>
        <div>
          <p>ログイン中: {currentUser?.name ?? 'ユーザー'}</p>
        </div>
        <button onClick={handleLogout}>ログアウト</button>
        <button onClick={() => navigate('/history', { state: { user: currentUser } })}>
          購入履歴を見る
        </button>
      </div>

      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.5rem', padding: '1rem' }}>
        <button onClick={() => setSelectedCategory('すべて')}>すべて</button>
        {[...new Set(items.map(item => item.category_name))].map(cat => (
          <button key={cat} onClick={() => setSelectedCategory(cat)}>{cat}</button>
        ))}
      </div>

      <div style={{ display: 'flex' }}>
        <div style={{
          flex: 2,
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fill, minmax(150px, 1fr))',
          gap: '2rem',
          alignItems: 'start',
          padding: '1rem'
        }}>
          {items.filter(item => selectedCategory === 'すべて' || item.category_name === selectedCategory).map((item) => (
            <div
              key={item.item_id}
              onClick={() => item.stock_quantity > 0 && handleSelect(item)}
              style={{
                border: '1px solid #ccc',
                padding: '10px',
                width: '150px',
                height: '150px',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'space-between',
                cursor: item.stock_quantity > 0 ? 'pointer' : 'not-allowed',
                backgroundColor: item.stock_quantity === 0 ? '#eee' : '#fff',
                opacity: item.stock_quantity === 0 ? 0.5 : 1,
              }}
            >
              <h3>{item.name}</h3>
              <p>{item.stock_quantity > 0 ? `在庫あり (${item.stock_quantity})` : '在庫切れ'}</p>
              <p>¥{item.item_price.toLocaleString()}</p>
            </div>
          ))}
        </div>

        <div style={{
          flex: 1,
          borderLeft: '2px solid #ccc',
          padding: '1rem',
          backgroundColor: '#f9f9f9',
          minWidth: '250px'
        }}>
          <h2>🛒 カート</h2>
          {selectedItems.length === 0 ? (
            <p>商品を選んでください</p>
          ) : (
            <>
              <ul>
                {selectedItems.map(item => (
                  <li key={item.item_id} style={{ marginBottom: '10px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                      <strong>{item.name}</strong>
                      <span>¥{(item.item_price * item.quantity).toLocaleString()}</span>
                    </div>
                    <div>
                      <button onClick={() => updateQuantity(item.item_id, -1)}>-</button>
                      <span style={{ margin: '0 8px' }}>{item.quantity}</span>
                      <button onClick={() => updateQuantity(item.item_id, 1)} disabled={item.quantity >= item.stock_quantity}>+</button>
                    </div>
                  </li>
                ))}
              </ul>
              <hr />
              <h3>合計金額: ¥{totalPrice.toLocaleString()}</h3>

              <div style={{ marginTop: '1rem' }}>
                <button onClick={() => setConfirming(true)}>購入確定</button>

                {confirming && (
                  <div style={{ marginTop: '0.5rem' }}>
                    <p>本当に購入しますか？</p>
                    <button onClick={handlePurchaseConfirm}>はい</button>
                    <button onClick={() => setConfirming(false)}>いいえ</button>
                  </div>
                )}
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
