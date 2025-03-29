import { createContext, useEffect, useState } from "react";

export const AppContext = createContext();

export default function AppProvider({ children }) {
    const [token, setToken] = useState(localStorage.getItem('token'));
    const [user, setUser] = useState(null);
    const [products, setProducts] = useState(null);

    async function getUser() {
        const res = await fetch('/api/user', {
            headers: {
                 'Authorization': `Bearer ${token}`,
            },
        });

        const data = await res.json();

        if (data.errors) {
            console.log(data.errors);
        }
        
        if (res.ok) {
            setUser(data);
        }
    };

    useEffect( () => {
        getUser();
        getProducts();
    }
    , [token]);

    async function getProducts() {
        const res = await fetch('/api/v1/products', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        const data = await res.json();

        if (data.errors) {
            console.log(data.errors);
        }
        
        if (res.ok) {
            setProducts(data.data);
        }
    }

    return (
        <AppContext.Provider value={{ token, setToken, user, setUser, products, setProducts }}>
        {children}
        </AppContext.Provider>
    );
}