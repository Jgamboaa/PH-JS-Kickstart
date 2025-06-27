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
