<?php
include "./scripts/connection.php";
include "./scripts/table.php";

// Define columns in_array table ([0] = pg col name, [1] = html col name, [2] = form input type[select/text],
// [3] = filtervalue, [4] = order[none/asc/desc], [5] = order by, [6] = class name for col spacing, [7] = array input for select filters, [8] = select filters enable/disable)
$columns = array(
    array('project_entity', 'Entity', 'select', "", 'none', "", 'col50'),
    array('project_year', 'Year', 'select', "", 'none', "", 'col50'),
    array('project_number', 'Number', 'select', "", 'none', "", 'col50'),
    array('client', 'Client', 'text', "", 'none', "", 'col50'),
    array('short_description', 'Description', 'text', "", 'none', "", 'col250'),
    array('status', 'Status', 'select', "", 'none', "", 'col100'));

$num_rows_sel = 20;
$pagenum_sel = 1;
$table_name = "projects";

//Update when form is submitted (onchange!) and filters, amount of pages to show and pagenumber are updated
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pagenum_sel = $_POST["pagenum"];
    $num_rows_sel = $_POST["num_rows"];
    for($k = 0; $k < count($columns); $k++) {
        if($columns[$k][2] == 'select' or $columns[$k][2] == 'text') {
            $columns[$k][3] = $_POST["filter_".$columns[$k][0]];
        } elseif($columns[$k][2] == 'date') {
            $columns[$k][3][0] = $_POST["filter_".$columns[$k][0]."_from"];
            $columns[$k][3][1] = $_POST["filter_".$columns[$k][0]."_to"];
        };
        $columns[$k][4] = $_POST["order_inp_".$columns[$k][0]];
        if($_POST["order_num_".$columns[$k][0]] == "") {
            $columns[$k][5] = "";
        } else {
            $columns[$k][5] = $_POST["order_num_".$columns[$k][0]];
        };
    };
};

 // Create query strings for filter_conditions and order
$string_conditions = create_condition_string((array) $columns);
$string_order = create_order_string((array) $columns);

// Trial queries to determine number of rows and input for filters
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
<div id="Projects" class="tabcontent">
    <h3>Projects</h3>
    <p><a href="add_project.php">Create New Project</a></p>
    <div class="table_wrapper">
        <form id="<?php echo $table_name;?>_table_form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <!-- Table Page navigation -->
            <div class="table_nav">
                <table>
                    <tr>
                        <td class="table_nav_label">
                            <label>Projects per page: </label>
                        </td>
                        <td class="table_nav_numpages">
                            <?php  select_number_rows_per_page($pagenum_options, $num_rows_sel) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="table_nav_left_buttons table_nav_buttons">
                            <button onclick="firstPage();">&laquo;</button>
                            <button onclick="prevPage(<?php echo $pagenum_sel ?>);">&lsaquo;</button>
                        </td>
                        <td class="table_nav_pagenum">
                            <select id="sel_pagenum" class="numtable" name="pagenum" onchange="this.form.submit();">
                                <?php foreach(range(1, $max_pagenum) as $num) {echo '<option value="'.$num.'" '.(($pagenum_sel == $num) ? 'selected' : "").'>'.$num.'</option>';} ?>
                            </select>
                        </td>
                        <td class="table_nav_right_buttons table_nav_buttons">
                            <button onclick="nextPage(<?php echo $pagenum_sel.', '.$max_pagenum ?>);">&rsaquo;</button>
                            <button onclick="LastPage(<?php echo $max_pagenum ?>);">&raquo;</button>
                        </td>
                    </tr>
                </table>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <!-- Column Names -->
                        <?php table_headings($columns) ?>
                    </tr>
                    <tr>
                        <!-- Filters -->
                        <?php table_filters($columns) ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- Main Table Rows --><?php foreach((array) $table_data as $row):?>
                    <tr class="accordion" onclick="openAccordion(this)">
                        <?php foreach($columns as $col) {echo '<td>'.$row[$col[0]].'</td>'; }; ?>
                        <td>
                            <a href="javascript:alert('functionality not yet included');" class="btn btn-edit">Edit</a>
                        </td>
                        <td>
                            <a href="javascript:alert('functionality not yet included');" class="btn btn-delete">Delete</a>
                        </td>
                    </tr>
                    <!-- Sub Table Rows -->
                    <tr class="panel">
                        <td colspan="<?php echo count($columns);?>">
                            <?php echo $row['long_description'] ?>
                        </td>
                        <td></td>
                    </tr><?php endforeach;?>
                </tbody>
            </table>
        </form>
    </div>
</div>

<script type="text/javascript">
    //Create onclick javascript functions for order of table
    <?php order_js((array) $columns, $table_name) ?>
</script>

