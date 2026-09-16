# Bait El Kebash API Documentation

This guide is intended for the React.js frontend application.

## What this file is

This is a reference guide for connecting the website's React frontend to the
Laravel backend. It is not code that you need to run by itself.

An API is a set of URLs that the frontend calls to exchange data with the
backend. For example, the frontend can call `GET /products` to retrieve the
available sheep, or `POST /login` to sign a user in. The tables and examples
below describe which URLs exist, what data to send, and what responses to
expect.

If you are only working on the Laravel backend, you usually do not need to
edit this file. Update it when an endpoint, request field, response, or
authentication rule changes so the frontend developer has accurate
instructions.

## Base URL

```js
const API_URL = import.meta.env.VITE_API_URL || "http://localhost:8000/api";
```

All endpoints below are relative to `API_URL` and return JSON.

## Authentication

Authentication uses a Laravel Sanctum bearer token. After login, store the token securely and send it with every protected request:

```js
const headers = {
	Accept: "application/json",
	"Content-Type": "application/json",
	Authorization: `Bearer ${token}`,
};
```

For browser-based Sanctum authentication, first request `/sanctum/csrf-cookie`, then include credentials:

```js
await axios.get(`${API_URL.replace(/\/api$/, "")}/sanctum/csrf-cookie`, {
	withCredentials: true,
});
```

## Response format

Successful responses generally use:

```json
{
	"message": "Request successful",
	"data": {}
}
```

Validation errors (`422`):

```json
{
	"message": "The given data was invalid.",
	"errors": {
		"email": ["The email field is required."]
	}
}
```

Common status codes: `200` success, `201` created, `204` no content, `401` unauthenticated, `403` forbidden, `404` not found, `422` validation error, and `500` server error.

## Authentication endpoints

### Register

`POST /register`

Request:

```json
{
	"name": "Ahmed Ali",
	"email": "ahmed@example.com",
	"password": "secret123",
	"password_confirmation": "secret123"
}
```

### Login

`POST /login`

Request:

```json
{
	"email": "ahmed@example.com",
	"password": "secret123"
}
```

Response:

```json
{
	"message": "Login successful",
	"user": { "id": 1, "name": "Ahmed Ali", "email": "ahmed@example.com" },
	"token": "TOKEN"
}
```

### Current user

`GET /user` — protected

### Logout

`POST /logout` — protected

## Products / sheep

The product resource represents the sheep and other livestock listed by Bait El Kebash. Confirm the exact resource name with the backend if it differs.

| Method | Endpoint | Auth | Purpose |
|---|---|---:|---|
| GET | `/products` | No | List products |
| GET | `/products/{id}` | No | Show product details |
| POST | `/products` | Yes | Create a product |
| PUT/PATCH | `/products/{id}` | Yes | Update a product |
| DELETE | `/products/{id}` | Yes | Delete a product |

List query parameters: `page`, `per_page`, `search`, `category`, `min_price`, `max_price`, and `sort`.

Example product:

```json
{
	"id": 1,
	"name": "Premium Eid Sheep",
	"description": "Healthy livestock ready for delivery",
	"category": "sheep",
	"price": 3500,
	"image_url": "https://example.com/images/sheep.jpg",
	"available": true,
	"created_at": "2025-01-01T12:00:00Z"
}
```

## Orders

| Method | Endpoint | Auth | Purpose |
|---|---|---:|---|
| GET | `/orders` | Yes | List the current user's orders |
| POST | `/orders` | Yes | Create an order |
| GET | `/orders/{id}` | Yes | View an order |
| PATCH | `/orders/{id}/status` | Yes/Admin | Change order status |

Create-order request:

```json
{
	"items": [
		{ "product_id": 1, "quantity": 1 }
	],
	"customer_name": "Ahmed Ali",
	"phone": "+201000000000",
	"address": "Cairo, Egypt",
	"notes": "Delivery before noon"
}
```

## React API helper

```js
// src/api/client.js
import axios from "axios";

export const api = axios.create({
	baseURL: import.meta.env.VITE_API_URL || "http://localhost:8000/api",
	headers: { Accept: "application/json", "Content-Type": "application/json" },
});

api.interceptors.request.use((config) => {
	const token = localStorage.getItem("auth_token");
	if (token) config.headers.Authorization = `Bearer ${token}`;
	return config;
});

export const getProducts = (params) => api.get("/products", { params });
export const getProduct = (id) => api.get(`/products/${id}`);
export const createOrder = (payload) => api.post("/orders", payload);
```

## Frontend notes

- Use an environment variable (`VITE_API_URL`), not a hard-coded production URL.
- Display `errors[field]` beside the relevant form control.
- Handle `401` by clearing the token and redirecting to `/login`.
- Use `data.data` for resource payloads when the API wraps responses.
- Send `multipart/form-data` only when uploading images; otherwise send JSON.
- Never expose admin credentials or secret keys in React code.
