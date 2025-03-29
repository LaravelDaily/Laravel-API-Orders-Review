import  { BrowserRouter, Routes, Route } from 'react-router-dom'
import './App.css'
import Layout from './pages/Layout';
import Home from './pages/Home';
import Login from './pages/auth/login';
import { useContext } from 'react';
import { AppContext } from './context/context';
import Orders from './pages/orders/index';
import CreateOrder from './pages/orders/create';
import ShowOrder from './pages/orders/show';
import UpdateOrder from './pages/orders/edit';

export default function App() {
  const { user } = useContext(AppContext);
  

  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<Home />} />
          <Route path='/orders' element={ user ? <Orders /> : <Login /> } />
          <Route path='/orders/create' element={ user ? <CreateOrder /> : <Login /> } />
          <Route path='/login' element={ user ? <Home /> : <Login /> } />
          <Route path='/orders/:id' element={ user ? <ShowOrder /> : <Login /> } />
          <Route path='/orders/:id/edit' element={ user ? <UpdateOrder /> : <Login /> } />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}

