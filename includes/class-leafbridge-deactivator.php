<?php
class LeafBridge_Deactivator {
	public static function deactivate() {
		// Get the Administrator role object
		$administrator_role = get_role('administrator');

		if ($administrator_role && method_exists($administrator_role, 'remove_cap')) {
			$administrator_role->remove_cap('read_lb_settings');
		}
		//remove lb_contributor
		remove_role('lb_contributor');

		// Remove custom capabilities from all roles (optional)
		// Note: Only use this if 'read_lb_settings' capability is not used in other roles
		$roles = wp_roles();
		foreach ($roles->role_objects as $role) {
			$role->remove_cap('read_lb_settings');
		}
		global $wp_rewrite;

		$wp_rewrite->flush_rules(true);
		flush_rewrite_rules();
	}
}
