# === OrderZap Notifications ===

> Automatically send WhatsApp notifications to customers for every WooCommerce order event.

---

## 📋 Requirements

| Dependency    | Minimum Version |
|---------------|-----------------|
| WordPress     | 6.0+            |
| WooCommerce   | 7.0+            |
| PHP           | 8.0+            |

---

## 🚀 Installation

1. Upload the `orderzap-notifications` folder to `/wp-content/plugins/`
2. Activate the plugin through **Plugins → Installed Plugins**
3. Navigate to **WhatsApp → Settings** in your WordPress admin
4. Choose a provider (Meta or Twilio), enter credentials, and enable the plugin

---

## ⚙️ Configuration

### Meta WhatsApp Cloud API (Recommended)

1. Go to [Meta for Developers](https://developers.facebook.com/)
2. Create a WhatsApp Business app
3. Copy **Access Token**, **Phone Number ID**, and **WABA ID**
4. Paste into **Settings → API Settings → Meta**

### Twilio WhatsApp API

1. Sign up at [twilio.com](https://www.twilio.com)
2. Enable WhatsApp Sandbox or a dedicated WhatsApp-enabled number
3. Copy **Account SID**, **Auth Token**, and **From Number**
4. Paste into **Settings → API Settings → Twilio**

---

## 🔔 Notification Events

| Event         | Setting Key            | Default  |
|---------------|------------------------|----------|
| Pending       | `notify_pending`       | ✅ On    |
| Processing    | `notify_processing`    | ✅ On    |
| Completed     | `notify_completed`     | ✅ On    |
| Cancelled     | `notify_cancelled`     | ✅ On    |
| Refunded      | `notify_refunded`      | ✅ On    |
| Failed        | `notify_failed`        | ❌ Off   |
| On Hold       | `notify_on_hold`       | ❌ Off   |
| Customer Note | `notify_customer_note` | ✅ On    |
| Tracking      | `notify_tracking`      | ✅ On    |

---

## 📝 Template Variables

Use these dynamic variables in your message templates:

| Variable           | Description                    |
|--------------------|--------------------------------|
| `{customer_name}`  | Customer's full name           |
| `{order_id}`       | WooCommerce order number       |
| `{order_total}`    | Formatted order total          |
| `{order_status}`   | Current order status           |
| `{order_url}`      | Link to view order             |
| `{tracking_number}`| Shipment tracking number       |
| `{courier_name}`   | Courier/carrier name           |
| `{tracking_url}`   | Full tracking link             |
| `{site_name}`      | Your WordPress site name       |
| `{note_message}`   | Admin customer note content    |

---

## 🚢 Tracking Notifications

To send a tracking update via REST API:

```bash
POST /wp-json/wc-wan/v1/tracking
Content-Type: application/json
X-WP-Nonce: <nonce>

{
  "order_id": 1234,
  "tracking_number": "DHL9876543",
  "courier_name": "DHL Express",
  "tracking_url": "https://www.dhl.com/track?id=DHL9876543"
}
```

---

## 🔌 REST API Endpoints

| Method | Endpoint                        | Description             |
|--------|---------------------------------|-------------------------|
| GET    | `/wc-wan/v1/settings`        | Fetch all settings      |
| POST   | `/wc-wan/v1/settings`        | Save settings           |
| GET    | `/wc-wan/v1/templates`       | Get all templates       |
| POST   | `/wc-wan/v1/templates`       | Update templates        |
| GET    | `/wc-wan/v1/logs`            | Get paginated logs      |
| GET    | `/wc-wan/v1/stats`           | Get delivery stats      |
| POST   | `/wc-wan/v1/test`            | Send test message       |
| POST   | `/wc-wan/v1/tracking`        | Send tracking update    |

All endpoints require `manage_woocommerce` capability and a valid `X-WP-Nonce` header.

---

## 🗄️ Database Tables

### `wp_wc_wan_logs`

| Column       | Type        | Description              |
|--------------|-------------|--------------------------|
| id           | BIGINT      | Auto-increment primary   |
| order_id     | BIGINT      | WooCommerce order ID     |
| event        | VARCHAR(50) | Trigger event slug       |
| recipient    | VARCHAR(30) | Phone number             |
| message      | TEXT        | Full message sent        |
| status       | VARCHAR(10) | `sent` or `failed`       |
| message_id   | VARCHAR(100)| Provider message ID      |
| error        | VARCHAR(500)| Error message if failed  |
| created_at   | DATETIME    | Timestamp                |

### `wp_wc_wan_templates`

| Column     | Type        | Description              |
|------------|-------------|--------------------------|
| id         | INT         | Auto-increment primary   |
| event_slug | VARCHAR(50) | Unique event identifier  |
| label      | VARCHAR(100)| Human-readable label     |
| template   | TEXT        | Message template body    |
| is_active  | TINYINT(1)  | 1 = active, 0 = disabled |
| updated_at | DATETIME    | Last updated timestamp   |

---

## 🔧 Developer Hooks

### Actions

```php
// Fires after checkout order meta is updated
do_action( 'wc_wan_checkout_complete', $order_id, $posted_data );
```

### Filters

```php
// Register a custom WhatsApp provider
add_filter( 'wc_wan_providers', function( $providers ) {
    $providers['myprovider'] = new MyCustomProvider( $settings );
    return $providers;
} );
```

### Adding a Custom Provider

Implement the `WcWan\Providers\ProviderInterface`:

```php
class MyCustomProvider implements \WcWan\Providers\ProviderInterface {
    public function send( string $to, string $message ): array {
        // Your API call here
        return [ 'success' => true, 'message_id' => 'msg_123', 'error' => '' ];
    }
    public function is_configured(): bool { return true; }
    public function get_name(): string { return 'My Provider'; }
}
```

---

## 🗂️ Folder Structure

```
orderzap-notifications/
├── orderzap-notifications.php          # Plugin bootstrap
├── uninstall.php                # Cleanup on deletion
├── README.md
│
├── assets/
│   ├── css/admin.css            # Admin panel styles
│   └── js/
│       ├── admin.js             # JS entry (CDN loader for dev)
│       └── admin.jsx            # React SPA source
│
└── inc/
    ├── core/
    │   ├── Plugin.php           # Singleton bootstrapper
    │   ├── Loader.php           # Hook registration
    │   ├── Settings.php         # Options management
    │   ├── TemplateParser.php   # Variable replacement
    │   └── WhatsAppManager.php  # Provider factory & dispatcher
    ├── api/
    │   └── APIController.php    # REST API endpoints
    ├── admin/
    │   └── Admin.php            # WP admin menus & asset loading
    ├── database/
    │   └── Installer.php        # DB table creation & seeding
    ├── helpers/
    │   └── functions.php        # Global helper functions
    ├── logs/
    │   └── Logger.php           # Log write/read/prune
    ├── notifications/
    │   └── NotificationManager.php # WC hook → WhatsApp dispatch
    └── providers/
        ├── ProviderInterface.php
        ├── MetaProvider.php     # Meta WhatsApp Cloud API
        └── TwilioProvider.php   # Twilio WhatsApp API
```

---

## 🔒 Security

- All REST endpoints validate `manage_woocommerce` capability
- Nonce validation via `X-WP-Nonce` header on all API calls
- Input sanitized with `sanitize_text_field`, `sanitize_key`, `absint`, `esc_url_raw`
- Output escaped with `esc_html`, `wp_kses_post`
- No eval(), no direct SQL without `$wpdb->prepare()`

---

## 📦 Production Build (React UI)

The admin UI ships with a CDN-based dev loader. For production:

```bash
# In a separate build directory
npm create vite@latest admin-build -- --template react
cd admin-build
npm install -D tailwindcss autoprefixer postcss
npx tailwindcss init -p

# Copy assets/js/admin.jsx to src/main.jsx
# Run build
npm run build

# Copy dist/assets/*.js  → orderzap-notifications/assets/js/admin.js
# Copy dist/assets/*.css → orderzap-notifications/assets/css/admin.css
```

---

## 🛣️ Roadmap (Pro Features)

- [ ] Multi-agent support
- [ ] Broadcast messages
- [ ] Abandoned cart recovery
- [ ] OTP verification
- [ ] Chat inbox
- [ ] Automation flows
- [ ] Analytics dashboard

---

## 📄 License

GPL-2.0+ — see [LICENSE](http://www.gnu.org/licenses/gpl-2.0.txt)

---

*Built with ❤️ for Indian WooCommerce stores by WC WhatsApp Order Notification*
