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

* VBEB Popup Calendar: designs the look of the date picker used by the Room - Booking Form widget. It goes in a "VBEB Popup" template with Popup Type "Popup Calendar".

The plugin also adds the "VBEB Popup" template type, with two popup types: "Information Form", used by the Room - Information Button widget to show the information request form in a popup, and "Popup Calendar", for the date picker of the Room - Booking Form widget.

= How to use =

1. In VikBooking > Shortcodes, create a shortcode of type "Room Details" and add it to a WordPress page (VikBooking can create the page for you). This is the page where VikBooking shows the rooms.
2. Edit that page and, in the "Template" option of the page settings, choose "Elementor Full Width", so the room details can use the whole width of the page. Update the page.
3. In the WordPress dashboard, go to Elementor > Templates > Saved Templates and add a new template of type "Page", for example "Room Details".
4. Open it with Elementor and build the layout with the widgets of the "VBEB - Room Details" category. In the editor the widgets show the data of a sample room, so you can see the real result while you design.
5. Publish the template.
6. Go to VBEB > Template, choose your template in "Room Details" and save. From now on, every room page of VikBooking uses this template.
7. Optional: for the information request in a popup, create a template of type "VBEB Popup" with Popup Type "Information Form", add the Room - Information Form widget and select the template in the Room - Information Button widget. For a custom date picker, create a "VBEB Popup" template with Popup Type "Popup Calendar", add the VBEB Popup Calendar widget and select the template in the Room - Booking Form widget.

To go back to the original VikBooking page, choose "Use the original VikBooking template" in VBEB > Template.

= Room layout option in VikBooking =

While VBEB is active, the "Layout style" option (Classic or Listing) is hidden in the VikBooking room edit screen, because the look of the room page is decided by the Elementor template. The value already saved for each room is not changed. VBEB uses the "Listing" layout, the default one for new rooms.

= Compatibility =

* Multilingual sites: works with WPML and Polylang (see the FAQ).
* Themes: tested with Plaza (theme for VikBooking), Hello Elementor and Astra.

VikBooking and Elementor are trademarks of their respective owners. This plugin is not affiliated with or endorsed by them.

= Requirements =

* [VikBooking](https://wordpress.org/plugins/vikbooking/) (free version is enough)
* [Elementor](https://wordpress.org/plugins/elementor/) (free version is enough)

== Installation ==

1. Install and activate VikBooking and Elementor.
2. Upload the plugin folder to `/wp-content/plugins/` or install it from the Plugins screen, then activate it.
3. In Elementor, create a template of type "Page" and add the widgets of the "VBEB - Room Details" category.
4. Go to VBEB > Template and select the template for the Room Details page.

The plugin does not copy or modify any file. When a template is selected, VBEB tells VikBooking (through its `vikbooking_before_display_roomdetails` action) to use the room details layout included in the plugin folder. Without a selected template, VikBooking works exactly as before, including any template override you created yourself.

== External services ==

This plugin can connect to the Google Maps JavaScript API, a service provided by Google, to show the map of a room.

* What it is used for: only in the Elementor editor, the "VBEB Room - Map" widget shows the real map of the preview room, so you can see how the map looks while you design the template. On the public pages of the site, the map is drawn by VikBooking itself, not by this plugin.
* When it is used: only when the "VBEB Room - Map" widget is on the template open in the Elementor editor, and only if "Enable Geocoding" is turned on in VikBooking with a Google Maps API key.
* What data is sent: the Google Maps API key saved in VikBooking, the coordinates of the room shown on the map and, like any request made by a browser, the IP address and browser information of the person using the Elementor editor.

This service is provided by Google: [Google Maps Platform Terms of Service](https://cloud.google.com/maps-platform/terms), [Google Privacy Policy](https://policies.google.com/privacy).

== Frequently Asked Questions ==

= Which room layout is supported? =

The "Listing" layout of VikBooking, the default one for new rooms. A room saved with the "Classic" layout keeps the original VikBooking page. To switch it to "Listing", deactivate VBEB for a moment (the "Layout style" option appears again in the room edit screen), change the option, save the room and activate VBEB again.

= Why is the "Layout style" option missing in the VikBooking room edit screen? =

VBEB hides it while it is active, because the look of the room page is decided by the Elementor template. See "Room layout option in VikBooking" above.

= What happens if I do not select a template? =

The Room Details page is shown exactly as VikBooking draws it.

= Does it work with WPML or Polylang? =

Yes. The room data is translated with the VikBooking translations. When a translated copy of the Room Details template (or of a "VBEB Popup" or popup calendar template) exists and is published, it is used for the pages in that language. With WPML, the texts typed in the widgets can be translated with the WPML translation editor (the plugin includes a wpml-config.xml file). With Polylang, enable the translation of the Elementor templates in Languages > Settings > Custom post types and taxonomies.

== Screenshots ==

1. The widgets of the "VBEB - Room Details" category in the Elementor panel, with the Room Details template open in the editor.
2. Description, amenities, availability calendar and booking form showing the real data of a sample room, and the VBEB Popup Calendar widget in the "VBEB - General Widgets" category.
3. Style options of the Room - Name widget.
4. Style options of the Room - Main Image widget, above the Room - Image Carousel.
5. Style options of the Room - Description widget.
6. A "VBEB Popup" template of type "Popup Calendar": the date picker of the booking form designed with the VBEB Popup Calendar widget.
7. A "VBEB Popup" template of type "Information Form" with the Room - Information Form widget.
8. The Room Details and "VBEB Popup" templates in Elementor > Templates > Saved Templates, on a multilingual site.
9. VBEB > Template: the template used for the VikBooking Room Details page.
10. VBEB requires VikBooking and Elementor, both in their free versions.

== Changelog ==

= 1.0.0 =
* First public release.
