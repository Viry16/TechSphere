<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if payment table exists
$check_table_sql = "SHOW TABLES LIKE 'payment'";
$check_result = mysqli_query($conn, $check_table_sql);

if (mysqli_num_rows($check_result) == 0) {
    // Table doesn't exist, create it
    echo "Payment table doesn't exist. Creating...<br>";
    
    $create_table_sql = "CREATE TABLE IF NOT EXISTS payment (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_amount DECIMAL(10,2) NOT NULL,
        payment_status ENUM('Pending', 'Completed', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
        payment_date DATETIME NOT NULL,
        payment_transaction_id VARCHAR(100),
        payment_notes TEXT,
        payment_proof VARCHAR(255),
        payment_confirmed_by INT,
        payment_confirmed_date DATETIME,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
        FOREIGN KEY (payment_confirmed_by) REFERENCES admin(admin_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (mysqli_query($conn, $create_table_sql)) {
        echo "Payment table created successfully!<br>";
        
        // Create indexes after table creation
        $create_indexes_sql = "
        CREATE INDEX idx_payment_order_id ON payment (order_id);
        CREATE INDEX idx_payment_status ON payment (payment_status);
        CREATE INDEX idx_payment_date ON payment (payment_date);";
        
        if (mysqli_multi_query($conn, $create_indexes_sql)) {
            echo "Payment indexes created successfully!<br>";
            // Clear results to continue executing queries
            while (mysqli_next_result($conn)) {;}
        } else {
            echo "Error creating payment indexes: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "Error creating payment table: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Payment table already exists.<br>";
}

// Check if orders table has required columns
$required_columns = [
    'order_subtotal' => 'DECIMAL(10,2)',
    'order_shipping' => 'DECIMAL(10,2)',
    'order_tax' => 'DECIMAL(10,2)',
    'order_payment_method' => 'VARCHAR(50)',
    'order_notes' => 'TEXT',
    'address_id' => 'VARCHAR(100)'
];

$check_orders_columns_sql = "SHOW COLUMNS FROM orders";
$orders_columns_result = mysqli_query($conn, $check_orders_columns_sql);

$existing_columns = [];
while ($column = mysqli_fetch_assoc($orders_columns_result)) {
    $existing_columns[] = $column['Field'];
}

$columns_to_add = [];
foreach ($required_columns as $column_name => $column_type) {
    if (!in_array($column_name, $existing_columns)) {
        $columns_to_add[$column_name] = $column_type;
    }
}

if (!empty($columns_to_add)) {
    echo "Adding missing columns to orders table:<br>";
    
    foreach ($columns_to_add as $column_name => $column_type) {
        $alter_sql = "ALTER TABLE orders ADD COLUMN $column_name $column_type";
        if (mysqli_query($conn, $alter_sql)) {
            echo "- Added column $column_name ($column_type)<br>";
        } else {
            echo "- Error adding column $column_name: " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "Orders table already has all required columns.<br>";
}

// Check the structure of the orders table
$check_orders_sql = "SHOW COLUMNS FROM orders";
$orders_result = mysqli_query($conn, $check_orders_sql);

echo "<h3>Orders Table Structure:</h3>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($orders_result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Check the structure of the address table
$check_address_sql = "SHOW COLUMNS FROM address";
$address_result = mysqli_query($conn, $check_address_sql);

echo "<h3>Address Table Structure:</h3>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($address_result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Close connection
mysqli_close($conn);
?> 