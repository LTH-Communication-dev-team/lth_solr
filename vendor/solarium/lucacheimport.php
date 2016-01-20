<?php
    require(__DIR__.'/init.php');

    error_reporting(E_ALL ^ E_NOTICE);
    
    // create a client instance
    //$client = new Solarium\Client($config);

    // get an update query instance
    //$update = $client->createUpdate();
    
    //tslib_eidtools::connectDB();
    
    $dbhost = "db.ddg.lth.se";
    $db = "users";
    $user = "lucache";
    $pw = "5ipsD3R2XA8wWEhm";
    $conn = mysql_connect($dbhost, $user, $pw) or die("18; ".mysql_error());
    $database = mysql_select_db($db);
    
    $res = mysql_query('select * from lucache_employee');
    
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            // create a new document for the data
            // please note that any type of validation is missing in this example to keep it simple!
            /*$doc = $update->createDocument();

            $doc->id = $row['uid'];
            $doc->display_name_s = $row['display_name'];
            $doc->first_name_s = $row['first_name'];
            $doc->last_name_s = $row['last_name'];
            $doc->email_s = $row['email'];
            $doc->ou_s = $row['ou'];
            $doc->title_s = $row['title'];
            $doc->orgid_s = $row['orgid'];
            $doc->primary_affiliation_s = $row['primary_affiliation'];
            $doc->homepage_s = $row['homepage'];
            $doc->lang_s = $row['lang'];
            $doc->degree_s = $row['degree'];
            $doc->degree_en_s = $row['degree_en'];
            $doc->phone_s = $row['phone'];
            $doc->hide_on_web_s = $row['hide_on_web'];*/
            

            // add the document and a commit command to the update query
            //$update->addDocument($doc);
            //$update->addCommit();
            // this executes the query and returns the result
            /*$result = $client->update($update);
            echo '<b>Update query executed</b><br/>';
            echo 'Query status: ' . $result->getStatus(). '<br/>';
            echo 'Query time: ' . $result->getQueryTime();*/
            print $row['display_name'];
    }
    
    //$GLOBALS['TYPO3_DB']->sql_free_result($res);
    mysql_free_result($res);