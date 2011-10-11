=== Issuu PDF Sync ===
Contributors: benjaminniess, momo360modena
Donate link: http://beapi.fr/donate
Tags: Issuu, flipbook, PDF, upload, synchronisation, flash, flip, book
Requires at least: 3.1
Tested up to: 3.2.2
Stable tag: 2.0.1

== Description ==

Allow to create PDF Flipbooks with the http://issuu.com service. You just need to get a free key and all your PDF will synchronised on the site. 
Then you'll be abble to insert flipbooks inside your post without having to quit the WordPress admin pannel.

== Installation ==

1. Upload and activate the plugin
2. Go to Issuu.com and get an API Key http://issuu.com/user/settings?services=true#services
3. Go back to your WordPress admin and go to 'Settings > Issuu PDF Sync'
4. Enter your Issuu API Key and Secret Key
5. Now, when you will upload a PDF in your library (or directly in your post), it will be sent to Issuu
6. If you want to add a flipbook into a post, clic on the Issuu button in your tinyMCE editor and select your PDF file.
7. For more info, go to the bottom of the 'Settings > Issuu PDF Sync' page 

== Frequently Asked Questions ==

= How to get an Issuu API Key ? =

You need to go to http://issuu.com and to create an account. Then go to the http://issuu.com/user/settings?services=true#services page and create a new application

= How to use the shortocde ? =

Click to the media button, choose a PDF document and click on the Issuu PDF button to insert the basic shortcode

If you want to add params for a specific PDF, you can follow these examples:

<code>[pdf issuu_pdf_id="id_of_your_PDF" width="500" height="300"]</code>

In this example, we want to specify a width and a height only for this PDF
<code>[pdf issuu_pdf_id="id_of_your_PDF" layout="browsing" autoFlip="true" autoFlipTime="4000"]</code>

In this other example, we want to specify the browsing layout (one page presentation) and we want the PDF pages to autoflip each 4 seconds

You will see all avaliable params inside the plugin option pannel

== Screenshots ==
1. The config page
2. The Issuu button
3. The shortcode
4. The Issuu Flipbook in a post
5. The media edit view
6. The Issuu button
7. The shortcode generator

== Changelog ==

* 1.0
	* First release
* 1.0.1
	* Fix bug on shortcode
* 1.0.2
	* Apply a new filter
	* Clean useless js
* 2.0
	* Add a new popin feature in the TinyMCE editor. Allow to generate a shortcode with all the Issuu options.
	* Add the wmode transparent to the swf embed code
* 2.0.1
	* Fix minor bug