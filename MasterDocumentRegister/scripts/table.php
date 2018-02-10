<?php
// Create query string for conditions
function create_condition_string(array $input_array_columns) {
    $string_conditions = '';
    $i = '';
    $j = '';
    foreach($input_array_columns as $col) {
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
function sel_input($input_array_columns, $table) {
    $k = 0;
    foreach($input_array_columns as $col) {
        if($col[2] == 'select') {
            $temp = array_unique(pg_fetch_all_columns($table, pg_field_num($table, $col[0])));
            asort($temp);
            array_unshift($temp, "");
            $input_array_columns[$k][7] = $temp;
        };
        $k++;
    };
    return $input_array_columns;
};

?>