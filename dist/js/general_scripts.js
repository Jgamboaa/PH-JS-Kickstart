// Funcionalidad del menú activo
$(function () {
  function getCurrentHash() {
    return window.location.hash.slice(1); // Eliminar el # del inicio
  }

  function highlightActiveMenuItem(hash) {
    // Primero removemos todas las clases activas previas
    $("ul.nav-sidebar .nav-link").removeClass("active");
    $("ul.nav-sidebar .nav-item").removeClass("menu-open");
    $("ul.nav-sidebar .nav-treeview").css("display", "");

    // Si no hay hash, activamos home por defecto
    if (!hash) {
      hash = "home";
    }

    // Buscamos el enlace que corresponde al hash actual
    $("ul.nav-sidebar")
      .find("a")
      .each(function () {
        var menuHash = $(this).attr("href");
        if (menuHash) {
          menuHash = menuHash.replace("#", ""); // Removemos el # del href
          if (menuHash === hash) {
            $(this).addClass("active");

            // Si es un elemento del submenú
            if ($(this).parents(".nav-treeview").length) {
              $(this).parents(".nav-treeview").css("display", "block");
              $(this).parents(".nav-item").addClass("menu-open");
              $(this).closest(".menu").find("> a.nav-link").addClass("active");
            }
          }
        }
      });
  }

  // Activar elemento de menú al cargar la página
  highlightActiveMenuItem(getCurrentHash());

  // Activar elemento de menú cuando cambia el hash
  $(window).on("hashchange", function () {
    highlightActiveMenuItem(getCurrentHash());
  });

  // Resto del código existente...
  $(document).on(
    "click",
    "ul.nav-sidebar .nav-item.menu > a.nav-link",
    function () {
      const $parent = $(this).closest(".nav-item.menu");
      if ($parent.find(".nav-treeview").length === 0) {
        $("ul.nav-sidebar .nav-item.menu.menu-open")
          .removeClass("menu-open")
          .find(".nav-treeview:visible")
          .slideUp(300);
      }
    }
  );

  // Removemos las inicializaciones de plugins que ahora están en home.php
  // y dejamos solo la funcionalidad del sidebar

  (function () {
    if (Boolean(localStorage.getItem("sidebar-toggle-collapsed"))) {
      var body = document.getElementsByTagName("body")[0];
      body.className = body.className + " sidebar-collapse";
    }
  })();

  // Click handler can be added latter, after jQuery is loaded...
  $(".toggle-sidebar").click(function (event) {
    event.preventDefault();
    if (Boolean(localStorage.getItem("sidebar-toggle-collapsed"))) {
      localStorage.setItem("sidebar-toggle-collapsed", "");
    } else {
      localStorage.setItem("sidebar-toggle-collapsed", "1");
    }
  });
});
