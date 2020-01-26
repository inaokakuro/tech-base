<!DOCTYPE html>
<html lang = “ja”>
  <head>
    <meta charset = “UFT-8”>
    <title>mission5-1</title>
  </head>
  <body>
    <h1>DBを用いたWeb掲示板！</h1>
    <?php
      $dsn = 'mysql:dbname=***;host=localhost'; #データソース名
      $username = '***'; #ユーザーネーム
      $password_db = 'PASSWORD'; #パスワード
      $pdo = new PDO($dsn, $username, $password_db, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

      $edit_true_num = NULL;
      $edit_name = NULL;
      $edit_comment = NULL; #一応初期化しておく、実際は無くても大丈夫っぽい

      if(isset($_POST["comment"]) and isset($_POST["name"])){ #投稿が押された時
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $date = date("Y/m/d H:i:s");
        $password = $_POST["password"];
        if($_POST["change_num"] != NULL){ #編集モード
          $change_num = $_POST["change_num"];
          $change_num_int = (int)$change_num;
          $sql = $pdo->prepare('UPDATE db_bbs SET name=:name, comment=:comment, date=:date, password=:password WHERE id=:id');
          $sql->bindParam(':name', $name, PDO::PARAM_STR);
          $sql->bindParam(':comment', $comment, PDO::PARAM_STR);
          $sql->bindParam(':date', $date, PDO::PARAM_STR);
          $sql->bindParam(':password', $password, PDO::PARAM_STR);
          $sql->bindParam(':id', $change_num_int, PDO::PARAM_INT);
          $sql->execute(); #編集した投稿をDBに格納
        }
        else{ #投稿モード
          $sql = $pdo->prepare("INSERT INTO db_bbs (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
        	$sql->bindParam(':name', $name, PDO::PARAM_STR);
        	$sql->bindParam(':comment', $comment, PDO::PARAM_STR);
          $sql->bindParam(':date', $date, PDO::PARAM_STR);
          $sql->bindParam(':password', $password, PDO::PARAM_STR);
        	$sql->execute(); #新しい投稿をDBに格納
        }
      }
      elseif(isset($_POST["delete_num"])){ #削除が押された時
        $delete_num = $_POST["delete_num"];
        $delete_num_int = (int)$delete_num;
        $password_judge = $_POST["password_judge"];
        $sql = 'SELECT * FROM db_bbs WHERE id=:id'; #該当番号のデータのみ読み出す
        $stmt = $pdo->prepare($sql); #SQL実行
        $stmt->bindParam(':id', $delete_num_int, PDO::PARAM_INT);
        $stmt->execute();
        $delete_data = $stmt->fetchAll(); #table_dataに格納、array型(格納され方はmission_5-1-5参照)
        if(empty($delete_data)){
          echo "該当する番号の投稿はありません！";
        }
        else{
          foreach($delete_data as $row){
            if($row['id'] == $delete_num_int){
              if($password_judge != $row['password']){
                echo "パスワードが違います！";
              }
              else{
              	$sql = 'DELETE FROM db_bbs WHERE id=:id'; #idを指定して削除
              	$stmt = $pdo->prepare($sql);
              	$stmt->bindParam(':id', $row['id'], PDO::PARAM_INT);
              	$stmt->execute();
              }
            }
          }
        }
      }
      elseif(isset($_POST["edit_num"])){ #編集が押された時
        $edit_num = $_POST["edit_num"];
        $edit_num_int = (int)$edit_num;
        $password_judge = $_POST["password_judge"];
        $sql = 'SELECT * FROM db_bbs WHERE id=:id'; #該当番号のデータのみ読み出す
        $stmt = $pdo->prepare($sql); #SQL実行
        $stmt->bindParam(':id', $edit_num_int, PDO::PARAM_INT);
        $stmt->execute();
        $edit_data = $stmt->fetchAll(); #table_dataに格納、array型(格納され方はmission_5-1-5参照)
        if(empty($edit_data)){
          echo "該当する番号の投稿はありません！";
        }
        else{
          foreach ($edit_data as $row){
            if($row['id'] == $edit_num_int){
              if($password_judge != $row['password']){
                echo "パスワードが違います！";
              }
              else{ #パスワードが一致した時のみ編集モードへ移行
                $edit_true_num = $row['id'];
                $edit_name = $row['name'];
                $edit_comment = $row['comment'];
                echo $row['id']."を編集します！";
              }
            }
          }
        }
      }
    ?>
    <form action="" method="POST">
      <input type="hidden" name="change_num" value="<?php echo $edit_true_num;?>"><br>
      名前：<input type="text" name="name" value="<?php echo $edit_name;?>" required="required"><br>
      コメント：<input type="text" name="comment" value="<?php echo $edit_comment;?>" required="required">
      パスワード：<input type="password" name="password" value="" required="required">
      <input type="submit" name="submit" value="投稿"><br>
    </form>
    <form action="" method="POST">
      削除番号：<input type="text" name="delete_num" value="" required="required">
      パスワード：<input type="password" name="password_judge" value="" required="required">
      <input type="submit" name="delete" value="削除"><br>
    </form>
    <form action="" method="POST">
      編集番号：<input type="text" name="edit_num" value="" required="required">
      パスワード：<input type="password" name="password_judge" value="" required="required">
      <input type="submit" name="edit" value="編集"><br>
    </form>
    <?php
      #DBの内容を読み出し・出力
      $sql = 'SELECT * FROM db_bbs'; #データベースからデータを取り出す、*で全部指定(*の部分に要素を入れればその要素だけ取り出せる)
      $stmt = $pdo->query($sql); #実行、stmtはPODstatementの略らしい
      $table_data = $stmt->fetchAll(); #結果セットに残っている全ての行をフェッチして$resultsに入れる、PODstatementからarrayに、
      echo "<hr>";
      foreach ($table_data as $row){
        echo $row['id']." ".$row['name']." ".$row['comment']." ".$row['date']." "."<br>";
        echo "<hr>"; #水平の横線
      }
    ?>
  </body>
</html>
