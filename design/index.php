<?php 
    session_start();
    require('dbconnect.php');

    // $nick_name =htmlspecialchars($_SESSION['nick_name']);
    if (isset($_SESSION['id']) && $_SESSION['time']+3600>time()) {
      // ログインしている
      $_SESSION['time'] = time();

      $sql = sprintf('SELECT * FROM members WHERE member_id = %d',
        mysqli_real_escape_string($db, $_SESSION['id'])
        );
      $record = mysqli_query($db, $sql) or die(my_sqli_error($db));
      $member = mysqli_fetch_assoc($record);

      $nick_name= htmlspecialchars($member['nick_name']);
      }else{
        // ログインしていない
        header('Location: login.php');
        exit();
      }

      if (!empty($_POST)) {
        if ($_POST['tweet'] != '') {
          $sql = sprintf('INSERT INTO tweets SET member_id = %d, tweet="%s", reply_tweet_id ="0", created = NOW()',
              mysqli_real_escape_string($db, $member['member_id']),
              mysqli_real_escape_string($db,$_POST['tweet'])
            );
          var_dump($sql);
          mysqli_query($db, $sql) or die(mysqli_error($db));
          header('Location:index.php');
          exit();
        }
      }

      // 投稿を取得する
      $sql = sprintf ('SELECT m.nick_name, m.picture_path, p.* FROM members m, tweets p WHERE m.member_id=p.member_id ORDER BY p.created DESC');
      $posts = mysqli_query($db,$sql) or die (mysqli_error($db));
      // デバッグ
      // var_dump($sql);
      // echo htmlspecialchars($posts['nick_name'], ENT_QUOTES, 'UTF-8');
      // $nick_name = htmlspecialchars($posts['nick_name'], ENT_QUOTES, 'UTF-8');
      // $tweet = htmlspecialchars($posts['tweet'], ENT_QUOTES, 'UTF-8');
      // $picture_path = htmlspecialchars($posts['picture_path'], ENT_QUOTES, 'UTF-8');


      // 返信の場合
      if (isset($_REQUEST['res'])) {
        $sql=sprintf('SELECT m.nick_name, m.picture_path, p.* FROM members m, tweets p WHERE m.member_id=p.member_id AND p.member_id =%d ORDER BY p.created DESC',
          mysqli_real_escape_string($db, $REQUEST['res'])
         );
        $record = mysqli_query($db, $sql) or die(mysqli_error($db));
        $table = mysqli_fetch_assoc($record);
        $message = '@' . $table['nick_name'] . ' ' . $table['tweet'];

        $tweet=htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $member_id = htmlspecialchars($_REQUEST['res'], ENT_QUOTES, 'UTF-8');
      }
 ?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="../assets/css/bootstrap.css" rel="stylesheet">
    <link href="../assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="../assets/css/form.css" rel="stylesheet">
    <link href="../assets/css/timeline.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ<?php echo $nick_name; ?>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <li><a href="index.php" class="btn btn-default">前</a></li>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <li><a href="index.php" class="btn btn-default">次</a></li>
          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">
      <?php while ($post = mysqli_fetch_assoc($posts)):  ?>
        <div class="msg">
          <img src='./member_picture/<?php echo htmlspecialchars($post['picture_path'], ENT_QUOTES, 'UTF-8'); ?>' width="48" height="48" alt="<?php echo htmlspecialchars($post['nick_name'], ENT_QUOTES, 'UTF-8'); ?>">
          <p>
            <?php echo htmlspecialchars($post['tweet'], ENT_QUOTES, 'UTF-8'); ?><span class="name"> (<?php echo htmlspecialchars($post['nick_name'], ENT_QUOTES, 'UTF-8'); ?>) </span>
            [<a href="index.php?res=<?php echo htmlspecialchars($post['member_id'],ENT_QUOTES, 'UTF-8'); ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.php">
              2016-01-28 18:04
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
      <?php endwhile; ?>
        <!-- <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            つぶやき３<span class="name"> (Seed kun) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.html">
              2016-01-28 18:03
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
        <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            つぶやき２<span class="name"> (Seed kun) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.html">
              2016-01-28 18:02
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
        <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            つぶやき１<span class="name"> (Seed kun) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.html">
              2016-01-28 18:01
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
      </div>

    </div> -->
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
