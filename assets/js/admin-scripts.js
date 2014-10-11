jQuery(document).ready(function ($) {
	
// Start easytabs()
if ( $( ".mashsb-tabs" ).length ) {
$('#tab_container').easytabs({
    animate:false
});
}

/*
 * jQuery hashchange event - v1.3 - 7/21/2010
 * http://benalman.com/projects/jquery-hashchange-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 
 * This is optional and allows browser back and forward buttons on jQuery easytabs()
 */
(function($,e,b){var c="hashchange",h=document,f,g=$.event.special,i=h.documentMode,d="on"+c in e&&(i===b||i>7);function a(j){j=j||location.href;return"#"+j.replace(/^[^#]*#?(.*)$/,"$1")}$.fn[c]=function(j){return j?this.bind(c,j):this.trigger(c)};$.fn[c].delay=50;g[c]=$.extend(g[c],{setup:function(){if(d){return false}$(f.start)},teardown:function(){if(d){return false}$(f.stop)}});f=(function(){var j={},p,m=a(),k=function(q){return q},l=k,o=k;j.start=function(){p||n()};j.stop=function(){p&&clearTimeout(p);p=b};function n(){var r=a(),q=o(m);if(r!==m){l(m=r,q);$(e).trigger(c)}else{if(q!==m){location.href=location.href.replace(/#.*/,"")+q}}p=setTimeout(n,$.fn[c].delay)}$.browser.msie&&!d&&(function(){var q,r;j.start=function(){if(!q){r=$.fn[c].src;r=r&&r+a();q=$('<iframe tabindex="-1" title="empty"/>').hide().one("load",function(){r||l(a());n()}).attr("src",r||"javascript:0").insertAfter("body")[0].contentWindow;h.onpropertychange=function(){try{if(event.propertyName==="title"){q.document.title=h.title}}catch(s){}}}};j.stop=k;o=function(){return a(q.location.href)};l=function(v,s){var u=q.document,t=$.fn[c].domain;if(v!==s){u.title=h.title;u.open();t&&u.write('<script>document.domain="'+t+'"<\/script>');u.close();q.location.hash=v}}})();return j})()})(jQuery,this);

/*
 * jQuery EasyTabs plugin 3.2.0
 *
 * Copyright (c) 2010-2011 Steve Schwartz (JangoSteve)
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Date: Thu May 09 17:30:00 2013 -0500
 */
( function($) {

  $.easytabs = function(container, options) {

        // Attach to plugin anything that should be available via
        // the $container.data('easytabs') object
    var plugin = this,
        $container = $(container),

        defaults = {
          animate: true,
          panelActiveClass: "active",
          tabActiveClass: "active",
          defaultTab: "li:first-child",
          animationSpeed: "normal",
          tabs: "> ul > li",
          updateHash: true,
          cycle: false,
          collapsible: false,
          collapsedClass: "collapsed",
          collapsedByDefault: true,
          uiTabs: false,
          transitionIn: 'fadeIn',
          transitionOut: 'fadeOut',
          transitionInEasing: 'swing',
          transitionOutEasing: 'swing',
          transitionCollapse: 'slideUp',
          transitionUncollapse: 'slideDown',
          transitionCollapseEasing: 'swing',
          transitionUncollapseEasing: 'swing',
          containerClass: "",
          tabsClass: "",
          tabClass: "",
          panelClass: "",
          cache: true,
          event: 'click',
          panelContext: $container
        },

        // Internal instance variables
        // (not available via easytabs object)
        $defaultTab,
        $defaultTabLink,
        transitions,
        lastHash,
        skipUpdateToHash,
        animationSpeeds = {
          fast: 200,
          normal: 400,
          slow: 600
        },

        // Shorthand variable so that we don't need to call
        // plugin.settings throughout the plugin code
        settings;

    // =============================================================
    // Functions available via easytabs object
    // =============================================================

    plugin.init = function() {

      plugin.settings = settings = $.extend({}, defaults, options);
      settings.bind_str = settings.event+".easytabs";

      // Add jQuery UI's crazy class names to markup,
      // so that markup will match theme CSS
      if ( settings.uiTabs ) {
        settings.tabActiveClass = 'ui-tabs-selected';
        settings.containerClass = 'ui-tabs ui-widget ui-widget-content ui-corner-all';
        settings.tabsClass = 'ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all';
        settings.tabClass = 'ui-state-default ui-corner-top';
        settings.panelClass = 'ui-tabs-panel ui-widget-content ui-corner-bottom';
      }

      // If collapsible is true and defaultTab specified, assume user wants defaultTab showing (not collapsed)
      if ( settings.collapsible && options.defaultTab !== undefined && options.collpasedByDefault === undefined ) {
        settings.collapsedByDefault = false;
      }

      // Convert 'normal', 'fast', and 'slow' animation speed settings to their respective speed in milliseconds
      if ( typeof(settings.animationSpeed) === 'string' ) {
        settings.animationSpeed = animationSpeeds[settings.animationSpeed];
      }

      $('a.anchor').remove().prependTo('body');

      // Store easytabs object on container so we can easily set
      // properties throughout
      $container.data('easytabs', {});

      plugin.setTransitions();

      plugin.getTabs();

      addClasses();

      setDefaultTab();

      bindToTabClicks();

      initHashChange();

      initCycle();

      // Append data-easytabs HTML attribute to make easy to query for
      // easytabs instances via CSS pseudo-selector
      $container.attr('data-easytabs', true);
    };

    // Set transitions for switching between tabs based on options.
    // Could be used to update transitions if settings are changes.
    plugin.setTransitions = function() {
      transitions = ( settings.animate ) ? {
          show: settings.transitionIn,
          hide: settings.transitionOut,
          speed: settings.animationSpeed,
          collapse: settings.transitionCollapse,
          uncollapse: settings.transitionUncollapse,
          halfSpeed: settings.animationSpeed / 2
        } :
        {
          show: "show",
          hide: "hide",
          speed: 0,
          collapse: "hide",
          uncollapse: "show",
          halfSpeed: 0
        };
    };

    // Find and instantiate tabs and panels.
    // Could be used to reset tab and panel collection if markup is
    // modified.
    plugin.getTabs = function() {
      var $matchingPanel;

      // Find the initial set of elements matching the setting.tabs
      // CSS selector within the container
      plugin.tabs = $container.find(settings.tabs),

      // Instantiate panels as empty jquery object
      plugin.panels = $(),

      plugin.tabs.each(function(){
        var $tab = $(this),
            $a = $tab.children('a'),

            // targetId is the ID of the panel, which is either the
            // `href` attribute for non-ajax tabs, or in the
            // `data-target` attribute for ajax tabs since the `href` is
            // the ajax URL
            targetId = $tab.children('a').data('target');

        $tab.data('easytabs', {});

        // If the tab has a `data-target` attribute, and is thus an ajax tab
        if ( targetId !== undefined && targetId !== null ) {
          $tab.data('easytabs').ajax = $a.attr('href');
        } else {
          targetId = $a.attr('href');
        }
        targetId = targetId.match(/#([^\?]+)/)[1];

        $matchingPanel = settings.panelContext.find("#" + targetId);

        // If tab has a matching panel, add it to panels
        if ( $matchingPanel.length ) {

          // Store panel height before hiding
          $matchingPanel.data('easytabs', {
            position: $matchingPanel.css('position'),
            visibility: $matchingPanel.css('visibility')
          });

          // Don't hide panel if it's active (allows `getTabs` to be called manually to re-instantiate tab collection)
          $matchingPanel.not(settings.panelActiveClass).hide();

          plugin.panels = plugin.panels.add($matchingPanel);

          $tab.data('easytabs').panel = $matchingPanel;

        // Otherwise, remove tab from tabs collection
        } else {
          plugin.tabs = plugin.tabs.not($tab);
          if ('console' in window) {
            console.warn('Warning: tab without matching panel for selector \'#' + targetId +'\' removed from set');
          }
        }
      });
    };

    // Select tab and fire callback
    plugin.selectTab = function($clicked, callback) {
      var url = window.location,
          hash = url.hash.match(/^[^\?]*/)[0],
          $targetPanel = $clicked.parent().data('easytabs').panel,
          ajaxUrl = $clicked.parent().data('easytabs').ajax;

      // Tab is collapsible and active => toggle collapsed state
      if( settings.collapsible && ! skipUpdateToHash && ($clicked.hasClass(settings.tabActiveClass) || $clicked.hasClass(settings.collapsedClass)) ) {
        plugin.toggleTabCollapse($clicked, $targetPanel, ajaxUrl, callback);

      // Tab is not active and panel is not active => select tab
      } else if( ! $clicked.hasClass(settings.tabActiveClass) || ! $targetPanel.hasClass(settings.panelActiveClass) ){
        activateTab($clicked, $targetPanel, ajaxUrl, callback);

      // Cache is disabled => reload (e.g reload an ajax tab).
      } else if ( ! settings.cache ){
        activateTab($clicked, $targetPanel, ajaxUrl, callback);
      }

    };

    // Toggle tab collapsed state and fire callback
    plugin.toggleTabCollapse = function($clicked, $targetPanel, ajaxUrl, callback) {
      plugin.panels.stop(true,true);

      if( fire($container,"easytabs:before", [$clicked, $targetPanel, settings]) ){
        plugin.tabs.filter("." + settings.tabActiveClass).removeClass(settings.tabActiveClass).children().removeClass(settings.tabActiveClass);

        // If panel is collapsed, uncollapse it
        if( $clicked.hasClass(settings.collapsedClass) ){

          // If ajax panel and not already cached
          if( ajaxUrl && (!settings.cache || !$clicked.parent().data('easytabs').cached) ) {
            $container.trigger('easytabs:ajax:beforeSend', [$clicked, $targetPanel]);

            $targetPanel.load(ajaxUrl, function(response, status, xhr){
              $clicked.parent().data('easytabs').cached = true;
              $container.trigger('easytabs:ajax:complete', [$clicked, $targetPanel, response, status, xhr]);
            });
          }

          // Update CSS classes of tab and panel
          $clicked.parent()
            .removeClass(settings.collapsedClass)
            .addClass(settings.tabActiveClass)
            .children()
              .removeClass(settings.collapsedClass)
              .addClass(settings.tabActiveClass);

          $targetPanel
            .addClass(settings.panelActiveClass)
            [transitions.uncollapse](transitions.speed, settings.transitionUncollapseEasing, function(){
              $container.trigger('easytabs:midTransition', [$clicked, $targetPanel, settings]);
              if(typeof callback == 'function') callback();
            });

        // Otherwise, collapse it
        } else {

          // Update CSS classes of tab and panel
          $clicked.addClass(settings.collapsedClass)
            .parent()
              .addClass(settings.collapsedClass);

          $targetPanel
            .removeClass(settings.panelActiveClass)
            [transitions.collapse](transitions.speed, settings.transitionCollapseEasing, function(){
              $container.trigger("easytabs:midTransition", [$clicked, $targetPanel, settings]);
              if(typeof callback == 'function') callback();
            });
        }
      }
    };


    // Find tab with target panel matching value
    plugin.matchTab = function(hash) {
      return plugin.tabs.find("[href='" + hash + "'],[data-target='" + hash + "']").first();
    };

    // Find panel with `id` matching value
    plugin.matchInPanel = function(hash) {
      return ( hash && plugin.validId(hash) ? plugin.panels.filter(':has(' + hash + ')').first() : [] );
    };

    // Make sure hash is a valid id value (admittedly strict in that HTML5 allows almost anything without a space)
    // but jQuery has issues with such id values anyway, so we can afford to be strict here.
    plugin.validId = function(id) {
      return id.substr(1).match(/^[A-Za-z]+[A-Za-z0-9\-_:\.].$/);
    };

    // Select matching tab when URL hash changes
    plugin.selectTabFromHashChange = function() {
      var hash = window.location.hash.match(/^[^\?]*/)[0],
          $tab = plugin.matchTab(hash),
          $panel;

      if ( settings.updateHash ) {

        // If hash directly matches tab
        if( $tab.length ){
          skipUpdateToHash = true;
          plugin.selectTab( $tab );

        } else {
          $panel = plugin.matchInPanel(hash);

          // If panel contains element matching hash
          if ( $panel.length ) {
            hash = '#' + $panel.attr('id');
            $tab = plugin.matchTab(hash);
            skipUpdateToHash = true;
            plugin.selectTab( $tab );

          // If default tab is not active...
          } else if ( ! $defaultTab.hasClass(settings.tabActiveClass) && ! settings.cycle ) {

            // ...and hash is blank or matches a parent of the tab container or
            // if the last tab (before the hash updated) was one of the other tabs in this container.
            if ( hash === '' || plugin.matchTab(lastHash).length || $container.closest(hash).length ) {
              skipUpdateToHash = true;
              plugin.selectTab( $defaultTabLink );
            }
          }
        }
      }
    };

    // Cycle through tabs
    plugin.cycleTabs = function(tabNumber){
      if(settings.cycle){
        tabNumber = tabNumber % plugin.tabs.length;
        $tab = $( plugin.tabs[tabNumber] ).children("a").first();
        skipUpdateToHash = true;
        plugin.selectTab( $tab, function() {
          setTimeout(function(){ plugin.cycleTabs(tabNumber + 1); }, settings.cycle);
        });
      }
    };

    // Convenient public methods
    plugin.publicMethods = {
      select: function(tabSelector){
        var $tab;

        // Find tab container that matches selector (like 'li#tab-one' which contains tab link)
        if ( ($tab = plugin.tabs.filter(tabSelector)).length === 0 ) {

          // Find direct tab link that matches href (like 'a[href="#panel-1"]')
          if ( ($tab = plugin.tabs.find("a[href='" + tabSelector + "']")).length === 0 ) {

            // Find direct tab link that matches selector (like 'a#tab-1')
            if ( ($tab = plugin.tabs.find("a" + tabSelector)).length === 0 ) {

              // Find direct tab link that matches data-target (lik 'a[data-target="#panel-1"]')
              if ( ($tab = plugin.tabs.find("[data-target='" + tabSelector + "']")).length === 0 ) {

                // Find direct tab link that ends in the matching href (like 'a[href$="#panel-1"]', which would also match http://example.com/currentpage/#panel-1)
                if ( ($tab = plugin.tabs.find("a[href$='" + tabSelector + "']")).length === 0 ) {

                  $.error('Tab \'' + tabSelector + '\' does not exist in tab set');
                }
              }
            }
          }
        } else {
          // Select the child tab link, since the first option finds the tab container (like <li>)
          $tab = $tab.children("a").first();
        }
        plugin.selectTab($tab);
      }
    };

    // =============================================================
    // Private functions
    // =============================================================

    // Triggers an event on an element and returns the event result
    var fire = function(obj, name, data) {
      var event = $.Event(name);
      obj.trigger(event, data);
      return event.result !== false;
    }

    // Add CSS classes to markup (if specified), called by init
    var addClasses = function() {
      $container.addClass(settings.containerClass);
      plugin.tabs.parent().addClass(settings.tabsClass);
      plugin.tabs.addClass(settings.tabClass);
      plugin.panels.addClass(settings.panelClass);
    };

    // Set the default tab, whether from hash (bookmarked) or option,
    // called by init
    var setDefaultTab = function(){
      var hash = window.location.hash.match(/^[^\?]*/)[0],
          $selectedTab = plugin.matchTab(hash).parent(),
          $panel;

      // If hash directly matches one of the tabs, active on page-load
      if( $selectedTab.length === 1 ){
        $defaultTab = $selectedTab;
        settings.cycle = false;

      } else {
        $panel = plugin.matchInPanel(hash);

        // If one of the panels contains the element matching the hash,
        // make it active on page-load
        if ( $panel.length ) {
          hash = '#' + $panel.attr('id');
          $defaultTab = plugin.matchTab(hash).parent();

        // Otherwise, make the default tab the one that's active on page-load
        } else {
          $defaultTab = plugin.tabs.parent().find(settings.defaultTab);
          if ( $defaultTab.length === 0 ) {
            $.error("The specified default tab ('" + settings.defaultTab + "') could not be found in the tab set ('" + settings.tabs + "') out of " + plugin.tabs.length + " tabs.");
          }
        }
      }

      $defaultTabLink = $defaultTab.children("a").first();

      activateDefaultTab($selectedTab);
    };

    // Activate defaultTab (or collapse by default), called by setDefaultTab
    var activateDefaultTab = function($selectedTab) {
      var defaultPanel,
          defaultAjaxUrl;

      if ( settings.collapsible && $selectedTab.length === 0 && settings.collapsedByDefault ) {
        $defaultTab
          .addClass(settings.collapsedClass)
          .children()
            .addClass(settings.collapsedClass);

      } else {

        defaultPanel = $( $defaultTab.data('easytabs').panel );
        defaultAjaxUrl = $defaultTab.data('easytabs').ajax;

        if ( defaultAjaxUrl && (!settings.cache || !$defaultTab.data('easytabs').cached) ) {
          $container.trigger('easytabs:ajax:beforeSend', [$defaultTabLink, defaultPanel]);
          defaultPanel.load(defaultAjaxUrl, function(response, status, xhr){
            $defaultTab.data('easytabs').cached = true;
            $container.trigger('easytabs:ajax:complete', [$defaultTabLink, defaultPanel, response, status, xhr]);
          });
        }

        $defaultTab.data('easytabs').panel
          .show()
          .addClass(settings.panelActiveClass);

        $defaultTab
          .addClass(settings.tabActiveClass)
          .children()
            .addClass(settings.tabActiveClass);
      }

      // Fire event when the plugin is initialised
      $container.trigger("easytabs:initialised", [$defaultTabLink, defaultPanel]);
    };

    // Bind tab-select funtionality to namespaced click event, called by
    // init
    var bindToTabClicks = function() {
      plugin.tabs.children("a").bind(settings.bind_str, function(e) {

        // Stop cycling when a tab is clicked
        settings.cycle = false;

        // Hash will be updated when tab is clicked,
        // don't cause tab to re-select when hash-change event is fired
        skipUpdateToHash = false;

        // Select the panel for the clicked tab
        plugin.selectTab( $(this) );

        // Don't follow the link to the anchor
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
      });
    };

    // Activate a given tab/panel, called from plugin.selectTab:
    //
    //   * fire `easytabs:before` hook
    //   * get ajax if new tab is an uncached ajax tab
    //   * animate out previously-active panel
    //   * fire `easytabs:midTransition` hook
    //   * update URL hash
    //   * animate in newly-active panel
    //   * update CSS classes for inactive and active tabs/panels
    //
    // TODO: This could probably be broken out into many more modular
    // functions
    var activateTab = function($clicked, $targetPanel, ajaxUrl, callback) {
      plugin.panels.stop(true,true);

      if( fire($container,"easytabs:before", [$clicked, $targetPanel, settings]) ){
        var $visiblePanel = plugin.panels.filter(":visible"),
            $panelContainer = $targetPanel.parent(),
            targetHeight,
            visibleHeight,
            heightDifference,
            showPanel,
            hash = window.location.hash.match(/^[^\?]*/)[0];

        if (settings.animate) {
          targetHeight = getHeightForHidden($targetPanel);
          visibleHeight = $visiblePanel.length ? setAndReturnHeight($visiblePanel) : 0;
          heightDifference = targetHeight - visibleHeight;
        }

        // Set lastHash to help indicate if defaultTab should be
        // activated across multiple tab instances.
        lastHash = hash;

        // TODO: Move this function elsewhere
        showPanel = function() {
          // At this point, the previous panel is hidden, and the new one will be selected
          $container.trigger("easytabs:midTransition", [$clicked, $targetPanel, settings]);

          // Gracefully animate between panels of differing heights, start height change animation *after* panel change if panel needs to contract,
          // so that there is no chance of making the visible panel overflowing the height of the target panel
          if (settings.animate && settings.transitionIn == 'fadeIn') {
            if (heightDifference < 0)
              $panelContainer.animate({
                height: $panelContainer.height() + heightDifference
              }, transitions.halfSpeed ).css({ 'min-height': '' });
          }

          if ( settings.updateHash && ! skipUpdateToHash ) {
            //window.location = url.toString().replace((url.pathname + hash), (url.pathname + $clicked.attr("href")));
            // Not sure why this behaves so differently, but it's more straight forward and seems to have less side-effects
            window.location.hash = '#' + $targetPanel.attr('id');
          } else {
            skipUpdateToHash = false;
          }

          $targetPanel
            [transitions.show](transitions.speed, settings.transitionInEasing, function(){
              $panelContainer.css({height: '', 'min-height': ''}); // After the transition, unset the height
              $container.trigger("easytabs:after", [$clicked, $targetPanel, settings]);
              // callback only gets called if selectTab actually does something, since it's inside the if block
              if(typeof callback == 'function'){
                callback();
              }
          });
        };

        if ( ajaxUrl && (!settings.cache || !$clicked.parent().data('easytabs').cached) ) {
          $container.trigger('easytabs:ajax:beforeSend', [$clicked, $targetPanel]);
          $targetPanel.load(ajaxUrl, function(response, status, xhr){
            $clicked.parent().data('easytabs').cached = true;
            $container.trigger('easytabs:ajax:complete', [$clicked, $targetPanel, response, status, xhr]);
          });
        }

        // Gracefully animate between panels of differing heights, start height change animation *before* panel change if panel needs to expand,
        // so that there is no chance of making the target panel overflowing the height of the visible panel
        if( settings.animate && settings.transitionOut == 'fadeOut' ) {
          if( heightDifference > 0 ) {
            $panelContainer.animate({
              height: ( $panelContainer.height() + heightDifference )
            }, transitions.halfSpeed );
          } else {
            // Prevent height jumping before height transition is triggered at midTransition
            $panelContainer.css({ 'min-height': $panelContainer.height() });
          }
        }

        // Change the active tab *first* to provide immediate feedback when the user clicks
        plugin.tabs.filter("." + settings.tabActiveClass).removeClass(settings.tabActiveClass).children().removeClass(settings.tabActiveClass);
        plugin.tabs.filter("." + settings.collapsedClass).removeClass(settings.collapsedClass).children().removeClass(settings.collapsedClass);
        $clicked.parent().addClass(settings.tabActiveClass).children().addClass(settings.tabActiveClass);

        plugin.panels.filter("." + settings.panelActiveClass).removeClass(settings.panelActiveClass);
        $targetPanel.addClass(settings.panelActiveClass);

        if( $visiblePanel.length ) {
          $visiblePanel
            [transitions.hide](transitions.speed, settings.transitionOutEasing, showPanel);
        } else {
          $targetPanel
            [transitions.uncollapse](transitions.speed, settings.transitionUncollapseEasing, showPanel);
        }
      }
    };

    // Get heights of panels to enable animation between panels of
    // differing heights, called by activateTab
    var getHeightForHidden = function($targetPanel){

      if ( $targetPanel.data('easytabs') && $targetPanel.data('easytabs').lastHeight ) {
        return $targetPanel.data('easytabs').lastHeight;
      }

      // this is the only property easytabs changes, so we need to grab its value on each tab change
      var display = $targetPanel.css('display'),
          outerCloak,
          height;

      // Workaround with wrapping height, because firefox returns wrong
      // height if element itself has absolute positioning.
      // but try/catch block needed for IE7 and IE8 because they throw
      // an "Unspecified error" when trying to create an element
      // with the css position set.
      try {
        outerCloak = $('<div></div>', {'position': 'absolute', 'visibility': 'hidden', 'overflow': 'hidden'});
      } catch (e) {
        outerCloak = $('<div></div>', {'visibility': 'hidden', 'overflow': 'hidden'});
      }
      height = $targetPanel
        .wrap(outerCloak)
        .css({'position':'relative','visibility':'hidden','display':'block'})
        .outerHeight();

      $targetPanel.unwrap();

      // Return element to previous state
      $targetPanel.css({
        position: $targetPanel.data('easytabs').position,
        visibility: $targetPanel.data('easytabs').visibility,
        display: display
      });

      // Cache height
      $targetPanel.data('easytabs').lastHeight = height;

      return height;
    };

    // Since the height of the visible panel may have been manipulated due to interaction,
    // we want to re-cache the visible height on each tab change, called
    // by activateTab
    var setAndReturnHeight = function($visiblePanel) {
      var height = $visiblePanel.outerHeight();

      if( $visiblePanel.data('easytabs') ) {
        $visiblePanel.data('easytabs').lastHeight = height;
      } else {
        $visiblePanel.data('easytabs', {lastHeight: height});
      }
      return height;
    };

    // Setup hash-change callback for forward- and back-button
    // functionality, called by init
    var initHashChange = function(){

      // enabling back-button with jquery.hashchange plugin
      // http://benalman.com/projects/jquery-hashchange-plugin/
      if(typeof $(window).hashchange === 'function'){
        $(window).hashchange( function(){
          plugin.selectTabFromHashChange();
        });
      } else if ($.address && typeof $.address.change === 'function') { // back-button with jquery.address plugin http://www.asual.com/jquery/address/docs/
        $.address.change( function(){
          plugin.selectTabFromHashChange();
        });
      }
    };

    // Begin cycling if set in options, called by init
    var initCycle = function(){
      var tabNumber;
      if (settings.cycle) {
        tabNumber = plugin.tabs.index($defaultTab);
        setTimeout( function(){ plugin.cycleTabs(tabNumber + 1); }, settings.cycle);
      }
    };


    plugin.init();

  };

  $.fn.easytabs = function(options) {
    var args = arguments;

    return this.each(function() {
      var $this = $(this),
          plugin = $this.data('easytabs');

      // Initialization was called with $(el).easytabs( { options } );
      if (undefined === plugin) {
        plugin = new $.easytabs(this, options);
        $this.data('easytabs', plugin);
      }

      // User called public method
      if ( plugin.publicMethods[options] ){
        return plugin.publicMethods[options](Array.prototype.slice.call( args, 1 ));
      }
    });
  };

})(jQuery);

	/**
	 * Download Configuration Metabox
	 */
	var EDD_Download_Configuration = {
		init : function() {
			this.add();
			this.move();
			this.remove();
			this.type();
			this.prices();
			this.files();
			this.updatePrices();
		},
		clone_repeatable : function(row) {

			clone = row.clone();

			/** manually update any select box values */
			clone.find( 'select' ).each(function() {
				$( this ).val( row.find( 'select[name="' + $( this ).attr( 'name' ) + '"]' ).val() );
			});

			var count = row.parent().find( 'tr' ).length;

			clone.removeClass( 'edd_add_blank' );

			clone.find( 'td input, td select' ).val( '' );
			clone.find( 'input, select' ).each(function() {
				var name = $( this ).attr( 'name' );

				name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');

				$( this ).attr( 'name', name ).attr( 'id', name );
			});

			clone.find( 'span.edd_price_id' ).each(function() {
				$( this ).text( parseInt( count ) );
			});

			return clone;
		},

		add : function() {
			$( 'body' ).on( 'click', '.submit .edd_add_repeatable', function(e) {
				e.preventDefault();
				var button = $( this ),
				row = button.parent().parent().prev( 'tr' ),
				clone = EDD_Download_Configuration.clone_repeatable(row);
				clone.insertAfter( row );
			});
		},

		move : function() {

			$(".edd_repeatable_table tbody").sortable({
				handle: '.edd_draghandle', items: '.edd_repeatable_row', opacity: 0.6, cursor: 'move', axis: 'y', update: function() {
					var count  = 0;
					$(this).find( 'tr' ).each(function() {
						$(this).find( 'input.edd_repeatable_index' ).each(function() {
							$( this ).val( count );
						});
						count++;
					});
				}
			});
			
		},

		remove : function() {
			$( 'body' ).on( 'click', '.edd_remove_repeatable', function(e) {
				e.preventDefault();

				var row   = $(this).parent().parent( 'tr' ),
					count = row.parent().find( 'tr' ).length - 1,
					type  = $(this).data('type'),
					repeatable = 'tr.edd_repeatable_' + type + 's';

				/** remove from price condition */
			    $( '.edd_repeatable_condition_field option[value=' + row.index() + ']' ).remove();

				if( count > 1 ) {
					$( 'input, select', row ).val( '' );
					row.fadeOut( 'fast' ).remove();
				} else {
					switch( type ) {
						case 'price' :
							alert( edd_vars.one_price_min );
							break;
						case 'file' :
							alert( edd_vars.one_file_min );
							break;
						default:
							alert( edd_vars.one_field_min );
							break;
					}
				}

				/* re-index after deleting */
			    $(repeatable).each( function( rowIndex ) {
			        $(this).find( 'input, select' ).each(function() {
			        	var name = $( this ).attr( 'name' );
			        	name = name.replace( /\[(\d+)\]/, '[' + rowIndex+ ']');
			        	$( this ).attr( 'name', name ).attr( 'id', name );
			    	});
			    });
			});
		},

		type : function() {

			$( 'body' ).on( 'change', '#_edd_product_type', function(e) {

				if ( 'bundle' === $( this ).val() ) {
					$( '#edd_products' ).show();
					$( '#edd_download_files' ).hide();
					$( '#edd_download_limit_wrap' ).hide();
				} else {
					$( '#edd_products' ).hide();
					$( '#edd_download_files' ).show();
					$( '#edd_download_limit_wrap' ).show();
				}

			});

		},

		prices : function() {
			$( 'body' ).on( 'change', '#edd_variable_pricing', function(e) {
				$( '.edd_pricing_fields,.edd_repeatable_table .pricing' ).toggle();
			});
		},

		files : function() {
			if( typeof wp === "undefined" || '1' !== edd_vars.new_media_ui ){
				//Old Thickbox uploader
				if ( $( '.edd_upload_file_button' ).length > 0 ) {
					window.formfield = '';

					$('body').on('click', '.edd_upload_file_button', function(e) {
						e.preventDefault();
						window.formfield = $(this).parent().prev();
						window.tbframe_interval = setInterval(function() {
							jQuery('#TB_iframeContent').contents().find('.savesend .button').val(edd_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
						}, 2000);
						if (edd_vars.post_id != null ) {
							var post_id = 'post_id=' + edd_vars.post_id + '&';
						}
						tb_show(edd_vars.add_new_download, 'media-upload.php?' + post_id +'TB_iframe=true');
					});

					window.edd_send_to_editor = window.send_to_editor;
					window.send_to_editor = function (html) {
						if (window.formfield) {
							imgurl = $('a', '<div>' + html + '</div>').attr('href');
							window.formfield.val(imgurl);
							window.clearInterval(window.tbframe_interval);
							tb_remove();
						} else {
							window.edd_send_to_editor(html);
						}
						window.send_to_editor = window.edd_send_to_editor;
						window.formfield = '';
						window.imagefield = false;
					};
				}
			} else {
				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';

				$('body').on('click', '.edd_upload_file_button', function(e) {

					e.preventDefault();

					var button = $(this);

					window.formfield = $(this).closest('.edd_repeatable_upload_wrapper');

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media( {
						frame: 'post',
						state: 'insert',
						title: button.data( 'uploader_title' ),
						button: {
							text: button.data( 'uploader_button_text' )
						},
						multiple: $( this ).data( 'multiple' ) == '0' ? false : true  // Set to true to allow multiple files to be selected
					} );

					file_frame.on( 'menu:render:default', function( view ) {
						// Store our views in an object.
						var views = {};

						// Unset default menu items
						view.unset( 'library-separator' );
						view.unset( 'gallery' );
						view.unset( 'featured-image' );
						view.unset( 'embed' );

						// Initialize the views in our view object.
						view.set( views );
					} );

					// When an image is selected, run a callback.
					file_frame.on( 'insert', function() {

						var selection = file_frame.state().get('selection');
						selection.each( function( attachment, index ) {
							attachment = attachment.toJSON();
							if ( 0 === index ) {
								// place first attachment in field
								window.formfield.find( '.edd_repeatable_attachment_id_field' ).val( attachment.id );
								window.formfield.find( '.edd_repeatable_upload_field' ).val( attachment.url );
								window.formfield.find( '.edd_repeatable_name_field' ).val( attachment.title );
							} else {
								// Create a new row for all additional attachments
								var row = window.formfield,
									clone = EDD_Download_Configuration.clone_repeatable( row );

								clone.find( '.edd_repeatable_attachment_id_field' ).val( attachment.id );
								clone.find( '.edd_repeatable_upload_field' ).val( attachment.url );
								if ( attachment.title.length > 0 ) {
									clone.find( '.edd_repeatable_name_field' ).val( attachment.title );
								} else {
									clone.find( '.edd_repeatable_name_field' ).val( attachment.filename );
								}
								clone.insertAfter( row );
							}
						});
					});

					// Finally, open the modal
					file_frame.open();
				});


				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';
			}

		},

		updatePrices: function() {
			$( '#edd_price_fields' ).on( 'keyup', '.edd_variable_prices_name', function() {

				var key = $( this ).parents( 'tr' ).index(),
					name = $( this ).val(),
					field_option = $( '.edd_repeatable_condition_field option[value=' + key + ']' );

				if ( field_option.length > 0 ) {
					field_option.text( name );
				} else {
					$( '.edd_repeatable_condition_field' ).append(
						$( '<option></option>' )
							.attr( 'value', key )
							.text( name )
					);
				}
			} );
		}

	};

	EDD_Download_Configuration.init();

	//$('#edit-slug-box').remove();

	// Date picker
	if ( $( '.edd_datepicker' ).length > 0 ) {
		var dateFormat = 'mm/dd/yy';
		$( '.edd_datepicker' ).datepicker( {
			dateFormat: dateFormat
		} );
	}

	/**
	 * Edit payment screen JS
	 */
	var EDD_Edit_Payment = {

		init : function() {
			this.edit_address();
			this.remove_download();
			this.add_download();
			this.recalculate_total();
			this.variable_prices_check();
			this.add_note();
			this.remove_note();
			this.resend_receipt();
			this.copy_download_link();
		},


		edit_address : function() {

			// Update base state field based on selected base country
			$('select[name="edd-payment-address[0][country]"]').change(function() {
				var $this = $(this);
				data = {
					action: 'edd_get_shop_states',
					country: $this.val(),
					field_name: 'edd-payment-address[0][state]'
				};
				$.post(ajaxurl, data, function (response) {
					if( 'nostates' == response ) {
						$('#edd-order-address-state-wrap select, #edd-order-address-state-wrap input').replaceWith( '<input type="text" name="edd-payment-address[0][state]" value="" class="edd-edit-toggles medium-text"/>' );
					} else {
						$('#edd-order-address-state-wrap select, #edd-order-address-state-wrap input').replaceWith( response );
					}
				});

				return false;
			});

		},

		remove_download : function() {

			// Remove a download from a purchase
			$('#edd-purchased-files').on('click', '.edd-order-remove-download', function() {
				if( confirm( edd_vars.delete_payment_download ) ) {
					$(this).parent().parent().parent().remove();
					// Flag the Downloads section as changed
					$('#edd-payment-downloads-changed').val(1);
					$('.edd-order-payment-recalc-totals').show();
				}
				return false;
			});

		},


		add_download : function() {

			// Add a New Download from the Add Downloads to Purchase Box
			$('#edd-purchased-files').on('click', '#edd-order-add-download', function(e) {

				e.preventDefault();

				var download_id    = $('#edd_order_download_select').val();
				var download_title = $('.chosen-single span').text();
				var amount         = $('#edd-order-download-amount').val();
				var price_id       = $('.edd_price_options_select option:selected').val();
				var price_name     = $('.edd_price_options_select option:selected').text();
				var quantity       = $('#edd-order-download-quantity').val();

				if( download_id < 1 ) {
					return false;
				}

				if( ! amount ) {
					amount = '0.00';
				}

				var formatted_amount = amount + edd_vars.currency_sign;
				if ( 'before' === edd_vars.currency_pos ) {
					formatted_amount = edd_vars.currency_sign + amount;
				}

				if( price_name ) {
					download_title = download_title + ' - ' + price_name;
				}

				var count = $('#edd-purchased-files div.row').length;
				var clone = $('#edd-purchased-files div.row:last').clone();

				clone.find( '.download span' ).html( '<a href="post.php?post=' + download_id + '&action=edit"></a>' );
				clone.find( '.download span a' ).text( download_title );
				clone.find( '.price' ).text( formatted_amount );
				clone.find( '.quantity span' ).text( quantity );
				clone.find( 'input.edd-payment-details-download-id' ).val( download_id );
				clone.find( 'input.edd-payment-details-download-price-id' ).val( price_id );
				clone.find( 'input.edd-payment-details-download-amount' ).val( amount );
				clone.find( 'input.edd-payment-details-download-quantity' ).val( quantity );

				// Replace the name / id attributes
				clone.find( 'input' ).each(function() {
					var name = $( this ).attr( 'name' );

					name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');

					$( this ).attr( 'name', name ).attr( 'id', name );
				});

				// Flag the Downloads section as changed
				$('#edd-payment-downloads-changed').val(1);

				$(clone).insertAfter( '#edd-purchased-files div.row:last' );
				$('.edd-order-payment-recalc-totals').show();

			});
		},

		recalculate_total : function() {

			// Remove a download from a purchase
			$('#edd-order-recalc-total').on('click', function(e) {
				e.preventDefault();
				var total = 0;
				if( $('#edd-purchased-files .row .edd-payment-details-download-amount').length ) {
					$('#edd-purchased-files .row .edd-payment-details-download-amount').each(function() {
						var quantity = $(this).next().val();
						if( quantity ) {
							total += ( parseFloat( $(this).val() ) * parseInt( quantity ) );
						} else {
							total += parseFloat( $(this).val() );
						}
					});
				}
				if( $('.edd-payment-fees').length ) {
					$('.edd-payment-fees span.fee-amount').each(function() {
						total += parseFloat( $(this).data('fee') );
					});
				}
				$('input[name=edd-payment-total]').val( total );
			});

		},

		variable_prices_check : function() {

			// On Download Select, Check if Variable Prices Exist
			$('#edd-purchased-files').on('change', 'select#edd_order_download_select', function() {

				var $this = $(this), download_id = $this.val();

				if(parseInt(download_id) > 0) {
					var postData = {
						action : 'edd_check_for_download_price_variations',
						download_id: download_id
					};

					$.ajax({
						type: "POST",
						data: postData,
						url: ajaxurl,
						success: function (response) {
							$('.edd_price_options_select').remove();
							$(response).insertAfter( $this.next() );
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				}
			});

		},

		add_note : function() {

			$('#edd-add-payment-note').on('click', function(e) {
				e.preventDefault();
				var postData = {
					action : 'edd_insert_payment_note',
					payment_id : $(this).data('payment-id'),
					note : $('#edd-payment-note').val()
				};

				if( postData.note ) {

					$.ajax({
						type: "POST",
						data: postData,
						url: ajaxurl,
						success: function (response) {
							$('#edd-payment-notes-inner').append( response );
							$('.edd-no-payment-notes').hide();
							$('#edd-payment-note').val('');
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				} else {
					var border_color = $('#edd-payment-note').css('border-color');
					$('#edd-payment-note').css('border-color', 'red');
					setTimeout( function() {
						$('#edd-payment-note').css('border-color', border_color );
					}, 500 );
				}

			});

		},

		remove_note : function() {

			$('body').on('click', '.edd-delete-payment-note', function(e) {

				e.preventDefault();

				if( confirm( edd_vars.delete_payment_note) ) {

					var postData = {
						action : 'edd_delete_payment_note',
						payment_id : $(this).data('payment-id'),
						note_id : $(this).data('note-id')
					};

					$.ajax({
						type: "POST",
						data: postData,
						url: ajaxurl,
						success: function (response) {
							$('#edd-payment-note-' + postData.note_id ).remove();
							if( ! $('.edd-payment-note').length ) {
								$('.edd-no-payment-notes').show();
							}
							return false;
						}
					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});
					return true;
				}

			});

		},

		resend_receipt : function() {
			$( 'body' ).on( 'click', '#edd-resend-receipt', function( e ) {
				return confirm( edd_vars.resend_receipt );
			} );
		},

		copy_download_link : function() {
			$( 'body' ).on( 'click', '.edd-copy-download-link', function( e ) {
				e.preventDefault();
				var $this    = $(this);
				var postData = {
					action      : 'edd_get_file_download_link',
					payment_id  : $('input[name="edd_payment_id"]').val(),
					download_id : $this.data('download-id'),
					price_id    : $this.data('price-id')
				};

				$.ajax({
					type: "POST",
					data: postData,
					url: ajaxurl,
					success: function (link) {
						$( "#edd-download-link" ).dialog({
							width: 400
						}).html( '<textarea rows="10" cols="40" id="edd-download-link-textarea">' + link + '</textarea>' );
						$( "#edd-download-link-textarea" ).focus().select();
						return false;
					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				});

			} );
		}

	};
	EDD_Edit_Payment.init();


	/**
	 * Discount add / edit screen JS
	 */
	var EDD_Discount = {

		init : function() {
			this.type_select();
			this.product_requirements();
		},

		type_select : function() {

			$('#edd-edit-discount #edd-type, #edd-add-discount #edd-type').change(function() {

				$('.edd-amount-description').toggle();

			});

		},

		product_requirements : function() {

			$('#products').change(function() {

				if( $(this).val() ) {

					$('#edd-discount-product-conditions').show();

				} else {

					$('#edd-discount-product-conditions').hide();
					
				}

			});

		},

	};
	EDD_Discount.init();


	/**
	 * Reports / Exports screen JS
	 */
	var EDD_Reports = {

		init : function() {
			this.date_options();
			this.customers_export();
		},

		date_options : function() {

			// Show hide extended date options
			$( '#edd-graphs-date-options' ).change( function() {
				var $this = $(this);
				if ( 'other' === $this.val() ) {
					$( '#edd-date-range-options' ).show();
				} else {
					$( '#edd-date-range-options' ).hide();
				}
			});

		},

		customers_export : function() {

			// Show / hide Download option when exporting customers

			$( '#edd_customer_export_download' ).change( function() {

				var $this = $(this), download_id = $('option:selected', $this).val();

				if ( '0' === $this.val() ) {
					$( '#edd_customer_export_option' ).show();
				} else {
					$( '#edd_customer_export_option' ).hide();
				}

				// On Download Select, Check if Variable Prices Exist
				if ( parseInt( download_id ) != 0 ) {
					var data = {
						action : 'edd_check_for_download_price_variations',
						download_id: download_id
					};
					$.post(ajaxurl, data, function(response) {
						$('.edd_price_options_select').remove();
						$this.after( response );
					});
				} else {
					$('.edd_price_options_select').remove();
				}
			});

		}

	};
	EDD_Reports.init();


	/**
	 * Settings screen JS
	 */
	var EDD_Settings = {

		init : function() {
			this.general();
			this.taxes();
			this.emails();
			this.misc();
		},

		general : function() {

			if( $('.edd-color-picker').length ) {
				$('.edd-color-picker').wpColorPicker();
			}

			// Settings Upload field JS
			if ( typeof wp === "undefined" || '1' !== edd_vars.new_media_ui ) {
				//Old Thickbox uploader
				if ( $( '.edd_settings_upload_button' ).length > 0 ) {
					window.formfield = '';

					$('body').on('click', '.edd_settings_upload_button', function(e) {
						e.preventDefault();
						window.formfield = $(this).parent().prev();
						window.tbframe_interval = setInterval(function() {
							jQuery('#TB_iframeContent').contents().find('.savesend .button').val(edd_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
						}, 2000);
						tb_show( edd_vars.add_new_download, 'media-upload.php?TB_iframe=true' );
					});

					window.edd_send_to_editor = window.send_to_editor;
					window.send_to_editor = function (html) {
						if (window.formfield) {
							imgurl = $('a', '<div>' + html + '</div>').attr('href');
							window.formfield.val(imgurl);
							window.clearInterval(window.tbframe_interval);
							tb_remove();
						} else {
							window.edd_send_to_editor(html);
						}
						window.send_to_editor = window.edd_send_to_editor;
						window.formfield = '';
						window.imagefield = false;
					};
				}
			} else {
				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';

				$('body').on('click', '.edd_settings_upload_button', function(e) {

					e.preventDefault();

					var button = $(this);

					window.formfield = $(this).parent().prev();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({
						frame: 'post',
						state: 'insert',
						title: button.data( 'uploader_title' ),
						button: {
							text: button.data( 'uploader_button_text' )
						},
						multiple: false
					});

					file_frame.on( 'menu:render:default', function( view ) {
						// Store our views in an object.
						var views = {};

						// Unset default menu items
						view.unset( 'library-separator' );
						view.unset( 'gallery' );
						view.unset( 'featured-image' );
						view.unset( 'embed' );

						// Initialize the views in our view object.
						view.set( views );
					} );

					// When an image is selected, run a callback.
					file_frame.on( 'insert', function() {

						var selection = file_frame.state().get('selection');
						selection.each( function( attachment, index ) {
							attachment = attachment.toJSON();
							window.formfield.val(attachment.url);
						});
					});

					// Finally, open the modal
					file_frame.open();
				});


				// WP 3.5+ uploader
				var file_frame;
				window.formfield = '';
			}

		},

		taxes : function() {

			if( $('select.edd-no-states').length ) {
				$('select.edd-no-states').closest('tr').hide();
			}

			// Update base state field based on selected base country
			$('select[name="edd_settings[base_country]"]').change(function() {
				var $this = $(this), $tr = $this.closest('tr');
				data = {
					action: 'edd_get_shop_states',
					country: $(this).val(),
					field_name: 'edd_settings[base_state]'
				};
				$.post(ajaxurl, data, function (response) {
					if( 'nostates' == response ) {
						$tr.next().hide();
					} else {
						$tr.next().show();
						$tr.next().find('select').replaceWith( response );
					}
				});

				return false;
			});

			// Update tax rate state field based on selected rate country
			$('body').on('change', '#edd_tax_rates select.edd-tax-country', function() {
				var $this = $(this);
				data = {
					action: 'edd_get_shop_states',
					country: $(this).val(),
					field_name: $this.attr('name').replace('country', 'state')
				};
				$.post(ajaxurl, data, function (response) {
					if( 'nostates' == response ) {
						var text_field = '<input type="text" name="' + data.field_name + '" value=""/>';
						$this.parent().next().find('select').replaceWith( text_field );
					} else {
						$this.parent().next().find('input,select').show();
						$this.parent().next().find('input,select').replaceWith( response );
					}
				});

				return false;
			});

			// Insert new tax rate row
			$('#edd_add_tax_rate').on('click', function() {
				var row = $('#edd_tax_rates tr:last');
				var clone = row.clone();
				var count = row.parent().find( 'tr' ).length;
				clone.find( 'td input' ).val( '' );
				clone.find( 'input, select' ).each(function() {
					var name = $( this ).attr( 'name' );
					name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');
					$( this ).attr( 'name', name ).attr( 'id', name );
				});
				clone.insertAfter( row );
				return false;
			});

			// Remove tax row
			$('body').on('click', '#edd_tax_rates .edd_remove_tax_rate', function() {
				if( confirm( edd_vars.delete_tax_rate ) )
					$(this).closest('tr').remove();
				return false;
			});

		},

		emails : function() {

			// Show the email template previews
			if( $('#email-preview-wrap').length ) {
				var emailPreview = $('#email-preview');
				$('#open-email-preview').colorbox({
					inline: true,
					href: emailPreview,
					width: '80%',
					height: 'auto'
				});
			}

		},

		misc : function() {

			// Hide Symlink option if Download Method is set to Direct
			if( $('select[name="edd_settings[download_method]"]:selected').val() != 'direct' ) {
				$('select[name="edd_settings[download_method]"]').parent().parent().next().hide();
				$('select[name="edd_settings[download_method]"]').parent().parent().next().find('input').attr('checked', false);
			}
			// Toggle download method option
			$('select[name="edd_settings[download_method]"]').on('change', function() {
				var symlink = $(this).parent().parent().next();
				if( $(this).val() == 'direct' ) {
					symlink.hide();
				} else {
					symlink.show();
					symlink.find('input').attr('checked', false);
				}
			});

		}

	}
	EDD_Settings.init();


	$('.download_page_edd-payment-history .row-actions .delete a').on('click', function() {
		if( confirm( edd_vars.delete_payment ) ) {
			return true;
		}
		return false;
	});


	$('#the-list').on('click', '.editinline', function() {
		inlineEditPost.revert();

		var post_id = $(this).closest('tr').attr('id');

		post_id = post_id.replace("post-", "");

		var $edd_inline_data = $('#post-' + post_id);

		var regprice = $edd_inline_data.find('.column-price .downloadprice-' + post_id).val();

		// If variable priced product disable editing, otherwise allow price changes
		if ( regprice != $('#post-' + post_id + '.column-price .downloadprice-' + post_id).val() ) {
			$('.regprice', '#edd-download-data').val(regprice).attr('disabled', false);
		} else {
			$('.regprice', '#edd-download-data').val( edd_vars.quick_edit_warning ).attr('disabled', 'disabled');
		}
	});


    // Bulk edit save
    $( 'body' ).on( 'click', '#bulk_edit', function() {

		// define the bulk edit row
		var $bulk_row = $( '#bulk-edit' );

		// get the selected post ids that are being edited
		var $post_ids = new Array();
		$bulk_row.find( '#bulk-titles' ).children().each( function() {
			$post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
		});

		// get the stock and price values to save for all the product ID's
		var $price = $( '#edd-download-data input[name="_edd_regprice"]' ).val();

		var data = {
			action: 		'edd_save_bulk_edit',
			edd_bulk_nonce:	$post_ids,
			post_ids:		$post_ids,
			price:			$price
		};

		// save the data
		$.post( ajaxurl, data );

	});

    // Setup Chosen menus
    $('.edd-select-chosen').chosen({
    	inherit_select_classes: true,
    	placeholder_text_single: edd_vars.one_option,
    	placeholder_text_multiple: edd_vars.one_or_more_option,
    });

	// Variables for setting up the typing timer
	var typingTimer;               // Timer identifier
	var doneTypingInterval = 342;  // Time in ms, Slow - 521ms, Moderate - 342ms, Fast - 300ms

    // Replace options with search results
	$('.edd-select.chosen-container .chosen-search input, .edd-select.chosen-container .search-field input').keyup(function(e) {

		var val = $(this).val(), container = $(this).closest( '.edd-select-chosen' );
		var menu_id = container.attr('id').replace( '_chosen', '' );
		var lastKey = e.which;

		// Don't fire if short or is a modifier key (shift, ctrl, apple command key, or arrow keys)
		if(
			val.length <= 3 ||
			(
				e.which == 16 ||
				e.which == 13 ||
				e.which == 91 ||
				e.which == 17 ||
				e.which == 37 ||
				e.which == 38 ||
				e.which == 39 ||
				e.which == 40
			)
		) {
			return;
		}
		
		clearTimeout(typingTimer);
		typingTimer = setTimeout(
			function(){
				$.ajax({
					type: 'GET',
					url: ajaxurl,
					data: {
						action: 'edd_download_search',
						s: val,
					},
					dataType: "json",
					beforeSend: function(){
						$('ul.chosen-results').empty();
					},
					success: function( data ) {

						// Remove all options but those that are selected
					 	$('#' + menu_id + ' option:not(:selected)').remove();
						$.each( data, function( key, item ) {
						 	// Add any option that doesn't already exist
							if( ! $('#' + menu_id + ' option[value="' + item.id + '"]').length ) {
								$('#' + menu_id).prepend( '<option value="' + item.id + '">' + item.name + '</option>' );
							}
						});
						 // Update the options
						$('.edd-select-chosen').trigger('chosen:updated');
						$('#' + menu_id).next().find('input').val(val);
					}
				}).fail(function (response) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				}).done(function (response) {

		        });
			},
			doneTypingInterval
		);
	});

	// This fixes the Chosen box being 0px wide when the thickbox is opened
	$( '#post' ).on( 'click', '.edd-thickbox', function() {
		$( '.edd-select-chosen', '#choose-download' ).css( 'width', '100%' );
	});

	/**
	 * Tools screen JS
	 */
	var EDD_Tools = {

		init : function() {
			this.revoke_api_key();
			this.regenerate_api_key();
		},

		revoke_api_key : function() {
			$( 'body' ).on( 'click', '.edd-revoke-api-key', function( e ) {
				return confirm( edd_vars.revoke_api_key );
			} );
		},
		regenerate_api_key : function() {
			$( 'body' ).on( 'click', '.edd-regenerate-api-key', function( e ) {
				return confirm( edd_vars.regenerate_api_key );
			} );
		},
	};
	EDD_Tools.init();

	// Ajax user search
	$('.edd-ajax-user-search').keyup(function() {
		var user_search = $(this).val();
		$('.edd-ajax').show();
		data = {
			action: 'edd_search_users',
			user_name: user_search
		};
		
		document.body.style.cursor = 'wait';

		$.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: ajaxurl,
			success: function (search_response) {

				$('.edd-ajax').hide();
				$('.edd_user_search_results').html('');
				$(search_response.results).appendTo('.edd_user_search_results');
				document.body.style.cursor = 'default';
			}
		});
	});
	$('body').on('click.eddSelectUser', '.edd_user_search_results a', function(e) {
		e.preventDefault();
		var login = $(this).data('login');
		$('.edd-ajax-user-search').val(login);
		$('.edd_user_search_results').html('');
	});

});
