import { useContext, useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { AppContext } from "../../context/context";
import { Link } from 'react-router-dom';
import { formatDate, getStatusText, ucwords } from "../utils";

export default function ShowOrder() {
    const navigate = useNavigate();
    const { id } = useParams();
    const { token, user } = useContext(AppContext);
    const [ order, setOrder ] = useState(null);
    const [ orderOwner, setOrderOwner ] = useState(null);

    async function getOrder() {
        const res = await fetch(`/api/v1/orders/${id}?include=user`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });

        const data = await res.json();
        console.log(data);

        if (data.errors) {
            console.log(data.errors);
        }
        
        if (res.ok) {
            if ( data.includes.owner.data.id !== user.id ) {
                navigate('/orders');
            }
            setOrder(data);
            setOrderOwner(data.includes.owner.data);
        }
    }

    useEffect(() => {
        getOrder();
    }, []);
    
    async function handleDelete(e) {
        e.preventDefault();
        if (!window.confirm('Are you sure you want to delete this order?')) {
            return;
        }
        const res = await fetch(`/api/v1/orders/${e.target.id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
            }
        });
        
        const data = await res.json();

        if (res.ok) {
            navigate('/orders');
        }
    }

    return (
        <>
            <h1 className="font-bold text-4xl my-5">Order Details</h1>
            {order ? (
                <div className="border border-spacing-4 border-gray-200 rounded-lg p-4">
                    <div key={order.id} className="card w-full mb-8">
                        {/* Flex container for the title and buttons */}
                        <div className="flex justify-between items-center mb-4">
                            {/* Order Name */}
                            <h1 className="card-title font-bold text-3xl">{ucwords(order.attributes.name)}</h1>
                    
                            {/* Action Buttons */}
                            <div className="flex space-x-2">
                                {/* Edit Button */}
                                <Link to={`/orders/${order.id}/edit`} className="nav-link primary-btn flex items-center space-x-1">Edit</Link>
                    
                                {/* Delete Button */}
                                <form onSubmit={handleDelete} id={order.id} className="inline">
                                    <button type="submit" className="nav-link primary-btn flex items-center space-x-1">Delete</button>
                                </form>
                            </div>
                        </div>
                    
                        {/* Flex container for the 2/3 and 1/3 layout */}
                        <div className="flex">
                            {/* Left Section (2/3 of the page) */}
                            <div className="w-2/3 pr-4">
                                {/* Image and Description */}
                                <div className="flex">
                                    {/* Placeholder Image */}
                                    <div className="w-1/3">
                                        <img
                                            src={`https://source.unsplash.com/random/300x300?${order.id}`}
                                            alt="Order Image"
                                            className="w-full h-auto rounded-lg"
                                        />
                                    </div>
                    
                                    {/* Order Description */}
                                    <div className="w-2/3 pl-4">
                                        <p className="card-text">{getStatusText(order.attributes.description)}</p>
                                    </div>
                                </div>
                    
                                {/* Additional Details */}
                                <div className="mt-4">
                                    {/* Status Badge */}
                                    <p className="card-text">
                                        <span
                                            className={`inline-block px-3 py-1 rounded-full text-sm font-semibold ${
                                                order.attributes.status === "C"
                                                    ? "bg-red-100 text-red-800" // Red for canceled
                                                    : order.attributes.status === "F"
                                                    ? "bg-green-100 text-green-800" // Green for fulfilled
                                                    : "bg-yellow-100 text-yellow-800" // Yellow for pending
                                            }`}
                                            >
                                            {getStatusText(order.attributes.status)}
                                        </span>
                                    </p>
                                    <p className="card-text mt-7">
                                        Created by: <strong>{orderOwner.attributes.name}</strong> 
                                        <br /> on date <span className="text-gray-600">{formatDate(order.attributes.date)}</span>
                                    </p>
                                </div>
                            </div>
                    
                            {/* Right Section (1/3 of the page) */}
                            <div className="w-1/3">
                                {/* Product Details Table */}
                                <table className="table-auto w-full">
                                    <thead>
                                        <tr>
                                            <th className="px-4 py-2">Product</th>
                                            <th className="px-4 py-2">Price</th>
                                            <th className="px-4 py-2">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {order.relationships.products.map(product => (
                                            <tr key={product.id}>
                                                <td className="border px-4 py-2">{product.name}</td>
                                                <td className="border px-4 py-2">€{product.price}</td>
                                                <td className="border px-4 py-2">{product.pivot.quantity}</td>
                                            </tr>
                                        ))}
                                        <tr>
                                            <td className="border px-4 py-2 font-bold">Total</td>
                                            <td className="border px-4 py-2 font-bold">€{order.relationships.products.reduce((total, product) => total + product.price * product.pivot.quantity, 0).toFixed(2)}</td>
                                            <td className="border px-4 py-2"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            ): (
                <h1 className="font-bold text-2xl">No order found!</h1>
            )}
        </>
    )
}