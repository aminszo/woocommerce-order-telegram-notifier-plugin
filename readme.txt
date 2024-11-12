=== Telegram order notification ===
Contributors: aminsz
Tags: Telegram notification, New order notification, WooCommerce, Telegram, Orders
Requires at least: 5.5
Tested up to: 6.6.2
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Telegram Order Notifications is a WooCommerce plugin that allows you to send real-time notifications to Telegram whenever a new order is placed.

== Description ==
### Key Features:
– Customizable notification templates with placeholders for order details.
– Option to send messages via a middleman service, such as Pipedream.
– Supports multiple order statuses, like \"On-Hold\" and \"Processing\".
– Seamless integration with WooCommerce.

### Why Use This Plugin?
If you rely on Telegram for instant updates, this plugin ensures you never miss an important order. It\'s perfect for store owners and managers who need real-time order updates on the go.

== Installation ==
### Automatic Installation
The quickest way to set up the plugin is through automatic installation, which is fully managed by WordPress. Here’s how to proceed:

– Access your WordPress Admin Dashboard.
– Go to the Plugins section and select Add New.
– Use the search bar to type in “telegram order notification” and press Enter.
– Find the plugin in the list of results.
– Click the Install Now button and wait for the installation to finish.
– Finally, click Activate to enable the plugin.

### Manual Installation
If you prefer to install the plugin manually, you’ll need to download the plugin file and upload it to your server using an FTP client. You can find detailed instructions for this process in the official WordPress documentation.

== Frequently Asked Questions ==
= How do I get a Telegram Bot API Token? =  
You can create a new bot and get the API token from [BotFather](https://core.telegram.org/bots#botfather).

= How do I find the Chat ID for my Telegram bot? =  
Add your bot to a chat or group and send a message. Then, use the Telegram API to retrieve the chat ID. Refer to the official Telegram Bot API documentation for more details.

= What are placeholders in the message template? =  
Placeholders like `{id}`, `{buyer_name}`, and `{total}` dynamically insert order details into the notification message. Refer to the settings page for a full list of available placeholders.

= What is the middleman option for? =  
The middleman option allows you to route notifications through an intermediary service, useful if your server has restrictions on accessing the Telegram API.

== Screenshots ==
1. **Admin Settings Page:** Configure your Telegram API token, chat ID, and notification settings.
2. **Order Notifications on Telegram:** Example of a real-time order notification sent to a Telegram chat.

== Changelog ==
= 1.0 =
* Initial release.