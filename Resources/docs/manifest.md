Zikula Extension Manifest Specification
=======================================
(With sincere thanks and apologies to the jQuery crew, from whom this concept was basically reverse-engineered)

This document is all you need to know about what's required in your `zikula.manifest.json` file(s).

[View the sample][sample] `zikula.manifest.json` file

Manifest files must live in the root of your repository and exist in your tags. The files must be actual JSON, not just
a JavaScript object literal.

Fields
------

 - [extension](#extension)
     - [title](#extension-title) (required)
     - [url](#extension-url)
     - [icon](#extension-icon)
 - [version](#version)
     - [semver](#version-semver) (required)
     - [composerpath](#version-composerpath) (required)
     - [description](#version-description)
     - [keywords](#version-keywords)
     - [urls](#version-urls)
     - [dependencies](#version-dependencies)
         - [zikula/core](#version-dependencies-core) (required)


<a name="extension"></a>Extension
=========

<a name="extension-title"></a>title (required)
----------------

The display title of your extension. This will be used for the page title and top-level heading on your
extension's page. Include spaces and mixed case as desired.

<a name="extension-url"></a>url
---

The url to the site for the extension. A Github page is suitable or any other page as you desire.

<a name="extension-icon"></a>icon
----

The url to your extension icon (different from your vendor logo). The image will be copied to our servers.
Supported image types are `.jpg`, `.jpeg`, `.gif` and `.png`. All images MUST have one of those filename extensions.
Images MUST be less than 120px x 120px. Recommended size is 90px x 90px.


<a name="version"></a>Version
=======

<a name="version-semver"></a>semver (required)
-----------------

A valid version string as defined by [SemVer 2.0.0](http://semver.org). Changes to the extension should come along with
changes to the version. See [Specifying Versions](#versions).

<a name="version-composerpath"></a>composerpath (required)
-------------------

A string defining the path the extension's `composer.json` file relative to the repository root.
MUST contain the file name at the end of the path (for example, at root level simply, `composer.json`)

<a name="version-description"></a>description
-----------

Put a description in it. It's a string. This helps people discover your extension, as it's listed in the
Zikula Extensions Library site.

<a name="version-keywords"></a>keywords
--------

Put keywords in it. It's an array of strings. This helps people discover your extension as it's listed on the
Zikula Extensions Library site. Keywords may only contain letters, numbers, hyphens, and dots.

<a name="version-urls"></a>urls
----

 - version
     - The url to the specific version site.
 - docs
     - The url to the version documentation.
 - demo
     - The url to the version demo.
 - download
     - The url to download the extension. A download URL will be automatically generated based on the tag in GitHub,
       but you can specify a custom URL if you'd prefer to send users to your own site.
 - issues
     - The url to the issue tracker for the version.

<a name="version-dependencies"></a>dependencies
------------

Dependencies are specified by mapping their name to the required version. The name should be the name specified in the
composer.json file and not the title.
See [Specifying Versions](#versions).

If a extension that you depend on uses other extensions as dependencies that your extension uses as well, we recommend
you list those also. In the event that the depended on extension alters its dependencies, your extension's dependency
tree won't be affected.

### <a name="version-dependencies-core"></a>Core dependency (required)

A string defining Zikula Core version compatibility. **Important: All modules using legacy core technologies, i.e.
Smarty, DBUtil, Doctrine1, the Forms Framework and many more, MUST specifiy `<1.5` in their core compatability.**
Example: `"zikula/core": ">=1.3.5 <1.5"`. See [Specifying Versions](#versions).


Additional Details
==================

<a name="versions"></a>Specifying Versions
-------------------

Please see https://getcomposer.org/doc/01-basic-usage.md#package-versions.


*NOTICE: the urls in this document are generated in the controller and are therefore not available if reading offline.*
