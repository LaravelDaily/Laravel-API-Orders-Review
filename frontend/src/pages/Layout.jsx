import { useContext } from 'react';
import { Link, Outlet, useNavigate } from 'react-router-dom';
import { AppContext } from '../context/context';

export default function Layout() {
    const { user, token, setUser, setToken } = useContext(AppContext);
    const navigate = useNavigate();

    async function handleLogout(e) {
        e.preventDefault();
        const res = await fetch('/api/logout', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
            }
        });
        
        const data = await res.json();
        
        if (res.ok) {
            setToken(null);
            setUser(null);
            localStorage.removeItem('token');
            navigate('/');
        }
    }

    return (
        <>
            <header>
                <nav>
                    {
                        user ? (
                            <>
                                <div className='nav-left space-x-4'>
                                    <Link to="/" className='nav-link'>Homepage</Link>
                                    <Link to="/orders" className='nav-link'>Orders</Link>
                                    <Link to="/orders/create" className='nav-link'>New Order</Link>
                                </div>
                                <div className='nav-right flex items-center space-x-4'>
                                    <div className='text-slate-400 text-xs'>{user.name}</div>
                                    <form onSubmit={handleLogout}>
                                        <button type='submit' className='nav-link'>Logout</button>
                                    </form>
                                </div>
                            </>
                        ) : (
                            <div className='nav-right space-x-4'>
                                <Link to="/login" className='nav-link'>Login</Link>
                            </div>
                        )
                    }
                </nav>
            </header>

            <main>
                <Outlet/>
            </main>
        </>
    );
}