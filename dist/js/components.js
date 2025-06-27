class NotificationComponent {
  static success(title, text = "", timer = 1500) {
    return Swal.fire({
      icon: "success",
      title: title,
      text: text,
      timer: timer,
      showConfirmButton: false,
    });
  }

  static error(title, text = "") {
    return Swal.fire({
      icon: "error",
      title: title,
      text: text,
    });
  }

  static confirm(title, text, onConfirm) {
    return Swal.fire({
      title: title,
      text: text,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, continuar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed && onConfirm) {
        onConfirm();
      }
    });
  }

  static inputModal(title, inputType = "text", placeholder = "") {
    return Swal.fire({
      title: title,
      input: inputType,
      inputPlaceholder: placeholder,
      showCancelButton: true,
      confirmButtonText: "Aceptar",
      cancelButtonText: "Cancelar",
    });
  }
}

// ...existing code...

class Select2Component {
  /**
   * Inicializa un Select2 con configuración básica
   * @param {string} selector - Selector del elemento select
   * @param {Object} options - Opciones de configuración
   */
  static init(selector, options = {}) {
    const defaultOptions = {
      theme: "bootstrap4",
      width: "100%",
      language: "es",
      placeholder: "Seleccione una opción...",
      allowClear: true,
    };

    const config = { ...defaultOptions, ...options };

    return $(selector).select2(config);
  }

  /**
   * Select2 con búsqueda AJAX
   * @param {string} selector - Selector del elemento select
   * @param {string} url - URL para la búsqueda AJAX
   * @param {Object} options - Opciones adicionales
   */
  static ajax(selector, url, options = {}) {
    const defaultOptions = {
      theme: "bootstrap4",
      width: "100%",
      language: "es",
      placeholder: "Buscar...",
      minimumInputLength: 2,
      allowClear: true,
      ajax: {
        url: url,
        dataType: "json",
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            page: params.page || 1,
          };
        },
        processResults: function (data, params) {
          params.page = params.page || 1;
          return {
            results: data.items,
            pagination: {
              more: params.page * 30 < data.total_count,
            },
          };
        },
        cache: true,
      },
    };

    const config = { ...defaultOptions, ...options };

    return $(selector).select2(config);
  }

  /**
   * Select2 múltiple con tags
   * @param {string} selector - Selector del elemento select
   * @param {Object} options - Opciones adicionales
   */
  static tags(selector, options = {}) {
    const defaultOptions = {
      theme: "bootstrap4",
      width: "100%",
      language: "es",
      placeholder: "Agregar tags...",
      tags: true,
      tokenSeparators: [",", " "],
      allowClear: true,
    };

    const config = { ...defaultOptions, ...options };

    return $(selector).select2(config);
  }

  /**
   * Select2 con datos estáticos
   * @param {string} selector - Selector del elemento select
   * @param {Array} data - Array de objetos con id y text
   * @param {Object} options - Opciones adicionales
   */
  static withData(selector, data, options = {}) {
    const defaultOptions = {
      theme: "bootstrap4",
      width: "100%",
      language: "es",
      placeholder: "Seleccione una opción...",
      allowClear: true,
      data: data,
    };

    const config = { ...defaultOptions, ...options };

    return $(selector).select2(config);
  }

  /**
   * Destruye la instancia de Select2
   * @param {string} selector - Selector del elemento select
   */
  static destroy(selector) {
    $(selector).select2("destroy");
  }

  /**
   * Limpia la selección
   * @param {string} selector - Selector del elemento select
   */
  static clear(selector) {
    $(selector).val(null).trigger("change");
  }

  /**
   * Establece un valor
   * @param {string} selector - Selector del elemento select
   * @param {string|Array} value - Valor o valores a establecer
   */
  static setValue(selector, value) {
    $(selector).val(value).trigger("change");
  }

  /**
   * Obtiene el valor seleccionado
   * @param {string} selector - Selector del elemento select
   */
  static getValue(selector) {
    return $(selector).val();
  }

  /**
   * Aplica estilos de validación de Bootstrap
   * @param {string} selector - Selector del elemento select
   * @param {boolean} isValid - Si es válido o no
   * @param {string} message - Mensaje de error (opcional)
   */
  static setValidation(selector, isValid, message = "") {
    const $select = $(selector);
    const $container = $select.next(".select2-container");
    const $feedback = $select.siblings(".invalid-feedback");

    // Remover clases previas
    $container.find(".select2-selection").removeClass("is-valid is-invalid");

    if (isValid) {
      $container.find(".select2-selection").addClass("is-valid");
      $feedback.hide();
    } else {
      $container.find(".select2-selection").addClass("is-invalid");
      if (message && $feedback.length) {
        $feedback.text(message).show();
      }
    }
  }
}
