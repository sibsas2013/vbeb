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

## How to use

1. In **VikBooking > Shortcodes**, create a shortcode of type "Room Details" and add it to a WordPress page (VikBooking can create the page for you). This is the page where VikBooking shows the rooms.
2. Edit that page and, in the **Template** option of the page settings, choose **Elementor Full Width**, so the room details can use the whole width of the page. Update the page.
3. In the WordPress dashboard, go to **Elementor > Templates > Saved Templates** and add a new template of type "Page", for example "Room Details".
4. Open it with Elementor and build the layout with the widgets of the **VBEB - Room Details** category. In the editor the widgets show the data of a sample room.
5. Publish the template.
6. Go to **VBEB > Template**, choose your template in "Room Details" and save. Every room page of VikBooking now uses this template.
7. Optional: create a **VBEB Popup** template with Popup Type "Information Form" (with the Room - Information Form widget) and select it in the Room - Information Button widget; create a **VBEB Popup** template with Popup Type "Popup Calendar" (with the VBEB Popup Calendar widget) and select it in the Room - Booking Form widget.

To go back to the original VikBooking page, choose "Use the original VikBooking template" in **VBEB > Template**.

### Room layout option in VikBooking

While VBEB is active, the "Style Layout" option (Classic or Listing) is hidden in the VikBooking room edit screen, because the look of the room page is decided by the Elementor template. The value already saved for each room is not changed. VBEB uses the "Listing" layout, the default one for new rooms: to switch a room saved as "Classic", deactivate VBEB for a moment, change the option, save the room and activate VBEB again.

## Installation

1. Install and activate VikBooking and Elementor.
2. Download the zip of the latest [release](../../releases) and install it from **Plugins > Add New > Upload Plugin**, then activate it.
3. Follow the steps in [How to use](#how-to-use).

The plugin does not copy or modify any file. When a template is selected, VBEB tells VikBooking (through its `vikbooking_before_display_roomdetails` action) to use the room details layout included in the plugin folder. Without a selected template, VikBooking works exactly as before, including any template override you created yourself.

The supported room layout is the VikBooking "Listing" layout, the default one for new rooms. Without a selected template, the Room Details page is shown exactly as VikBooking draws it.

## Multilingual sites

VBEB works with **WPML** and **Polylang**.

- The room data (name, descriptions, amenities) is translated with the VikBooking translations (VikBooking > Translations, with "Enable Multi-Language" turned on).
- When a translated copy of the Room Details template (or of a "VBEB Popup" or popup calendar template) exists and is published, it is used for the pages in that language.
- With WPML, the texts typed in the widgets can be translated with the WPML translation editor: the plugin includes a `wpml-config.xml` file.
- With Polylang, enable the translation of the Elementor templates in **Languages > Settings > Custom post types and taxonomies**.

Note: for a logged-in user, VikBooking shows its data in the language chosen in the user profile, not in the language of the page. To check a page as a visitor, use a private window.

## Translations

The interface of the plugin is in English (text domain `vbeb-bridge-for-vikbooking-and-elementor`). Translations into other languages are managed on [translate.wordpress.org](https://translate.wordpress.org/) and are installed automatically by WordPress.

## License

GPLv2 or later. See [LICENSE](LICENSE).

VikBooking and Elementor are trademarks of their respective owners. This plugin is not affiliated with or endorsed by them.
