<?php 
	session_start();
	require('dbconnect.php');

	if (isset($_SESSION['id'])) {
		$tweet_id=$_REQUEST['tweet_id'];

		// 投稿を検査する
		$sql = sprintf('SELECT * FROM tweets WHERE tweet_id= %d',
			mysqli_real_escape_string($db, $tweet_id)
			);
		// デバッグ
		var_dump($sql);

		$record = mysqli_query($db,$sql) or die(mysqli_error($db));
		$table = mysqli_fetch_assoc($record);
		if ($table['member_id'] == $_SESSION['id']) {
			$sql = sprintf('UPDATE `tweets` SET `delete_frag`=1 WHERE `tweet_id`= %d',
			mysqli_real_escape_string($db, $tweet_id)
			);
		mysqli_query($db,$sql) or die(mysqli_error($db));
		}
	}

	header('Location: index.php');
 ?>