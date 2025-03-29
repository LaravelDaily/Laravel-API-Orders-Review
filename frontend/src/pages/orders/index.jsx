import { useContext, useEffect, useState } from 'react';
import { AppContext } from '../../context/context';
import { Link } from 'react-router-dom';
import { ucwords, formatDate, getStatusText } from "../utils";

export default function Orders () {
    const { token, user } = useContext(AppContext);
    const [ orders, setOrders ] = useState([]);

    // filters 
    const [ filterOrder, setFilterOrder ] = useState('');
    const [ filterProducts, setFilterProducts ] = useState('');
    const [ filterStatus, setFilterStatus ] = useState('all');
    const [ filterDate, setFilterDate ] = useState('');
    const [ filterDateTo, setFilterDateTo ] = useState('');
    const [ filterSortBy, setFilterSortBy ] = useState('');

    const restoreFilters = () => {
        setFilterOrder('');
        setFilterProducts('');
        setFilterStatus('all');
        setFilterDate('');
        setFilterDateTo('');
        setFilterSortBy('');
        document.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
        document.querySelectorAll('input[type="date"]').forEach(input => input.value = '');
        document.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    };

    async function getOrders() {
        const res = await fetch(`/api/v1/owners/${user.id}/orders?include=user&sortByDesc=id`, {
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
            setOrders(data.data);
        }
    }

    useEffect(() => {
        getOrders();
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
            getOrders();
        }
    }

    return (
      <>
        <h1 className="title">Your Orders</h1>
        <div className="filters border border-spacing-4 border-gray-200 rounded-lg p-4">
            <div className="flex justify-between items-center mb-4">
                <h2 className="text-lg font-bold">Filters</h2>
                <button onClick={ restoreFilters } className="btn btn-primary">Reset</button>
            </div>
            <div className="flex">
                <div className="flex flex-col gap-2">
                    <label className="label">
                        <span className="label-text">Search Order name/description</span>
                    </label>
                    <input 
                        type="text" 
                        placeholder="Search by product name or description" 
                        onChange={ (e) => setFilterOrder(e.target.value) } 
                        className="input input-bordered w-full max-w-xs mb-4"
                    />
                </div>
                <div className="flex flex-col gap-2 ml-4">
                    <label className="label">
                        <span className="label-text">Search Products</span>
                    </label>
                    <input 
                        type="text" 
                        placeholder="Search by product name or description" 
                        onChange={ (e) => setFilterProducts(e.target.value) } 
                        className="input input-bordered w-full max-w-xs mb-4"
                    />
                </div>
                <div className="flex flex-col gap-2 ml-4 flex-grow">
                    <label className="label">
                        <span className="label-text">Filter by status</span>
                    </label>
                    <select
                        onChange={ (e) => setFilterStatus(e.target.value) } 
                        className="select select-bordered w-full max-w-xs">
                            <option value="all">All</option>
                            <option value="F">Fulfilled</option>
                            <option value="C">Cancelled</option>
                            <option value="P">Pending</option>
                    </select>
                </div>
            </div>

            <div className='flex'>
                <div className="flex flex-col gap-2 ml-4">
                    <label className="label">
                        <span className="label-text">Filter by date</span>
                    </label>
                    <input
                        onChange={ (e) => setFilterDate(e.target.value) }
                        type="date"
                        className="input input-bordered w-full max-w-xs"
                    />
                </div>
                <div className="flex flex-col gap-2 ml-4">
                    <label className="label">
                        <span className="label-text">To date</span>
                    </label>
                    <input
                        onChange={ (e) => setFilterDateTo(e.target.value) }
                        type="date"
                        className="input input-bordered w-full max-w-xs"
                    />
                </div>
                <div className="flex flex-col gap-2 ml-4">
                    <button 
                        onClick={() => {
                            setFilterDate('');
                            setFilterDateTo('');
                            document.querySelectorAll('input[type="date"]').forEach(input => input.value = '');
                        }} 
                        className="btn primary-btn mt-2"
                        >
                        Reset Dates
                    </button>
                </div>

                <div className="flex flex-col gap-2 ml-4 float-end">
                    <label className="label">
                        <span className="label-text">Sort By</span>
                    </label>
                    <select
                        onChange={ (e) => setFilterSortBy(e.target.value) } 
                        className="select select-bordered w-full max-w-xs">
                            <option value="">No sorting</option>
                            <option value="name">Name ASC</option>
                            <option value="-name">Name DESC</option>
                            <option value="description">Description ASC</option>
                            <option value="-description">Description DESC</option>
                            <option value="status">Status ASC</option>
                            <option value="-status">Status DESC</option>
                            <option value="date">Date ASC</option>
                            <option value="-date">Date DESC</option>
                    </select>
                </div>
            </div>

        </div>

        <hr className='mb-8' />

        <div className="border border-spacing-4 border-gray-200 rounded-lg p-4">
            {orders.filter((order) => {
                const order_data = order.attributes;

                const productFound = filterProducts.trim() !== '' 
                    ? order.relationships.products.some(product => product.name.toLowerCase().includes(filterProducts.toLowerCase()))
                    : true;
                
                const orderFound = filterOrder.trim() !== ''
                    ? order_data.name.toLowerCase().includes(filterOrder.toLowerCase()) ||
                    order_data.description.toLowerCase().includes(filterOrder.toLowerCase())
                    : true;

                const orderStatusFound = filterStatus === 'all' || order_data.status.toLowerCase() === filterStatus.toLowerCase();

                const orderDateFound = filterDateTo.trim() === '' 
                        ? ( filterDate.trim() !== '' 
                            ? order_data.date === filterDate
                            : true )
                        : ( filterDate.trim() !== '' 
                            ? order_data.date >= filterDate && order_data.date <= filterDateTo
                            : order_data.date <= filterDateTo );
                
                return productFound && orderFound && orderStatusFound && orderDateFound;
            }).sort((a, b) => {
                switch (filterSortBy) {
                    case 'name':
                        return a.attributes.name.localeCompare(b.attributes.name);

                    case '-name':
                        return b.attributes.name.localeCompare(a.attributes.name);

                    case 'description':
                        return a.attributes.description.localeCompare(b.attributes.description);

                    case '-description':
                        return b.attributes.description.localeCompare(a.attributes.description);

                    case 'status':
                        return a.attributes.status.localeCompare(b.attributes.status);

                    case '-status':
                        return b.attributes.status.localeCompare(a.attributes.status);

                    case 'date':
                        return new Date(a.attributes.date) - new Date(b.attributes.date);

                    case '-date':
                        return new Date(b.attributes.date) - new Date(a.attributes.date);
                
                    default:
                        return 0;
                }
            }).map(order => (
                <div key={order.id} className="card w-full mb-8">
                {/* Flex container for the title and buttons */}
                <div className="flex justify-between items-center mb-4">
                    {/* Order Name */}
                    <h1 className="card-title font-bold text-3xl">{ucwords(order.attributes.name)}</h1>
            
                    {/* Action Buttons */}
                    <div className="flex space-x-2">
                        {/* Show Button */}
                        <Link to={`/orders/${order.id}`} className="nav-link primary-btn flex items-center space-x-1">Show</Link>
            
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
                                Created by: <strong>{order.includes.owner.data.attributes.name}</strong> 
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
            ))}
        </div>
      </>
    );
}