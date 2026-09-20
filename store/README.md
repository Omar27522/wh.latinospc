# 🛍️ NEXUS-7 Hardware Storefront (`/store`)

The **NEXUS-7 Storefront** is a high-performance, responsive e-commerce application designed specifically for selling certified refurbished, tested, and as-is computer hardware directly from warehouse inventory.

It bridges real physical warehouse stock from [`data/db/warehouse.db`](file:///c:/Users/Laptop/Documents/wh.latinospc/data/db/warehouse.db) into a streamlined consumer storefront, allowing warehouse staff to effortlessly curate, price, customize, and publish items with full control over photography and listing details.

---

## 🧭 Navigation & Directory Structure

```
store/
├── index.php                 # Main storefront catalog (guest browsing + inline admin editing)
├── category.php              # Category-filtered catalog (Laptops, Desktops, Servers, Parts)
├── cart.php                  # Session-backed shopping cart with inventory-backed checkout
├── admin_action.php          # Central REST/AJAX controller for CRUD & warehouse postings
├── terms.php                 # UCC-compliant legal terms of sale & warranty disclaimers
├── store.md                  # Design philosophy & scalable 5-phase roadmap
├── README.md                 # Primary overview and quickstart (this file)
│
├── core/                     # Business logic and data access layer
│   ├── db.php                # SQLite PDO database connection (WAL mode enabled)
│   ├── Inventory.php         # Product querying, warehouse posting, stock deduction
│   ├── StoreImageProcessor.php # Dual-tier WebP conversion and thumbnailing engine
│   ├── Cart.php              # Session-based cart storage and state management
│   └── UI.php                # Reusable UI component generators
│
├── views/                    # Reusable view partials
│   ├── header.php            # Global navigation, dark/light theme switch, cart badge
│   ├── footer.php            # Global footer with legal links and copyright
│   ├── product_card.php      # Modular card supporting guest view & inline admin editing
│   ├── add_item.php          # Manual item creation card with instant photo preview
│   └── warehouse_modal.php   # Slide-over modal for browsing and posting warehouse stock
│
├── assets/                   # Client-side presentation assets
│   └── css/
│       ├── store.css         # Design tokens, typography, dark/light theme variables
│       └── components.css    # Card styles, warehouse modal drawer, badges, buttons
│
├── images/                   # Storefront asset storage
│   ├── placeholder.svg       # Lightweight, zero-dependency SVG fallback image
│   └── store/                # Dedicated vault for store-uploaded WebP photography
│
└── docs/                     # Comprehensive engineering & AI handover documentation
    ├── ARCHITECTURE.md       # Deep architectural patterns, schema, and API contracts
    ├── AGENT_HANDOVER.md     # Essential "survival guide", 6 golden rules, and gotchas for AI agents
    ├── COMPONENTS_GUIDE.md   # Design tokens, view components, and interaction patterns
    ├── DATABASE_REFERENCE.md # Master SQLite schemas, PRAGMAs, query manual, and migrations
    ├── RECIPES_AND_EXTENSIONS.md # Cookbooks: Stripe, sales logs, condition grades, categories
    └── TESTING_AND_VERIFICATION.md # Headless CLI test suites, diagnostics, and pre-completion QA
```

---

## ⚡ Core Features

1. **Warehouse Inventory Integration**:
   - Access over 1,200+ authentic warehouse hardware items (Laptops, Desktops, Gaming, Servers, Parts) directly through the **Warehouse Stock** drawer modal.
   - Search warehouse inventory live with debounce filtering across brand, model, specs, and shelf location codes.
   - **Quick Post**: Instantly post an item by entering a retail price.
   - **Custom Post**: Fine-tune quantity allocations, public specs descriptions, category sector, and optionally upload a dedicated storefront photo.

2. **Self-Contained WebP Media Engine**:
   - All store photo uploads are processed locally by [`StoreImageProcessor.php`](file:///c:/Users/Laptop/Documents/wh.latinospc/store/core/StoreImageProcessor.php).
   - Generates dual-tier WebP assets: optimized full-resolution (`opt_...webp`, max 1200px) and square thumbnails (`thumb_...webp`, 250px).
   - Zero coupling to warehouse marketing directories; all store assets reside in [`images/store/`](file:///c:/Users/Laptop/Documents/wh.latinospc/store/images/store/).

3. **Inline Dual-Mode Catalog**:
   - **Guest View**: Clean, high-converting product cards with category badges, hardware specs, stock counters, and "Add to Cart" actions.
   - **Admin Edit View**: When logged in as warehouse staff, product cards transform into inline editing forms allowing real-time price updates, quantity changes, description editing, instant photo updates, and an "Unpost" button to retract items back to warehouse stock.

4. **Cart & Inventory-Safe Checkout**:
   - Shopping cart persists across sessions using [`Cart.php`](file:///c:/Users/Laptop/Documents/wh.latinospc/store/core/Cart.php).
   - Checkout simulates order processing and atomically decrements inventory stock in SQLite (`UPDATE inventory SET quantity = MAX(0, quantity - ?)`).

5. **Aesthetic Excellence & Theming**:
   - Built with Vanilla CSS and modern typography (Inter).
   - Dynamic dark/light theme with zero-flash rendering via inline localStorage checks in [`header.php`](file:///c:/Users/Laptop/Documents/wh.latinospc/store/views/header.php).
   - Robust `onerror` fallback mechanics preventing browser image fetch loops.

---

## 📚 Deep Dive Documentation Suite

For developers and AI agents continuing work on this codebase, refer to the documentation suite in `docs/`:

1. [**`docs/ARCHITECTURE.md`**](file:///c:/Users/Laptop/Documents/wh.latinospc/store/docs/ARCHITECTURE.md): Database schemas, single sources of truth, image resolution priority, and API specifications.
2. [**`docs/AGENT_HANDOVER.md`**](file:///c:/Users/Laptop/Documents/wh.latinospc/store/docs/AGENT_HANDOVER.md): The **AI Agent Survival Guide** covering the 6 Golden Rules, safety boundaries, and common pitfalls.
3. [**`docs/COMPONENTS_GUIDE.md`**](file:///c:/Users/Laptop/Documents/wh.latinospc/store/docs/COMPONENTS_GUIDE.md): Detailed reference for UI tokens, CSS variables, modal components, and client-side interactions.
4. [**`docs/DATABASE_REFERENCE.md`**](file:///c:/Users/Laptop/Documents/wh.latinospc/store/docs/DATABASE_REFERENCE.md): Complete SQLite schema breakdown, PRAGMA concurrency rules, index optimization, and query manual.
5. [**`docs/RECIPES_AND_EXTENSIONS.md`**](file:///c:/Users/Laptop/Documents/wh.latinospc/store/docs/RECIPES_AND_EXTENSIONS.md): Production blueprints for Stripe payments, order logging, condition grades, and category creation.
6. [**`docs/TESTING_AND_VERIFICATION.md`**](file:///c:/Users/Laptop/Documents/wh.latinospc/store/docs/TESTING_AND_VERIFICATION.md): Headless CLI testing scripts, image processor verification, and agent pre-completion checklist.

