# 🛍️ Laravel Shopify API

Create **Shopify products with variations and images** using Shopify’s **Admin GraphQL API (2025-07)**.  
This project is built with **Laravel 12** and follows the **Repository Pattern**, ensuring clean, maintainable, and testable code.

---

## Table of Contents

- [Overview](#overview)
  - [Features](#features)
  - [Requirements](#requirements)
  - [🏗️ Folder Structure](#🏗️-folder-structure)
- [Setup Instructions](#setup-instructions)
    - [1️⃣ Clone the Repository](#1️⃣-clone-the-repository)
    - [2️⃣ Install Dependencies](#2️⃣-install-dependencies)
- [🔑 How to Get Shopify API Access Token](#🔑-how-to-get-shopify-api-access-token)
    - [Prerequisites](#prerequisites)
    - [Steps to Get Shopify API Access Token](#steps-to-get-shopify-api-access-token)
    - [Notes](#notes)
- [🚦 API Endpoint](#🚦-api-endpoint)
    - [Headers](#headers)
    - [Example JSON Body](#example-json-body)
    - [Success Response](#success-response)
- [📬 Postman Setup](#📬-postman-setup)
- [🧪 Testing](#🧪-testing)
- [🧰 Optional Enhancements](#🧰-optional-enhancements)
- [🧑‍💻 Author](#🧑‍💻-author)
- [📝 License](#📝-license)

---

## 📘 Overview

This RESTful API lets you create products in your Shopify store directly from Laravel.  
It integrates with Shopify’s Admin GraphQL API and supports:
- Variants (e.g., Small, Medium, Large)
- Product Images
- Full validation
- Error handling
- Repository pattern for code abstraction

You’ll use **Postman** (or any HTTP client) to send JSON data with the required Shopify credentials in the request headers.

### 🧩 Features

✅ Create products with variants & images  
✅ Clean architecture using Repository pattern  
✅ Laravel FormRequest for validation  
✅ Handles Shopify authentication via headers  
✅ Proper error handling (Guzzle + Shopify)  
✅ Unit-tested endpoint  
✅ PSR-12 compliant

### ⚙️ Requirements

| Requirement | Version       |
|--------------|---------------|
| PHP | 8.2 or higher |
| Laravel | 12.x          |
| Composer | 2.x           |
| Guzzle | ^7.10         |
| Shopify Admin API | 2025-07       |
| Shopify Partner Account | Required      |

### 🏗️ Folder Structure

```swift
app/
 ├── Http/
 │   ├── Controllers/Api/ShopifyProductController.php
 │   ├── Requests/CreateShopifyProductRequest.php
 ├── Providers/
 │   ├── AppServiceProvider.php
 │   ├── ShopifyServiceProvider.php
 ├── Repositories/
 │   ├── Contracts/ShopifyProductRepositoryInterface.php
 │   ├── Eloquents/Products/ShopifyProductRepository.php
 ├── Services/ShopifyApiService.php
bootstrap
 ├── cache/
 ├── app.php
 ├── providers.php
tests/
 ├── Feature/
 │   ├── ProductCreateApiTest.php
```

---

## 🚀 Setup Instructions

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/MahadiHossain28/laravel-shopify-api.git
cd shopify-product-api
```

### 2️⃣ Install Dependencies
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan serve
```

### 3️⃣ System Live On This link
```ruby
http://127.0.0.1:8000
```

---

## 🔑 How to Get Shopify API Access Token
This guide explains how to generate a Shopify Admin API Access Token using your Shopify Partner account.

### Prerequisites

- A [Shopify Partner Account](https://www.shopify.com/partners)
- An existing Shopify store (or create a new one through the Partner dashboard)

### Steps to Get Shopify API Access Token

1. **Log in** to your [Shopify Partner Account](https://www.shopify.com/partners).
    - If you don’t have an account, sign up for a free Partner Account.

2. Go to **Stores** in the left-hand panel.

3. Add a store by clicking **Add Store**, or log in from your existing list of stores.

4. Navigate to **Settings** at the bottom left of the panel.

5. Select **Apps and Sales Channels**.

6. Click **Develop Apps**.

7. Click **Create an App**.

8. Enter an **App Name**, then click **Create App**. You will now see the **Overview** of your app.

9. Go to the **Configuration** tab and click **Configure** under **Admin API Integration**.

10. Select the necessary Admin API access scopes. Ensure **Webhook Subscriptions** is set to **2025-07**, or at a minimum include:

    ```ruby
    write_products
    read_products
    write_locations
    read_locations
    write_inventory
    read_inventory
    ```  

11. Click **Save**.

12. Go to the **API Credentials** tab. Click **Install App** to generate your Admin API Access Token.

13. Click **View**, then **Copy** the token. **Important:** This token is only visible once. Store it securely.

14. Use the token as `X-Shopify-Access-Token` in **Postman** or any HTTP client to access Shopify APIs.

### Notes

- Keep your API token private; do not share it publicly.
- The token provides full access to the selected API scopes.
- If you lose the token, you must generate a new one by reinstalling the app.

---

## 🚦 API Endpoint

### Headers

| Key                    | Value                   |
| ---------------------- | ----------------------- |
| Accept                 | application/json        |
| Content-Type           | application/json        |
| X-Shopify-Access-Token | your_admin_api_token    |
| X-Shopify-Shop-Domain  | yourstore.myshopify.com |

### Example JSON Body
```json
{
    "title": "Cotton T-Shirt Premium",
    "description": "<p>High quality premium cotton t-shirt</p>",
    "vendor": "My Brand",
    "product_type": "Apparel",
    "status": "active",
    "options": [
        { "name": "Size", "values": ["Small", "Medium", "Large"] },
        { "name": "Color", "values": ["Red", "Blue"] }
    ],
    "variants": [
        { "options": { "Size": "Small", "Color": "Red" }, "price":
        19.99, "sku": "TSHIRT-SM-RED", "inventory_quantity": 100 },
        { "options": { "Size": "Small", "Color": "Blue" }, "price":
        19.99, "sku": "TSHIRT-SM-BLUE", "inventory_quantity": 50 },
        { "options": { "Size": "Medium", "Color": "Red" }, "price":
        21.99, "sku": "TSHIRT-MD-RED", "inventory_quantity": 75 },
        { "options": { "Size": "Medium", "Color": "Blue" },
            "price": 21.99, "sku": "TSHIRT-MD-BLUE", "inventory_quantity":
        60 },
        { "options": { "Size": "Large", "Color": "Red" }, "price":
        23.99, "sku": "TSHIRT-LG-RED", "inventory_quantity": 40 },
        { "options": { "Size": "Large", "Color": "Blue" }, "price":
        23.99, "sku": "TSHIRT-LG-BLUE", "inventory_quantity": 30 }
    ],
    "images": [
        { 
            "src": "https://cdn.shopify.com/s/files/1/0533/2089/files/placeholder-images-image_large.png", 
            "alt": "T-Shirt"
        }
    ]
}
```

### Success Response
```json
{
  "success": true,
  "message": "Product created successfully on Shopify.",
  "product": {
    "id": "gid://shopify/Product/123456789",
    "title": "T-Shirt"
  }
}
```
---

## 📬 Postman Setup
1. Open Postman

2. Create a POST request to:
    ```ruby
    http://127.0.0.1:8000/api/v1/shopify/products
    ```
3. Add Headers:

    - Accept: application/json

    - Content-Type: application/json

    - X-Shopify-Access-Token: your_admin_api_token

    - X-Shopify-Shop-Domain: yourstore.myshopify.com

4. Paste JSON body (see example above)

5. Send request

6. You should receive a ```201 Created``` response with success message

---

## 🧪 Testing

A sample PHPUnit test is included in ```tests/Feature/ProductCreateApiTest.php```.

Run all tests:

```bash
php artisan test
```

---

## 🧰 Optional Enhancements

- 🔁 Add retry logic for Shopify rate limits

- 🪵 Log API calls in storage/logs/shopify.log

- 🧾 Store created product IDs locally

- 🔒 Implement request throttling for security

---

## 🧑‍💻 Author

### MD Mahadi Hossain

📧 **mahadihossain28@gmail.com**

[Linkedin](https://www.linkedin.com/in/mahadi-hossain/) | [GitHub](https://github.com/MahadiHossain28/)

---

## 📝 License

This project is open-sourced software licensed under the MIT license.

