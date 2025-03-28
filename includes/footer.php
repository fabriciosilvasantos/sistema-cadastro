                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Máscara para o telefone
        document.getElementById('telefone').addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });

        // Validação de senha em tempo real
        const senha = document.getElementById('senha');
        const confirmarSenha = document.getElementById('confirmar_senha');
        const feedbackSenha = confirmarSenha.nextElementSibling;

        function validarSenhas() {
            if (senha.value !== confirmarSenha.value) {
                confirmarSenha.setCustomValidity('As senhas não conferem');
                feedbackSenha.textContent = 'As senhas não conferem. Por favor, verifique.';
            } else {
                confirmarSenha.setCustomValidity('');
                feedbackSenha.textContent = 'Por favor, confirme sua senha.';
            }
        }

        senha.addEventListener('input', validarSenhas);
        confirmarSenha.addEventListener('input', validarSenhas);

        // Validação do formulário
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation');
            const submitButton = document.querySelector('button[type="submit"]');
            const spinner = submitButton.querySelector('.spinner-border');

            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        // Mostrar spinner durante o envio
                        submitButton.disabled = true;
                        spinner.classList.remove('d-none');
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Validação em tempo real
        const inputs = document.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.validity.valid) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });
    </script>
</body>
</html> 