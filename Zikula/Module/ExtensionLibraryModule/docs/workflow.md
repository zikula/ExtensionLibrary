ExtensionLibrary
================

Just some notes on how ExtensionLibrary is supposed to work.

Someone creates a module/theme/plugin and puts it on github. When they have something ready for release they tag it.

Tagging the extension automatically alerts the ExtensionLibrary and (assuming everything validates properly), the
extension is *automatically* added to the Library.

There are three entities in the Library: Vendor, Extension and Version

Vendor <-- one to many --> Extension <-- one to many --> Version

A typical github repo url looks like `http://www.github.com/zikula-modules/Legal`. This is therefore understood as
`http://www.gihub.com/<owner>/<repository name>`

Vendor
------
Vendor uses the repository **owner** as a unique id. This value is obtained via the posted payload information when
the tag is pushed. Other values MAY optionally be set in the manifest. The `title` is used for the display of the
related extensions, but will default to the repository `owner` if left empty.

The vendor can later be "claimed" by one or more Core User(s) and then managed (somewhat) by those users. Claims are
processed by moderators to ensure authenticity.

Vendors can be *verified*, *unverified* (default), or *denied*. Ultimately, this information will be used to filter
the user display, probably only displaying verified vendors (at least as a default). Denied vendors would be hidden
entirely preventing spam. Spam seems unlikely however, since a github account will be required and several steps must
be taken to "push" an extension to the Library.

Extension
---------
Extension uses the repository **id** and **repository name** as unique ids. These values are obtained via the posted
payload information when the tag is pushed. Additionally, the **title** and **type** MUST be declared in the manifest.
Other values MAY optionally be set in the manifest. The `title` is used for the display of the extension but will
default to the `repository name` if left empty.

Version
-------
Version uses the **extension version** as a unique id. This value is obtained via the posted payload information when
the tag is pushed. Additionally, the **compatibility** and **licenses** MUST be declared in the manifest. Other values
MAY optionally be set in the manifest.

Versions may be marked as Active/Inactive


Administration
--------------
There will be very little to administrate.
 - There will be a way to verify/deny vendors.
 - There will be a way to remove any entities.

User Interface
--------------
Users will be able to
 - browse the library
   - by 'type' (m/t/p)
   - select tags
   - search
 - submit a claim of ownership for a vendor
 - 'manage' their extensions
   - not sure what they should be able to do here...
   - edit/delete ?
 - validate a manifest
 - view a sample manifest
 - view a log file of past auto-commits


Downloads
---------
All downloads will be handled by Github. No files will be kept on the zikula servers.


Translations
------------
Currently there is no mechanism for handling automatic inclusion of translation files on a per-extension basis.
This will be left to the individual extension owners to allow contributions to their repositories including the
translation.


Images
------
Vendor logos and Extension icons are included in the manifest as a URL. When the tag is pushed, the url is processed
and an attempt is made to transfer that image from the URL to our server. The image is thereafter served from the
Zikula domain. Several layers of security are active to prevent malicious code from being unwittingly uploaded via this
process. Only `.jpg`, `.jpeg`, `.gif` and `.png` images are allowed. All images are served from a protected directory.