<?php
// admin/includes/components/form_fields.php
function renderInputField($options = [])
{
    $defaults = [
        'type' => 'text',
        'name' => '',
        'id' => '',
        'label' => '',
        'value' => '',
        'placeholder' => '',
        'required' => false,
        'disabled' => false,
        'class' => 'form-control',
        'icon' => '',
        'help_text' => ''
    ];

    $config = array_merge($defaults, $options);
    $required = $config['required'] ? 'required' : '';
    $disabled = $config['disabled'] ? 'disabled' : '';

    $html = "<div class='form-group'>";
    if ($config['label'])
    {
        $html .= "<label for='{$config['id']}'>{$config['label']}</label>";
    }

    if ($config['icon'])
    {
        $html .= "<div class='input-group'>";
        $html .= "<div class='input-group-prepend'>";
        $html .= "<span class='input-group-text'><i class='{$config['icon']}'></i></span>";
        $html .= "</div>";
    }

    $html .= "<input type='{$config['type']}' 
                     name='{$config['name']}' 
                     id='{$config['id']}' 
                     class='{$config['class']}' 
                     value='{$config['value']}' 
                     placeholder='{$config['placeholder']}' 
                     {$required} {$disabled}>";

    if ($config['icon'])
    {
        $html .= "</div>";
    }

    if ($config['help_text'])
    {
        $html .= "<small class='form-text text-muted'>{$config['help_text']}</small>";
    }

    $html .= "</div>";

    return $html;
}

function renderSelectField($options = [])
{
    $defaults = [
        'name' => '',
        'id' => '',
        'label' => '',
        'options' => [],
        'selected' => '',
        'multiple' => false,
        'required' => false,
        'class' => 'form-control'
    ];

    $config = array_merge($defaults, $options);
    $multiple = $config['multiple'] ? 'multiple' : '';
    $required = $config['required'] ? 'required' : '';
    $name = $config['multiple'] ? $config['name'] . '[]' : $config['name'];

    $html = "<div class='form-group'>";
    if ($config['label'])
    {
        $html .= "<label for='{$config['id']}'>{$config['label']}</label>";
    }

    $html .= "<select name='{$name}' id='{$config['id']}' class='{$config['class']}' {$multiple} {$required}>";

    foreach ($config['options'] as $value => $text)
    {
        $selected = ($value == $config['selected']) ? 'selected' : '';
        $html .= "<option value='{$value}' {$selected}>{$text}</option>";
    }

    $html .= "</select></div>";

    return $html;
}
