<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>mission_5</title>
</head>

<body>
    <?php
    $dsn = 'データベース名';
    $user = 'ユーザ名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    //ATTR_ERRMODEはエラー時の対応。SILENT(報告なし、デフォ)、EXCEPTION(スルー)、WARNING(警告)の三種類ある
    //DBに接続
    if (!empty($_POST["sub1"])) {
        //$_POST時はif(!empty($_POST[]))をしないと初回アクセスの際変数が未定義とエラー吐かれる
        $comment = $_POST["comment"];
        $name = $_POST["name"];
        $hidden_number = $_POST["number"];
        $pass = $_POST["pass"];
        //入力されたデータの回収
        $date = date("Y/m/d H:i:s");
        //日付の取得
        //投稿ボタンが押された時の挙動
        if ($hidden_number) {
            //編集時の挙動    
            //変更対象番号を入れる
            $sql = 'UPDATE DB SET name = :name,comment = :comment WHERE id = :id';
            //:hogeは空箱を表している(名前付きプレースホルダー)
            $stmt = $pdo->prepare($sql);
            //穴埋めの状態で文を作る
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':id', $hidden_number, PDO::PARAM_INT);
            //(空箱, 中身, PDO::PARAM_(STRやINTなどに変換))
            $stmt->execute();
            //実行
        } else {
            //投稿時の挙動
            $sql = $pdo->prepare("INSERT INTO DB (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)");
            //"INSERT INTO テーブル名(列のタイトル) VALUES(各列の中身)"
            //prepareの場合ユーザからの入力を利用する。queryだとしない。
            //プリペアドステートメントで変更箇所だけ変数のようにする。
            $sql->bindParam(':name', $name, PDO::PARAM_STR);
            $sql->bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql->bindParam(':date', $date, PDO::PARAM_STR);
            $sql->bindParam(':pass', $pass, PDO::PARAM_STR);
            //bindParamでplaceholderに値を入れる
            $sql->execute();
            //executeでプリペアードステートメントの実行
        }
        header("Location:" . $_SERVER['PHP_SELF']);
    } elseif (!empty($_POST["sub2"])) {
        $del = $_POST["delete"];
        $PASS = $_POST["PASS"];
        $sql = 'SELECT * FROM DB where id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $del, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        foreach ($results as $row) {
        }
        //削除対象のpassをとってくる
        if ($PASS == $row['pass']) {
            //パスワードが一致するか確認して
            //削除時の挙動
            $sql = 'delete from DB where id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $del, PDO::PARAM_INT);
            $stmt->execute();
        }
    } elseif (!empty($_POST["sub3"])) {
        //編集ボタンが押された時の挙動
        $edit = $_POST["edit"];
        $PASS = $_POST["PASS"];
        $sql = 'SELECT * FROM DB where id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $edit, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        foreach ($results as $row) {
        }
        //編集対象のpassをとってくる
        if ($PASS == $row['pass']) {
            //パスワードが一致するか確認して
            //編集番号が入力された時の挙動
            $sql = 'SELECT * FROM DB where id = :id';
            //"SELECT コード名 FROM テーブル名"、変数へ格納
            //オプションでORDER BY(並べ替え)、WHERE(一致したものだけ)
            $stmt = $pdo->prepare($sql);
            //差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $edit, PDO::PARAM_INT);
            //その差し替えるパラメータの値を指定してから、
            $stmt->execute();
            //SQLを実行する。
            $results = $stmt->fetchAll();
            //全配列を返す
            foreach ($results as $row) {
                //$rowの中にはテーブルのカラム名が入る
                $value0 = $row[0];
                $value1 = $row[1];
                $value2 = $row[2];
            }
        }
    }
    //表示パート
    $sql = 'SELECT * FROM DB';
    //"SELECT コード名 FROM テーブル名"、変数へ格納
    //オプションでORDER BY(並べ替え)、WHERE(一致したものだけ)
    $stmt = $pdo->prepare($sql);
    //差し替えるパラメータを含めて記述したSQLを準備し
    $stmt->execute();
    //SQLを実行する。
    $results = $stmt->fetchAll();
    //全配列を返す
    foreach ($results as $row) {
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id'] . ',';
        echo $row['name'] . ',';
        echo $row['comment'] . ',';
        echo $row['date'] . ',';
        echo $row['pass'] . '<br>';
        echo "<hr>";
        //パスワードが掲示板に表示されている状態。passをエコーしなければ外からは見えない
    }
    ?>
    <!--htmlでページに表示するゾーン-->
    <fieldset style="display:inline-block">
        <!--投稿ゾーン
        入力フォームのグループ化-->
        <form action="" method="post">
            <!--actionで送信先URLを指定。
            methodは内容の属性でget（デフォ）はURLでmethodは本文-->
            <input type="text" name="name" placeholder="名前" value=<?php if (!empty($value1)) {
                                                                        echo $value1;
                                                                    } ?>> <br>
            <input type="text" name="comment" placeholder="本文" value=<?php if (!empty($value2)) {
                                                                            echo $value2;
                                                                        } ?>> <br>
            <input type="password" name="pass" placeholder="パスワード">
            <input type="hidden" name="number" value=<?php if (!empty($value0)) {
                                                            echo $value0;
                                                        } ?>>
            <!--input type="text"はinputboxで"submit"は送信ボタン
            valueで初期値を設定でplaceholderだと半透明
            <?php ?>でくくるとphpの関数や変数を扱える-->
    </fieldset>
    <input type="submit" name="sub1">
    </form>
    <fieldset style="display:inline-block">
        <!--削除ゾーン-->
        <form action="" method="post">
            <input type="number" name="delete" placeholder="削除対象番号"> <br>
            <input type="password" name="PASS" placeholder="パスワード"> <br>
    </fieldset>
    <input type="submit" name="sub2">
    </form>
    <fieldset style="display:inline-block">
        <!--編集ゾーン-->
        <form action="" method="post">
            <input type="number" name="edit" placeholder="編集対象番号"> <br>
            <input type="password" name="PASS" placeholder="パスワード"> <br>
    </fieldset>
    <input type="submit" name="sub3">
    </form>
</body>

</html>