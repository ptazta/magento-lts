<?php
$dbhost = 'localhost';
$dbuser = 'bongosza_iDmJ1ma';
$dbpass = 'fxt5VKz5bloBG';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die                      ('Error connecting to mysql');

$dbname = 'bongosza_iDmJ1ma';
mysqli_select_db($conn,$dbname);
$sql = "alter table idmj_sales_flat_order add column  ds_order varchar(32) null ;";

if (mysqli_query($conn, $sql) ){
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}
$sql = "alter table idmj_sales_flat_order add column ds_authorisationcode varchar(32) null ;";

if (mysqli_query($conn, $sql) ){
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}
$conn->close();
?>
