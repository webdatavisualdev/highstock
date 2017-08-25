<?php

	$date = date("y-m-d");
	echo $date;
	$db = new db();
	$results = $db->query("SELECT * FROM stock_volumn WHERE volume > 0 AND do_date between '2012-1-1' AND '".$date."' LIMIT 50000", TRUE);

	echo json_encode($results);

	class db {

		public $mysqli;

		public function __construct() {

			$this->mysqli = new mysqli("localhost", "kmpscazrwg", "5VkSQGX5Gc", "kmpscazrwg");

			if (mysqli_connect_errno()) {

				exit();

			}

		}

		public function __destruct() {

			$this->disconnect();

			unset($this->mysqli);

		}

		public function disconnect() {

			$this->mysqli->close();

		}

		function query($q, $resultset) {

	

			/* create a prepared statement */

			if (!($stmt = $this->mysqli->prepare($q))) {

				echo("Sql Error: " . $q . ' Sql error #: ' . $this->mysqli->errno . ' - ' . $this->mysqli->error);

				return false;

			}

	

			/* execute query */

			$stmt->execute();

	

			if ($stmt->errno) {

				echo("Sql Error: " . $q . ' Sql error #: ' . $stmt->errno . ' - ' . $stmt->error);

				return false;

			}

			if ($resultset) {

				$result = $stmt->get_result();

				for ($set = array(); $row = $result->fetch_assoc();) {

				$set[] = $row;

				}

				$stmt->close();

				return $set;

			}

		}

	}

?>