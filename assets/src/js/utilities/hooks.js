/**
 * EDD Hooks System - Reusable Utility
 *
 * A simple, priority-based hooks system for JavaScript that mirrors WordPress PHP hooks.
 * Can be imported and used by any EDD script.
 *
 * Usage:
 *   import { Hooks } from './utilities/hooks.js';
 *
 *   const hooks = new Hooks();
 *
 *   // Add filters
 *   hooks.addFilter( 'myFilter', ( value ) => value + '!', 10 );
 *
 *   // Apply filters
 *   const result = hooks.applyFilters( 'myFilter', 'Hello' );
 *   // result === 'Hello!'
 *
 *   // Add actions
 *   hooks.addAction( 'myAction', () => console.log( 'Action!' ), 10 );
 *
 *   // Execute actions
 *   hooks.doAction( 'myAction' );
 *   // Console: "Action!"
 *
 * @class Hooks
 * @since 3.6.2
 */
class Hooks {
	/**
	 * Constructor
	 *
	 * @since 3.6.2
	 */
	constructor() {
		this.filters = {};
		this.actions = {};
	}

	/**
	 * Add a filter hook
	 *
	 * Filters can be used to modify a value by passing it through registered callbacks.
	 * Callbacks are executed in priority order (lower priority = runs first).
	 *
	 * @since 3.6.2
	 * @param {string} hook Hook name
	 * @param {Function} callback Callback function(s) to execute
	 * @param {number} priority Priority level (default 10, lower runs first)
	 * @example
	 *   hooks.addFilter( 'myFilter', ( value ) => value.toUpperCase(), 10 );
	 */
	addFilter( hook, callback, priority = 10 ) {
		if ( !this.filters[ hook ] ) {
			this.filters[ hook ] = [];
		}
		this.filters[ hook ].push( { callback, priority } );
		// Sort by priority (lower numbers = higher priority, like WordPress)
		this.filters[ hook ].sort( ( a, b ) => a.priority - b.priority );
	}

	/**
	 * Apply filters to a value
	 *
	 * Passes a value through all registered filter callbacks and returns the result.
	 * Callbacks are executed in priority order and each receives the output of the previous.
	 *
	 * @since 3.6.2
	 * @param {string} hook Hook name
	 * @param {*} value Initial value to filter
	 * @param {...*} args Additional arguments to pass to callbacks
	 * @return {*} Filtered value
	 * @example
	 *   hooks.addFilter( 'greet', ( name ) => 'Hello, ' + name, 10 );
	 *   const result = hooks.applyFilters( 'greet', 'World' );
	 *   // result === 'Hello, World'
	 */
	applyFilters( hook, value, ...args ) {
		if ( !this.filters[ hook ] ) {
			return value;
		}

		return this.filters[ hook ].reduce( ( accumulator, { callback } ) => {
			return callback( accumulator, ...args );
		}, value );
	}

	/**
	 * Add an action hook
	 *
	 * Actions are used to execute arbitrary code at specific points.
	 * Callbacks are executed in priority order (lower priority = runs first).
	 *
	 * @since 3.6.2
	 * @param {string} hook Hook name
	 * @param {Function} callback Callback function to execute
	 * @param {number} priority Priority level (default 10, lower runs first)
	 * @example
	 *   hooks.addAction( 'onInit', () => console.log( 'Initialized!' ), 10 );
	 */
	addAction( hook, callback, priority = 10 ) {
		if ( !this.actions[ hook ] ) {
			this.actions[ hook ] = [];
		}
		this.actions[ hook ].push( { callback, priority } );
		this.actions[ hook ].sort( ( a, b ) => a.priority - b.priority );
	}

	/**
	 * Execute action hooks
	 *
	 * Runs all callbacks registered for an action hook in priority order.
	 *
	 * @since 3.6.2
	 * @param {string} hook Hook name
	 * @param {...*} args Arguments to pass to callbacks
	 * @example
	 *   hooks.doAction( 'onInit', { data: 'value' } );
	 */
	doAction( hook, ...args ) {
		if ( !this.actions[ hook ] ) {
			return;
		}

		for ( const { callback } of this.actions[ hook ] ) {
			callback( ...args );
		}
	}

	/**
	 * Remove a filter hook
	 *
	 * Removes a specific callback from a filter hook.
	 *
	 * @since 3.6.2
	 * @param {string} hook Hook name
	 * @param {Function} callback Callback function to remove
	 * @return {boolean} True if removed, false if not found
	 * @example
	 *   const myFilter = ( value ) => value + '!';
	 *   hooks.addFilter( 'test', myFilter, 10 );
	 *   hooks.removeFilter( 'test', myFilter );
	 */
	removeFilter( hook, callback ) {
		if ( !this.filters[ hook ] ) {
			return false;
		}

		const originalLength = this.filters[ hook ].length;
		this.filters[ hook ] = this.filters[ hook ].filter(
			( item ) => item.callback !== callback
		);

		return this.filters[ hook ].length < originalLength;
	}

	/**
	 * Remove an action hook
	 *
	 * Removes a specific callback from an action hook.
	 *
	 * @since 3.6.2
	 * @param {string} hook Hook name
	 * @param {Function} callback Callback function to remove
	 * @return {boolean} True if removed, false if not found
	 * @example
	 *   const myAction = () => console.log( 'Action!' );
	 *   hooks.addAction( 'test', myAction, 10 );
	 *   hooks.removeAction( 'test', myAction );
	 */
	removeAction( hook, callback ) {
		if ( !this.actions[ hook ] ) {
			return false;
		}

		const originalLength = this.actions[ hook ].length;
		this.actions[ hook ] = this.actions[ hook ].filter(
			( item ) => item.callback !== callback
		);

		return this.actions[ hook ].length < originalLength;
	}

	/**
	 * Get all registered filters for a hook
	 *
	 * Useful for debugging and inspection.
	 *
	 * @since 3.6.2
	 * @param {string} hook Hook name (optional, returns all if omitted)
	 * @return {Object} Registered filters
	 * @example
	 *   console.log( hooks.getFilters( 'myFilter' ) );
	 *   console.log( hooks.getFilters() ); // All filters
	 */
	getFilters( hook ) {
		if ( hook ) {
			return this.filters[ hook ] || [];
		}
		return this.filters;
	}

	/**
	 * Get all registered actions for a hook
	 *
	 * Useful for debugging and inspection.
	 *
	 * @since 3.6.2
	 * @param {string} hook Hook name (optional, returns all if omitted)
	 * @return {Object} Registered actions
	 * @example
	 *   console.log( hooks.getActions( 'myAction' ) );
	 *   console.log( hooks.getActions() ); // All actions
	 */
	getActions( hook ) {
		if ( hook ) {
			return this.actions[ hook ] || [];
		}
		return this.actions;
	}

	/**
	 * Clear all hooks
	 *
	 * Removes all registered filters and actions. Useful for testing.
	 *
	 * @since 3.6.2
	 * @example
	 *   hooks.clear();
	 */
	clear() {
		this.filters = {};
		this.actions = {};
	}

	/**
	 * Clear a specific hook
	 *
	 * Removes all callbacks for a specific hook.
	 *
	 * @since 3.6.2
	 * @param {string} hook Hook name
	 * @param {string} type Hook type ('filter' or 'action', optional)
	 * @example
	 *   hooks.clearHook( 'myFilter', 'filter' );
	 */
	clearHook( hook, type ) {
		if ( !type || type === 'filter' ) {
			delete this.filters[ hook ];
		}
		if ( !type || type === 'action' ) {
			delete this.actions[ hook ];
		}
	}

	/**
	 * Check if a hook has any registered callbacks
	 *
	 * @since 3.6.2
	 * @param {string} hook Hook name
	 * @param {string} type Hook type ('filter', 'action', or omit for either)
	 * @return {boolean} True if hook has callbacks
	 * @example
	 *   if ( hooks.hasHook( 'myFilter', 'filter' ) ) {
	 *     console.log( 'Filter exists' );
	 *   }
	 */
	hasHook( hook, type ) {
		if ( type === 'filter' ) {
			return !!this.filters[ hook ] && this.filters[ hook ].length > 0;
		}
		if ( type === 'action' ) {
			return !!this.actions[ hook ] && this.actions[ hook ].length > 0;
		}
		return ( this.filters[ hook ] && this.filters[ hook ].length > 0 ) ||
			( this.actions[ hook ] && this.actions[ hook ].length > 0 );
	}
}

// Export for ES6 modules
export { Hooks };
