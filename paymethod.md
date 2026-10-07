# JazzCash Online Payment – How to Test and How to Add It to AutoCare Pro

## 1. Short answer

1. Create a free **JazzCash sandbox** (test) merchant account. It gives you 3 test keys.
2. Add JazzCash to the "Pay Invoice" page of this project using those test keys.
3. Pay an invoice with the **test wallet/card data** shown in the sandbox portal.
4. Check that the payment is done using the 4 checks in section 6.

This whole file is about test mode only.

---

## 2. Get the sandbox account (test keys)

1. Open `https://sandbox.jazzcash.com.pk` and sign up as a merchant (email + phone, no documents needed).
2. After login, find the merchant credentials page and copy:
   - **Merchant ID**
   - **Password**
   - **Integrity Salt** (also called Hash Key)
3. In the same portal, set the **Return URL** to the callback of this project (see section 4.5).
4. Open the **Sandbox Testing** section. It lists the test mobile number, CNIC digits, MPIN/OTP and test card for success and failure cases. Use those exact values. I have not put them here because I cannot confirm the current ones without your portal.

---

## 3. How a JazzCash payment works (hosted checkout / "Page Redirection")

This is the simplest method and the right one to start with.

```
Customer clicks "Pay with JazzCash" on the invoice
        │
        ▼
Our server builds the pp_* fields + pp_SecureHash
        │
        ▼
Browser auto-submits a hidden form (POST) to JazzCash
        │
        ▼
Customer pays on the JazzCash page (wallet / card / voucher)
        │
        ▼
JazzCash POSTs the result back to our Return URL
        │
        ▼
We verify the hash + amount, then mark the invoice paid
```

URLs for this method:

Test form POST URL:

`https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/`

---

## 4. How to add it to this project

What the project already has:

- `invoices` table with `status`, `payment_method`, `paid_at`.
- Route `invoices.pay` → `InvoiceController@pay` → `resources/views/customer/invoices/pay.blade.php`, which currently shows "Online payment is coming soon."
- Admin "mark as paid" for cash.

So only the JazzCash part is missing.

### 4.1 `.env` keys

```env
JAZZCASH_MERCHANT_ID=MC12345
JAZZCASH_PASSWORD=xxxxxxxx
JAZZCASH_INTEGRITY_SALT=xxxxxxxx
JAZZCASH_ENDPOINT=https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/
```

Add the same names (empty) to `.env.example`.

### 4.2 `config/jazzcash.php`

```php
<?php

return [
    'merchant_id' => env('JAZZCASH_MERCHANT_ID'),
    'password' => env('JAZZCASH_PASSWORD'),
    'integrity_salt' => env('JAZZCASH_INTEGRITY_SALT'),
    'endpoint' => env('JAZZCASH_ENDPOINT'),
];
```

### 4.3 New `payments` table

Do not mark the invoice paid directly from the callback without a record. Keep one row per payment attempt:

| Column | Purpose |
|--------|---------|
| `invoice_id` | which invoice |
| `txn_ref_no` (unique) | our reference sent as `pp_TxnRefNo` |
| `amount` | amount we asked for |
| `status` | `pending` / `paid` / `failed` |
| `response_code`, `response_message` | what JazzCash returned |
| `gateway_reference` | JazzCash's own reference number |
| `payload` (json) | full response, for disputes and debugging |

### 4.4 `app/Services/JazzCash.php` – build the request and the hash

Fields sent to JazzCash:

| Field | Value |
|-------|-------|
| `pp_Version` | `1.1` |
| `pp_TxnType` | empty (customer picks method on JazzCash page) or `MWALLET` |
| `pp_Language` | `EN` |
| `pp_MerchantID` | from config |
| `pp_Password` | from config |
| `pp_TxnRefNo` | unique, e.g. `T20261007153000123` (max 20 chars) |
| `pp_Amount` | **in paisa, no decimals**: Rs 1,080.00 → `108000` |
| `pp_TxnCurrency` | `PKR` |
| `pp_TxnDateTime` | `YmdHis` in Pakistan time |
| `pp_TxnExpiryDateTime` | `YmdHis`, e.g. now + 1 hour |
| `pp_BillReference` | e.g. `invoice42` |
| `pp_Description` | e.g. `AutoCare Pro invoice 42` (plain letters and numbers only) |
| `pp_ReturnURL` | our callback URL |
| `pp_SecureHash` | calculated below |

Hash rule: take every `pp_*` field that is not empty (not `pp_SecureHash`), sort by field name, join the values with `&`, put the salt in front, then HMAC-SHA256 with the salt as the key.

```php
public function hash(array $fields): string
{
    $salt = config('jazzcash.integrity_salt');

    unset($fields['pp_SecureHash']);
    $fields = array_filter($fields, fn ($value) => $value !== null && $value !== '');
    ksort($fields);

    return strtoupper(hash_hmac('sha256', $salt.'&'.implode('&', $fields), $salt));
}
```

Building the amount and dates:

```php
'pp_Amount' => (string) (int) round($invoice->total * 100),
'pp_TxnDateTime' => now('Asia/Karachi')->format('YmdHis'),
'pp_TxnExpiryDateTime' => now('Asia/Karachi')->addHour()->format('YmdHis'),
```

### 4.5 Routes (`routes/web.php`)

Inside the existing customer group:

```php
Route::post('/invoices/{invoice}/pay/jazzcash', [JazzCashController::class, 'start'])
    ->name('invoices.pay.jazzcash');
```

Outside every `auth` group (JazzCash calls it, not our logged-in page):

```php
Route::post('/payments/jazzcash/callback', [JazzCashController::class, 'callback'])
    ->name('payments.jazzcash.callback');
```

The Return URL to register in the JazzCash portal is `{APP_URL}/payments/jazzcash/callback`.

### 4.6 Turn off CSRF for the callback only (`bootstrap/app.php`)

JazzCash's POST has no Laravel CSRF token, so without this you get a 419 error:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => RoleMiddleware::class,
    ]);

    $middleware->validateCsrfTokens(except: [
        'payments/jazzcash/callback',
    ]);
})
```

### 4.7 `JazzCashController`

`start(Invoice $invoice)`
1. `authorize('view', $invoice)` and stop if already paid (same checks as `InvoiceController@pay`).
2. Create a `payments` row with status `pending` and a new `txn_ref_no`.
3. Build the fields + hash and return a small view that auto-submits a hidden form to `config('jazzcash.endpoint')`.

`callback(Request $request)`
1. Recalculate the hash from the posted `pp_*` fields and compare with `pp_SecureHash` using `hash_equals` (compare in the same letter case). If it does not match → reject, do nothing.
2. Find the payment by `pp_TxnRefNo`. If it is already `paid`, just redirect (callback can arrive twice).
3. Check `pp_Amount` equals the amount we stored.
4. If `pp_ResponseCode === '000'` → payment `paid`, and update the invoice:
   `status = paid`, `payment_method = 'jazzcash'`, `paid_at = now()`.
   Otherwise → payment `failed`, save the code and message.
5. Write one log line with the reference, response code and message (`Log::info('JazzCash callback', [...])`). This is what you read while testing.
6. Redirect to `invoices.show` with a success or error flash message.

Two things to know about the callback:

- The user's login session cookie is normally **not** sent on this POST (it comes from another site). So do not use `Auth::user()` there. Find everything from `pp_TxnRefNo`, then redirect; after the redirect the user is logged in again as normal.
- Response code `000` is success. `124` means a voucher was created and is waiting for payment at a shop (only if you allow voucher/OTC). Anything else is a failure.

### 4.8 Small changes to existing files

- `app/Models/Invoice.php`: add `public const METHOD_JAZZCASH = 'jazzcash';` next to `METHOD_CASH`. `paymentSummary()` prints "Paid in {method}", so for JazzCash it should read "Paid via JazzCash".
- `resources/views/customer/invoices/pay.blade.php`: replace the "coming soon" box with a form that posts to `invoices.pay.jazzcash` with a "Pay Rs X with JazzCash" button.

---

## 5. How to test (sandbox)

Run the app (`composer dev`), log in as a customer with an unpaid invoice, open the invoice → Pay.

| # | Test | Expected result |
|---|------|-----------------|
| 1 | Pay with the sandbox **success** test data | Back on invoice page, status Paid, method JazzCash, `paid_at` set, payment row `paid` |
| 2 | Pay with the sandbox **failure** test data | Invoice still Unpaid, payment row `failed` with code/message, error shown |
| 3 | Cancel / close the JazzCash page | Invoice still Unpaid, payment row stays `pending` |
| 4 | Check amount on JazzCash page | Same as invoice total (catches the paisa ×100 mistake) |
| 5 | Open pay page of an already paid invoice | Redirected with "already been paid" |
| 6 | Post a fake callback with a wrong hash (Postman/curl) | Rejected, invoice not changed |
| 7 | Send the same success callback twice | Invoice paid once, no error |
| 8 | Customer A tries to pay customer B's invoice | 403 |

Tests 6–8 can also be written as feature tests in `tests/Feature`, because the callback is just a POST we can build ourselves with the sandbox salt.

About localhost: the result comes back through the customer's browser, so `http://127.0.0.1:8000/payments/jazzcash/callback` normally works as the Return URL. If the sandbox portal refuses a localhost URL, run `ngrok http 8000` and use the `https://...ngrok...` address as `APP_URL` and as the Return URL.

Common errors:

| Problem | Usual cause |
|---------|-------------|
| "Invalid hash" / secure hash error from JazzCash | wrong salt, fields not sorted, empty field included, special characters in `pp_Description` |
| 419 Page Expired on return | callback not excluded from CSRF (4.6) |
| Amount 100× too small or too big | `pp_Amount` must be in paisa |
| Transaction expired at once | date not in Pakistan time, or expiry in the past |
| Invalid return URL | Return URL in the portal differs from `pp_ReturnURL` |

---

## 6. How to check the test payment is done

After you pay with the sandbox test data, check these 4 places. If all 4 agree, the payment works.

### Check 1 – On the screen

- You come back to the invoice page with a success message.
- The invoice shows **Paid** (and "Paid via JazzCash" with today's date).
- Log in as admin → Invoices: the same invoice shows Paid there too.

### Check 2 – In the database

Run `php artisan tinker`, then:

```php
App\Models\Invoice::find(42)->only(['status', 'payment_method', 'paid_at']);
// done  => status "paid", payment_method "jazzcash", paid_at has a date
// not done => status "unpaid", payment_method null, paid_at null

App\Models\Payment::where('invoice_id', 42)->latest()->first();
// done  => status "paid", response_code "000"
// failed => status "failed", response_code is something else
// never came back => status "pending"
```

(Use your own invoice id instead of 42.)

### Check 3 – In the log

`composer dev` already runs `php artisan pail`, so the callback log line appears in the terminal the moment JazzCash sends you back. You can also open `storage/logs/laravel.log`.

- `pp_ResponseCode` = `000` → payment done.
- Any other code → not done; `pp_ResponseMessage` says why.
- No log line at all → JazzCash never reached the callback (check the Return URL and the CSRF exception).

### Check 4 – In the JazzCash sandbox portal

Open the transactions list in the sandbox portal and search for your `pp_TxnRefNo` (it is saved in the `payments` row). The portal should show the same transaction with the same amount and a success status.

### Reading the result

| What you see | Meaning |
|--------------|---------|
| Invoice Paid, payment `paid`, code `000`, shown in portal | Working correctly |
| Portal shows success but invoice still Unpaid | Callback problem in our code (hash check, CSRF, or wrong Return URL) |
| Invoice Paid but nothing in portal | Bug: we marked it paid without a real JazzCash result |
| Payment stays `pending` | Customer never returned from JazzCash page, or callback was not reached |
| Payment `failed` | JazzCash refused it; read the code and message in the log |

---

## 7. Later, if needed

- **Status Inquiry API**: ask JazzCash for the result of a `pp_TxnRefNo` when the customer paid but never came back to our site (payment stuck as `pending`).
- **Direct Mobile Wallet API**: customer types mobile number + last 6 CNIC digits on our own page and approves with MPIN on their phone, with no redirect.
- **Easypaisa**: same idea (its own sandbox, keys and hash); the `payments` table above already fits a second gateway.

All three also have a sandbox, so they can be tested the same way.
