<?php
function renderFormField(array $options = [])
{
    // 1. Valores por defecto
    $defaults = [
        'type'          => 'text',    // text, textarea, select, checkbox, radio, switch, file, range, color, date, time, datetime-local, select2, duallistbox, inputmask, colorpicker, timepicker, datepicker, daterangepicker, bs-stepper, dropzone, richtext…
        'name'          => '',
        'id'            => '',
        'label'         => '',
        'value'         => '',
        'placeholder'   => '',
        'required'      => false,
        'disabled'      => false,
        'class'         => 'form-control',
        'icon'          => '',        // FontAwesome o similar
        'help_text'     => '',
        // Para <select>
        'options'       => [],        // ['value'=>'Texto']
        'selected'      => null,
        'multiple'      => false,
        // Nuevas opciones para selects dinámicos
        'data_source'   => null,      // Fuente de datos (resultados de consulta)
        'value_field'   => 'id',      // Campo a usar como valor
        'text_field'    => 'nombre',  // Campo a usar como texto
        // Para skins de iCheck / custom-range
        'skin'          => 'primary', // primary, danger, success, teal…
        // Para plugins avanzados
        'config'        => [],        // Parámetros de configuración JS
        // Slots para addons en InputGroup
        'prepend'       => '',
        'append'        => '',
    ];
    $cfg = array_merge($defaults, $options);

    // Flags comunes
    $req    = $cfg['required']  ? 'required'  : '';
    $dis    = $cfg['disabled']  ? 'disabled'  : '';
    $multi  = $cfg['multiple']  ? ' multiple' : '';
    $name   = $cfg['multiple']  ? "{$cfg['name']}[]" : $cfg['name'];

    // Contenedor general
    $html = "<div class='form-group'>";

    // Etiqueta
    if ($cfg['label'])
    {
        $html .= "<label for='{$cfg['id']}'>{$cfg['label']}</label>";
    }

    // Input Group: abrir
    if ($cfg['prepend'] || $cfg['append'] || $cfg['icon'])
    {
        $classes = 'input-group';
        if (!empty($options['igroup_size']))
        {
            $classes .= " input-group-{$options['igroup_size']}";
        }
        $html .= "<div class='{$classes}'>";
        // Prepend slot
        if ($cfg['prepend'])
        {
            $html .= "<div class='input-group-prepend'>{$cfg['prepend']}</div>";
        }
        // Icono por defecto
        if ($cfg['icon'])
        {
            $html .= "<div class='input-group-prepend'>
                        <span class='input-group-text'>
                          <i class='{$cfg['icon']}'></i>
                        </span>
                      </div>";
        }
    }

    // 2. Render según tipo
    switch ($cfg['type'])
    {
        // —— Campos de texto básicos ——
        case 'textarea':
            $html .= "<textarea 
                          name='{$name}' 
                          id='{$cfg['id']}' 
                          class='{$cfg['class']}' 
                          placeholder='{$cfg['placeholder']}' 
                          {$req} {$dis}>{$cfg['value']}</textarea>";
            break;

        case 'select':
        case 'select2':
        case 'select2-bootstrap4':
        case 'duallistbox':
            // Ajustar clases para plugins
            $selClass = $cfg['class'];
            if ($cfg['type'] === 'select2')
            {
                $selClass .= ' select2';
            }
            elseif ($cfg['type'] === 'select2-bootstrap4')
            {
                $selClass .= ' select2bs4';
            }
            elseif ($cfg['type'] === 'duallistbox')
            {
                $selClass .= ' duallistbox';
            }
            $html .= "<select 
                          name='{$name}' 
                          id='{$cfg['id']}' 
                          class='{$selClass}' 
                          {$multi} {$req} {$dis}>";

            // Procesar opciones del select
            if ($cfg['data_source'])
            {
                // Opción 1: Fuente de datos dinámica (resultados de consulta)
                foreach ($cfg['data_source'] as $item)
                {
                    $value = $item->{$cfg['value_field']};
                    $text = $item->{$cfg['text_field']};
                    $sel = ($value == $cfg['selected']) ? 'selected' : '';
                    $html .= "<option value='{$value}' {$sel}>{$text}</option>";
                }
            }
            else
            {
                // Opción 2: Array de opciones estático
                foreach ($cfg['options'] as $val => $txt)
                {
                    $sel = ($val == $cfg['selected']) ? 'selected' : '';
                    $html .= "<option value='{$val}' {$sel}>{$txt}</option>";
                }
            }

            $html .= "</select>";
            break;

        // —— Checkboxes y radios ——
        case 'checkbox':
        case 'radio':
            $inpType = $cfg['type'];
            $html .= "<div class='form-check'>";
            $html .= "<input 
                          class='form-check-input' 
                          type='{$inpType}' 
                          name='{$name}' 
                          id='{$cfg['id']}' 
                          value='{$cfg['value']}' 
                          {$req} {$dis}>";
            if ($cfg['label'])
            {
                $html .= "<label class='form-check-label' for='{$cfg['id']}'>{$cfg['label']}</label>";
            }
            $html .= "</div>";
            break;

        case 'icheck':
            // iCheck Bootstrap skins (icheck-primary, icheck-danger…)
            $skinCls = "icheck-{$cfg['skin']}";
            $html .= "<div class='{$skinCls} d-inline'>";
            $html .= "<input 
                          type='checkbox' 
                          id='{$cfg['id']}' 
                          {$req} {$dis}>";
            $html .= "<label for='{$cfg['id']}'>{$cfg['label']}</label>";
            $html .= "</div>";
            break;

        case 'switch':
            // Bootstrap Switch (requiere plugin bootstrapSwitch)
            $html .= "<div class='custom-control custom-switch'>";
            $html .= "<input 
                          type='checkbox' 
                          class='custom-control-input' 
                          id='{$cfg['id']}' 
                          name='{$name}' 
                          {$req} {$dis}>";
            $html .= "<label class='custom-control-label' for='{$cfg['id']}'>{$cfg['label']}</label>";
            $html .= "</div>";
            break;

        // —— File input y rangos ——
        case 'file':
            $html .= "<div class='custom-file'>";
            $html .= "<input 
                          type='file' 
                          class='custom-file-input' 
                          id='{$cfg['id']}' 
                          name='{$name}' 
                          {$req} {$dis}>";
            $html .= "<label class='custom-file-label' for='{$cfg['id']}'>{$cfg['placeholder']}</label>";
            $html .= "</div>";
            break;

        case 'range':
            // custom-range y skins custom-range-danger, etc.
            $html .= "<input 
                          type='range' 
                          class='custom-range custom-range-{$cfg['skin']}' 
                          id='{$cfg['id']}' 
                          name='{$name}' 
                          value='{$cfg['value']}' 
                          {$req} {$dis}>";
            break;

        // —— Inputs HTML5 extendidos (date, time, color…) ——
        case 'date':
        case 'time':
        case 'color':
        case 'datetime-local':
        case 'month':
        case 'week':
            $html .= "<input 
                          type='{$cfg['type']}' 
                          name='{$name}' 
                          id='{$cfg['id']}' 
                          class='{$cfg['class']}' 
                          value='{$cfg['value']}' 
                          placeholder='{$cfg['placeholder']}' 
                          {$req} {$dis}>";
            break;

        // —— Plugins avanzados que requieren JS/CSS externos ——
        case 'inputmask':
            // data-inputmask y máscaras específicas
            $html .= "<input 
                          type='text' 
                          data-inputmask='{$cfg['config']['mask']}' 
                          name='{$name}' 
                          id='{$cfg['id']}' 
                          class='{$cfg['class']}'>";
            break;

        case 'colorpicker':
            // Bootstrap Colorpicker (itsjavi.com)
            $html .= "<div class='input-group my-colorpicker2'>";
            $html .= "<input 
                          type='text' 
                          name='{$name}' 
                          id='{$cfg['id']}' 
                          class='{$cfg['class']}' 
                          value='{$cfg['value']}' 
                          {$req} {$dis}>";
            $html .= "<div class='input-group-append'>
                          <span class='input-group-text'>
                            <i class='fas fa-square'></i>
                          </span>
                      </div>";
            $html .= "</div>";
            break;

        case 'timepicker':
            // Tempus Dominus / time picker
            $html .= "<div class='input-group date' id='timepicker_{$cfg['id']}'>
                          <div class='input-group-prepend' data-target='#timepicker_{$cfg['id']}' data-toggle='datetimepicker'>
                              <div class='input-group-text'>
                                  <i class='far fa-clock'></i>
                              </div>
                          </div>
                          <input 
                              type='text' 
                              class='form-control datetimepicker-input' 
                              data-target='#timepicker_{$cfg['id']}'
                              name='{$name}'>
                      </div>";
            break;

        case 'datepicker':
        case 'daterangepicker':
            // Date Picker / Date Range Picker (temporizadores y Moment.js) :contentReference[oaicite:8]{index=8}
            $pickerCls = ($cfg['type'] === 'daterangepicker') ? 'daterange' : 'date';
            $html .= "<div class='input-group {$pickerCls}' id='{$cfg['id']}' data-target-input='nearest'>";
            $html .= "<input 
                          type='text' 
                          class='form-control datetimepicker-input' 
                          data-target='#{$cfg['id']}' 
                          name='{$name}'>";
            $html .= "<div class='input-group-append' data-target='#{$cfg['id']}' data-toggle='datetimepicker'>
                          <div class='input-group-text'><i class='fa fa-calendar'></i></div>
                      </div>";
            $html .= "</div>";
            break;

        case 'bs-stepper':
            // Pasos de formulario con bs-stepper
            $html .= "<div class='bs-stepper' id='{$cfg['id']}'> …</div>";
            break;

        case 'dropzone':
            // Dropzone.js
            $html .= "<form 
                          id='dropzone_{$cfg['id']}' 
                          class='dropzone'>…</form>";
            break;

        case 'richtext':
            // Summernote o similar
            $html .= "<textarea 
                          class='textarea' 
                          id='{$cfg['id']}' 
                          name='{$name}' 
                          placeholder='{$cfg['placeholder']}'>
                      {$cfg['value']}</textarea>";
            break;

        default:
            // Cualquier otro tipo de <input>
            $html .= "<input 
                          type='{$cfg['type']}' 
                          name='{$name}' 
                          id='{$cfg['id']}' 
                          class='{$cfg['class']}' 
                          value='{$cfg['value']}' 
                          placeholder='{$cfg['placeholder']}' 
                          {$req} {$dis}>";
            break;
    }

    // Input Group: cerrar
    if ($cfg['prepend'] || $cfg['append'] || $cfg['icon'])
    {
        if ($cfg['append'])
        {
            $html .= "<div class='input-group-append'>{$cfg['append']}</div>";
        }
        $html .= "</div>";
    }

    // Help text / validación
    if ($cfg['help_text'])
    {
        $html .= "<small class='form-text text-muted'>{$cfg['help_text']}</small>";
    }

    $html .= "</div>"; // .form-group

    return $html;
}
