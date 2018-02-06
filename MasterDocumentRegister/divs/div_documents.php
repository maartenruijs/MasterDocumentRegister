<?php
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

// Create query string for conditions
$string_conditions = '';
foreach($doc_columns as $col) {
    if(!($col[3] == "")) {
        if ($col[2] == 'select') {
            $i = '=';
            $j = '';
        } elseif($col[2] == 'text') {
            $i = 'LIKE';
            $j = '%';
        };
        $string_conditions .= " AND LOWER({$col[0]}) {$i} LOWER('{$j}{$col[3]}{$j}')";
    };
};
if(!empty($string_conditions)){
    $string_conditions = substr($string_conditions, 5);
    $string_conditions = 'WHERE '.$string_conditions;
};

// Create query string for order
$order_arr = [];
$order = [];
$string_order = "";
foreach($doc_columns as $col) {
    if ($col[5] == "") {
        array_push($order_arr, 9999);
    } else {
        array_push($order_arr, $col[5]);
    };
};

$i = 0;
while(min($order_arr) < 9999) {
    $y = array_search(min($order_arr), $order_arr);
    $order[$i] = $y;
    $order_arr[$y] = 9999;
    $i++;
};

if(!empty($order)) {
    $string_order .= "ORDER BY ";
    foreach($order as $o){
        $string_order .= $doc_columns[$o][0].' '.strtoupper($doc_columns[$o][4]);
        $string_order .= ", ";
    };
    $string_order = substr($string_order, 0, -2);
    $string_order .= " ";
};

// Select query to determine number of rows and input for filters
$query1 = "SELECT * FROM documents {$string_conditions};";
$res = performQuery($query1);

//Determine number of rows, maximum number of pages and input for select options
$count = pg_num_rows($res);
$max_pagenum_docs = (int) ceil($count / $num_rows_sel);
if($count > 20) {
    $pagenum_options = array(1, 2, 20, 50);
} elseif ($count > 50) {
    $pagenum_options = array(1, 2, 20, 50, 100);
} elseif ($count > 100) {
    $pagenum_options = array(1, 2, 20, 50, 100, $count);
} else {
    $pagenum_options = array(1, 2, 20);
}

// Determine input for select filters
$k = 0;
foreach($doc_columns as $col) {
    if($col[2] == 'select') {
        $temp = array_unique(pg_fetch_all_columns($res, pg_field_num($res, $col[0])));
        asort($temp);
        array_unshift($temp, "");
        $doc_columns[$k][7] = $temp;
    };
    $k++;
};

// Retrieve final table to be presented
$offset = ($pagenum_sel - 1) * $num_rows_sel;
$query2 = "SELECT * FROM documents {$string_conditions} {$string_order} OFFSET '{$offset}' LIMIT '{$num_rows_sel}' ;";
$data = performQuery($query2);
$docs = pg_fetch_all($data);
?>

<div id="docs" class="tabcontent">
    <h3 id="doc_title">Documents</h3>
    <p>
        <a href="add_doc.php">Create New Document</a>
    </p>
    <div>
        <form id="doc_table_form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
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
    <?php foreach($doc_columns as $col): ?>
    $('#order_<?php echo $col[0]; ?>').click(function () {
        if ($('#order_inp_<?php echo $col[0]; ?>').val() == 'none') {
            $('#order_inp_<?php echo $col[0]; ?>').val('asc');
        $('#order_<?php echo $col[0]; ?>').val('asc');
        } else {
            if ($('#order_inp_<?php echo $col[0]; ?>').val() == 'asc') {
                $('#order_inp_<?php echo $col[0]; ?>').val('desc');
            } else {
                if ($('#order_inp_<?php echo $col[0]; ?>').val() == 'desc') {
                    $('#order_inp_<?php echo $col[0]; ?>').val("none");
                }
            }
        };

        <?php foreach($doc_columns as $col2){?>
              if (($('#order_num_<?php echo $col2[0]; ?>').val() != "")) {
                   $('#order_num_<?php echo $col2[0]; ?>').val(parseInt($('#order_num_<?php echo $col2[0]; ?>').val(), 10) + 1);
              };
              <?php if($col[0] == $col2[0]) { ?>
                   if (($('#order_inp_<?php echo $col2[0]; ?>').val() == 'none')) {
                           $('#order_num_<?php echo $col2[0]; ?>').val("");
                   } else {
                           $('#order_num_<?php echo $col2[0]; ?>').val(1);
                   };
              <?php };?>
        <?php };?>
        $('#doc_table_form').submit();
    });
    <?php endforeach;?>
</script>
