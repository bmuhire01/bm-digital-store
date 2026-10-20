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
│   ├── functions.php    # helper functions (e, get_all, get_row, price, ...)
│   └── credentials.example.php   # copy to credentials.php and fill in
├── bin/
│   └── setup.php        # creates the tables the first time the app starts
├── docker/
│   ├── Dockerfile       # for Railway and other Docker hosts
│   └── start.sh         # what the container runs
├── railway.json         # the Railway settings
├── api/
│   └── index.php        # the front controller for Vercel
├── vercel.json          # the Vercel settings
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

## Putting it online

There are three ways. Nothing secret is in the repository, so any of them works.

### Option 1 - Railway (recommended, same feel as Vercel)

Push to GitHub and the site is live. Railway builds the `docker/Dockerfile` in this
project and runs the app in a container, with MySQL next to it.

1. Go to **railway.app** -> **New Project** -> **Deploy from GitHub Repo** ->
   pick `bm-digital-store`. Railway sees the Dockerfile and starts building.
2. Add the database: on the project, click **+ New** -> **Database** -> **MySQL**.
   Railway then links it to your app automatically and puts the connection in
   the `DATABASE_URL` variable.
3. Add a **volume** so the product pictures survive a restart:
   right click the **app service** -> **Volumes** -> **Add Volume** ->
   Mount Path `/var/www/html/assets/uploads`.
4. Give it a name: **Settings -> Networking -> Generate Domain**. You get
   something like `bm-digital-store.up.railway.app`.
5. Every `git push` to `main` now redeploys the site by itself.

The database tables are created the first time the container starts
(`bin/setup.php`), so there is nothing to import by hand.

Railway gives a free trial with credit, then the Hobby plan is $5 a month.

### Option 2 - Classic shared hosting (cheapest, nothing to build)

Hostinger, InfinityFree, Namecheap - any host that runs PHP + MySQL.

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

### Option 3 - Vercel (needs a free MySQL)

Vercel runs the app with the community PHP runtime. Because Vercel has no
database of its own and the files on the server are not kept, two things are
needed:

| What we need | Vercel reality | What we do |
|---|---|---|
| `$_SESSION` for the cart and the login | Functions are stateless | `config/session_db.php` keeps the sessions in the database |
| Saved product pictures | The filesystem is read only | The pictures go into the database, `image.php` gives them out |
| MySQL | Vercel has none | A free one from Aiven |

**1. Make a free MySQL database**

1. Sign up at **aiven.io** (no credit card, the free plan has no time limit)
2. **Create service** -> **MySQL** -> plan **Free**
3. On the **Overview** page open **Service settings** -> **General** and note the
   **Service URI**. It looks like this:
   ```
   mysql://avnadmin:YOUR_PASSWORD@mysql-xxxx.aivencloud.com:12345/defaultdb
   ```

**2. Push the code and deploy**

The repository already has everything Vercel needs (`vercel.json`, `api/index.php`).
Import it at **vercel.com/new** -> pick `bm-digital-store` -> **Deploy**.

**3. Add the environment variables**

In the Vercel project: **Settings -> Environment Variables** and add:

| Name | Value |
|---|---|
| `DATABASE_URL` | the service URI from Aiven (mark it as **Secret**) |
| `SESSION_DRIVER` | `db` |
| `IMAGE_DRIVER` | `db` |

**4. Done**

Every push to `main` deploys again. The tables are made the first time somebody
opens the site.

> The free Aiven service can power off after a long time without visitors. It
> wakes up again by itself, the first request just takes a little longer.

### Before you go live

The accounts in `database.sql` use `admin123`. Change them first:

```sql
UPDATE users SET password = '$2y$10$...' WHERE email = 'admin@shop.com';
```

The easiest way is to run this in PHP and copy the result:

```php
echo password_hash('myNewPassword', PASSWORD_DEFAULT);
```


## Next steps (if you want to keep going)

- Discount codes (a `coupons` table + a code field on the checkout form)
- Customer review / rating table
- A page where the customer can change their address
- Email with the order confirmation (`mail()`)
