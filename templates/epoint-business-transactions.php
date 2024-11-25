<?php
/*
Template Name: Transacciones por empresa
*/

get_header();


if (!current_user_can('manage_options') && !current_user_can('verify_qr')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

global $wpdb; 

// Fetch transactions related to the logged-in user's ID
$current_user_id = get_current_user_id(); // Get the current logged-in user ID
$table_name = $wpdb->prefix . 'epoint_qr_transactions'; // Adjust the prefix if different

// Prepare the SQL query to fetch data
$sql = $wpdb->prepare(
    "SELECT * FROM $table_name WHERE verifier_user_id = %d",
    $current_user_id
);

$transactions = $wpdb->get_results($sql); // Execute the query and get the results



echo '<style>
/* Overall body styling */
body {
    color: #333; /* Dark grey text for better readability */
}

/* Style adjustments for the form */
form#date-filter {
    background-color: #ffffff; /* White background for the form area */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Subtle shadow for a pop-out effect */
    display: flex;
    height: auto;
    gap: 10px; /* Spacing between form elements */
    max-width: 80%; /* Limiting form width */
    margin: 20px auto; /* Centering form */
}

label {
    margin-bottom: 5px;
    font-weight: bold;
    margin-top: 15px;
}

input[type="date"] {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ccc; /* Subtle border for inputs */
}

button {
    background-color: rgb(22,28,38); /* A pleasant shade of blue for the button */
    color: #ffffff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer; /* Pointer cursor on hover for better user interaction feedback */
    transition: background-color 0.3s;
}

button:hover {
    background-color: rgba(0,54,124,0.91); /* Darken button on hover for interactive feedback */
}

/* Table styling for improved readability */
table {
    width: 80%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: #fff; /* White background for the table */
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Consistent with form shadow */
}

th, td {
    border: 1px solid #ccc;
    padding: 12px 15px; /* Slightly larger padding for a more spacious layout */
    text-align: left;
}

th {
    background-color: #5b7bf8; /* Blue headers for a modern look */
    color: #ffffff; /* White text for contrast */
}

tr:nth-child(even) {
    background-color: #f2f2f2; /* Zebra striping for rows */
}

h1 {
    margin-left: 10%;
    color: #333;
    margin-top: 30px; /* Adding more space above the title */
}
</style>';

echo '<h1>Historial de transacciones con el bono de descuento:</h1>';

echo '<form id="date-filter" style="margin-left: 10%; margin-bottom: 20px;">
    <label for="from-date">Desde:</label>
    <input type="date" id="from-date" name="from_date" required>

    <label for="to-date">Hasta:</label>
    <input type="date" id="to-date" name="to_date" required>

    <button type="submit">Filtrar transacciones</button>
</form>';

// Check if there are any results and then display them
if (!empty($transactions)) {
    echo '<table></table>';
} else {
    echo '<p>No existen transacciones entre estas fechas.</p>';
}

echo '<script>
document.getElementById("date-filter").addEventListener("submit", function(event) {
    event.preventDefault();
    var fromDate = document.getElementById("from-date").value;
    var toDate = document.getElementById("to-date").value;

    // AJAX request to the server with from and to dates
    jQuery.ajax({
        url: "' . admin_url('admin-ajax.php') . '",
        type: "POST",
        data: {
            "action": "filter_transactions", // Custom action for WP AJAX
            "from_date": fromDate,
            "to_date": toDate
        },
        success: function(response) {
            // Update your table with the response here
            jQuery("table").html(response);
        }
    });
});
</script>';

?>