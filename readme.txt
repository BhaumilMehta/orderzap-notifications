=== OrderZap Notifications ===
Contributors:      wcwhatsappnotify
Tags:              woocommerce, whatsapp, order notification, sms, meta api, twilio
Requires at least: 6.0
Tested up to:      7.0
Requires PHP:      8.0
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Automatically send WhatsApp messages to customers for every WooCommerce order event — new order, processing, shipped, completed, cancelled, and more.

== Description ==

**OrderZap Notifications** keeps your customers informed at every step of their order journey by sending automated WhatsApp messages directly to their phone number.

No third-party SaaS subscription required — connect directly to **Meta WhatsApp Cloud API** (official, free tier available) or **Twilio** using your own credentials.

= Core Features =

* 🔔 **Automatic notifications** for all WooCommerce order status changes
* 📦 **Tracking updates** — send courier name, tracking number & link
* 📝 **Customer notes** — WhatsApp the customer when admin adds a note
* ✏️ **Fully customizable templates** with dynamic variables
* 📊 **Delivery logs** — view sent, failed, and API responses
* 🔌 **Provider-based architecture** — easily add future providers
* 🇮🇳 **India-first** — auto-formats bare 10-digit mobile numbers to +91

= Supported Events =

* New Order (Pending)
* Order Processing
* Order Completed
* Order Cancelled
* Order Refunded
* Order Failed
* Order On Hold
* Customer Note (admin → customer)
* Shipment Tracking

= Dynamic Template Variables =

`{customer_name}` `{order_id}` `{order_total}` `{order_status}` `{order_url}`
`{tracking_number}` `{courier_name}` `{tracking_url}` `{site_name}` `{note_message}`

= WhatsApp API Providers =

**Meta WhatsApp Cloud API** (Recommended)
Official Meta Business API. Free to start. Get credentials at [developers.facebook.com](https://developers.facebook.com/apps/).

**Twilio**
Industry-standard messaging API. Great for testing. Get credentials at [twilio.com/console](https://www.twilio.com/console).

= Developer Friendly =

* Clean OOP architecture with PSR-4 autoloading
* Filter hook `wc_wan_providers` to register custom providers
* Action hook `wc_wan_checkout_complete` on checkout
* Full REST API (`wc-wan/v1`) for headless integrations
* WordPress coding standards throughout

== Installation ==

1. Upload the `orderzap-notifications` folder to `/wp-content/plugins/`
2. Activate through **Plugins → Installed Plugins**
3. Navigate to **WA Notify → Settings** in your WordPress admin
4. Select your provider (Meta or Twilio), enter credentials
5. Toggle **Plugin Active** to enable
6. Use **Send Test** to verify everything works

= Minimum Requirements =

* WordPress 6.0 or higher
* WooCommerce 7.0 or higher
* PHP 8.0 or higher

== Frequently Asked Questions ==

= Does this plugin work without a WhatsApp Business account? =

You need either a Meta WhatsApp Business account or a Twilio account with WhatsApp enabled. Both offer free tiers for testing.

= Will this work for Indian mobile numbers? =

Yes. The plugin automatically adds the +91 country code to bare 10-digit Indian mobile numbers.

= Can I customize the message templates? =

Absolutely. Go to **WA Notify → Templates** to edit the message for each event. Use the dynamic variable buttons to insert customer and order details.

= Is customer data stored? =

Only the recipient phone number, message content, and delivery status are stored in the local WordPress database (`wp_wc_wan_logs` table). No data is sent to any third-party except the WhatsApp API provider you configure.

= Can I add my own WhatsApp provider? =

Yes. Register a custom class that implements `WcWan\Providers\ProviderInterface` and hook it via:

`add_filter( 'wc_wan_providers', function( $providers ) {`
`    $providers['myprovider'] = new MyProvider( $settings );`
`    return $providers;`
`} );`

= How is the order total formatted? =

The plugin uses WooCommerce's own currency settings (symbol, decimal separator, thousand separator, currency position) to produce a clean Unicode string like `₹1,499.00` — no HTML entities in your WhatsApp messages.

== Screenshots ==

1. Dashboard — delivery stats at a glance
2. Settings — provider selection with direct console links
3. Message template editor with live WhatsApp-style preview
4. Delivery logs table with status filtering

== Changelog ==

= 1.0.0 =
* Initial release
* Meta WhatsApp Cloud API support
* Twilio WhatsApp API support
* 9 default order event templates
* Template variable editor
* Delivery logs with pagination
* REST API for headless use

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade steps needed.
