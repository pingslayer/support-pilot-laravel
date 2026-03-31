# Support Pilot 🚀

**Support Pilot** is a high-performance, enterprise-grade AI Customer Support Agent built as a multi-tenant SaaS. It leverages local RAG (Retrieval-Augmented Generation) and an autonomous reasoning loop to resolve customer issues, process refunds, and escalate complex queries to humans.

## 🗝️ Core Features

-   **Local RAG (PostgreSQL + pgvector)**: Secure, private, and extremely fast semantic search for company policies and FAQs. No data ever leaves your database for vector storage.
-   **Multi-tenant Architecture**: Strict data isolation using `TenantScope`. One SaaS instance can support thousands of independent companies securely.
-   **External Store Proxy**: Instead of duplicataing data, Support Pilot acts as a secure proxy to query live order details and process refunds in external tenant systems (Shopify, Custom Stores, etc.) via API.
-   **Autonomous Reasoning Loop**: Powered by the **Laravel AI SDK**, the agent intelligently chooses between searching the knowledge base, checking order status, or escalating to a human based on confidence scores.
-   **Real-time Notifications**: Automatic customer updates via email whenever the AI responds or escalates a ticket.
-   **SaaS Onboarding**: Built-in API endpoints for Tenant Registration and Login using **Laravel Sanctum**.

## 🛠️ Technology Stack

-   **Framework**: [Laravel 13.x](https://laravel.com)
-   **Database**: PostgreSQL with [pgvector](https://github.com/pgvector/pgvector)
-   **AI SDK**: [Laravel AI](https://laravel.com/docs/ai)
-   **Auth**: Laravel Sanctum (API Tokens)
-   **Queue**: Integrated Redis/Database queue for asynchronous message processing.

## 🚀 Getting Started

### 1. Prerequisites (PostgreSQL + pgvector)
Ensure you have PostgreSQL installed with the `pgvector` extension. On Fedora/Ubuntu:
```bash
# Ubuntu
sudo apt-get install postgresql-16-pgvector

# Fedora
sudo dnf install postgresql-pgvector
```

### 2. Installation
```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup & Seeding
Initialize the multi-tenant schema and create a default test tenant (`admin@test.com / password`):
```bash
php artisan migrate:fresh --seed
```

## 🧪 Simulation & Testing

To see the AI Support Agent in action, use the built-in simulation command. This will create a mock customer message, trigger the RAG search, query the mock external store, and generate a response:

```bash
php artisan app:simulate-support
```

**Monitor the reasoning logs:**
```bash
tail -f storage/logs/laravel.log
```

---
*Built with ❤️ for High-Performance AI SaaS.*
