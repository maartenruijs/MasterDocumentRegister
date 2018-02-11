<?php
// Create query string for conditions
function create_condition_string(array $input_array_columns) {
    $string_conditions = '';
    $i = '';
    $j = '';
    foreach($input_array_columns as $col) {
        if(!($col[3] == "")) {
            if ($col[2] == 'select') {
                $string_conditions .= " AND {$col[0]} = '{$col[3]}'";
            } elseif($col[2] == 'text') {
                $string_conditions .= " AND LOWER({$col[0]}) LIKE LOWER('%{$col[3]}%')";
            } elseif($col[2] == 'datetime'){
                $string_conditions .= " AND ({$col[0]} BETWEEN '{$col[3][0]}' AND '{$col[3][1]}')";
            };

        };
    };
    if(!empty($string_conditions)){
        $string_conditions = substr($string_conditions, 5);
        $string_conditions = 'WHERE '.$string_conditions;
    };
    return $string_conditions;
};


// Create query string for order
function create_order_string(array $input_array_columns) {
    $order_arr = [];
    $order = [];
    $string_order = "";
    foreach($input_array_columns as $col) {
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
            $string_order .= $input_array_columns[$o][0].' '.strtoupper($input_array_columns[$o][4]);
            $string_order .= ", ";
        };
        $string_order = substr($string_order, 0, -2);
        $string_order .= " ";
    };
    return $string_order;
};

function determine_pagenum_options($num_rows) {
    $pagenum_options = [];
    if($num_rows <= 20) {
        $pagenum_options = array(1, 2, 20);
    } elseif ($num_rows <= 50) {
        $pagenum_options = array(1, 2, 20, 50);
    } elseif ($num_rows <= 100) {
        $pagenum_options = array(1, 2, 20, 50, 100);
    } else {
        $pagenum_options = array(1, 2, 20, 50, 100, $num_rows);
    };
    return $pagenum_options;
};

// Determine input for select filters
function sel_input($input_array_columns, $table_full) {
    $k = 0;
    foreach($input_array_columns as $col) {
        if($col[2] == 'select') {
            $select_options_full = array_unique(pg_fetch_all_columns($table_full, pg_field_num($table_full, $col[0])));
            asort($select_options_full);
            array_unshift($select_options_full, "");
            $input_array_columns[$k][7] = $select_options_full;
        };
        $k++;
    };
    return $input_array_columns;
};

// Enable / Disable select input based on availability influenced by other filters
function disable_input($input_array_columns, $table_cond) {
    $k = 0;
    foreach((array) $input_array_columns as $col) {
        if($col[2] == 'select') {
            $select_options_cond = array_unique(pg_fetch_all_columns($table_cond, pg_field_num($table_cond, $col[0])));
            $enable_disable = [];
            foreach((array) $input_array_columns[$k][7] as $option) {
                if((in_array($option, $select_options_cond) or !$option)) {
                    array_push($enable_disable, "enabled");
                } else {
                    array_push($enable_disable, "disabled");
                };
            };
            $input_array_columns[$k][8] = $enable_disable;
        };
        $k++;
    };
    return $input_array_columns;
};

//Create onclick javascript functions for order of table
function order_js($input_array_colums, $table_name) {
    foreach($input_array_colums as $col) {
        echo "$('#order_".$col[0]."').click(function () {";
        echo "modify_order('order_inp_".$col[0]."');";
        foreach($input_array_colums as $col2) {
            echo "modify_order_of_orderinput('order_num_".$col[0]."', 'order_num_".$col2[0]."' , 'order_inp_".$col2[0]."');";
        };
        echo "$('#".$table_name."_table_form').submit();";
        echo "});";
    };
};

function table_headings($input_array_colums) {
    foreach($input_array_colums as $col) {
        echo '<th class="'.$col[6].'">'.$col[1].str_repeat('&nbsp;', 1).'<i class="'.$col[4].'" id="order_'.$col[0].'"></i><input type="text" class="invis" id="order_inp_'.$col[0].'" name="order_inp_'.$col[0].'" value="'.$col[4].'"><input type="text" class="invis" id="order_num_'.$col[0].'" name="order_num_'.$col[0].'" value="'.$col[5].'"></th>';}
};

function select_number_rows_per_page($pagenum_options, $num_rows_sel) {
    echo '<select class="numtable" name="num_rows" onchange="this.form.submit();">';
    foreach($pagenum_options as $num) {
        echo '<option value="'.$num.'" '.(($num_rows_sel == $num) ? 'selected' : "").'>'.$num.'</option>';
    };
    echo '</select>';
};

function table_filters($input_array_colums) {
    foreach($input_array_colums as $col) {
        echo '<th>';
        if($col[2] == 'select')  {
            echo '<select class="full_width" name="filter_'.$col[0].'" onchange="this.form.submit();">';
            $i = 0;
            for($i = 0; $i < count($col[7]); $i++){
                echo '<option id = "option_'.$col[8][$i].'" value="'.$col[7][$i].'" '.(($col[3] == $col[7][$i]) ? 'selected' : "").'>'.$col[7][$i].'</option>';
            };
            echo '</select>';
        } elseif ($col[2] == 'text'){
            echo '<input class="full_width" type="text" name="filter_'.$col[0].'" value="'.$col[3].'" onchange="this.form.submit();">';
        } elseif ($col[2] == 'datetime') {
            echo '<input type="datetime-local" class="full_width" name="filter_'.$col[0].'_from" value="'.$col[3][0].'" onchange="this.form.submit();">';
            echo '<input type="datetime-local" class="full_width" name="filter_'.$col[0].'_to" value="'.$col[3][1].'" onchange="this.form.submit();">';
        };
        echo '</th>';
    };
};
?>