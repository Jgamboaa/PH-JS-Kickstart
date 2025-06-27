<?php
// admin/includes/components/datatable.php
function renderDataTable($options = [])
{
    $defaults = [
        'id' => 'dataTable',
        'class' => 'table table-hover',
        'columns' => [],
        'ajax_url' => '',
        'responsive' => true,
        'buttons' => [],
        'order' => [[0, 'asc']]
    ];

    $config = array_merge($defaults, $options);

    $html = "<table id='{$config['id']}' class='{$config['class']}'>";
    $html .= "<thead><tr>";

    foreach ($config['columns'] as $column)
    {
        $html .= "<th>{$column}</th>";
    }

    $html .= "</tr></thead><tbody></tbody></table>";

    // JavaScript initialization
    $html .= "<script>
    $(document).ready(function() {
        $('#{$config['id']}').DataTable({
            ajax: '{$config['ajax_url']}',
            responsive: " . ($config['responsive'] ? 'true' : 'false') . ",
            order: " . json_encode($config['order']) . ",
            language: {
                url: '../plugins/datatables/Spanish.json'
            }
        });
    });
    </script>";

    return $html;
}
