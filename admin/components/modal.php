<?php
// admin/includes/components/modal.php
function renderModal($options = [])
{
    $defaults = [
        'id' => 'genericModal',
        'title' => 'Modal',
        'size' => '', // 'modal-lg', 'modal-xl', 'modal-sm'
        'scrollable' => false,
        'body' => '',
        'footer' => '',
        'closable' => true
    ];

    $config = array_merge($defaults, $options);
    $sizeClass = $config['size'] ? $config['size'] : '';
    $scrollableClass = $config['scrollable'] ? 'modal-dialog-scrollable' : '';

    return "
    <div class='modal fade' id='{$config['id']}' tabindex='-1'>
        <div class='modal-dialog {$sizeClass} {$scrollableClass}'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title'>{$config['title']}</h5>
                    " . ($config['closable'] ? "<button type='button' class='close' data-dismiss='modal'><span>&times;</span></button>" : "") . "
                </div>
                <div class='modal-body'>
                    {$config['body']}
                </div>
                " . ($config['footer'] ? "<div class='modal-footer justify-content-between'>{$config['footer']}</div>" : "") . "
            </div>
        </div>
    </div>";
}
