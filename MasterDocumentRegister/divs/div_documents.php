<?php
include "./scripts/connection.php";
include "./scripts/table.php";

$columns = array(
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
    $pagenum_sel = $_POST["pagenum"];
    $num_rows_sel = $_POST["num_rows"];
    $k = 0;
    foreach($columns as $col) {
        $columns[$k][3] = $_POST["filter_".$columns[$k][0]];
        $columns[$k][4] = $_POST["order_inp_".$columns[$k][0]];
        if($_POST["order_num_".$columns[$k][0]] == "") {
            $columns[$k][5] = "";
        } else {
            $columns[$k][5] = $_POST["order_num_".$columns[$k][0]];
        };
        $k++;
    };
};

// Create query string for conditions and order
$string_conditions = create_condition_string((array) $columns);
$string_order = create_order_string((array) $columns);

// Trial query to determine number of rows and input for filters
$query1 = "SELECT * FROM {$table_name} {$string_conditions};";
$res_cond = performQuery($query1);
$query2 = "SELECT * FROM {$table_name};";
$res_full = performQuery($query2);

//Determine number of rows, maximum number of pages and input for select options
$count = pg_num_rows($res_cond);
$max_pagenum = (int) ceil($count / $num_rows_sel);
$pagenum_options = determine_pagenum_options($count);
$columns = sel_input($columns, $res_full);
$columns = disable_input($columns, $res_cond);

// Retrieve final table to be presented
$offset = ($pagenum_sel - 1) * $num_rows_sel;
$query3 = "SELECT * FROM {$table_name} {$string_conditions} {$string_order} OFFSET '{$offset}' LIMIT '{$num_rows_sel}' ;";
$data = performQuery($query3);
$table_data = pg_fetch_all($data);
?>

<div id="docs" class="tabcontent">
    <h3 id="doc_title">Documents</h3>
    <p><a href="add_doc.php">Create New Document</a></p>
    <div>
        <form id="documents_table_form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <table id="doc_grid" class="table">
                <thead>
                    <tr>
                        <!-- Column Names -->
                        <?php table_headings($columns) ?>
						<!-- Docs per page -->
                        <th class="float_right" colspan="2"># Projects per page:</th>
                        <th><?php  select_number_rows_per_page($pagenum_options, $num_rows_sel) ?></th>
                    </tr>
                    <tr>
						<!-- Filters -->
                        <?php table_filters($columns) ?>
						<!-- Table Page navigation -->
                        <th class="float_right">
                            <button onclick="firstPage();">&laquo;</button>
                            <button onclick="prevPage(<?php echo $pagenum_sel ?>);">&lsaquo;</button>
                        </th>
                        <th>
                            <select id="sel_pagenum" class="numtable" name="pagenum" onchange="this.form.submit();">
                                <?php foreach(range(1, $max_pagenum) as $num) {echo '<option value="'.$num.'" '.(($pagenum_sel == $num) ? 'selected' : "").'>'.$num.'</option>';} ?>
                            </select>
                        </th>
                        <th>
                            <button onclick="nextPage(<?php echo $pagenum_sel.', '.$max_pagenum ?>);">&rsaquo;</button>
                            <button onclick="LastPage(<?php echo $max_pagenum ?>);"> &raquo;</button>
                        </th>
                    </tr>
                </thead>
                <tbody>
					<!-- Main Table Rows -->
                    <?php foreach((array) $table_data as $row) {?>
                        <tr class="accordion" onclick="openAccordion(this)">
                            <?php foreach($columns as $col) {echo '<td>'.$row[$col[0]].'</td>'; } ?>
                            <td><a href="javascript:alert('functionality not yet included');" target="_blank" class="btn btn-addRev">Add Rev</a></td>
                            <td><a href="javascript:alert('functionality not yet included');" target="_blank" class="btn btn-edit">Edit</a></td>
                            <td><a href="javascript:alert('functionality not yet included');" target="_blank" class="btn btn-delete">Delete</a></td>
                        </tr>
					    <!-- Sub Table Rows -->
                        <?php
                        $query3 = "SELECT * FROM doc_revisions WHERE project_entity = '{$row['project_entity']}' AND project_year = '{$row['project_year']}' AND project_number = '{$row['project_number']}' AND doc_discipline = '{$row['doc_discipline']}' AND doc_type = '{$row['doc_type']}' AND doc_number = '{$row['doc_number']}'";
                        $data = performQuery($query3);
                        $revs = pg_fetch_all($data);
                        if (!empty($revs)) {
                            echo '<tr class="panel">';
                                foreach($rev_columns as $rev_column) {echo '<th>'.$rev_column[1].'</th>';}
                            echo '</tr>';
                            foreach((array) $revs as $rev) {
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
                    };?>
                </tbody>
            </table>
        </form>
    </div>
</div>

<script type="text/javascript">
    //Create onclick javascript functions for order of table
    <?php order_js((array) $columns, $table_name) ?>
</script>
