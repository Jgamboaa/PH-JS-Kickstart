<?php
function renderCard($options = [])
{
    $defaults = [
        'title' => '',
        'body' => '',
        'footer' => '',
        'header_class' => '',
        'body_class' => '',
        'card_class' => 'card',
        'icon' => '',
        'tools' => []
    ];

    $config = array_merge($defaults, $options);

    $html = "<div class='{$config['card_class']}'>";

    if ($config['title'] || $config['tools'])
    {
        $html .= "<div class='card-header {$config['header_class']}'>";
        if ($config['title'])
        {
            $html .= "<h3 class='card-title'>";
            if ($config['icon'])
            {
                $html .= "<i class='{$config['icon']} mr-2'></i>";
            }
            $html .= $config['title'] . "</h3>";
        }

        if ($config['tools'])
        {
            $html .= "<div class='card-tools'>";
            foreach ($config['tools'] as $tool)
            {
                $html .= $tool;
            }
            $html .= "</div>";
        }
        $html .= "</div>";
    }

    $html .= "<div class='card-body {$config['body_class']}'>";
    $html .= $config['body'];
    $html .= "</div>";

    if ($config['footer'])
    {
        $html .= "<div class='card-footer'>{$config['footer']}</div>";
    }

    $html .= "</div>";

    return $html;
}

function renderInfoBox($options = [])
{
    $defaults = [
        'icon' => 'fa fa-info',
        'icon_bg' => 'bg-info',
        'title' => '',
        'number' => '',
        'description' => ''
    ];

    $config = array_merge($defaults, $options);

    return "
    <div class='info-box'>
        <span class='info-box-icon {$config['icon_bg']}'><i class='{$config['icon']}'></i></span>
        <div class='info-box-content'>
            <span class='info-box-text'>{$config['title']}</span>
            <span class='info-box-number'>{$config['number']}</span>
            <span class='text-muted'>{$config['description']}</span>
        </div>
    </div>";
}
