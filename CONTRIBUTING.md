#Contribute To Easy Digital Downloads

Community made patches, localisations, bug reports and contributions are always welcome and are crucial to ensure Easy Digital Downloads remains the #1 eCommerce platform for digital goods on WordPress.

When contributing please ensure you follow the guidelines below so that we can keep on top of things.

__Please Note:__ GitHub is for bug reports and contributions only - if you have a support question or a request for a customisation don't post here, go to our [Support Forum](https://easydigitaldownloads.com/support/) instead.

## Getting Started

* Submit a ticket for your issue, assuming one does not already exist.
  * Raise it on our [Issue Tracker](https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues)
  * Clearly describe the issue including steps to reproduce the bug.
  * Make sure you fill in the earliest version that you know has the issue as well as the version of WordPress you're using.

## Making Changes

* Fork the repository on GitHub
* Make the changes to your forked repository
  * Ensure you stick to the [WordPress Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards)
* When committing, reference your issue (if present) and include a note about the fix
* If possible, and if applicable, please also add/update unit tests for your changes
* Push the changes to your fork and submit a pull request to the 'master' branch of the EDD repository

## Code Documentation

* We ensure that every EDD function is documented well and follows the standards set by phpDoc
* An example function can be found [here](https://gist.github.com/sunnyratilal/5308969)
* Please make sure that every function is documented so that when we update our API Documentation things don't go awry!
	* If you're adding/editing a function in a class, make sure to add `@access {private|public|protected}`
* Finally, please use tabs and not spaces. The tab indent size should be 4 for all EDD code.

At this point you're waiting on us to merge your pull request. We'll review all pull requests, and make suggestions and changes if necessary.

# Additional Resources
* [EDD Developer's API](https://easydigitaldownloads.com/docs/developers-intro-to-easy-digital-downloads/)
* [General GitHub Documentation](http://help.github.com/)
* [GitHub Pull Request documentation](http://help.github.com/send-pull-requests/)
* [PHPUnit Tests Guide](http://phpunit.de/manual/current/en/writing-tests-for-phpunit.html)
