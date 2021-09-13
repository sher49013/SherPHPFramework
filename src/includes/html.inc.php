<?php

function html_select_list(array $list, $select = null) {
    $listS = '';
    foreach ($list as $key => $value) {
        $selected = $select === $key ? 'selected' : '';
        $listS .= "<option $selected value='$key'>$value</option>";
    }
    return $listS;
}