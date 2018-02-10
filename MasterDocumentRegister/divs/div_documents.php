<?php
include "./scripts/connection.php";
include "./scripts/table.php";

$doc_columns = array(
    array('project_entity', 'Entity', 'select', "", 'none', "", 'entity'),
    array('project_year', 'Year', 'select', "", 'none', "", 'year'),
    array('project_number', 'Number', 'select', "", 'none', "", 'pr_num'),
    array('doc_discipline', 'Discipline', 'select', "", 'none', "", 'disc'),
    array('doc_type', 'Doc Type', 'select', "", 'none', "", 'doc_type'),
    array('doc_number', 'Doc number', 'select', "", 'none', "", 'doc_num'),
    array('doc_name', 'Doc Name', 'text', "", 'none', "", 'doc_name'),
    array('client_doc_number', 'Client Doc number', 'text', "", 'none', "", 'client_doc_number'),
    array('description', 'Detailed Description', 'text', "", 'none', "", 'long_description'));
$rev_columns = array(
    array('project_entity', 'Entity'),
    array('project_year', 'Year'),
    array('project_number', 'Number'),
    array('doc_discipline', 'Discipline'),
    array('doc_type', 'Doc Type'),
    array('doc_number', 'Doc number'),
    array('doc_rev', 'Revision'),
    array('rev_date', 'Revision Date'));

$num_rows_sel = 20;
$pagenum_sel = 1;
$table_name = "documents";

//Update when form is submitted (onchange!) and filters, amount of pages to show and pagenumber are updated
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pagenum_sel = $_POST["pagenum_docs"];
    $num_rows_sel = $_POST["num_rows_docs"];
    $k = 0;
    foreach($doc_columns as $col) {
        $doc_columns[$k][3] = $_POST["filter_".$doc_columns[$k][0]];
        $doc_columns[$k][4] = $_POST["order_inp_".$doc_columns[$k][0]];
        if($_POST["order_num_".$doc_columns[$k][0]] == "") {
            $doc_columns[$k][5] = "";
        } else {
            $doc_columns[$k][5] = $_POST["order_num_".$doc_columns[$k][0]];
        };
        $k++;
    };
};

// Create query string for conditions and order
$string_conditions = create_condition_string((array) $doc_columns);
$string_order = create_order_string((array) $doc_columns);

// Trial query to determine number of rows and input for filters
$query1 = "SELECT * FROM {$table_name} {$string_conditions};";
$res = performQuery($query1);

//Determine number of rows, maximum number of pages and input for select options
$count = pg_num_rows($res);
$max_pagenum_docs = (int) ceil($count / $num_rows_sel);
$pagenum_options = determine_pagenum_options($count);
$doc_columns = sel_input($doc_columns, $res);

// Retrieve final table to be presented
$offset = ($pagenum_sel - 1) * $num_rows_sel;
$query2 = "SELECT * FROM {$table_name} {$string_conditions} {$string_order} OFFSET '{$offset}' LIMIT '{$num_rows_sel}' ;";
$data = performQuery($query2);
$docs = pg_fetch_all($data);
?>

<div id="docs" class="tabcontent">
    <h3 id="doc_title">Documents</h3>
    <p>
        <a href="add_doc.php">Create New Document</a>
    </p>
    <div>
        <form id="documents_table_form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <table id="doc_grid" class="table">
                <thead>
					<tr>
                        <?php foreach($doc_columns as $col) {echo '<th class="'.$col[6].'">'.$col[1].str_repeat('&nbsp;', 1).'<i class="'.$col[4].'" id="order_'.$col[0].'"></i><input type="text" class="invis" id="order_inp_'.$col[0].'" name="order_inp_'.$col[0].'" value="'.$col[4].'"><input type="text" class="invis" id="order_num_'.$col[0].'" name="order_num_'.$col[0].'" value="'.$col[5].'"></th>';} ?>
						<th class="float_right" colspan="2">Docs per page:</th>
						<th><select class="numtable" name="num_rows_docs" onchange="this.form.submit();"><?php foreach($pagenum_options as $num) {echo '<option value="'.$num.'" '.(($num_rows_sel == $num) ? 'selected' : "").'>'.$num.'</option>';} ?></select></th>
					</tr>
                    <tr>
                        <?php foreach($doc_columns as $col) {
                                  echo '<th>';
                                  if($col[2] == 'select')  {
                                      echo '<select class="full_width" name="filter_'.$col[0].'" onchange="this.form.submit();">';
                                      foreach($col[7] as $option){
                                          echo '<option value="'.$option.'" '.(($col[3] == $option) ? 'selected' : "").'>'.$option.'</option>';
                                      }
                                      echo '</select>';
                                  } elseif ($col[2] == 'text'){
                                      echo '<input class="full_width" type="text" name="filter_'.$col[0].'" value="'.$col[3].'" onchange="this.form.submit();">';
                                  };
                                  echo '</th>';
                              };?>
                        <th class="float_right">
                            <button onclick="firstPage();">&laquo;</button>
                            <button onclick="prevPage(<?php echo $pagenum_sel ?>);">&lsaquo;</button>
                        </th>
                        <th>
                            <select id="sel_pagenum" class="numtable" name="pagenum_docs" onchange="this.form.submit();">
                                <?php foreach(range(1, $max_pagenum_docs) as $num) {echo '<option value="'.$num.'" '.(($pagenum_sel == $num) ? 'selected' : "").'>'.$num.'</option>';} ?>
                            </select>
                        </th>
                        <th>
                            <button onclick="nextPage(<?php echo $pagenum_sel.', '.$max_pagenum_docs ?>);">&rsaquo;</button>
                            <button onclick="LastPage(<?php echo $max_pagenum_docs ?>);"> &raquo;</button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach((array) $docs as $doc):?>
                    <tr class="accordion" onclick="openAccordion(this)">
                        <?php foreach($doc_columns as $doc_column) {echo '<td>'.$doc[$doc_column[0]].'</td>'; } ?>
                        <td><a href="javascript:alert('functionality not yet included');" target="_blank" class="btn btn-addRev">Add Rev</a></td>
                        <td><a href="javascript:alert('functionality not yet included');" target="_blank" class="btn btn-edit">Edit</a></td>
                        <td><a href="javascript:alert('functionality not yet included');" target="_blank" class="btn btn-delete">Delete</a></td>
                    </tr>
                    <?php
                    $query3 = "SELECT * FROM doc_revisions WHERE project_entity = '{$doc['project_entity']}' AND project_year = '{$doc['project_year']}' AND project_number = '{$doc['project_number']}' AND doc_discipline = '{$doc['doc_discipline']}' AND doc_type = '{$doc['doc_type']}' AND doc_number = '{$doc['doc_number']}'";
                    $data = performQuery($query3);
                    $revs = pg_fetch_all($data);
                    if (!empty($revs)) {
                        echo '<tr class="panel">';
                            foreach($rev_columns as $rev_column) {echo '<th>'.$rev_column[1].'</th>';}
                        echo '</tr>';
                        foreach((array) $revs as $rev){
                            echo '<tr class="panel">';
                                foreach($rev_columns as $rev_column) {
                                    echo '<td>'.$rev[$rev_column[0]].'</td>';
                                };
                                echo '<td><a href="#" target="_blank" class="btn btn-link">Link</a></td>';
                                echo '<td><a href="#" target="_blank" class="btn btn-edit">Edit</a></td>';
                                echo '<td><a href="#" target="_blank" class="btn btn-delete">Delete</a></td>';
                            echo '</tr>';
                        };
                    } else {
                        echo '<tr class="panel"><td colspan="9">No revisions in database</td></tr>';
                    };
                    ?>
                    <?php endforeach;?>
                </tbody>
            </table>
        </form>
    </div>
</div>

<script type="text/javascript">
    //Create onclick javascript functions for order of table
    <?php order_js((array) $doc_columns, $table_name) ?>
</script>
