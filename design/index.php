<?php 
    session_start();
    require('dbconnect.php');

    // htmlspecialcharsのショートカット
    function h($value){
      return htmlspecialchars($value,ENT_QUOTES, 'UTF-8');
    }
    // 本文何のURLにリンクを設定します
    // function makeLink($value){
    //   return mb_ereg_replace("(https?)(://[:alnum:]¥+¥$S¥;¥?¥.%,!#~*/:@&=_-]+)", '<a href="¥1¥2">¥1¥2</a>', $value);
    // }

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

      if (isset($_GET['res'])&&!empty($_POST)) {
        $sql = sprintf('INSERT INTO tweets SET member_id = %d, tweet="%s", reply_tweet_id ="%d", created = NOW()',
              mysqli_real_escape_string($db, $member['member_id']),
              mysqli_real_escape_string($db,$_POST['tweet']),
              mysqli_real_escape_string($db,$_GET['res'])
            );
          // デバッグ用
          // var_dump($sql);
          mysqli_query($db, $sql) or die(mysqli_error($db));
          header('Location:index.php');
          exit();
      }

      if (!empty($_POST)) {
        if ($_POST['tweet'] != '') {
          $sql = sprintf('INSERT INTO tweets SET member_id = %d, tweet="%s", reply_tweet_id ="0", created = NOW()',
              mysqli_real_escape_string($db, $member['member_id']),
              mysqli_real_escape_string($db,$_POST['tweet'])
            );
          //デバッグ
          // var_dump($sql);
          mysqli_query($db, $sql) or die(mysqli_error($db));
          header('Location:index.php');
          exit();
        }
      }

      // 投稿を取得する
      if (isset($_REQUEST['page'])) {
      $page = $_REQUEST['page'];
      }

      if (empty($_REQUEST['page'])) {
       $page = 1;
      }

      $page = max($page,1);

      // 最終ページを取得する
           //$sql = 'SELECT COUNT(*) AS cnt FROM tweets';

      // ②必要なページ数を計算する
   if (isset($_GET['search_word']) && !empty($_GET['search_word'])) {
     $sql = sprintf('SELECT COUNT(*) AS cnt FROM `tweets` WHERE `tweet` LIKE "%%%s%%"',
       mysqli_real_escape_string($db, $_GET['search_word'])
     );
   } else {
     $sql = 'SELECT COUNT(*) AS cnt FROM `tweets`';
   }

        

      $recordSet = mysqli_query($db, $sql);
      $table = mysqli_fetch_assoc($recordSet);
      $maxPage = ceil($table['cnt']/5);
      $page = min($page, $maxPage);

      $start = ($page - 1) * 5;
      $start = max(0, $start);
    

      //最終ページ取得
      // $sql = sprintf('SELECT m.nick_name, m.picture_path, t.* FROM members m, tweets t WHERE m.member_id=t.member_id AND delete_frag=0 ORDER BY t.created DESC LIMIT %d ,5',
      //   $start
      //   );

            // 検索の場合
   if (isset($_GET['search_word']) && !empty($_GET['search_word'])) {
     $sql = sprintf('SELECT m.`nick_name`, m.`picture_path`, t.* FROM `tweets` t, `members` m WHERE t.`member_id` = m.`member_id` AND t.`tweet` LIKE "%%%s%%" ORDER BY t.`created` DESC LIMIT %d, 5',
         mysqli_real_escape_string($db, $_GET['search_word']),
         $start
      );

   } else {
     // 投稿内容（表示するページ分）を取得する
     $sql = sprintf('SELECT m.`nick_name`, m.`picture_path`, t.* FROM `tweets` t, `members` m WHERE t.`member_id` = m.`member_id` ORDER BY t.`created` DESC LIMIT %d, 5',
         $start
       );
   }

      $posts = mysqli_query($db,$sql) or die (mysqli_error($db));




      // // 投稿を取得する（before）
      // $sql = sprintf ('SELECT m.nick_name, m.picture_path, p.* FROM members m, tweets p WHERE m.member_id=p.member_id AND delete_frag=0 ORDER BY p.created DESC');
      // $posts = mysqli_query($db,$sql) or die (mysqli_error($db));
      // //// デバッグ
      // // var_dump($sql);
      // // echo htmlspecialchars($posts['nick_name'], ENT_QUOTES, 'UTF-8');
      // // $nick_name = htmlspecialchars($posts['nick_name'], ENT_QUOTES, 'UTF-8');
      // // $tweet = htmlspecialchars($posts['tweet'], ENT_QUOTES, 'UTF-8');
      // // $picture_path = htmlspecialchars($posts['picture_path'], ENT_QUOTES, 'UTF-8');
      

      // 返信の場合
      if (isset($_GET['res'])) {
        $sql=sprintf('SELECT m.nick_name, m.picture_path, p.* FROM members m, tweets p WHERE m.member_id=p.member_id AND p.tweet_id =%d ORDER BY p.created DESC',
          mysqli_real_escape_string($db, $_GET['res'])
         );
        var_dump($sql);
        $record = mysqli_query($db, $sql) or die(mysqli_error($db));
        $table = mysqli_fetch_assoc($record);
        $message = '@' . $table['nick_name'] . ' ' . $table['tweet'];

        // デバッグ用
        // echo $table['nick_name'].'<br>';
        // echo $message.'<br>';

        $tweet=htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        // デバッグ
        // echo $tweet;

        $tweet_id = htmlspecialchars($_GET['res'], ENT_QUOTES, 'UTF-8');
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
                <?php if (isset($_GET['res'])): ?>
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"><?php echo $tweet; ?></textarea>
                <?php else: ?>
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
                <?php endif ?>
              </div>
            </div>
          <ul class="paging">
              <?php
               $word = '';
               if (isset($_GET['search_word'])) {
                 $word = '&search_word=' . $_GET['search_word'];
               }
             ?>
            <input type="submit" class="btn btn-info" value="つぶやく">
            <!-- POSTにhiddenにて登録 -->
            <input type="hidden" name="reply_tweet_id" value="<?php echo h($_GET['res']); ?>">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <?php if ($page>1): ?>
                <li><a href="index.php?page=<?php print($page -1); ?><?php echo $word; ?>" class="btn btn-default">前</a></li>
               &nbsp;&nbsp;|&nbsp;&nbsp;

                <?php else: ?>
                <li class="btn">前</li>
               &nbsp;&nbsp;|&nbsp;&nbsp;
                <?php endif ?>
                <?php if ($page < $maxPage): ?>
                <li><a href="index.php?page=<?php print ($page+1); ?><?php echo $word; ?>" class="btn btn-default">次</a></li>
                <?php else: ?>
                <li class="btn">次</li>
                <?php endif ?>
          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">

       <!-- 検索ボックス -->
         <form action="" method="get" class="form-horizontal" role="form">
          <?php if (isset($_GET['search_word']) && !empty($_GET['search_word'])): ?>
            <input type="text" name="search_word" value="<?php echo $_GET['search_word']; ?>">
           <?php else: ?>
            <input type="text" name="search_word" value="">
           <?php endif; ?>
           <input type="submit" class="btn btn-success btn-xs" value="検索">
         </form>


      <?php while ($post = mysqli_fetch_assoc($posts)):  ?>
        <div class="msg">
          <img src='./member_picture/<?php echo htmlspecialchars($post['picture_path'], ENT_QUOTES, 'UTF-8'); ?>' width="48" height="48" alt="<?php echo htmlspecialchars($post['nick_name'], ENT_QUOTES, 'UTF-8'); ?>">
          <p>
            <?php echo htmlspecialchars($post['tweet'], ENT_QUOTES, 'UTF-8'); ?><span class="name"> (<?php echo htmlspecialchars($post['nick_name'], ENT_QUOTES, 'UTF-8'); ?>) </span>
            [<a href="index.php?res=<?php echo htmlspecialchars($post['tweet_id'],ENT_QUOTES, 'UTF-8'); ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.php">
              <?php echo h($post['created']); ?>
            </a>

            <?php if (isset($post['reply_tweet_id'])&&$post['reply_tweet_id']>0): ?>
              <a href="view.php?tweet_id=<?php echo htmlspecialchars($post['tweet_id'], ENT_QUOTES, 'UTF-8'); ?>">返信元のメッセージ</a>
            <?php endif ?>

            <?php if (isset($_SESSION['id'])&&$_SESSION['id'] == $post['member_id']): ?>
           [<a href="edit.php?tweet_id=<?php echo h($post['tweet_id']); ?>" style="color: #00994C;">編集</a>]
            [<a href="delete.php?tweet_id=<?php echo h($post['tweet_id']); ?>" style="color: #F33;">削除</a>]

            <?php endif ?>

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
