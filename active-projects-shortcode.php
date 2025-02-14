<?php

function fetch_and_display_projects_table() {
    // API endpoint and credentials
    $endpoint_url = '';
    $username = '';
    $password = '';

    // Perform the HTTP request
    $response = wp_remote_get( $endpoint_url, array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
        ),
    ));

    // Handle errors
    if ( is_wp_error( $response ) ) {
        return '<p>Error: Unable to fetch data. ' . esc_html( $response->get_error_message() ) . '</p>';
    }

    // Check for valid response code
    $status_code = wp_remote_retrieve_response_code( $response );
    if ( $status_code !== 200 ) {
        return '<p>Error: Invalid response status (' . esc_html( $status_code ) . ')</p>';
    }

    // Decode the JSON response
    $data = wp_remote_retrieve_body( $response );
    $projects = json_decode( $data, true );

    // Check if decoding was successful
    if ( json_last_error() !== JSON_ERROR_NONE || !is_array( $projects ) ) {
        return '<p>Error: Unable to parse the response data.</p>';
    }

    // Start building the table
    $html = '<table id="example" class="display responsive" style="width: 100%;">
        <thead>
            <tr>
                <th>Project/Activity Number</th>
                <th>Project Name</th>
                <th>Division</th>
                <th>Sub-Division</th>
                <th>Customer</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Project Manager</th>
            </tr>
        </thead>
        <tbody>';

    // Populate table rows
	 foreach ( $projects as $project ) {
		// project_number: Check for links in brackets
        if ( preg_match( '/\[(.*?)\]\s*-\s*(.*)/', $project['project_number'], $matches ) ) {
            $project_number = '<a href="' . esc_url( $matches[1] ) . '">' . esc_html( $matches[2] ) . '</a>';
        } else {
            $project_number = esc_html( $project['project_number'] );
        }

        // project_name: detect if it contains a link
        if ( strpos( $project['project_name'], '<a ' ) !== false ) {
            $project_name = $project['project_name'];
        } else {
			$project_name = esc_html( $project['project_name'] );å
        }

        // project_manager: detect if it contains a link
        if ( strpos( $project['project_manager'], '<a ' ) !== false ) {
            $project_manager = $project['project_manager'];
        } else {
            $project_manager = esc_html( $project['project_manager'] );
        }

        $html .= '<tr>
            <td>' . $project_number . '</td>
            <td>' . $project['project_name'] . '</td>
            <td>' . esc_html( $project['division'] ) . '</td>
            <td>' . esc_html( $project['sub_division'] ) . '</td>
            <td>' . esc_html( $project['customer'] ) . '</td>
            <td>' . esc_html( $project['start_date'] ) . '</td>
            <td>' . esc_html( $project['end_date'] ) . '</td>
            <td>' . $project_manager . '</td>
        </tr>';
    }

    $html .= '</tbody>
    </table>';

    return $html;
}
add_shortcode( 'projects_table', 'fetch_and_display_projects_table' );