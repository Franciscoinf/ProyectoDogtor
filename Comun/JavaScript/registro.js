// Validación en tiempo real para cada campo
document.addEventListener('DOMContentLoaded', () => {
    const campos = {
        dni: document.querySelector('[name="dni"]'),
        nombre: document.querySelector('[name="nombre"]'),
        apellidos: document.querySelector('[name="apellidos"]'),
        email: document.querySelector('[name="email"]'),
        password: document.querySelector('[name="password"]'),
        direccion: document.querySelector('[name="direccion"]'),
        telefono: document.querySelector('[name="telefono"]')
    };

    // Patrones de validación
    const patrones = {
        dni: /^[0-9]{8}[A-Za-z]$/,
        nombre: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/,
        apellidos: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,100}$/,
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/,
        telefono: /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/
    };

    // Mensajes de error
    const mensajesError = {
        dni: 'Formato DNI incorrecto (8 números + letra)',
        nombre: 'Nombre inválido (solo letras y espacios)',
        apellidos: 'Apellidos inválidos',
        email: 'Email no válido',
        password: 'Mínimo 8 caracteres con mayúscula, número y símbolo',
        direccion: 'Dirección requerida',
        telefono: 'Formato teléfono incorrecto'
    };

    // Validar campo individual
    function validarCampo(campo, patron) {
        const valor = campo.value.trim();
        const grupo = campo.closest('.form-group');
        const errorSpan = grupo.querySelector('.error-mensaje');

        if (!valor.match(patron)) {
            grupo.classList.add('invalido');
            if (errorSpan) errorSpan.textContent = mensajesError[campo.name];
            return false;
        } else {
            grupo.classList.remove('invalido');
            if (errorSpan) errorSpan.textContent = '';
            return true;
        }
    }

    // Event listeners para validación en tiempo real
    Object.entries(campos).forEach(([nombre, campo]) => {
        if (campo) {
            campo.addEventListener('blur', () => {
                if (nombre === 'telefono' && campo.value === '') return;
                validarCampo(campo, patrones[nombre] || /.+/);
            });
        }
    });

    // Validación especial para DNI (letra correcta)
    campos.dni.addEventListener('input', function(e) {
        const letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
        const dni = e.target.value.trim();
        
        if (dni.length === 9) {
            const numero = dni.substr(0, 8);
            const letra = dni.substr(8, 1).toUpperCase();
            const letraCorrecta = letras[numero % 23];
            
            if (letra !== letraCorrecta) {
                const grupo = e.target.closest('.form-group');
                grupo.classList.add('invalido');
                grupo.querySelector('.error-mensaje').textContent = 'Letra de DNI incorrecta';
            }
        }
    });
});

// Validación completa del formulario
function validateForm() {
    let valido = true;
    const formulario = document.forms;

    // Validar todos los campos requeridos
    Array.from(formulario.elements).forEach(elemento => {
        if (elemento.required && elemento.value.trim() === '') {
            const grupo = elemento.closest('.form-group');
            grupo.classList.add('invalido');
            grupo.querySelector('.error-mensaje').textContent = 'Campo obligatorio';
            valido = false;
        }
    });

    return valido;
}