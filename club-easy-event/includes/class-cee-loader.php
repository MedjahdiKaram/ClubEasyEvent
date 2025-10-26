<?php
/**
 * Loader class.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages registering WordPress hooks for the plugin.
 */
class CEE_Loader {

	/**
	 * Registered actions.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Registered filters.
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Add a WordPress action.
	 *
	 * @param string   $hook          Hook name.
	 * @param object   $component     Component instance.
	 * @param string   $callback      Callback method.
	 * @param int      $priority      Priority.
	 * @param int      $accepted_args Accepted args.
	 *
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
	}

	/**
	 * Add a WordPress filter.
	 *
	 * @param string   $hook          Hook name.
	 * @param object   $component     Component instance.
	 * @param string   $callback      Callback method.
	 * @param int      $priority      Priority.
	 * @param int      $accepted_args Accepted args.
	 *
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
	}

	/**
	 * Register actions and filters with WordPress.
	 *
	 * @return void
	 */
	public function run() {
		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
	}
}
