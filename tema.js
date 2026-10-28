(function () {
    var temaGuardado = localStorage.getItem('tema-liga');
    var tema = temaGuardado === 'oscuro' ? 'oscuro' : 'claro';
    var botones = document.querySelectorAll('.boton-tema, .boton-tema-login');

    function aplicarTema() {
        document.documentElement.setAttribute('data-tema', tema);

        botones.forEach(function (boton) {
            boton.setAttribute('aria-pressed', tema === 'oscuro' ? 'true' : 'false');
            boton.textContent = tema === 'oscuro' ? 'Modo claro' : 'Modo oscuro';
        });
    }

    aplicarTema();

    botones.forEach(function (boton) {
        boton.addEventListener('click', function () {
            tema = tema === 'oscuro' ? 'claro' : 'oscuro';
            localStorage.setItem('tema-liga', tema);
            aplicarTema();
        });
    });
}());
