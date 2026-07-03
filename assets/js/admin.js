// admin.js
// Admin page helpers: edition form population and resets
(function(){
    function loadEditionData() {
        const select = document.getElementById('edicaoSelect');
        if (!select) return;
        const option = select.options[select.selectedIndex];
        if (!option) return resetForm();

        if (option.value) {
            const set = (id, attr) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.value = option.getAttribute(attr) || '';
            };
            set('titulo', 'data-titulo');
            set('descricao', 'data-descricao');
            set('data_inicio', 'data-data_inicio');
            set('data_fim', 'data-data_fim');
            set('local', 'data-local');
            set('data_inscricao_inicio', 'data-data_inscricao_inicio');
            set('data_inscricao_fim', 'data-data_inscricao_fim');
            set('hora_inicio', 'data-hora_inicio');
            set('hora_fim', 'data-hora_fim');
            set('hora_inscricao_inicio', 'data-hora_inscricao_inicio');
            set('hora_inscricao_fim', 'data-hora_inscricao_fim');
        } else {
            resetForm();
        }
    }

    function resetForm() {
        const ids = ['titulo','descricao','data_inicio','data_fim','local','data_inscricao_inicio','data_inscricao_fim','hora_inicio','hora_fim','hora_inscricao_inicio','hora_inscricao_fim'];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    }

    // Export to global so inline attributes still work
    window.loadEditionData = loadEditionData;
    window.resetForm = resetForm;

    document.addEventListener('DOMContentLoaded', function(){
        const select = document.getElementById('edicaoSelect');
        if (select) select.addEventListener('change', loadEditionData);

        // If there's a reset button that clears the select, we rely on inline onclick to clear value then call resetForm();
    });
})();
