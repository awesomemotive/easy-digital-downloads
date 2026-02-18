=== Action Scheduler ===
Contributors: Automattic, wpmuguru, claudiosanches, peterfabian1000, vedjain, jamosova, obliviousharmony, konamiman, sadowski, royho, barryhughes-1
Tags: scheduler, cron
Stable tag: 3.9.3
License: GPLv3
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.2

Action Scheduler - Job Queue for WordPress

== Description ==

Action Scheduler is a scalable, traceable job queue for background processing large sets of actions in WordPress. It's specially designed to be distributed in WordPress plugins.

Action Scheduler works by triggering an action hook to run at some time in the future. Each hook can be scheduled with unique data, to allow callbacks to perform operations on that data. The hook can also be scheduled to run on one or more occasions.

Think of it like an extension to `do_action()` which adds the ability to delay and repeat a hook.

## Battle-Tested Background Processing

Every month, Action Scheduler processes millions of payments for [Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/), webhooks for [WooCommerce](https://wordpress.org/plugins/woocommerce/), as well as emails and other events for a range of other plugins.

It's been seen on live sites processing queues in excess of 50,000 jobs and doing resource intensive operations, like processing payments and creating orders, at a sustained rate of over 10,000 / hour without negatively impacting normal site operations.

This is all on infrastructure and WordPress sites outside the control of the plugin author.

If your plugin needs background processing, especially of large sets of tasks, Action Scheduler can help.

## Learn More

To learn more about how Action Scheduler works, and how to use it in your plugin, check out the docs on [ActionScheduler.org](https://actionscheduler.org).

There you will find:

* [Usage guide](https://actionscheduler.org/usage/): instructions on installing and using Action Scheduler
* [WP CLI guide](https://actionscheduler.org/wp-cli/): instructions on running Action Scheduler at scale via WP CLI
* [API Reference](https://actionscheduler.org/api/): complete reference guide for all API functions
* [Administration Guide](https://actionscheduler.org/admin/): guide to managing scheduled actions via the administration screen
* [Guide to Background Processing at Scale](https://actionscheduler.org/perf/): instructions for running Action Scheduler at scale via the default WP Cron queue runner

## Credits

Action Scheduler is developed and maintained by [Automattic](http://automattic.com/) with significant early development completed by [Flightless](https://flightless.us/).

Collaboration is cool. We'd love to work with you to improve Action Scheduler. [Pull Requests](https://github.com/woocommerce/action-scheduler/pulls) welcome.

== Changelog ==

= 3.9.3 - 2025-07-15 =
* Add hook 'action_scheduler_ensure_recurring_actions' specifically for scheduling recurring actions.
* Assume an action is valid until proven otherwise.
* Implement SKIP LOCKED during action claiming.
* Import `get_flag_value()` from `WP_CLI\Utils` before using.
* Make `$unique` available to all pre-creation/short-circuit hooks.
* Make version/source information available via new class.
* Only release claims on pending actions.
* Tweak - WP 6.8 compatibility.
* Update minimum supported php and phpunit versions.
* Update readme.txt.
* WP CLI get action command: correct parentheses/nesting of conditional checks.

= 3.9.2 - 2025-02-03 =
* Fixed fatal errors by moving version info methods to a new class and deprecating conflicting ones in ActionScheduler_Versions

= 3.9.1 - 2025-01-21 =
* A number of new WP CLI commands have been added, making it easier to manage actions in the terminal and from scripts.
* New wp action-scheduler source command to help determine how Action Scheduler is being loaded.
* Additional information about the active instance of Action Scheduler is now available in the Help pull-down drawer.
* Make some other nullable parameters explicitly nullable.
* Set option value to `no` rather than deleting.

= 3.9.0 - 2024-11-14 =  
* Minimum required version of PHP is now 7.1.  
* Performance improvements for the `as_pending_actions_due()` function.  
* Existing filter hook `action_scheduler_claim_actions_order_by` enhanced to provide callbacks with additional information.  
* Improved compatibility with PHP 8.4, specifically by making implicitly nullable parameters explicitly nullable.  
* A large number of coding standards-enhancements, to help reduce friction when submitting plugins to marketplaces and plugin directories. Special props @crstauf for this effort.  
* Minor documentation tweaks and improvements.

= 3.8.2 - 2024-09-12 =
* Add missing parameter to the `pre_as_enqueue_async_action` hook.
* Bump minimum PHP version to 7.0.
* Bump minimum WordPress version to 6.4.
* Make the batch size adjustable during processing.

= 3.8.1 - 2024-06-20 =
* Fix typos.
* Improve the messaging in our unidentified action exceptions.

= 3.8.0 - 2024-05-22 =
* Documentation - Fixed typos in perf.md.
* Update - We now require WordPress 6.3 or higher.
* Update - We now require PHP 7.0 or higher.

= 3.7.4 - 2024-04-05 =
* Give a clear description of how the $unique parameter works.
* Preserve the tab field if set.
* Tweak - WP 6.5 compatibility.

= 3.7.3 - 2024-03-20 =
* Do not iterate over all of GET when building form in list table.
* Fix a few issues reported by PCP (Plugin Check Plugin).
* Try to save actions as unique even when the store doesn't support it.
* Tweak - WP 6.4 compatibility.
* Update "Tested up to" tag to WordPress 6.5.
* update version in package-lock.json.

= 3.7.2 - 2024-02-14 =
* No longer user variables in `_n()` translation function.

= 3.7.1 - 2023-12-13 =
* update semver to 5.7.2 because of a security vulnerability in 5.7.1.

= 3.7.0 - 2023-11-20 =
* Important: starting with this release, Action Scheduler follows an L-2 version policy (WordPress, and consequently PHP).
* Add extended indexes for hook_status_scheduled_date_gmt and status_scheduled_date_gmt.
* Catch and log exceptions thrown when actions can't be created, e.g. under a corrupt database schema.
* Tweak - WP 6.4 compatibility.
* Update unit tests for upcoming dependency version policy.
* make sure hook action_scheduler_failed_execution can access original exception object.
* mention dependency version policy in usage.md.

= 3.6.4 - 2023-10-11 =
* Performance improvements when bulk cancelling actions.
* Dev-related fixes.

= 3.6.3 - 2023-09-13 =
* Use `_doing_it_wrong` in initialization check.

= 3.6.2 - 2023-08-09 =
* Add guidance about passing arguments.
* Atomic option locking.
* Improve bulk delete handling.
* Include database error in the exception message.
* Tweak - WP 6.3 compatibility.

= 3.6.1 - 2023-06-14 =
* Document new optional `$priority` arg for various API functions.
* Document the new `--exclude-groups` WP CLI option.
* Document the new `action_scheduler_init` hook.
* Ensure actions within each claim are executed in the expected order.
* Fix incorrect text domain.
* Remove SHOW TABLES usage when checking if tables exist.

= 3.6.0 - 2023-05-10 =
* Add $unique parameter to function signatures.
* Add a cast-to-int for extra safety before forming new DateTime object.
* Add a hook allowing exceptions for consistently failing recurring actions.
* Add action priorities.
* Add init hook.
* Always raise the time limit.
* Bump minimatch from 3.0.4 to 3.0.8.
* Bump yaml from 2.2.1 to 2.2.2.
* Defensive coding relating to gaps in declared schedule types.
* Do not process an action if it cannot be set to `in-progress`.
* Filter view labels (status names) should be translatable | #919.
* Fix WPCLI progress messages.
* Improve data-store initialization flow.
* Improve error handling across all supported PHP versions.
* Improve logic for flushing the runtime cache.
* Support exclusion of multiple groups.
* Update lint-staged and Node/NPM requirements.
* add CLI clean command.
* add CLI exclude-group filter.
* exclude past-due from list table all filter count.
* throwing an exception if as_schedule_recurring_action interval param is not of type integer.

= 3.5.4 - 2023-01-17 =
* Add pre filters during action registration.
* Async scheduling.
* Calculate timeouts based on total actions.
* Correctly order the parameters for `ActionScheduler_ActionFactory`'s calls to `single_unique`.
* Fetch action in memory first before releasing claim to avoid deadlock.
* PHP 8.2: declare property to fix creation of dynamic property warning.
* PHP 8.2: fix "Using ${var} in strings is deprecated, use {$var} instead".
* Prevent `undefined variable` warning for `$num_pastdue_actions`.

= 3.5.3 - 2022-11-09 =
* Query actions with partial match.

= 3.5.2 - 2022-09-16 =
* Fix - erroneous 3.5.1 release.

= 3.5.1 - 2022-09-13 =
* Maintenance on A/S docs.
* fix: PHP 8.2 deprecated notice.

= 3.5.0 - 2022-08-25 =
* Add - The active view link within the "Tools > Scheduled Actions" screen is now clickable.
* Add - A warning when there are past-due actions.
* Enhancement - Added the ability to schedule unique actions via an atomic operation.
* Enhancement - Improvements to cache invalidation when processing batches (when running on WordPress 6.0+).
* Enhancement - If a recurring action is found to be consistently failing, it will stop being rescheduled.
* Enhancement - Adds a new "Past Due" view to the scheduled actions list table.

= 3.4.2 - 2022-06-08 =
* Fix - Change the include for better linting.
* Fix - update: Added Action scheduler completed action hook.

= 3.4.1 - 2022-05-24 =
* Fix - Change the include for better linting.
* Fix - Fix the documented return type.

= 3.4.0 - 2021-10-29 =
* Enhancement - Number of items per page can now be set for the Scheduled Actions view (props @ovidiul). #771
* Fix - Do not lower the max_execution_time if it is already set to 0 (unlimited) (props @barryhughes). #755
* Fix - Avoid triggering autoloaders during the version resolution process (props @olegabr). #731 & #776
* Dev - ActionScheduler_wcSystemStatus PHPCS fixes (props @ovidiul). #761
* Dev - ActionScheduler_DBLogger.php PHPCS fixes (props @ovidiul). #768
* Dev - Fixed phpcs for ActionScheduler_Schedule_Deprecated (props @ovidiul). #762
* Dev - Improve actions table indices (props @glagonikas). #774 & #777
* Dev - PHPCS fixes for ActionScheduler_DBStore.php (props @ovidiul). #769 & #778
* Dev - PHPCS Fixes for ActionScheduler_Abstract_ListTable (props @ovidiul). #763 & #779
* Dev - Adds new filter action_scheduler_claim_actions_order_by to allow tuning of the claim query (props @glagonikas). #773
* Dev - PHPCS fixes for ActionScheduler_WpPostStore class (props @ovidiul). #780

= 3.3.0 - 2021-09-15 =
* Enhancement - Adds as_has_scheduled_action() to provide a performant way to test for existing actions. #645
* Fix - Improves compatibility with environments where NO_ZERO_DATE is enabled. #519
* Fix - Adds safety checks to guard against errors when our database tables cannot be created. #645
* Dev - Now supports queries that use multiple statuses. #649
* Dev - Minimum requirements for WordPress and PHP bumped (to 5.2 and 5.6 respectively). #723

= 3.2.1 - 2021-06-21 =
* Fix - Add extra safety/account for different versions of AS and different loading patterns. #714
* Fix - Handle hidden columns (Tools → Scheduled Actions) | #600.

= 3.2.0 - 2021-06-03 =
* Fix - Add "no ordering" option to as_next_scheduled_action().
* Fix - Add secondary scheduled date checks when claiming actions (DBStore) | #634.
* Fix - Add secondary scheduled date checks when claiming actions (wpPostStore) | #634.
* Fix - Adds a new index to the action table, reducing the potential for deadlocks (props: @glagonikas).
* Fix - Fix unit tests infrastructure and adapt tests to PHP 8.
* Fix - Identify in-use data store.
* Fix - Improve test_migration_is_scheduled.
* Fix - PHP notice on list table.
* Fix - Speed up clean up and batch selects.
* Fix - Update pending dependencies.
* Fix - [PHP 8.0] Only pass action arg values through to do_action_ref_array().
* Fix - [PHP 8] Set the PHP version to 7.1 in composer.json for PHP 8 compatibility.
* Fix - add is_initialized() to docs.
* Fix - fix file permissions.
* Fix - fixes #664 by replacing __ with esc_html__.
