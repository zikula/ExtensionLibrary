Publishing your Extension
=========================
(With sincere thanks and apologies to the jQuery crew, from whom this concept was basically reverse-engineered)

Add a Post-Receive Hook
------------------------
First, you'll need to create a post-receive hook on GitHub. Just follow the [step-by-step guide for adding a webhook][webhook].

Add a Manifest and Composer file to your Repository
---------------------------------------------------
The Zikula Extensions Library will look in the root level of your repository for a file named `zikula.manifest.json`.
You will need to create the file according to the [manifest specification][manifest]. The manifest specifies the
location of the `composer.json` file which is also required (see [specification][composer]). Use an online
JSON verifier such as [JSONlint](http://jsonlint.com/) to make sure both files are valid. Use our [online manifest validator][validate]
to make sure your manifest validates against the required schema. You are now ready to publish your extension!

Publishing a Version
--------------------
After the post-receive hook is setup and your manifest has been added, publishing your extension is as simple as tagging
the version in git and pushing the tag to GitHub. The post-receive hook will notify the extensions site that a new tag
is available and the extensions site will take care of the rest!

<pre>
$ git tag 0.1.0
$ git push origin --tags
</pre>

The name of the tag must be a valid [semver](http://semver.org/) value, but may contain an optional `v` prefix. The tag
name must also match the version listed in the manifest file. So, if the version field in the manifest is "0.1.1" the
tag should be either "0.1.1" or "v0.1.1". If the manifest file is valid, the version will be automatically added to the
extensions site.

The extensions library does not support re-processing tags that it has already seen. Therefore, we strongly suggest that
you do not overwrite old tags. Instead, update the version number tag in the manifest, commit, and create a new tag to
fix any errors you've encountered.

For example, you've pushed version v1.7.0 of your extension, but there is an error detected in the manifest. If you fix
the error, delete, re-create, and push another v1.7.0 tag, the library will not detect it. You will have to create and
push v1.7.1.

Troubleshooting
---------------
If you have problems with your extension not publishing you should check the [error log][log] for hints on what the
problem might be.

If you still encounter trouble getting this process to work with your extension, please submit a support request on the
forums at http://www.zikula.org/forums

How long should the process take
--------------------------------
When everything works, this process is pretty close to instant. There are caches in place, etc, but in general, if you
haven't seen your extension get updated on the site within 5 minutes, there is a good chance something went wrong. Going
into your Web Hooks settings and hitting the "Test Hook" button (once) may help if you recently pushed a new tag.


*NOTICE: the urls in this document are generated in the controller and are therefore not available if reading offline.*
