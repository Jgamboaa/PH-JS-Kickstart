<?php
// admin/includes/components/form_fields.php

function renderModal(array $options = [])
{
    // 1. Defaults
    $defaults = [
        'id'             => 'genericModal',
        'title'          => 'Modal Title',
        'size'           => '',      // '', 'modal-sm', 'modal-lg', 'modal-xl'
        'scrollable'     => false,   // modal-dialog-scrollable
        'centered'       => false,   // modal-dialog-centered
        'backdrop'       => true,    // true, false, 'static'
        'keyboard'       => true,    // close on ESC
        'show'           => false,   // whether to add 'show' class initially
        'closable'       => true,    // show close button
        'body'           => '',      // HTML string
        'footer'         => '',      // HTML string
        'headerClass'    => '',      // extra classes for .modal-header
        'bodyClass'      => '',      // extra classes for .modal-body
        'footerClass'    => '',      // extra classes for .modal-footer
    ];
    $cfg = array_merge($defaults, $options);

    // 2. Compute attributes & classes
    $dialogClasses = trim(sprintf(
        "%s %s %s",
        $cfg['size'],
        $cfg['scrollable'] ? 'modal-dialog-scrollable' : '',
        $cfg['centered']   ? 'modal-dialog-centered'    : ''
    ));
    $backdropAttr = '';
    if ($cfg['backdrop'] === false)
    {
        $backdropAttr = 'data-backdrop="false"';
    }
    elseif ($cfg['backdrop'] === 'static')
    {
        $backdropAttr = 'data-backdrop="static"';
    }
    $keyboardAttr = $cfg['keyboard'] ? '' : 'data-keyboard="false"';
    $showClass = $cfg['show'] ? ' show' : '';
    $ariaLabelledby = $cfg['id'] . 'Label';

    // 3. Build HTML
    $html  = "<div class='modal fade{$showClass}'"
        . " id='{$cfg['id']}'"
        . " tabindex='-1'"
        . " role='dialog'"
        . " aria-labelledby='{$ariaLabelledby}'"
        . " aria-hidden='true'"
        . " aria-modal='true'"
        . " {$backdropAttr}"
        . " {$keyboardAttr}"
        . ">";

    $html .= "<div class='modal-dialog {$dialogClasses}' role='document'>";
    $html .= "<div class='modal-content'>";

    // Header
    $html .= "<div class='modal-header {$cfg['headerClass']}'>";
    $html .= "<h5 class='modal-title' id='{$ariaLabelledby}'>{$cfg['title']}</h5>";
    if ($cfg['closable'])
    {
        $html .= "<button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
        $html .= "<span aria-hidden='true'>&times;</span>";
        $html .= "</button>";
    }
    $html .= "</div>"; // .modal-header

    // Body
    $html .= "<div class='modal-body {$cfg['bodyClass']}'>";
    $html .= $cfg['body'];
    $html .= "</div>"; // .modal-body

    // Footer (if provided)
    if (strlen(trim($cfg['footer'])) > 0)
    {
        $html .= "<div class='modal-footer justify-content-between {$cfg['footerClass']}'>";
        $html .= $cfg['footer'];
        $html .= "</div>"; // .modal-footer
    }

    // Close .modal-content, .modal-dialog, .modal
    $html .= "</div></div></div>";

    return $html;
}
