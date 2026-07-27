=== Native Forms ===
Contributors: moghadampro
Requires at least: 6.5
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later

A lightweight, brand-neutral WordPress form builder with private submission storage and RTL support.

== Description ==

Native Forms provides:

* A top-level Forms menu with Forms and Submissions screens.
* Private database-only submissions; no submission email is sent.
* Text, email, telephone, number, textarea, select, radio, scale, checkbox and section fields.
* Unicode-safe Persian and Arabic labels, options and help text.
* Automatic RTL frontend layout when WordPress uses an RTL language.
* Responsive two-column desktop and one-column mobile layouts.
* Nonce validation, honeypot protection and server-side sanitization.
* A shortcode compatible with the block editor, Elementor and other page builders.
* A provider-neutral `native_forms_submission_created` action for optional integrations.

== Usage ==

1. Open Forms > Forms and add a new form.
2. Enter one field per line using:
   `type|name|label|required|options|help`
3. Publish the form.
4. Copy `[native_form id="123"]` into a page or shortcode widget.
5. Review responses under Forms > Submissions.

Shortcode text can be customized:

`[native_form id="123" button="ارسال" sent="پاسخ شما ثبت شد." error="فیلدهای الزامی را بررسی کنید."]`

For a Persian form on an English WordPress installation, force RTL and localize the built-in labels:

`[native_form id="123" dir="rtl" button="ارسال" select="انتخاب کنید" yes="بله" sent="پاسخ شما ثبت شد." error="فیلدهای الزامی را بررسی کنید."]`

Persian example:

`section||اطلاعات تماس|||لطفاً مشخصات خود را وارد کنید.`
`text|full_name|نام و نام خانوادگی|required||`
`email|email|ایمیل|required||`
`radio|contact_method|روش تماس ترجیحی|required|تلفن,ایمیل,واتساپ|`
`textarea|message|پیام|required||`

== Data and privacy ==

Submissions remain in the current WordPress database as private administration records. Native Forms does not send submission emails, connect to external services or delete data during deactivation.

== Changelog ==

= 1.0.0 =

* Initial standalone release derived from the proven multilingual form architecture.
