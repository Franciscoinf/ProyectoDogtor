  document.addEventListener('DOMContentLoaded', function() {
        const calcularPrecio = () => {
            const fechaEntrada = document.querySelector('[name="fecha_entrada"]').value;
            const horaEntrada = document.querySelector('[name="hora_entrada"]').value;
            const fechaSalida = document.querySelector('[name="fecha_salida"]').value;
            const horaSalida = document.querySelector('[name="hora_salida"]').value;
            
            if (fechaEntrada && horaEntrada && fechaSalida && horaSalida) {
                const entrada = new Date(`${fechaEntrada}T${horaEntrada}`);
                const salida = new Date(`${fechaSalida}T${horaSalida}`);
                
                if (salida > entrada) {
                    const diff = salida - entrada;
                    const horas = Math.floor(diff / 3600000);
                    document.getElementById('precioEstimado').textContent = 
                        `${horas}€ (${horas} horas)`;
                }
            }
        }
        
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('change', calcularPrecio);
        });
        
        calcularPrecio(); 
    });