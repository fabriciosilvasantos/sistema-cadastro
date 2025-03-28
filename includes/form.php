<?php if ($mensagem): ?>
    <div class="alert alert-<?php echo $tipo_mensagem; ?>">
        <?php echo $mensagem; ?>
    </div>
<?php endif; ?>

<form method="POST" action="" class="needs-validation" novalidate>
    <div class="mb-4">
        <label for="nome" class="form-label">Nome Completo</label>
        <input type="text" 
               class="form-control" 
               id="nome" 
               name="nome" 
               required 
               minlength="3"
               maxlength="100"
               pattern="[A-Za-zÀ-ÿ\s]+"
               title="Digite apenas letras e espaços"
               placeholder="Digite seu nome completo">
        <div class="invalid-feedback">
            Por favor, insira seu nome completo (apenas letras e espaços).
        </div>
    </div>

    <div class="mb-4">
        <label for="email" class="form-label">Email</label>
        <input type="email" 
               class="form-control" 
               id="email" 
               name="email" 
               required 
               placeholder="seu@email.com">
        <div class="invalid-feedback">
            Por favor, insira um endereço de email válido.
        </div>
    </div>

    <div class="mb-4">
        <label for="senha" class="form-label">Senha</label>
        <input type="password" 
               class="form-control" 
               id="senha" 
               name="senha" 
               required 
               minlength="6"
               maxlength="20"
               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
               title="A senha deve conter pelo menos 6 caracteres, incluindo letras maiúsculas, minúsculas e números"
               placeholder="Mínimo 6 caracteres">
        <div class="invalid-feedback">
            A senha deve ter no mínimo 6 caracteres, incluindo letras maiúsculas, minúsculas e números.
        </div>
    </div>

    <div class="mb-4">
        <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
        <input type="password" 
               class="form-control" 
               id="confirmar_senha" 
               name="confirmar_senha" 
               required 
               minlength="6"
               maxlength="20"
               placeholder="Confirme sua senha">
        <div class="invalid-feedback">
            As senhas não conferem. Por favor, verifique.
        </div>
    </div>

    <div class="mb-4">
        <label for="telefone" class="form-label">Telefone</label>
        <input type="tel" 
               class="form-control" 
               id="telefone" 
               name="telefone" 
               required 
               pattern="\(\d{2}\) \d{5}-\d{4}"
               title="Use o formato: (00) 00000-0000"
               placeholder="(00) 00000-0000">
        <div class="invalid-feedback">
            Por favor, insira um número de telefone válido no formato (00) 00000-0000.
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        Cadastrar
    </button>
</form> 