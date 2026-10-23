=== VBEB - Bridge for VikBooking and Elementor ===
Contributors: sibsas2013
Tags: vikbooking, elementor, booking, hotel, room
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Design the VikBooking room details page with Elementor widgets.

== Description ==

VBEB lets you replace the content of the VikBooking "Room Details" page with an Elementor template built from dedicated widgets. Every widget shows the real data of the room being viewed and keeps the original VikBooking features (availability calendar, booking form, information request).

Widgets in the "VBEB - Room Details" category:

* Room - Name
* Room - Category
* Room - Main Image
* Room - Image Carousel
* Room - Gallery
* Room - Description
* Room - Short Description
* Room - Price
* Room - Amenities
* Room - Calendar
* Room - Map
* Room - Booking Form
* Room - Information Button
* Room - Information Form

Widget in the "VBEB - General Widgets" category:

* VBEB Popup Calendar: designs the look of the date picker used by the Room - Booking Form widget.

The plugin also adds the "VBEB Popup" template type, used by the Room - Information Button widget to show the information request form in a popup.

VikBooking and Elementor are trademarks of their respective owners. This plugin is not affiliated with or endorsed by them.

= Requirements =

* [VikBooking](https://wordpress.org/plugins/vikbooking/) (free version is enough)
* [Elementor](https://wordpress.org/plugins/elementor/) (free version is enough)

== Installation ==

1. Install and activate VikBooking and Elementor.
2. Upload the plugin folder to `/wp-content/plugins/` or install it from the Plugins screen, then activate it.
3. In Elementor, create a template of type "Section" and add the widgets of the "VBEB - Room Details" category.
4. Go to VBEB > Template and select the template for the Room Details page.

On activation the plugin copies the override file `default_listing.php` to `wp-content/uploads/vikbooking/overrides/site/roomdetails/`. If a file with the same name already exists and was not created by this plugin, it is not overwritten: a copy with the suffix `-vbeb-suggested.php` is saved next to it and a notice is shown in the dashboard.

== Frequently Asked Questions ==

= Which room layout is supported? =

The "Listing" layout of VikBooking, the default one for new rooms.

= What happens if I do not select a template? =

The Room Details page is shown exactly as VikBooking draws it.

= Does it work with WPML or Polylang? =

Yes. The room data is translated with the VikBooking translations. When a translated copy of the Room Details template (or of a "VBEB Popup" template) exists and is published, it is used for the pages in that language. With WPML, the texts typed in the widgets can be translated with the WPML translation editor (the plugin includes a wpml-config.xml file). With Polylang, enable the translation of the Elementor templates in Languages > Settings > Custom post types and taxonomies.

== Changelog ==

= 1.0.0 =
* First public release.
