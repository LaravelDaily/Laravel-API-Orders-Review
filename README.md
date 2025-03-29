# Order Management System

This **Order Management System** project is built using **Laravel**, and **ReactJS**. It allows you to manage orders, products, and stock levels.
<br>
Concurrent requests are handled by Database Transactions with Row Locking.
<br>
The API is versioned, current version is: V1.0.0. <br>
Order requests are managed by certain Stock Validation Rules for CreateOrder and UpdateOrder. <br>
Authorization is achieved by OrderPolicy. <br>
PUT: Request validated by ReplaceOrderRequest, <br>
PATCH: Request validated by UpdateOrderRequest, <br>
POST: Request validated by CreateOrderRequest, all inheriting from BaseOrderRequest.
<br>
GET: Orders are filtered by OrderFilter and QueryFilter. <br>
ex. using Postman or whatever: `/api/v1/orders?filter[name]=*ordername*&filter[status]=P,C`
<br>
This will filter the orders by name and status in Pending or Cancelled.
<br>
<br>
Response and payload is designed in the Resource/Collection of each model according to the API endpoints.

---

## Features

- **Order Management**: Create, read, update, and delete orders.
- **Product Management**: Manage products and track stock levels.
- **Stock Validation**: Prevent orders from exceeding available stock.
- **Authentication**: Secure API endpoints using Laravel Sanctum.
- **React Frontend**: A user-friendly interface for managing orders and viewing products.

---

## Available Endpoints

### Orders
- **GET|HEAD /api/v1/orders**: List all orders.
- **POST /api/v1/orders**: Create a new order.
- **GET|HEAD /api/v1/orders/{order}**: Get details of a specific order.
- **DELETE /api/v1/orders/{order}**: Delete an order.
- **PUT/PATCH /api/v1/orders/{order}**: Replace/Update an existing order.

### Products
- **GET|HEAD /api/v1/products**: List all products.

### Authentication
- **POST /api/login**: Authenticate a user and retrieve an access token.
- **POST /api/logout**: Revoke the user's access token.
- **GET|HEAD /api/user**: Retrieve the authenticated user's details.
- **GET|HEAD /api/v1/user**: Retrieve the authenticated user's details.

### API documentation
- **GET|HEAD /docs**: API documentation by Scribe.
- **GET|HEAD /docs.openapi**: OpenAPI documentation by Scribe (OpenAPI 3.0).
- **GET|HEAD /docs.postman**: OpenAPI documentation by Scribe (Postman).
- **GET|HEAD /api**: API entry point.

- **GET|HEAD /**: Web application entry point.
