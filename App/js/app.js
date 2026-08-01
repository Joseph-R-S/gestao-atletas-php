const App = {
    /**
     * Procesa la respuesta JSON enviada por PHP (Soporta objeto único o Array de acciones)
     */
    procesarRespuesta: function(respuesta) {
        // Si el PHP devuelve un Array con múltiples instrucciones Ajax, las recorre todas
        if (Array.isArray(respuesta)) {
            respuesta.forEach(item => App.procesarRespuesta(item));
            return;
        }

        // 1. Asignar valores a inputs/selects/HTML
        if (respuesta.tipo === 'setValue') {
            let elemento = document.getElementById(respuesta.target) 
                        || document.querySelector(`[name="${respuesta.target}"]`);
            
            if (!elemento) {
                console.error('Elemento no encontrado en el DOM:', respuesta.target);
                return;
            }
            
            // Si el elemento es un <select>
            if (elemento.tagName.toLowerCase() === 'select') {
                elemento.innerHTML = '<option value="">Seleccione...</option>';
                
                if (respuesta.data && typeof respuesta.data === 'object') {
                    Object.entries(respuesta.data).forEach(([id, nome]) => {
                        let opt = document.createElement('option');
                        opt.value = id;
                        opt.textContent = nome;
                        elemento.appendChild(opt);
                    });
                }
            } 
            // Si es un <input>, <textarea>, etc.
            else if ('value' in elemento) {
                elemento.value = respuesta.data ?? '';
            } 
            // Si es un <span>, <div>, <label>, etc.
            else {
                elemento.textContent = respuesta.data ?? '';
            }
        }

        // 2. Agregar nueva fila (<tr>) o HTML a la tabla/Datagrid
        else if (respuesta.tipo === 'appendHTML') {
            let contenedor = document.querySelector(respuesta.target);
            if (contenedor) {
                contenedor.insertAdjacentHTML('beforeend', respuesta.html);
            } else {
                console.error('Contenedor para appendHTML no encontrado:', respuesta.target);
            }
        }

        // 3. Reemplazar completamente el HTML de un elemento (ej. recargar la DataGrid completa)
        else if (respuesta.tipo === 'replaceHTML') {
            let elemento = document.querySelector(respuesta.target);
            if (elemento) {
                elemento.innerHTML = respuesta.html;
            }
        }

        // 4. Eliminar un elemento del DOM
        else if (respuesta.tipo === 'removerElemento') {
            let elemento = document.querySelector(respuesta.target);
            if (elemento) {
                elemento.remove();
            } else {
                console.error('Elemento a remover no encontrado:', respuesta.target);
            }
        }

        // 5. Ejecutar código JavaScript arbitrario enviado desde el backend
        else if (respuesta.tipo === 'executeJS') {
            new Function(respuesta.script)();
        }
    },

    /**
     * Ejecuta peticiones AJAX ligeras (Ej: evento onchange de combos)
     */
    ejecutar: function(url, datos = {}) {
        let bodyContent = (typeof datos === 'string') 
            ? datos 
            : new URLSearchParams(datos);

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: bodyContent
        })
        .then(response => response.json())
        .then(respuesta => App.procesarRespuesta(respuesta))
        .catch(err => console.error('Error Ajax en App.ejecutar:', err));
    },

    /**
     * Método invocado por los Botones (Button.php) enviando todo el Formulario
     */
    executaAjax: function(url, formName) {
        const form = document.forms[formName];
        if (!form) {
            console.error('Formulario no encontrado:', formName);
            return;
        }

        const formData = new FormData(form);

        fetch(url, {
            method: 'POST',
            body: formData // Envía todos los campos e inputs del formulario
        })
        .then(response => response.json()) // Lee la respuesta como JSON
        .then(respuesta => App.procesarRespuesta(respuesta))
        .catch(error => console.error('Error Ajax en App.executaAjax:', error));
    }
};

// Alias global opcional para mantener compatibilidad si Button.php llama a executaAjax() directamente
function executaAjax(url, formName) {
    App.executaAjax(url, formName);
}