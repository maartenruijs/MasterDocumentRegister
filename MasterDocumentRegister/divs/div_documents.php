<?php
$query2 = "SELECT * FROM documents";
$docs = performQuery($query2);
$doc_columns = array(
    array('project_entity', 'Entity'),
    array('project_year', 'Year'),
    array('project_number', 'Number'),
    array('doc_discipline', 'Discipline'),
    array('doc_type', 'Doc Type'),
    array('doc_number', 'Doc number'),
    array('doc_name', 'Doc Name'),
    array('client_doc_number', 'Client Doc number'),
    array('description', 'Detailed Description'));
$rev_columns = array(
    array('project_entity', 'Entity'),
    array('project_year', 'Year'),
    array('project_number', 'Number'),
    array('doc_discipline', 'Discipline'),
    array('doc_type', 'Doc Type'),
    array('doc_number', 'Doc number'),
    array('doc_rev', 'Revision'),
    array('rev_date', 'Revision Date'));
?>

<div id="Documents" class="tabcontent">
    <h3>Documents</h3>
    <p>
        <a href="add_doc.php">Create New Document</a>
    </p>
    <table id="document_grid" class="table" width="100%" cellspacing="0">
        <thead>
            <tr>
                <?php foreach($doc_columns as $doc_column) {echo '<th>'.$doc_column[1].'</th>';} ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach((array) $docs as $doc):?>
            <tr class="accordion" onclick="openAccordion(this)">
                <?php foreach($doc_columns as $doc_column) {echo '<td>'.$doc[$doc_column[0]].'</td>'; } ?>
                <td>
                    <div class="btn-group" data-toggle="buttons">
                        <a href="#" target="_blank" class="btn btn-edit">Edit</a>
                        <a href="#" target="_blank" class="btn btn-delete">Delete</a>
                        <a href="#" target="_blank" class="btn btn-view">View</a>
                    </div>
                </td>
            </tr>
            <?php
                      $query3 = "SELECT * FROM doc_revisions WHERE project_entity = '{$doc['project_entity']}' AND project_year = '{$doc['project_year']}' AND project_number = '{$doc['project_number']}' AND doc_discipline = '{$doc['doc_discipline']}' AND doc_type = '{$doc['doc_type']}' AND doc_number = '{$doc['doc_number']}'";
                      $revs = performQuery($query3);
                      if (!empty($revs)) {
                          echo '<tr class="panel">';
                          foreach($rev_columns as $rev_column) {echo '<th>'.$rev_column[1].'</th>';}
                          echo '</tr>';
                          foreach((array) $revs as $rev){
                              echo '<tr class="panel">';
                              foreach($rev_columns as $rev_column) {
                                  echo '<td>'.$rev[$rev_column[0]].'</td>';
                              };
                              echo '</tr>';
                          };
                      } else {
                          echo '<tr class="panel"><td colspan="100">No revisions in database</td></tr>';
                      };

            ?>
            <?php endforeach;?>
        </tbody>
    </table>
</div>