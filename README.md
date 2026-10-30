# VBEB - Bridge for VikBooking and Elementor

Design the VikBooking room details page with Elementor widgets.

VBEB lets you replace the content of the VikBooking "Room Details" page with an Elementor template built from dedicated widgets. Every widget shows the real data of the room being viewed and keeps the original VikBooking features (availability calendar, booking form, information request).

## Requirements

- WordPress 6.5 or later
- PHP 7.4 or later
- [VikBooking](https://wordpress.org/plugins/vikbooking/) (the free version is enough)
- [Elementor](https://wordpress.org/plugins/elementor/) (the free version is enough)

## Widgets

Category **VBEB - Room Details**:

| Widget | What it shows |
| --- | --- |
| Room - Name | The room name |
| Room - Category | The room category |
| Room - Main Image | The main image of the room |
| Room - Image Carousel | The room images as a carousel |
| Room - Gallery | The room images as a gallery |
| Room - Description | The full description |
| Room - Short Description | The short description |
| Room - Price | The starting price |
| Room - Amenities | The amenities of the room |
| Room - Calendar | The VikBooking availability calendar |
| Room - Map | The VikBooking map of the room location |
| Room - Booking Form | The VikBooking booking form |
| Room - Information Button | The button that opens the information request |
| Room - Information Form | The information request form |

Category **VBEB - General Widgets**:

| Widget | What it does |
| --- | --- |
| VBEB Popup Calendar | Designs the look of the date picker used by the Room - Booking Form widget. It goes in a "VBEB Popup" template with Popup Type "Popup Calendar" |

The plugin also adds the **VBEB Popup** template type, with two popup types: **Information Form**, used by the Room - Information Button widget to show the information request form in a popup, and **Popup Calendar**, for the date picker of the Room - Booking Form widget.

## Installation

1. Install and activate VikBooking and Elementor.
2. Download the zip of the latest [release](../../releases) and install it from **Plugins > Add New > Upload Plugin**, then activate it.
3. In Elementor, create a template of type "Page" and add the widgets of the "VBEB - Room Details" category.
4. Go to **VBEB > Template** and select the template for the Room Details page.

On activation the plugin copies the override file `default_listing.php` to `wp-content/uploads/vikbooking/overrides/site/roomdetails/`. If a file with the same name already exists and was not created by this plugin, it is not overwritten: a copy with the suffix `-vbeb-suggested.php` is saved next to it and a notice is shown in the dashboard.

The supported room layout is the VikBooking "Listing" layout, the default one for new rooms. Without a selected template, the Room Details page is shown exactly as VikBooking draws it.

## Multilingual sites

VBEB works with **WPML** and **Polylang**.

- The room data (name, descriptions, amenities) is translated with the VikBooking translations (VikBooking > Translations, with "Enable Multi-Language" turned on).
- When a translated copy of the Room Details template (or of a "VBEB Popup" or popup calendar template) exists and is published, it is used for the pages in that language.
- With WPML, the texts typed in the widgets can be translated with the WPML translation editor: the plugin includes a `wpml-config.xml` file.
- With Polylang, enable the translation of the Elementor templates in **Languages > Settings > Custom post types and taxonomies**.

Note: for a logged-in user, VikBooking shows its data in the language chosen in the user profile, not in the language of the page. To check a page as a visitor, use a private window.

## Translations

The interface of the plugin is in English and is translated into Italian and Spanish (text domain `vbeb-bridge-for-vikbooking-and-elementor`, files in the `languages` folder).

## License

GPLv2 or later. See [LICENSE](LICENSE).

VikBooking and Elementor are trademarks of their respective owners. This plugin is not affiliated with or endorsed by them.
