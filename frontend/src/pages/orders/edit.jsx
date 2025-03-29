import { useContext, useEffect, useState } from "react";
import { AppContext } from "../../context/context";
import { Link, useNavigate, useParams } from "react-router-dom";
import { ucwords, calculateTotal } from "../utils";


export default function UpdateOrder() {
    const navigate = useNavigate();
    const { id } = useParams();
    const { token, user, products } = useContext(AppContext);
    
    const [ errors, setErrors ]     = useState({});
    const [ formData, setFormData ] = useState({
        name: "",
        description: "",
        status: "",
        date: "",
        purchasedProducts: {},
        stock: products?.reduce((acc, product) => {
            acc[product.id] = product.attributes.stock; 
            return acc;
        }, {})
    });

    async function getOrder() {
        const res = await fetch(`/api/v1/orders/${id}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });

        const data = await res.json();

        if (data.errors) {
            setErrors(data.errors);
        }


        if (res.ok) {
            setFormData({
                name: data.attributes.name,
                description: data.attributes.description,
                status: data.attributes.status,
                date: data.attributes.date,
                purchasedProducts: Object.values(data?.relationships?.products || {}).reduce((acc, product) => {
                    acc[product.id] = {
                        id: product.id,
                        name: product.name,
                        quantity: product.pivot.quantity,
                        price: product.price,
                    };
                    return acc;
                }, {}),
                stock: products?.reduce((acc, product) => {
                    acc[product.id] = product.attributes.stock;
                    return acc;
                }, {})
            });
        }
    }

    useEffect(() => {
        getOrder();
    }, []);
    

    const handleAddProduct = (product) => {
        setFormData((prev) => {
            const updatedProducts = { ...prev.purchasedProducts };
            const updatedStock = { ...prev.stock };
            if (updatedStock[product.id] > 0) {
                if (updatedProducts[product.id]) {
                    updatedProducts[product.id].quantity += 1;
                } else {
                    updatedProducts[product.id] = { ...product, quantity: 1 };
                }
                updatedStock[product.id] -= 1;
            }
            return { ...prev, purchasedProducts: updatedProducts, stock: updatedStock };
        });
    };

    const handleQuantityChange = (productId, newQuantity) => {
        setFormData((prev) => {
            const updatedProducts = { ...prev.purchasedProducts };
            const updatedStock = { ...prev.stock };

            if (newQuantity > 0) {
                const diff = newQuantity - (updatedProducts[productId]?.quantity || 0);
                if (updatedStock[productId] - diff >= 0) {
                    updatedProducts[productId].quantity = newQuantity;
                    updatedStock[productId] -= diff;
                }
            } else {
                updatedStock[productId] += updatedProducts[productId]?.quantity || 0;
                delete updatedProducts[productId];
            }
            return { ...prev, purchasedProducts: updatedProducts, stock: updatedStock };
        });
    };

    const calcDataSet = () => {
        return {
            data: {
                attributes: {
                    user_id: user.id,
                    name: formData.name,
                    description: formData.description,
                    status: formData.status,
                    date: formData.date,
                },
                relationships: {
                    products: Object.values(formData.purchasedProducts).map((product) => ({
                        id: product.id,
                        quantity: product.quantity,
                        price: product.price,
                    })),
                }
            }
        };
    }

    async function handleUpdateOrder(e) {
        e.preventDefault();
        if (!window.confirm("Are you sure you want to update this order?")) {
            return;
        }

        const res = await fetch(`/api/v1/orders/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
            body: JSON.stringify(calcDataSet()),
        });

        const data = await res.json();
        
        if (res.ok) {
            navigate(`/orders/${id}`);
        } else {
            setErrors(data.errors);
        }
    };


    return (
        <>
            <div className="flex gap-8">

                {/* Form Section */}
                <div className="w-1/2">
                    <h1 className="title">Update your Order</h1>
                    <form onSubmit={handleUpdateOrder} className="space-y-5">
                        <div>
                            <label htmlFor="name">Name</label>
                            <input type="text" id="name" value={formData.name} onChange={(e) => setFormData({ ...formData, name: e.target.value })} required />
                        </div>
                        <div>
                            <label htmlFor="description">Description</label>
                            <textarea id="description" value={formData.description} onChange={(e) => setFormData({ ...formData, description: e.target.value })} required />
                        </div>
                        <div>
                            <label htmlFor="status">Status</label>
                            <select id="status" value={formData.status} onChange={(e) => setFormData({ ...formData, status: e.target.value })} required>
                                <option value="">Select a status</option>
                                <option value="P">Pending</option>
                                <option value="F">Fulfilled</option>
                                <option value="C">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label htmlFor="date">Date</label>
                            <input type="date" id="date" value={formData.date} onChange={(e) => setFormData({ ...formData, date: e.target.value })} required />
                        </div>

                            <h3 className="text-lg font-bold mt-6">Added Products</h3>
                            {/* Added Products Section */}
                            {Object.values(formData.purchasedProducts).length > 0 ? (
                                <div key={id} className="space-y-3">
                                    {Object.values(formData.purchasedProducts).map((product) => (
                                        <>
                                            <div key={product.id} className="flex items-left justify-between border p-2 rounded">
                                                <span className="flex text-left">€{product.price}</span>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    value={product.quantity}
                                                    onChange={(e) => handleQuantityChange(product.id, parseInt(e.target.value) || 0)}
                                                    className="w-16 text-left border rounded"
                                                />
                                                <span>{product.name}</span>
                                                <button onClick={() => handleQuantityChange(product.id, 0)} className="btn btn-sm btn-danger">Remove</button>
                                            </div>
                                        </>
                                    ))}
                                    <div className="text-left font-bold">
                                        Total: €{calculateTotal(formData.purchasedProducts).toFixed(2)}
                                    </div>
                                </div>
                            ) : (
                                <p>No products added.</p>
                            )}

                        <div className="flex gap-2 justify-between items-center mb-4 space-x-2">
                            <Link to={`/orders/${id}`} className="text-red-500 flex items-center space-x-1">Cancel</Link>
                            <button type="submit" className="btn primary-btn">Update Order</button>
                        </div>

                        {Object.keys(errors).length > 0 && (
                            <div className="mt-4 text-red-500">
                                {Object.keys(errors).map((key) => (
                                    <div key={key}>
                                        {errors[key].map((error, index) => (
                                            <p key={index}>{error}</p>
                                        ))}
                                    </div>
                                ))}
                            </div>
                        )}
                    </form>
                </div>

                {/* Products Section */}
                <div className="w-1/2">
                    <h1 className="title text-lg font-bold">Select Products</h1>

                    <div className="grid grid-cols-3 gap-4">
                        {products?.map((product) => (
                            <div key={product.id} className="border p-3 rounded-lg flex flex-col justify-between">
                                <div>
                                    <h4>{ucwords(product.attributes.name)}</h4>
                                    <p>Stock: {formData.stock[product.id]}</p>
                                </div>
                                <button onClick={() => handleAddProduct({
                                    id: product.id,
                                    name: product.attributes.name,
                                    price: product.attributes.price,
                                    quantity: 1
                                })} className="btn btn-sm mt-2 primary-btn flex items-center justify-center" disabled={formData.stock[product.id] <= 0}>
                                    <span className="material-icons">Buy &nbsp; {product.attributes.price} €</span>
                                </button>
                            </div>
                        ))}
                    </div>
                </div>

            </div>
        </>
    );
}
