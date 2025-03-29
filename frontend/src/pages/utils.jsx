export function ucwords(str) {
    return str.replace(/\b\w/g, char => char.toUpperCase());
};

export function formatDate(dateStr) {
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
};

export function getStatusText(status) {
    switch (status) {
        case 'F':
            return 'Fulfilled';
        case 'C':
            return 'Cancelled';
        case 'P':
            return 'Pending';
        default:
            return status;
    }
};

export function calculateTotal(products) {
    return Object.values(products).reduce((total, product) => {
        return total + product.price * product.quantity;
    }, 0);
};

export function calculateTotalCreateOrder(products) {
    return Object.values(products).reduce((total, product) => {
        return total + product.attributes.price * product.quantity;
    }, 0);
};