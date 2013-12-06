<?php
/*

© 2013 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class QM_Output_Html_Hooks extends QM_Output_Html {

	public $id = 'hooks';

	public function __construct( QM_Component $component ) {
		parent::__construct( $component );
		add_filter( 'query_monitor_menus', array( $this, 'admin_menu' ), 80 );
	}

	public function output() {

		$data = $this->component->get_data();

		if ( empty( $data ) )
			return;

		$row_attr = array();

		echo '<div class="qm" id="' . $this->component->id() . '">';
		echo '<table cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>' . __( 'Hook', 'query-monitor' ) . $this->build_filter( 'name', $data['parts'] ) . '</th>';
		echo '<th colspan="3">' . __( 'Actions', 'query-monitor' ) . $this->build_filter( 'component', $data['components'] ) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $data['hooks'] as $hook ) {

			if ( !empty( $data['screen'] ) ) {

				if ( false !== strpos( $hook['name'], $data['screen'] . '.php' ) )
					$hook['name'] = str_replace( '-' . $data['screen'] . '.php', '-<span class="qm-current">' . $data['screen'] . '.php</span>', $hook['name'] );
				else
					$hook['name'] = str_replace( '-' . $data['screen'], '-<span class="qm-current">' . $data['screen'] . '</span>', $hook['name'] );

			}

			$row_attr['data-qm-hooks-name']      = implode( ' ', $hook['parts'] );
			$row_attr['data-qm-hooks-component'] = implode( ' ', $hook['components'] );

			$attr = '';

			if ( !empty( $hook['actions'] ) )
				$rowspan = count( $hook['actions'] );
			else
				$rowspan = 1;

			foreach ( $row_attr as $a => $v )
				$attr .= ' ' . $a . '="' . esc_attr( $v ) . '"';

			echo "<tr{$attr}>";

			echo "<td valign='top' rowspan='{$rowspan}'>{$hook['name']}</td>";	
			if ( !empty( $hook['actions'] ) ) {

				$first = true;

				foreach ( $hook['actions'] as $action ) {

					if ( isset( $action['callback']['component'] ) )
						$component = $action['callback']['component']->name;
					else
						$component = '';

					if ( !$first )
						echo "<tr{$attr}>";

					echo '<td valign="top" class="qm-priority">' . $action['priority'] . '</td>';
					echo '<td valign="top" class="qm-ltr">';
					echo esc_html( $action['callback']['name'] );
					if ( isset( $action['callback']['error'] ) ) {
						echo '<br><span class="qm-warn">';
						printf( __( 'Error: %s', 'query-monitor' ),
							esc_html( $action['callback']['error']->get_error_message() )
						);
						echo '<span>';
					}
					echo '</td>';
					echo '<td valign="top">';
					echo esc_html( $component );
					echo '</td>';
					echo '</tr>';
					$first = false;
				}

			} else {
				echo '<td colspan="3">&nbsp;</td>';
			}
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';
		echo '</div>';

	}

	public function admin_menu( array $menu ) {

		$menu[] = $this->menu( array(
			'title' => __( 'Hooks', 'query-monitor' )
		) );
		return $menu;

	}

}

function register_qm_hooks_output_html( QM_Output $output = null, QM_Component $component ) {
	return new QM_Output_Html_Hooks( $component );
}

add_filter( 'query_monitor_output_html_hooks', 'register_qm_hooks_output_html', 10, 2 );
