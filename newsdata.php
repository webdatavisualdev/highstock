<?php
	$date = date("y-m-d");
	
	$db = new db();
	$results = $db->query("select dj_news.id, dj_news_isin.code as code,dj_news.headline, dj_news.lexicon, dj_news.hot,dj_news.display_date ,companies.name, companies.symbol from dj_news_isin left join dj_news on dj_news.id=dj_news_isin.dj_news_id left join companies on dj_news_isin.code=companies.isin where dj_news_isin.code= '".$_POST["company"]."' and dj_news.hot='Y' order by dj_news.display_date", TRUE);

	echo json_encode($results);

	class db {

		public $mysqli;

		public function __construct() {

			$this->mysqli = new mysqli("localhost", "zpghkvqdgf", "BMfQkz5X94", "zpghkvqdgf");

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