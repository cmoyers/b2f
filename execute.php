<?php

#gotta call these, the classes depend on it.
require_once "constants.inc";
require_once "Biblio.class";
require_once "Bibtex.class";
require_once "Fedora.class";


$successful = 0;
$failed = 0;
$failures='';
$nid;
$timestamp;
$process_method;
$fedora_errors='';
$unprocessed=0;
$biblio = new Biblio();


#figure out what to do.  We'll first try to do a single NID.  If that doesn't work out, then we'll look for a timestamp.  Then, we'll try to load all records that do not exist in Fedora (assuming that if a PID doesn't exist in the biblio table it doesn't exist in Fedora.  If this assumption is wrong, the Fedora class will still know how to handle it but we'll just end up querying more records from Biblio than we need.  I know, boo hoo.)
if(isset($_POST["nid"])){
      $nid = $_POST["nid"];
      $process_method='single';
      $biblio->getSingleData($nid);
}
else if(isset($_POST["timestamp"])){
      $timestamp = $_POST["timestamp"];
      $process_method='batch';
      $biblio->getBatchData($timestamp);
}
else if(isset($_POST["non_existent"])){
      $unprocessed = 1;
      $process_method='!processed';
      $biblio->getUnprocessedData();
}

#did we get anything?  Launch into process.  
if($biblio->biblio_dataset){
      while($bib_row = mysql_fetch_assoc($biblio->biblio_dataset)){
            
            #get att, kwd, and author data specific for this nid and drop into strings or arrays
            $attachments;
            $keyword_string='';
            $author_string='';
            $editor_string='';
            $contributor_string='';

            #stick keywords into a string, separate each one with , and cleanup trailing comma
            if($biblio->keywords_dataset) {
                  while($row = mysql_fetch_assoc($biblio->keywords_dataset)){
                        if($row["nid"]==$bib_row["nid"]){
                              $keyword_string.=$row["word"].', ';
                        }
                  }
                  $keyword_string = trim($keyword_string, ', ');
            }

            #stick authors and editors into strings, separate each one with ' and ' and cleanup trailing ' and '
            if($biblio->authors_dataset) {
                  while($row = mysql_fetch_assoc($biblio->authors_dataset)){
                        if($row["nid"]==$bib_row["nid"]){
                              if($row["auth_type"]==1){
                                    $author_string.=$row["name"].' and ';
                              }
                              else if($row["auth_type"]==2 || $row["auth_type"]==10 || $row["auth_type"]==14){
                                    $editor_string.=$row["name"].' and ';
                              }
                        }
                  }
                  $author_string = trim($author_string, ' and ');
                  $editor_string = trim($editor_string, ' and ');
                  $contributor_string = trim($author_string . ' and ' . $editor_string, ' and ');
            }

            #files will go into an array, since we will use this a bit differently than we did the keywords and authors and editors
            if($biblio->attachments_dataset) {
                  while($row = mysql_fetch_assoc($biblio->attachments_dataset)){
                        if($row["nid"]==$bib_row["nid"]){
                              $attachments[] = $row;
                        }
                  }
            }
            
            
            # now that we have nice little arrays and a recordset pertaining to the *specific* nid we are working with, we can acutally do the heavy lifting now.

            #create Bibtex object
            $bibtex = new Bibtex($bib_row, $author_string, $editor_string, $keyword_string);

            #create fedora object
            $fedora = new Fedora($bib_row, $attachments, $contributor_string, $keyword_string, $bibtex);
            #try to update this row
            if($fedora->updateFedora()){
                  $successful++;
            } else {
                  $failed++;
                  $failures.=$bib_row["nid"].',';
            }

            #update Biblio with PID if it's not already there
            if($bib_row[BIBLIO_PID_FIELD]==null){
                  $biblio->updateBiblio($bib_row["nid"], $fedora->PID);
            }
            
            #if we got problems during the fedora load, we'll want to log the problem areas.
            $fedora_errors.=$fedora->errors;

      }

}

# log the job.  This really just adds to a table the summary of the run that just happened.
$biblio->appendLog($successful, $failed, $failures, $process_method, $fedora_errors);

echo 'complete';

?>
