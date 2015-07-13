<?php
      require_once "constants.inc";
      $showall=0;
      if(isset($_GET["showall"]))
            $showall=$_GET["showall"];
      

	#set up connection to Biblio (Drupal)
      $biblio_conn = mysql_connect(DATABASE_SERVER, DATABASE_USER, DATABASE_PWD);
      
      if($showall==0)

            $get_log_sql = sprintf("select record_id, log_message, processed, process_method, failures
                                    from ".DATABASE_NAME.".b2f_log
                                    order by processed desc
                                    limit 0,100");

      else
            $get_log_sql = sprintf("select record_id, log_message, processed, process_method, failures
                                    from ".DATABASE_NAME.".b2f_log
                                    order by processed desc");

      $results = mysql_query($get_log_sql, $biblio_conn);
      if(!$results){
            echo "Query failed to execute.";
      }


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<title>Its big!  Its heavy!  Its wood!  Its better than bad, its good.</title>
      <script type="text/javascript" src="../../misc/jquery.js"></script>
      <style type="text/css">
            body {font-size:0.85em;font-family:verdana, sans-serif;}
            fieldset {border:none;}
            td {border-bottom:solid 1px #e0e0e0;}
            th {text-align:left;border-bottom:solid 1px #666666;}
            div.tabstrip {border-bottom:solid 1px #999999;width:90%;padding-left:4px;}
            div.tabstrip span {padding:0px 10px;text-align:center;background-color:#f0f0f0;border:solid 1px #999999;margin:0px 2px;cursor:pointer;border-bottom:none;}
            div.tabstrip span.active {padding:1px 10px;text-align:center;background-color:#ebf1ff;border:solid 1px #999999;margin:0px 2px;cursor:pointer;border-bottom:none;}
            div.tabbody {padding-top:10px;width:90%;}
            tr.alt {background-color:#f9f9f9;}
            blockquote.err {background-color:#ffbbbb;border:dashed 1px #666666;padding:5px;font-family:courier}
            div.popup {position:absolute;top:50px;left:200px;width:400px;height:300px;background-color:#ebf1ff;display:none;}
            popup div.head {border-bottom:solid 1px #666666;background-color:#6699ff;padding:3px;cursor:pointer;text-align:center;}
      </style>

</head>
<body>


      <h1>Biblio to Fedora Log</h1>
      

<?php

if($showall==0)
      echo '<p>Last 100 records.&nbsp;&nbsp;<a href="log.php?showall=1">Show All.</a></p>';
else
      echo '<p>All records.&nbsp;&nbsp;<a href="log.php?showall=0">Show only last 100.</a></p>';

      if($error!='')
            echo '<blockquote class="err">'.$error.'</blockquote>';

?>

      <table width="100%" cellpadding="2" cellspacing="0">
      <tr>
            <th>Process Method</th>
            <th>Timestamp</th>
            <th>Message</th>
            <th>Failures (click to retry)</th>
      </tr>

      <?php
            $rownum = 0;
            while($row = mysql_fetch_assoc($results)){
   
                  $fails = '';
                  if($row["failures"]!==''){
                        $aFails = split(',', $row["failures"]);
                        for($i=0;$i<count($aFails);$i++){
                              $fails .= '<a href="javascript:void(\'\');" onclick="retry_item(this, '.$aFails[$i].');">'.$aFails[$i].'</a> ';
                        }
   
                  }

                  if($rownum % 2 == 0)
                        echo '<tr class="alt" id="row_'.$row["record_id"].'">';
                  else
                        echo '<tr id="row_'.$row["record_id"].'">';

                  echo '<td>'.$row["process_method"].'</td>
                        <td>'.$row["processed"].'</td>
                        <td>'.$row["log_message"].'</td>
                        <td>'.$fails;
         
                  if($row["fedora_errors"]!='')
                        echo '  Also, there were <a href="javascript:void(\'\');" onclick="show_f_errors('.$rownum.');">Fedora errors</a>.<div id="fe_'.$rownum.'" class="popup"><div class="head" onclick="close_popup(\'fe_'.$rownum.'\');">click to close</div>'.$row["fedora_errors"].'</div></td>';
         
                  echo '</td>';
                  echo '</tr>';

                  $rownum++;
            }
      ?>

      </table>


      <script type="text/javascript"><!--
            function retry_item(anchor, nid){
                  $.ajax({
                        type: "POST",
                        url: "execute.php",
                        data: "nid="+nid,
                        failure: function(msg){
                              anchor.title='Failed again!';
                        },
                        success: function(msg){
                              anchor.style.textDecoration='strike-though';
                              anchor.style.backgroundColor='#66cc66';
                              anchor.title='Success!';
                        }
                  });
            }

            function show_f_errors(id){
                  document.getElementById(id).style.display='block';
            }

            function close_popup(id){
                  document.getElementById(id).style.display='none';
            }
      //--></script>


</body></html>
