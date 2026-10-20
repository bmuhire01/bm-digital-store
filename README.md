# BM Digital Store

A small online shop built with **procedural PHP + MySQL (mysqli)**, running on XAMPP.

> **Brand:** BM Digital Store - technology, gadgets and accessories for everyday life.
> The name shows in the header, the page titles and the footer. Change it in one place:
> `$shop_name` in `includes/header.php`.

## What it does

- Browse products, filter by category, search by name
- Product page with stock info, add to cart
- Cart stored in the session: change quantity, remove items, empty cart
- Register / login / logout (passwords are hashed with `password_hash()`)
- Checkout that saves the order and its product lines to the database and takes the items out of stock
- Order history for the logged-in customer
- Customers can **cancel a pending order** - the items go back into the stock
- Admin area: add / edit / delete products, upload product pictures, change the status of an order
- Nobody can look at an order that is not theirs (see `order.php`)

## The admin panel

`http://localhost/project1/admin/` - login with `admin@shop.com` / `admin123`.

The admin side looks **nothing like the shop**. It is a real dashboard:

- A dark sidebar with the menu: Dashboard, Products, Orders, Customers, View Store, Logout
- **Dashboard** - total revenue, number of orders, pending orders, product count, a bar
  chart of the orders of the last 7 days, the best sellers, the newest orders and the
  products that are running low
- **Products** - search and filter by category, stock shown as a green/amber/red badge
- **Orders** - filter by status (with the count on every button), change the status inline
- **Customers** - every account with the number of orders and how much they have spent
- Add / edit / delete with picture upload

The admin has its own stylesheet (`assets/admin.css`) and its own layout
(`admin/includes/header.php`), so the shop and the panel never mix.

## The look

- Header with the **BM** logo mark, a thin top bar (free delivery / support) and the menu
- A hero banner on the home page with the store name and a few live numbers
- The categories are now clickable filter pills instead of plain links
- Product cards lift up a little when you hover over them
- A footer with the brand, quick links and the copyright

## The currency

Everything is in **RWF (Rwandan francs)**. One helper does all of it -
`price()` in `config/functions.php`:

```php
price(1250000);   // RWF 1,250,000
price(19.99);     // RWF 19.99   (the cents only show up when there are cents)
```

Use it everywhere instead of writing the currency yourself:

```php
<?php echo price($product['price']); ?>
```

If you ever want another currency, change the `RWF` inside that one function.

The colours live at the top of `assets/style.css` as CSS variables:

```css
:root {
    --dark:   #111827;   /* header, footer, dark text */
    --brand:  #f97316;   /* the orange of the logo and the buttons */
    --green:  #16a34a;   /* prices */
}
```

Change them there and the whole store follows.

## Folder structure

```
project1/
├── config/
│   ├── db.php           # database connection
│   └── functions.php    # helper functions (e, get_all, get_row, price, ...)
├── includes/
│   ├── header.php       # top of every page (menu, logo)
│   └── footer.php       # bottom of every page
├── assets/
│   ├── style.css           # the shop
│   ├── admin.css           # the admin panel (a completely different look)
│   └── uploads/            # product pictures (the ready-made ones + the uploaded ones)
├── admin/
│   ├── auth.php            # checks that the visitor is an admin
│   ├── index.php           # the dashboard: numbers, chart, best sellers
│   ├── products.php        # the products
│   ├── orders.php          # the orders
│   ├── customers.php       # the people who have an account
│   ├── add_product.php
│   ├── edit_product.php
│   ├── delete_product.php
│   ├── update_status.php
│   └── includes/
│       ├── header.php      # dark sidebar + top bar
│       └── footer.php
├── index.php             # home page / product list
├── product.php           # one product + add to cart
├── cart.php              # the cart
├── checkout.php          # delivery form + saving the order
├── cancel_order.php      # customer cancels a pending order
├── order.php             # one finished order
├── orders.php            # order history of the customer
├── login.php
├── register.php
├── logout.php
└── database.sql          # the database structure + sample data
```

## Setup

1. Copy the folder into `C:\xampp\htdocs\` (it already is).
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Create the database:
   - open <http://localhost/phpmyadmin>, Import -> choose `database.sql` -> Go
   - or run it from the terminal:
     ```
     C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\project1\database.sql
     ```
4. Open <http://localhost/project1/>

Database login is in `config/db.php`: user `root`, empty password (the XAMPP default).

## Test accounts

| Role    | Email            | Password  |
|---------|------------------|-----------|
| Admin   | admin@shop.com   | admin123  |
| Customer| jane@shop.com    | admin123  |

## The database

```
users        -> people who can log in
categories  -> product categories
products    -> the items for sale (name, price, stock, image)
orders      -> one row per checkout (name, address, total, status)
order_items -> the product lines inside one order
```

## Useful things to try

- Buy 3 laptops and look at `products.stock` afterwards - it goes down by 3.
- Buy something, then press **Cancel** in *My Orders* - the stock goes back up.
- Open `admin/index.php` as a guest - it sends you to the login page.
- Put `?id=1` in the URL of `product.php` and try `id=999` - you get "Product not found".
- Try to register with the email `admin@shop.com` - it tells you the email is taken.
- Upload a `.txt` file as a product picture in the admin - it is refused.
- Try to open `order.php?id=1` in a browser where you did not order anything - you are refused.

## How the pictures work

- All pictures live in `assets/uploads/`.
- `products.image` only holds the **file name** (for example `laptop.svg`).
- `product_image()` in `config/functions.php` builds the path and falls back to `placeholder.svg`
  when the file is missing.
- New uploads get a unique name (`product_<time>_<number>.<ext>`), so two products never
  overwrite each other.
- Only JPG, PNG, GIF, WEBP and SVG are allowed, and the file may not be bigger than 2 MB.
- When a product is deleted, its uploaded picture is removed - but only when no other
  product uses that picture.

## Putting it online later

The project is ready to be hosted. Nothing secret is in the repository.

**1. Copy the code to the server**

Upload the folder through FTP (FileZilla) or clone it from GitHub. Put it in the
`public_html` folder (that name is normal on shared hosting).

**2. Make the database**

Create a database and a database user in the hosting control panel (phpMyAdmin).
Import `database.sql` there, or run it in phpMyAdmin -> Import.

**3. Tell the code where the database is**

Copy `config/credentials.example.php` to `config/credentials.php` and fill in
the details the host gave you:

```php
<?php
$host = 'sql123.yourhost.com';   // the host gave you this
$user = 'your_user';
$pass = 'your_password';
$name = 'your_database';
```

`config/credentials.php` is in `.gitignore`, so your password never goes to GitHub.

(Instead of that file you can also set the environment variables `DB_HOST`,
`DB_USER`, `DB_PASS` and `DB_NAME` in the hosting panel.)

**4. Make sure the uploads folder can be written to**

Right click `assets/uploads` -> Properties -> Permissions -> `755` (or `775`).

**5. Turn on HTTPS**

Uncomment the HTTPS block at the bottom of `.htaccess` once the site has a
certificate.

**6. Change the test passwords**

The accounts in `database.sql` use `admin123`. Change them before you go live:

```sql
UPDATE users SET password = '$2y$10$...' WHERE email = 'admin@shop.com';
```

The easiest way is to just register again in the admin and use the **forgot
password** page, or run this in PHP:

```php
echo password_hash('myNewPassword', PASSWORD_DEFAULT);
```

**Free hosting to try:** InfinityFree, Hostinger, or a small VPS with
cPanel + Apache + MySQL. Any of them works because the code is plain PHP and
MySQL with no extra packages.

## Next steps (if you want to keep going)

- Discount codes (a `coupons` table + a code field on the checkout form)
- Customer review / rating table
- A page where the customer can change their address
- Email with the order confirmation (`mail()`)
