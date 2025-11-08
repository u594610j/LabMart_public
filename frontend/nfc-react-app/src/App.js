import { BrowserRouter as Router, Routes, Route, useNavigate } from "react-router-dom";
import { useLocation } from "react-router-dom"; 
import LoginPage from './LoginPage';
import PurchasePage from './PurchasePage';
import HistoryPage from './HistoryPage';
import CompletePage from "./CompletePage";

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<LoginPage />} />
        <Route path="/purchase" element={<PurchasePage />} />
        <Route path="/history" element={<HistoryPage />} />
        <Route path="/complete" element={<CompletePage />} /> 
      </Routes>
    </Router>
  );
}

export default App;