// script.js
document.addEventListener('DOMContentLoaded', () => {
    // Mapeia o ID do input para o ID da imagem de preview
    const config = {
        'foto_frente': 'preview_frente',
        'foto_costa': 'preview_costa',
        'foto_lado': 'preview_lado'
    };

    // Adiciona o evento para cada campo de foto
    Object.keys(config).forEach(inputId => {
        const input = document.getElementById(inputId);
        const previewId = config[inputId];

        if (input) {
            input.addEventListener('change', function() {
                const preview = document.getElementById(previewId);
                
                if (this.files && this.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }

                    reader.readAsDataURL(this.files[0]);
                } else {
                    preview.style.display = 'none';
                    preview.src = '#';
                }
            });
        }
    });
});