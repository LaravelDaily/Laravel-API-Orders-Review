import { useContext, useState } from "react";
import { AppContext } from "../../context/context";
import { useNavigate } from "react-router-dom";
import { ucwords, calculateTotalCreateOrder } from "../utils";

export default function CreateOrder() {
    const navigate = useNavigate();
    const { token, user, products } = useContext(AppContext);
    
    const [ errors, setErrors ]     = useState({});
    const [ formData, setFormData ] = useState({
        name: "",
        description: "",
        status: "",
        date: "",
        purchasedProducts: {},
        stock: products.reduce((acc, product) => {
            acc[product.id] = product.attributes.stock; 
            return acc;
        }, {})
    });

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

    async function handleCreateOrder(e) {
        e.preventDefault();

        const dataSet = {
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
                        price: product.attributes.price,
                    })),
                }
            }
        }

        const res = await fetch('/api/v1/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
            body: JSON.stringify(dataSet),
        });

        const data = await res.json();
        
        if (res.ok) {
            navigate(`/orders/${data.id}`);
        } else {
            setErrors(data.errors);
        }
    };

    return (
        <>
            <div className="flex gap-8">

                {/* Form Section */}
                <div className="w-1/2">
                    <h1 className="title">Make an Order</h1>
                    <form onSubmit={handleCreateOrder} className="space-y-5">
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

                            {/* Added Products Section */}
                            <h3 className="text-lg font-bold mt-6">Added Products</h3>
                            {Object.values(formData.purchasedProducts).length > 0 ? (
                                <div className="space-y-3">
                                    {Object.values(formData.purchasedProducts).map((product) => (
                                        <>
                                            <div key={product.id} className="flex items-center justify-between border p-2 rounded">
                                                <span className="flex text-left">€{product.attributes.price}</span>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    value={product.quantity}
                                                    onChange={(e) => handleQuantityChange(product.id, parseInt(e.target.value) || 0)}
                                                    className="flex w-16 text-center border rounded"
                                                />
                                                <span> &nbsp; {product.attributes.name}</span>
                                                <button onClick={() => handleQuantityChange(product.id, 0)} className="btn btn-sm btn-danger">Remove</button>
                                            </div>
                                        </>
                                    ))}
                                    <div className="text-left font-bold">
                                        Total: €{calculateTotalCreateOrder(formData.purchasedProducts).toFixed(2)}
                                    </div>
                                </div>
                            ) : (
                                <p>No products added.</p>
                            )}

                        <button type="submit" className="btn primary-btn">Create Order</button>

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
                        {products.map((product) => (
                            <div key={product.id} className="border p-3 rounded-lg flex flex-col justify-between">
                                <div>
                                    <h4>{ucwords(product.attributes.name)}</h4>
                                    <p>Stock: {formData.stock[product.id]}</p>
                                </div>
                                <button onClick={() => handleAddProduct(product)} className="btn btn-sm mt-2 primary-btn flex items-center justify-center" disabled={formData.stock[product.id] <= 0}>
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
