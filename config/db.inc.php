<?php
/**
 * This file is used for creating a connection to the database
 */
 
// parses the settings file
$settings = parse_ini_file('settings.ini', true);

try {

  // starts the connection to the database
$dbh = new PDO(
  sprintf(
    "%s:host=%s;dbname=%s",
    $settings['database']['driver'],
    $settings['database']['host'],
    $settings['database']['dbname']
  ),
  $settings['database']['user'],
  $settings['database']['password'],
  array(PDO::ATTR_PERSISTENT => true)
);
// set the PDO error mode to exception
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Error: " .$e->getMessage();
}
?>
