import { useContext, useState } from "react";
import { useNavigate } from "react-router-dom";
import { AppContext } from "../../context/context";

export default function Login () {
    const { setToken } = useContext(AppContext);

    const navigate = useNavigate();
    
    const [ formData, setFormData ] = useState({
        email: '',
        password: ''
    });

    const [ errors, setErrors ] = useState({});

    async function handleLogin(e) {
        e.preventDefault();
        const res = await fetch('/api/login', {
            method: 'POST',
            body: JSON.stringify(formData)
        });

        const data = await res.json();
        if (data.errors) {
            setErrors(data.errors);
        } else {
            localStorage.setItem('token', data.data.token);
            setToken(data.data.token);
            navigate('/');
        }
    }

    return (
        <>
            <div>
                <h1 className="title">Login</h1>
            </div>

            <div className="form-container">
                <form onSubmit={handleLogin} className="form w-1/2 mx-auto space-y-5">
                    <div className="form-group">
                        <label htmlFor="email">Email</label>
                        <input type="email" id="email" name="email" 
                            value={formData.email} onChange={(e) => setFormData({...formData, email: e.target.value})} 
                        required />
                        <div className="error-message">
                            { errors.email && <p className="error">{errors.email}</p> }
                        </div>
                    </div>
                    <div className="form-group">
                        <label htmlFor="password">Password</label>
                        <input type="password" id="password" name="password" 
                            value={formData.password} onChange={(e) => setFormData({...formData, password: e.target.value})} 
                        required />
                        <div className="error-message">
                            { errors.password && <p className="error">{errors.password}</p> }
                        </div>

                    </div>
                    <button type="submit" className="btn primary-btn mt-4">Login</button>
                </form>
            </div>
        </>
    );
}