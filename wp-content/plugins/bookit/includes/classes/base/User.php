<?php
namespace Bookit\Classes\Base;


use Bookit\Classes\Database\Staff;

/**
 * Class User
 * Bookit users connection with wp user, plugin custom roles and capabilities
 */
class User {

	public static $staff_role           = 'bookit_staff';
	public static $customer_role        = 'bookit_customer';
	protected static $wp_roles          = [ 'administrator', 'editor' ];
	private static $bookit_user_roles   = [
		'bookit_customer' => [
			'name'  => 'Bookit Customer',
			'roles' => [
				'read'         => false,
				'edit_posts'   => false,
				'upload_files' => false,
			]
		],
		'bookit_staff' => [
			'name'  => 'Bookit Staff',
			'roles' => [
				'read'         => true,
				'edit_posts'   => false,
				'upload_files' => false,
				'manage_bookit_staff' => true,
			]
		],
	];

	public static function getUserData() {
		$user    = wp_get_current_user();

		$staff   = [];
		$isStaff = false;

		if( array_intersect([self::$staff_role], $user->roles ) ) {
			$isStaff = true;
			$staff   = Staff::get_by_wp_user_id( $user->data->ID );
		}

		return [ 'staff' => $staff, 'is_staff' => $isStaff ];
	}

	/**
	 * Add user roles with capabilities for bookit plugin
	 */
	public static function addBookitUserRoles() {
		foreach (self::$bookit_user_roles as $bookitRoleName => $bookitRoleInfo) {
			remove_role($bookitRoleName);
			add_role( $bookitRoleName, __( $bookitRoleInfo['name'] ), $bookitRoleInfo['roles']);
		}
	}

	/**
	 * Add 'manage_bookit_staff' capability to exist wordpress roles ($wp_roles)
	 */
	public static function addBookitCapabilitiesToWpRoles() {

		foreach (self::$wp_roles as $wpRole) {
			$role = get_role( $wpRole );
			$role->add_cap('manage_bookit_staff');
		}
	}

	/**
	 * Remove bookit user roles
	 */
	public static function cleanRoles() {
		foreach (self::$bookit_user_roles as $bookitRoleName => $bookitRoleInfo) {
			remove_role($bookitRoleName);
		}
	}

	/**
	 * Remove 'manage_bookit_staff' capability from exist wordpress roles ($wp_roles)
	 */
	public static function cleanCapabilities() {
		foreach (self::$wp_roles as $wpRole) {
			$role = get_role( $wpRole );
			$role->remove_cap('manage_bookit_staff');
		}
	}
}