async function submitFormInNewTab(url, data, newTab = true) {
  if (newTab) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = url;
    form.target = "_blank";

    for (const key in data) {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = key;
      input.value = data[key];
      form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
  } else {
    try {
      Swal.fire({
        title: "Descargando...",
        text: "Por favor espere mientras se genera el archivo",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      // Convertir los datos a FormData o URLSearchParams para enviarlos como form-urlencoded
      const formData = new URLSearchParams();
      for (const key in data) {
        formData.append(key, data[key]);
      }

      const response = await fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: formData.toString(),
      });

      // Obtener el nombre del archivo del encabezado Content-Disposition o de la URL
      let filename = "";
      const contentDisposition = response.headers.get("Content-Disposition");
      if (contentDisposition) {
        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
        const matches = filenameRegex.exec(contentDisposition);
        if (matches != null && matches[1]) {
          filename = matches[1].replace(/['"]/g, "");
        }
      }

      // Si no se encontró en el encabezado, extraer de la URL
      if (!filename) {
        const urlParts = url.split("/");
        filename =
          urlParts[urlParts.length - 1].split("?")[0] || "archivo_descargado";
      }

      const blob = await response.blob();
      const downloadUrl = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = downloadUrl;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      Swal.close();
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Hubo un problema al generar el archivo",
      });
    }
  }
}
